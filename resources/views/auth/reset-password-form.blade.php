<?php $page = 'reset-password'; ?>

@extends('layout.mainlayout')
@section('content')
<div class="container-fuild">
    <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
        <div class="row">
            <!-- Left side with background animation -->
            <div class="col-lg-5">
                <div class="login-background position-relative d-lg-flex align-items-center justify-content-center d-none flex-wrap vh-100 p-0">
                    <div class="authentication-card w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="authen-overlay-item border w-100 d-flex flex-column align-items-center justify-content-center">
                            <div class="d-flex align-items-center justify-content-center">
                                <img src="{{ URL::asset('build/img/bg/1.gif') }}" alt="Img" class="w-100" style="object-fit: cover; width:100%;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side: Reset Password Form -->
            <div class="col-lg-7 col-md-12 col-sm-12">
                <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
                    <div class="col-md-7 mx-auto vh-100">
                        <form id="resetPasswordForm" class="vh-100 d-flex flex-column justify-content-between" method="POST" action="{{ route('password.reset') }}">
                            @csrf
                            <div class="p-4 pb-0">
                                <div class="mx-auto mb-5 text-center">
                                    <img src="{{ URL::asset('build/img/Timora-logo.png') }}" class="img-fluid" alt="Logo" style="width: 50%; height: auto;">
                                </div>

                                <div class="text-center mb-4">
                                    <h2 class="mb-2">Reset Password</h2>
                                    <p class="mb-0">Enter your new password below.</p>
                                </div>

                                @if (session('status'))
                                    <div class="alert alert-success mb-3">{{ session('status') }}</div>
                                @endif

                                <!-- Password Field -->
                                <div class="mb-4 position-relative">
                                    <label class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password"
                                               name="password"
                                               id="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               placeholder="Enter new password"
                                               required
                                               pattern="(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                                               title="Must be at least 8 characters, include letters, numbers, and special characters">
                                        <span class="input-group-text">
                                            <i class="ti ti-eye" id="togglePassword" style="cursor:pointer;"></i>
                                        </span>
                                    </div>
                                    @error('password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirm Password Field -->
                                <div class="mb-4 position-relative">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password"
                                               name="password_confirmation"
                                               id="password_confirmation"
                                               class="form-control"
                                               placeholder="Confirm new password"
                                               required>
                                        <span class="input-group-text">
                                            <i class="ti ti-eye" id="toggleConfirmPassword" style="cursor:pointer;"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                                </div>

                                <div class="text-center">
                                    <a href="{{ route('login') }}" class="link-primary">
                                        <i class="ti ti-arrow-left"></i> Back to Sign In
                                    </a>
                                </div>
                            </div>

                            <div class="mt-5 pb-4 text-center">
                                <img class="img-fluid" src="{{ URL::asset('build/img/gdpr-image.png') }}" alt="GDPR" style="max-height:100px;">
                                <p class="mb-0 text-gray-9">Copyright &copy; 2025 - JAF Digital Group Inc.</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        togglePassword.classList.toggle('ti-eye-off');
    });

    const toggleConfirm = document.getElementById('toggleConfirmPassword');
    const confirmPassword = document.getElementById('password_confirmation');

    toggleConfirm.addEventListener('click', () => {
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        toggleConfirm.classList.toggle('ti-eye-off');
    });

    // Client-side password match validation
    const form = document.getElementById('resetPasswordForm');
    form.addEventListener('submit', function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Passwords do not match.');
        }
    });

});
</script>
@endpush
