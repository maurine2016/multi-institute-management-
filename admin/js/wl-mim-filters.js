(function ($) {
    "use strict";
    jQuery(document).ready(function () {
        /* Year picker */
        function yearPicker() {
            jQuery('.wlim-year').datetimepicker({
                format: 'YYYY',
                useCurrent: true,
                showClear: true,
                showClose: true
            });
        }

        /* Month picker */
        function monthPicker() {
            jQuery('.wlim-month').datetimepicker({
                format: 'MM',
                useCurrent: true,
                showClear: true,
                showClose: true
            });
        }

        yearPicker();
        monthPicker();
    });
})(jQuery);