{{-- resources/views/profile/index.blade.php --}}
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
                             @if (session('status') === 'password-updated')
                                 <div class="alert alert-success" role="alert">
                                     {{ __('Password successfully updated.') }}
                                 </div>
                             @endif
                             <form method="post" action="{{ route('password.update') }}" class="mt-4">
                                 @csrf
                                 @method('put')

                                 {{-- Current Password Field --}}
                                 <div class="mb-3 row">
                                     <label for="current_password" class="col-md-4 col-form-label text-md-end">{{ __('Current Password') }}</label>
                                     <div class="col-md-6">
                                         <input id="current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                                         @error('current_password')
                                         <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                                         @enderror
                                     </div>
                                 </div>

                                 {{-- New Password Field --}}
                                 <div class="mb-3 row">
                                     <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('New Password') }}</label>
                                     <div class="col-md-6">
                                         <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                                         @error('password')
                                         <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                                         @enderror
                                     </div>
                                 </div>

                                 {{-- Confirm New Password Field --}}
                                 <div class="mb-3 row">
                                     <label for="password_confirmation" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                                     <div class="col-md-6">
                                         <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" required>
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

				<div class="card mt-4">
						 <div class="card-header"><h2>{{ __('Delete Account') }}</h2></div>
						 <div class="card-body">
								 <p class="text-danger">{{ __('Once your account is deleted, all of your resources and data will be permanently deleted.') }}</p>
                             <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
                                 {{ __('Delete Account') }}
                             </button>

                             <!-- Modal -->
                             <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
                                 <div class="modal-dialog">
                                     <div class="modal-content">
                                         <form method="post" action="{{ route('profile.destroy') }}">
                                             @csrf
                                             @method('delete')

                                             <div class="modal-header">
                                                 <h5 class="modal-title" id="confirmUserDeletionModalLabel">{{ __('Confirm Account Deletion') }}</h5>
                                                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                             </div>

                                             <div class="modal-body">
                                                 <p>
                                                     {{ __('Are you sure you want to delete your account?') }}
                                                 </p>
                                             </div>

                                             <div class="modal-footer">
                                                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                 <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
                                             </div>
                                         </form>
                                     </div>
                                 </div>
                             </div>
						 </div>
				 </div>

			</div>
		</div>
	</div>
@endsection

@if($errors->userDeletion->isNotEmpty())
    @push('scripts')
        <script>
            $(document).ready(function() {
                var userDeletionModal = new bootstrap.Modal(document.getElementById('confirmUserDeletionModal'));
                userDeletionModal.show();
            });
        </script>
    @endpush
@endif
