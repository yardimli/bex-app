<?php

	namespace App\Http\Controllers;
    use App\Helpers\MyHelper;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;

	class AppController extends Controller
	{
		public function index()
		{
			$user = Auth::user();
			if ($user) {
				$chatHeaders = $user->chatHeaders()->orderBy('updated_at', 'desc')->get();
                $userTeams = $user->teams()->get();
                $currentTeamId = session('current_team_id');
                $llms = MyHelper::getLlmList();

                return view('pages.dashboard', [
					'chatHeaders' => $chatHeaders,
					'activeChat' => null, // Pass the active chat object (or null)
					'messages' => null,
                    'userTeams' => $userTeams,
                    'currentTeamId' => $currentTeamId,
                    'llms' => $llms,
				]);

			} else {
				// You might pass initial data here later if needed
				return view('pages.dashboard');
			}
		}

		// --- Placeholder AJAX endpoints (Add these later if needed) ---
		// public function getMeetingDetails(Request $request, $meetingId) { /* Fetch data */ return response()->json(/* data */); }
		// public function getNoteDetails(Request $request, $noteId) { /* Fetch data */ return response()->json(/* data */); }
		// public function getFileDetails(Request $request, $fileId) { /* Fetch data */ return response()->json(/* data */); }
		// public function getRecordingDetails(Request $request, $recordingId) { /* Fetch data */ return response()->json(/* data */); }
		// public function getActionItems(Request $request) { /* Fetch data */ return response()->json(/* data */); }
		// public function addActionItem(Request $request) { /* Save data */ return response()->json(['success' => true]); }
		// public function updateSettings(Request $request) { /* Save data */ return response()->json(['success' => true]); }
		// public function summarize(Request $request) { /* Process summarization */ return response()->json(/* summary */); }
		// public function transcribe(Request $request) { /* Process transcription */ return response()->json(/* transcript */); }
	}
