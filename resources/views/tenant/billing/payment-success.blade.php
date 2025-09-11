<?php $page = 'payment-success'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card text-center">
                        <div class="card-body py-5">
                            <div class="mb-4">
                                <i class="ti ti-check-circle text-success" style="font-size: 5rem;"></i>
                            </div>
                            <h2 class="text-success mb-3">Payment Successful!</h2>
                            <p class="text-muted mb-4 fs-5">
                                Thank you for your payment. Your subscription has been activated successfully.
                            </p>

                            <div class="row justify-content-center mb-4">
                                <div class="col-md-8">
                                    <div class="bg-light rounded p-3">
                                        <div class="row text-start">
                                            <div class="col-6">
                                                <small class="text-muted">Payment Status:</small>
                                                <div class="fw-bold text-success">Completed</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Subscription Status:</small>
                                                <div class="fw-bold text-success">Active</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3 justify-content-center">
                                <a href="{{ route('billing.index') }}" class="btn btn-primary">
                                    <i class="ti ti-receipt me-2"></i>
                                    View Billing
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    <script>
        // Auto redirect after 10 seconds (optional)
        setTimeout(function() {
            // Uncomment if you want auto redirect
            // window.location.href = "{{ route('billing.index') }}";
        }, 10000);
    </script>
@endpush
