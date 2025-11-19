<?php $page = 'banks'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Banks</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Banks</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if(in_array('Export',$permission))
                    {{-- <div class="me-2 mb-2">
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
                    </div> --}}
                    @endif
                    @if(in_array('Create',$permission))
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_bank"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Bank</a>
                    </div>
                    @endif
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
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3 bg-primary">
                    <h5 class="text-white">Bank List</h5>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th class="text-center">Bank</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center" >Account #</th>
                                    <th class="text-center">Remarks</th>
                                    @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                    <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($banks as $bank)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <h6 class="fw-medium fs-14 text-dark">{{ $bank->bank_name ?? 'N/A' }}</h6>
                                        </td>
                                        <td class="text-center">
                                            {{ $bank->bank_code ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $bank->bank_account_number ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $bank->bank_remarks ?? 'N/A' }}
                                        </td>
                                        @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td class="text-center">
                                            <div class="action-icon d-inline-flex">
                                                @if(in_array('Update',$permission))
                                                <a href="#" class="me-2 btn-edit" data-bs-toggle="modal"
                                                    data-bs-target="#edit_bank" data-id="{{ $bank->id }}"
                                                    data-bank-name="{{ $bank->bank_name }}"
                                                    data-bank-code="{{ $bank->bank_code }}"
                                                    data-bank-account-number="{{ $bank->bank_account_number }}"
                                                    data-bank-remarks="{{ $bank->bank_remarks }}"><i
                                                        class="ti ti-edit"></i></a>
                                                @endif
                                                @if(in_array('Delete',$permission))
                                                <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_bank"
                                                    data-id="{{ $bank->id }}"
                                                    data-bank-name="{{ $bank->bank_name }}" title="Delete"><i
                                                        class="ti ti-trash"></i></a>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Search Filter -->

        </div>

        @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup')
    @endcomponent
@endsection

@push('scripts')
    {{-- Add Bank Form Submission --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            document
                .getElementById('addBankForm')
                .addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const form = e.target;
                    const fd = new FormData(form);
                    // Convert FormData to plain object
                    const payload = Object.fromEntries(fd.entries());

                    try {
                        const res = await fetch('/api/bank/create', {
                            method: 'POST',
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: JSON.stringify(payload)
                        });

                        const json = await res.json();
                        if (!res.ok) {
                            if (json.errors) {
                                Object.values(json.errors).flat().forEach(msg => toastr.error(msg));
                            } else {
                                toastr.error(json.message || 'Something went wrong.');
                            }
                            return;
                        }

                        toastr.success(json.message || 'Bank Added!');
                        form.reset();
                        const modalEl = form.closest('.modal');
                        bootstrap.Modal.getInstance(modalEl)?.hide();

                        setTimeout(() => {
                            window.location.reload();
                        }, 800);

                    } catch (err) {
                        console.error(err);
                        toastr.error(err.message || 'Please check your input.');
                    }
                });
        });
    </script>

    {{-- Edit Bank Form Submission --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // 🌟 1. Delegate click events for edit buttons
            document.addEventListener("click", function(e) {
                const button = e.target.closest('[data-bs-target="#edit_bank"]');
                if (!button) return;

                const id = button.dataset.id;
                const bankName = button.dataset.bankName;
                const bankCode = button.dataset.bankCode;
                const bankAccountNumber = button.dataset.bankAccountNumber;
                const bankRemarks = button.dataset.bankRemarks;

                document.getElementById("editBankId").value = id;
                document.getElementById("editBankName").value = bankName;
                document.getElementById("editBankCode").value = bankCode;
                document.getElementById("editBankAccountNumber").value = bankAccountNumber;
                document.getElementById("editBankRemarks").value = bankRemarks;
            });

            // 🌟 2. Handle update button click
            document.getElementById("updateBankBtn").addEventListener("click", async function(e) {
                e.preventDefault();

                const editId = document.getElementById("editBankId").value;
                const bankName = document.getElementById("editBankName").value.trim();
                const bankCode = document.getElementById("editBankCode").value;
                const bankAccountNumber = document.getElementById("editBankAccountNumber").value;
                const bankRemarks = document.getElementById("editBankRemarks").value;

                if (!bankName || !bankCode || !bankAccountNumber) {
                    return toastr.error("Please complete all fields.");
                }

                const payload = {
                    bank_name: bankName,
                    bank_code: bankCode,
                    bank_account_number: bankAccountNumber,
                    bank_remarks: bankRemarks
                };

                try {
                    const res = await fetch(`/api/bank/update/${editId}`, {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Authorization": `Bearer ${authToken}`
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if (res.ok) {
                        toastr.success("Bank updated successfully!");
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        (data.errors ?
                            Object.values(data.errors).flat().forEach(msg => toastr.error(msg)) :
                            toastr.error(data.message || "Update failed.")
                        );
                    }

                } catch (err) {
                    console.error(err);
                    toastr.error("Something went wrong.");
                }
            });
        });
    </script>

    {{-- Delete Bank Confirmation --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            let bankDeleteId = null;
            const bankConfirmDeleteBtn = document.getElementById('bankConfirmDeleteBtn');
            const bankPlaceHolder = document.getElementById('bankPlaceHolder');

            // Use delegation to listen for delete button clicks
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                bankDeleteId = button.getAttribute('data-id');
                const bankName = button.getAttribute('data-bank-name');

                if (bankPlaceHolder) {
                    bankPlaceHolder.textContent = bankName;
                }
            });

            // Confirm delete
            bankConfirmDeleteBtn?.addEventListener('click', function() {
                if (!bankDeleteId) return;

                fetch(`/api/bank/delete/${bankDeleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Bank deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_bank'));
                            deleteModal.hide();

                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting bank.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });
        });
    </script>
@endpush
