<?php $page = 'forgot-password'; ?>

@extends('layout.mainlayout')
@section('content')
    <div class="container-fuild">
        <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
            <div class="row">
                <div class="col-lg-5">
                    <div class="login-background position-relative d-lg-flex align-items-center justify-content-center d-none flex-wrap vh-100 p-0">
                        <div class="authentication-card w-100 h-100 d-flex align-items-center justify-content-center">
                            <div class="authen-overlay-item border w-100 d-flex flex-column align-items-center justify-content-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <img src="{{ URL::asset('build/img/bg/1.gif') }}" alt="Img" class="w-100"
                                        style="object-fit: cover; width:100%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 col-md-12 col-sm-12">
                    <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
                        <div class="col-md-7 mx-auto vh-100">
                            <!-- Form must POST to 'forgot-password-otp' route -->
                            <form id="forgotPasswordForm" class="vh-100 d-flex flex-column justify-content-between" method="POST" action="{{ route('forgot-password-otp') }}">
                                @csrf
                                <div class="p-4 pb-0">
                                    <div class="mx-auto mb-5 text-center">
                                        <img src="{{ URL::asset('build/img/Timora-logo.png') }}" class="img-fluid"
                                            alt="Logo" style="width: 50%; height: auto;">
                                    </div>
                                    <div class="">
                                        <div class="text-center mb-4">
                                            <h2 class="mb-2">Forgot Password</h2>
                                            <p class="mb-0">Enter your email address and we'll send you an OTP to reset your password.</p>
                                        </div>
                                        
                                        @if (session('status'))
                                            <div class="alert alert-success mb-3">
                                                {{ session('status') }}
                                            </div>
                                        @endif

                                        @if ($errors->has('email'))
                                            <div class="alert alert-danger mb-3">
                                                {{ $errors->first('email') }}
                                            </div>
                                        @endif

                                        <div class="mb-4">
                                            <label class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <input type="email" 
                                                       name="email" 
                                                       id="email" 
                                                       value="{{ old('email') }}"
                                                       class="form-control border-end-0 @error('email') is-invalid @enderror"
                                                       placeholder="Enter your registered email"
                                                       required>
                                                <span class="input-group-text border-start-0">
                                                    <i class="ti ti-mail"></i>
                                                </span>
                                            </div>
                                            @error('email')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-4">
                                             <button type="submit" class="btn btn-primary w-100">Send OTP</button>
                                        </div>

                                        <div class="text-center">
                                            <a href="{{ route('login') }}" class="link-primary">
                                                <i class="ti ti-arrow-left"></i> Back to Sign In
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 pb-4 text-center">
                                    <img class="img-fluid" src="{{ URL::asset('build/img/gdpr-image.png') }}"
                                        alt="GDPR" style="max-height:100px;">
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
            // Clear storage if needed
            localStorage.clear();
            sessionStorage.clear();

            // Form validation
            const form = document.getElementById('forgotPasswordForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value.trim();
                    if (!email) {
                        e.preventDefault();
                        alert('Please enter your email address.');
                        return false;
                    }
                    // Form will submit to forgot-password-otp route
                });
            }
        });
    </script>
@endpush