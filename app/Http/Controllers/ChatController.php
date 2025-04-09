<?php

	namespace App\Http\Controllers;

	use App\Helpers\MyHelper;

	// Import MyHelper
	use App\Models\ChatHeader;
	use App\Models\ChatMessage;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Str;

	class ChatController extends Controller
	{
		// Max message history (pairs) to send to LLM
		const MAX_HISTORY_PAIRS = 5;

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
				return redirect()->route('login'); // Should be handled by middleware, but belt-and-suspenders
			}

			$chatHeader = null;

			// If a chat ID was provided, try to find it
			if ($chatHeaderId) {
				$chatHeader = ChatHeader::where('id', $chatHeaderId)
					->where('user_id', $user->id)
					->first();

				// If requested chat doesn't exist or doesn't belong to user, redirect to main chat page
				if (!$chatHeader) {
					return redirect()->route('chat.show')->with('error', 'Chat not found or was deleted.');
				}
			}

			$chatHeaders = $user->chatHeaders()->orderBy('updated_at', 'desc')->get();
			$messages = $chatHeader ? $chatHeader->messages()->get() : collect();

			$initialPrompt = null;
			if (!$chatHeader && $request->has('prompt')) {
				$initialPrompt = trim($request->input('prompt'));
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
				'message' => 'required|string|max:4000',
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

			DB::beginTransaction(); // Start transaction for atomicity
			try {
				// --- START: Action Item Extraction ---
				// We do this *before* creating the user message in DB,
				// but it needs to be inside the transaction.
				// Run this extraction regardless of whether it's a new chat or not.
				$extractedActionItems = null;
				try {
					// Use a potentially faster/cheaper model for detection if desired
					// Pass null to use the default from the helper/env
					$actionItemModel = 'openai/gpt-4o-mini'; // Or another suitable model like Claude Haiku
					//$actionItemModel = 'anthropic/claude-3-haiku-20240307';
					$extractedActionItems = MyHelper::extractActionItems($userMessageContent, $actionItemModel);
				} catch (\Exception $e) {
					Log::error("Exception during action item extraction call", ['error' => $e->getMessage()]);
					// Do not stop the chat flow, just log the error.
				}
				// --- END: Action Item Extraction ---


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
						// Basic check to avoid overly long items, although the model should handle length
						$itemContent = Str::limit(trim($itemContent), 990); // Limit slightly below DB max
						if (!empty($itemContent)) {
							try {
								// Check for duplicates (optional, based on exact content match)
								$exists = $user->actionItems()
									->where('content', $itemContent)
									->where('is_done', false) // Only check against open items
									->exists();

								if (!$exists) {
									$user->actionItems()->create([
										'content' => $itemContent,
										'is_done' => false,
										// 'due_date' => null, // Add logic later if needed
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
								// Continue saving other items if possible
							}
						}
					}
					// Potential future enhancement: Notify user in chat response or via UI toast
				}
				// --- END: Save Extracted Action Items ---


				// Prepare message history for LLM
				$historyMessages = $chatHeader->messages()
					->orderBy('created_at', 'desc')
					->limit(self::MAX_HISTORY_PAIRS * 2) // Get last N pairs
					->get();

				$llmMessages = [];
				foreach ($historyMessages as $message) {
					$llmMessages[] = [
						'role' => $message->role,
						'content' => $message->content,
					];
				}

				$userMessage = $chatHeader->messages()->create([
					'role' => 'user',
					'content' => $userMessageContent,
				]);

				$llmMessages[] = ['role' => 'user', 'content' => $userMessageContent];

				Log::info("LLM Messages for chat ID {$chatHeaderId}", ['messages' => $llmMessages]);

				// Define system prompt (optional, can be customized)
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
					default: // Fallback to professional
						$systemPrompt = $basePrompt . " Respond in a helpful, professional, and concise manner.";
						break;
				}
				Log::info("Using personality tone: {$selectedTone}");

				$modelToUse = $selectedModel ?: env('DEFAULT_LLM', 'openai/gpt-4o-mini');

				// Call the LLM
				$llmResult = MyHelper::llm_no_tool_call(
					$modelToUse,
					$systemPrompt,
					$llmMessages,
					false // Get raw text content
				);

				$assistantMessageContent = "Sorry, I couldn't get a response. Please try again."; // Default error message
				$promptTokens = $llmResult['prompt_tokens'] ?? 0;
				$completionTokens = $llmResult['completion_tokens'] ?? 0;
				$assistantMessage = null;

				if (isset($llmResult['content']) && !str_starts_with($llmResult['content'], 'Error:')) {
					// Update user message with prompt tokens (approximation, as it includes system prompt)
					$userMessage->prompt_tokens = $promptTokens;
					$userMessage->save();

					$assistantMessageContent = $llmResult['content'];
					// Save the assistant's message
					$assistantMessage = $chatHeader->messages()->create([
						'role' => 'assistant',
						'content' => $assistantMessageContent,
						'completion_tokens' => $completionTokens,
					]);
				} else {
					Log::error("LLM call failed or returned error content", ['result' => $llmResult, 'chat_header_id' => $chatHeaderId]);
					// Rollback might be too aggressive here, maybe just log and return error?
					// For now, we'll save the assistant error message
					$assistantMessage = $chatHeader->messages()->create([
						'role' => 'assistant',
						'content' => $assistantMessageContent, // Save the error message
						'completion_tokens' => 0,
					]);
					// No need to rollback, just return the error message in the response below
					// DB::rollBack();
					// return response()->json(['error' => $llmResult['content'] ?? 'LLM Error'], 500);
				}


				// --- Generate Title for New Chats ---
				$updatedTitle = null;
				if ($isNewChat && $assistantMessage) { // Only if it's a new chat and we got a response
					try {
						$titlePrompt = "Based on the following user query and assistant response, generate a very short, concise title (max 5 words) for this conversation. Only output the title text, nothing else.\n\nUser: " . Str::limit($userMessageContent, 100) . "\n\nAssistant: " . Str::limit($assistantMessageContent, 150);

						$titleResult = MyHelper::llm_no_tool_call(
							env('DEFAULT_LLM', 'openai/gpt-4o-mini'),
							"You are a title generator. Only output the title text.",
							[['role' => 'user', 'content' => $titlePrompt]],
							false
						);

						if (isset($titleResult['content']) && !str_starts_with($titleResult['content'], 'Error:')) {
							$updatedTitle = trim(Str::limit($titleResult['content'], 50)); // Limit length
							$chatHeader->title = $updatedTitle;
						} else {
							// Fallback title if generation fails
							$chatHeader->title = "Chat: " . Str::limit($userMessageContent, 30);
							$updatedTitle = $chatHeader->title;
							Log::warning("Failed to generate title for new chat", ['chat_header_id' => $chatHeaderId]);
						}

					} catch (\Exception $e) {
						Log::error("Exception during title generation", ['error' => $e->getMessage(), 'chat_header_id' => $chatHeaderId]);
						// Fallback title on exception
						$chatHeader->title = "Chat: " . Str::limit($userMessageContent, 30);
						$updatedTitle = $chatHeader->title;
					}
				}

				// Touch the header to update its `updated_at` timestamp for sorting
				$chatHeader->touch();
				$chatHeader->save(); // Save title if updated

				DB::commit(); // Commit transaction

				// Prepare response data for JS
				return response()->json([
					'success' => true,
					'user_message' => [
						'id' => $userMessage->id,
						'role' => 'user',
						'content' => $userMessageContent,
						'created_at' => $userMessage->created_at->diffForHumans(), // Or format as needed
						'can_delete' => true // Flag for JS
					],
					'assistant_message' => [
						'id' => $assistantMessage->id,
						'role' => 'assistant',
						'content' => $assistantMessageContent,
						'created_at' => $assistantMessage->created_at->diffForHumans(), // Or format as needed
						'can_delete' => false // Flag for JS
					],
					'chat_header_id' => $chatHeaderId,
					'is_new_chat' => $isNewChat,
					'updated_title' => $updatedTitle, // Send the new title if generated
				]);

			} catch (\Exception $e) {
				DB::rollBack(); // Rollback on any error
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

			// Ensure the message belongs to the user and is a 'user' role message
			if ($userMessage->role !== 'user' || $userMessage->chatHeader->user_id !== $user->id) {
				return response()->json(['error' => 'Unauthorized or invalid message'], 403);
			}

			DB::beginTransaction();
			try {
				// Find the immediately following assistant message in the same chat
				$assistantMessage = ChatMessage::where('chat_header_id', $userMessage->chat_header_id)
					->where('role', 'assistant')
					->where('id', '>', $userMessage->id) // Find message *after* user message
					->orderBy('id', 'asc') // Get the very next one
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

			// Ensure the chat header belongs to the authenticated user
			if ($chatHeader->user_id !== $user->id) {
				return response()->json(['error' => 'Unauthorized'], 403);
			}

			DB::beginTransaction();
			try {
				// Delete associated messages first (if cascade delete is not set up)
				// $chatHeader->messages()->delete(); // Uncomment if cascade is not on
				$chatHeader->delete(); // This should trigger cascade delete if set in the model migration

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
				'message_text' => 'required|string|max:4096', // Limit text length for safety
				'voice' => 'nullable|string|in:alloy,echo,fable,onyx,nova,shimmer', // Optional: Allow specific OpenAI voices
			]);

			$text = $validated['message_text'];
			// Use provided voice or a default one
			$voice = $validated['voice'] ?? 'alloy'; // Defaulting to 'alloy'

			Log::info("TTS request received. Voice: {$voice}, Text length: " . strlen($text));

			try {
				// Use the MyHelper function
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
					], 500); // Internal Server Error status
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
