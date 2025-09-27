{{-- NEW: This file provides the subscription interface for logged-in, unsubscribed users. --}}
@extends('layouts.guest')

@section('title', 'Subscribe')

@section('content')
	<div class="hero min-h-screen bg-base-200 pt-16">
		<div class="hero-content flex-col w-full max-w-2xl">
			<div class="text-center mb-8">
				<h1 class="text-4xl font-bold">Choose Your Plan</h1>
				<p class="text-lg mt-4">You're one step away from unlocking Bex's full potential.</p>
			</div>
			
			{{-- Display Errors --}}
			@if (session('error'))
				<div role="alert" class="alert alert-error mb-4">
					<i class="bi bi-x-circle-fill"></i>
					<span>{{ session('error') }}</span>
				</div>
			@endif
			
			{{-- Subscription Form --}}
			<form id="subscription-form" method="POST" action="{{ route('subscribe.checkout') }}" class="w-full">
				@csrf
				<input type="hidden" name="billing_cycle" id="billing_cycle_input" value="monthly">
				<input type="hidden" name="quantity" id="quantity_input" value="1">
				
				<div class="card bg-base-100 shadow-xl border-2 border-primary">
					<div class="card-body">
						<div class="text-center mb-6">
							<span class="font-semibold mr-4">Bill Monthly</span>
							<input type="checkbox" class="toggle toggle-primary" id="billing-toggle" />
							<span class="ml-4 font-semibold">Bill Yearly (Save up to 30%)</span>
						</div>
						
						<div class="flex justify-between items-start">
							<div>
								<h3 class="card-title text-2xl" id="plan-title">Individual Plan</h3>
								<p id="plan-description">For solo power users.</p>
							</div>
							<div class="badge badge-primary badge-lg">POPULAR</div>
						</div>
						
						<div class="mt-4">
							<label for="quantity-slider" class="label">Number of Users: <span class="font-bold" id="quantity-label">1</span></label>
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
						
						<ul class="space-y-2 mt-4">
							<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Unlimited Personal & Group Chats</li>
							<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Document Summarization & Analysis</li>
							<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Audio Transcription</li>
							<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Shared Team Workspace (2+ users)</li>
							<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Centralized Billing (2+ users)</li>
						</ul>
						
						<div class="card-actions mt-6">
							<button type="submit" class="btn btn-primary w-full">Proceed to Checkout</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
@endsection

@push('scripts')
	{{-- NEW: Added script to handle the dynamic pricing calculator and form input updates. --}}
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			// --- DOM Elements ---
			const billingToggle = document.getElementById('billing-toggle');
			const quantitySlider = document.getElementById('quantity-slider');
			const billingCycleInput = document.getElementById('billing_cycle_input');
			const quantityInput = document.getElementById('quantity_input');
			
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
			const yearlyTiers = {
				1: 69.90, 2: 64.90, 11: 59.90, 51: 54.90, 101: 49.90
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
				
				const tiers = isYearly ? yearlyTiers : monthlyTiers;
				const pricePerUser = getPriceForQuantity(quantity, tiers);
				const totalPrice = pricePerUser * quantity;
				const billingCycle = isYearly ? 'yearly' : 'monthly';
				
				// Update hidden form inputs
				quantityInput.value = quantity;
				billingCycleInput.value = billingCycle;
				
				// Update display elements
				quantityLabel.textContent = quantity;
				pricePerUserEl.textContent = `$${pricePerUser.toFixed(2)}`;
				totalPriceEl.textContent = `$${totalPrice.toFixed(2)}`;
				
				const billingPeriodString = isYearly ? 'year' : 'month';
				totalPeriodEl.textContent = `/ ${billingPeriodString}`;
				
				if (quantity === 1) {
					planTitle.textContent = 'Individual Plan';
					planDescription.textContent = 'For solo power users.';
					periodEl.textContent = `/ ${billingPeriodString}`;
				} else {
					planTitle.textContent = 'Team Plan';
					planDescription.textContent = `For your team of ${quantity}.`;
					periodEl.textContent = `/ user / ${billingPeriodString}`;
				}
			}
			
			// --- Event Listeners ---
			billingToggle.addEventListener('change', updateUI);
			quantitySlider.addEventListener('input', updateUI);
			
			// --- Initial Load ---
			updateUI();
		});
	</script>
@endpush
