<?php

namespace App\Http\Controllers;

use App\Helpers\MyHelper;
use App\Models\File; // <-- Required for the File model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Required for Auth::user()
use Illuminate\Support\Facades\Cache; // <-- FIX: Required for the Cache facade
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
            session()->save();

            return response()->json([
                'success' => true,
                'context_key' => $sessionKey,
                'text_preview' => Str::limit($extractedText, self::MAX_TEXT_FOR_CHAT_INPUT)
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
            session()->save();

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
            'text' => 'required|string|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        try {
            $text = $request->input('text');
            $sessionKey = 'context_text_' . Str::random(16);
            $promptText = "Summarize the following text:";

            session([$sessionKey => [
                'prompt_text' => $promptText,
                'full_text' => $text
            ]]);
            session()->save();

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

    public function processFileIdForSummarization(Request $request)
    {
        $validated = $request->validate([
            'file_id' => 'required|integer|exists:files,id',
        ]);

        try {
            $user = Auth::user();
            $file = File::findOrFail($validated['file_id']);

            // --- Authorization Check ---
            $isOwner = $file->user_id === $user->id;
            $userTeamIds = $user->teams()->pluck('teams.id');
            $isSharedWithTeam = $file->sharedWithTeams->pluck('id')->intersect($userTeamIds)->isNotEmpty();

            if (!$isOwner && !$isSharedWithTeam) {
                return response()->json(['success' => false, 'error' => 'You do not have permission to access this file.'], 403);
            }
            // --- End Authorization Check ---

            $text = MyHelper::extractTextFromFile($file);

            if (empty(trim($text))) {
                return response()->json(['success' => false, 'error' => 'The file appears to be empty or contains no extractable text.'], 422);
            }

            $context = [
                'type' => 'file',
                'source' => $file->original_filename,
                'content' => $text,
                'prompt' => 'Summarize this document.',
            ];

            $key = 'summarize_context_' . Str::random(40);
            Cache::put($key, $context, now()->addMinutes(10));

            return response()->json(['success' => true, 'context_key' => $key]);
        } catch (\Exception $e) {
            Log::error("Error processing file ID for summarization: " . $e->getMessage(), ['file_id' => $validated['file_id']]);
            return response()->json(['success' => false, 'error' => 'An internal error occurred while processing the file.'], 500);
        }
    }

    public function transcribeAudio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Common audio and video formats. Adjust max size as needed.
            'file' => 'required|file|mimes:mp3,mp4,mpeg,mpga,m4a,wav,webm|max:25600', // 25MB limit
            'language' => 'nullable|string|size:2', // ISO-639-1 format
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        try {
            $file = $request->file('file');
            $language = $request->input('language', 'en');

            $result = MyHelper::transcribeAudio($file);

            if (!$result['success']) {
                // The helper function provides the error message
                return response()->json($result, 500);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Audio transcription processing error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to process audio file: ' . $e->getMessage()], 500);
        }
    }
}
