@extends('layouts.app')

@section('content')
	<div class="p-4 flex flex-col h-full gap-4 items-center justify-center">
		<div class="card bg-base-100 shadow-xl w-full max-w-2xl">
			<div class="card-body">
				<div class="text-center">
					<h2 class="card-title justify-center text-3xl mb-2">Choose Your Plan</h2>
					<p class="text-base-content/70">Select the number of users and your preferred billing cycle.</p>
					
					@if (session('error'))
						<div role="alert" class="alert alert-error my-4"><i class="bi bi-x-circle-fill"></i><span>{{ session('error') }}</span></div>
					@endif
				</div>
				
				{{-- MODIFIED: Simplified form structure --}}
				<form action="{{ route('subscribe.checkout') }}" method="POST" id="subscription-form">
					@csrf
					<input type="hidden" name="billing_cycle" id="billing-cycle-input" value="monthly">
					<input type="hidden" name="quantity" id="quantity-input" value="1">
					
					<div class="text-center my-6">
						<span class="font-semibold mr-4">Bill Monthly</span>
						<input type="checkbox" class="toggle toggle-primary" id="billing-toggle" />
						<span class="ml-4 font-semibold">Bill Yearly (Save up to 30%)</span>
					</div>
					
					{{-- MODIFIED: A single, unified pricing card --}}
					<div class="card bg-base-200 border-2 border-primary">
						<div class="card-body">
							<h3 class="card-title text-2xl" id="plan-title">Individual Plan</h3>
							<p id="plan-description">For solo power users.</p>
							
							<div class="mt-4">
								<label for="team-slider" class="label">Number of Users: <span class="font-bold" id="quantity-label">1</span></label>
								<input type="range" min="1" max="100" value="1" class="range range-primary" id="quantity-slider" />
							</div>
							
							<div class="my-4 text-center">
								<p class="text-xl">
									<span class="text-5xl font-extrabold" id="price-per-user">$6.99</span>
									<span id="period">/ user / month</span>
								</p>
								<p class="text-2xl font-bold mt-4">
									Total: <span id="total-price">$6.99</span> <span id="total-period">/ month</span>
								</p>
							</div>
						</div>
					</div>
					
					<div class="card-actions justify-center mt-8">
						<button type="submit" class="btn btn-primary btn-lg w-full max-w-sm">
							Proceed to Payment
						</button>
					</div>
					<div class="text-center mt-4">
						<a href="{{ route('billing.portal') }}" class="link link-hover">Already subscribed? Manage your billing here.</a>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			// --- DOM Elements ---
			const billingToggle = document.getElementById('billing-toggle');
			const quantitySlider = document.getElementById('quantity-slider');
			const billingCycleInput = document.getElementById('billing-cycle-input');
			const quantityInput = document.getElementById('quantity-input');
			
			const planTitle = document.getElementById('plan-title');
			const planDescription = document.getElementById('plan-description');
			const quantityLabel = document.getElementById('quantity-label');
			const pricePerUserEl = document.getElementById('price-per-user');
			const periodEl = document.getElementById('period');
			const totalPriceEl = document.getElementById('total-price');
			const totalPeriodEl = document.getElementById('total-period');
			
			// --- Pricing Tiers (MUST match Stripe) ---
			const monthlyTiers = {
				1: 6.99, 2: 6.49, 11: 5.99, 51: 5.49, 101: 4.99
			};
			const yearlyTiers = { // Example yearly prices
				1: 4.99, 2: 4.49, 11: 3.99, 51: 3.49, 101: 2.99
			};
			
			function getPriceForQuantity(quantity, tiers) {
				let price = 0;
				if (quantity >= 101) price = tiers[101];
				else if (quantity >= 51) price = tiers[51];
				else if (quantity >= 11) price = tiers[11];
				else if (quantity >= 2) price = tiers[2];
				else if (quantity >= 1) price = tiers[1];
				return price;
			}
			
			function updateUI() {
				const quantity = parseInt(quantitySlider.value, 10);
				const isYearly = billingToggle.checked;
				
				// 1. Determine correct price per user
				const tiers = isYearly ? yearlyTiers : monthlyTiers;
				const pricePerUser = getPriceForQuantity(quantity, tiers);
				const totalPrice = pricePerUser * quantity;
				
				// 2. Update UI Text
				quantityLabel.textContent = quantity;
				pricePerUserEl.textContent = `$${pricePerUser.toFixed(2)}`;
				totalPriceEl.textContent = `$${totalPrice.toFixed(2)}`;
				
				const billingPeriodString = isYearly ? 'year' : 'month';
				periodEl.textContent = quantity > 1 ? `/ user / ${billingPeriodString}` : `/ ${billingPeriodString}`;
				totalPeriodEl.textContent = `/ ${billingPeriodString}`;
				
				if (quantity === 1) {
					planTitle.textContent = 'Individual Plan';
					planDescription.textContent = 'For solo power users.';
				} else {
					planTitle.textContent = 'Team Plan';
					planDescription.textContent = `For your team of ${quantity}.`;
				}
				
				// 3. Update Hidden Form Inputs
				billingCycleInput.value = isYearly ? 'yearly' : 'monthly';
				quantityInput.value = quantity;
			}
			
			// --- Event Listeners ---
			billingToggle.addEventListener('change', updateUI);
			quantitySlider.addEventListener('input', updateUI);
			
			// --- Initial Load ---
			updateUI();
		});
	</script>
@endpush
