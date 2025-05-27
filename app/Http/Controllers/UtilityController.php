<?php

	namespace App\Http\Controllers;

	use App\Helpers\MyHelper;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Validator;

	class UtilityController extends Controller
	{
		public function processFileUploadForSummarization(Request $request)
		{
			$validator = Validator::make($request->all(), [
				'file' => 'required|file|mimes:txt,pdf,docx|max:10240', // Max 10MB, adjust as needed
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
				// Limit text length to avoid overly long prompts (e.g., 30k chars)
				$maxLength = 30000;
				if (mb_strlen($extractedText) > $maxLength) {
					$extractedText = mb_substr($extractedText, 0, $maxLength) . "\n\n[Content truncated due to length]";
					Log::info("Uploaded file content truncated for summarization.", ['original_length' => mb_strlen($extractedText)]);
				}


				return response()->json(['success' => true, 'text' => $extractedText]);

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

				// Limit text length
				$maxLength = 30000;
				if (mb_strlen($extractedText) > $maxLength) {
					$extractedText = mb_substr($extractedText, 0, $maxLength) . "\n\n[Content truncated due to length]";
					Log::info("URL content truncated for summarization.", ['url' => $url, 'original_length' => mb_strlen($extractedText)]);
				}

				return response()->json(['success' => true, 'text' => $extractedText]);

			} catch (\Exception $e) {
				Log::error('URL summarization processing error: ' . $e->getMessage());
				return response()->json(['success' => false, 'error' => 'Failed to process URL: ' . $e->getMessage()], 500);
			}
		}
	}
