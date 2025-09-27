<div class="navbar bg-base-100 rounded-box shadow-sm">
    <div class="navbar-start">
        {{-- Hamburger menu button for mobile to open the drawer --}}
        <label for="my-drawer-2" class="btn btn-ghost btn-circle lg:hidden">
            <i class="bi bi-list text-xl"></i>
        </label>
        {{-- The account switcher is here for desktop view. --}}
        <div class="hidden lg:flex items-center gap-2">
            @include('partials.llm_selector')
            @include('partials.account_switcher')
        </div>
    </div>
    <div class="navbar-end">
        @include('partials.header_icons_and_user_menu')
    </div>
</div>
