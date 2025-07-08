<?php

	namespace App\Http\Controllers\Api;

	use App\Http\Controllers\Controller;
	use App\Models\Message;
	use App\Models\MessageRecipient;
	use App\Models\Team;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Validation\Rule;

	class MessageController extends Controller
	{
		public function sendMessage(Request $request, Team $team)
		{
			$sender = Auth::user();

			// Ensure the sender is a member of the team
			if (!$sender->teams()->where('team_id', $team->id)->exists()) {
				return response()->json(['error' => 'You are not a member of this team.'], 403);
			}

			$validated = $request->validate([
				'recipient_ids' => 'required|array|min:1',
				'recipient_ids.*' => [
					'integer',
					// Rule to ensure every recipient is a member of the specified team
					Rule::exists('team_members', 'user_id')->where(function ($query) use ($team) {
						return $query->where('team_id', $team->id);
					}),
				],
				'subject' => 'required|string|max:255',
				'body' => 'required|string',
			]);

			$message = DB::transaction(function () use ($sender, $team, $validated) {
				$message = $team->messages()->create([
					'sender_id' => $sender->id,
					'subject' => $validated['subject'],
					'body' => $validated['body'],
				]);

				foreach ($validated['recipient_ids'] as $recipientId) {
					$message->recipients()->create(['recipient_id' => $recipientId]);
				}

				return $message;
			});

			return response()->json($message->load('recipients.recipient'), 201);
		}

		/**
		 * Display a single message.
		 *
		 * @param  \App\Models\Message $message
		 * @return \Illuminate\Http\JsonResponse
		 */
		public function show(Message $message)
		{
			$user = Auth::user();

			// Security check: Ensure the user is a recipient of the message.
			// This endpoint is called from the inbox, so the user must be a recipient.
			$isRecipient = $message->recipients()->where('recipient_id', $user->id)->exists();

			if (!$isRecipient) {
				return response()->json(['error' => 'Unauthorized to view this message.'], 403);
			}

			// Eager load the relationships required by the frontend modal.
			$message->load(['sender', 'team']);

			return response()->json($message);
		}

		public function inbox(Request $request)
		{
			$user = Auth::user();

			$query = $user->receivedMessages()
				->with(['message.sender', 'message.team'])
				->join('messages', 'message_recipients.message_id', '=', 'messages.id')
				->orderBy('messages.created_at', 'desc');

			if ($request->filled('team_id')) {
				$query->whereHas('message', function ($q) use ($request) {
					$q->where('team_id', $request->team_id);
				});
			}

			if ($request->boolean('unread')) {
				$query->whereNull('read_at');
			}

			$inbox = $query->select('message_recipients.*')->paginate(15);

			return response()->json($inbox);
		}

		public function markAsRead(Message $message)
		{
			$recipientRecord = MessageRecipient::where('message_id', $message->id)
				->where('recipient_id', Auth::id())
				->first();

			if (!$recipientRecord) {
				return response()->json(['error' => 'Message not found in your inbox.'], 404);
			}

			if (!$recipientRecord->read_at) {
				$recipientRecord->update(['read_at' => now()]);
			}

			return response()->json(['success' => true, 'read_at' => $recipientRecord->read_at]);
		}

		public function unreadCount()
		{
			$count = Auth::user()->receivedMessages()->whereNull('read_at')->count();
			return response()->json(['unread_count' => $count]);
		}

		public function sent(Request $request)
		{
			$user = Auth::user();

			$query = $user->sentMessages()
				->with(['team', 'recipients.recipient']) // Eager load relationships
				->orderBy('created_at', 'desc');

			if ($request->filled('team_id')) {
				$query->where('team_id', $request->team_id);
			}

			$sentMessages = $query->paginate(15);

			return response()->json($sentMessages);
		}
	}
