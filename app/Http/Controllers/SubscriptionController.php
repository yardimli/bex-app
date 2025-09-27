<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	// MODIFIED: Removed unused exception class.
	// use Laravel\Cashier\Exceptions\IncompletePayment;
	// ADDED: Import Stripe's specific API exception for better error handling.
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
			// MODIFIED: Validation now checks for billing_cycle instead of a complex plan name.
			$validated = $request->validate([
				'billing_cycle' => 'required|string|in:monthly,yearly',
				'quantity' => 'required|integer|min:1|max:100',
			]);

			// MODIFIED: Logic to select the single correct tiered price ID based on the billing cycle.
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
				// The core Cashier logic remains the same. This creates a Stripe Checkout
				// session for a new subscription with a specific quantity.
				$checkout = $user->newSubscription('default', $planId)
					->quantity($validated['quantity'])
					->checkout([
						'success_url' => route('subscribe.success') . '?session_id={CHECKOUT_SESSION_ID}',
						'cancel_url' => route('subscribe.cancel'),
					]);

				return redirect($checkout->url);
			} catch (ApiErrorException $e) {
				// MODIFIED: Catch specific Stripe API errors for better feedback.
				// This error is often caused by a misconfiguration in the Stripe Dashboard.
				// For example, if the Price ID is for a 'tiered' billing scheme, Stripe's API
				// will reject the 'quantity' parameter used by Cashier's quantity() method.
				// The Price should be configured with a 'per_unit' billing scheme in Stripe to support quantities.
				\Illuminate\Support\Facades\Log::error('Stripe API Error during checkout: ' . $e->getMessage());
				return back()->with('error', 'There was a configuration problem with the payment provider. Please contact support.');

			} catch (\Exception $e) {
				// MODIFIED: This is a general fallback for other unexpected errors.
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
	}
