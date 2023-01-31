(function ($) {
    "use strict";
    jQuery(document).ready(function () {
        /* Loading */
        jQuery(document).ajaxStart(function () {
            jQuery('button[type="submit"]').prop('disabled', true);
        }).ajaxStop(function () {
            jQuery('button[type="submit"]').prop('disabled', false);
        });

        /* Add or update record */
        function save(selector, form = null, alert = false) {
            jQuery(form).ajaxForm({
                success: function (response) {
                    jQuery(selector).prop('disabled', false);
                    if (response.success) {
                        jQuery('span.text-danger').remove();
                        jQuery(".is-invalid").removeClass("is-invalid");
                        if (alert) {
                            jQuery('.wl_im_container .alert').remove();
                            var alertBox = '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i> &nbsp;' + response.data.message + '</strong></div>';
                            jQuery(alertBox).insertBefore(form);
                        }
                        toastr.success(response.data.message);
                        if (response.data.hasOwnProperty('reload') && response.data.reload) {
                            location.reload();
                        } else {
                            jQuery(form)[0].reset();
                        }
                    } else {
                        jQuery('span.text-danger').remove();
                        if (response.data && jQuery.isPlainObject(response.data)) {
                            jQuery(form + ' :input').each(function () {
                                var input = this;
                                jQuery(input).removeClass('is-invalid');
                                if (response.data[input.name]) {
                                    var errorSpan = '<span class="text-danger">' + response.data[input.name] + '</span>';
                                    jQuery(input).addClass('is-invalid');
                                    jQuery(errorSpan).insertAfter(input);
                                }
                            });
                        } else {
                            var errorSpan = '<span class="text-danger">' + response.data + '<hr></span>';
                            jQuery(errorSpan).insertBefore(form);
                            toastr.error(response.data);
                        }
                    }
                },
                error: function (response) {
                    jQuery(selector).prop('disabled', false);
                    toastr.error(response.statusText);
                },
                uploadProgress(event, progress, total, percentComplete) {
                    jQuery('#wlim-progress').text(percentComplete);
                }
            });
        }

        /* Fetch records */
        function fetchRecords(action, target, data = null) {
            jQuery.ajax({
                type: 'post',
                url: wlimajaxurl + '?security=' + WLIMAjax.security + '&action=' + action,
                data: data,
                success: function (data) {
                    jQuery(target).html(data);
                }
            });
        }

        /* Action to fetch institute categories */
        jQuery(document).on('change', '#wlim-enquiry-institute', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                fetchRecords('wl-mim-fetch-institute-categories', '#wlim-fetch-institute-categories', data);
                fetchRecords('wl-mim-fetch-institute-custom-fields', '#wlim-fetch-institute-custom-fields', data);
            } else {
                jQuery('#wlim-fetch-institute-categories').html("");
                jQuery('#wlim-fetch-institute-custom-fields').html("");
            }
        });

        /* Action to fetch category courses */
        jQuery(document).on('change', '#wlim-enquiry-category', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                fetchRecords('wl-mim-fetch-category-courses', '#wlim-fetch-category-courses', data);
            } else {
                jQuery('#wlim-fetch-category-courses').html("");
            }
        });

        /* Action to add enquiry */
        save('.add-enquiry-submit', '#wlim-add-enquiry-form', true);

        /* Action to fetch institute exams */
        jQuery(document).on('change', '#wlim-result-institute', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                var dob = $(this).data('dob');
                if(dob) {
                    data += '&dob=' + dob;
                }
                jQuery.ajax({
                    type: 'post',
                    url: wlimajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-institute-exams',
                    data: data,
                    success: function (data) {
                        jQuery('#wlim-fetch-institute-exams').html(data);
                        try {
                            jQuery('.wlim-date_of_birth').datetimepicker({
                                format: 'DD-MM-YYYY',
                                showClear: true,
                                showClose: true
                            });
                        } catch(e) {}
                    }
                });
            } else {
                jQuery('#wlim-fetch-institute-exams').html("");
            }
        });

         /* Action to fetch institute results exams */
        jQuery(document).on('change', '#wlim-results-institute', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                var dob = $(this).data('dob');
                if(dob) {
                    data += '&dob=' + dob;
                }
                jQuery.ajax({
                    type: 'post',
                    url: wlimajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-institute-exams',
                    data: data,
                    success: function (data) {
                        jQuery('#wlim-results-fetch-institute-exams').html(data);
                    }
                });
            } else {
                jQuery('#wlim-results-fetch-institute-exams').html("");
            }
        });

        /* Action to fetch institute dob field if enabled */
        jQuery(document).on('change', '#wlim-id-card-institute', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: wlimajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-institute-dob',
                    data: data,
                    success: function (data) {
                        jQuery('#wlim-fetch-institute-dob').html(data);
                        try {
                            jQuery('.wlim-date_of_birth').datetimepicker({
                                format: 'DD-MM-YYYY',
                                showClear: true,
                                showClose: true
                            });
                        } catch(e) {}
                    }
                });
            } else {
                jQuery('#wlim-fetch-institute-dob').html("");
            }
        });

        /* Action to fetch institute dob field for certificate if enabled */
        jQuery(document).on('change', '#wlim-certificate-institute', function () {
            jQuery('span.text-danger').remove();
            if (this.value) {
                var data = 'id=' + this.value;
                jQuery.ajax({
                    type: 'post',
                    url: wlimajaxurl + '?security=' + WLIMAjax.security + '&action=wl-mim-fetch-institute-dob-certificate',
                    data: data,
                    success: function (data) {
                        jQuery('#wlim-fetch-institute-dob-certificate').html(data);
                        try {
                            jQuery('.wlim-date_of_birth').datetimepicker({
                                format: 'DD-MM-YYYY',
                                showClear: true,
                                showClose: true
                            });
                        } catch(e) {}
                    }
                });
            } else {
                jQuery('#wlim-fetch-institute-dob-certificate').html("");
            }
        });

        /* Action to get id card */
        jQuery(document).on('submit', '#wlim-id-card-form', function (e) {
            e.preventDefault();
            var data = jQuery('#wlim-id-card-form').serialize();
            fetchRecords('wl-mim-get-id-card', '#wlim-get-id-card', data);
        });

        /* Action to get admit card */
        jQuery(document).on('submit', '#wlim-admit-card-form', function (e) {
            e.preventDefault();
            var data = jQuery('#wlim-admit-card-form').serialize();
            fetchRecords('wl-mim-get-admit-card', '#wlim-get-admit-card', data);
        });

        /* Action to get certificate */
        jQuery(document).on('submit', '#wlim-certificate-form', function (e) {
            e.preventDefault();
            var data = jQuery('#wlim-certificate-form').serialize();
            fetchRecords('wl-mim-get-certificate', '#wlim-get-certificate', data);
        });

        /* Action to get exam result */
        jQuery(document).on('submit', '#wlim-exam-result-form', function (e) {
            e.preventDefault();
            var data = jQuery('#wlim-exam-result-form').serialize();
            fetchRecords('wl-mim-get-exam-result', '#wlim-get-exam-result', data);
        });

         /* Print invoice */
         jQuery(document).on('click', '#wl-invoice-fee-invoice-print', function () {
            jQuery.print("#wl-invoice-fee-invoice");
        });

        /* Action to get exam results by name */
        jQuery(document).on('submit', '#wlim-exam-results-form', function (e) {
            e.preventDefault();
            var data = jQuery('#wlim-exam-results-form').serialize();
            fetchRecords('wl-mim-get-exam-results', '#wlim-get-exam-results', data);
        });
    });
})(jQuery);