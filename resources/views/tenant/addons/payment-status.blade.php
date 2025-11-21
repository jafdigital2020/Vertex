<?php $page = 'addons'; ?>
@extends('layout.mainlayout')
@section('content')
    @php
        $status = request('status', 'success');
        $isSuccess = in_array($status, ['success', 'completed', 'paid']);
        $isCanceled = in_array($status, ['canceled', 'cancelled', 'failed']);
    @endphp
    <div class="page-wrapper d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="card shadow-lg p-5 text-center border-0" style="max-width: 420px; margin: auto; background: #f8fafc;">
            <div class="mb-4">
                @if($isSuccess)
                    <span class="avatar avatar-xl bg-success text-white mb-3 shadow"
                        style="font-size: 3rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px;">
                        <i class="ti ti-check"></i>
                    </span>
                    <h2 class="mb-2 text-success fw-bold">Payment Successful!</h2>
                    <p class="mb-0 text-secondary">Thank you for your payment.<br>Your addon will be activated shortly.
                    </p>
                @elseif($isCanceled)
                    <span class="avatar avatar-xl bg-danger text-white mb-3 shadow"
                        style="font-size: 3rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px;">
                        <i class="ti ti-x"></i>
                    </span>
                    <h2 class="mb-2 text-danger fw-bold">Payment {{ ucfirst($status) }}</h2>
                    <p class="mb-0 text-secondary">Your payment was not completed.<br>Please try again or contact support.</p>
                @else
                    <span class="avatar avatar-xl bg-warning text-white mb-3 shadow"
                        style="font-size: 3rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px;">
                        <i class="ti ti-alert-triangle"></i>
                    </span>
                    <h2 class="mb-2 text-warning fw-bold">Payment Pending</h2>
                    <p class="mb-0 text-secondary">Your payment status is unclear.<br>Please contact support if needed.</p>
                @endif
            </div>
            <div class="mt-4">
                <a href="{{ route('addons.purchase') }}" class="btn btn-primary w-100">Go to Add-ons</a>
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
        const interval = setInterval(function () {
            seconds--;
            timer.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = "{{ route('addons.purchase') }}";
            }
        }, 1000);
    </script>
@endsection
