<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ config('app.name', 'Laravel') }}</title>
	
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<!-- Fonts -->
	<link rel="dns-prefetch" href="//fonts.bunny.net">
	<link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
	
	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
	      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
	<!-- Bootstrap Icons -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<!-- Custom CSS -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	@stack('styles')
</head>
<body>
<div class="main-wrapper">
	<!-- Sidebar -->
	@include('partials.sidebar')
	
	<!-- Main Content -->
	<main class="main-content" id="main-content">
		{{-- Sidebar Toggle Button (positioned via CSS) --}}
		<button id="sidebarToggle" class="btn sidebar-toggle-btn shadow-sm" aria-label="Toggle sidebar">
			<i class="bi bi-list"></i>
		</button>
		@yield('content')
	</main>
</div>
<div class="sidebar-backdrop"></div>

<!-- Modals -->
@include('partials.modals.recent_meetings')
@include('partials.modals.my_notes')
@include('partials.modals.team_files')
@include('partials.modals.my_recordings')
@include('partials.modals.my_action_items')
@include('partials.modals.settings')
@include('partials.modals.summarize_content')
@include('partials.modals.transcribe')

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
<!-- Custom JS -->
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')

</body>
</html>
