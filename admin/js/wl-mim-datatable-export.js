(function ($) {
    "use strict";
    jQuery(document).ready(function () {
        var urlParams = new URLSearchParams(window.location.search);
        var year = urlParams.get('year');
        var month = urlParams.get('month');
        var status = urlParams.get('status');
        var batch_id = urlParams.get('batch_id');
        var course_id = urlParams.get('course_id');
        var filters = '&filter_by_year=' + year + '&filter_by_month=' + month + '&status=' + status + '&course_id=' + course_id + '&batch_id=' + batch_id;

        /* Get data to display on table with export options */
        function initializeDatatable(table, action) {
            var table = jQuery(table).DataTable({
                aaSorting: [],
                responsive: true,
                ajax: {
                    url: ajaxurl + '?security=' + WLIMAjax.security + '&action=' + action + filters,
                    dataSrc: 'data'
                },
                language: {
                    "loadingRecords": "Loading..."
                },
                lengthChange: false,
                dom: 'lBfrtip',
                columnDefs: [
                    { orderable: false, targets: 0 }
                  ],
                select: true,
                buttons: [
                    'pageLength',
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
                            modifier: {
                                selected: null
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
                            modifier: {
                                selected: null
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
                            modifier: {
                                selected: null
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print all',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
                            modifier: {
                                selected: null
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print selected',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
                        }
                    },
                    'colvis'
                ]
            });
        }

        /* Actions for student */
        initializeDatatable('#student-table', 'wl-mim-get-student-data');
    });
})(jQuery);