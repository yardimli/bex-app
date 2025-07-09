<div class="navbar bg-base-100 rounded-box shadow-sm">
	<div class="navbar-start">
		{{-- Hamburger menu button for mobile to open the drawer --}}
		<label for="my-drawer-2" class="btn btn-ghost btn-circle lg:hidden">
			<i class="bi bi-list text-xl"></i>
		</label>
		{{-- MODIFIED: Wrapped the mode selector to only show on large screens (lg and up) --}}
		<div class="hidden lg:block">
			@include('partials.dropdowns.mode_selector')
		</div>
	</div>
	<div class="navbar-end">
		{{-- Include the reusable partial for icons and user menu --}}
		@include('partials.header_icons_and_user_menu')
	</div>
</div>