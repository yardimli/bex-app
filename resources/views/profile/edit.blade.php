{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.1/dist/cropper.css" />
    <style>
        /* MODIFIED: Make the image container responsive and prevent modal overflow */
        .img-container {
            max-height: 60vh; /* Limit height to 60% of the viewport height */
            width: 100%;
            background-color: #f7f7f7;
            overflow: hidden; /* Hide parts of the image that overflow the container */
        }
        .img-container img {
            max-width: 100%;
        }

        /* ADDED: CSS to make the Cropper.js preview circular */
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }
        /* Optional: Hide the dashed outline since it's rectangular */
        .cropper-dashed {
            display: none;
        }
        /* Optional: Hide the corner drag handles */
        .cropper-point {
            display: none;
        }
        /* Optional: Hide the side drag handles */
        .cropper-line {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="p-4 flex flex-col h-full gap-4">
        @include('partials.page_header')

        <div class="bg-base-100 rounded-box shadow-sm flex-grow p-4 overflow-y-auto">
            <div class="flex justify-center">
                <div class="w-full lg:w-8/12 space-y-6">

                    {{-- ADDED: Avatar Update Card --}}
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h2 class="card-title">{{ __('Profile Avatar') }}</h2>
                            <p class="text-base-content/70">{{ __("Update your profile's avatar.") }}</p>
                            <div id="avatar-update-alert" class="hidden"></div>
                            <div class="mt-4 flex items-center gap-4">
                                <div class="avatar">
                                    <div class="w-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                        <img src="{{ $user->avatar_url }}" id="current-avatar-img" alt="{{ $user->name }}'s avatar" />
                                    </div>
                                </div>
                                <div>
                                    <label for="avatar-upload-input" class="btn btn-primary">
                                        {{ __('Upload New Avatar') }}
                                    </label>
                                    <input type="file" id="avatar-upload-input" class="hidden" accept="image/*" />
                                    <p class="text-xs text-base-content/60 mt-2">JPG, GIF, WEBP or PNG. Max size of 2MB.</p>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                    <input id="name" name="name" type="text" class="input input-bordered @error('name') input-error @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
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

    <!-- ADDED: Avatar Cropping Modal -->
    <dialog id="avatarCropModal" class="modal">
        <div class="modal-box w-11/12 max-w-lg flex flex-col max-h-[85vh]">
            {{-- Header --}}
            <h3 class="font-bold text-lg flex-shrink-0">Crop Your Avatar</h3>

            {{-- Main content area that grows and scrolls --}}
            <div class="py-4 flex-grow min-h-0 overflow-y-auto">
                <div class="img-container">
                    <img id="image-to-crop" src="">
                </div>
            </div>

            {{-- Footer with actions that stays at the bottom --}}
            <div class="modal-action flex-shrink-0">
                <form method="dialog">
                    <button class="btn btn-ghost" id="cancel-crop-btn">Cancel</button>
                </form>
                <button class="btn btn-primary" id="crop-and-upload-btn">Crop & Upload</button>
            </div>
        </div>
    </dialog>

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

    {{-- ADDED: Cropper.js library and the script to handle avatar cropping/uploading --}}
    {{-- MODIFIED: Switched to unpkg.com to avoid integrity hash issues --}}
    <script src="https://unpkg.com/cropperjs@1.6.1/dist/cropper.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avatarInput = document.getElementById('avatar-upload-input');
            const cropModal = document.getElementById('avatarCropModal');
            const imageToCrop = document.getElementById('image-to-crop');
            const cropAndUploadBtn = document.getElementById('crop-and-upload-btn');
            const currentAvatarImg = document.getElementById('current-avatar-img');
            const userAvatarInHeader = document.getElementById('user-avatar-header');
            const alertContainer = document.getElementById('avatar-update-alert');
            let cropper;

            avatarInput.addEventListener('change', function(e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const file = files[0];
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        imageToCrop.src = event.target.result;
                        cropModal.showModal();
                        if (cropper) {
                            cropper.destroy();
                        }
                        cropper = new Cropper(imageToCrop, {
                            aspectRatio: 1 / 1,
                            viewMode: 1,
                            background: false,
                            autoCropArea: 0.8,
                        });
                    };
                    reader.readAsDataURL(file);
                }
                this.value = ''; // Reset input to allow re-uploading the same file
            });

            cropModal.addEventListener('close', () => {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            });

            cropAndUploadBtn.addEventListener('click', function() {
                if (!cropper) {
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Uploading...';

                // MODIFIED: Fixed typo from getCrippedCanvas to getCroppedCanvas
                cropper.getCroppedCanvas({
                    width: 512,
                    height: 512,
                    imageSmoothingQuality: 'high',
                }).toBlob((blob) => {
                    const formData = new FormData();
                    formData.append('avatar', blob, 'avatar.png');

                    fetch('{{ route("profile.avatar.update") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => {
                            // Check if the response is ok, if not, parse the error JSON
                            if (!response.ok) {
                                return response.json().then(err => { throw err; });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                const newAvatarUrl = data.avatar_url + '?t=' + new Date().getTime(); // Cache bust
                                currentAvatarImg.src = newAvatarUrl;
                                if (userAvatarInHeader) {
                                    userAvatarInHeader.src = newAvatarUrl;
                                }
                                showAlert('success', data.message);
                                cropModal.close();
                            }
                        })
                        .catch(error => {
                            console.error('Upload error:', error);
                            let errorMessage = error.message || 'An unknown error occurred.';
                            if (error.errors && error.errors.avatar) {
                                errorMessage = error.errors.avatar[0];
                            }
                            showAlert('error', `Upload failed: ${errorMessage}`);
                        })
                        .finally(() => {
                            this.disabled = false;
                            this.innerHTML = 'Crop & Upload';
                        });
                }, 'image/png');
            });

            function showAlert(type, message) {
                alertContainer.className = `alert alert-${type} mt-4`;
                alertContainer.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'}"></i> <span>${message}</span>`;
                alertContainer.classList.remove('hidden');
                setTimeout(() => {
                    alertContainer.classList.add('hidden');
                }, 5000);
            }
        });
    </script>
@endpush
