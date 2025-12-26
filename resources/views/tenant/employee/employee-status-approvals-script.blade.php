<script>
// ============ APPROVAL SYSTEM ============

// Load Pending Approvals
function loadPendingApprovals() {
    $.ajax({
        url: '{{ route('employee-status-approvals') }}',
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + authToken,
            'X-CSRF-TOKEN': csrfToken
        },
        success: function (response) {
            if (response.status === 'success') {
                const approvals = response.approvals;
                $('#pendingCount').text(approvals.length);
                
                let html = '';
                if (approvals.length === 0) {
                    html = '<tr><td colspan="7" class="text-center">No pending approvals</td></tr>';
                } else {
                    approvals.forEach(approval => {
                        const employee = approval.employee;
                        const requester = approval.requester;
                        const statusBadges = {
                            'Active': 'bg-success',
                            'AWOL': 'bg-dark',
                            'Resigned': 'bg-info',
                            'Terminated': 'bg-danger',
                            'Suspended': 'bg-secondary',
                            'Floating': 'bg-primary'
                        };
                        
                        html += `
                            <tr>
                                <td>${employee.personal_information?.full_name || 'N/A'}</td>
                                <td><span class="badge ${statusBadges[approval.current_status] || 'bg-secondary'}">${approval.current_status}</span></td>
                                <td><span class="badge ${statusBadges[approval.requested_status] || 'bg-secondary'}">${approval.requested_status}</span></td>
                                <td>${requester?.personal_information?.full_name || 'N/A'}</td>
                                <td>${new Date(approval.created_at).toLocaleString()}</td>
                                <td>${approval.remarks || '-'}</td>
                                <td>
                                    <button class="btn btn-success btn-sm approve-btn" data-id="${approval.id}">
                                        <i class="ti ti-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm reject-btn" data-id="${approval.id}">
                                        <i class="ti ti-x"></i> Reject
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                
                $('#approvalsTableBody').html(html);
                
                if ($.fn.DataTable.isDataTable('#approvalsTable')) {
                    $('#approvalsTable').DataTable().destroy();
                }
                $('#approvalsTable').DataTable({
                    order: [[4, 'desc']],
                    pageLength: 25,
                    columns: [
                        { data: 'employee' },
                        { data: 'current_status' },
                        { data: 'requested_status' },
                        { data: 'requested_by' },
                        { data: 'request_date' },
                        { data: 'remarks' },
                        { data: 'actions', orderable: false, searchable: false }
                    ]
                });
            }
        },
        error: function (xhr) {
            toastr.error('Failed to load pending approvals');
        }
    });
}

// Load approvals when tab is clicked
$('#pending-approvals-tab').on('click', function () {
    loadPendingApprovals();
});

// Load on page load
loadPendingApprovals();

// Approve Status Change
$(document).on('click', '.approve-btn', function () {
    const approvalId = $(this).data('id');
    $('#approveId').val(approvalId);
    $('#approveModal').modal('show');
});

$('#confirmApprove').on('click', function () {
    const approvalId = $('#approveId').val();
    
    $.ajax({
        url: '{{ route('employee-status-approve') }}',
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + authToken,
            'X-CSRF-TOKEN': csrfToken
        },
        data: {
            approval_id: approvalId
        },
        success: function (response) {
            if (response.status === 'success') {
                toastr.success(response.message);
                $('#approveModal').modal('hide');
                loadPendingApprovals();
                
                if ($('#employee-list-tab').hasClass('active')) {
                    $('#applyFilter').click();
                }
            }
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Failed to approve status change');
        }
    });
});

// Reject Status Change
$(document).on('click', '.reject-btn', function () {
    const approvalId = $(this).data('id');
    $('#rejectId').val(approvalId);
    $('#rejectionReason').val('');
    $('#rejectModal').modal('show');
});

$('#rejectForm').on('submit', function (e) {
    e.preventDefault();
    
    const approvalId = $('#rejectId').val();
    const rejectionReason = $('#rejectionReason').val();
    
    $.ajax({
        url: '{{ route('employee-status-reject') }}',
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + authToken,
            'X-CSRF-TOKEN': csrfToken
        },
        data: {
            approval_id: approvalId,
            rejection_reason: rejectionReason
        },
        success: function (response) {
            if (response.status === 'success') {
                toastr.success(response.message);
                $('#rejectModal').modal('hide');
                loadPendingApprovals();
            }
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || 'Failed to reject status change');
        }
    });
});
</script>
