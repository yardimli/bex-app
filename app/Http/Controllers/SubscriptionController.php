<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Stripe\Exception\ApiErrorException;


	/**
	 * NEW: Controller to handle subscription management with Stripe.
	 */
	class SubscriptionController extends Controller
	{
		/**
		 * Display the subscription plans page.
		 */
		public function index()
		{
			return view('pages.subscribe');
		}

		/**
		 * Create a Stripe Checkout session for the selected plan.
		 */
		public function checkout(Request $request)
		{
			$validated = $request->validate([
				'billing_cycle' => 'required|string|in:monthly,yearly',
				'quantity' => 'required|integer|min:1|max:100',
			]);

			$planId = null;
			if ($validated['billing_cycle'] === 'monthly') {
				$planId = config('services.stripe.tiered_monthly_price_id');
			} else {
				$planId = config('services.stripe.tiered_yearly_price_id');
			}

			if (!$planId) {
				return back()->with('error', 'The selected pricing plan is not configured.');
			}

			$user = Auth::user();

			try {
				// MODIFIED: The checkout creation logic has been changed to support
				// Stripe Prices that use a 'tiered' or 'volume' billing scheme.
				// Cashier's ->quantity() method is only for 'per_unit' pricing and
				// causes an error with tiered prices. By building the 'line_items'
				// array manually, we create a checkout session that is compatible
				// with the volume pricing model defined in the Stripe dashboard.
				$checkout = $user->newSubscription('default') // The price is now defined in the checkout options.
				->checkout([
					'success_url' => route('subscribe.success') . '?session_id={CHECKOUT_SESSION_ID}',
					'cancel_url' => route('subscribe.cancel'),
					'line_items' => [
						[
							'price' => $planId,
							'quantity' => $validated['quantity'],
						],
					],
				]);

				return redirect($checkout->url);
			} catch (ApiErrorException $e) {
				// This error is often caused by a misconfiguration between the code and the Stripe Dashboard.
				// While the code is now fixed, this catch block is kept for future debugging.
				\Illuminate\Support\Facades\Log::error('Stripe API Error during checkout: ' . $e->getMessage());
				return back()->with('error', 'There was a configuration problem with the payment provider. Please contact support.');

			} catch (\Exception $e) {
				// This is a general fallback for other unexpected errors.
				\Illuminate\Support\Facades\Log::error('Generic Stripe Checkout Error: ' . $e->getMessage());
				return back()->with('error', 'Could not process your subscription. Please try again or contact support.');
			}
		}

		/**
		 * Handle successful payment redirection from Stripe.
		 */
		public function success(Request $request)
		{
			return redirect()->route('home')->with('success', 'Thank you for subscribing!');
		}

		/**
		 * Handle cancelled payment redirection from Stripe.
		 */
		public function cancel()
		{
			return redirect()->route('subscribe.index')->with('error', 'Your subscription process was cancelled.');
		}

		/**
		 * Redirect to the Stripe Customer Portal.
		 */
		public function portal()
		{
			return Auth::user()->redirectToBillingPortal(route('home'));
		}

		/**
		 * NEW: Display the subscription management page.
		 */
		public function manage()
		{
			$user = Auth::user();
			$subscription = $user->subscription('default');

			// Get all teams the user is a member of.
			$userTeams = $user->teams()->get();
			// Get the ID of the currently active team from the session.
			$currentTeamId = session('current_team_id');

			return view('pages.subscription.manage', [
				'userTeams' => $userTeams,
				'currentTeamId' => $currentTeamId,
				'subscription' => $subscription,
			]);
		}

		/**
		 * NEW: Cancel the user's active subscription.
		 */
		public function cancelSubscription(Request $request)
		{
			$user = Auth::user();
			$user->subscription('default')->cancel();

			return redirect()->route('subscription.manage')->with('success', 'Your subscription has been cancelled and will end on your next billing date.');
		}

		/**
		 * NEW: Resume a cancelled subscription.
		 */
		public function resumeSubscription(Request $request)
		{
			$user = Auth::user();
			$user->subscription('default')->resume();

			return redirect()->route('subscription.manage')->with('success', 'Your subscription has been resumed.');
		}
	}
