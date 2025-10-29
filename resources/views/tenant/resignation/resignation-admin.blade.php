<?php $page = 'resignation'; ?>
@extends('layout.mainlayout')
@section('content')
  <style>
    .remarks-chat {
        background-color: #f9f9f9;
        padding: 10px;
        border-radius: 8px;
        overflow-y: auto;
    }

    .chat-bubble {
        padding: 10px 14px;
        border-radius: 15px;
        max-width: 70%;
        word-wrap: break-word;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
 
    .chat-right {
        background-color: #f1f1f1;
        color: #333;
        text-align: left;
        border-top-left-radius: 0;
    }
 
    .chat-left {
        background-color: #d1f7d6;
        color: #333;
        text-align: left;
        border-top-right-radius: 0;
    }

   </style>
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Resignation Admin</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Resignation
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Resignation Admin</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap "> 
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- Resignation List -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5 class="d-flex align-items-center">Resignation List</h5>
                            <div class="d-flex align-items-center flex-wrap row-gap-3">
                                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3"> 
                                    <div class="me-3">
                                        <div class="input-icon-end position-relative">
                                            <input type="text" class="form-control date-range bookingrange-filtered"
                                                placeholder="dd/mm/yyyy - dd/mm/yyyy" id="dateRange_filter">
                                            <span class="input-icon-addon">
                                                <i class="ti ti-chevron-down"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group me-2" style="max-width:200px;">
                                        <select name="branch_filter" id="branch_filter" class="select2 form-select" style="width:150px;"  oninput="filter()">
                                            <option value="" selected>All Branches</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group me-2">
                                        <select name="department_filter" id="department_filter" class="select2 form-select" style="width:150px;"
                                            oninput="filter()">
                                            <option value="" selected>All Departments</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group me-2">
                                        <select name="designation_filter" id="designation_filter" class="select2 form-select" style="width:150px;"
                                            oninput="filter()">
                                            <option value="" selected>All Designations</option>
                                            @foreach ($designations as $designation)
                                                <option value="{{ $designation->id }}">{{ $designation->designation_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group me-2">
                                        <select name="status_filter" id="status_filter" class="select2 form-select" style="width:150px;"
                                            oninput="filter()">
                                            <option value="" selected>All Status</option> 
                                            <option value="0">For Approval</option>
                                            <option value="1">For Acceptance</option>
                                            <option value="3">For Clearance</option>
                                            <option value="4">Resigned</option>
                                            <option value="2">Rejected</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">

                            <div class="custom-datatable-filter table-responsive">
                                <table class="table datatable" id="resignationAdminTable">
                                    <thead class="thead-light">
                                        <tr class="text-center"> 
                                            <th>Date Filed</th>
                                            <th class="text-center">Resigning Employee</th> 
                                            <th class="text-center">Branch</th>
                                            <th class="text-center">Department</th>
                                            <th class="text-center">Designation</th>
                                            <th class="text-center">Resignation Letter</th>  
                                            <th class="text-center">Date Accepted</th>
                                            <th class="text-center">Remaining Days</th>
                                            <th class="text-center">Resignation Date</th>
                                            <th class="text-center">Remarks</th>
                                            <th class="text-center">Status</th> 
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="resignationAdminTableBody"> 
                                        @foreach ($resignations as $resignation)
                                            <tr class="text-center">
                                                <td>{{$resignation->date_filed}}</td>
                                                <td>{{$resignation->personalInformation->first_name ?? '' }} {{$resignation->personalInformation->last_name ?? '' }}</td> 
                                                <td>{{$resignation->employmentDetail->branch->name ?? ''}}</td>
                                                <td>{{$resignation->employmentDetail->department->department_name ?? ''}}</td>
                                                <td>{{$resignation->employmentDetail->designation->designation_name ?? ''}}</td>
                                                <td>
                                                    <button 
                                                        class="btn btn-sm btn-primary"
                                                        onclick="viewResignationFile('{{ asset('storage/' . $resignation->resignation_file) }}', '{{$resignation->reason}}')">
                                                        View <i class="fa fa-file"></i>
                                                    </button>
                                                </td>  
                                                <td>{{$resignation->accepted_date ?? '-'}}</td>    
                                                 @php
                                                    if ($resignation->resignation_date !== null) {
                                                        $remainingDays = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($resignation->resignation_date), false);
                                                    } else {
                                                        $remainingDays = null;
                                                    }
                                                @endphp

                                                <td>
                                                    @if ($remainingDays === null)
                                                        -
                                                    @elseif ($remainingDays > 0)
                                                        {{ $remainingDays }} days
                                                    @else
                                                        Expired
                                                    @endif
                                                </td>

                                                <td>{{$resignation->resignation_date ?? '-'}}</td>
                                                   <td>
                                                    @if($resignation->status_remarks !== null || $resignation->accepted_remarks !== null)
                                                         <button 
                                                        class="btn btn-sm btn-primary"
                                                        onclick="viewResignationRemarks( '{{$resignation->id }}')">
                                                        View <i class="fa fa-sticky-note"></i>
                                                    </button>
                                                    @else 
                                                    -
                                                    @endif
                                                </td>
                                                <td>
                                                     @if($resignation->status === 0) 
                                                    <span>For Approval</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date === null )
                                                    <span>For Acceptance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 0)
                                                    <span>For Clearance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_date !== null  && $resignation->cleared_status === 1 )
                                                    <span>Resigned</span>
                                                    @elseif($resignation->status === 2)
                                                    <span>Rejected</span>
                                                    @endif
                                                </td>
                                             
                                                <td> 
                                                    @if($resignation->status === 0)
                                                        <button class="btn btn-success btn-sm" onclick="openApprovalModal({{ $resignation->id }}, 'approve')">
                                                            Approve
                                                        </button> 
                                                        <button class="btn btn-danger btn-sm" onclick="openApprovalModal({{ $resignation->id }}, 'reject')">
                                                            Reject
                                                        </button>  
                                                    @endif
                                                 
                                                </td>
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
      <div class="modal fade" id="viewResignationModal" tabindex="-1" aria-labelledby="viewResignationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resignation Letter Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body text-center">

                    <!-- Reason Section -->
                    <div id="resignationReasonContainer" class="mb-4 text-start d-none">
                        <h6 class="fw-bold">Reason for Resignation:</h6>
                        <p id="resignationReasonText" class="border rounded p-2 bg-light"></p>
                    </div>
                    <!-- File Preview Section -->
                    <iframe id="resignationPreviewFrame" src="" style="width:100%;height:80vh;border:none;display:none;"></iframe>

                    <div id="resignationWordNotice" class="d-none">
                        <p>This file cannot be previewed directly. Click below to open it in Office viewer:</p>
                        <a id="resignationWordLink" href="#" target="_blank" class="btn btn-primary">Open Document</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="approveResignationModal" tabindex="-1" aria-labelledby="approveResignationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="approvalModalTitle">Approve Resignation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="approveResignationForm">
                        <input type="hidden" id="resignationId" name="resignation_id">
                        <input type="hidden" id="approvalAction" name="action">

                        <div class="mb-3">
                            <label for="status_remarks" class="form-label fw-bold">Remarks</label>
                            <textarea id="status_remarks" name="status_remarks" class="form-control" rows="4" maxlength="500" placeholder="Enter your remarks (optional)"></textarea>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="submitApprovalBtn" class="btn btn-success">Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> 
