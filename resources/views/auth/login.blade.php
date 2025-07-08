// resources/views/auth/login.blade.php:

@extends('layouts.app')

@section('content')
    {{-- MODIFIED: Replaced Bootstrap grid with a full-height flex container to center the login card. --}}
    <div class="flex items-center justify-center flex-grow">
        {{-- MODIFIED: Replaced Bootstrap card with DaisyUI card component for a modern look. --}}
        <div class="card bg-base-100 shadow-xl w-full max-w-lg mx-4">
            <div class="card-body">
                {{-- MODIFIED: Updated card title to match DaisyUI card structure. --}}
                <h2 class="card-title justify-center text-2xl mb-4">{{ __('Login') }}</h2>
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    {{-- MODIFIED: Replaced Bootstrap form group with DaisyUI form-control for Email. --}}
                    <div class="form-control w-full mb-3">
                        <label class="label" for="email">
                            <span class="label-text">{{ __('Email Address') }}</span>
                        </label>
                        {{-- MODIFIED: Updated input with DaisyUI classes. Used `input-error` for validation state. --}}
                        <input id="email" type="email" class="input input-bordered w-full @error('email') input-error @enderror" name="email" value="{{ old('email', 'demo@bex.today') }}" required autocomplete="email" autofocus>
                        @error('email')
                        {{-- MODIFIED: Styled error message using DaisyUI/Tailwind classes. --}}
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>
                    
                    {{-- MODIFIED: Replaced Bootstrap form group with DaisyUI form-control for Password. --}}
                    <div class="form-control w-full mb-3">
                        <label class="label" for="password">
                            <span class="label-text">{{ __('Password') }}</span>
                        </label>
                        {{-- MODIFIED: Updated input with DaisyUI classes. Used `input-error` for validation state. --}}
                        <input id="password" type="password" class="input input-bordered w-full @error('password') input-error @enderror" name="password" value="A123456b" required autocomplete="current-password">
                        @error('password')
                        {{-- MODIFIED: Styled error message using DaisyUI/Tailwind classes. --}}
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>
                    
                    {{-- MODIFIED: Replaced Bootstrap form-check with DaisyUI form-control and checkbox. --}}
                    <div class="form-control mb-6">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="remember" id="remember" class="checkbox checkbox-primary" {{ old('remember') ? 'checked' : '' }}>
                            <span class="label-text">{{ __('Remember Me') }}</span>
                        </label>
                    </div>
                    
                    {{-- MODIFIED: Updated form actions to use DaisyUI button and layout classes. --}}
                    <div class="card-actions items-center justify-between">
                        {{-- NEW: Added link to registration page, styled as a DaisyUI link. --}}
                        <a href="{{ route('register') }}" class="link link-hover">
                            {{ __("Don't have an account?") }}
                        </a>
                        {{-- MODIFIED: Updated button with DaisyUI classes. --}}
                        <button type="submit" class="btn btn-primary">
                            {{ __('Login') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
