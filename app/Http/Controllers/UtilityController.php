<?php

namespace App\Http\Controllers;

use App\Helpers\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UtilityController extends Controller
{
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
            $sessionKey = 'context_text_' . Str::random(16);
            $promptText = "Summarize the content of the file [[ {$fileName} ]]";
            session([$sessionKey => [
                'prompt_text' => $promptText,
                'full_text' => $extractedText
            ]]);

            session()->save(); // ADDED: Force session save before responding

            return response()->json([
                'success' => true,
                'context_key' => $sessionKey, // Client will use this
                'text_preview' => Str::limit($extractedText, self::MAX_TEXT_FOR_CHAT_INPUT) // For immediate display if needed
            ]);

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

            $sessionKey = 'context_text_' . Str::random(16);
            $promptText = "Summarize the content of this webpage ({$url}):\n\n";
            session([$sessionKey => [
                'prompt_text' => $promptText,
                'full_text' => $extractedText
            ]]);


            session()->save(); // ADDED: Force session save before responding

            return response()->json([
                'success' => true,
                'context_key' => $sessionKey,
                'text_preview' => Str::limit($extractedText, self::MAX_TEXT_FOR_CHAT_INPUT)
            ]);

        } catch (\Exception $e) {
            Log::error('URL summarization processing error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to process URL: ' . $e->getMessage()], 500);
        }
    }

    public function processTextForSummarization(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:100000', // A reasonable max length
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        try {
            $text = $request->input('text');

            $sessionKey = 'context_text_' . Str::random(16);
            $promptText = "Summarize the following text:";

            // MODIFIED: Store an array in the session
            session([$sessionKey => [
                'prompt_text' => $promptText,
                'full_text' => $text
            ]]);

            session()->save(); // ADDED: Force session save before responding

            return response()->json([
                'success' => true,
                'context_key' => $sessionKey,
                'text_preview' => Str::limit($text, self::MAX_TEXT_FOR_CHAT_INPUT)
            ]);

        } catch (\Exception $e) {
            Log::error('Text summarization processing error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to process text: ' . $e->getMessage()], 500);
        }
    }
}
