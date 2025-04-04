<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\View\View; // <-- Import View
	use Illuminate\Http\RedirectResponse; // <-- Import RedirectResponse

	class ProfileController extends Controller
	{
		/**
		 * Show the form for editing the user's profile.
		 */
		public function edit(Request $request): View // <-- Type hint return
		{
			// Pass the authenticated user to the view
			return view('profile.edit', [
				'user' => $request->user(),
			]);
		}

		/**
		 * Update the user's profile information.
		 */
		public function update(Request $request): RedirectResponse // <-- Type hint return
		{
			$user = $request->user();

			// Validate the request data (add your specific rules)
			$validated = $request->validate([
				'name' => ['required', 'string', 'max:255'],
				'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id], // Ensure email is unique except for the current user
				// Add password validation if you allow password changes here
				// 'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
			]);

			// Update user data
			$user->name = $validated['name'];
			$user->email = $validated['email'];

			// Handle email verification status change if email was updated
			if ($user->isDirty('email')) {
				$user->email_verified_at = null; // Reset verification if email changes (if you use verification)
			}

			$user->save();

			// Redirect back to the profile edit page with a success message
			return redirect()->route('profile.edit')->with('status', 'profile-updated');
		}
	}
