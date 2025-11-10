/**
 * Employee Import Management with License Validation
 */

$(document).ready(function() {
    // Handle CSV upload form submission
    $('#csvUploadForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.text();

        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('Validating...');

        // Clear previous messages
        clearImportMessages();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status === 'success') {
                    showImportSuccess(response);
                    startImportStatusChecking();

                    // ✅ Close the upload modal
                    $('#upload_employee').modal('hide');

                    // ✅ Reset the form
                    $('#csvUploadForm')[0].reset();
                } else {
                    showImportError(response);
                }
            },
            error: function(xhr) {
                let response = xhr.responseJSON;
                if (response && response.errors && response.errors.license_limit) {
                    showLicenseLimitError(response.errors.license_limit);
                } else if (response && response.message) {
                    showImportError(response);
                } else {
                    showImportError({
                        message: 'Import failed. Please try again.',
                        errors: ['Unknown error occurred']
                    });
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalBtnText);
            }
        });
    });

    // Removed automatic status check on page load to prevent looping
    // checkImportStatus();
});

/**
 * Show successful import initiation
 */
function showImportSuccess(response) {
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show import-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-check-circle me-2"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${response.message}</h6>
                    <small>
                        • Importing ${response.details.rows_to_import} employees<br>
                        • Current users: ${response.details.current_users}/${response.details.plan_limit}<br>
                        • After import: ${response.details.total_after_import}/${response.details.plan_limit}
                    </small>
                    <div class="mt-2">
                        <strong>Next steps:</strong>
                        <ul class="mb-0 small">
                            ${response.next_steps.map(step => `<li>${step}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    $('.content').prepend(alertHtml);

    // Auto-scroll to alert
    $('html, body').animate({
        scrollTop: $('.import-alert').offset().top - 100
    }, 500);
}

/**
 * Show license limit error with detailed information
 */
function showLicenseLimitError(licenseError) {
    const details = licenseError.details;
    const suggestions = licenseError.suggestions;

    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show import-alert" role="alert">
            <div class="d-flex align-items-start">
                <i class="fa fa-exclamation-triangle me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-2">${licenseError.message}</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Current Situation:</strong>
                            <ul class="mb-0 small">
                                <li>Current active users: ${details.current_users}</li>
                                <li>Plan limit (${details.plan_name}): ${details.plan_limit}</li>
                                <li>Trying to import: ${details.trying_to_import} employees</li>
                                <li>Would exceed by: ${details.would_exceed_by} users</li>
                                <li>Additional cost: ${details.overage_cost_per_month}/month</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <strong>Solutions:</strong>
                            <ul class="mb-0 small">
                                ${suggestions.map(suggestion => `<li>${suggestion}</li>`).join('')}
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openPlanUpgrade()">
                            <i class="fa fa-arrow-up"></i> Upgrade Plan
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openCsvEditor()">
                            <i class="fa fa-edit"></i> Edit CSV File
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    $('.content').prepend(alertHtml);

    // Auto-scroll to alert
    $('html, body').animate({
        scrollTop: $('.import-alert').offset().top - 100
    }, 500);
}

/**
 * Show general import error
 */
function showImportError(response) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show import-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-exclamation-circle me-2"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${response.message || 'Import failed'}</h6>
                    ${response.errors && Array.isArray(response.errors) ?
                        `<ul class="mb-0 small">${response.errors.map(error => `<li>${error}</li>`).join('')}</ul>` :
                        ''
                    }
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    $('.content').prepend(alertHtml);
}

/**
 * Clear previous import messages
 */
function clearImportMessages() {
    $('.import-alert').remove();
}

/**
 * Start checking import status periodically
 */
function startImportStatusChecking() {
    let checkCount = 0;
    const maxChecks = 20; // Check for 10 minutes (30 seconds × 20)

    const statusInterval = setInterval(function() {
        checkCount++;

        if (checkCount >= maxChecks) {
            clearInterval(statusInterval);
            showTimeoutMessage();
            return;
        }

        checkImportStatus(function(found, results) {
            if (found) {
                clearInterval(statusInterval);
                showImportResults(results);
            }
        });

    }, 1000); // Check every 30 seconds
}

// Add a flag to track if we've already shown the status
let importStatusShown = false;

/**
 * Check import status
 */
function checkImportStatus(callback) {
    $.ajax({
        url: '/employee/import-status',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.status === 'found' && response.results) {
                if (callback) {
                    callback(true, response.results);
                } else {
                    // Only show if we haven't shown it yet and it's recent
                    if (!importStatusShown) {
                        const resultTime = new Date(response.results.timestamp);
                        const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000);

                        if (resultTime > fiveMinutesAgo) {
                            importStatusShown = true;
                            showImportResults(response.results);
                        }
                    }
                }
            } else {
                if (callback) callback(false, null);
            }
        },
        error: function() {
            if (callback) callback(false, null);
        }
    });
}

/**
 * Show import results
 */
function showImportResults(results) {
    clearImportMessages();

    const summary = results.summary;
    const status = results.status;

    let alertClass = 'alert-info';
    let icon = 'fa-info-circle';
    let title = 'Import Status';

    if (status === 'completed') {
        if (summary.errors_count === 0) {
            alertClass = 'alert-success';
            icon = 'fa-check-circle';
            title = 'Import Completed Successfully!';
        } else {
            alertClass = 'alert-warning';
            icon = 'fa-exclamation-triangle';
            title = 'Import Completed with Issues';
        }
    } else if (status === 'failed' || status === 'blocked') {
        alertClass = 'alert-danger';
        icon = 'fa-exclamation-circle';
        title = 'Import Failed';
    }

    let resultHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show import-alert" role="alert">
            <div class="d-flex align-items-start">
                <i class="fa ${icon} me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-2">${title}</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Summary:</strong>
                            <ul class="mb-0 small">
                                <li>Total processed: ${summary.total_processed}</li>
                                <li>Successfully imported: ${summary.successful_imports}</li>
                                <li>Skipped (duplicates): ${summary.skipped_records}</li>
                                <li>Errors: ${summary.errors_count}</li>
                            </ul>
                        </div>
    `;

    if (results.errors && results.errors.length > 0) {
        resultHtml += `
                        <div class="col-md-6">
                            <strong>Issues found:</strong>
                            <div class="small" style="max-height: 150px; overflow-y: auto;">
                                ${results.errors.slice(0, 10).map(error => {
                                    if (error.type === 'license_limit_exceeded') {
                                        return `<div class="text-danger"><i class="fa fa-ban"></i> ${error.error}</div>`;
                                    } else if (error.row) {
                                        return `<div>Row ${error.row}: ${error.error}</div>`;
                                    } else {
                                        return `<div>${error.error || error}</div>`;
                                    }
                                }).join('')}
                                ${results.errors.length > 10 ? `<div class="text-muted">... and ${results.errors.length - 10} more issues</div>` : ''}
                            </div>
                        </div>
        `;
    }

    resultHtml += `
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            ${status === 'completed' && summary.successful_imports > 0 ?
                                '<small class="text-info"><i class="fa fa-info-circle"></i> Refresh the employee list to see the newly imported employees.</small>' :
                                ''
                            }
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearImportStatus()">
                            <i class="fa fa-times"></i> Clear Status
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    $('.content').prepend(resultHtml);

    // Auto-scroll to alert
    $('html, body').animate({
        scrollTop: $('.import-alert').offset().top - 100
    }, 500);

    // If successful, refresh employee table after a short delay
    if (status === 'completed' && summary.successful_imports > 0) {
        setTimeout(function() {
            location.reload();
        }, 2000);
    }
}

/**
 * Show timeout message
 */
function showTimeoutMessage() {
    const alertHtml = `
        <div class="alert alert-warning alert-dismissible fade show import-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-clock me-2"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1">Import Status Check Timeout</h6>
                    <p class="mb-2 small">The import is still processing. This may take longer for large files.</p>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkImportStatus()">
                        <i class="fa fa-refresh"></i> Check Status Again
                    </button>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    $('.content').prepend(alertHtml);
}

/**
 * Open plan upgrade modal/page
 */
function openPlanUpgrade() {
    // You can redirect to subscriptions page or open a modal
    window.location.href = '/subscriptions';
}

/**
 * Provide guidance on editing CSV file
 */
function openCsvEditor() {
    const alertHtml = `
        <div class="alert alert-info alert-dismissible fade show import-alert" role="alert">
            <div class="d-flex align-items-start">
                <i class="fa fa-lightbulb me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-2">How to Reduce Your CSV File Size</h6>
                    <ol class="mb-0 small">
                        <li>Open your CSV file in Excel or Google Sheets</li>
                        <li>Remove some employee rows to fit within your plan limit</li>
                        <li>Save the file and try importing again</li>
                        <li>You can import the remaining employees in a separate batch later</li>
                    </ol>
                    <div class="mt-2">
                        <strong>Tip:</strong> Consider upgrading your plan if you frequently need to add many employees.
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    $('.content').prepend(alertHtml);
}

/**
 * Clear import status and hide alerts
 */
function clearImportStatus() {
    // Clear the import alert
    clearImportMessages();

    // Optional: Clear the status on the server side by making a request
    // This prevents the status from showing again on refresh
    $.ajax({
        url: '/employee/clear-import-status',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            // Status cleared successfully
            console.log('Import status cleared');
        },
        error: function() {
            // Silent fail - just clear the UI
            console.log('Failed to clear status on server, but UI cleared');
        }
    });
}

/**
 * Utility function to format timestamp for display
 */
function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString();
}
