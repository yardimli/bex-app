<?php

namespace App\Http\Controllers;

use App\Helpers\MyHelper;
use App\Models\GroupChatHeader;
use App\Models\GroupChatMessage;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class GroupChatController extends Controller
{
    const MAX_HISTORY_PAIRS = 8;

    public function show(Request $request, Team $team, $groupChatHeaderId = null)
    {
        $user = Auth::user();
        if (!$user->teams()->where('team_id', $team->id)->exists()) {
            return redirect()->route('dashboard')->with('error', 'You are not a member of this team.');
        }

        $userTeams = $user->teams()->get();
        $currentTeamId = session('current_team_id');
        $groupChatHeader = null;
        if ($groupChatHeaderId) {
            $groupChatHeader = GroupChatHeader::where('id', $groupChatHeaderId)
                ->where('team_id', $team->id)
                ->firstOrFail();
        }

        $messages = $groupChatHeader ? $groupChatHeader->messages()->with('user')->get() : collect();

        return view('pages.group_chat', [
            'team' => $team,
            'activeChat' => $groupChatHeader,
            'messages' => $messages,
            'userTeams' => $userTeams,
            'currentTeamId' => $currentTeamId,
        ]);
    }

    public function setup(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|integer|exists:teams,id',
            'title' => 'required|string|max:255',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => [
                'integer',
                Rule::exists('team_members', 'user_id')->where(function ($query) use ($request) {
                    return $query->where('team_id', $request->team_id);
                }),
            ],
            'llm_model' => 'nullable|string|max:100', // Add validation for llm_model
        ]);

        $user = Auth::user();
        $team = Team::findOrFail($validated['team_id']);

        if (!$user->teams()->where('team_id', $team->id)->exists()) {
            return response()->json(['error' => 'You are not a member of this team.'], 403);
        }

        $groupChatHeader = DB::transaction(function () use ($user, $team, $validated) {
            $chat = $team->groupChats()->create([
                'creator_id' => $user->id,
                'title' => $validated['title'],
                'llm_model' => $validated['llm_model'] ?? env('DEFAULT_LLM'),
            ]);

            // Add the creator and the selected participants to the chat
            $allParticipantIds = array_unique(array_merge($validated['participant_ids'], [$user->id]));
            $chat->participants()->attach($allParticipantIds);

            return $chat;
        });

        return response()->json([
            'success' => true,
            'redirect_url' => route('group-chat.show', ['team' => $team->id, 'groupChatHeader' => $groupChatHeader->id]),
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:60000',
            'team_id' => 'required|integer|exists:teams,id',
            'group_chat_header_id' => 'nullable|integer|exists:group_chat_headers,id',
        ]);

        $user = Auth::user();
        $team = Team::findOrFail($validated['team_id']);

        if (!$user->teams()->where('team_id', $team->id)->exists()) {
            return response()->json(['error' => 'You are not a member of this team.'], 403);
        }

        $userPrompt = $validated['message'];
        $groupChatHeaderId = $validated['group_chat_header_id'];
        $groupChatHeader = null;
        $isNewChat = false;

        DB::beginTransaction();

        try {
            if ($groupChatHeaderId) {
                $groupChatHeader = GroupChatHeader::where('team_id', $team->id)->findOrFail($groupChatHeaderId);
            } else {
                $groupChatHeader = $team->groupChats()->create([
                    'creator_id' => $user->id,
                    'title' => 'New Group Chat',
                    'llm_model' => env('DEFAULT_LLM', 'openai/gpt-4o-mini'),
                ]);
                $groupChatHeader->participants()->attach($user->id);
                $groupChatHeaderId = $groupChatHeader->id;
                $isNewChat = true;
            }

            $userMessage = $groupChatHeader->messages()->create([
                'user_id' => $user->id,
                'role' => 'user',
                'content' => $userPrompt,
            ]);

            $historyMessages = $groupChatHeader->messages()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(self::MAX_HISTORY_PAIRS * 2)
                ->get()->reverse();

            $llmChatMessages = [];
            foreach ($historyMessages as $message) {
                if ($message->role === 'assistant') {
                    $llmChatMessages[] = ['role' => 'assistant', 'content' => $message->content];
                } else {
                    $userName = $message->user ? $message->user->name : 'A user';
                    $llmChatMessages[] = ['role' => 'user', 'content' => "{$userName}: {$message->content}"];
                }
            }

            $shouldReply = MyHelper::shouldAiReplyInGroupChat($llmChatMessages, $groupChatHeader->llm_model);
            $assistantMessage = null;

            if ($shouldReply) {
                Log::info("AI will reply in group chat {$groupChatHeaderId}.");
                $systemPrompt = "You are Bex, an AI assistant in a group chat. Be helpful and address users by name if appropriate. The user messages are prefixed with their name.";
                $modelToUse = $groupChatHeader->llm_model ?: env('DEFAULT_LLM', 'openai/gpt-4o-mini');
                $llmResult = MyHelper::llm_no_tool_call($modelToUse, $systemPrompt, $llmChatMessages, false);

                if (isset($llmResult['content']) && !str_starts_with($llmResult['content'], 'Error:')) {
                    $userMessage->prompt_tokens = $llmResult['prompt_tokens'] ?? 0;
                    $userMessage->save();
                    $assistantMessage = $groupChatHeader->messages()->create([
                        'user_id' => null,
                        'role' => 'assistant',
                        'content' => $llmResult['content'],
                        'completion_tokens' => $llmResult['completion_tokens'] ?? 0,
                    ]);
                } else {
                    Log::error("Group Chat LLM call failed", ['result' => $llmResult, 'group_chat_header_id' => $groupChatHeaderId]);
                }
            } else {
                Log::info("AI will not reply in group chat {$groupChatHeaderId}.");
            }

            $updatedTitle = null;
            if ($isNewChat) {
                $titlePrompt = "Based on the following user query, generate a very short, concise title (max 5 words) for this group conversation. Only output the title text, nothing else.\n\nUser: " . Str::limit($userPrompt, 150);
                $titleResult = MyHelper::llm_no_tool_call(env('DEFAULT_LLM', 'openai/gpt-4o-mini'), "You are a title generator.", [['role' => 'user', 'content' => $titlePrompt]], false);
                if (isset($titleResult['content']) && !str_starts_with($titleResult['content'], 'Error:')) {
                    $updatedTitle = trim(Str::limit($titleResult['content'], 50));
                    $groupChatHeader->title = $updatedTitle;
                } else {
                    $groupChatHeader->title = "Group Chat: " . Str::limit($userPrompt, 30);
                    $updatedTitle = $groupChatHeader->title;
                }
            }

            $groupChatHeader->touch();
            $groupChatHeader->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'user_message' => $userMessage->load('user'),
                'assistant_message' => $assistantMessage ? $assistantMessage->load('user') : null,
                'group_chat_header_id' => $groupChatHeaderId,
                'is_new_chat' => $isNewChat,
                'updated_title' => $updatedTitle,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing group chat message: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'An internal error occurred.'], 500);
        }
    }

    public function indexHeaders(Request $request, Team $team)
    {
        $user = Auth::user();
        if (!$user->teams()->where('team_id', $team->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chatHeaders = $team->groupChats()
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->select('id', 'title', 'updated_at')
            ->get();

        return response()->json($chatHeaders);
    }


    public function search(Request $request, Team $team)
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);
        $user = Auth::user();
        if (!$user->teams()->where('team_id', $team->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $searchTerm = $request->input('q');
        $chatHeaders = $team->groupChats()
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->where(function (Builder $query) use ($searchTerm) {
                $query->where('title', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhereHas('messages', function (Builder $msgQuery) use ($searchTerm) {
                        $msgQuery->where('content', 'LIKE', '%' . $searchTerm . '%');
                    });
            })
            ->select('group_chat_headers.id', 'group_chat_headers.title', 'group_chat_headers.updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($chatHeaders);
    }

    public function destroyMessagePair(GroupChatMessage $userMessage)
    {
        $user = Auth::user();
        if ($userMessage->role !== 'user' || !$user->teams()->where('team_id', $userMessage->groupChatHeader->team_id)->exists()) {
            return response()->json(['error' => 'Unauthorized or invalid message'], 403);
        }

        DB::beginTransaction();
        try {
            $assistantMessage = GroupChatMessage::where('group_chat_header_id', $userMessage->group_chat_header_id)
                ->where('role', 'assistant')
                ->where('id', '>', $userMessage->id)
                ->orderBy('id', 'asc')
                ->first();

            $deletedAssistantId = null;
            if ($assistantMessage) {
                $deletedAssistantId = $assistantMessage->id;
                $assistantMessage->delete();
            }
            $userMessage->delete();
            DB::commit();
            return response()->json(['success' => true, 'deleted_user_id' => $userMessage->id, 'deleted_assistant_id' => $deletedAssistantId]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Could not delete messages.'], 500);
        }
    }
}
