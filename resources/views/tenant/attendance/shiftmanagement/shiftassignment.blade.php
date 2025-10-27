<?php $page = 'shift-management'; ?>
@extends('layout.mainlayout')
@include('components.modal-popup', [
  'branches' => $branches,
  'departments' => $departments,
  'designations' => $designations,
  'employees' => $employees,
  'shifts' => $shifts,
])
@section('content')
  <!-- Page Wrapper -->
  <div class="page-wrapper">
      <div class="content">

      <!-- Breadcrumb -->
      <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
      <div class="my-auto mb-2">
          <h2 class="mb-1">Shift Management</h2>
          <nav>
          <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item">
              <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
          </li>
          <li class="breadcrumb-item">
              Attendance
          </li>
          <li class="breadcrumb-item active" aria-current="page">Shift & Schedule</li>
          </ol>
          </nav>
      </div>

      <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
          @if(in_array('Export', $permission))
          <div class="me-2 mb-2">
          <div class="dropdown">
          <a href="javascript:void(0);"
              class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
              data-bs-toggle="dropdown">
              <i class="ti ti-file-export me-1"></i>Export
          </a>
          <ul class="dropdown-menu  dropdown-menu-end p-3" style="z-index:1050;position:absolute">
              <li>
              <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                  class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
              </li>
              <li>
              <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                  class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
              </li>
              <li>
              <a href="javascript:void(0);" class="dropdown-item rounded-1"><i
                  class="ti ti-file-type-xls me-1"></i>Download Template</a>
              </li>
          </ul>
          </div>
          </div>
          @endif
          @if(in_array('Create', $permission))
          <div class="d-flex gap-2 mb-2">
          <a href="#" data-bs-toggle="modal" data-bs-target="#assign_shift_modal"
          class="btn btn-primary d-flex align-items-center">
          <i class="ti ti-circle-plus me-2"></i>Assign Shift
          </a>
          <a href="{{ route('shift-list') }}" class="btn btn-secondary d-flex align-items-center">
          <i class="ti ti-arrow-right me-2"></i>Shift List
          </a>
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
      <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
          <h5>Schedule List</h5>
          <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
            <div class="me-3">
          <div class="input-icon-end position-relative">
              <input type="text" class="form-control date-range bookingrange"
              placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
              <span class="input-icon-addon">
              <i class="ti ti-chevron-down"></i>
              </span>
          </div>
          </div>
          <div class="form-group me-2">
          <select name="branch_filter" id="branch_filter" class="select2 form-select" style="width:150px;">
              <option value="" selected>All Branches</option>
              @foreach ($branches as $branch)
              <option value="{{ $branch->id }}">{{ $branch->name }}</option>
              @endforeach
          </select>
          </div>
          <div class="form-group me-2">
          <select name="department_filter" id="department_filter" class="select2 form-select" style="width:150px;">
              <option value="" selected>All Departments</option>
              @foreach ($departments as $department)
              <option value="{{ $department->id }}">{{ $department->department_name }}</option>
              @endforeach
          </select>
          </div>
          <div class="form-group me-2">
          <select name="designation_filter" id="designation_filter" class="select2 form-select" style="width:150px;">
              <option value="" selected>All Designations</option>
              @foreach ($designations as $designation)
              <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
              @endforeach
          </select>
          </div>
          </div>
      </div>
         <div class="card-body p-0">
         <div class="custom-datatable-filter table-responsive">
    <table class="table datatable table-bordered text-center align-middle mb-0" id="shiftTable">
  <thead class="table-light">
    <tr>
      <th>Employee</th>
      @foreach ($dateRange as $date)
    <th data-date="{{ $date->format('Y-m-d') }}">
      {{ $date->format('m/d/Y') }}<br>
      <small>{{ $date->format('D') }}</small>
    </th>
      @endforeach
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="shiftAssignmentTableBody">
    @foreach ($employees as $emp)
      <tr data-user-id="{{ $emp->id }}">
    <td class="text-start">
      <div class="d-flex align-items-center">
        @if(isset($emp->personalInformation) && !empty($emp->personalInformation->profile_picture))
      <img src="{{ asset('storage/' . $emp->personalInformation->profile_picture) }}" class="rounded-circle me-2" width="40" height="40" alt="Profile Picture">
        @else
      <img src="https://via.placeholder.com/40" class="rounded-circle me-2" width="40" height="40" alt="Profile Picture">
        @endif
        {{ $emp->personalInformation->first_name ?? 'N/A'}} {{ $emp->personalInformation->last_name ?? 'N/A'}}
      </div>
    </td>

    @foreach ($dateRange as $date)
      @php
    $dateStr = $date->format('Y-m-d');
    $shifts = $assignments[$emp->id][$dateStr] ?? [];
      @endphp
      <td class="p-2 align-middle">
        @if (empty($shifts))
      <span class="badge bg-danger">No Shift</span>
        @else
      @foreach ($shifts as $shift)
        @if (!empty($shift['rest_day']))
          <span class="badge bg-warning text-dark">Rest Day</span>
        @else
          <div class="badge bg-outline-success d-flex flex-column align-items-center mb-1">
        <div>{{ $shift['name'] }}</div>
        <small>{{ $shift['start_time'] }} - {{ $shift['end_time'] }}</small>
          </div>
        @endif
      @endforeach
        @endif
      </td>
    @endforeach

    <td>
      <button class="btn btn-sm btn-danger deleteEmployeeShiftBtn"
          data-employee-id="{{ $emp->id }}">
        <i class="ti ti-trash"></i> Delete
      </button>
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

