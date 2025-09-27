{{-- MODIFIED: Changed layout to the new guest layout and updated styling. --}}
@extends('layouts.guest')

@section('title', 'Confirm Password')

@section('content')
    <div class="hero min-h-screen bg-base-200 pt-16">
        <div class="hero-content flex-col w-full max-w-md">
            <div class="card bg-base-100 shadow-xl w-full">
                <div class="card-body">
                    <h2 class="card-title justify-center text-xl mb-4">{{ __('Confirm Password') }}</h2>
                    <p class="mb-4 text-center">{{ __('Please confirm your password before continuing.') }}</p>
                    
                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf
                        <div class="form-control w-full mb-3">
                            <label class="label" for="password"><span class="label-text">{{ __('Password') }}</span></label>
                            <input id="password" type="password" class="input input-bordered w-full @error('password') input-error @enderror" name="password" required autocomplete="current-password">
                            @error('password')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>
                        
                        <div class="card-actions items-center justify-between mt-6">
                            @if (Route::has('password.request'))
                                <a class="btn btn-link p-0" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                {{ __('Confirm Password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
