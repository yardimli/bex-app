<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<title>{{ config('app.name', 'Laravel') }} - Authentication</title>
	
	<!-- Fonts -->
	<link rel="dns-prefetch" href="//fonts.bunny.net">
	<link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
	
	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
	
	<!-- Bootstrap Icons -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	
	<!-- Custom CSS (includes dark mode) -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	
	<!-- Scripts required for Vite/Laravel Mix (if used) and dark mode JS -->
	{{-- @vite(['resources/sass/app.scss', 'resources/js/app.js']) --}}
	{{-- NOTE: If you are using Vite, uncomment the line above and potentially remove the app.css/app.js links below.
					 If you are NOT using Vite (just manually linking), keep the lines below.
					 Make sure your build process compiles app.css and app.js to the public directory. --}}

</head>
<body>
{{-- The main-wrapper and sidebar are intentionally omitted here --}}
<main class="py-4"> {{-- Add some padding like default auth pages often have --}}
	@yield('content')
</main>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!-- Custom JS (includes theme toggling logic) -->
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
