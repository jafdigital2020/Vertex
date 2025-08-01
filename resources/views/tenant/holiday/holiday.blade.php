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
                    @if (in_array('Export', $permission))
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
                    @endif
                    <div class="d-flex gap-2 mb-2">
                        @if (in_array('Create', $permission))
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_holiday"
                                class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add
                                Holiday</a>
                        @endif
                        @if (in_array('Create', $permission) || in_array('Update', $permission) || in_array('Delete', $permission))
                            <a href="{{ route('holiday-exception') }}"
                                class="btn btn-secondary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>
                                Holiday Exceptions</a>
                        @endif
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
                                <input type="text" id="dateRange_filter"
                                    class="form-control date-range bookingrange-filtered"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" onchange="holidayFilter()">
                                <span class="input-icon-addon">
                                    <i class="ti ti-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <select name="holidayType_filter" id="holidayType_filter" class="select2 form-select"  oninput="holidayFilter()">
                                <option value="" selected>All Holiday Type</option>
                                <option value="regular">Regular</option>
                                <option value="special-non-working">Special Non Working</option>
                                <option value="special working">Special Working</option>
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="paid_filter" id="paid_filter" class="select2 form-select"  oninput="holidayFilter()">
                                <option value="" selected>All Paid Status</option>
                                <option value="1">Paid</option>
                                <option value="0">Unpaid</option>
                            </select>
                        </div>
                        <div class="form-group me-2">
                            <select name="status_filter" id="status_filter" class="select2 form-select"  oninput="holidayFilter()">
                                <option value="" selected>All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>  
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table datatable" id="holidayTable">
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
                                    <th class="text-center">Paid Status</th>
                                    <th class="text-center">Status</th>
                                    @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                        <th class="text-center">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="holidayTableBody">
                                @if (in_array('Read', $permission))
                                    @foreach ($holidays as $holiday)
                                        @php
                                            $statusClass =
                                                $holiday->status === 'active' ? 'badge-success' : 'badge-danger';
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
                                            <td class="text-center"> <span class="badge {{ $paidClass }}">
                                                    <i class="ti ti-point-filled"></i> {{ $paidLabel }}
                                                </span></td>
                                            <td class="text-center">
                                                <span class="badge {{ $statusClass }}">
                                                    <i class="ti ti-point-filled"></i> {{ $statusLabel }}
                                                </span>
                                            </td>
                                            @if (in_array('Update', $permission) || in_array('Delete', $permission))
                                                <td class="text-center">
                                                    <div class="action-icon d-inline-flex">
                                                        @if (in_array('Update', $permission))
                                                            <a href="#" class="me-2" data-bs-toggle="modal"
                                                                data-bs-target="#edit_holiday"
                                                                data-id="{{ $holiday->id }}"
                                                                data-name="{{ $holiday->name }}"
                                                                data-holiday-type="{{ $holiday->type }}"
                                                                data-recurring="{{ $holiday->recurring ? '1' : '0' }}"
                                                                data-month-day="{{ $holiday->month_day }}"
                                                                data-date="{{ $holiday->date }}"
                                                                data-is-paid="{{ $holiday->is_paid ? '1' : '0' }}"
                                                                data-status="{{ $holiday->status }}"><i
                                                                    class="ti ti-edit"></i></a>
                                                        @endif
                                                        @if (in_array('Delete', $permission))
                                                            <a href="javascript:void(0);" data-bs-toggle="modal"
                                                                class="btn-delete" data-bs-target="#delete_holiday"
                                                                data-id="{{ $holiday->id }}"
                                                                data-name="{{ $holiday->name }}"><i
                                                                    class="ti ti-trash"></i></a>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
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
        if ($('.bookingrange-filtered').length > 0) {
            var start = moment().startOf('year');
            var end = moment().endOf('year');

            function booking_range(start, end) {
                $('.bookingrange-filtered span').html(start.format('M/D/YYYY') + ' - ' + end.format('M/D/YYYY'));
            }

            $('.bookingrange-filtered').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Next Year': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
                }
            }, booking_range);
            booking_range(start, end);
        }
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

                        holidayFilter();

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

            // 🌟 1. Delegate click events for edit buttons
            document.addEventListener("click", function(e) {
                const button = e.target.closest('[data-bs-target="#edit_holiday"]');
                if (!button) return;

                const id = button.dataset.id;
                const name = button.dataset.name;
                const type = button.dataset.holidayType;
                const isPaid = button.dataset.isPaid === '1';
                const recurring = button.dataset.recurring === '1';
                const monthDay = button.dataset.monthDay;
                const fullDate = button.dataset.date;
                const status = button.dataset.status;

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

                const recurCheckbox = document.getElementById("editHolidayRecurring");
                recurCheckbox.checked = recurring;
                recurCheckbox.dispatchEvent(new Event('change'));

                const dateInput = document.getElementById("editHolidayDate");
                if (recurring) {
                    dateInput.type = "date";
                    const currentYear = new Date().getFullYear();
                    const fakeFullDate = `${currentYear}-${monthDay}`;
                    dateInput.value = fakeFullDate;
                } else {
                    dateInput.type = "date";
                    dateInput.value = fullDate;
                }

            });

            // 🌟 2. Handle update button click
            document.getElementById("updateHolidayBtn").addEventListener("click", async function(e) {
                e.preventDefault();

                const editId = document.getElementById("holidayId").value;
                const name = document.getElementById("editHolidayName").value.trim();
                const dateVal = document.getElementById("editHolidayDate").value;
                const type = document.getElementById("editHolidayType").value;
                const isPaid = document.getElementById("editHolidayIsPaid").value;
                const recurring = document.getElementById("editHolidayRecurring").checked;
                const status = document.getElementById("editHolidayStatus").value;

                if (!name || !dateVal || !type || typeof isPaid === 'undefined' || !status) {
                    return toastr.error("Please complete all fields.");
                }

                const payload = {
                    name: name,
                    type: type,
                    is_paid: isPaid,
                    recurring: recurring ? 1 : 0,
                    status: status,
                    date: dateVal
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
                        $('#edit_holiday').modal('hide');
                        holidayFilter();
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

            let holidayDeleteId = null;
            const holidayDeleteBtn = document.getElementById('holidayConfirmDeleteBtn');
            const holidayPlaceHolder = document.getElementById('holidayPlaceHolder');

            // Use delegation to listen for delete button clicks
            document.addEventListener('click', function(e) {
                const button = e.target.closest('.btn-delete');
                if (!button) return;

                holidayDeleteId = button.getAttribute('data-id');
                const holidayName = button.getAttribute('data-name');

                if (holidayPlaceHolder) {
                    holidayPlaceHolder.textContent = holidayName;
                }
            });

            // Confirm delete
            holidayDeleteBtn?.addEventListener('click', function() {
                if (!holidayDeleteId) return;

                fetch(`/api/holidays/delete/${holidayDeleteId}`, {
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
                            toastr.success("Holiday deleted successfully.");

                            const deleteModal = bootstrap.Modal.getInstance(document.getElementById(
                                'delete_holiday'));
                            deleteModal.hide();
                            holidayFilter();
                        } else {
                            return response.json().then(data => {
                                toastr.error(data.message || "Error deleting holiday.");
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        toastr.error("Server error.");
                    });
            });
        });

        function holidayFilter() {
            var dateRange = $('#dateRange_filter').val();
            var holidayType = $('#holidayType_filter').val();
            var status = $('#status_filter').val();
            var paid = $('#paid_filter').val();

            $.ajax({
                url: '{{ route('holiday_filter') }}',
                type: 'GET',
                data: {
                    dateRange: dateRange,
                    status: status,
                    paid: paid,
                    holidayType: holidayType
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#holidayTable').DataTable().destroy(); 
                        $('#holidayTableBody').html(response.html);
                        $('#holidayTable').DataTable();     
                    } else if (response.status === 'error') {
                        toastr.error(response.message || 'Something went wrong.');
                    }
                },
                error: function(xhr) {
                    let message = 'An unexpected error occurred.';
                    if (xhr.status === 403) {
                        message = 'You are not authorized to perform this action.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                }
            });
        }
    </script>
@endpush
