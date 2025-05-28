<?php $page = 'branch-grid'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Branches</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                CRM
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Branches Grid</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                                            class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_company"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Branch</a>
                    </div>
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5>Branches Grid</h5>
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-sm btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                Sort By : Last 7 Days
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Branch Card --}}
            <div class="row">
                @foreach ($branches as $branch)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="form-check form-check-md">
                                        <input class="form-check-input" type="checkbox">
                                    </div>
                                    <div>
                                        <a href="{{ url('company-details') }}"
                                            class="avatar avatar-xl avatar-rounded online border rounded-circle">
                                            <img src="{{ URL::asset('build/img/company/company-13.svg') }}"
                                                class="img-fluid h-auto w-auto" alt="img">
                                        </a>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-icon btn-sm rounded-circle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end p-3">
                                            <li>
                                                <a class="dropdown-item rounded-1" href="javascript:void(0);"
                                                    data-bs-toggle="modal" data-bs-target="#edit_company"><i
                                                        class="ti ti-edit me-1"></i>Edit</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item rounded-1" href="javascript:void(0);"
                                                    data-bs-toggle="modal" data-bs-target="#delete_modal"><i
                                                        class="ti ti-trash me-1"></i>Delete</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="text-center mb-3">
                                    <h6 class="mb-1"><a href="{{ url('company-details') }}">{{ $branch->name }}</a></h6>
                                </div>
                                <div class="d-flex flex-column">
                                    <p class="text-dark d-inline-flex align-items-center mb-2">
                                        <i
                                            class="{{ $branch->branch_type === 'main' ? 'ti ti-star-filled text-warning' : 'ti ti-star text-gray-5' }} me-2"></i>
                                        {{ ucfirst($branch->branch_type) }}
                                    </p>
                                    <p class="text-dark d-inline-flex align-items-center mb-2">
                                        <i class="ti ti-phone text-gray-5 me-2"></i>
                                        {{ $branch->contact_number }}
                                    </p>
                                    <p class="text-dark d-inline-flex align-items-center">
                                        <i class="ti ti-map-pin text-gray-5 me-2"></i>
                                        {{ $branch->location }}
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
                                    <div class="icons-social d-flex align-items-center">
                                        <a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
                                                class="ti ti-mail"></i></a>
                                        <a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
                                                class="ti ti-phone-call"></i></a>
                                        <a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
                                                class="ti ti-message-2"></i></a>
                                        <a href="#" class="avatar avatar-rounded avatar-sm me-1"><i
                                                class="ti ti-brand-skype"></i></a>
                                        <a href="#" class="avatar avatar-rounded avatar-sm"><i
                                                class="ti ti-brand-facebook"></i></a>
                                    </div>
                                    <span class="d-inline-flex align-items-center"><i
                                            class="ti ti-star-filled text-warning me-1"></i>5.0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- <div class="text-center mb-4">
                <a href="#" class="btn btn-white border"><i class="ti ti-loader-3 text-primary me-2"></i>Load More</a>
            </div> --}}
        </div>

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2025 &copy; OneJAF Vertex.</p>
            <p>Designed &amp; Developed By <a href="https://jafdigital.co/" class="text-primary" target="_blank">JAF
                    Digital Group Inc.</a></p>
        </div>

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection
