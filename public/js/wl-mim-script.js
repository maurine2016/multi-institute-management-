(function ($) {
    "use strict";
    jQuery(document).ready(function () {
        /* Date time picker */
        var defaultDate = new Date();
        var maxDate = new Date();
        var minDate = new Date();
        defaultDate.setFullYear(defaultDate.getFullYear() - 15);
        maxDate.setFullYear(maxDate.getFullYear() - 2);
        minDate.setFullYear(minDate.getFullYear() - 100);
        jQuery('.wlim-date_of_birth').datetimepicker({
            format: 'DD-MM-YYYY',
            showClear: true,
            showClose: true
        });

        /* Print student result */
        jQuery(document).on('click', '#wlmim-admit-card-print-button', function () {
            jQuery.print("#wlmim-admit-card-print");
        });

        /* Print student id card */
        jQuery(document).on('click', '#wlmim-id-card-print-button', function () {
            jQuery.print("#wl-id-card");
        });

        /* Print student certificate */
        jQuery(document).on('click', '#wlmim-certificate-print-button', function () {
            jQuery.print("#wl-certificate");
        });
    });
})(jQuery);
