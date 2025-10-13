<?php $page = 'resignation'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Resignation Employee</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Resignation
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Resignation Employee</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    @if (in_array('Create', $permission))
                    <div class="mb-2">
                        <a href="#" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#upload_resignation"><i class="ti ti-circle-plus me-2"></i>Upload Resignation Letter</a>
                    </div>
                    @endif
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
                                            <th>Resignation Letter</th> 
                                            <th>Date Accepted</th>
                                            <th>Remaining Days</th>
                                            <th>Resignation Date</th>  
                                            <th>Remarks</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                                        @foreach ($resignations as $resignation)
                                            <tr class="text-center">
                                                <td>{{$resignation->date_filed}}</td>
                                                <td>{{$resignation->personalInformation->first_name ?? '' }} {{$resignation->personalInformation->last_name ?? '' }}</td> 
                                               <td>
                                                    <button 
                                                        class="btn btn-sm btn-primary"
                                                        onclick="viewResignationFile('{{ asset('storage/' . $resignation->resignation_file) }}','{{$resignation->reason ?? ''}}')">
                                                        View <i class="fa fa-file"></i>
                                                    </button>
                                                </td>  
                                                <td>{{$resignation->accepted_date ?? '-'}}</td>
                                                <td>
                                                    @if($resignation->resignation_date === null)
                                                     - 
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
                                                    @elseif($resignation->status === 1 )
                                                    <span>For Acceptance</span>
                                                    @elseif($resignation->status === 2)
                                                    <span>Rejected</span>
                                                    @endif
                                                </td> 
                                                <td>
                                                    @if($resignation->status === 0)
                                                    <div class="action-icon d-inline-flex text-center">
                                                    @if (in_array('Update', $permission))
                                                      <a href="javascript:void(0);" 
                                                        class="btn-edit" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#edit_resignation_modal"
                                                        data-id="{{ $resignation->id }}"
                                                        data-reason="{{ $resignation->reason }}"
                                                        data-file="{{ asset('storage/' . $resignation->resignation_file) }}"
                                                        title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                       </a> 
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                            data-bs-target="#delete_resignation_modal" data-id="{{ $resignation->id }}" 
                                                            title="Delete"><i class="ti ti-trash"></i></a>
                                                  
                                                    @endif
                                                    </div>
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
 
      @include('layout.partials.footer-company') 

    </div> 
    <div class="modal fade" id="upload_resignation" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content"> 
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Resignation Letter</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                  <form id="resignationForm" method="POST" action="{{ route('submit-resignation-letter') }}" enctype="multipart/form-data">
                        @csrf
                    <div class="modal-body"> 
                        <div class="mb-3">
                            <label class="form-label">Resignation Letter</label>
                           <input type="file" class="form-control" name="resignation_letter" id="resignation_letter" 
                           accept=".pdf,.doc,.docx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason (optional)</label>
                            <textarea class="form-control" rows="5" id="resignation_reason" name="resignation_reason"></textarea>
                        </div>
                    </div> 
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form> 
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
 
                    <div id="resignationReasonContainer" class="mb-4 text-start d-none">
                        <h6 class="fw-bold">Reason for Resignation:</h6>
                        <p id="resignationReasonText" class="border rounded p-2 bg-light"></p>
                    </div> 
                    <iframe id="resignationPreviewFrame" src="" style="width:100%;height:80vh;border:none;display:none;"></iframe>

                    <div id="resignationWordNotice" class="d-none">
                        <p>This file cannot be previewed directly. Click below to open it in Office viewer:</p>
                        <a id="resignationWordLink" href="#" target="_blank" class="btn btn-primary">Open Document</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
 <div class="modal fade" id="edit_resignation_modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content"> 
            <div class="modal-header">
                <h5 class="modal-title">Edit Resignation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="editResignationForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST') 
                
                <div class="modal-body"> 
                    <input type="hidden" id="edit_resignation_id" name="id">

                    <div class="mb-3">
                        <label class="form-label">Resignation Letter</label>
                        <input type="file" class="form-control" name="resignation_letter" id="edit_resignation_letter" 
                               accept=".pdf,.doc,.docx">
                        <small class="text-muted d-block mt-1" id="current_file_info"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason (optional)</label>
                        <textarea class="form-control" rows="5" id="edit_resignation_reason" name="resignation_reason"></textarea>
                    </div>
                </div> 
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form> 
        </div>
    </div>
</div>

  <div class="modal fade" id="delete_resignation_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                    <i class="ti ti-trash-x fs-36"></i>
                </span>
                <h4 class="mb-1">Confirm Delete</h4>
                <p class="mb-3">Are you sure you want to delete this resignation record?</p>
                <div class="d-flex justify-content-center">
                    <form id="deleteResignationForm">
                        @csrf
                        <input type="hidden" name="id" id="delete_resignation_id">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </form>
                </div>
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

    document.addEventListener('DOMContentLoaded', function () {
 
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-edit');
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const reason = btn.getAttribute('data-reason') || '';
            const file = btn.getAttribute('data-file') || '';
 
            document.getElementById('edit_resignation_id').value = id;
            document.getElementById('edit_resignation_reason').value = reason;
    
            const fileInfo = document.getElementById('current_file_info');
            if (file) {
                fileInfo.innerHTML = `Current file: <a href="${file}" target="_blank">View</a>`;
            } else {
                fileInfo.textContent = 'No file uploaded yet.';
            }
 
            const form = document.getElementById('editResignationForm');
            form.setAttribute('action', `/api/resignations/${id}`);
        });
 
        const form = document.getElementById('editResignationForm');
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const id = document.getElementById('edit_resignation_id').value;
            const formData = new FormData(form);

            fetch(`/api/resignations/${id}`, {
                method: 'POST',  
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const modalEl = document.getElementById('edit_resignation_modal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();

                toastr.success(data.message || 'Resignation updated successfully!', 'Success');
                setTimeout(() => location.reload(), 1500);
            })
            .catch(error => {
                console.error(error);
                toastr.error('Failed to update resignation. Please try again.', 'Error');
            });
        });

    }); 

      document.addEventListener('DOMContentLoaded', function () {
    
        document.addEventListener('click', function (e) {
            if (e.target.closest('.btn-delete')) {
                const button = e.target.closest('.btn-delete');
                const id = button.getAttribute('data-id');
                console.log('Delete clicked, ID:', id);
    
                document.getElementById('delete_resignation_id').value = id;
            }
        });
    
        const deleteForm = document.getElementById('deleteResignationForm');
        deleteForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const id = document.getElementById('delete_resignation_id').value;

            fetch(`/api/resignations/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => { 
                const modalEl = document.getElementById('delete_resignation_modal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
    
                toastr.success(data.message || 'Resignation deleted successfully!', 'Success', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 2000
                });
    
                setTimeout(() => location.reload(), 1500);
            })
            .catch(error => {
                console.error(error);
                toastr.error('Failed to delete resignation. Please try again.', 'Error', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 3000
                });
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

                    if (deptHeadRemarks || hrRemarks) {
                        remarksDiv.innerHTML = deptHeadRemarks + hrRemarks;
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
    @component('components.modal-popup')
    @endcomponent

@endsection
