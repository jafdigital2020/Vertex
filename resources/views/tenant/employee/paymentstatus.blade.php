<?php $page = 'employees'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="card shadow-lg p-5 text-center border-0" style="max-width: 420px; margin: auto; background: #f8fafc;">
            <div class="mb-4">
                <span class="avatar avatar-xl bg-success text-white mb-3 shadow" style="font-size: 3rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px;">
                    <i class="ti ti-check"></i>
                </span>
                <h2 class="mb-2 text-success fw-bold">Payment Successful!</h2>
                <p class="mb-0 text-secondary">Thank you for your payment.<br>Your transaction was completed successfully.</p>
            </div>
            <div class="mt-4">
                <a href="{{ url('/employees') }}" class="btn btn-primary w-100">Go to Employees</a>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    You will be redirected automatically in 
                    <span id="timer" class="fw-bold text-primary">15</span> seconds...
                </small>
            </div>
        </div>
    </div>
    <script>
        let seconds = 15;
        const timer = document.getElementById('timer');
        const interval = setInterval(function() {
            seconds--;
            timer.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = "{{ url('/employees') }}";
            }
        }, 1000);
    </script>
@endsection