@endsection

<!-- Delete Employee Shifts Modal -->
<div class="modal fade" id="deleteEmployeeShiftModal" tabindex="-1" aria-labelledby="deleteEmployeeShiftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
  <form id="deleteEmployeeShiftForm">
    <div class="modal-header">
    <h5 class="modal-title" id="deleteEmployeeShiftModalLabel">Delete All Assigned Shifts</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body text-center">
    <input type="hidden" id="deleteEmployeeId" name="employee_id">
    <p>Are you sure you want to delete <strong>all assigned shifts</strong> for this employee?</p>
    </div>
    <div class="modal-footer justify-content-center">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-danger">Delete</button>
    </div>
  </form>
    </div>
  </div>
</div>


@push('scripts')
    <script>
  // ---------- Helpers (shared) ----------
  function selectFirstNonEmpty($sel) {
    const firstVal = $sel.find('option')
      .map((i, o) => o.value)
      .get()
      .find(v => v !== '');
    if (firstVal !== undefined) {
      $sel.val([String(firstVal)]).trigger('change');
      return true;
    }
    return false;
  }

  function rebuildOptions($sel, items, allLabel) {
    let html = `<option value="">All ${allLabel}</option>`;
    (items || []).forEach(i => {
      html += `<option value="${i.id}">${i.name || i.department_name || i.designation_name}</option>`;
    });
    $sel.html(html);
  }
    </script>

    <script>
  // ---------- Table filter + header ----------
  function fetchFilteredData() {
    let dateRange = $('#dateRange_filter').val();
    let [start_date, end_date] = (dateRange || '').split(' - ');
    let branch_id = $('#branch_filter').val();
    let department_id = $('#department_filter').val();
    let designation_id = $('#designation_filter').val();

    $.ajax({
      url: "{{ route('shiftmanagement.filter') }}",
      method: "GET",
      data: {
    start_date, end_date, branch_id, department_id, designation_id
      },
      success: function (response) {
    if ($.fn.DataTable.isDataTable('#shiftTable')) {
      $('#shiftTable').DataTable().destroy();
    }
    updateTableHeader(response.dateRange || []);
    
    // Ensure the filtered HTML is rendered
    let $tbody = $('<div>').html(response.html || '');
    $tbody.find('tr').each(function() {
      const $row = $(this);
      const userId = $row.data('user-id');
      
      // Check if Actions column exists, if not add it
      if ($row.find('td:last .deleteEmployeeShiftBtn').length === 0) {
        const actionTd = `<td>
          <button class="btn btn-sm btn-danger deleteEmployeeShiftBtn"
              data-employee-id="${userId}">
            <i class="ti ti-trash"></i> Delete
          </button>
        </td>`;
        $row.append(actionTd);
      }
    });
    
    $('#shiftAssignmentTableBody').html($tbody.html());
    
    // Use setTimeout to ensure DOM is updated before initializing DataTable
    setTimeout(function() {
      const columnCount = $('#shiftTable thead tr th').length;
      const bodyColumnCount = $('#shiftTable tbody tr:first td').length;
      
      // Only initialize DataTable if column counts match
      if (columnCount === bodyColumnCount) {
        $('#shiftTable').DataTable({
          "autoWidth": false,
          "columnDefs": [
            { "orderable": false, "targets": -1 }
          ]
        });
      }
    }, 100);
      },
      error: function (xhr) {
    console.error(xhr.responseText);
      }
    });
  }

  // This function only updates the header, not the body!
  function updateTableHeader(dateRange) {
    let headerHtml = '<th>Employee</th>';
    (dateRange || []).forEach(date => {
      headerHtml += `<th data-date="${date.full}">
    ${date.short}<br><small>${date.day}</small>
  </th>`;
    });
    headerHtml += '<th>Actions</th>';
    $('#shiftTable thead tr').html(headerHtml);
  } 

  $(document).ready(function () {
    $('.select2').select2();

    let start = moment().startOf('isoWeek');
    let end   = moment().endOf('isoWeek');

    $('.bookingrange').daterangepicker({
      startDate: start,
      endDate: end,
      locale: { format: 'MM/DD/YYYY' }
    });

    $('#branch_filter, #department_filter, #designation_filter').on('change', fetchFilteredData);
    $('.bookingrange').on('apply.daterangepicker', fetchFilteredData);
  });
    </script>

    <script>
  // ---------- Page-level dependent filters (top of page, not modal) ----------
  function populateDropdown($select, items, placeholder = 'Select') {
    $select.empty();
    $select.append(`<option value="">All ${placeholder}</option>`);
    (items || []).forEach(item => {
      $select.append(`<option value="${item.id}">${item.name}</option>`);
    });
  }

  $(document).ready(function () {
    // Branch → Department/Designation (page filters)
    $('#branch_filter').on('input', function () {
      const branchId = $(this).val();

      $.get('/api/filter-from-branch', { branch_id: branchId }, function (res) {
    if (res.status === 'success') {
      populateDropdown($('#department_filter'),  res.departments,  'Departments');
      populateDropdown($('#designation_filter'), res.designations, 'Designations');

      selectFirstNonEmpty($('#department_filter'));
      selectFirstNonEmpty($('#designation_filter'));

      fetchFilteredData();
    }
      });
    });

    // Department → Designation (page filters)
    $('#department_filter').on('input', function () {
      const departmentId = $(this).val();
      const branchId = $('#branch_filter').val();

      $.get('/api/filter-from-department', {
    department_id: departmentId, branch_id: branchId
      }, function (res) {
    if (res.status === 'success') {
      if (res.branch_id) {
        $('#branch_filter').val(res.branch_id).trigger('change');
      }
      populateDropdown($('#designation_filter'), res.designations, 'Designations');

      selectFirstNonEmpty($('#designation_filter'));

      fetchFilteredData();
    }
      });
    });

    // Designation → (optional sync back to branch/department)
    $('#designation_filter').on('change', function () {
      const designationId = $(this).val();
      const branchId = $('#branch_filter').val();
      const departmentId = $('#department_filter').val();

      $.get('/api/filter-from-designation', {
    designation_id: designationId, branch_id: branchId, department_id: departmentId
      }, function (res) {
    if (res.status === 'success') {
      if (designationId === '') {
        populateDropdown($('#designation_filter'), res.designations, 'Designations');
        selectFirstNonEmpty($('#designation_filter'));
      } else {
        $('#branch_filter').val(res.branch_id).trigger('change');
        $('#department_filter').val(res.department_id).trigger('change');
      }
      fetchFilteredData();
    }
      });
    });
  });
    </script>

    <script>
  // ---------- Assignment type toggles ----------
  document.addEventListener("DOMContentLoaded", function() {
    const typeSelect   = document.getElementById("assignmentType");
    const daysWrapper  = document.getElementById("daysOfWeekWrapper");
    const customWrapper= document.getElementById("customDatesWrapper");

    if (!typeSelect) return;

    function applyType(type) {
      daysWrapper?.classList.add("d-none");
      customWrapper?.classList.add("d-none");
      if (type === "recurring") daysWrapper?.classList.remove("d-none");
      if (type === "custom")    customWrapper?.classList.remove("d-none");
    }

    applyType(typeSelect.value);
    typeSelect.addEventListener("change", function() { applyType(this.value); });
  });
    </script>

    <script>
  // ---------- Modal (Assign Shift) dynamic filters + submit ----------
  document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const authToken = localStorage.getItem('token');

    function handleSelectAll($sel) {
      const vals = $sel.val() || [];
      if (vals.includes('')) {
    const all = $sel.find('option').map((i, opt) => $(opt).val()).get().filter(v => v !== '');
    $sel.val(all).trigger('change');
    return true;
      }
      return false;
    }

    function updateEmployeeSelect(modal) {
      const allEmps  = modal.data('employees') || [];
      const deptIds  = modal.find('.department-select').val() || [];
      const desigIds = modal.find('.designation-select').val() || [];

      const filtered = allEmps.filter(emp => {
    if (deptIds.length  && !deptIds.includes(String(emp.department_id)))   return false;
    if (desigIds.length && !desigIds.includes(String(emp.designation_id))) return false;
    return true;
      });

      let opts = '<option value="">All Employee</option>';
      filtered.forEach(emp => {
    const u = emp.user?.personal_information;
    if (u) {
      opts += `<option value="${emp.user.id}">${u.last_name}, ${u.first_name}</option>`;
    }
      });

      modal.find('.employee-select').html(opts).trigger('change');
    }

    $(document).on('change', '.branch-select', function() {
      const $this = $(this);
      if (handleSelectAll($this)) return;

      const branchIds = $this.val() || [];
      const modal  = $this.closest('.modal');
      const depSel = modal.find('.department-select');
      const desSel = modal.find('.designation-select');
      const empSel = modal.find('.employee-select');
      const shiftSel = modal.find('#shiftAssignmentShiftId');

      depSel.html('<option value="">All Department</option>').trigger('change');
      desSel.html('<option value="">All Designation</option>').trigger('change');
      empSel.html('<option value="">All Employee</option>').trigger('change');
      modal.removeData('employees');

      if (!branchIds.length) return;

      $.ajax({
    url: '/api/shift-management/get-branch-data?' + $.param({ branch_ids: branchIds }),
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Authorization': authToken ? ('Bearer ' + authToken) : undefined
    },
    success(data) {
      rebuildOptions(depSel, data.departments || [], 'Department');

      if (Array.isArray(data.designations)) {
        rebuildOptions(desSel, data.designations || [], 'Designation');
      } else {
        desSel.html('<option value="">All Designation</option>').trigger('change');
      }

      modal.data('employees', data.employees || []);
      updateEmployeeSelect(modal);

      let sOpts = '<option value="">All Shift</option>';
      (data.shifts || []).forEach(s => { sOpts += `<option value="${s.id}">${s.name}</option>`; });
      shiftSel.html(sOpts).trigger('change');

      if (data.default_department_id) {
        depSel.val([String(data.default_department_id)]).trigger('change');
      } else {
        selectFirstNonEmpty(depSel);
      }

      if (Array.isArray(data.designations)) {
        if (data.default_designation_id) {
      desSel.val([String(data.default_designation_id)]).trigger('change');
        } else {
      selectFirstNonEmpty(desSel);
        }
      }
    },
    error() { alert('Failed to fetch branch data.'); }
      });
    });

    $(document).on('change', '.department-select', function() {
      const $this = $(this);
      if (handleSelectAll($this)) return;

      const deptIds = $this.val() || [];
      const modal = $this.closest('.modal');
      const desSel = modal.find('.designation-select');

      desSel.html('<option value="">All Designation</option>').trigger('change');
      updateEmployeeSelect(modal);

      if (!deptIds.length) return;

      $.ajax({
    url: '/api/shift-management/get-designations?' + $.param({ department_ids: deptIds }),
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Authorization': authToken ? ('Bearer ' + authToken) : undefined
    },
    success(data) {
      const list = Array.isArray(data) ? data : (data.designations || []);
      rebuildOptions(desSel, list, 'Designation');

      const defaultId = !Array.isArray(data) ? data.default_designation_id : null;
      if (defaultId) {
        desSel.val([String(defaultId)]).trigger('change');
      } else {
        selectFirstNonEmpty(desSel);
      }

      updateEmployeeSelect(modal);
    },
    error() { alert('Failed to fetch designations.'); }
      });
    });

    $(document).on('change', '.designation-select', function() {
      const $this = $(this);
      if (handleSelectAll($this)) return;
      updateEmployeeSelect($this.closest('.modal'));
    });

    $(document).on('change', '.employee-select', function() {
      handleSelectAll($(this));
    });
  });
    </script>

    <script>
  // ---------- Assign Shift form submission ----------
  document.addEventListener('DOMContentLoaded', () => {
    const form      = document.getElementById('assignShiftForm');
    if (!form) return;

    const typeEl    = document.getElementById('assignmentType');
    const daysChecks= document.querySelectorAll('input[name="days_of_week[]"]');
    const authToken = localStorage.getItem('token');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const $shiftSelect = $('#shiftAssignmentShiftId');
    const $isRestDay   = $('#isRestDay');

    $shiftSelect.select2({ allowClear: true });

    $isRestDay.on('change', function() {
      const isChecked = $(this).is(':checked');
      if (isChecked) $shiftSelect.val([]).trigger('change.select2');
      $shiftSelect.prop('disabled', isChecked);
    });

    async function sendAssignment({ override = false, skip = false } = {}) {
      const isRestDay = document.getElementById('isRestDay').checked;

      const payload = {
    user_id: [].concat($('#shiftAssignmentUserId').val() || []),
    type: typeEl.value,
    start_date: document.getElementById('shiftAssignmentStartDate').value,
    end_date: document.getElementById('shiftAssignmentEndDate').value || null,
    is_rest_day: isRestDay ? 1 : 0,
    override: override ? 1 : 0,
    skip_rest_check: skip ? 1 : 0,
      };

      if (!isRestDay) payload.shift_id = [].concat($('#shiftAssignmentShiftId').val() || []);

      if (typeEl.value === 'recurring') {
    payload.days_of_week = Array.from(daysChecks).filter(cb => cb.checked).map(cb => cb.value);
    payload.custom_dates = [];
      } else if (typeEl.value === 'custom') {
    payload.custom_dates = [].concat($('#customDates').val() || []);
    payload.days_of_week = [];
      }

      const res = await fetch('/api/shift-management/shift-assignment', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': authToken ? `Bearer ${authToken}` : undefined,
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (res.ok) {
    toastr?.success?.(data.message || 'Shift assigned successfully!');
    fetchFilteredData();
    $('#assign_shift_modal').modal('hide');
    return;
      }

      if (data.requires_override && !override && !skip) {
    const doOverride = confirm(
      (data.message || 'Conflict with rest day was detected.') +
      "\n\nOK = Override the rest day\nCancel = Skip the rest day"
    );
    if (doOverride) return sendAssignment({ override: true,  skip: false });

    const doSkip = confirm(
      "Are you sure you want to SKIP the conflicting rest day?\n\nOK = Skip it\nCancel = Cancel assignment"
    );
    if (doSkip) return sendAssignment({ override: false, skip: true });

    toastr?.info?.('Shift assignment cancelled.');
    return;
      }

      toastr?.error?.(data.message || 'Failed to assign shift.');
    }

    form.addEventListener('submit', e => {
      e.preventDefault();
      sendAssignment();
    });
  });
    </script>

    <script>
  // ---------- Ensure employees load when the modal opens ----------
  $(document).on('shown.bs.modal', '#assign_shift_modal', function () {
    const $modal  = $(this);
    const $branch = $modal.find('#shiftAssignmentBranchId');

    if (!$branch.hasClass('select2-hidden-accessible')) {
      $branch.select2({ width: '100%' });
    }

    const selected = $branch.find('option:selected').map(function(){ return this.value; }).get();

    if (selected.length) {
      $branch.val(selected).trigger('change');
    } else {
      const firstVal = $branch.find('option').first().val();
      if (firstVal) $branch.val([firstVal]).trigger('change');
    }
  });
    </script>

<script>
  $(document).ready(function() {
    let deleteModal = new bootstrap.Modal(document.getElementById('deleteEmployeeShiftModal'));

    // When Delete button is clicked
    $(document).on('click', '.deleteEmployeeShiftBtn', function() {
      let empId = $(this).data('employee-id');
      $('#deleteEmployeeId').val(empId);
      deleteModal.show();
    });

    // When Confirm Delete form is submitted
    $('#deleteEmployeeShiftForm').on('submit', function(e) {
      e.preventDefault();

      const empId = $('#deleteEmployeeId').val();
      const csrfToken = $('meta[name="csrf-token"]').attr('content');

      $.ajax({
        // ✅ Updated to match your route definition
        url: '/api/shift-management/shift-assignment/delete/' + empId,
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(res) {
          toastr.success(res.message || 'Shift assignment deleted successfully.');
          deleteModal.hide();
          fetchFilteredData(); // Refresh the table dynamically
        },
        error: function(xhr) {
          console.error(xhr.responseText);
          let message = 'Failed to delete shift assignment.';
          if (xhr.status === 403) message = 'You do not have permission to delete.';
          toastr.error(message);
        }
      });
    });
  });
</script>


@endpush
