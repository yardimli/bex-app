{{--// resources/views/auth/register.blade.php:--}}

@extends('layouts.app')

@section('content')
    <div class="p-4 flex flex-col h-full gap-4">
        {{-- MODIFIED: Included the shared page header. --}}
        @include('partials.page_header')

        {{-- MODIFIED: Replaced Bootstrap card with DaisyUI card component. --}}
        <div class="card bg-base-100 shadow-xl w-full max-w-lg mx-4">
            <div class="card-body">
                {{-- MODIFIED: Updated card title to match DaisyUI card structure. --}}
                <h2 class="card-title justify-center text-2xl mb-4">{{ __('Register') }}</h2>
                
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    {{-- MODIFIED: Replaced Bootstrap form group with DaisyUI form-control for Name. --}}
                    <div class="form-control w-full mb-3">
                        <label class="label" for="name">
                            <span class="label-text">{{ __('Name') }}</span>
                        </label>
                        {{-- MODIFIED: Updated input with DaisyUI classes. Used `input-error` for validation state. --}}
                        <input id="name" type="text" class="input input-bordered w-full @error('name') input-error @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                        @error('name')
                        {{-- MODIFIED: Styled error message using DaisyUI/Tailwind classes. --}}
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>
                    
                    {{-- MODIFIED: Replaced Bootstrap form group with DaisyUI form-control for Email. --}}
                    <div class="form-control w-full mb-3">
                        <label class="label" for="email">
                            <span class="label-text">{{ __('Email Address') }}</span>
                        </label>
                        <input id="email" type="email" class="input input-bordered w-full @error('email') input-error @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                        @error('email')
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
                        <input id="password" type="password" class="input input-bordered w-full @error('password') input-error @enderror" name="password" required autocomplete="new-password">
                        @error('password')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>
                    
                    {{-- MODIFIED: Replaced Bootstrap form group with DaisyUI form-control for Password Confirmation. --}}
                    <div class="form-control w-full mb-6">
                        <label class="label" for="password-confirm">
                            <span class="label-text">{{ __('Confirm Password') }}</span>
                        </label>
                        <input id="password-confirm" type="password" class="input input-bordered w-full" name="password_confirmation" required autocomplete="new-password">
                    </div>
                    
                    {{-- MODIFIED: Updated form actions to use DaisyUI button and layout classes. --}}
                    <div class="card-actions items-center justify-between">
                        {{-- NEW: Added link to login page, styled as a DaisyUI link. --}}
                        <a href="{{ route('login') }}" class="link link-hover">
                            {{ __('Already have an account?') }}
                        </a>
                        {{-- MODIFIED: Updated button with DaisyUI classes. --}}
                        <button type="submit" class="btn btn-primary">
                            {{ __('Register') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
