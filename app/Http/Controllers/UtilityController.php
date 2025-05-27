<?php

	namespace App\Http\Controllers;

	use App\Helpers\MyHelper;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Validator;
	use Illuminate\Support\Str; // Add Str facade

	class UtilityController extends Controller
	{
		// Max characters to pass to the chat input.
		// This is not the LLM limit, but what we're comfortable putting in a textarea.
		// The LLM itself will have its own context window limits.
		const MAX_TEXT_FOR_CHAT_INPUT = 25000; // Adjust as needed

		public function processFileUploadForSummarization(Request $request)
		{
			$validator = Validator::make($request->all(), [
				'file' => 'required|file|mimes:txt,pdf,docx|max:10240',
			]);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
			}

			try {
				$file = $request->file('file');
				$extractedText = MyHelper::getTextFromUploadedFile($file);

				if (empty(trim($extractedText))) {
					return response()->json(['success' => false, 'error' => 'Could not extract text from the file or the file is empty.'], 400);
				}

				$fileName = $file->getClientOriginalName();
				$promptPrefix = "Summarize the following document content from file \"{$fileName}\":\n\n";

				// Store the full text in session if it's too long for direct URL,
				// otherwise, pass it directly for the prompt.
				if (strlen($promptPrefix . $extractedText) > 2000) { // Heuristic for URL length
					$sessionKey = 'summarization_text_' . Str::random(16);
					session([$sessionKey => $extractedText]); // Store full text
					// The client will construct the final prompt using this key
					return response()->json([
						'success' => true,
						'prompt_prefix' => $promptPrefix,
						'text_key' => $sessionKey, // Client will use this
						'text_preview' => Str::limit($extractedText, self::MAX_TEXT_FOR_CHAT_INPUT) // For immediate display if needed
					]);
				} else {
					// Text is short enough, can be part of the prompt directly
					return response()->json([
						'success' => true,
						'prompt_prefix' => $promptPrefix,
						'full_text_for_prompt' => $extractedText // Client will use this
					]);
				}

			} catch (\Exception $e) {
				Log::error('File summarization processing error: ' . $e->getMessage());
				return response()->json(['success' => false, 'error' => 'Failed to process file: ' . $e->getMessage()], 500);
			}
		}

		public function processUrlForSummarization(Request $request)
		{
			$validator = Validator::make($request->all(), [
				'url' => 'required|url',
			]);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
			}

			try {
				$url = $request->input('url');
				$extractedText = MyHelper::getTextFromUrl($url);

				if (empty(trim($extractedText))) {
					return response()->json(['success' => false, 'error' => 'Could not extract text from the URL or the page is empty.'], 400);
				}

				$promptPrefix = "Summarize the content of this webpage ({$url}):\n\n";

				if (strlen($promptPrefix . $extractedText) > 2000) { // Heuristic for URL length
					$sessionKey = 'summarization_text_' . Str::random(16);
					session([$sessionKey => $extractedText]);
					return response()->json([
						'success' => true,
						'prompt_prefix' => $promptPrefix,
						'text_key' => $sessionKey,
						'text_preview' => Str::limit($extractedText, self::MAX_TEXT_FOR_CHAT_INPUT)
					]);
				} else {
					return response()->json([
						'success' => true,
						'prompt_prefix' => $promptPrefix,
						'full_text_for_prompt' => $extractedText
					]);
				}

			} catch (\Exception $e) {
				Log::error('URL summarization processing error: ' . $e->getMessage());
				return response()->json(['success' => false, 'error' => 'Failed to process URL: ' . $e->getMessage()], 500);
			}
		}
	}
