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
      /* Simple animation for hero section */
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
			
			<div class="max-w-4xl mx-auto">
				<div class="text-center mb-6">
					<input type="checkbox" class="toggle toggle-primary" id="billing-toggle" checked />
					<span class="ml-4 font-semibold">Monthly</span>
					<span class="mx-2">/</span>
					<span class="font-semibold">Yearly (Save 28%)</span>
				</div>
				
				<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
					{{-- Individual Plan --}}
					<div class="card bg-base-100 shadow-xl">
						<div class="card-body">
							<h3 class="card-title text-2xl">Individual</h3>
							<p>For freelancers and solo power users.</p>
							<div class="my-4">
								<span class="text-5xl font-extrabold" id="individual-price">$6.99</span>
								<span class="text-xl" id="individual-period">/ month</span>
							</div>
							<ul class="space-y-2">
								<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Unlimited Personal Chats</li>
								<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> 10GB File Storage</li>
								<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Document Summarization</li>
								<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Audio Transcription</li>
							</ul>
							<div class="card-actions mt-6">
								<a href="{{ route('register') }}" class="btn btn-primary w-full">Get Started</a>
							</div>
						</div>
					</div>
					
					{{-- Team Plan --}}
					<div class="card bg-base-100 shadow-xl border-2 border-primary">
						<div class="card-body">
							<h3 class="card-title text-2xl">Team</h3>
							<p>For collaborative teams of any size.</p>
							<div class="my-4">
								<span class="text-5xl font-extrabold" id="team-price-per-user">$6.49</span>
								<span class="text-xl" id="team-period">/ user / month</span>
							</div>
							<div class="my-4">
								<label for="team-slider" class="label">Number of Team Members: <span id="team-size-label" class="font-bold">2</span></label>
								<input type="range" min="2" max="100" value="2" class="range range-primary" id="team-slider" />
							</div>
							<p class="text-center font-bold text-xl">Total: <span id="team-total-price">$12.98</span> / month</p>
							<ul class="space-y-2 mt-4">
								<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> All Individual Features</li>
								<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Unlimited Group Chats</li>
								<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Shared Team Workspace</li>
								<li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> Centralized Billing</li>
							</ul>
							<div class="card-actions mt-6">
								<a href="{{ route('register') }}" class="btn btn-primary w-full">Choose Team Plan</a>
							</div>
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
		const billingToggle = document.getElementById('billing-toggle');
		const teamSlider = document.getElementById('team-slider');
		
		const individualPriceEl = document.getElementById('individual-price');
		const individualPeriodEl = document.getElementById('individual-period');
		
		const teamPricePerUserEl = document.getElementById('team-price-per-user');
		const teamPeriodEl = document.getElementById('team-period');
		const teamSizeLabelEl = document.getElementById('team-size-label');
		const teamTotalPriceEl = document.getElementById('team-total-price');
		
		const prices = {
			individual: { monthly: 6.99, yearly: 4.99 },
			team: { monthly: 6.99, yearly: 4.99 }
		};
		
		function updatePrices() {
			const isMonthly = billingToggle.checked;
			const teamSize = parseInt(teamSlider.value, 10);
			
			// Individual Pricing
			const individualPrice = isMonthly ? prices.individual.monthly : prices.individual.yearly;
			individualPriceEl.textContent = `$${individualPrice.toFixed(2)}`;
			individualPeriodEl.textContent = isMonthly ? '/ month' : '/ month (billed yearly)';
			
			// Team Pricing
			const baseTeamPrice = isMonthly ? prices.team.monthly : prices.team.yearly;
			// Cheaper per user for more members (example discount logic)
			const discountFactor = 1 - (Math.min(teamSize, 50) * 0.005); // up to 25% discount
			const teamPricePerUser = baseTeamPrice * discountFactor;
			const teamTotalPrice = teamPricePerUser * teamSize;
			
			teamPricePerUserEl.textContent = `$${teamPricePerUser.toFixed(2)}`;
			teamPeriodEl.textContent = isMonthly ? '/ user / month' : '/ user / month';
			teamSizeLabelEl.textContent = teamSize;
			teamTotalPriceEl.textContent = `$${teamTotalPrice.toFixed(2)}`;
		}
		
		billingToggle.addEventListener('change', updatePrices);
		teamSlider.addEventListener('input', updatePrices);
		
		// Initial calculation
		updatePrices();
	});
</script>
</body>
</html>
