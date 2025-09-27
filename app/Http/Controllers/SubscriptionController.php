<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Laravel\Cashier\Exceptions\IncompletePayment;

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
				'plan' => 'required|string|in:individual_monthly,individual_yearly,team_monthly,team_yearly',
				'quantity' => 'required|integer|min:1|max:100',
			]);

			$planId = config('services.stripe.' . $validated['plan'] . '_price_id');
			if (!$planId) {
				return back()->with('error', 'The selected pricing plan is not configured.');
			}

			$user = Auth::user();

			try {
				$checkout = $user->newSubscription('default', $planId)
					->quantity($validated['quantity'])
					->checkout([
						'success_url' => route('subscribe.success') . '?session_id={CHECKOUT_SESSION_ID}',
						'cancel_url' => route('subscribe.cancel'),
					]);

				return redirect($checkout->url);
			} catch (\Exception $e) {
				return back()->with('error', 'Could not process your subscription. ' . $e->getMessage());
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
	}
