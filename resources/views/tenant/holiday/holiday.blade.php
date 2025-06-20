<?php $page = 'holidays'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Holidays</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Holidays</li>
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
                    <div class="d-flex gap-2 mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_holiday"
                            class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                            Holiday</a>

                        <a href="{{ route('holiday-exception') }}" class="btn btn-secondary d-flex align-items-center"><i
                                class="ti ti-circle-plus me-2"></i>
                            Holiday Exceptions</a>

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
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Holidays List</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" class="form-control date-range bookingrange"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="dropdown me-3">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                Select Status
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);"
                                class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
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
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Paid Status</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($holidays as $holiday)
                                    @php
                                        $statusClass = $holiday->status === 'active' ? 'badge-success' : 'badge-danger';
                                        $statusLabel = ucfirst($holiday->status);

                                        $paidClass = $holiday->is_paid ? 'badge-success' : 'badge-secondary';
                                        $paidLabel = $holiday->is_paid ? 'Paid' : 'Unpaid';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="fw-medium"><a href="#">{{ $holiday->name }}</a></h6>
                                        </td>
                                        <td>
                                            @if ($holiday->recurring && $holiday->month_day)
                                                {{ \Carbon\Carbon::createFromFormat('m-d', $holiday->month_day)->format('F j') }}
                                                <span class="badge bg-primary fs-9 py-0 px-1">Recurring</span>
                                            @elseif($holiday->date)
                                                {{ \Carbon\Carbon::parse($holiday->date)->format('F j, Y') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ ucfirst(strtolower($holiday->type)) }}</td>
                                        <td> <span class="badge {{ $paidClass }}">
                                                <i class="ti ti-point-filled"></i> {{ $paidLabel }}
                                            </span></td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">
                                                <i class="ti ti-point-filled"></i> {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_holiday" data-id="{{ $holiday->id }}"
                                                    data-name="{{ $holiday->name }}"
                                                    data-holiday-type="{{ $holiday->type }}"
                                                    data-recurring="{{ $holiday->recurring ? '1' : '0' }}"
                                                    data-month-day="{{ $holiday->month_day }}"
                                                    data-date="{{ $holiday->date }}"
                                                    data-is-paid="{{ $holiday->is_paid ? '1' : '0' }}"
                                                    data-status="{{ $holiday->status }}"><i class="ti ti-edit"></i></a>

                                                <a href="javascript:void(0);" data-bs-toggle="modal" class="btn-delete"
                                                    data-bs-target="#delete_holiday" data-id="{{ $holiday->id }}"
                                                    data-name="{{ $holiday->name }}"><i class="ti ti-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

      @include('layout.partials.footer-company')

    </div>
    <!-- /Page Wrapper -->

    @component('components.modal-popup', [
        'branches' => $branches,
        'departments' => $departments,
        'designations' => $designations,
        'holidays' => $holidays,
    ])
    @endcomponent
@endsection

@push('scripts')
    {{-- Add Holiday --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            document
                .getElementById('addHolidayForm')
                .addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const form = e.target;
                    const fd = new FormData(form);
                    // Convert FormData to plain object
                    const payload = Object.fromEntries(fd.entries());

                    try {
                        const res = await fetch('/api/holidays/create', {
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

                        toastr.success(json.message || 'Holiday saved!');
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

    {{-- Edit Holiday --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // Edit
            let editId = "";

            // 🌟 1. Populate fields when edit icon is clicked
            document.querySelectorAll('[data-bs-target="#edit_holiday"]').forEach(button => {
                button.addEventListener("click", function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const type = this.dataset.holidayType;
                    const isPaid = this.dataset.isPaid === '1';
                    const recurring = this.dataset.recurring === '1';
                    const monthDay = this.dataset.monthDay;
                    const fullDate = this.dataset.date;
                    const status = this.dataset.status;

                    // Populate hidden ID & name/type/is_paid/status selects...
                    document.getElementById("holidayId").value = id;
                    document.getElementById("editHolidayName").value = name;

                    const typeSel = document.getElementById("editHolidayType");
                    typeSel.value = type;
                    typeSel.dispatchEvent(new Event('change'));

                    const paidSel = document.getElementById("editHolidayIsPaid");
                    paidSel.value = isPaid ? '1' : '0';
                    paidSel.dispatchEvent(new Event('change'));

                    const statusSel = document.getElementById("editHolidayStatus");
                    statusSel.value = status;
                    statusSel.dispatchEvent(new Event('change'));

                    // Recurring switch
                    const recurCheckbox = document.getElementById("editHolidayRecurring");
                    recurCheckbox.checked = recurring;
                    recurCheckbox.dispatchEvent(new Event('change'));

                    // Date field
                    const dateInput = document.getElementById("editHolidayDate");
                    if (recurring) {
                        dateInput.value = monthDay;
                    } else {
                        dateInput.value = fullDate;
                    }
                });
            });

            // 🌟 2. Handle update button click
            document.getElementById("updateHolidayBtn").addEventListener("click", async function(e) {
                e.preventDefault();

                const editId = document.getElementById("holidayId").value;
                const name = document.getElementById("editHolidayName").value.trim();
                const dateVal = document.getElementById("editHolidayDate")
                    .value; // "MM-DD" or "YYYY-MM-DD"
                const type = document.getElementById("editHolidayType").value;
                const isPaid = document.getElementById("editHolidayIsPaid").value;
                const recurring = document.getElementById("editHolidayRecurring").checked;
                const status = document.getElementById("editHolidayStatus").value;

                // 2) Simple validation
                if (!name || !dateVal || !type || typeof isPaid === 'undefined' || !status) {
                    return toastr.error("Please complete all fields.");
                }

                // 3) Build payload
                const payload = {
                    name: name,
                    type: type,
                    is_paid: isPaid,
                    recurring: recurring ? 1 : 0,
                    status: status, // only for update
                    date: dateVal // ALWAYS the full YYYY-MM-DD
                };

                try {
                    const res = await fetch(`/api/holidays/update/${editId}`, {
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
                        toastr.success("Holiday updated successfully!");
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

    {{-- Delete Holiday --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // Experience Delete
            let holidayDeleteId = null;

            const holidayDeleteButtons = document.querySelectorAll('.btn-delete');
            const holidayDeleteBtn = document.getElementById('holidayConfirmDeleteBtn');
            const holidayPlaceHolder = document.getElementById('holidayPlaceHolder');

            // Set up the delete buttons to capture data
            holidayDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    holidayDeleteId = this.getAttribute('data-id');
                    const holidayName = this.getAttribute('data-name');

                    if (holidayPlaceHolder) {
                        holidayPlaceHolder.textContent =
                            holidayName;
                    }
                });
            });

            // Confirm delete button click event
            holidayDeleteBtn?.addEventListener('click', function() {
                if (!holidayDeleteId)
                    return;

                fetch(`/api/holidays/delete/${holidayDeleteId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content"),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${authToken}`,
                        },
                    })
                    .then(response => {
                        if (response.ok) {
                            toastr.success("Holiday deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_holiday'));
                            deleteModal.hide(); // Hide the modal

                            setTimeout(() => window.location.reload(),
                                800); // Refresh the page after a short delay
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message ||
                                    "Error deleting holiday.");
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
