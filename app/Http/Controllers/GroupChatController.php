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
use Illuminate\Support\Facades\Cache;

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
        $participants = collect();
        $mentionableParticipants = collect();


        if ($groupChatHeaderId) {
            $groupChatHeader = GroupChatHeader::where('id', $groupChatHeaderId)
                ->where('team_id', $team->id)
                ->firstOrFail();
            $participants = $groupChatHeader->participants()->get();

            $bexParticipant = new \stdClass();
            $bexParticipant->id = 'bex_ai'; // A unique, non-numeric ID.
            $bexParticipant->name = 'Bex';

            $mentionableParticipants = $participants->reject(function ($participant) use ($user) {
                return $participant->id === $user->id;
            })->prepend($bexParticipant);
        }

        $messages = $groupChatHeader ? $groupChatHeader->messages()->with('user')->get() : collect();

        return view('pages.group_chat', [
            'team' => $team,
            'activeChat' => $groupChatHeader,
            'messages' => $messages,
            'userTeams' => $userTeams,
            'currentTeamId' => $currentTeamId,
            'participants' => $participants,
            'mentionableParticipants' => $mentionableParticipants,
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
            'attached_files' => 'nullable|array',
            'attached_files.*' => 'integer|exists:files,id',
            'llm_model' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $team = Team::findOrFail($validated['team_id']);

        if (!$user->teams()->where('team_id', $team->id)->exists()) {
            return response()->json(['error' => 'You are not a member of this team.'], 403);
        }

        $userPrompt = $validated['message'] ?? '';
        $groupChatHeaderId = $validated['group_chat_header_id'];
        $attachedFileIds = $validated['attached_files'] ?? [];
        $selectedModel = $validated['llm_model'] ?? null;
        $actualUserMessageForLlm = $userPrompt;

        $fileContextText = '';
        $validatedFileIds = [];
        if (!empty($attachedFileIds)) {
            $files = \App\Models\File::with('sharedWithTeams')->find($attachedFileIds);
            $userTeamIds = $user->teams()->pluck('teams.id');

            foreach ($files as $file) {
                // Authorization Check: User must own the file OR it must be shared with their teams
                $isOwner = $file->user_id === $user->id;
                $isSharedWithTeam = $file->sharedWithTeams->pluck('id')->intersect($userTeamIds)->isNotEmpty();

                if ($isOwner || $isSharedWithTeam) {
                    try {
                        $fileText = MyHelper::extractTextFromFile($file);
                        if (!empty(trim($fileText))) {
                            $fileContextText .= "--- Start of content from file: {$file->original_filename} ---\n";
                            $fileContextText .= $fileText . "\n";
                            $fileContextText .= "--- End of content from file: {$file->original_filename} ---\n\n";
                            $validatedFileIds[] = $file->id;
                        } else {
                            Log::warning("Extracted text was empty for attached file_id: {$file->id} in group chat");
                            $actualUserMessageForLlm .= "\n[System notice: The attached file '{$file->original_filename}' appears to be empty or contains no extractable text.]";
                        }
                    } catch (\Exception $e) {
                        Log::warning("Could not extract text from attached file_id: {$file->id} for group chat. Error: " . $e->getMessage());
                        $actualUserMessageForLlm .= "\n[System notice: Could not process the attached file '{$file->original_filename}'.]";
                    }
                } else {
                    Log::warning("User {$user->id} attempted to use unauthorized file_id: {$file->id} in group chat {$groupChatHeaderId}.");
                }
            }
        }

        if (!empty($fileContextText)) {
            $actualUserMessageForLlm = $fileContextText . $actualUserMessageForLlm;
            Log::info("Prepended text from " . count($validatedFileIds) . " file(s) to user prompt for group_chat_header_id: {$groupChatHeaderId}");
        }

        if (empty(trim($userPrompt)) && empty($validatedFileIds)) {
            return response()->json(['error' => 'A message or a file is required.'], 422);
        }


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

            if (!empty($validatedFileIds)) {
                $userMessage->files()->attach($validatedFileIds);
            }

            $historyMessages = $groupChatHeader->messages()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(self::MAX_HISTORY_PAIRS * 2)
                ->get()->reverse();

            $llmChatMessages = [];
            foreach ($historyMessages as $message) {
                if ($message->id === $userMessage->id) {
                    continue;
                }
                if ($message->role === 'assistant') {
                    $llmChatMessages[] = ['role' => 'assistant', 'content' => $message->content];
                } else {
                    $userName = $message->user ? $message->user->name : 'A user';
                    $llmChatMessages[] = ['role' => 'user', 'content' => "{$userName}: {$message->content}"];
                }
            }

            $llmChatMessages[] = [
                'role' => 'user',
                'content' => "{$user->name}: {$actualUserMessageForLlm}"
            ];

            $shouldReply = MyHelper::shouldAiReplyInGroupChat($llmChatMessages, $selectedModel);
            $assistantMessage = null;

            if ($shouldReply) {
                Log::info("AI will reply in group chat {$groupChatHeaderId}.");
                $systemPrompt = "You are Bex, an AI assistant in a group chat. Be helpful and address users by name if appropriate. The user messages are prefixed with their name.";
                $modelToUse = $selectedModel ?: env('DEFAULT_LLM', 'openai/gpt-4o-mini');
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

    public function getUpdates(Request $request, Team $team, GroupChatHeader $groupChatHeader)
    {
        $user = Auth::user();

        // Authorization check
        if (!$groupChatHeader->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lastMessageId = $request->input('last_message_id', 0);

        // Fetch new messages, eager load relations for efficiency
        $newMessages = GroupChatMessage::where('group_chat_header_id', $groupChatHeader->id)
            ->where('id', '>', $lastMessageId)
            ->with('user', 'files')
            ->orderBy('id', 'asc')
            ->get();

        // Fetch typing users from cache
        $typingUsers = [];
        $participants = $groupChatHeader->participants()->get();
        foreach ($participants as $participant) {
            // Don't show the current user that they are typing
            if ($participant->id === $user->id) {
                continue;
            }
            $cacheKey = 'group-chat:'.$groupChatHeader->id.':typing:'.$participant->id;
            if (Cache::has($cacheKey)) {
                $typingUsers[] = Cache::get($cacheKey);
            }
        }

        return response()->json([
            'new_messages' => $newMessages,
            'typing_users' => array_unique($typingUsers),
        ]);
    }

    /**
     * Set a user's typing status in the cache.
     */
    public function isTyping(Request $request, Team $team, GroupChatHeader $groupChatHeader)
    {
        $user = Auth::user();

        // Authorization check
        if (!$groupChatHeader->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cacheKey = 'group-chat:'.$groupChatHeader->id.':typing:'.$user->id;

        // Set cache with a short TTL (e.g., 10 seconds)
        Cache::put($cacheKey, $user->name, now()->addSeconds(4));

        return response()->json(['success' => true]);
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
    public function destroyHeader(GroupChatHeader $groupChatHeader)
    {
        $user = Auth::user();

        // Authorization: User must be a member of the team that owns the chat.
        if (!$user->teams()->where('team_id', $groupChatHeader->team_id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            // The GroupChatHeader model's 'deleting' event will handle deleting messages.
            $groupChatHeader->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting group chat header: " . $e->getMessage(), ['group_chat_header_id' => $groupChatHeader->id]);
            return response()->json(['error' => 'Could not delete chat.'], 500);
        }
    }
}
