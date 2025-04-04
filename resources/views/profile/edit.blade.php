{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app') {{-- Or your main app layout --}}

@section('content')
	<div class="container py-4">
		<div class="row justify-content-center">
			<div class="col-md-8">
				<div class="card">
					<div class="card-header"><h2>{{ __('Profile Information') }}</h2></div>
					
					<div class="card-body">
						<p class="text-muted">{{ __("Update your account's profile information and email address.") }}</p>
						
						@if (session('status') === 'profile-updated')
							<div class="alert alert-success" role="alert">
								{{ __('Profile successfully updated.') }}
							</div>
						@endif
						
						<form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-6">
							@csrf
							@method('patch') {{-- Use PATCH method for updates --}}
							
							{{-- Name Field --}}
							<div class="mb-3 row">
								<label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
								<div class="col-md-6">
									<input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
									@error('name')
									<span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
									@enderror
								</div>
							</div>
							
							{{-- Email Field --}}
							<div class="mb-3 row">
								<label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email') }}</label>
								<div class="col-md-6">
									<input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
									@error('email')
									<span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
									@enderror
									
									{{-- Optional: Add message about email verification if applicable --}}
									{{-- @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) --}}
									{{-- Add verification resend logic here if needed --}}
									{{-- @endif --}}
								</div>
							</div>
							
							{{-- Save Button --}}
							<div class="mb-0 row">
								<div class="col-md-6 offset-md-4">
									<button type="submit" class="btn btn-primary">
										{{ __('Save') }}
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
				
				{{-- Optional: Add separate cards/sections for Password Update and Delete Account --}}
				<div class="card mt-4">
						<div class="card-header"><h2>{{ __('Update Password') }}</h2></div>
						 <div class="card-body">
								<p class="text-muted">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
								// Password update form here
						</div>
				</div>

				<div class="card mt-4">
						 <div class="card-header"><h2>{{ __('Delete Account') }}</h2></div>
						 <div class="card-body">
								 <p class="text-danger">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>
								 // Delete account button/modal trigger here
						 </div>
				 </div>
			
			</div>
		</div>
	</div>
@endsection
