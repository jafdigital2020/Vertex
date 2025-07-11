<?php $page = 'leave-settings'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Leave Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Leave Settings</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Create', $permission))
                        <div class="mb-2">
                            <a href="{{ route('leave-type') }}" class="btn btn-primary d-flex align-items-center"><i
                                    class="ti ti-circle-plus me-2"></i>Add
                                Leave Type</a>
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

            <!-- Leaves Info -->
            <div class="row">
                @foreach ($leaveTypes as $leaveType)
                    @php
                        $modalId = 'leave_settings_' . $leaveType->id;
                    @endphp
                    <div class="col-xl-4 col-md-6">
                        <div class="card">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-check-md form-switch me-1">
                                        <label class="form-check-label">
                                            <input class="form-check-input leave-type-toggle" type="checkbox" role="switch"
                                                data-id="{{ $leaveType->id }}" data-name="{{ $leaveType->name }}"
                                                {{ $leaveType->status === 'active' ? 'checked' : '' }} name="status"
                                                id="leaveTypeStatus">
                                        </label>
                                    </div>
                                    <h6 class="d-flex align-items-center">{{ $leaveType->name }}</h6>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="text-decoration-underline me-2"
                                        data-bs-toggle="modal" data-bs-target="#assign_user"
                                        data-leave-type-id="{{ $leaveType->id }}">Assign User</a>

                                    <a href="{{ url('leave/leave-settings/'. $leaveType->id . '/assigned-users' ) }}"
                                        class="me-2"
                                        title="View Assigned Users">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="javascript:void(0);"
                                        data-bs-toggle="modal"
                                        data-bs-target="#{{ $modalId }}"
                                        title="Edit Settings">
                                        <i class="ti ti-settings"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @component('components.modal-popup', [
                        'leaveType' => $leaveType,
                        'modalId' => $modalId,
                        'branches' => $branches,
                        'departments' => $departments,
                        'designations' => $designations,
                    ])
                    @endcomponent
                @endforeach
            </div>
            <!-- /Leaves Info -->
        </div>

        {{-- Footer --}}
        @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->
@endsection

