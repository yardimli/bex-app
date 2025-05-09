<?php

	namespace App\Http\Controllers\Api;

	use App\Http\Controllers\Controller;
	use App\Models\Note;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Gate; // For authorization (optional for now)

	class NoteController extends Controller
	{
		/**
		 * Display a listing of the user's notes.
		 */
		public function index(Request $request)
		{
			$user = $request->user();
			if (!$user) {
				return response()->json(['error' => 'Unauthenticated.'], 401);
			}
			$notes = $user->notes()->orderBy('updated_at', 'desc')->get();
			return response()->json($notes);
		}

		/**
		 * Store a newly created note in storage.
		 */
		public function store(Request $request)
		{
			$user = $request->user();
			if (!$user) {
				return response()->json(['error' => 'Unauthenticated.'], 401);
			}

			$validated = $request->validate([
				'title' => 'required|string|max:255',
				'content' => 'nullable|string|max:65535', // Max TEXT size
			]);

			$note = $user->notes()->create($validated);

			return response()->json($note, 201);
		}

		/**
		 * Display the specified note.
		 * (Optional, can be useful if index doesn't return full content)
		 */
		public function show(Request $request, Note $note)
		{
			$user = $request->user();
			if (!$user || $note->user_id !== $user->id) {
				return response()->json(['error' => 'Forbidden or Not Found'], 403);
			}
			return response()->json($note);
		}


		/**
		 * Update the specified note in storage.
		 */
		public function update(Request $request, Note $note)
		{
			$user = $request->user();
			if (!$user || $note->user_id !== $user->id) {
				// Using Gate for authorization would be cleaner: if (Gate::denies('update', $note))
				return response()->json(['error' => 'Forbidden'], 403);
			}

			$validated = $request->validate([
				'title' => 'sometimes|required|string|max:255',
				'content' => 'nullable|string|max:65535',
			]);

			$note->update($validated);

			return response()->json($note);
		}

		/**
		 * Remove the specified note from storage.
		 */
		public function destroy(Request $request, Note $note)
		{
			$user = $request->user();
			if (!$user || $note->user_id !== $user->id) {
				// Using Gate: if (Gate::denies('delete', $note))
				return response()->json(['error' => 'Forbidden'], 403);
			}

			$note->delete();

			return response()->json(['success' => true, 'message' => 'Note deleted.']);
		}
	}
