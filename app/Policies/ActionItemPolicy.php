<?php

	namespace App\Policies;

	use App\Models\ActionItem;
	use App\Models\User;
	use Illuminate\Auth\Access\Response;

	class ActionItemPolicy
	{
		/**
		 * Determine whether the user can update the model.
		 */
		public function update(User $user, ActionItem $actionItem): bool
		{
			return $user->id === $actionItem->user_id;
		}

		/**
		 * Determine whether the user can delete the model.
		 */
		public function delete(User $user, ActionItem $actionItem): bool
		{
			return $user->id === $actionItem->user_id;
		}

		// Add other policy methods (viewAny, view, create, restore, forceDelete) if needed
	}
