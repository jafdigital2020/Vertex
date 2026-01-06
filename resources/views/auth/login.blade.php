<?php $page = 'login'; ?>

@extends('layout.mainlayout')
@section('content')
    <div class="container-fuild">
        <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
            <div class="row">

                <div class="col-lg-5">
                    <div
                        class="login-background position-relative d-lg-flex align-items-center justify-content-center d-none flex-wrap vh-100 p-0">
                        <div class="authentication-card w-100 h-100 d-flex align-items-center justify-content-center">
                            <div
                                class="authen-overlay-item border w-100 d-flex flex-column align-items-center justify-content-center">
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
                            <form id="loginForm" class="vh-100" method="POST">
                                @csrf
                                <div class="vh-100 d-flex flex-column justify-content-between p-4 pb-0">
                                    <div class=" mx-auto mb-5 text-center">
                                        <img src="{{ URL::asset('build/img/Timora-logo.png') }}" class="img-fluid"
                                            alt="Logo" style="width: 50%; height: auto;">
                                    </div>
                                    <div class="">
                                        <div class="text-center mb-3">
                                            <h2 class="mb-2">Sign In</h2>
                                            <p class="mb-0">Please enter your details to sign in</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Company Code</label>
                                            <div class="input-group">
                                                <input type="text" value="" id="companyCode" name="companyCode"
                                                    class="form-control border-end-0">
                                                <span class="input-group-text border-start-0">
                                                    <i class="ti ti-building"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Username or Email Address</label>
                                            <div class="input-group">
                                                <input type="text" value="" name="login" id="login"
                                                    class="form-control border-end-0">
                                                <span class="input-group-text border-start-0">
                                                    <i class="ti ti-mail"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <div class="pass-group">
                                                <input type="password" name="password" id="password"
                                                    class="pass-input form-control">
                                                <span class="ti toggle-password ti-eye-off"></span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="form-check form-check-md mb-0">
                                                    <input class="form-check-input" id="remember_me" name="remember"
                                                        type="checkbox" value="1"
                                                        {{ old('remember') ? 'checked' : '' }}>
                                                    <label for="remember_me" class="form-check-label mt-0">Remember
                                                        Me</label>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <a href="{{ url('forgot-password') }}" class="link-danger">Forgot
                                                    Password?</a>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary w-100">Sign In</button>
                                        </div>

                                        <div class="login-or">
                                            <span class="span-or">Download Our App</span>
                                        </div>
                                        <div class="mt-2">
                                            <div class="d-flex align-items-center justify-content-center flex-wrap">
                                                <div class="text-center me-2 flex-fill">
                                                    <a href="https://play.google.com/store/apps/details?id=com.jafdigital.timora"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="br-10 p-2 btn btn-outline-light border d-flex align-items-center justify-content-center">
                                                        <img class="img-fluid m-1"
                                                            src="{{ URL::asset('build/img/icons/google-play-badge.svg') }}"
                                                            alt="Google Play" style="max-width:40px; height:auto;">
                                                    </a>
                                                </div>
                                                <div class="text-center flex-fill">
                                                    <a href="https://apps.apple.com/ph/app/timora-automated-payroll-app/id6749219661"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="bg-dark br-10 p-2 btn btn-dark d-flex align-items-center justify-content-center">
                                                        <img class="img-fluid m-1"
                                                            src="{{ URL::asset('build/img/icons/apple-logo.svg') }}"
                                                            alt="Apple" style="max-width:160px; height:auto;">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 pb-4 text-center">
                                        <img class="img-fluid" src="{{ URL::asset('build/img/gdpr-image.png') }}"
                                            alt="GDPR" style="max-height:100px;">
                                        <p class="mb-0 text-gray-9">Copyright &copy; 2025 - JAF Digital Group Inc.</p>
                                    </div>
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
    <script src="{{ asset('build/js/login.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            localStorage.clear();
            sessionStorage.clear();

            // Remove remember-me cookies but keep session/CSRF cookies intact so Sanctum can validate requests
            ['remember_web', 'remember_global'].forEach(name => {
                document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;`;
            });

            // Make sure the XSRF token cookie exists before hitting /api/login
            try {
                await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
            } catch (e) {
                console.error('Unable to refresh CSRF cookie', e);
            }

            // Auto-uppercase company code input
            const companyCodeInput = document.getElementById('companyCode');
            if (companyCodeInput) {
                companyCodeInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.toUpperCase();
                });
            }
        });
    </script>
@endpush
