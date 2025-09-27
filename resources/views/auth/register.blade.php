@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <div class="hero min-h-screen bg-base-200 pt-16">
        <div class="hero-content flex-col w-full max-w-md">
            <div class="card bg-base-100 shadow-xl w-full">
                <div class="card-body">
                    <h2 class="card-title justify-center text-2xl mb-4">{{ __('Register') }}</h2>
                    
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        
                        <div class="form-control w-full mb-3">
                            <label class="label" for="name">
                                <span class="label-text">{{ __('Name') }}</span>
                            </label>
                            <input id="name" type="text" class="input input-bordered w-full @error('name') input-error @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                            @enderror
                        </div>
                        
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
                        
                        <div class="form-control w-full mb-6">
                            <label class="label" for="password-confirm">
                                <span class="label-text">{{ __('Confirm Password') }}</span>
                            </label>
                            <input id="password-confirm" type="password" class="input input-bordered w-full" name="password_confirmation" required autocomplete="new-password">
                        </div>
                        
                        <div class="card-actions items-center justify-between">
                            <a href="{{ route('login') }}" class="link link-hover">
                                {{ __('Already have an account?') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('Register') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
