<?php $page = 'affiliate.register'; ?>

@extends('layout.mainlayout')
@section('content')
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .wizard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
            background-color: #ffffff;
        }

        .wizard-header {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .wizard-logo {
            width: 50px;
            height: 50px;
            background: #008080;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .wizard-header-content h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a202c;
        }

        .wizard-header-content p {
            margin: 0;
            font-size: 0.875rem;
            color: #718096;
        }

        .wizard-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .wizard-step-indicator {
            background: #ffffff;
            color: #1a202c;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid #e8ecef;
        }

        .step-circle {
            width: 48px;
            height: 48px;
            background: #008080;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .step-info h2 {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 400;
            opacity: 0.9;
        }

        .step-info h3 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
        }

        .wizard-content {
            padding: 2.5rem;
            background-color: #ffffff;
        }

        .two-column-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .subscription-card {
            background: #fff;
            border: 1px solid #e8ecef;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .subscription-card:hover {
            box-shadow: 0 4px 12px rgba(0, 128, 128, 0.08);
            border-color: rgba(0, 128, 128, 0.2);
        }

        .subscription-card h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1a202c;
        }

        .subscription-card p {
            color: #718096;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .employee-input-group {
            margin-bottom: 1.5rem;
        }

        .employee-input-group label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3748;
        }

        .employee-input-group label small {
            font-weight: 400;
            color: #718096;
        }

        .employee-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .employee-input:focus {
            outline: none;
            border-color: #008080;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 128, 128, 0.1);
        }

        .employee-input:hover {
            border-color: #cbd5e0;
        }

        .features-section h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1a202c;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .feature-category h5 {
            font-weight: 600;
            color: #e53e3e;
            margin-bottom: 0.75rem;
            font-size: 0.9375rem;
        }

        .feature-category.owner h5 {
            color: #dd6b20;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0;
            font-size: 0.875rem;
            color: #4a5568;
        }

        .feature-item i {
            color: #008080;
        }

        .price-display {
            background: linear-gradient(135deg, #008080 0%, #006666 100%);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            text-align: center;
            margin-top: 1.5rem;
        }

        .price-display .label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }

        .price-display .amount {
            font-size: 2rem;
            font-weight: 700;
        }

        .price-display .period {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        /* Sidebar */
        .sidebar-section {
            position: sticky;
            top: 2rem;
        }

        .addon-section {
            background: linear-gradient(to bottom, #ffffff, #fafbfc);
            border: 1px solid #e8ecef;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .addon-section:hover {
            box-shadow: 0 4px 12px rgba(0, 128, 128, 0.08);
            border-color: rgba(0, 128, 128, 0.2);
        }

        .addon-section h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1a202c;
        }

        .addon-section > p {
            color: #718096;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .addon-badge {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #78350f;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(251, 191, 36, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 2px 4px rgba(251, 191, 36, 0.3);
            }
            50% {
                box-shadow: 0 2px 8px rgba(251, 191, 36, 0.5);
            }
        }

        .addon-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f7fafc;
        }

        .addon-item:last-child {
            border-bottom: none;
        }

        .addon-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .addon-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #008080;
            transition: transform 0.2s ease;
        }

        .addon-checkbox:hover {
            transform: scale(1.1);
        }

        .addon-checkbox:checked {
            transform: scale(1.05);
        }

        .addon-label label {
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            color: #2d3748;
            margin: 0;
        }

        .addon-price {
            font-weight: 600;
            color: #1a202c;
            font-size: 0.875rem;
        }

        .pricing-summary {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e8ecef;
            box-shadow: 0 2px 8px rgba(0, 128, 128, 0.04);
            transition: all 0.3s ease;
        }

        .pricing-summary:hover {
            box-shadow: 0 4px 12px rgba(0, 128, 128, 0.08);
            border-color: rgba(0, 128, 128, 0.2);
        }

        .pricing-summary h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1a202c;
        }

        .pricing-summary p {
            color: #718096;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .pricing-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.875rem;
            color: #4a5568;
        }

        .pricing-row.total {
            border-top: 2px solid #e2e8f0;
            margin-top: 0.5rem;
            padding-top: 1rem;
            font-weight: 700;
            font-size: 1rem;
            color: #1a202c;
        }

        .pricing-row.total {
            background: linear-gradient(135deg, rgba(0, 128, 128, 0.05), rgba(0, 128, 128, 0.1));
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin: 0 -1rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .pricing-row.total .amount {
            color: #008080;
            font-size: 1.25rem;
            font-weight: 700;
        }

        /* Step 2 Styles */
        .form-section {
            background: #fffbeb;
            border-left: 3px solid #fbbf24;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
        }

        .form-section h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: #78350f;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .referral-input-group {
            display: flex;
            gap: 0.5rem;
        }

        .referral-input-group input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            background: #f8f9fa;
        }

        .btn-verify {
            background: linear-gradient(135deg, #008080, #006666);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 128, 128, 0.2);
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 128, 128, 0.3);
        }

        .btn-verify:active {
            transform: translateY(0);
        }

        .details-section {
            background: #fff;
            border: 1px solid #e8ecef;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .details-section h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #1a202c;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2d3748;
            font-size: 0.875rem;
        }

        .form-group label i {
            color: #718096;
            font-size: 1rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .form-group input:hover {
            border-color: #cbd5e0;
        }

        .form-group input:focus {
            outline: none;
            border-color: #008080;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 128, 128, 0.1);
        }

        .form-group input.is-invalid {
            border-color: #e53e3e;
            background: #fff5f5;
        }

        .invalid-feedback {
            color: #e53e3e;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: none;
        }

        .form-group input.is-invalid + .invalid-feedback {
            display: block;
        }

        .two-column-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Navigation */
        .wizard-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .btn-prev {
            background: white;
            color: #4a5568;
            padding: 0.75rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-prev:hover {
            border-color: #008080;
            background: rgba(0, 128, 128, 0.05);
            color: #008080;
            transform: translateX(-2px);
        }

        .btn-prev:active {
            transform: translateX(0);
        }

        .btn-prev:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-prev:disabled:hover {
            border-color: #e2e8f0;
            background: white;
            color: #4a5568;
        }

        .step-counter {
            color: #718096;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .btn-next, .btn-submit {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3);
        }

        .btn-next:hover, .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
        }

        .btn-next:active, .btn-submit:active {
            transform: translateY(0);
        }

        .wizard-step {
            display: none;
        }

        .wizard-step.active {
            display: block;
        }

        @media (max-width: 1024px) {
            .two-column-layout {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .sidebar-section {
                position: relative;
                top: 0;
            }
        }

        @media (max-width: 768px) {
            .wizard-content {
                padding: 1.5rem;
            }

            .two-column-form {
                grid-template-columns: 1fr;
            }

            .wizard-navigation {
                padding: 1rem 1.5rem;
            }
        }
    </style>

    <div class="wizard-container">
        <!-- Header -->
        <div class="wizard-header">
            <div class="wizard-logo">T</div>
            <div class="wizard-header-content">
                <h1>Timora</h1>
                <p>Business Registration</p>
            </div>
        </div>

        <!-- Wizard Card -->
        <div class="wizard-card">
            <!-- Step Indicator -->
            <div class="wizard-step-indicator">
                <div class="step-circle">
                    <span id="stepNumber">1</span>
                </div>
                <div class="step-info">
                    <h2>Step <span id="currentStep">1</span> of 2</h2>
                    <h3 id="stepTitle">Plan Summary</h3>
                </div>
            </div>

            <!-- Form -->
            <form id="registrationForm" method="POST" action="{{ route('affiliate-branch-register') }}">
                @csrf
                <input type="hidden" name="role_id" value="2">
                <input type="hidden" name="billing_period" value="monthly">
                <input type="hidden" name="is_trial" value="1">
                <input type="hidden" name="plan_slug" value="starter">

                <!-- Step 1: Plan Summary -->
                <div class="wizard-step active" data-step="1">
                    <div class="wizard-content">
                        <!-- Section Header -->
                        <div style="margin-bottom: 2rem;">
                            <h2 style="font-size: 1.25rem; font-weight: 600; color: #1a202c; margin-bottom: 0.5rem;">Plan Summary</h2>
                        </div>

                        <div class="two-column-layout">
                            <!-- Left Column - Your Subscription and Add-ons -->
                            <div>
                                <!-- Your Subscription Card -->
                                <div class="subscription-card" style="margin-bottom: 2rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                                        <div>
                                            <h3 style="font-size: 1.125rem; font-weight: 600; color: #1a202c; margin-bottom: 0.25rem;">Your Subscription</h3>
                                            <p style="font-size: 0.875rem; color: #718096; margin: 0;">Customize your HR & Payroll needs</p>
                                        </div>
                                        <div style="text-align: right;">
                                            <div style="font-size: 0.75rem; color: #718096; margin-bottom: 0.125rem;">Monthly Total</div>
                                            <div style="font-size: 1.75rem; font-weight: 700; color: #008080;" id="monthlyTotal">₱61.47</div>
                                            <div style="font-size: 0.75rem; color: #718096;">VAT Inclusive</div>
                                        </div>
                                    </div>

                                    <div class="employee-input-group">
                                        <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span style="display: flex; align-items: center; gap: 0.5rem; font-weight: 500; color: #2d3748;">
                                                <i class="bi bi-people"></i> Employees:
                                            </span>
                                            <small style="font-weight: 400; color: #718096;">₱40 per additional</small>
                                        </label>
                                        <input type="number" class="employee-input" id="totalEmployees"
                                               name="total_employees" value="1" min="1"
                                               data-price-per-user="49">
                                    </div>
                                </div>

                                <!-- Add-on Features Card -->
                                <div class="subscription-card">
                                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                                        <h3 style="font-size: 1.125rem; font-weight: 600; color: #1a202c; margin: 0;">Add-on Features</h3>
                                        <span style="background: #fbbf24; color: #78350f; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Optional</span>
                                    </div>

                                    <div id="addonsList">
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status" style="color: #008080 !important;">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Included Features and Pricing Summary -->
                            <div class="sidebar-section">
                                <!-- Included Features Card -->
                                <div class="subscription-card" style="margin-bottom: 1.5rem;">
                                    <h3 style="font-size: 1.125rem; font-weight: 600; color: #1a202c; margin-bottom: 1.25rem;">Included Features</h3>

                                    <div style="margin-bottom: 1.5rem;">
                                        <h5 style="font-weight: 600; color: #e53e3e; margin-bottom: 0.75rem; font-size: 0.9375rem;">Employee Access</h5>
                                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #e53e3e; margin-top: 0.125rem;">•</span>
                                                <span>Time Keeping (Check-in & Check-out)</span>
                                            </li>
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #e53e3e; margin-top: 0.125rem;">•</span>
                                                <span>Payslip View & Download</span>
                                            </li>
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #e53e3e; margin-top: 0.125rem;">•</span>
                                                <span>Attendance Photo Capture</span>
                                            </li>
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #e53e3e; margin-top: 0.125rem;">•</span>
                                                <span>Leave and Overtime Filing</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div>
                                        <h5 style="font-weight: 600; color: #dd6b20; margin-bottom: 0.75rem; font-size: 0.9375rem;">Owner Access</h5>
                                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #dd6b20; margin-top: 0.125rem;">•</span>
                                                <span>Government Report Generator</span>
                                            </li>
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #dd6b20; margin-top: 0.125rem;">•</span>
                                                <span>Employee List View</span>
                                            </li>
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #dd6b20; margin-top: 0.125rem;">•</span>
                                                <span>Payroll Process</span>
                                            </li>
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #dd6b20; margin-top: 0.125rem;">•</span>
                                                <span>Create Employee</span>
                                            </li>
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #dd6b20; margin-top: 0.125rem;">•</span>
                                                <span>Geotagging + Location Tracking</span>
                                            </li>
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #dd6b20; margin-top: 0.125rem;">•</span>
                                                <span>Earnings & Deductions</span>
                                            </li>
                                            <li style="display: flex; align-items: start; gap: 0.5rem; font-size: 0.875rem; color: #4a5568;">
                                                <span style="color: #dd6b20; margin-top: 0.125rem;">•</span>
                                                <span>Flexible Shift Scheduling</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Pricing Summary Card -->
                                <div class="pricing-summary">
                                    <h5>Pricing Summary</h5>

                                    <div class="pricing-row" style="margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.875rem;">Base Price</span>
                                        <span id="basePrice" style="font-weight: 600;">₱54.88</span>
                                    </div>
                                    <div class="pricing-row" style="margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.875rem;">Employees (<span id="empCount">0</span>)</span>
                                        <span id="empPrice" style="font-weight: 600;">₱0.00</span>
                                    </div>
                                    <div class="pricing-row" style="margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.875rem;">Add-ons (<span id="addonCount">0</span>)</span>
                                        <span id="addonPrice" style="font-weight: 600;">₱0.00</span>
                                    </div>

                                    <div style="border-top: 1px solid #e2e8f0; margin: 0.75rem 0; padding-top: 0.75rem;">
                                        <div class="pricing-row" style="margin-bottom: 0.5rem;">
                                            <span style="font-size: 0.875rem;">Subtotal</span>
                                            <span id="subtotal" style="font-weight: 600;">₱54.88</span>
                                        </div>
                                        <div class="pricing-row" style="margin-bottom: 0.75rem;">
                                            <span style="font-size: 0.875rem;">VAT (12%)</span>
                                            <span id="vat" style="font-weight: 600;">₱6.59</span>
                                        </div>
                                    </div>

                                    <div class="pricing-row total">
                                        <span style="font-weight: 600;">Total Monthly</span>
                                        <span class="amount" id="totalPrice">₱61.47</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Basic Information -->
                <div class="wizard-step" data-step="2">
                    <div class="wizard-content">
                        <!-- Referral Code Section (Full Width) -->
                        <div class="form-section" style="margin-bottom: 1.5rem;">
                            <h4>Referral Code (Optional)</h4>
                            <div class="referral-input-group">
                                <input type="text" name="referral_code" id="referralCode"
                                       placeholder="Enter referral code">
                                <button type="button" class="btn-verify" id="verifyBtn">Verify</button>
                            </div>
                            <div id="referralStatus" class="text-success d-none mt-2" style="font-size: 0.875rem;"></div>
                            <div id="referralError" class="text-danger d-none mt-2" style="font-size: 0.875rem;"></div>
                        </div>

                        <!-- Two Column Layout -->
                        <div class="two-column-layout">
                            <!-- Left Column - User Details -->
                            <div class="subscription-card">
                                <h3>User Details</h3>

                                <div class="form-group">
                                    <label><i class="bi bi-person"></i> Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" required placeholder="Enter your full name">
                                    <div class="invalid-feedback">Full name is required</div>
                                </div>

                                <div class="form-group">
                                    <label><i class="bi bi-person-badge"></i> Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" required placeholder="Enter username">
                                    <div class="invalid-feedback" id="errorUsername">Username is required</div>
                                </div>

                                <div class="form-group">
                                    <label><i class="bi bi-envelope"></i> Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" required placeholder="Enter email address">
                                    <div class="invalid-feedback" id="errorEmail">Email is required</div>
                                </div>

                                <div class="form-group">
                                    <label><i class="bi bi-lock"></i> Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" required placeholder="Enter password">
                                    <div class="invalid-feedback">Password is required</div>
                                </div>

                                <div class="form-group">
                                    <label><i class="bi bi-lock-fill"></i> Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_password" required placeholder="Re-enter password">
                                    <div class="invalid-feedback">Passwords must match</div>
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label><i class="bi bi-phone"></i> Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone_number" required placeholder="Enter phone number">
                                    <div class="invalid-feedback">Phone number is required</div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="sidebar-section">
                                <!-- Business Information -->
                                <div class="subscription-card" style="margin-bottom: 1.5rem;">
                                    <h3>Business Information</h3>

                                    <div class="form-group">
                                        <label><i class="bi bi-shop"></i> Company Name <span class="text-danger">*</span></label>
                                        <input type="text" name="branch_name" required placeholder="Enter company name">
                                        <div class="invalid-feedback">Company name is required</div>
                                    </div>

                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label><i class="bi bi-geo-alt"></i> Address <span class="text-danger">*</span></label>
                                        <input type="text" name="branch_location" required placeholder="Enter company address">
                                        <div class="invalid-feedback">Address is required</div>
                                    </div>
                                </div>

                                <!-- Pricing Summary -->
                                <div class="pricing-summary">
                                    <h5>Pricing Summary</h5>
                                    <p>Plan: <strong>Starter</strong></p>

                                    <div class="pricing-row">
                                        <span>Base Price</span>
                                        <span id="basePrice2">₱54.88</span>
                                    </div>
                                    <div class="pricing-row">
                                        <span>Employees (<span id="empCount2">0</span>)</span>
                                        <span id="empPrice2">₱0.00</span>
                                    </div>
                                    <div class="pricing-row">
                                        <span>Add-ons (<span id="addonCount2">0</span>)</span>
                                        <span id="addonPrice2">₱0.00</span>
                                    </div>
                                    <div class="pricing-row">
                                        <span>Subtotal</span>
                                        <span id="subtotal2">₱54.88</span>
                                    </div>
                                    <div class="pricing-row">
                                        <span>VAT (12%)</span>
                                        <span id="vat2">₱6.59</span>
                                    </div>
                                    <div class="pricing-row total">
                                        <span>Total Monthly</span>
                                        <span class="amount" id="totalPrice2">₱61.47</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="wizard-navigation">
                    <button type="button" class="btn-prev" id="prevBtn" disabled>
                        <i class="bi bi-arrow-left"></i> Previous
                    </button>
                    <div class="step-counter">
                        Step <span id="navStep">1</span> of 2
                    </div>
                    <button type="button" class="btn-next" id="nextBtn">
                        Next Step <i class="bi bi-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn-submit d-none" id="submitBtn">
                        <i class="bi bi-check-circle"></i> Save Company
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentStep = 1;
            const totalSteps = 2;

            // Load add-ons
            function loadAddons() {
                $.get('{{ route("api.affiliate-addons") }}', function(data) {
                    let html = '';
                    if (data && Array.isArray(data.addons) && data.addons.length) {
                        data.addons.forEach(function(addon) {
                            const price = parseFloat(addon.price).toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            html += `
                                <div class="addon-item">
                                    <div class="addon-label">
                                        <input type="checkbox" class="addon-checkbox"
                                               name="features[]" value="${addon.addon_key}"
                                               data-addon-key="${addon.addon_key}"
                                               data-price="${addon.price}">
                                        <label>${addon.name}</label>
                                    </div>
                                    <div class="addon-price">₱${price}</div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<p class="text-muted text-center">No add-ons available</p>';
                    }
                    $('#addonsList').html(html);

                    // Bind change event
                    $('.addon-checkbox').on('change', calculatePricing);
                }).fail(function() {
                    $('#addonsList').html('<p class="text-danger text-center">Failed to load add-ons</p>');
                });
            }

            // Calculate pricing
            function calculatePricing() {
                const basePrice = 54.88;
                const employees = parseInt($('#totalEmployees').val()) || 1;
                const pricePerUser = 49;

                // Calculate employee cost (first employee included in base)
                const employeeCost = Math.max(0, employees - 1) * pricePerUser;

                // Calculate addons cost
                let addonsCost = 0;
                let addonCount = 0;
                $('.addon-checkbox:checked').each(function() {
                    addonsCost += parseFloat($(this).data('price')) || 0;
                    addonCount++;
                });

                const subtotal = basePrice + employeeCost + addonsCost;
                const vat = subtotal * 0.12;
                const total = subtotal + vat;

                // Update display
                function formatPrice(amount) {
                    return '₱' + amount.toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                $('#basePrice, #basePrice2').text(formatPrice(basePrice));
                $('#empCount, #empCount2').text(employees - 1);
                $('#empPrice, #empPrice2').text(formatPrice(employeeCost));
                $('#addonCount, #addonCount2').text(addonCount);
                $('#addonPrice, #addonPrice2').text(formatPrice(addonsCost));
                $('#subtotal, #subtotal2').text(formatPrice(subtotal));
                $('#vat, #vat2').text(formatPrice(vat));
                $('#totalPrice, #totalPrice2, #monthlyTotal').text(formatPrice(total));
            }

            // Step navigation
            function updateStep(step) {
                currentStep = step;

                // Update visibility
                $('.wizard-step').removeClass('active');
                $(`.wizard-step[data-step="${step}"]`).addClass('active');

                // Update indicators
                $('#stepNumber, #currentStep, #navStep').text(step);
                $('#stepTitle').text(step === 1 ? 'Plan Summary' : 'Basic Information');

                // Update buttons
                $('#prevBtn').prop('disabled', step === 1);
                if (step === totalSteps) {
                    $('#nextBtn').addClass('d-none');
                    $('#submitBtn').removeClass('d-none');
                } else {
                    $('#nextBtn').removeClass('d-none');
                    $('#submitBtn').addClass('d-none');
                }
            }

            // Validation
            function validateStep2() {
                let valid = true;
                $('.wizard-step[data-step="2"] input[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        valid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                // Password match
                const pw = $('input[name="password"]').val();
                const cpw = $('input[name="confirm_password"]').val();
                if (pw !== cpw) {
                    $('input[name="confirm_password"]').addClass('is-invalid');
                    valid = false;
                }

                return valid;
            }

            // Events
            $('#nextBtn').click(function() {
                if (currentStep < totalSteps) {
                    updateStep(currentStep + 1);
                }
            });

            $('#prevBtn').click(function() {
                if (currentStep > 1) {
                    updateStep(currentStep - 1);
                }
            });

            $('#totalEmployees').on('input change', calculatePricing);

            // Remove validation on input
            $('input[required]').on('input', function() {
                if ($(this).val()) {
                    $(this).removeClass('is-invalid');
                }
            });

            // Configure toastr options
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 3000,
                    showMethod: 'fadeIn',
                    hideMethod: 'fadeOut'
                };
            }

            // Referral code verification
            $('#verifyBtn').click(function() {
                const code = $('#referralCode').val() || 'AFLJDGI'; // Use default if empty

                $.ajax({
                    url: '{{ route("verify.referral.code") }}',
                    type: 'POST',
                    data: { referral_code: code, _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            const displayCode = $('#referralCode').val() ? code : 'default referral code';
                            $('#referralStatus').text('Referral code is valid (' + displayCode + ')').removeClass('d-none');
                            $('#referralError').addClass('d-none');

                            // Show success toast
                            if (typeof toastr !== 'undefined') {
                                toastr.success('Referral code verified successfully!', 'Success');
                            }
                        } else {
                            $('#referralError').text(response.message).removeClass('d-none');
                            $('#referralStatus').addClass('d-none');

                            // Show error toast
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || 'Invalid referral code', 'Error');
                            }
                        }
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON?.message || 'Invalid referral code';
                        $('#referralError').text(errorMessage).removeClass('d-none');
                        $('#referralStatus').addClass('d-none');

                        // Show error toast
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMessage, 'Error');
                        }
                    }
                });
            });

            // Form submission
            $('#registrationForm').submit(function(e) {
                e.preventDefault();

                if (currentStep === 2 && !validateStep2()) {
                    return;
                }

                const formData = new FormData(this);

                // Collect features
                formData.delete('features[]');
                let features = [];
                $('.addon-checkbox:checked').each(function() {
                    features.push({
                        addon_key: $(this).data('addon-key'),
                        start_date: null,
                        end_date: null
                    });
                });

                features.forEach(function(feature, idx) {
                    for (const key in feature) {
                        if (feature[key] !== undefined && feature[key] !== null) {
                            formData.append(`features[${idx}][${key}]`, feature[key]);
                        }
                    }
                });

                $.ajax({
                    url: "{{ url('/api/affiliate/branch/register') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            if (response.payment_checkout_url) {
                                window.location.href = response.payment_checkout_url;
                            }
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        if (errors.username) {
                            $('input[name="username"]').addClass('is-invalid');
                            $('#errorUsername').text(errors.username[0]).show();
                        }
                        if (errors.email) {
                            $('input[name="email"]').addClass('is-invalid');
                            $('#errorEmail').text(errors.email[0]).show();
                        }
                    }
                });
            });

            // Initialize
            loadAddons();
            calculatePricing();
        });
    </script>

    <!-- Toastr JS (fallback if not loaded in layout) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@endsection