<div class="modal fade" id="viewRemarksModal" tabindex="-1" aria-labelledby="viewRemarksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRemarksModalLabel">Resignation Remarks</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="remarksContent" class="text-dark"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>  
 
  @push('scripts')
    <script> 
     if ($('.bookingrange-filtered').length > 0) {

            var start = moment().subtract(29, 'days');
            var end = moment();

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

        $('#dateRange_filter').on('apply.daterangepicker', function(ev, picker) {
            filter();
        });

        function filter() {
            const dateRange = $('#dateRange_filter').val();
            const branch = $('#branch_filter').val();
            const department = $('#department_filter').val();
            const designation = $('#designation_filter').val();
            const status = $('#status_filter').val();

            $.ajax({
                url: '{{ route('resignation-admin-filter') }}',
                type: 'GET',
                data: {
                    branch,
                    department,
                    designation,
                    dateRange,
                    status,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#resignationAdminTable').DataTable().destroy();
                        $('#resignationAdminTableBody').html(response.html);
                        $('#resignationAdminTable').DataTable();
                        
                    } else {
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

   <script>

    function viewResignationFile(fileUrl, reason) {
        const fileExtension = fileUrl.split('.').pop().toLowerCase();
        const iframe = document.getElementById('resignationPreviewFrame');
        const wordNotice = document.getElementById('resignationWordNotice');
        const wordLink = document.getElementById('resignationWordLink');
        const reasonContainer = document.getElementById('resignationReasonContainer');
        const reasonText = document.getElementById('resignationReasonText');
 
        iframe.style.display = 'none';
        wordNotice.classList.add('d-none');
 
        if (fileExtension === 'pdf') {
            iframe.src = fileUrl;
            iframe.style.display = 'block';
        } else if (fileExtension === 'doc' || fileExtension === 'docx') {
            wordLink.href = `https://view.officeapps.live.com/op/view.aspx?src=${encodeURIComponent(fileUrl)}`;
            wordNotice.classList.remove('d-none');
            wordNotice.innerHTML = `
                <p>This file cannot be previewed directly. Click below to open it in Office viewer:</p>
                <a id="resignationWordLink" href="${wordLink.href}" target="_blank" class="btn btn-primary">Open Document</a>
            `;
        } else {
            wordNotice.classList.remove('d-none');
            wordNotice.innerHTML = `<p>Unsupported file format. <a href="${fileUrl}" target="_blank">Download file</a></p>`;
        }
 
        if (reason && reason.trim() !== '') {
            reasonText.textContent = reason;
            reasonContainer.classList.remove('d-none');
        } else {
            reasonContainer.classList.add('d-none');
        }
 
        const modal = new bootstrap.Modal(document.getElementById('viewResignationModal'));
        modal.show();
    } 
    
    function openApprovalModal(resignationId, action) {
        const modalTitle = document.getElementById('approvalModalTitle');
        const submitBtn = document.getElementById('submitApprovalBtn');
        const actionInput = document.getElementById('approvalAction');
        const remarksField = document.getElementById('status_remarks');
 
        document.getElementById('resignationId').value = resignationId;
        actionInput.value = action;
        remarksField.value = '';  
 
        if (action === 'approve') {
            modalTitle.textContent = 'Approve Resignation';
            submitBtn.textContent = 'Approve';
            submitBtn.className = 'btn btn-success';
        } else if (action === 'reject') {
            modalTitle.textContent = 'Reject Resignation';
            submitBtn.textContent = 'Reject';
            submitBtn.className = 'btn btn-danger';
        }

        const modal = new bootstrap.Modal(document.getElementById('approveResignationModal'));
        modal.show();
        } 

      document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('approveResignationForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const resignationId = document.getElementById('resignationId').value;
            const action = document.getElementById('approvalAction').value; 
            const remarks = document.getElementById('status_remarks').value.trim();

            if (!remarks) {
                toastr.warning('Please enter remarks before submitting.', 'Warning');
                return;
            }

            fetch(`/api/resignation/${action}/${resignationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status_remarks: remarks })
            })
            .then(async (response) => {
                const data = await response.json().catch(() => null);
                if (!response.ok) {
                    throw new Error(data?.message || `HTTP ${response.status}`);
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    $('#approveResignationModal').modal('hide');
                    filter();
                    toastr.success(`Resignation successfully ${action}d.`, 'Success');
                      
                } else {
                    toastr.error(data.message || 'Something went wrong.', 'Error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An unexpected error occurred. Please try again.', 'Error');
            });
        });
    });
          
   function viewResignationRemarks(resignationId) {

        fetch(`/api/resignation/remarks/${resignationId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const remarksDiv = document.getElementById('remarksContent');
                    remarksDiv.innerHTML = '';  

                    const deptHeadRemarks = data.status_remarks ? `
                        <div class="mb-3">
                            <h6 class="fw-bold text-primary mb-1">Department Head / Reporting To Remarks:</h6>
                            <p class="border rounded p-2 bg-light">${data.status_remarks}</p>
                        </div>` : '';

                    const hrRemarks = data.accepted_remarks ? `
                        <div class="mb-3">
                            <h6 class="fw-bold text-success mb-1">HR Remarks:</h6>
                            <p class="border rounded p-2 bg-light">${data.accepted_remarks}</p>
                        </div>` : '';

                     const hrInstruction = data.instruction ? `
                        <div class="mb-3">
                            <h6 class="fw-bold text-success mb-1">HR Instruction:</h6>
                            <p class="border rounded p-2 bg-light">${data.instruction}</p>
                        </div>` : '';

                    if (deptHeadRemarks || hrRemarks || hrInstruction) {
                        remarksDiv.innerHTML = deptHeadRemarks + hrRemarks + hrInstruction;
                    } else {
                        remarksDiv.innerHTML = '<p class="text-muted mb-0">No remarks available.</p>';
                    }

                    const remarksModal = new bootstrap.Modal(document.getElementById('viewRemarksModal'));
                    remarksModal.show();
                } else {
                    toastr.warning(data.message || 'No remarks found.', 'Notice');
                }
            })
            .catch(error => {
                console.error('Error fetching remarks:', error);
                toastr.error('Failed to load remarks. Please try again.', 'Error');
            });
    } 
  
</script> 
  <script>
        function populateDropdown($select, items, placeholder = 'Select') {
            $select.empty();
            $select.append(`<option value="">All ${placeholder}</option>`);
            items.forEach(item => {
                $select.append(`<option value="${item.id}">${item.name}</option>`);
            });
        }

        $(document).ready(function() {

            $('#branch_filter').on('input', function() {
                const branchId = $(this).val();

                $.get('/api/filter-from-branch', {
                    branch_id: branchId
                }, function(res) {
                    if (res.status === 'success') {
                        populateDropdown($('#department_filter'), res.departments, 'Departments');
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });


            $('#department_filter').on('input', function() {
                const departmentId = $(this).val();
                const branchId = $('#branch_filter').val();

                $.get('/api/filter-from-department', {
                    department_id: departmentId,
                    branch_id: branchId,
                }, function(res) {
                    if (res.status === 'success') {
                        if (res.branch_id) {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                        }
                        populateDropdown($('#designation_filter'), res.designations,
                            'Designations');
                    }
                });
            });

            $('#designation_filter').on('change', function() {
                const designationId = $(this).val();
                const branchId = $('#branch_filter').val();
                const departmentId = $('#department_filter').val();

                $.get('/api/filter-from-designation', {
                    designation_id: designationId,
                    branch_id: branchId,
                    department_id: departmentId
                }, function(res) {
                    if (res.status === 'success') {
                        if (designationId === '') {
                            populateDropdown($('#designation_filter'), res.designations,
                                'Designations');
                        } else {
                            $('#branch_filter').val(res.branch_id).trigger('change');
                            $('#department_filter').val(res.department_id).trigger('change');
                        }
                    }
                });
            });

        });
 </script>
@endpush
    @include('layout.partials.footer-company') 

</div>  

@component('components.modal-popup')
@endcomponent

@endsection
