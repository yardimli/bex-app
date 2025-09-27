<?php

	namespace App\Http\Middleware;

	use Closure;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Symfony\Component\HttpFoundation\Response;

	/**
	 * NEW: Middleware to grant access to subscribed users or members of a subscribed team.
	 */
	class CheckSubscriptionOrTeamMembership
	{
		/**
		 * Handle an incoming request.
		 *
		 * This middleware checks two conditions:
		 * 1. If the currently authenticated user has an active subscription.
		 * 2. If not, it checks if the user is a member of any team whose owner has an active subscription.
		 *
		 * If either condition is met, the request is allowed to proceed. Otherwise, the user is
		 * redirected to the subscription page.
		 *
		 * @param \Illuminate\Http\Request $request The incoming HTTP request.
		 * @param \Closure $next The next middleware in the pipeline.
		 * @return \Symfony\Component\HttpFoundation\Response
		 */
		public function handle(Request $request, Closure $next): Response
		{
			$user = Auth::user();

			// First, check if the user is authenticated.
			if (!$user) {
				return redirect()->route('login');
			}

			// 1. Check if the user has a personal subscription.
			// The 'default' is the name of the subscription plan in this application.
			if ($user->subscribed('default')) {
				return $next($request);
			}

			// 2. If no personal subscription, check if the user is part of a team
			// where the owner has an active subscription.
			// Eager load the owner and their subscriptions to avoid N+1 query problems.
			$teams = $user->teams()->with('owner.subscriptions')->get();

			foreach ($teams as $team) {
				// Check if the team owner exists and has an active 'default' subscription.
				if ($team->owner && $team->owner->subscribed('default')) {
					return $next($request);
				}
			}

			// 3. If neither the user nor any of their team owners are subscribed,
			// redirect them to the subscription page with an informative error message.
			return redirect()->route('subscribe.index')->with('error', 'An active subscription is required to access this content.');
		}
	}
