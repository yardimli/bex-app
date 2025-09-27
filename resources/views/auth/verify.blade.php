{{-- MODIFIED: Changed layout to the new guest layout and updated styling. --}}
@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')
    <div class="hero min-h-screen bg-base-200 pt-16">
        <div class="hero-content flex-col w-full max-w-md">
            <div class="card bg-base-100 shadow-xl w-full">
                <div class="card-body text-center">
                    <h2 class="card-title justify-center text-xl mb-4">{{ __('Verify Your Email Address') }}</h2>
                    
                    @if (session('resent'))
                        <div class="alert alert-success mb-4" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif
                    
                    <p>{{ __('Before proceeding, please check your email for a verification link.') }}</p>
                    <p>{{ __('If you did not receive the email') }},</p>
                    <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
