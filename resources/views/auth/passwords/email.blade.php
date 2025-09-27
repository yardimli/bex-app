@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <div class="hero min-h-screen bg-base-200 pt-16">
        <div class="hero-content flex-col w-full max-w-md">
            <div class="card bg-base-100 shadow-xl w-full">
                <div class="card-body">
                    <h2 class="card-title justify-center text-xl mb-4">{{ __('Reset Password') }}</h2>
                    
                    @if (session('status'))
                        <div class="alert alert-success mb-4">
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="form-control w-full mb-3">
                            <label class="label" for="email"><span class="label-text">{{ __('Email Address') }}</span></label>
                            <input id="email" type="email" class="input input-bordered w-full @error('email') input-error @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>
                        
                        <div class="card-actions justify-end mt-6">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Send Password Reset Link') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
