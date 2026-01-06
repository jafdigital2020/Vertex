<?php $page = 'otp-form' ?>

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
                                <img src="{{ URL::asset('build/img/bg/1.gif') }}" alt="Img" class="w-100"
                                     style="object-fit: cover; width:100%;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side: OTP form -->
            <div class="col-lg-7 col-md-12 col-sm-12">
                <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
                    <div class="col-md-7 mx-auto vh-100">
                        <form id="otpForm" class="vh-100 d-flex flex-column justify-content-between" method="POST" action="{{ route('forgot.password.verify') }}">
                            @csrf
                            <div class="mx-auto text-center mb-5">
                                <img src="{{ URL::asset('build/img/Timora-logo.png') }}" class="img-fluid"
                                     alt="Logo" style="width: 50%; height: auto;">
                            </div>

                            <div>
                                <div class="text-center mb-4">
                                    <h2 class="mb-2">Enter OTP</h2>
                                    <p class="mb-0">We have sent a 6-digit OTP to your email. Please enter it below to reset your password.</p>
                                </div>

                                @if (session('status'))
                                    <div class="alert alert-success mb-3">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger mb-3">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between gap-2 mb-3" id="otpInputs">
                                    @for ($i = 0; $i < 6; $i++)
                                        <input type="text"
                                            class="form-control text-center otp-box"
                                            style ={{ "    font-size: 1.5rem; height: 55px; border-radius: 10px;"}}
                                            maxlength="1"
                                            inputmode="numeric"
                                            pattern="[0-9]"
                                            required>
                                    @endfor
                                </div>

                                <input type="hidden" name="otp" id="otp">

                                <div class="mb-4">
                                    <button class="btn btn-primary w-100">Verify OTP</button>
                                </div>
                                

                                <div class="text-center">
                                    <a href="{{ route('forgot-password') }}" class="link-primary">
                                        <i class="ti ti-arrow-left"></i> Back to Email
                                    </a>
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
document.addEventListener('DOMContentLoaded', function () {

    /* ===== OTP BOXES LOGIC ===== */
    const inputs = document.querySelectorAll('.otp-box');
    const hiddenOtp = document.getElementById('otp');

    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '');
            if (input.value && inputs[index + 1]) {
                inputs[index + 1].focus();
            }
            hiddenOtp.value = Array.from(inputs).map(i => i.value).join('');
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && inputs[index - 1]) {
                inputs[index - 1].focus();
            }
        });
    });

    /* ===== FORM VALIDATION ===== */
    const form = document.getElementById('otpForm');
    form.addEventListener('submit', function (e) {
        if (hiddenOtp.value.length !== 6) {
            e.preventDefault();
            alert('Please enter the complete 6-digit OTP.');
        }
    });

    /* ===== TIMER (10 MINUTES) ===== */
    let timeLeft = 600;
    const timer = document.createElement('p');
    timer.className = 'text-center text-muted mt-2';
    timer.innerHTML = 'OTP expires in <span id="otpTimer">10:00</span>';
    document.querySelector('#otpInputs').after(timer);
    const timerSpan = document.getElementById('otpTimer');

    const countdown = setInterval(() => {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerSpan.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        if (timeLeft-- <= 0) {
            clearInterval(countdown);
            timerSpan.textContent = 'Expired';
        }
    }, 1000);

});
</script>
@endpush
