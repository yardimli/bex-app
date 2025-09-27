@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="hero min-h-screen bg-base-200 pt-16">
        <div class="hero-content flex-col w-full max-w-md">
            <div class="card bg-base-100 shadow-xl w-full">
                <div class="card-body">
                    <h2 class="card-title justify-center text-2xl mb-4">{{ __('Login') }}</h2>
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="form-control w-full mb-3">
                            <label class="label" for="email">
                                <span class="label-text">{{ __('Email Address') }}</span>
                            </label>
                            <input id="email" type="email" class="input input-bordered w-full @error('email') input-error @enderror" name="email" value="{{ old('email', 'demo@bex.today') }}" required autocomplete="email" autofocus>
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
                            <input id="password" type="password" class="input input-bordered w-full @error('password') input-error @enderror" name="password" value="A123456b" required autocomplete="current-password">
                            @error('password')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                            @enderror
                        </div>
                        
                        <div class="form-control mb-6">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="remember" id="remember" class="checkbox checkbox-primary" {{ old('remember') ? 'checked' : '' }}>
                                <span class="label-text">{{ __('Remember Me') }}</span>
                            </label>
                        </div>
                        
                        <div class="card-actions items-center justify-between">
                            <a href="{{ route('register') }}" class="link link-hover">
                                {{ __("Don't have an account?") }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('Login') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
