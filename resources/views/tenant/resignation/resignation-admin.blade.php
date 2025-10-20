<?php $page = 'resignation'; ?>
@extends('layout.mainlayout')
@section('content')

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
                               
                            </div>
                        </div>
                        <div class="card-body p-0">

                            <div class="custom-datatable-filter table-responsive">
                                <table class="table datatable">
                                    <thead class="thead-light">
                                        <tr class="text-center"> 
                                            <th>Date Filed</th>
                                            <th>Resigning Employee</th> 
                                            <th>Branch</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            <th>Resignation Letter</th>  
                                            <th>Date Accepted</th>
                                            <th>Remaining Days</th>
                                            <th>Resignation Date</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
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
                                                    @if($resignation->status === 0) 
                                                    <span>For Approval</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_by === null )
                                                    <span>For Acceptance</span>
                                                    @elseif($resignation->status === 1 && $resignation->accepted_by !== null )
                                                    <span>Accepted</span>
                                                    @elseif($resignation->status === 2)
                                                    <span>Rejected</span>
                                                    @endif
                                                </td>
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
                                                   <button class="btn btn-success btn-sm" onclick="openApprovalModal({{ $resignation->id }}, 'approve')">
                                                        Approve
                                                    </button> 
                                                    <button class="btn btn-danger btn-sm" onclick="openApprovalModal({{ $resignation->id }}, 'reject')">
                                                        Reject
                                                    </button> 
                                                    @elseif($isActiveHR && $resignation->status === 1 && $resignation->accepted_by === null)
                                                    <button class="btn btn-success btn-sm" onclick="openAcceptanceModal({{ $resignation->id }}, 'accept')">
                                                        Accept
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
<!-- HR Acceptance Modal -->
<div class="modal fade" id="acceptResignationModal" tabindex="-1" aria-labelledby="acceptResignationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title">Accept Resignation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> 
           <form id="acceptResignationForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="acceptResignationId" name="resignation_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Resignation Date</label>
                        <input type="date" class="form-control" name="resignation_date" id="resignation_date" required>
                    </div>

                    <div class="mb-3">
                        <label for="accept_remarks" class="form-label fw-bold">Remarks</label>
                        <textarea id="accept_remarks" name="accepted_remarks" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="accept_instruction" class="form-label fw-bold">Instruction</label>
                        <textarea id="accept_instruction" name="accepted_instruction" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Attachment</label>
                        <input type="file" name="resignation_attachment[]" id="resignation_attachment" class="form-control" multiple> 
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Accept Resignation</button>
                </div>
            </form> 
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

   <script>
    function viewResignationFile(fileUrl, reason) {
        const fileExtension = fileUrl.split('.').pop().toLowerCase();
        const iframe = document.getElementById('resignationPreviewFrame');
        const wordNotice = document.getElementById('resignationWordNotice');
        const wordLink = document.getElementById('resignationWordLink');
        const reasonContainer = document.getElementById('resignationReasonContainer');
        const reasonText = document.getElementById('resignationReasonText');

        // Hide iframe and notice by default
        iframe.style.display = 'none';
        wordNotice.classList.add('d-none');

        // Handle file preview logic
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

        // Show reason only if provided
        if (reason && reason.trim() !== '') {
            reasonText.textContent = reason;
            reasonContainer.classList.remove('d-none');
        } else {
            reasonContainer.classList.add('d-none');
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('viewResignationModal'));
        modal.show();
    }
    </script>
<script>
function openApprovalModal(resignationId, action) {
    const modalTitle = document.getElementById('approvalModalTitle');
    const submitBtn = document.getElementById('submitApprovalBtn');
    const actionInput = document.getElementById('approvalAction');
    const remarksField = document.getElementById('status_remarks');

    // Set modal data
    document.getElementById('resignationId').value = resignationId;
    actionInput.value = action;
    remarksField.value = ''; // clear previous input

    // Adjust modal appearance based on action
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
</script>
<script>
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
                toastr.success(`Resignation successfully ${action}d.`, 'Success');
                setTimeout(() => {
                    location.reload();
                }, 1500);  
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

</script>

<script>
function openAcceptanceModal(id) {
    document.getElementById('acceptResignationId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('acceptResignationModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('acceptResignationForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const resignationId = document.getElementById('acceptResignationId').value;
        const formData = new FormData(form);

        try {
            const response = await fetch(`/api/resignation/accept/${resignationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            let data;
            try {
                data = await response.json();
            } catch (err) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                alert('Server returned an unexpected response. Please check console.');
                return;
            }

            if (data.success) {
                alert('Resignation successfully accepted by HR.');
                location.reload();
            } else {
                alert(data.message || 'Something went wrong.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An unexpected error occurred. Please try again.');
        }
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
      @include('layout.partials.footer-company') 

    </div>  

    @component('components.modal-popup')
    @endcomponent

@endsection
