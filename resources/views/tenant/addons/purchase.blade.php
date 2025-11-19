<?php $page = 'addons'; ?>
@extends('layout.mainlayout')
@section('content')
    <style>
        /* Shadcn-inspired minimal design */
        .addon-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid hsl(214.3 31.8% 91.4%);
            border-radius: 0.5rem;
            background: white;
            height: 100%;
            position: relative;
        }

        .addon-card:hover {
            border-color: hsl(215 20.2% 65.1%);
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        }

        .addon-card.active {
            border-color: hsl(180 50% 85%);
            background: hsl(180 60% 98%);
        }

        .addon-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: hsl(215.4 16.3% 96.9%);
            color: hsl(215.4 16.3% 46.9%);
            margin-bottom: 1rem;
        }

        .addon-card.active .addon-icon {
            background: hsl(180 40% 96%);
            color: hsl(180 50% 40%);
        }

        .addon-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            padding: 0.125rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            background: hsl(180 45% 94%);
            color: hsl(180 50% 35%);
            border: 1px solid hsl(180 40% 88%);
        }

        .price-tag {
            font-size: 2rem;
            font-weight: 700;
            color: hsl(222.2 84% 4.9%);
            letter-spacing: -0.025em;
        }

        .price-tag small {
            font-size: 0.875rem;
            font-weight: 400;
            color: hsl(215.4 16.3% 46.9%);
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .feature-list li {
            padding: 0.375rem 0;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: hsl(215.4 16.3% 46.9%);
        }

        .feature-list li i {
            color: #008080;
            font-size: 1rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .billing-toggle {
            background: hsl(215.4 16.3% 96.9%);
            padding: 0.25rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0;
            border: 1px solid hsl(214.3 31.8% 91.4%);
        }

        .billing-toggle .option {
            padding: 0.375rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.15s ease;
            font-weight: 500;
            font-size: 0.875rem;
            color: hsl(215.4 16.3% 46.9%);
        }

        .billing-toggle .option.active {
            background: white;
            color: hsl(222.2 84% 4.9%);
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }

        .discount-badge {
            background: hsl(24 95% 90%);
            color: hsl(24 95% 40%);
            padding: 0.125rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .purchase-btn {
            width: 100%;
            padding: 0.625rem 1rem;
            font-weight: 500;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .purchase-btn.btn-primary {
            background: #12515D;
            color: white;
        }

        .purchase-btn.btn-primary:hover:not(:disabled) {
            background: #0d3d47;
        }

        .purchase-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid hsl(214.3 31.8% 91.4%);
            color: hsl(222.2 84% 4.9%);
        }

        .btn-ghost:hover {
            background: hsl(215.4 16.3% 96.9%);
        }

        .btn-destructive {
            background: hsl(0 84.2% 60.2%);
            color: white;
            border: none;
        }

        .btn-destructive:hover {
            background: hsl(0 84.2% 50.2%);
        }

        .info-banner {
            background: white;
            border: 1px solid hsl(214.3 31.8% 91.4%);
            border-left: 3px solid #008080;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .portal-link {
            background: hsl(215.4 16.3% 96.9%);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: hsl(222.2 84% 4.9%);
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid hsl(214.3 31.8% 91.4%);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .portal-link:hover {
            background: hsl(215.4 16.3% 93%);
            color: hsl(222.2 84% 4.9%);
        }

        .section-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: hsl(222.2 84% 4.9%);
            letter-spacing: 0.025em;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: hsl(222.2 84% 4.9%);
            margin-bottom: 0.5rem;
        }

        .card-description {
            font-size: 0.875rem;
            color: hsl(215.4 16.3% 46.9%);
            line-height: 1.5;
        }
    </style>

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Branch Add-ons</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('/') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Subscription</li>
                            <li class="breadcrumb-item active" aria-current="page">Branch Add-ons</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Info Banner -->
            <div class="info-banner">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <div class="d-flex align-items-start gap-3">
                            <div class="addon-icon" style="margin: 0;">
                                <i class="ti ti-package"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-2">Flexible Branch Add-ons</h5>
                                <p class="card-description mb-3">
                                    <strong>Pay only for what you use.</strong> Enhance your branch capabilities with powerful
                                    add-ons designed to scale with your business needs.
                                </p>
                                <div class="d-flex gap-4 flex-wrap">
                                    <div class="d-flex align-items-center gap-2" style="font-size: 0.875rem; color: hsl(215.4 16.3% 46.9%);">
                                        <i class="ti ti-check" style="color: #008080;"></i>
                                        <span>No commitment</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2" style="font-size: 0.875rem; color: hsl(215.4 16.3% 46.9%);">
                                        <i class="ti ti-check" style="color: #008080;"></i>
                                        <span>Cancel anytime</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2" style="font-size: 0.875rem; color: hsl(215.4 16.3% 46.9%);">
                                        <i class="ti ti-check" style="color: #008080;"></i>
                                        <span>Instant activation</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end mt-3 mt-md-0">
                        <a href="https://wizard.timora.ph/wizard?system=timora&plan=core&trial=true&billingPeriod=monthly"
                            class="portal-link" target="_blank">
                            <i class="ti ti-external-link"></i>
                            <div>
                                <small style="display: block; font-size: 0.75rem; opacity: 0.7;">Manage</small>
                                <strong style="font-size: 0.875rem;">Subscriptions</strong>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Billing Cycle Toggle -->
            <div class="text-center mb-4">
                <div class="billing-toggle">
                    <div class="option active" data-cycle="monthly">
                        Monthly
                    </div>
                    <div class="option" data-cycle="yearly">
                        Yearly <span class="discount-badge">Save 10%</span>
                    </div>
                </div>
            </div>

            <!-- Addons Grid -->
            <div class="row g-4" id="addons-container">
                @forelse($addons as $addon)
                    @php
                        $isActive = isset($activeAddons[$addon->id]);
                        $branchAddonId = $activeAddons[$addon->id] ?? null;
                        $monthlyPrice = $addon->price;
                        $yearlyPrice = $monthlyPrice * 12 * 0.9;
                    @endphp
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="card addon-card {{ $isActive ? 'active' : '' }}" data-addon-id="{{ $addon->id }}">
                            @if ($isActive)
                                <span class="addon-badge">
                                    Active
                                </span>
                            @endif

                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="addon-icon">
                                        <i class="ti ti-{{ $addon->icon ?? 'puzzle' }}"></i>
                                    </div>
                                    <div class="text-start flex-grow-1">
                                        <h5 class="card-title mb-1">{{ $addon->name }}</h5>
                                        <p class="card-description mb-0">{{ $addon->description }}</p>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="price-tag monthly-price">
                                        ₱{{ number_format($monthlyPrice, 2) }}
                                        <small>/month</small>
                                    </div>
                                    <div class="price-tag yearly-price" style="display: none;">
                                        ₱{{ number_format($yearlyPrice, 2) }}
                                        <small>/year</small>
                                    </div>
                                </div>

                                @if ($addon->features)
                                    <ul class="feature-list">
                                        @foreach (json_decode($addon->features, true) ?? [] as $feature)
                                            <li>
                                                <i class="ti ti-check"></i>
                                                <span>{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($isActive)
                                    <button class="btn purchase-btn btn-ghost" disabled>
                                        Currently Active
                                    </button>
                                    <button class="btn mt-2 w-100 cancel-addon-btn btn-destructive"
                                        data-addon-id="{{ $addon->id }}" data-branch-addon-id="{{ $branchAddonId }}">
                                        Cancel
                                    </button>
                                @else
                                    <button class="btn btn-primary purchase-btn purchase-addon-btn"
                                        data-addon-id="{{ $addon->id }}" data-addon-name="{{ $addon->name }}"
                                        data-monthly-price="{{ $monthlyPrice }}" data-yearly-price="{{ $yearlyPrice }}">
                                        Purchase
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="ti ti-package-off fs-48 text-muted mb-3"></i>
                                <h4 class="text-muted">No Add-ons Available</h4>
                                <p class="text-muted">Check back later for new features and enhancements.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Help Section -->
            <div class="card mt-4" style="border: 1px solid hsl(214.3 31.8% 91.4%);">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-start gap-3">
                                <div class="addon-icon" style="margin: 0;">
                                    <i class="ti ti-help-circle"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">Need help choosing?</h5>
                                    <p class="card-description mb-0">
                                        Our team is here to help you select the right add-ons for your business needs.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end mt-3 mt-md-0">
                            <a href="mailto:support@timora.ph" class="btn btn-ghost">
                                <i class="ti ti-mail me-2"></i>Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <!-- SweetAlert2 -->
    <script src="{{ URL::asset('build/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
    
    <script>
        $(document).ready(function () {
            let currentCycle = 'monthly';

                // Billing cycle toggle
                $('.billing-toggle .option').on('click', function () {
                    $('.billing-toggle .option').removeClass('active');
                    $(this).addClass('active');
                    currentCycle = $(this).data('cycle');

                    // Toggle price display
                    if (currentCycle === 'monthly') {
                        $('.monthly-price').show();
                        $('.yearly-price').hide();
                    } else {
                        $('.monthly-price').hide();
                        $('.yearly-price').show();
                    }
                });

                // Purchase addon
                $('.purchase-addon-btn').on('click', function () {
                    const addonId = $(this).data('addon-id');
                    const addonName = $(this).data('addon-name');
                    const price = currentCycle === 'monthly' ? $(this).data('monthly-price') : $(this).data(
                        'yearly-price');

                    Swal.fire({
                        title: 'Confirm Purchase',
                        html: `
                                    <div class="text-start">
                                        <p><strong>Add-on:</strong> ${addonName}</p>
                                        <p><strong>Billing Cycle:</strong> ${currentCycle.charAt(0).toUpperCase() + currentCycle.slice(1)}</p>
                                        <p><strong>Price:</strong> ₱${parseFloat(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</p>
                                        <hr>
                                        <p class="text-muted small">You will be redirected to our secure payment gateway to complete the purchase.</p>
                                    </div>
                                `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Proceed to Payment',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#008080',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return $.ajax({
                                url: '{{ route('addon.purchase') }}',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    addon_id: addonId,
                                    branch_id: '{{ $branch->id }}',
                                    billing_cycle: currentCycle
                                }
                            }).then(response => {
                                if (response.success) {
                                    return response;
                                }
                                throw new Error(response.message || 'Payment initialization failed');
                            }).catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error.responseJSON?.message || error.message}`
                                );
                            });
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            if (result.value.checkoutUrl) {
                                // Redirect to payment gateway
                                window.location.href = result.value.checkoutUrl;
                            } else {
                                // Payment created but no checkout URL (likely development mode)
                                Swal.fire({
                                    title: 'Payment Created',
                                    text: result.value.message || 'Payment record created successfully.',
                                    icon: 'info',
                                    confirmButtonColor: '#008080'
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        }
                    });
                });

                // Cancel addon
                $('.cancel-addon-btn').on('click', function () {
                    const branchAddonId = $(this).data('branch-addon-id');
                    const addonId = $(this).data('addon-id');

                    Swal.fire({
                        title: 'Cancel Add-on?',
                        text: 'Are you sure you want to cancel this add-on? Access will be removed immediately.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Cancel It',
                        cancelButtonText: 'No, Keep It',
                        confirmButtonColor: '#b53654',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return $.ajax({
                                url: '{{ route('addon.cancel') }}',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    branch_addon_id: branchAddonId
                                }
                            }).then(response => {
                                if (response.success) {
                                    return response;
                                }
                                throw new Error(response.message || 'Cancellation failed');
                            }).catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error.responseJSON?.message || error.message}`
                                );
                            });
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Cancelled!',
                                text: 'The add-on has been cancelled successfully.',
                                icon: 'success',
                                confirmButtonColor: '#008080'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    });
                });
            });
        </script>
@endpush