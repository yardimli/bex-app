@extends('layouts.app')

@section('content')
	<div class="p-4 flex flex-col h-full gap-4 items-center justify-center">
		<div class="card bg-base-100 shadow-xl w-full max-w-4xl">
			<div class="card-body">
				<div class="text-center">
					<h2 class="card-title justify-center text-3xl mb-2">Choose Your Plan</h2>
					<p class="text-base-content/70">You're almost there! Select a plan to unlock all of Bex's features.</p>
					
					{{-- Error/Success Alerts --}}
					@if (session('error'))
						<div role="alert" class="alert alert-error my-4">
							<i class="bi bi-x-circle-fill"></i>
							<span>{{ session('error') }}</span>
						</div>
					@endif
					@if (session('success'))
						<div role="alert" class="alert alert-success my-4">
							<i class="bi bi-check-circle-fill"></i>
							<span>{{ session('success') }}</span>
						</div>
					@endif
				</div>
				
				<form action="{{ route('subscribe.checkout') }}" method="POST" id="subscription-form">
					@csrf
					<input type="hidden" name="plan" id="plan-input" value="individual_monthly">
					<input type="hidden" name="quantity" id="quantity-input" value="1">
					
					<div class="text-center my-6">
						<span class="font-semibold mr-4">Bill Monthly</span>
						<input type="checkbox" class="toggle toggle-primary" id="billing-toggle" />
						<span class="ml-4 font-semibold">Bill Yearly (Save 28%)</span>
					</div>
					
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						{{-- Individual Plan --}}
						<div class="card bg-base-200 cursor-pointer plan-card border-2 border-primary" data-plan-monthly="individual_monthly" data-plan-yearly="individual_yearly" data-quantity="1">
							<div class="card-body">
								<h3 class="card-title text-2xl">Individual</h3>
								<p>For solo power users.</p>
								<div class="my-4 text-center">
									<span class="text-5xl font-extrabold price" data-price-monthly="6.99" data-price-yearly="4.99">$6.99</span>
									<span class="text-xl period">/ month</span>
								</div>
							</div>
						</div>
						
						{{-- Team Plan --}}
						<div class="card bg-base-200 cursor-pointer plan-card border-2 border-transparent" data-plan-monthly="team_monthly" data-plan-yearly="team_yearly">
							<div class="card-body">
								<h3 class="card-title text-2xl">Team</h3>
								<p>For collaborative teams.</p>
								<div class="my-4 text-center">
									<span class="text-5xl font-extrabold price" data-price-monthly="6.49" data-price-yearly="4.49">$6.49</span>
									<span class="text-xl period">/ user / month</span>
								</div>
								<div class="mt-4">
									<label for="team-slider" class="label">Team Members: <span class="font-bold team-size-label">2</span></label>
									<input type="range" min="2" max="100" value="2" class="range range-primary team-slider" />
								</div>
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
			const billingToggle = document.getElementById('billing-toggle');
			const planCards = document.querySelectorAll('.plan-card');
			const planInput = document.getElementById('plan-input');
			const quantityInput = document.getElementById('quantity-input');
			const teamSlider = document.querySelector('.team-slider');
			const teamSizeLabel = document.querySelector('.team-size-label');
			
			function updatePrices() {
				const isYearly = billingToggle.checked;
				
				planCards.forEach(card => {
					const priceEl = card.querySelector('.price');
					const periodEl = card.querySelector('.period');
					const monthlyPrice = parseFloat(priceEl.dataset.priceMonthly);
					const yearlyPrice = parseFloat(priceEl.dataset.priceYearly);
					
					if (isYearly) {
						priceEl.textContent = `$${yearlyPrice.toFixed(2)}`;
						periodEl.textContent = card.dataset.quantity ? '/ month' : '/ user / month';
					} else {
						priceEl.textContent = `$${monthlyPrice.toFixed(2)}`;
						periodEl.textContent = card.dataset.quantity ? '/ month' : '/ user / month';
					}
				});
			}
			
			function selectPlan(selectedCard) {
				const isYearly = billingToggle.checked;
				planCards.forEach(card => card.classList.remove('border-primary', 'border-transparent'));
				selectedCard.classList.add('border-primary');
				
				const planName = isYearly ? selectedCard.dataset.planYearly : selectedCard.dataset.planMonthly;
				planInput.value = planName;
				
				let quantity = 1;
				if (selectedCard.dataset.planMonthly.startsWith('team')) {
					quantity = teamSlider.value;
				}
				quantityInput.value = quantity;
			}
			
			planCards.forEach(card => {
				card.addEventListener('click', () => selectPlan(card));
			});
			
			billingToggle.addEventListener('change', () => {
				updatePrices();
				// Reselect the current plan to update the hidden input value
				const selectedCard = document.querySelector('.plan-card.border-primary');
				if (selectedCard) {
					selectPlan(selectedCard);
				}
			});
			
			teamSlider.addEventListener('input', () => {
				const teamSize = teamSlider.value;
				teamSizeLabel.textContent = teamSize;
				quantityInput.value = teamSize;
				
				// Auto-select team plan when slider is used
				const teamCard = document.querySelector('[data-plan-monthly="team_monthly"]');
				selectPlan(teamCard);
			});
			
			// Initial setup
			updatePrices();
			selectPlan(document.querySelector('.plan-card')); // Select individual plan by default
		});
	</script>
@endpush
