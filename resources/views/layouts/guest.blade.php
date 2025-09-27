{{-- NEW: This layout provides a consistent wrapper for all public-facing pages like login, register, and subscribe. --}}
	<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	{{-- MODIFIED: Title is now dynamic based on the page. --}}
	<title>@yield('title', 'Bex - Your AI Team Assistant')</title>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="dns-prefetch" href="//fonts.bunny.net">
	<link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
	<link rel="manifest" href="/images/site.webmanifest">
	@vite('resources/css/app.css')
	@stack('styles')
</head>
<body class="bg-base-200">
<div id="app" class="flex flex-col min-h-screen">
	{{-- Navbar --}}
	<div class="navbar bg-base-100 shadow-sm fixed top-0 z-50">
		<div class="container mx-auto">
			<div class="navbar-start">
				<a href="/" class="btn btn-ghost text-xl">
					<img src="/images/logo-bex_02-logo-bex-color.png" alt="Bex Logo" class="h-8 w-auto">
				</a>
			</div>
			<div class="navbar-end">
				{{-- MODIFIED: Navbar dynamically shows Dashboard or Login/Register links. --}}
				@if (Route::has('login'))
					@auth
						<a href="{{ url('/home') }}" class="btn btn-primary">Dashboard</a>
					@else
						<a href="{{ route('login') }}" class="btn btn-ghost">Login</a>
						@if (Route::has('register'))
							<a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
						@endif
					@endauth
				@endif
			</div>
		</div>
	</div>
	
	{{-- Main Content Area --}}
	<main class="flex-grow">
		@yield('content')
	</main>
	
	{{-- Footer --}}
	<footer class="footer footer-center p-4 bg-base-300 text-base-content">
		<aside>
			<p>Copyright © {{ date('Y') }} - All right reserved by Bex</p>
		</aside>
	</footer>
</div>
@stack('scripts')
</body>
</html>
