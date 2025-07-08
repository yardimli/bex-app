{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@section('content')
	{{-- MODIFIED: Added a full-height flex container to manage the layout. --}}
	<div class="p-4 flex flex-col h-full gap-4">
		{{-- MODIFIED: Included the shared page header. --}}
		@include('partials.page_header')
		
		{{-- MODIFIED: Wrapped original content in a flex-grow container for proper layout and scrolling. --}}
		<div class="bg-base-100 rounded-box shadow-sm flex-grow p-4 overflow-y-auto">
			<div class="flex justify-center">
				<div class="w-full lg:w-8/12 space-y-6">
					
					{{-- MODIFIED: Profile Information Card with DaisyUI/Tailwind classes --}}
					<div class="card bg-base-100 shadow-xl">
						<div class="card-body">
							<h2 class="card-title">{{ __('Profile Information') }}</h2>
							<p class="text-base-content/70">{{ __("Update your account's profile information and email address.") }}</p>
							
							@if (session('status') === 'profile-updated')
								<div role="alert" class="alert alert-success mt-4">
									<i class="bi bi-check-circle-fill"></i>
									<span>{{ __('Profile successfully updated.') }}</span>
								</div>
							@endif
							
							<form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
								@csrf
								@method('patch')
								
								{{-- Name Field --}}
								<div class="form-control">
									<label class="label" for="name"><span class="label-text">{{ __('Name') }}</span></label>
									<input id="name" name="name" type="text" class="input input-bordered @error('name') input-error @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
									@error('name')
									<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
									@enderror
								</div>
								
								{{-- Email Field --}}
								<div class="form-control">
									<label class="label" for="email"><span class="label-text">{{ __('Email') }}</span></label>
									<input id="email" name="email" type="email" class="input input-bordered @error('email') input-error @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
									@error('email')
									<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
									@enderror
								</div>
								
								<div class="card-actions justify-start">
									<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
								</div>
							</form>
						</div>
					</div>
					
					{{-- MODIFIED: Update Password Card with DaisyUI/Tailwind classes --}}
					<div class="card bg-base-100 shadow-xl">
						<div class="card-body">
							<h2 class="card-title">{{ __('Update Password') }}</h2>
							<p class="text-base-content/70">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
							@if (session('status') === 'password-updated')
								<div role="alert" class="alert alert-success mt-4">
									<i class="bi bi-check-circle-fill"></i>
									<span>{{ __('Password successfully updated.') }}</span>
								</div>
							@endif
							<form method="post" action="{{ route('password.update') }}" class="mt-4 space-y-4">
								@csrf
								@method('put')
								
								<div class="form-control">
									<label class="label" for="current_password"><span class="label-text">{{ __('Current Password') }}</span></label>
									<input id="current_password" name="current_password" type="password" class="input input-bordered @error('current_password', 'updatePassword') input-error @enderror" autocomplete="current-password" required>
									@error('current_password', 'updatePassword')
									<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
									@enderror
								</div>
								
								<div class="form-control">
									<label class="label" for="password"><span class="label-text">{{ __('New Password') }}</span></label>
									<input id="password" name="password" type="password" class="input input-bordered @error('password', 'updatePassword') input-error @enderror" autocomplete="new-password" required>
									@error('password', 'updatePassword')
									<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
									@enderror
								</div>
								
								<div class="form-control">
									<label class="label" for="password_confirmation"><span class="label-text">{{ __('Confirm Password') }}</span></label>
									<input id="password_confirmation" name="password_confirmation" type="password" class="input input-bordered" autocomplete="new-password" required>
								</div>
								
								<div class="card-actions justify-start">
									<button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
								</div>
							</form>
						</div>
					</div>
					
					{{-- MODIFIED: Delete Account Card with DaisyUI/Tailwind classes --}}
					<div class="card bg-base-100 shadow-xl">
						<div class="card-body">
							<h2 class="card-title">{{ __('Delete Account') }}</h2>
							<p class="text-error">{{ __('Once your account is deleted, all of your resources and data will be permanently deleted.') }}</p>
							{{-- MODIFIED: Button to open DaisyUI modal --}}
							<div class="card-actions justify-start mt-4">
								<button type="button" class="btn btn-error" onclick="confirmUserDeletionModal.showModal()">
									{{ __('Delete Account') }}
								</button>
							</div>
						</div>
					</div>
				
				</div>
			</div>
		</div>
	</div>
	
	<!-- MODIFIED: Delete confirmation modal converted to DaisyUI <dialog> -->
	<dialog id="confirmUserDeletionModal" class="modal">
		<div class="modal-box">
			<form method="post" action="{{ route('profile.destroy') }}">
				@csrf
				@method('delete')
				
				<h3 class="font-bold text-lg">{{ __('Confirm Account Deletion') }}</h3>
				<p class="py-4">{{ __('Are you sure you want to delete your account? This action cannot be undone.') }}</p>
				
				<div class="form-control">
					<label class="label" for="password_delete"><span class="label-text">{{ __('Please enter your password to confirm.') }}</span></label>
					<input id="password_delete" name="password" type="password" class="input input-bordered @error('password', 'userDeletion') input-error @enderror" required>
					@error('password', 'userDeletion')
					<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
					@enderror
				</div>
				
				<div class="modal-action">
					<button type="button" class="btn" onclick="confirmUserDeletionModal.close()">{{ __('Cancel') }}</button>
					<button type="submit" class="btn btn-error">{{ __('Delete Account') }}</button>
				</div>
			</form>
		</div>
		<form method="dialog" class="modal-backdrop"><button>close</button></form>
	</dialog>
@endsection

@push('scripts')
	{{-- MODIFIED: Script to open modal on validation error --}}
	@if($errors->userDeletion->isNotEmpty())
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const modal = document.getElementById('confirmUserDeletionModal');
				if (modal) {
					modal.showModal();
				}
			});
		</script>
	@endif
@endpush
