<?php $page = 'user-payslip-view'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Payslip</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Payslip</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="mb-2 me-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary d-flex align-items-center dropdown-toggle" type="button"
                                id="templateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-layout me-2"></i>Template: <span id="currentTemplate"
                                    class="ms-1 fw-semibold">{{ ucfirst($payslips->payslip_template ?? 'template1') }}</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="templateDropdown">
                                <li><a class="dropdown-item template-option" href="#" data-template="template1">
                                        <i class="ti ti-layout-grid me-2"></i>Template 1 - Modern
                                    </a></li>
                                <li><a class="dropdown-item template-option" href="#" data-template="template2">
                                        <i class="ti ti-table me-2"></i>Template 2 - Classic
                                    </a></li>
                                <li><a class="dropdown-item template-option" href="#" data-template="template3">
                                        <i class="ti ti-palette me-2"></i>Template 3 - Minimal
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="mb-2">
                        <a href="#" id="downloadBtn" class="btn btn-dark d-flex align-items-center">
                            <i class="ti ti-download me-2"></i>Download
                        </a>
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

            <!-- Payslip -->
            @php
                $selectedTemplate = $payslips->payslip_template ?? 'template1';
            @endphp

            <div id="template-container">
                @include('tenant.payroll.payslip.userpayslip.templates.' . $selectedTemplate)
            </div>
            <!-- /Payslip -->

        </div>

        @include('layout.partials.footer-company')

    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // Template Switcher functionality
        document.querySelectorAll('.template-option').forEach(function (element) {
            element.addEventListener('click', function (e) {
                e.preventDefault();
                const template = this.getAttribute('data-template');

                // Update URL without page reload
                const url = new URL(window.location.href);
                url.searchParams.set('template', template);
                window.history.pushState({}, '', url);

                // Update current template display
                const templateName = template.charAt(0).toUpperCase() + template.slice(1);
                document.getElementById('currentTemplate').textContent = templateName;

                // Reload page to apply new template
                window.location.reload();
            });
        });

        // Download PDF functionality
        document.getElementById('downloadBtn').addEventListener('click', function () {
            var content = document.querySelector('.printable-area');

            html2canvas(content, {
                useCORS: true,
                scale: 1.5,
                logging: false,
                allowTaint: true,
                backgroundColor: '#ffffff'
            }).then(function (canvas) {
                try {
                    var imgData = canvas.toDataURL('image/jpeg', 0.85);
                    const {
                        jsPDF
                    } = window.jspdf;
                    const doc = new jsPDF({
                        orientation: 'portrait',
                        unit: 'pt',
                        format: 'a4',
                        compress: true
                    });

                    var pageWidth = doc.internal.pageSize.getWidth();
                    var pageHeight = doc.internal.pageSize.getHeight();
                    var imgWidth = canvas.width;
                    var imgHeight = canvas.height;
                    var ratio = Math.min(pageWidth / imgWidth, pageHeight / imgHeight);

                    var imgX = (pageWidth - imgWidth * ratio) / 2;
                    var imgY = 20;

                    doc.addImage(imgData, 'JPEG', imgX, imgY, imgWidth * ratio, imgHeight * ratio, undefined, 'FAST');

                    doc.save('payslip-{{ $payslips->id }}.pdf');
                } catch (error) {
                    console.error('Error capturing the printable area:', error);
                }
            }).catch(function (error) {
                console.error('html2canvas failed:', error);
            });
        });
    </script>
@endpush