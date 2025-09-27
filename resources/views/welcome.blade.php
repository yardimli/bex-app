<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Bex - Your AI Team Assistant</title>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="dns-prefetch" href="//fonts.bunny.net">
	<link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
	<link rel="manifest" href="/images/site.webmanifest">
	@vite('resources/css/app.css')
	<style>
      .fade-in-up {
          animation: fadeInUp 1s ease-out forwards;
          opacity: 0;
          transform: translateY(20px);
      }
      @keyframes fadeInUp {
          to {
              opacity: 1;
              transform: translateY(0);
          }
      }
	</style>
</head>
<body class="bg-base-200">
<div id="app">
	{{-- Navbar --}}
	<div class="navbar bg-base-100 shadow-sm fixed top-0 z-50">
		<div class="container mx-auto">
			<div class="navbar-start">
				<a href="/" class="btn btn-ghost text-xl">
					<img src="/images/logo-bex_02-logo-bex-color.png" alt="Bex Logo" class="h-8 w-auto">
				</a>
			</div>
			<div class="navbar-end">
				<a href="{{ route('login') }}" class="btn btn-ghost">Login</a>
				<a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
			</div>
		</div>
	</div>
	
	{{-- Hero Section --}}
	<div class="hero min-h-screen bg-base-200 pt-16">
		<div class="hero-content text-center">
			<div class="max-w-2xl fade-in-up">
				<h1 class="text-5xl font-bold">The AI Assistant Built for Teams</h1>
				<p class="py-6 text-lg">Bex integrates seamlessly into your workflow, offering intelligent chat, document summarization, and collaborative tools to boost your team's productivity. Stop juggling apps and start focusing on what matters.</p>
				<a href="#pricing" class="btn btn-primary btn-lg">View Pricing</a>
			</div>
		</div>
	</div>
	
	{{-- Features Section --}}
	<div class="py-20 bg-base-100">
		<div class="container mx-auto text-center">
			<h2 class="text-4xl font-bold mb-12">Why Teams Choose Bex</h2>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-10 px-4">
				<div class="card bg-base-200 shadow-lg">
					<div class="card-body items-center text-center">
						<i class="bi bi-people-fill text-primary text-5xl mb-4"></i>
						<h3 class="card-title">Group Collaboration</h3>
						<p>Engage in intelligent group chats where Bex can be mentioned to provide information, summarize conversations, or create action items.</p>
					</div>
				</div>
				<div class="card bg-base-200 shadow-lg">
					<div class="card-body items-center text-center">
						<i class="bi bi-file-earmark-text-fill text-primary text-5xl mb-4"></i>
						<h3 class="card-title">Document Intelligence</h3>
						<p>Upload PDFs, Word documents, or text files and ask Bex questions about them. Get instant summaries and find key information in seconds.</p>
					</div>
				</div>
				<div class="card bg-base-200 shadow-lg">
					<div class="card-body items-center text-center">
						<i class="bi bi-shield-check text-primary text-5xl mb-4"></i>
						<h3 class="card-title">Secure Workspace</h3>
						<p>Your data is your own. Bex provides a secure, private workspace for your team's files and conversations, ensuring confidentiality.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	{{-- Pricing Section --}}
	<div id="pricing" class="py-20 bg-base-200">
		<div class="container mx-auto px-4">
			<div class="text-center mb-12">
				<h2 class="text-4xl font-bold">Affordable Plans for Every Team</h2>
				<p class="text-lg mt-4">Choose the plan that's right for you. More members means more savings.</p>
			</div>
			
			<div class="max-w-2xl mx-auto">
				<div class="text-center mb-6">
					<span class="font-semibold mr-4">Bill Monthly</span>
					<input type="checkbox" class="toggle toggle-primary" id="billing-toggle" />
					<span class="ml-4 font-semibold">Bill Yearly (Save up to 30%)</span>
				</div>
				
				<div class="card bg-base-100 shadow-xl border-2 border-primary">
					<div class="card-body">
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
							<a href="{{ route('register') }}" class="btn btn-primary w-full">Get Started</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	{{-- Footer --}}
	<footer class="footer footer-center p-4 bg-base-300 text-base-content">
		<aside>
			<p>Copyright © {{ date('Y') }} - All right reserved by Bex</p>
		</aside>
	</footer>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		// --- DOM Elements ---
		const billingToggle = document.getElementById('billing-toggle');
		const quantitySlider = document.getElementById('quantity-slider');
		
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
		// MODIFIED: Updated yearlyTiers with the new yearly prices.
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
</body>
</html>
