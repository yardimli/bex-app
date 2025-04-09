<?php

	namespace App\Http\Controllers\Api;

	use App\Http\Controllers\Controller;
	use Illuminate\Http\Request;
	use App\Models\ActionItem;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Gate; // For authorization

	class ActionItemController extends Controller
	{
		/**
		 * Display a listing of the user's action items.
		 */
		public function index(Request $request)
		{
			$user = $request->user(); // Get authenticated user
			if (!$user) {
				return response()->json(['error' => 'Unauthenticated.'], 401);
			}
			$actionItems = $user->actionItems()->orderBy('is_done', 'asc')->orderBy('created_at', 'asc')->get();
			return response()->json($actionItems);
		}

		/**
		 * Store a newly created action item in storage.
		 */
		public function store(Request $request)
		{
			$user = $request->user();
			if (!$user) {
				return response()->json(['error' => 'Unauthenticated.'], 401);
			}

			$validated = $request->validate([
				'content' => 'required|string|max:1000',
				// Add validation for due_date if you implement it
			]);

			$actionItem = $user->actionItems()->create([
				'content' => $validated['content'],
				'is_done' => false,
				// 'due_date' => $validated['due_date'] ?? null,
			]);

			return response()->json($actionItem, 201); // Return created item with 201 status
		}


		/**
		 * Update the specified action item (toggle done status).
		 */
		public function update(Request $request, ActionItem $actionItem)
		{
			// Basic Authorization: Ensure the logged-in user owns the item
			if (Gate::denies('update', $actionItem)) {
				return response()->json(['error' => 'Forbidden'], 403);
			}

			$validated = $request->validate([
				'is_done' => 'required|boolean',
			]);

			$actionItem->update(['is_done' => $validated['is_done']]);

			return response()->json($actionItem);
		}

		/**
		 * Remove the specified action item from storage.
		 */
		public function destroy(ActionItem $actionItem)
		{
			// Basic Authorization: Ensure the logged-in user owns the item
			if (Gate::denies('delete', $actionItem)) {
				return response()->json(['error' => 'Forbidden'], 403);
			}

			$actionItem->delete();

			return response()->json(['success' => true, 'message' => 'Action item deleted.']);
		}
	}
