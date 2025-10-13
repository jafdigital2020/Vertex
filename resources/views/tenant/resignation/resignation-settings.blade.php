<?php $page = 'resignation'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Resignation Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Resignation
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Resignation Settings</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap "> 
                    @if (in_array('Create', $permission))
                    <div class="mb-2">
                        <a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#assign_resignation_hr"><i class="ti ti-circle-plus me-2"></i>Assign User</a>
                    </div>
                    @endif
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div> 
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5 class="d-flex align-items-center">Assigned Resignation Users</h5>
                            <div class="d-flex align-items-center flex-wrap row-gap-3">
                               
                            </div>
                        </div>
                        <div class="card-body p-0">

                            <div class="custom-datatable-filter table-responsive">
                                <table class="table datatable">
                                    <thead class="thead-light">
                                        <tr class="text-center"> 
                                          <th>Name</th>
                                          <th>Branch</th>
                                          <th>Department</th>
                                          <th>Designation</th>
                                          <th>Assigned By</th>
                                          <th>Assigned Date</th>
                                          <th>Status</th>
                                          <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                                          @foreach ($resignationHR as $item)
                                              <tr>
                                                <td>{{ $item->hr->personalInformation->first_name ?? '' }} {{ $item->hr->personalInformation->last_name ?? '' }}</td>
                                                <td>{{$item->hr->employmentDetail->branch->name ?? ''}}</td>
                                                <td>{{$item->hr->employmentDetail->department->department_name ?? ''}}</td>
                                                <td>{{$item->hr->employmentDetail->designation->designation_name ?? ''}}</td>
                                                <td>{{ $item->assignedBy->personalInformation->first_name ?? '' }} {{ $item->assignedBy->personalInformation->last_name ?? '' }}</td>
                                                <td>{{ $item->assignedAt }}</td>
                                                <td>{{ $item->status }}</td>
                                                <td></td>
                                              </tr>
                                          @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
 <!-- Assign HR Modal -->
    <div class="modal fade" id="assign_resignation_hr" tabindex="-1" aria-labelledby="assign_resignation_hr_label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
        
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="assign_resignation_hr_label">Assign HR to Handle Resignations</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <form id="assignHrForm">

            <!-- Branch -->
            <div class="mb-3">
                <label class="form-label fw-bold">Branch</label>
                <select class="form-select" id="branch" name="branch">
                <option value="">-- Select Branch --</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
                </select>
            </div>

            <!-- Department -->
            <div class="mb-3">
                <label class="form-label fw-bold">Department</label>
                <select class="form-select" id="department" name="department" disabled>
                <option value="">-- Select Department --</option>
                </select>
            </div>

            <!-- Designation -->
            <div class="mb-3">
                <label class="form-label fw-bold">Designation</label>
                <select class="form-select" id="designation" name="designation" disabled>
                <option value="">-- Select Designation --</option>
                </select>
            </div>

            <!-- Employees (multi-select) -->
            <div class="mb-3">
                <label class="form-label fw-bold">Employees</label>
                <select class="form-select" id="employee" name="hr_ids[]" multiple disabled>
                <option value="">-- Select Employees --</option>
                </select>
                <small class="text-muted">Hold <b>Ctrl</b> (Windows) or <b>Cmd</b> (Mac) to select multiple employees.</small>
            </div>

            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="btnAssignHr" class="btn btn-primary">Assign Selected</button>
        </div>

        </div>
    </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const branchSelect = document.getElementById('branch');
  const departmentSelect = document.getElementById('department');
  const designationSelect = document.getElementById('designation');
  const employeeSelect = document.getElementById('employee');

  branchSelect.addEventListener('change', async function () {
    const branchId = this.value;
    resetSelect(departmentSelect, 'Select Department');
    resetSelect(designationSelect, 'Select Designation', true);
    resetSelect(employeeSelect, 'Select Employee', true);

    if (!branchId) return;

    const res = await fetch(`/get-departments-by-branch/${branchId}`);
    const data = await res.json();
    populateDepartments(departmentSelect, data, 'Select Department');
  });

  departmentSelect.addEventListener('change', async function () {
    const deptId = this.value;
    resetSelect(designationSelect, 'Select Designation');
    resetSelect(employeeSelect, 'Select Employee', true);

    if (!deptId) return;

    const res = await fetch(`/get-designations-by-department/${deptId}`);
    const data = await res.json();
    populateDesignations(designationSelect, data, 'Select Designation');
  });

  designationSelect.addEventListener('change', async function () {
    const designationId = this.value;
    resetSelect(employeeSelect, 'Select Employee');

    if (!designationId) return;

    const res = await fetch(`/get-employees-by-designation/${designationId}`);
    const data = await res.json();
    populateEmployees(employeeSelect, data, 'Select Employee');
  });

  // Utility functions
  function resetSelect(select, placeholder, disable = false) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
    select.disabled = disable;
  }

  function populateDepartments(select, items, placeholder) {
    select.disabled = false;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(item => {
      const option = document.createElement('option');
      option.value = item.id;
      option.textContent = item.department_name;
      select.appendChild(option);
    });
  }

  function populateDesignations(select, items, placeholder) {
    select.disabled = false;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(item => {
      const option = document.createElement('option');
      option.value = item.id;
      option.textContent = item.designation_name;
      select.appendChild(option);
    });
  }

  function populateEmployees(select, items, placeholder) {
    select.disabled = false;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(item => {
      const option = document.createElement('option');
      option.value = item.id;
      option.textContent = `${item.first_name} ${item.last_name}`;
      select.appendChild(option);
    });
  } 
  // Handle form submission
  document.getElementById('assignHrForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const selectedEmployees = Array.from(employeeSelect.selectedOptions).map(opt => opt.value);
    if (selectedEmployees.length === 0) {
      alert('Please select at least one employee.');
      return;
    }

    const payload = {
      hr_ids: selectedEmployees,
      assigned_by: {{ auth()->id() }},
      _token: '{{ csrf_token() }}'
    };

    const response = await fetch('{{ route("assignMultipleResignationHr") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await response.json();
    if (result.success) {
      alert('HRs successfully assigned!');
      location.reload();
    } else {
      alert('Something went wrong.');
    }
  });
});
</script>


      @include('layout.partials.footer-company') 

    </div>  

    @component('components.modal-popup')
    @endcomponent

@endsection
