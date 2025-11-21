<?php $page = 'affiliate.register'; ?>

@extends('layout.mainlayout')
@section('content')
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'timora-teal': '#008080',
                        'timora-dark': '#12515D',
                        'timora-orange': '#ed7464',
                        'timora-yellow': '#FFB400',
                        'employee-red': '#b53654'
                    }
                }
            }
        }
    </script>

    <style>
        .wizard-step { display: none; }
        .wizard-step.active { display: block; }
        .addon-item.selected { 
            border-left: 3px solid #008080 !important; 
            background: white !important; 
        }
    </style>

    <!-- Header -->
    <header class="w-full shadow-md border-b border-gray-200 bg-white">
        <div class="flex items-center justify-between px-4 sm:px-6 lg:px-16 xl:px-20 py-3">
            <!-- Logo -->
            <div onclick="window.location.href = 'https://jafdigital.co'" class="cursor-pointer">
                <img src="https://jafdigital.co/wp-content/uploads/2023/05/JAF-New-logo-300x300.png" alt="JAF Digital" class="h-8 w-auto">
            </div>

            <!-- Help link -->
            <div onclick="window.open('https://jafdigital.co/contact-us/')" class="text-xs sm:text-sm md:text-base text-gray-700 hover:underline cursor-pointer">
                Need Help?
            </div>
        </div>
    </header>

    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-3 sm:p-6">
        <div class="w-full max-w-6xl">
            <!-- Header -->
            <div class="mb-4 sm:mb-8">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-timora-teal rounded-lg flex items-center justify-center text-white font-semibold text-base sm:text-lg">T</div>
                    <div>
                        <div class="text-lg sm:text-xl font-semibold text-timora-dark">Timora</div>
                        <div class="text-xs text-gray-500">Business Registration</div>
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <div class="bg-white border border-gray-200 shadow-2xl rounded-lg">
                <!-- Step Indicator -->
                <div class="px-3 py-3 sm:px-6 sm:py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-timora-teal rounded-lg flex items-center justify-center text-white text-xs sm:text-sm font-medium">
                            <span id="stepNumber">1</span>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm text-gray-500">Step <span id="currentStep">1</span> of 2</div>
                            <div class="text-sm sm:font-medium text-timora-dark" id="stepTitle">Plan Summary</div>
                        </div>
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
                        <div class="p-3 sm:p-4">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-5">
                                <!-- Left Column - Plan Summary & Add-on Features -->
                                <div class="lg:col-span-7 space-y-3 sm:space-y-4">
                                    <!-- Plan Summary -->
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-3 space-y-2 sm:space-y-0">
                                            <div>
                                                <div class="font-medium mb-1 text-timora-dark text-sm sm:text-base">Your Subscription</div>
                                                <div class="text-xs text-gray-500">Customize your HR & Payroll needs</div>
                                            </div>
                                            <div class="text-left sm:text-right">
                                                <div class="text-xs text-gray-500 mb-1">Monthly Total</div>
                                                <div class="text-xl sm:text-2xl font-semibold text-timora-teal" id="monthlyTotal">₱54.88</div>
                                                <div class="text-xs text-gray-400">VAT Inclusive</div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 text-sm bg-gray-50 p-3 rounded border border-gray-100">
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-people text-gray-400"></i>
                                                <span class="text-gray-600">Employees:</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="number" min="1" value="1" 
                                                       class="w-16 px-2 py-1 border border-gray-200 rounded text-center font-medium focus:outline-none focus:border-gray-400 text-sm text-timora-dark"
                                                       id="totalEmployees" name="total_employees" data-price-per-user="49">
                                                <span class="text-xs text-gray-400">₱49 per additional</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add-on Features -->
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-2 space-y-2 sm:space-y-0">
                                            <h3 class="font-semibold text-timora-dark text-sm sm:text-base">Add-on Features</h3>
                                            <span class="text-xs font-medium px-2 py-1 rounded-full text-white bg-timora-yellow w-fit">Optional</span>
                                        </div>
                                        <div class="space-y-1.5" id="addonsList">
                                            <div class="text-center py-4">
                                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-timora-teal"></div>
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column - Included Features & Pricing Summary -->
                                <div class="lg:col-span-5 space-y-3 sm:space-y-4">
                                    <!-- Included Features -->
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
                                        <h3 class="font-semibold text-timora-dark mb-3 text-sm sm:text-base">Included Features</h3>
                                        <div class="space-y-2">
                                            <!-- Employee Access -->
                                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-employee-red">
                                                <div class="text-xs font-medium mb-2 text-employee-red">Employee Access</div>
                                                <div class="space-y-1.5">
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-employee-red"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Time Keeping (Check-in & Check-out)</span>
                                                    </div>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-employee-red"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Payslip View & Download</span>
                                                    </div>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-employee-red"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Attendance Photo Capture</span>
                                                    </div>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-employee-red"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Leave and Overtime Filing</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Owner Access -->
                                            <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-timora-orange">
                                                <div class="text-xs font-medium mb-2 text-timora-orange">Owner Access</div>
                                                <div class="space-y-1.5">
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-timora-orange"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Government Report Generator</span>
                                                    </div>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-timora-orange"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Employee List View</span>
                                                    </div>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-timora-orange"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Payroll Process</span>
                                                    </div>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-timora-orange"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Create Employee</span>
                                                    </div>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-timora-orange"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Geotagging + Location Tracking</span>
                                                    </div>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-timora-orange"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Earnings & Deductions</span>
                                                    </div>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-1 h-1 rounded-full mt-1.5 flex-shrink-0 bg-timora-orange"></div>
                                                        <span class="text-xs text-gray-600 leading-tight">Flexible Shift Scheduling</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pricing Summary -->
                                    <div class="bg-gradient-to-r from-timora-dark to-timora-teal rounded-lg p-3 shadow-lg transform lg:-translate-y-1">
                                        <div class="font-medium mb-3 text-sm text-white">Pricing Summary</div>
                                        <div class="space-y-2 text-xs">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-200">Base Price</span>
                                                <span class="text-white" id="basePrice">₱49.00</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-200">Employees (<span id="empCount">0</span>)</span>
                                                <span class="text-white" id="empPrice">₱0.00</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-200">Add-ons (<span id="addonCount">0</span>)</span>
                                                <span class="text-white" id="addonPrice">₱0.00</span>
                                            </div>
                                            <div class="border-t border-gray-400 pt-2 mt-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-300">Subtotal</span>
                                                    <span class="text-gray-100" id="subtotal">₱49.00</span>
                                                </div>
                                                <div class="flex justify-between items-center mt-1">
                                                    <span class="text-gray-300">VAT (12%)</span>
                                                    <span class="text-gray-100" id="vat">₱5.88</span>
                                                </div>
                                            </div>
                                            <div class="border-t-2 border-gray-300 pt-2 mt-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-medium text-white text-sm">Total Monthly</span>
                                                    <div class="text-right">
                                                        <div class="text-xl font-semibold text-timora-yellow" id="totalPrice">₱54.88</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Navigation -->
                            <div class="mt-4 sm:mt-6 pt-3 sm:pt-5 border-t-2 flex flex-col sm:flex-row items-center justify-between space-y-3 sm:space-y-0">
                                <button type="button" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-400 opacity-50 cursor-not-allowed" disabled id="prevBtn">
                                    ← Previous
                                </button>
                                <div class="flex items-center gap-2">
                                    <div class="text-sm text-gray-500">Step <span id="navStep">1</span> of 2</div>
                                </div>
                                <button type="button" class="w-full sm:w-auto text-white hover:opacity-90 shadow-lg px-6 sm:px-8 py-2 rounded-md transition-all bg-timora-orange" id="nextBtn">
                                    Next Step →
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Basic Information -->
                    <div class="wizard-step" data-step="2">
                        <div class="p-3 sm:p-4">
                            <!-- Referral Code Section -->
                            <div class="mb-3 p-3 rounded-lg border-l-4 border-timora-yellow border border-gray-200 bg-gray-50">
                                <label class="text-xs font-medium mb-2 block text-timora-yellow">Referral Code (Optional)</label>
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <input placeholder="Enter referral code" name="referral_code" id="referralCode"
                                        class="flex-1 h-9 text-sm px-3 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200">
                                    <button type="button" class="text-white px-4 hover:opacity-90 h-9 text-sm rounded-md bg-timora-teal whitespace-nowrap" id="verifyBtn">
                                        Verify
                                    </button>
                                </div>
                                <div id="referralStatus" class="text-success d-none mt-2 text-sm"></div>
                                <div id="referralError" class="text-danger d-none mt-2 text-sm"></div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-5">
                                <!-- Left Column - User Details -->
                                <div class="lg:col-span-5">
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
                                        <h3 class="font-semibold text-timora-dark mb-4 text-sm sm:text-base">User Details</h3>
                                        
                                        <div class="space-y-3">
                                            <!-- Full Name -->
                                            <div class="form-group">
                                                <label class="text-sm font-medium flex items-center gap-2">
                                                    <i class="bi bi-person text-gray-400"></i>
                                                    Full Name *
                                                </label>
                                                <input placeholder="Enter your full name" name="full_name" required
                                                    class="mt-1.5 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 border-gray-300">
                                                <div class="invalid-feedback hidden text-xs text-red-600 mt-1 flex items-center gap-1">
                                                    <span>⚠</span>
                                                    <span>Full name is required</span>
                                                </div>
                                            </div>

                                            <!-- Username -->
                                            <div class="form-group">
                                                <label class="text-sm font-medium flex items-center gap-2">
                                                    <i class="bi bi-person text-gray-400"></i>
                                                    Username *
                                                </label>
                                                <input placeholder="Enter username" name="username" required
                                                    class="mt-1.5 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 border-gray-300">
                                                <div class="invalid-feedback hidden text-xs text-red-600 mt-1 flex items-center gap-1" id="errorUsername">
                                                    <span>⚠</span>
                                                    <span>Username is required</span>
                                                </div>
                                            </div>

                                            <!-- Email -->
                                            <div class="form-group">
                                                <label class="text-sm font-medium flex items-center gap-2">
                                                    <i class="bi bi-envelope text-gray-400"></i>
                                                    Email *
                                                </label>
                                                <input type="email" placeholder="Enter email address" name="email" required
                                                    class="mt-1.5 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 border-gray-300">
                                                <div class="invalid-feedback hidden text-xs text-red-600 mt-1 flex items-center gap-1" id="errorEmail">
                                                    <span>⚠</span>
                                                    <span>Email is required</span>
                                                </div>
                                            </div>

                                            <!-- Password -->
                                            <div class="form-group">
                                                <label class="text-sm font-medium flex items-center gap-2">
                                                    <i class="bi bi-lock text-gray-400"></i>
                                                    Password *
                                                </label>
                                                <input type="password" placeholder="Enter password" name="password" required
                                                    class="mt-1.5 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 border-gray-300">
                                                <div class="invalid-feedback hidden text-xs text-red-600 mt-1 flex items-center gap-1">
                                                    <span>⚠</span>
                                                    <span>Password is required</span>
                                                </div>
                                            </div>

                                            <!-- Confirm Password -->
                                            <div class="form-group">
                                                <label class="text-sm font-medium flex items-center gap-2">
                                                    <i class="bi bi-lock text-gray-400"></i>
                                                    Confirm Password *
                                                </label>
                                                <input type="password" placeholder="Re-enter password" name="confirm_password" required
                                                    class="mt-1.5 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 border-gray-300">
                                                <div class="invalid-feedback hidden text-xs text-red-600 mt-1 flex items-center gap-1">
                                                    <span>⚠</span>
                                                    <span>Passwords must match</span>
                                                </div>
                                            </div>

                                            <!-- Phone Number -->
                                            <div class="form-group">
                                                <label class="text-sm font-medium flex items-center gap-2">
                                                    <i class="bi bi-phone text-gray-400"></i>
                                                    Phone Number *
                                                </label>
                                                <input type="tel" placeholder="Enter phone number" name="phone_number" required
                                                    class="mt-1.5 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 border-gray-300">
                                                <div class="invalid-feedback hidden text-xs text-red-600 mt-1 flex items-center gap-1">
                                                    <span>⚠</span>
                                                    <span>Phone number is required</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column - Business Information & Pricing -->
                                <div class="lg:col-span-7 space-y-3">
                                    <!-- Business Information -->
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
                                        <h3 class="font-semibold text-timora-dark mb-3 text-sm sm:text-base">Business Information</h3>

                                        <div class="space-y-3">
                                            <!-- Company Name -->
                                            <div class="form-group">
                                                <label class="text-sm font-medium flex items-center gap-2">
                                                    <i class="bi bi-shop text-gray-400"></i>
                                                    Company Name *
                                                </label>
                                                <input placeholder="Enter company name" name="branch_name" required
                                                    class="mt-1.5 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 border-gray-300">
                                                <div class="invalid-feedback hidden text-xs text-red-600 mt-1 flex items-center gap-1">
                                                    <span>⚠</span>
                                                    <span>Company name is required</span>
                                                </div>
                                            </div>

                                            <!-- Address -->
                                            <div class="form-group">
                                                <label class="text-sm font-medium flex items-center gap-2">
                                                    <i class="bi bi-geo-alt text-gray-400"></i>
                                                    Address *
                                                </label>
                                                <input placeholder="Enter company address" name="branch_location" required
                                                    class="mt-1.5 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-gray-200 border-gray-300">
                                                <div class="invalid-feedback hidden text-xs text-red-600 mt-1 flex items-center gap-1">
                                                    <span>⚠</span>
                                                    <span>Address is required</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pricing Display -->
                                    <div class="bg-gradient-to-r from-timora-dark to-timora-teal rounded-lg p-3">
                                        <div class="font-medium mb-3 text-sm text-white">Pricing Summary</div>
                                        <div class="space-y-2 text-xs">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-200">Base Price</span>
                                                <span class="text-white" id="basePrice2">₱49.00</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-200">Employees (<span id="empCount2">0</span>)</span>
                                                <span class="text-white" id="empPrice2">₱0.00</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-200">Add-ons (<span id="addonCount2">0</span>)</span>
                                                <span class="text-white" id="addonPrice2">₱0.00</span>
                                            </div>
                                            <div class="border-t border-gray-400 pt-2 mt-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-300">Subtotal</span>
                                                    <span class="text-gray-100" id="subtotal2">₱49.00</span>
                                                </div>
                                                <div class="flex justify-between items-center mt-1">
                                                    <span class="text-gray-300">VAT (12%)</span>
                                                    <span class="text-gray-100" id="vat2">₱5.88</span>
                                                </div>
                                            </div>
                                            <div class="border-t-2 border-gray-300 pt-2 mt-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-medium text-white text-sm">Total Monthly</span>
                                                    <div class="text-right">
                                                        <div class="text-xl font-semibold text-timora-yellow" id="totalPrice2">₱54.88</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Navigation -->
                            <div class="mt-4 sm:mt-6 pt-3 sm:pt-5 border-t-2 flex flex-col sm:flex-row items-center justify-between space-y-3 sm:space-y-0">
                                <button type="button" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 hover:bg-gray-50 shadow-sm" id="prevBtn2">
                                    ← Previous
                                </button>
                                <div class="text-sm text-gray-500">Step 2 of 2</div>
                                <button type="submit" class="w-full sm:w-auto text-white px-6 sm:px-8 py-2 hover:opacity-90 shadow-lg transition-all rounded-md bg-timora-orange" id="submitBtn">
                                    Proceed to Payment
                                </button>
                                <button type="button" class="w-full sm:w-auto text-white px-6 sm:px-8 py-2 hover:opacity-90 shadow-lg transition-all rounded-md bg-timora-orange d-none" id="nextBtn2">
                                    Next Step →
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
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
                                <div class="addon-item flex items-center justify-between p-2 rounded-lg border transition-all cursor-pointer hover:border-gray-400 bg-gray-50 border-gray-200 mb-1.5" data-addon="${addon.addon_key}">
                                    <div class="flex items-center gap-2 flex-1">
                                        <input type="checkbox" class="addon-checkbox w-4 h-4 rounded border-gray-300"
                                               name="features[]" value="${addon.addon_key}"
                                               data-addon-key="${addon.addon_key}"
                                               data-price="${addon.price}">
                                        <span class="text-xs sm:text-sm text-gray-700">${addon.name}</span>
                                    </div>
                                    <span class="text-xs sm:text-sm font-medium text-gray-900">₱${price}</span>
                                </div>
                            `;
                        });
                    } else {
                        html = '<p class="text-gray-500 text-center text-sm">No add-ons available</p>';
                    }
                    $('#addonsList').html(html);

                    // Bind change event for checkboxes and item clicks
                    $('.addon-checkbox').on('change', function() {
                        const $item = $(this).closest('.addon-item');
                        if ($(this).is(':checked')) {
                            $item.addClass('selected');
                        } else {
                            $item.removeClass('selected');
                        }
                        calculatePricing();
                    });

                    // Make entire addon item clickable
                    $('.addon-item').on('click', function(e) {
                        if (!$(e.target).is('input')) {
                            const $checkbox = $(this).find('.addon-checkbox');
                            $checkbox.prop('checked', !$checkbox.is(':checked')).trigger('change');
                        }
                    });
                }).fail(function() {
                    $('#addonsList').html('<p class="text-red-500 text-center text-sm">Failed to load add-ons</p>');
                });
            }

            // Calculate pricing
            function calculatePricing() {
                const basePrice = 49;
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
                $('#prevBtn').prop('disabled', step === 1).toggleClass('opacity-50 cursor-not-allowed', step === 1);
                $('#prevBtn2').toggle(step === 2);
                $('#nextBtn').toggle(step === 1);
                $('#submitBtn').toggle(step === 2);
            }

            // Validation
            function validateStep2() {
                let valid = true;
                $('.wizard-step[data-step="2"] input[required]').each(function() {
                    const $feedback = $(this).siblings('.invalid-feedback');
                    if (!$(this).val()) {
                        $(this).removeClass('border-gray-300').addClass('border-red-500 bg-red-50');
                        $feedback.removeClass('hidden').addClass('flex');
                        valid = false;
                    } else {
                        $(this).removeClass('border-red-500 bg-red-50').addClass('border-gray-300');
                        $feedback.removeClass('flex').addClass('hidden');
                    }
                });

                // Password match
                const pw = $('input[name="password"]').val();
                const cpw = $('input[name="confirm_password"]').val();
                const $cpwField = $('input[name="confirm_password"]');
                const $cpwFeedback = $cpwField.siblings('.invalid-feedback');
                
                if (pw !== cpw) {
                    $cpwField.removeClass('border-gray-300').addClass('border-red-500 bg-red-50');
                    $cpwFeedback.removeClass('hidden').addClass('flex');
                    valid = false;
                } else if (cpw) {
                    $cpwField.removeClass('border-red-500 bg-red-50').addClass('border-gray-300');
                    $cpwFeedback.removeClass('flex').addClass('hidden');
                }

                return valid;
            }

            // Events
            $('#nextBtn').click(function() {
                if (currentStep < totalSteps) {
                    updateStep(currentStep + 1);
                }
            });

            $('#prevBtn2').click(function() {
                if (currentStep > 1) {
                    updateStep(currentStep - 1);
                }
            });

            $('#totalEmployees').on('input change', calculatePricing);

            // Remove validation on input
            $('input[required]').on('input', function() {
                if ($(this).val()) {
                    $(this).removeClass('border-red-500 bg-red-50').addClass('border-gray-300');
                    $(this).siblings('.invalid-feedback').removeClass('flex').addClass('hidden');
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
                            $('input[name="username"]').removeClass('border-gray-300').addClass('border-red-500 bg-red-50');
                            $('#errorUsername span:last-child').text(errors.username[0]);
                            $('#errorUsername').removeClass('hidden').addClass('flex');
                        }
                        if (errors.email) {
                            $('input[name="email"]').removeClass('border-gray-300').addClass('border-red-500 bg-red-50');
                            $('#errorEmail span:last-child').text(errors.email[0]);
                            $('#errorEmail').removeClass('hidden').addClass('flex');
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

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 text-gray-700">
        <!-- Top Section -->
        <div class="px-6 md:px-20 py-16 flex flex-col md:flex-row justify-between items-start gap-12">
            <!-- Left: Logo & Description -->
            <div class="md:w-1/2 max-w-md">
                <div onclick="window.location.href='https://jafdigital.co/'" class="flex items-center mb-4 cursor-pointer">
                    <img src="https://jafdigital.co/wp-content/uploads/2023/05/JAF-New-logo-300x300.png" alt="OneJAF Logo" class="h-8 w-auto">
                </div>
                <p class="text-sm leading-relaxed text-gray-600">
                    A foundation system by JAF Digital that simplifies and automates HR,
                    payroll, and inventory operations for businesses in the Philippines.
                </p>
            </div>

            <!-- Right: Navigation & Contact (Grouped tightly) -->
            <div class="flex flex-col sm:flex-row gap-32 text-sm">
                <!-- Services -->
                <div>
                    <h4 class="text-base font-semibold text-gray-900 mb-4">Services</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="https://jafdigital.co/custom-software-development/" class="hover:text-red-500 transition">
                                Custom Software Development
                            </a>
                        </li>
                        <li>
                            <a href="https://jafdigital.co/it-infrastructure-technical-support/" class="hover:text-red-500 transition">
                                IT Infrastructure and Technical Support
                            </a>
                        </li>
                        <li>
                            <a href="https://jafdigital.co/digital-marketing-advertising-solutions/" class="hover:text-red-500 transition">
                                Digital Marketing and Advertising Solutions
                            </a>
                        </li>
                        <li>
                            <a href="https://jafdigital.co/web-and-mobile-applications/" class="hover:text-red-500 transition">
                                Web and Mobile Applications
                            </a>
                        </li>
                        <li>
                            <a href="https://jafdigital.co/system-integration-and-automation/" class="hover:text-red-500 transition">
                                System Integration and Automation
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Products -->
                <div>
                    <h4 class="text-base font-semibold text-gray-900 mb-4">Our Products</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="https://jafdigital.co/timora/" class="hover:text-red-500 transition">
                                Timora
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-base font-semibold text-gray-900 mb-4">Contact Us</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-center">
                            <a href="tel:+639171883359" class="flex items-center hover:text-red-500 transition">
                                <i class="bi bi-telephone mr-2 text-gray-500"></i>
                                +63 917 188 3359
                            </a>
                        </li>
                        <li class="flex items-center">
                            <a href="mailto:sales@jafdigital.co" class="flex items-center hover:text-red-500 transition">
                                <i class="bi bi-envelope mr-2 text-gray-500"></i>
                                sales@jafdigital.co
                            </a>
                        </li>
                        <li class="flex items-start">
                            <a href="https://www.google.com/maps/place/JAF+Digital/@14.5590245,121.019474,19z/data=!3m1!4b1!4m6!3m5!1s0x3397c9c1f6dfce91:0x43ec0af73b03fe22!8m2!3d14.5590245!4d121.019474!16s%2Fg%2F11n0pn3pzv?entry=ttu&g_ep=EgoyMDI1MDcyNy4wIKXMDSoASAFQAw%3D%3D" target="_blank" rel="noopener noreferrer" class="flex items-start hover:text-red-500 transition">
                                <i class="bi bi-geo-alt mr-2 mt-1 text-gray-500"></i>
                                <span>
                                    Unit D 49th Floor PBCom Tower, 6795 Ayala Avenue, corner
                                    V.A. Rufino St, Makati City, Metro Manila, Philippines
                                </span>
                            </a>
                        </li>
                    </ul>

                    <!-- Social Icons -->
                    <div class="flex space-x-4 mt-6 text-gray-500">
                        <a href="https://www.facebook.com/OneJAFCustomizableSystemDevelopment" class="hover:text-red-500 transition">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://www.youtube.com/@jafdigitalofficial" class="hover:text-blue-400 transition">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="https://ph.linkedin.com/company/jafdigital" class="hover:text-blue-700 transition">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="https://www.instagram.com/jafdigitalofficial/" class="hover:text-pink-500 transition">
                            <i class="bi bi-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-200 py-6 px-6 md:px-20 flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
            <div>&copy; 2025 Powered by JAF Digital Group Inc.</div>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="https://jafdigital.co/privacy-policy/" class="hover:text-gray-700 transition">
                    Privacy Policy
                </a>
                <a href="https://jafdigital.co/terms-and-conditions/" class="hover:text-gray-700 transition">
                    Terms & Conditions
                </a>
            </div>
        </div>
    </footer>
@endsection