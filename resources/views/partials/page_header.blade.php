{{-- resources/views/partials/page_header.blade.php --}}

{{-- NEW: This new partial contains the shared header navbar for consistent use across pages. --}}
<div class="navbar bg-base-100 rounded-box shadow-sm">
	<div class="navbar-start">
		{{-- Hamburger menu button for mobile to open the drawer --}}
		<label for="my-drawer-2" class="btn btn-ghost btn-circle lg:hidden">
			<i class="bi bi-list text-xl"></i>
		</label>
		{{-- MODIFIED: This container correctly shows the dropdown only on large screens. --}}
		<div class="hidden lg:block">
			{{-- MODIFIED: The partial is included without extra classes, ensuring the button has a default auto-width. --}}
			@include('partials.dropdowns.mode_selector')
		</div>
	</div>
	<div class="navbar-end">
		{{-- Include the reusable partial for icons and user menu --}}
		@include('partials.header_icons_and_user_menu')
	</div>
</div>