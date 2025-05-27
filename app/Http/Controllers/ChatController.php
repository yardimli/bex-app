<?php namespace App\Http\Controllers;

use App\Helpers\MyHelper;

// Import MyHelper
use App\Models\ChatHeader;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Note;

class ChatController extends Controller
{
	// Max message history (pairs) to send to LLM
	const MAX_HISTORY_PAIRS = 5;
	const MAX_CONTEXT_NOTES = 5; // Max notes to include in context
	const MAX_CONTEXT_ACTION_ITEMS = 10; // Max action items to include in context
	const MAX_NOTE_CONTENT_LENGTH_IN_CONTEXT = 150; // Max length for note content in context

	/**
	 * Display the main chat view, optionally loading a specific chat.
	 *
	 * @param ChatHeader|null $chatHeader
	 * @return \Illuminate\Contracts\View\View
	 */
	public function show(Request $request, $chatHeaderId = null)
	{
		$user = Auth::user();
		if (!$user) {
			return redirect()->route('login');
		}

		$chatHeader = null;
		if ($chatHeaderId) {
			$chatHeader = ChatHeader::where('id', $chatHeaderId)
				->where('user_id', $user->id)
				->first();
			if (!$chatHeader) {
				return redirect()->route('chat.show')->with('error', 'Chat not found or was deleted.');
			}
		}

		$chatHeaders = $user->chatHeaders()->orderBy('updated_at', 'desc')->get();
		$messages = $chatHeader ? $chatHeader->messages()->get() : collect();

		$initialPrompt = null;
		if (!$chatHeader) { // Only process initial prompts for new chats
			if ($request->has('prompt')) {
				$initialPrompt = trim($request->input('prompt'));
			} elseif ($request->has('summarize_key') && $request->has('prompt_prefix')) {
				$sessionKey = $request->input('summarize_key');
				$promptPrefix = $request->input('prompt_prefix');
				if (session()->has($sessionKey)) {
					$fullText = session($sessionKey);
					// Prepend the prefix and limit the text for the textarea
					// The full text is available for the first LLM call if needed,
					// but the textarea might not handle extremely long text well.
					$initialPrompt = $promptPrefix . Str::limit($fullText, UtilityController::MAX_TEXT_FOR_CHAT_INPUT);

					// We'll store the full text in a separate session variable
					// that the `store` method can pick up for the first message.
					session(['pending_full_text_for_chat' => $fullText]);
					session()->forget($sessionKey); // Clean up original key
				} else {
					Log::warning("Summarize key '{$sessionKey}' not found in session.");
					// Optionally, set an error message or a default prompt
					$initialPrompt = $promptPrefix . "[Error: Content for summarization not found. Please try again.]";
				}
			}
		}

		return view('pages.chat', [
			'chatHeaders' => $chatHeaders,
			'activeChat' => $chatHeader,
			'messages' => $messages,
			'initialPrompt' => $initialPrompt,
		]);
	}

