<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    const MAX_FILE_SIZE_KB = 10240; // 10MB

    /**
     * List the authenticated user's uploaded files.
     */
    public function userFiles()
    {
        $user = Auth::user();
        $files = $user->files()->with('sharedWithTeams:id,name')->orderBy('created_at', 'desc')->get();
        return response()->json($files);
    }

    /**
     * List files shared with a specific team.
     */
    public function teamFiles(Team $team)
    {
        $user = Auth::user();
        if (!$user->teams->contains($team)) {
            return response()->json(['error' => 'FORBIDDEN', 'message' => 'You are not a member of this team.'], 403);
        }
        // Order by your specific pivot timestamp
        $files = $team->sharedFiles()->with('owner:id,name')->orderBy('pivot_shared_at', 'desc')->get();
        return response()->json($files);
    }

    /**
     * Store a new file.
     */
    public function upload(Request $request)
    {
        try {
            $validated = $request->validate([
                'file' => [
                    'required',
                    'file',
                    'mimes:txt,doc,docx,pdf,jpg,jpeg,png',
                    'max:' . self::MAX_FILE_SIZE_KB,
                ],
            ]);
        } catch (ValidationException $e) {
            // ... (error handling is fine)
            $errors = $e->errors();
            if (isset($errors['file'])) {
                if (str_contains($errors['file'][0], 'size')) {
                    return response()->json(['error' => 'FILE_TOO_LARGE', 'message' => 'File must not be greater than ' . (self::MAX_FILE_SIZE_KB / 1024) . 'MB.'], 422);
                }
                if (str_contains($errors['file'][0], 'mime')) {
                    return response()->json(['error' => 'INVALID_FILE_TYPE', 'message' => 'Invalid file type. Allowed types: txt, doc, docx, pdf, jpg, jpeg, png.'], 422);
                }
            }
            return response()->json(['error' => 'VALIDATION_ERROR', 'message' => $e->getMessage(), 'errors' => $errors], 422);
        }

        $user = Auth::user();
        $file = $validated['file'];
        // Use a unique ID instead of UUID for the stored name
        $storedName = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('files/' . $user->id, $storedName);

        // Use your database column names
        $newFile = $user->files()->create([
            'original_filename' => $file->getClientOriginalName(),
            'filename'          => $storedName,
            'path'              => $path,
            'mime_type'         => $file->getMimeType(),
            'size'              => $file->getSize(),
        ]);

        $newFile->load('sharedWithTeams:id,name');
        return response()->json($newFile, 201);
    }

    /**
     * Share a file with one or more teams.
     */
    public function share(Request $request, File $file)
    {
        if (Auth::id() !== $file->user_id) {
            return response()->json(['error' => 'FORBIDDEN', 'message' => 'You do not own this file.'], 403);
        }

        $validated = $request->validate([
            'team_ids' => 'required|array',
            'team_ids.*' => 'integer|exists:teams,id',
        ]);

        $userTeams = Auth::user()->teams()->pluck('teams.id')->all();
        $teamsToShare = $validated['team_ids'];

        if (count(array_diff($teamsToShare, $userTeams)) > 0) {
            return response()->json(['error' => 'FORBIDDEN', 'message' => 'You can only share files with teams you are a member of.'], 403);
        }

        // Prepare data for sync, including the extra pivot column 'shared_by'
        $syncData = collect($teamsToShare)->mapWithKeys(function ($teamId) {
            return [$teamId => ['shared_by' => Auth::id(), 'shared_at' => now()]];
        });

        $file->sharedWithTeams()->sync($syncData);
        $file->load('sharedWithTeams:id,name');
        return response()->json($file);
    }

    /**
     * Revoke a file's access from a team.
     */
    public function revokeShare(File $file, Team $team)
    {
        if (Auth::id() !== $file->user_id) {
            return response()->json(['error' => 'FORBIDDEN', 'message' => 'You do not own this file.'], 403);
        }

        $file->sharedWithTeams()->detach($team->id);
        return response()->json(['success' => true]);
    }

    /**
     * Download a file.
     */
    public function download(File $file)
    {
        $user = Auth::user();
        $isOwner = $file->user_id === $user->id;
        $isSharedWithUserTeam = $file->sharedWithTeams()->whereIn('teams.id', $user->teams()->pluck('teams.id'))->exists();

        if (!$isOwner && !$isSharedWithUserTeam) {
            return response()->json(['error' => 'FORBIDDEN', 'message' => 'You do not have access to this file.'], 403);
        }

        $filePath = storage_path('app/' . $file->path);
        if (!Storage::exists($file->path)) {
            Log::error("File not found on disk for download.", ['file_id' => $file->id, 'path' => $filePath]);
            return response()->json(['error' => 'NOT_FOUND', 'message' => 'File not found on the server.'], 404);
        }

        // Use your 'original_filename' column
        return response()->download($filePath, $file->original_filename);
    }
}
