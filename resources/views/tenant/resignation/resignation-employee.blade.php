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
                                            <th>Resigning Employee</th> 
                                            <th>Resignation Letter</th> 
                                            <th>Resignation Date</th>  
                                            <th>Effective Date</th>
                                            <th>Remaining Days</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                                        @foreach ($resignations as $resignation)
                                            <tr class="text-center">
                                                <td>{{$resignation->personalInformation->first_name ?? '' }} {{$resignation->personalInformation->last_name ?? '' }}</td> 
                                               <td>
                                                    <button 
                                                        class="btn btn-sm btn-primary"
                                                        onclick="viewResignationFile('{{ asset('storage/' . $resignation->resignation_file) }}','{{$resignation->reason ?? ''}}')">
                                                        View <i class="fa fa-file"></i>
                                                    </button>
                                                </td>  
                                                <td>{{$resignation->resignation_date}}</td>  
                                                <td></td>
                                                <td></td>
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
                                                        <a href="#" class="me-2" data-bs-toggle="modal"
                                                            data-bs-target="#edit_resignation_employee" title="Edit"><i
                                                                class="ti ti-edit"></i></a>
                                                    @endif
                                                    @if (in_array('Delete', $permission))
                                                        <a href="javascript:void(0);" class="btn-delete" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal" data-id="{{ $resignation->id }}" 
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
                            <input type="file" class="form-control" name="resignation_letter" id="resignation_letter">
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




    @component('components.modal-popup')
    @endcomponent

@endsection
