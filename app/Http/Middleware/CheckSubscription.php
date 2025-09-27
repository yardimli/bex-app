<?php

	namespace App\Http\Middleware;

	use Closure;
	use Illuminate\Http\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Illuminate\Support\Facades\Auth;

	/**
	 * NEW: Middleware to verify if the authenticated user has an active subscription.
	 */
	class CheckSubscription
	{
		/**
		 * Handle an incoming request.
		 *
		 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
		 */
		public function handle(Request $request, Closure $next): Response
		{
			$user = Auth::user();

			// Check if the user is authenticated and does not have an active subscription.
			// The 'default' name refers to the subscription name we use in the SubscriptionController.
			if ($user && !$user->subscribed('default')) {
				// If they are trying to access the subscription page, let them.
				if ($request->routeIs('subscribe.*')) {
					return $next($request);
				}
				// Otherwise, redirect them to the subscription page.
				return redirect()->route('subscribe.index')->with('error', 'You need an active subscription to access this page.');
			}

			return $next($request);
		}
	}