	/**
	 * Store a new message, get LLM response, and potentially create/update chat.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		$request->validate([
			'message' => 'required|string|max:60000',
			'chat_header_id' => 'nullable|integer|exists:chat_headers,id',
			'llm_model' => 'nullable|string|max:100',
			'personality_tone' => 'nullable|string|in:professional,witty,motivational,friendly,poetic,sarcastic',
		]);

		$user = Auth::user();
		$userMessageContent = $request->input('message');
		$selectedTone = $request->input('personality_tone', 'professional');
		$chatHeaderId = $request->input('chat_header_id');
		$selectedModel = $request->input('llm_model');

		$chatHeader = null;
		$isNewChat = false;

		// --- Check for pending full text for the first message of a new chat ---
		$actualUserMessageForLlm = $userMessageContent;
		if (empty($chatHeaderId) && session()->has('pending_full_text_for_chat')) {
			// This is likely the first message of a summarization chat.
			// The $userMessageContent might be a truncated version for the textarea.
			// We use the full text from the session for the LLM.
			$fullTextFromSession = session('pending_full_text_for_chat');
			$promptPrefix = Str::before($userMessageContent, $fullTextFromSession); // Attempt to get prefix if any
			if(Str::contains($userMessageContent, Str::limit($fullTextFromSession, UtilityController::MAX_TEXT_FOR_CHAT_INPUT))) {
				// Reconstruct with full text
				$actualUserMessageForLlm = Str::before($userMessageContent, Str::limit($fullTextFromSession, UtilityController::MAX_TEXT_FOR_CHAT_INPUT)) . $fullTextFromSession;
			} else {
				// Fallback if string reconstruction is tricky, just use the full text with a generic prefix.
				// This might happen if Str::limit changed the string significantly.
				$actualUserMessageForLlm = "Summarize the following content:\n\n" . $fullTextFromSession;
				Log::warning("Could not accurately reconstruct prompt prefix for full text summarization. Using generic prefix.");
			}
			session()->forget('pending_full_text_for_chat'); // Clean up
			Log::info("Using full text from session for initial summarization message. Original input length: " . strlen($userMessageContent) . ", Full text length: " . strlen($actualUserMessageForLlm));
		}
		// --- End check for pending full text ---

		DB::beginTransaction(); // Start transaction for atomicity

		try {
			// --- START: Action Item Extraction ---
			$extractedActionItems = null;
			try {
				$actionItemModel = 'openai/gpt-4o-mini';
				$extractedActionItems = MyHelper::extractActionItems($userMessageContent, $actionItemModel);
			} catch (\Exception $e) {
				Log::error("Exception during action item extraction call", ['error' => $e->getMessage()]);
			}
			// --- END: Action Item Extraction ---

			// --- START: Note Intent Extraction ---
			$noteIntentData = null;
			$createdNoteFromChat = null;
			try {
				$noteIntentModel = env('NOTE_INTENT_LLM', 'openai/gpt-4o-mini');
				$noteIntentData = MyHelper::extractNoteIntents($userMessageContent, $noteIntentModel);

				if ($noteIntentData && isset($noteIntentData['intent'])) {
					if ($noteIntentData['intent'] === 'create_note' && !empty($noteIntentData['title']) && isset($noteIntentData['content'])) {
						$noteTitle = Str::limit(trim($noteIntentData['title']), 250);
						$noteContent = trim($noteIntentData['content']);
						if (!empty($noteTitle)) {
							$newNote = $user->notes()->create([
								'title' => $noteTitle,
								'content' => $noteContent,
							]);
							$createdNoteFromChat = $newNote;
							Log::info("New note created from chat intent", ['note_id' => $newNote->id, 'title' => $noteTitle]);
						} else {
							Log::warning("Note creation intent detected, but title was empty after processing.", ['data' => $noteIntentData]);
						}
					} elseif ($noteIntentData['intent'] === 'append_to_note') {
						Log::info("Append to note intent detected (not yet implemented)", [
							'hint' => $noteIntentData['note_title_hint'] ?? 'N/A',
							'content_to_append' => $noteIntentData['content_to_append'] ?? 'N/A'
						]);
					}
				}
			} catch (\Exception $e) {
				Log::error("Exception during note intent extraction or creation", ['error' => $e->getMessage()]);
			}
			// --- END: Note Intent Extraction ---

			// Find existing or create new ChatHeader
			if ($chatHeaderId) {
				$chatHeader = ChatHeader::where('user_id', $user->id)->findOrFail($chatHeaderId);
			} else {
				$chatHeader = ChatHeader::create([
					'user_id' => $user->id,
					'title' => 'New Chat', // Default title
				]);
				$chatHeaderId = $chatHeader->id;
				$isNewChat = true;
			}

			// --- START: Save Extracted Action Items ---
			if (!empty($extractedActionItems) && is_array($extractedActionItems)) {
				Log::info("Saving extracted action items", ['count' => count($extractedActionItems), 'chat_header_id' => $chatHeaderId]);
				foreach ($extractedActionItems as $itemContent) {
					$itemContent = Str::limit(trim($itemContent), 990);
					if (!empty($itemContent)) {
						try {
							$exists = $user->actionItems()
								->where('content', $itemContent)
								->where('is_done', false)
								->exists();
							if (!$exists) {
								$user->actionItems()->create([
									'content' => $itemContent,
									'is_done' => false,
								]);
								Log::info("Action item saved", ['content' => $itemContent]);
							} else {
								Log::info("Duplicate action item skipped", ['content' => $itemContent]);
							}
						} catch (\Exception $e) {
							Log::error("Failed to save an extracted action item", [
								'content' => $itemContent,
								'error' => $e->getMessage(),
								'chat_header_id' => $chatHeaderId
							]);
						}
					}
				}
			}
			// --- END: Save Extracted Action Items ---

			// --- START: Prepare Context for LLM (Notes & Action Items) ---
			$contextForLlm = "";

			// Fetch user's notes
			$userNotes = $user->notes()->orderBy('updated_at', 'desc')->limit(self::MAX_CONTEXT_NOTES)->get();
			if ($userNotes->isNotEmpty()) {
				$contextForLlm .= "Your current notes (most recent first):\n";
				foreach ($userNotes as $note) {
					$contextForLlm .= "- Title: " . $note->title . "\n";
					if (!empty($note->content)) {
						$contextForLlm .= "  Content: " . Str::limit($note->content, self::MAX_NOTE_CONTENT_LENGTH_IN_CONTEXT) . "\n";
					}
				}
				$contextForLlm .= "\n";
			}

			// Fetch user's open action items
			$userActionItems = $user->actionItems()->where('is_done', false)->orderBy('created_at', 'desc')->limit(self::MAX_CONTEXT_ACTION_ITEMS)->get();
			if ($userActionItems->isNotEmpty()) {
				$contextForLlm .= "Your current open action items (most recent first):\n";
				foreach ($userActionItems as $item) {
					$contextForLlm .= "- " . $item->content . "\n";
				}
				$contextForLlm .= "\n";
			}
			// --- END: Prepare Context for LLM ---

			// Prepare message history for LLM
			$historyMessages = $chatHeader->messages()
				->orderBy('created_at', 'desc')
				->limit(self::MAX_HISTORY_PAIRS * 2)
				->get()->reverse(); // Reverse to get chronological order

			$llmChatMessages = []; // This will be passed to llm_no_tool_call

			// Add the prepared context as a system message if it's not empty.
			// This will be inserted after the main system prompt (persona) by llm_no_tool_call.
			if (!empty(trim($contextForLlm))) {
				$llmChatMessages[] = ['role' => 'system', 'content' => "For your reference, here is some context about your current notes and action items:\n" . trim($contextForLlm)];
				Log::info("Prepared notes/action items context for LLM call for chat ID {$chatHeaderId}");
			}

			foreach ($historyMessages as $message) {
				$llmChatMessages[] = [
					'role' => $message->role,
					'content' => $message->content,
				];
			}

			$userMessage = $chatHeader->messages()->create([
				'role' => 'user',
				'content' => $userMessageContent,
			]);

			$llmChatMessages[] = ['role' => 'user', 'content' => $userMessageContent]; // Current user message

			Log::info("LLM Chat Messages (excluding main system prompt) for chat ID {$chatHeaderId}", ['messages_preview' => array_map(function ($m) {
				return Str::limit($m['content'], 50);
			}, $llmChatMessages)]);


			// Define system prompt (persona)
			$basePrompt = "You are Bex, an AI assistant.";
			switch ($selectedTone) {
				case 'professional':
					$systemPrompt = $basePrompt . " Respond in a helpful, professional, and concise manner.";
					break;
				case 'witty':
					$systemPrompt = $basePrompt . " You have a witty and slightly humorous personality. Keep it clever but helpful.";
					break;
				case 'motivational':
					$systemPrompt = $basePrompt . " You are an upbeat and motivational assistant. Encourage the user and respond with positivity.";
					break;
				case 'friendly':
					$systemPrompt = $basePrompt . " You are warm, friendly, and approachable. Respond in a conversational and encouraging manner.";
					break;
				case 'poetic':
					$systemPrompt = $basePrompt . " You often respond with a touch of poetic flair or thoughtful reflection, while still being helpful.";
					break;
				case 'sarcastic':
					$systemPrompt = $basePrompt . " You are known for dry wit and occasional sarcasm, but ultimately aim to be helpful (even if begrudgingly).";
					break;
				default:
					$systemPrompt = $basePrompt . " Respond in a helpful, professional, and concise manner.";
					break;
			}
			Log::info("Using personality tone: {$selectedTone}");

			$modelToUse = $selectedModel ?: env('DEFAULT_LLM', 'openai/gpt-4o-mini');

			// Call the LLM
			$llmResult = MyHelper::llm_no_tool_call(
				$modelToUse,
				$systemPrompt,      // Main system prompt (persona)
				$llmChatMessages,   // Context + History + Current User Message
				false               // Get raw text content
			);

			$assistantMessageContent = "Sorry, I couldn't get a response. Please try again."; // Default error message
			$promptTokens = $llmResult['prompt_tokens'] ?? 0;
			$completionTokens = $llmResult['completion_tokens'] ?? 0;
			$assistantMessage = null;

			if (isset($llmResult['content']) && !str_starts_with($llmResult['content'], 'Error:')) {
				$userMessage->prompt_tokens = $promptTokens;
				$userMessage->save();

				$assistantMessageContent = $llmResult['content'];
				$assistantMessage = $chatHeader->messages()->create([
					'role' => 'assistant',
					'content' => $assistantMessageContent,
					'completion_tokens' => $completionTokens,
				]);
			} else {
				Log::error("LLM call failed or returned error content", ['result' => $llmResult, 'chat_header_id' => $chatHeaderId]);
				$assistantMessage = $chatHeader->messages()->create([
					'role' => 'assistant',
					'content' => $assistantMessageContent,
					'completion_tokens' => 0,
				]);
			}

			// --- Generate Title for New Chats ---
			$updatedTitle = null;
			if ($isNewChat && $assistantMessage) {
				try {
					$titlePrompt = "Based on the following user query and assistant response, generate a very short, concise title (max 5 words) for this conversation. Only output the title text, nothing else.\n\nUser: " . Str::limit($userMessageContent, 100) . "\n\nAssistant: " . Str::limit($assistantMessageContent, 150);
					$titleResult = MyHelper::llm_no_tool_call(
						env('DEFAULT_LLM', 'openai/gpt-4o-mini'), // Use a fast model for titles
						"You are a title generator. Only output the title text.",
						[['role' => 'user', 'content' => $titlePrompt]],
						false
					);
					if (isset($titleResult['content']) && !str_starts_with($titleResult['content'], 'Error:')) {
						$updatedTitle = trim(Str::limit($titleResult['content'], 50));
						$chatHeader->title = $updatedTitle;
					} else {
						$chatHeader->title = "Chat: " . Str::limit($userMessageContent, 30);
						$updatedTitle = $chatHeader->title;
						Log::warning("Failed to generate title for new chat", ['chat_header_id' => $chatHeaderId]);
					}
				} catch (\Exception $e) {
					Log::error("Exception during title generation", ['error' => $e->getMessage(), 'chat_header_id' => $chatHeaderId]);
					$chatHeader->title = "Chat: " . Str::limit($userMessageContent, 30);
					$updatedTitle = $chatHeader->title;
				}
			}

			$chatHeader->touch();
			$chatHeader->save();

			DB::commit();

			return response()->json([
				'success' => true,
				'user_message' => [
					'id' => $userMessage->id,
					'role' => 'user',
					'content' => $userMessageContent,
					'created_at' => $userMessage->created_at->diffForHumans(),
					'can_delete' => true
				],
				'assistant_message' => [
					'id' => $assistantMessage->id,
					'role' => 'assistant',
					'content' => $assistantMessageContent,
					'created_at' => $assistantMessage->created_at->diffForHumans(),
					'can_delete' => false
				],
				'chat_header_id' => $chatHeaderId,
				'is_new_chat' => $isNewChat,
				'updated_title' => $updatedTitle,
			]);

		} catch (\Exception $e) {
			DB::rollBack();
			Log::error("Error processing chat message: " . $e->getMessage(), ['exception' => $e]);
			return response()->json(['error' => 'An internal error occurred. Please try again.'], 500);
		}
	}

	/**
	 * Delete a user message and the subsequent assistant message.
	 *
	 * @param ChatMessage $userMessage
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroyMessagePair(ChatMessage $userMessage)
	{
		$user = Auth::user();
		if ($userMessage->role !== 'user' || $userMessage->chatHeader->user_id !== $user->id) {
			return response()->json(['error' => 'Unauthorized or invalid message'], 403);
		}

		DB::beginTransaction();
		try {
			$assistantMessage = ChatMessage::where('chat_header_id', $userMessage->chat_header_id)
				->where('role', 'assistant')
				->where('id', '>', $userMessage->id)
				->orderBy('id', 'asc')
				->first();

			$deletedAssistantId = null;
			if ($assistantMessage) {
				$deletedAssistantId = $assistantMessage->id;
				$assistantMessage->delete();
			}

			$deletedUserId = $userMessage->id;
			$userMessage->delete();

			DB::commit();
			return response()->json([
				'success' => true,
				'deleted_user_id' => $deletedUserId,
				'deleted_assistant_id' => $deletedAssistantId
			]);
		} catch (\Exception $e) {
			DB::rollBack();
			Log::error("Error deleting message pair: " . $e->getMessage(), ['user_message_id' => $userMessage->id]);
			return response()->json(['error' => 'Could not delete messages.'], 500);
		}
	}

	public function destroyHeader(ChatHeader $chatHeader)
	{
		$user = Auth::user();
		if ($chatHeader->user_id !== $user->id) {
			return response()->json(['error' => 'Unauthorized'], 403);
		}

		DB::beginTransaction();
		try {
			$chatHeader->delete();
			DB::commit();
			return response()->json(['success' => true]);
		} catch (\Exception $e) {
			DB::rollBack();
			Log::error("Error deleting chat header: " . $e->getMessage(), ['chat_header_id' => $chatHeader->id]);
			return response()->json(['error' => 'Could not delete chat.'], 500);
		}
	}

	public function textToSpeech(Request $request)
	{
		$validated = $request->validate([
			'message_text' => 'required|string|max:4096',
			'voice' => 'nullable|string|in:alloy,echo,fable,onyx,nova,shimmer',
		]);

		$text = $validated['message_text'];
		$voice = $validated['voice'] ?? 'alloy';

		Log::info("TTS request received. Voice: {$voice}, Text length: " . strlen($text));
		try {
			$result = MyHelper::text2speech($text, $voice, 'tts_' . Str::slug(Str::limit($text, 20)));
			if ($result['success'] && isset($result['fileUrl'])) {
				Log::info("TTS generation successful. URL: " . $result['fileUrl']);
				return response()->json([
					'success' => true,
					'fileUrl' => $result['fileUrl']
				]);
			} else {
				Log::error("TTS generation failed in Helper.", ['result' => $result]);
				return response()->json([
					'success' => false,
					'error' => $result['message'] ?? 'Text-to-Speech generation failed.'
				], 500);
			}
		} catch (\Exception $e) {
			Log::error("Exception during TTS generation: " . $e->getMessage(), ['exception' => $e]);
			return response()->json([
				'success' => false,
				'error' => 'An unexpected error occurred during audio generation.'
			], 500);
		}
	}
}
