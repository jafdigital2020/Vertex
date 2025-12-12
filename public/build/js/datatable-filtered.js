function initFilteredDataTable(selector, options = {}) {
    const defaultOptions = {
        bFilter: true,
        ordering: true,
        info: true, 
        pageLength: 10,
        language: {
            search: ' ',
            sLengthMenu: 'Row Per Page _MENU_ Entries',
            searchPlaceholder: "Search",
            info: "Showing _START_ - _END_ of _TOTAL_ entries",
            paginate: {
                next: '<i class="ti ti-chevron-right"></i>',
                previous: '<i class="ti ti-chevron-left"></i>'
            }
        },
        initComplete: function() {
            // Add Bootstrap 5 classes to DataTable elements after initialization
            const tableWrapper = $(selector).closest('.dataTables_wrapper');
            tableWrapper.find('.dataTables_length select').addClass('form-select');
            tableWrapper.find('.dataTables_filter input').addClass('form-control');
        }
    };

    const config = $.extend(true, {}, defaultOptions, options);

    if ($(selector).length > 0) {
        const table = $(selector).DataTable(config);
        
        // Also apply Bootstrap classes after table is created
        setTimeout(function() {
            const tableWrapper = $(selector).closest('.dataTables_wrapper');
            tableWrapper.find('.dataTables_length select').addClass('form-select');
            tableWrapper.find('.dataTables_filter input').addClass('form-control');
        }, 100);
        
        return table;
    }

    return null;
}