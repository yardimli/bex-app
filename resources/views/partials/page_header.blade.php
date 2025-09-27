<div class="navbar bg-base-100 rounded-box shadow-sm">
    <div class="navbar-start">
        {{-- Hamburger menu button for mobile to open the drawer --}}
        <label for="my-drawer-2" class="btn btn-ghost btn-circle lg:hidden">
            <i class="bi bi-list text-xl"></i>
        </label>
        {{-- The account switcher is here for desktop view. --}}
        <div class="hidden lg:flex items-center gap-2">
            @include('partials.account_switcher')
        </div>
    </div>
    <div class="navbar-center">
        @include('partials.llm_selector')
    </div>
    <div class="navbar-end">
        <div class="lg:hidden">
            @include('partials.account_switcher')
        </div>
        @include('partials.header_icons_and_user_menu')
    </div>
</div>
