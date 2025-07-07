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
        }
    };

    const config = $.extend(true, {}, defaultOptions, options);

    if ($(selector).length > 0) {
        return $(selector).DataTable(config);
    }

    return null;
}