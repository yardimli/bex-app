<div class="navbar bg-base-100 rounded-box shadow-sm">
    <div class="navbar-start">
        {{-- Hamburger menu button for mobile to open the drawer --}}
        <label for="my-drawer-2" class="btn btn-ghost btn-circle lg:hidden">
            <i class="bi bi-list text-xl"></i>
        </label>
        {{-- MODIFIED: This container correctly shows the dropdown only on large screens. --}}
        <div class="hidden lg:block">
            {{-- MODIFIED: Replaced the static mode_selector with the new dynamic llm_selector. --}}
            @include('partials.llm_selector')
        </div>
    </div>
    <div class="navbar-end">
        {{-- Include the reusable partial for icons and user menu --}}
        @include('partials.header_icons_and_user_menu')
    </div>
</div>