@push('scripts')
    {{-- Status Toggle --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let authToken = localStorage.getItem("token");
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            document.querySelectorAll('.leave-type-toggle').forEach(toggle => {
                toggle.addEventListener('change', async () => {
                    const id = toggle.dataset.id;
                    const name = toggle.dataset.name;
                    const status = toggle.checked ? 'active' : 'inactive';
                    const actionTxt = status === 'active' ? 'activated' : 'deactivated';

                    try {
                        const res = await fetch(`/api/leave/leave-settings/status/${id}/`, {
                            method: 'PATCH',
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: JSON.stringify({
                                status
                            })
                        });

                        const json = await res.json();

                        if (!res.ok) {
                            throw new Error(json.message || `HTTP ${res.status}`);
                        }

                        if (json.success) {
                            toastr.success(
                                `${name} has been ${actionTxt}.`,
                                'Status Updated'
                            );
                        } else {
                            throw new Error(json.message || 'Update failed');
                        }

                    } catch (err) {
                        console.error("Toggle error:", err);
                        toggle.checked = !toggle.checked;

                        toastr.error(
                            err.message ||
                            `Could not ${actionTxt} ${name}. Please try again.`,
                            'Error'
                        );
                    }

                });
            });
        });
    </script>

    {{-- Form Handling Submission --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem("token");
            const fieldNameMap = {
                advance_notice_days: "Advance Notice Days",
                allow_half_day: "Half-Day",
                allow_backdated: "Backdated Leave",
                backdated_days: "Backdated Days",
                require_documents: "Document Requirement"
            };

            // Attach to every leave‐settings form
            document.querySelectorAll("form.leaveSettingsForm").forEach(form => {
                const leaveTypeId = form.dataset.leaveTypeId;
                const apiGetRoute = `/api/leave/leave-settings/${leaveTypeId}`;
                const apiUpdateRoute = `/api/leave/leave-settings/create`;

                // Inputs scoped to this form
                const noticeInput = form.querySelector('[name="advance_notice_days"]');
                const halfDayToggle = form.querySelector('[name="allow_half_day"]');
                const backToggle = form.querySelector('[name="allow_backdated"]');
                const backSection = form.querySelector(".backdatedDaysSection");
                const backDaysInput = form.querySelector('[name="backdated_days"]');
                const docToggle = form.querySelector('[name="require_documents"]');

                // Show/hide backdated‐days
                function toggleBackSection() {
                    const show = backToggle.checked;
                    backSection.classList.toggle("d-none", !show);
                    backDaysInput.required = show;
                }
                backToggle.addEventListener("change", () => {
                    toggleBackSection();
                    saveField("allow_backdated", backToggle.checked ? 1 : 0);
                });

                // Wire up other change events
                noticeInput.addEventListener("change", e => saveField(e.target.name, e.target.value));
                halfDayToggle.addEventListener("change", e => saveField(e.target.name, e.target.checked ?
                    1 : 0));
                backDaysInput.addEventListener("change", e => saveField(e.target.name, e.target.value));
                docToggle.addEventListener("change", e => saveField(e.target.name, e.target.checked ? 1 :
                    0));

                // Save a single field
                async function saveField(name, value) {
                    try {
                        const payload = {
                            leave_type_id: leaveTypeId,
                            [name]: value
                        };
                        const res = await fetch(apiUpdateRoute, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                                "Authorization": `Bearer ${authToken}`
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (!res.ok) throw data;
                        toastr.success(`${fieldNameMap[name] || name} updated.`);
                    } catch (err) {
                        toastr.error(err.message || `Failed to update ${fieldNameMap[name] || name}.`);
                        console.error(err);
                    }
                }

                // Load this leave type’s settings
                async function populate() {
                    try {
                        const res = await fetch(apiGetRoute, {
                            headers: {
                                "Accept": "application/json",
                                "Authorization": `Bearer ${authToken}`
                            }
                        });
                        const data = await res.json();
                        if (!res.ok) throw data;

                        noticeInput.value = data.advance_notice_days;
                        halfDayToggle.checked = Boolean(data.allow_half_day);
                        backToggle.checked = Boolean(data.allow_backdated);
                        backDaysInput.value = data.backdated_days;
                        docToggle.checked = Boolean(data.require_documents);

                        toggleBackSection();
                    } catch (err) {
                        toastr.error("Failed to load leave settings.");
                        console.error(err);
                    }
                }

                // Initial load
                populate();
            });
        });
    </script>

    {{-- Filter --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem('token');

            // Helper: when you copy‐paste the empty‐value trick, but we don't need it anymore
            function handleSelectAll($sel) {
                const vals = $sel.val() || [];
                if (vals.includes('')) {
                    const all = $sel.find('option')
                        .map((i, opt) => $(opt).val())
                        .get()
                        .filter(v => v !== '');
                    $sel.val(all).trigger('change');
                    return true;
                }
                return false;
            }

            // Rebuild Employee list based on selected Departments & Designations
            function updateEmployeeSelect(container) {
                const allEmps = container.data('employees') || [];
                const deptIds = container.find('.department-select').val() || [];
                const desigIds = container.find('.designation-select').val() || [];

                const filtered = allEmps.filter(emp => {
                    if (deptIds.length && !deptIds.includes(String(emp.department_id))) return false;
                    if (desigIds.length && !desigIds.includes(String(emp.designation_id))) return false;
                    return true;
                });

                // Build only real options—no “all” placeholder
                let opts = '';
                filtered.forEach(emp => {
                    const u = emp.user?.personal_information;
                    if (u) {
                        opts += `<option value="${emp.user.id}">${u.last_name}, ${u.first_name}</option>`;
                    }
                });


                container.find('.employee-select')
                    .html(opts)
                    .trigger('change');

                window.employeeDualList.bootstrapDualListbox('refresh', true);
            }

            // Whenever branch changes → fetch data & populate
            $(document).on('change', '.branch-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;

                const branchIds = $this.val() || [];
                const form = $('#leaveSettingsAddUser');
                const depSel = form.find('.department-select');
                const desSel = form.find('.designation-select');
                const empSel = form.find('.employee-select');

                // reset downstream
                depSel.html('').trigger('change');
                desSel.html('').trigger('change');
                empSel.html('').trigger('change');
                form.removeData('employees');

                if (!branchIds.length) return;

                $.ajax({
                    url: '/api/shift-management/get-branch-data?' + $.param({
                        branch_ids: branchIds
                    }),
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': 'Bearer ' + authToken
                    },
                    success(data) {
                        let dOpts = '<option value="">All Department</option>';
                        data.departments.forEach(d => {
                            dOpts +=
                                `<option value="${d.id}">${d.department_name}</option>`;
                        });
                        depSel.html(dOpts).trigger('change');

                        form.data('employees', data.employees || []);
                        updateEmployeeSelect(form);
                    },
                    error() {
                        alert('Failed to fetch branch data.');
                    }
                });
            });

            // Department change → re-filter & optionally fetch designations
            $(document).on('change', '.department-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;

                const deptIds = $this.val() || [];
                const form = $('#leaveSettingsAddUser');
                const desSel = form.find('.designation-select');

                desSel.html('').trigger('change');
                updateEmployeeSelect(form);

                if (!deptIds.length) return;

                $.ajax({
                    url: '/api/shift-management/get-designations?' + $.param({
                        department_ids: deptIds
                    }),
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': 'Bearer ' + authToken
                    },
                    success(data) {
                        let o = '<option value="">All Designation</option>';
                        data.forEach(d => {
                            o += `<option value="${d.id}">${d.designation_name}</option>`;
                        });
                        desSel.html(o).trigger('change');
                    },
                    error() {
                        alert('Failed to fetch designations.');
                    }
                });
            });

            // Designation change → re-filter employees
            $(document).on('change', '.designation-select', function() {
                const $this = $(this);
                if (handleSelectAll($this)) return;
                updateEmployeeSelect($('#leaveSettingsAddUser'));
            });
        });
    </script>

    {{-- Assign User --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('assign_user');
            const form = document.getElementById('leaveSettingsAddUser');
            let leaveTypeId;

            modalEl.addEventListener('show.bs.modal', e => {
                leaveTypeId = e.relatedTarget.getAttribute('data-leave-type-id');
            });

            form.addEventListener('submit', async e => {
                e.preventDefault();
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const selected = Array.from(
                    form.querySelector('.employee-select').selectedOptions
                ).map(o => o.value);

                try {
                    const res = await fetch(
                        '/api/leave-entitlements/assign-users', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                leave_type_id: leaveTypeId,
                                user_ids: selected
                            })
                        }
                    );
                    const body = await res.json();
                    if (!res.ok) throw body;
                    toastr.success(body.message);
                    bootstrap.Modal.getInstance(modalEl).hide();

                    setTimeout(() => {
                        location.reload();
                    }, 800);
                } catch (err) {
                    const msg = err.message || (err.errors && Object.values(err.errors)[0][0]) ||
                        'Submission failed.';
                    toastr.error(msg);
                }
            });
        });
    </script>
@endpush
