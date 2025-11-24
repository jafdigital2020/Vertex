<?php $page = 'addons'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <style>
        /* Modern card design inspired by the screenshot */
        .addons-page-bg {
            background: #f8fafb;
            min-height: 100vh;
        }

        /* Stats Cards */
        .stats-container {
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.25rem;
            border: 1px solid #e8ecef;
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 128, 128, 0.1);
            border-color: #008080;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, rgba(0, 128, 128, 0.1) 0%, rgba(18, 81, 93, 0.05) 100%);
            color: #008080;
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, rgba(237, 116, 100, 0.1) 0%, rgba(255, 180, 0, 0.05) 100%);
            color: #ed7464;
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, rgba(255, 180, 0, 0.1) 0%, rgba(237, 116, 100, 0.05) 100%);
            color: #FFB400;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #12515D;
            margin-bottom: 0;
        }

        /* Addon Cards */
        .addon-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e8ecef;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .addon-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: transparent;
            transition: all 0.3s ease;
        }

        .addon-card.status-active::before {
            background: linear-gradient(90deg, #008080, #12515D);
        }

        .addon-card.status-popular::before {
            background: linear-gradient(90deg, #FFB400, #ed7464);
        }

        .addon-card:hover {
            transform: translateY(-6px);
            border-color: #008080;
            box-shadow: 0 12px 32px rgba(0, 128, 128, 0.12);
        }

        .addon-card.status-active {
            background: linear-gradient(135deg, rgba(0, 128, 128, 0.02) 0%, #ffffff 100%);
            border-color: rgba(0, 128, 128, 0.25);
        }

        .addon-header {
            padding: 1.5rem 1.5rem 1rem;
        }

        .addon-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-active {
            background: linear-gradient(135deg, #008080, #12515D);
            color: #ffffff;
        }

        .badge-popular {
            background: linear-gradient(135deg, #FFB400, #ed7464);
            color: #ffffff;
        }

        .addon-icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, rgba(0, 128, 128, 0.1) 0%, rgba(18, 81, 93, 0.05) 100%);
            color: #008080;
        }

        .addon-card.status-popular .addon-icon-wrapper {
            background: linear-gradient(135deg, rgba(255, 180, 0, 0.1) 0%, rgba(237, 116, 100, 0.05) 100%);
            color: #FFB400;
        }

        .addon-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #12515D;
            margin-bottom: 0.5rem;
        }

        .addon-description {
            font-size: 0.875rem;
            color: #6c757d;
            line-height: 1.5;
            margin-bottom: 1rem;
            min-height: 40px;
        }

        .addon-price {
            font-size: 1.75rem;
            font-weight: 700;
            color: #12515D;
            margin-bottom: 0.25rem;
        }

        .addon-price small {
            font-size: 0.875rem;
            font-weight: 400;
            color: #6c757d;
        }

        .addon-body {
            padding: 0 1.5rem 1rem;
            flex-grow: 1;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .feature-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            font-size: 0.875rem;
            color: #495057;
        }

        .feature-list li i {
            color: #008080;
            font-size: 1rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .addon-footer {
            padding: 0 1.5rem 1.5rem;
            margin-top: auto;
        }

        .btn-addon {
            width: 100%;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-get-started {
            color: #ffffff;
        }

        .btn-get-started:hover {
            transform: translateY(-2px);
            color: #ffffff;
        }

        .btn-manage {
            background: transparent;
            color: #008080;
            border: 2px solid #008080;
        }

        .btn-manage:hover {
            transform: translateY(-2px);
            background: rgba(0, 128, 128, 0.05);
            color: #006666;
            border-color: #006666;
        }

        .btn-cancel {
            background: #ffffff;
            border: 1px solid #e8ecef;
            color: #12515D;
            margin-top: 0.625rem;
        }

        .btn-cancel:hover {
            background: #f8f9fa;
            border-color: #b53654;
            color: #b53654;
        }

        /* Swiper Customization */
        .swiper {
            padding: 1.5rem 0 2.5rem;
            overflow: visible;
        }

        .swiper.addonsSwiper {
            display: none;
        }

        .swiper.addonsSwiper.active {
            display: block;
        }

        .swiper-button-next,
        .swiper-button-prev {
            width: 44px;
            height: 44px;
            background: #ffffff;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8ecef;
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 1.125rem;
            color: #008080;
            font-weight: 700;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: #008080;
            border-color: #008080;
        }

        .swiper-button-next:hover:after,
        .swiper-button-prev:hover:after {
            color: #ffffff;
        }

        .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: #cbd5e0;
            opacity: 1;
        }

        .swiper-pagination-bullet-active {
            background: linear-gradient(135deg, #008080, #12515D);
            width: 28px;
            border-radius: 5px;
        }

        /* Support Section */
        .support-section {
            background: #12515D;
            border-radius: 16px;
            padding: 2.5rem;
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
            color: #ffffff;
            border: 1px solid rgba(0, 128, 128, 0.2);
        }

        .support-content {
            position: relative;
            z-index: 1;
        }

        .support-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1.25rem;
        }

        .btn-contact {
            background: #008080;
            color: #ffffff;
            padding: 0.75rem 1.75rem;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            font-size: 0.875rem;
            box-shadow: 0 2px 8px rgba(0, 128, 128, 0.2);
        }

        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 128, 128, 0.3);
            background: #006666;
            color: #ffffff;
        }

        /* View Toggle Buttons */
        .view-toggle-wrapper {
            display: flex;
            gap: 0.5rem;
            background: #ffffff;
            padding: 0.25rem;
            border-radius: 8px;
            border: 1px solid #e8ecef;
        }

        .view-toggle-btn {
            padding: 0.5rem 0.75rem;
            border: none;
            background: transparent;
            color: #6c757d;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .view-toggle-btn:hover {
            background: #f8f9fa;
            color: #12515D;
        }

        .view-toggle-btn.active {
            background: linear-gradient(135deg, #008080 0%, #12515D 100%);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 128, 128, 0.2);
        }

        /* Grid View Layout */
        .addons-grid-container {
            display: none;
        }

        .addons-grid-container.active {
            display: block;
        }

        .addons-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        /* Random Button Colors - Solid Colors */
        .btn-get-started.color-teal {
            background: #008080;
            box-shadow: 0 2px 8px rgba(0, 128, 128, 0.2);
        }

        .btn-get-started.color-teal:hover {
            background: #006666;
            box-shadow: 0 4px 12px rgba(0, 128, 128, 0.3);
        }

        .btn-get-started.color-coral {
            background: #ed7464;
            box-shadow: 0 2px 8px rgba(237, 116, 100, 0.2);
        }

        .btn-get-started.color-coral:hover {
            background: #e55a47;
            box-shadow: 0 4px 12px rgba(237, 116, 100, 0.3);
        }

        .btn-get-started.color-mustard {
            background: #FFB400;
            box-shadow: 0 2px 8px rgba(255, 180, 0, 0.2);
        }

        .btn-get-started.color-mustard:hover {
            background: #e6a200;
            box-shadow: 0 4px 12px rgba(255, 180, 0, 0.3);
        }

        .btn-get-started.color-raspberry {
            background: #b53654;
            box-shadow: 0 2px 8px rgba(181, 54, 84, 0.2);
        }

        .btn-get-started.color-raspberry:hover {
            background: #9e2e47;
            box-shadow: 0 4px 12px rgba(181, 54, 84, 0.3);
        }

        .btn-get-started.color-forest {
            background: #12515D;
            box-shadow: 0 2px 8px rgba(18, 81, 93, 0.2);
        }

        .btn-get-started.color-forest:hover {
            background: #0d3a43;
            box-shadow: 0 4px 12px rgba(18, 81, 93, 0.3);
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .addons-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 1200px) {
            .addons-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .addons-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .addon-card {
                margin-bottom: 1.5rem;
            }

            .swiper-button-next,
            .swiper-button-prev {
                display: none;
            }

            .support-section {
                padding: 2rem 1.5rem;
            }

            .addons-grid {
                grid-template-columns: 1fr;
            }

            .view-toggle-wrapper {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content addons-page-bg">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Add-ons</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('/') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Subscription</li>
                            <li class="breadcrumb-item active" aria-current="page">Add-ons</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <!-- View Toggle -->
                    <div class="view-toggle-wrapper">
                        <button class="view-toggle-btn" id="carousel-view-btn" data-view="carousel">
                            <i class="ti ti-layout-carousel"></i>
                            <span class="d-none d-sm-inline">Carousel</span>
                        </button>
                        <button class="view-toggle-btn active" id="grid-view-btn" data-view="grid">
                            <i class="ti ti-layout-grid"></i>
                            <span class="d-none d-sm-inline">Grid</span>
                        </button>
                    </div>
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Page Header -->
            <div class="card mb-4"
                style="background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid rgba(0, 128, 128, 0.15); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">
                <div class="card-body p-4 position-relative">
                    <div
                        style="position: absolute; top: -30%; right: -5%; width: 200px; height: 200px; background: rgba(0, 128, 128, 0.03); border-radius: 50%;">
                    </div>
                    <div class="row align-items-center position-relative" style="z-index: 1;">
                        <div class="col-md-9">
                            <div class="d-flex align-items-start gap-3">
                                <div
                                    style="width: 56px; height: 56px; background: #008080; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: #ffffff;">
                                    <i class="ti ti-puzzle"></i>
                                </div>
                                <div>
                                    <h4 class="mb-2" style="font-weight: 700; color: #12515D;">Add-ons</h4>
                                    <p class="mb-3" style="color: #6c757d; font-size: 0.9375rem;">
                                        Powerful add-ons designed to scale with your business. Pay only for what you need.
                                    </p>
                                    <div class="d-flex gap-4 flex-wrap" style="font-size: 0.875rem;">
                                        <div class="d-flex align-items-center gap-2" style="color: #495057;">
                                            <i class="ti ti-check" style="color: #008080; font-weight: 600;"></i>
                                            <span>No commitment</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2" style="color: #495057;">
                                            <i class="ti ti-check" style="color: #008080; font-weight: 600;"></i>
                                            <span>Cancel anytime</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2" style="color: #495057;">
                                            <i class="ti ti-check" style="color: #008080; font-weight: 600;"></i>
                                            <span>Instant activation</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-md-end mt-3 mt-md-0">
                            <a href="{{ url('/billing') }}" class="btn btn-contact">
                                <i class="ti ti-settings me-2"></i>Manage
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="ti ti-package"></i>
                            </div>
                            <div class="stat-label">Active Add-ons</div>
                            <h3 class="stat-value">{{ count($activeAddons) }}</h3>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="ti ti-currency-peso"></i>
                            </div>
                            <div class="stat-label">Monthly Cost</div>
                            <h3 class="stat-value">
                                ₱{{ number_format($addons->whereIn('id', array_keys($activeAddons))->sum('price'), 2) }}
                            </h3>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="ti ti-box"></i>
                            </div>
                            <div class="stat-label">Available Add-ons</div>
                            <h3 class="stat-value">{{ count($addons) - count($activeAddons) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Addons Carousel -->
            <div class="swiper addonsSwiper" id="carousel-container">
                <div class="swiper-wrapper">
                    @php
                        $buttonColors = ['color-teal', 'color-coral', 'color-mustard', 'color-raspberry', 'color-forest'];
                        $carouselColorIndex = 0;
                    @endphp
                    @forelse($addons as $addon)
                        @php
                            $isActive = isset($activeAddons[$addon->id]);
                            $branchAddonId = $activeAddons[$addon->id] ?? null;
                            $monthlyPrice = $addon->price;
                            $isPopular = in_array($addon->addon_key, ['payroll_batch_processing', 'asset_management_tracking']);
                            $carouselButtonColor = $buttonColors[$carouselColorIndex % count($buttonColors)];
                            $carouselColorIndex++;
                        @endphp
                        <div class="swiper-slide">
                            <div
                                class="addon-card {{ $isActive ? 'status-active' : '' }} {{ $isPopular && !$isActive ? 'status-popular' : '' }}">
                                @if ($isActive)
                                    <span class="addon-badge badge-active">Active</span>
                                @elseif($isPopular)
                                    <span class="addon-badge badge-popular">Popular</span>
                                @endif

                                <div class="addon-header">
                                    <div class="addon-icon-wrapper">
                                        <i class="ti ti-{{ $addon->icon ?? 'puzzle' }}"></i>
                                    </div>
                                    <h3 class="addon-title">{{ $addon->name }}</h3>
                                    <p class="addon-description">{{ $addon->description }}</p>
                                    <div class="addon-price">
                                        ₱{{ number_format($monthlyPrice, 2) }}
                                        <small>{{ $addon->type === 'one_time' ? ' (one-time)' : '/month' }}</small>
                                    </div>
                                </div>

                                <div class="addon-body">
                                    @if ($addon->features)
                                        <ul class="feature-list">
                                            @foreach ($addon->features ?? [] as $feature)
                                                <li>
                                                    <i class="ti ti-circle-check-filled"></i>
                                                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                          title="{{ is_array($feature) ? ($feature['tooltip'] ?? '') : '' }}"
                                                          style="cursor: help;">
                                                        {{ is_array($feature) ? $feature['title'] : $feature }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <div class="addon-footer">
                                    @if ($isActive)
                                        <button class="btn btn-addon btn-manage" disabled>
                                            <i class="ti ti-check me-2"></i>Manage Subscription
                                        </button>
                                        <button class="btn btn-addon btn-cancel cancel-addon-btn" data-addon-id="{{ $addon->id }}"
                                            data-branch-addon-id="{{ $branchAddonId }}" data-addon-name="{{ $addon->name }}">
                                            <i class="ti ti-x me-2"></i>Cancel
                                        </button>
                                    @else
                                        <button class="btn btn-addon btn-get-started {{ $carouselButtonColor }} purchase-addon-btn"
                                            data-addon-id="{{ $addon->id }}" data-addon-name="{{ $addon->name }}"
                                            data-monthly-price="{{ $monthlyPrice }}">
                                            <i class="ti ti-rocket me-2"></i>Get Started
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <div class="text-center py-5">
                                <i class="ti ti-package-off" style="font-size: 3.5rem; color: #cbd5e0;"></i>
                                <h5 class="mt-3" style="color: #6c757d;">No Add-ons Available</h5>
                                <p style="color: #adb5bd;">Check back later for new features</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>

                <!-- Pagination -->
                <div class="swiper-pagination"></div>
            </div>

            <!-- Addons Grid View -->
            <div class="addons-grid-container active" id="grid-container">
                <div class="addons-grid">
                    @php
                        $buttonColors = ['color-teal', 'color-coral', 'color-mustard', 'color-raspberry', 'color-forest'];
                        $colorIndex = 0;
                    @endphp
                    @forelse($addons as $addon)
                        @php
                            $isActive = isset($activeAddons[$addon->id]);
                            $branchAddonId = $activeAddons[$addon->id] ?? null;
                            $monthlyPrice = $addon->price;
                            $isPopular = in_array($addon->addon_key, ['payroll_batch_processing', 'asset_management_tracking']);
                            $buttonColor = $buttonColors[$colorIndex % count($buttonColors)];
                            $colorIndex++;
                        @endphp
                        <div
                            class="addon-card {{ $isActive ? 'status-active' : '' }} {{ $isPopular && !$isActive ? 'status-popular' : '' }}">
                            @if ($isActive)
                                <span class="addon-badge badge-active">Active</span>
                            @elseif($isPopular)
                                <span class="addon-badge badge-popular">Popular</span>
                            @endif

                            <div class="addon-header">
                                <div class="addon-icon-wrapper">
                                    <i class="ti ti-{{ $addon->icon ?? 'puzzle' }}"></i>
                                </div>
                                <h3 class="addon-title">{{ $addon->name }}</h3>
                                <p class="addon-description">{{ $addon->description }}</p>
                                <div class="addon-price">
                                    ₱{{ number_format($monthlyPrice, 2) }}
                                    <small>{{ $addon->type === 'one_time' ? ' (one-time)' : '/month' }}</small>
                                </div>
                            </div>

                            <div class="addon-body">
                                @if ($addon->features)
                                    <ul class="feature-list">
                                        @foreach ($addon->features ?? [] as $feature)
                                            <li>
                                                <i class="ti ti-circle-check-filled"></i>
                                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                      title="{{ is_array($feature) ? ($feature['tooltip'] ?? '') : '' }}"
                                                      style="cursor: help;">
                                                    {{ is_array($feature) ? $feature['title'] : $feature }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="addon-footer">
                                @if ($isActive)
                                    <button class="btn btn-addon btn-manage" disabled>
                                        <i class="ti ti-check me-2"></i>Manage Subscription
                                    </button>
                                    <button class="btn btn-addon btn-cancel cancel-addon-btn" data-addon-id="{{ $addon->id }}"
                                        data-branch-addon-id="{{ $branchAddonId }}" data-addon-name="{{ $addon->name }}">
                                        <i class="ti ti-x me-2"></i>Cancel
                                    </button>
                                @else
                                    <button class="btn btn-addon btn-get-started {{ $buttonColor }} purchase-addon-btn"
                                        data-addon-id="{{ $addon->id }}" data-addon-name="{{ $addon->name }}"
                                        data-monthly-price="{{ $monthlyPrice }}">
                                        <i class="ti ti-rocket me-2"></i>Get Started
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="ti ti-package-off" style="font-size: 3.5rem; color: #cbd5e0;"></i>
                            <h5 class="mt-3" style="color: #6c757d;">No Add-ons Available</h5>
                            <p style="color: #adb5bd;">Check back later for new features</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Support Section -->
            <div class="support-section">
                <div class="support-content">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="support-icon">
                                <i class="ti ti-headset"></i>
                            </div>
                            <h4 style="font-weight: 700; margin-bottom: 0.75rem; color: #ffffff;">Need help choosing the
                                right add-ons?</h4>
                            <p style="font-size: 0.9375rem; margin-bottom: 1.25rem; opacity: 0.95;">
                                Our team of experts is ready to help you select the perfect combination of add-ons for your
                                business needs.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="mailto:sales@timora.ph" class="btn btn-contact w-100 w-md-auto">
                                <i class="ti ti-mail me-2"></i>Talk to Sales
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="{{ URL::asset('build/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>

    <script>
        // Initialize Swiper
        const swiper = new Swiper('.addonsSwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: false,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 24,
                },
            },
        });

        $(document).ready(function () {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // View Toggle Functionality
            $('.view-toggle-btn').on('click', function () {
                const view = $(this).data('view');

                // Update active state
                $('.view-toggle-btn').removeClass('active');
                $(this).addClass('active');

                // Toggle views
                if (view === 'carousel') {
                    $('#grid-container').removeClass('active');
                    $('#carousel-container').addClass('active');
                    swiper.update(); // Refresh swiper
                } else {
                    $('#carousel-container').removeClass('active');
                    $('#grid-container').addClass('active');
                }
            });

            // Purchase addon
            $('.purchase-addon-btn').on('click', function () {
                const addonId = $(this).data('addon-id');
                const addonName = $(this).data('addon-name');
                const price = $(this).data('monthly-price');

                Swal.fire({
                    title: 'Confirm Purchase',
                    html: `
                                <div class="text-start" style="padding: 1rem;">
                                    <div style="background: linear-gradient(135deg, rgba(0, 128, 128, 0.1) 0%, rgba(18, 81, 93, 0.05) 100%);
                                                padding: 1.25rem; border-radius: 12px; margin-bottom: 1.25rem;">
                                        <h5 style="color: #12515D; margin-bottom: 0.5rem; font-weight: 600;">${addonName}</h5>
                                        <div style="font-size: 1.75rem; font-weight: 700; color: #008080;">
                                            ₱${parseFloat(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
                                            <small style="font-size: 0.875rem; color: #6c757d;">/month</small>
                                        </div>
                                    </div>
                                    <p style="color: #6c757d; font-size: 0.875rem; margin-bottom: 0;">
                                        <i class="ti ti-info-circle me-2" style="color: #008080;"></i>
                                        You will be redirected to our secure payment gateway to complete the purchase.
                                    </p>
                                </div>
                            `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="ti ti-rocket me-2"></i>Proceed to Payment',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#008080',
                    cancelButtonColor: '#6c757d',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            url: '{{ route('addon.purchase') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                addon_id: addonId,
                                branch_id: '{{ $branch->id }}',
                                billing_cycle: 'monthly'
                            }
                        }).then(response => {
                            if (response.success) {
                                return response;
                            }
                            throw new Error(response.message || 'Payment initialization failed');
                        }).catch(error => {
                            // Check if it's a redirect response (pending payment exists)
                            if (error.status === 409 && error.responseJSON?.redirect) {
                                Swal.close();
                                Swal.fire({
                                    title: 'Pending Payment Found',
                                    html: `
                                        <div style="text-align: center; padding: 1rem;">
                                            <div style="font-size: 3rem; color: #FFB400; margin-bottom: 1rem;">
                                                <i class="ti ti-clock-hour-4"></i>
                                            </div>
                                            <p style="color: #12515D; font-size: 1rem; margin-bottom: 1.5rem;">
                                                ${error.responseJSON.message}
                                            </p>
                                            <p style="color: #6c757d; font-size: 0.875rem;">
                                                You will be redirected to the billing page where you can complete your payment.
                                            </p>
                                        </div>
                                    `,
                                    icon: 'info',
                                    confirmButtonText: 'Go to Billing',
                                    confirmButtonColor: '#008080',
                                    allowOutsideClick: false
                                }).then(() => {
                                    window.location.href = error.responseJSON.redirect_url;
                                });
                                return;
                            }

                            Swal.showValidationMessage(
                                `Request failed: ${error.responseJSON?.message || error.message}`
                            );
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        if (result.value.checkoutUrl) {
                            window.location.href = result.value.checkoutUrl;
                        } else {
                            Swal.fire({
                                title: 'Success!',
                                text: result.value.message || 'Add-on activated successfully.',
                                icon: 'success',
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
                const addonName = $(this).data('addon-name');

                Swal.fire({
                    title: 'Cancel Add-on?',
                    html: `
                                <div class="text-start" style="padding: 1rem;">
                                    <p style="font-size: 1rem; color: #12515D; margin-bottom: 1rem;">
                                        Are you sure you want to cancel <strong>${addonName}</strong>?
                                    </p>
                                    <div style="background: rgba(181, 54, 84, 0.1); padding: 1rem; border-radius: 8px; border-left: 3px solid #b53654;">
                                        <p style="color: #6c757d; margin-bottom: 0; font-size: 0.875rem;">
                                            <i class="ti ti-alert-triangle me-2" style="color: #b53654;"></i>
                                            Access will be removed immediately and you will lose all associated features.
                                        </p>
                                    </div>
                                </div>
                            `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="ti ti-x me-2"></i>Yes, Cancel It',
                    cancelButtonText: 'Keep It',
                    confirmButtonColor: '#b53654',
                    cancelButtonColor: '#6c757d',
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