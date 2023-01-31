(function ($) {
    "use strict";
    jQuery(document).ready(function () {
        var minDate = new Date();
        minDate.setFullYear(minDate.getFullYear() - 100);
        jQuery('#wl_min_date_issued').datetimepicker({
            format: 'DD-MM-YYYY',
            showClear: true,
            showClose: true,
            minDate: minDate
        });
        
        /* Date time picker */
        function dateTimePicker() {
            var minDate = new Date();
            minDate.setFullYear(minDate.getFullYear() - 100);
            jQuery('.wlim-date_of_birth').datetimepicker({
                format: 'DD-MM-YYYY',
                showClear: true,
                showClose: true,
                minDate: minDate
            });
            console.log(jQuery('#wl_min_date_issued'));
            
            jQuery('.wlim-batch-date').datetimepicker({
                format: 'DD-MM-YYYY',
                showClear: true,
                showClose: true
            });
            jQuery('.wlim-consumption_date').datetimepicker({
                format: 'DD-MM-YYYY',
                showClear: true,
                showClose: true
            });
            jQuery('.wlim-notes_date').datetimepicker({
                format: 'DD-MM-YYYY',
                showClear: true,
                showClose: true
            });
            jQuery('.wlim-batch-time').datetimepicker({
                format: 'h:mm A',
                showClear: true,
                showClose: true
            });
            jQuery('.wlim-exam-exam_date').datetimepicker({
                format: 'DD-MM-YYYY',
                showClear: true,
                showClose: true
            });
        }

        jQuery('.wlim-custom-duration-field').datetimepicker({
            format: 'DD-MM-YYYY h:mm A',
            showClear: true,
            showClose: true
        });

        jQuery('.wlim-attendance_date').datetimepicker({
            format: 'DD-MM-YYYY',
            showClear: true,
            showClose: true,
            maxDate: new Date()
        });

        jQuery('.wlim-follow_up_date').datetimepicker({
            format: 'DD-MM-YYYY',
            showClear: true,
            showClose: true,

        });
        jQuery('.wlim-enquiry-follow_up_date').datetimepicker({
            format: 'DD-MM-YYYY',
            showClear: true,
            showClose: true,

        });



        // Show date time picker inside modal
        function showDateTimePickerInsideModal(modal, refresh = false, target = null) {
            jQuery(document).on('shown.bs.modal', modal, function () {
                dateTimePicker();
                jQuery('span.text-danger').remove();
                jQuery('.is-valid').removeClass('is-valid');
                jQuery('.is-invalid').removeClass('is-invalid');

                if (refresh) {
                    jQuery(target).load(location.href + " " + target, function () {
                        /* Select single option */
                        jQuery('.wlim-enquiry-follow_up_date').datetimepicker({
            format: 'DD-MM-YYYY',
            showClear: true,
            showClose: true,

        });
                        try {
                            jQuery('.selectpicker').selectpicker({
                                liveSearch: true
                            });
                        } catch (error) {
                        }
                    });
                }
            });
        }

        /* Copy target content to clipboard on click */
        function copyToClipboard(selector, target) {
            jQuery(document).on('click', selector, function () {
                var value = jQuery(target).text();
                var temp = jQuery("<input>");
                jQuery("body").append(temp);
                temp.val(value).select();
                document.execCommand("copy");
                temp.remove();
                toastr.success('Copied to clipboard.');
            });
        }

        /* Reset form on open modal */
        function resetFormOnOpenModal(modal, form, refresh = false, target = null) {
            jQuery(document).on('shown.bs.modal', modal, function () {
                jQuery(form)[0].reset();
                jQuery('span.text-danger').remove();
                jQuery(form + ' .is-valid').removeClass('is-valid');
                jQuery(form + ' .is-invalid').removeClass('is-invalid');
                if (refresh) {
                    jQuery(target).load(location.href + " " + target, function () {
                        /* Select single option */
                        try {
                            jQuery('.selectpicker').selectpicker({
                                liveSearch: true
                            });
                        } catch (error) {
                        }
                    });
                }
            });
        }

        /* Append modal to body */
        function appendModalToBody(modal) {
            jQuery(modal).appendTo("body");
        }

        /* Select single option */
        jQuery('.selectpicker').selectpicker({
            liveSearch: true
        });

        jQuery(document).on('shown.bs.modal', '#add-enquiry', function () {
            jQuery('#wlim-enquiry-category').val("");
            jQuery('#wlim-fetch-category-courses').html("");
        });

        showDateTimePickerInsideModal('#add-main-course', true, '.wlim-selectpicker');
        showDateTimePickerInsideModal('#add-course', true, '.wlim-selectpicker');
        showDateTimePickerInsideModal('#add-enquiry', true, '.wlim-selectpicker');
        showDateTimePickerInsideModal('#add-student', true, '.wlim-selectpicker');
        showDateTimePickerInsideModal('#add-note', true, '.wlim-selectpicker');
        showDateTimePickerInsideModal('#add-expense');
        showDateTimePickerInsideModal('#add-batch');
        showDateTimePickerInsideModal('#add-exam');
        showDateTimePickerInsideModal('#add-administrator');

        copyToClipboard('#wl_im_id_card_form_shortcode_copy', '#wl_im_id_card_form_shortcode');
        copyToClipboard('#wl_im_admit_card_form_shortcode_copy', '#wl_im_admit_card_form_shortcode');
        copyToClipboard('#wl_im_certificate_form_shortcode_copy', '#wl_im_certificate_form_shortcode');
        copyToClipboard('#wl_im_noticeboard_form_shortcode_copy', '#wl_im_noticeboard_form_shortcode');
        copyToClipboard('#wl_im_enquiry_form_shortcode_copy', '#wl_im_enquiry_form_shortcode');
        copyToClipboard('#wl_im_exam_result_form_shortcode_copy', '#wl_im_exam_result_form_shortcode');
        copyToClipboard('#wl_im_exam_results_by_name_form_shortcode_copy', '#wl_im_exam_results_by_name_form_shortcode');

        resetFormOnOpenModal('#add-administrator', '#wlim-add-administrator-form');
        resetFormOnOpenModal('#add-category', '#wlim-add-category-form');
        resetFormOnOpenModal('#add-course', '#wlim-add-course-form', true, '.wlim-selectpicker');
        resetFormOnOpenModal('#add-main-course', '#wlim-add-main-course-form', true, '.wlim-selectpicker');
        resetFormOnOpenModal('#add-fee-type', '#wlim-add-fee-type-form');
        resetFormOnOpenModal('#add-installment', '#wlim-add-installment-form', true, '.wlim-add-installment-form-fields');
        resetFormOnOpenModal('#add-invoice', '#wlim-add-invoice-form', true, '.wlim-add-invoice-form-fields');
        resetFormOnOpenModal('#add-batch', '#wlim-add-batch-form', true, '.wlim-selectpicker');
        resetFormOnOpenModal('#add-notice', '#wlim-add-notice-form');
        resetFormOnOpenModal('#add-exam', '#wlim-add-exam-form');

        appendModalToBody('#add-administrator');
        appendModalToBody('#update-administrator');
        appendModalToBody('#add-institute');
        appendModalToBody('#update-institute');
        appendModalToBody('#add-main-course');
        appendModalToBody('#update-main-course');
        appendModalToBody('#add-category');
        appendModalToBody('#update-category');
        appendModalToBody('#add-course');
        appendModalToBody('#update-course');
        appendModalToBody('#add-batch');
        appendModalToBody('#update-batch');
        appendModalToBody('#add-enquiry');
        appendModalToBody('#update-enquiry');
        appendModalToBody('#add-expense');
        appendModalToBody('#update-expense');
        appendModalToBody('#add-student');
        appendModalToBody('#update-student');
        appendModalToBody('#print-student');
        appendModalToBody('#print-student-pending-fees');
        appendModalToBody('#print-student-admission-detail');
        appendModalToBody('#print-student-fees-report');
        appendModalToBody('#print-student-certificate');
        appendModalToBody('#view-student-note');
        appendModalToBody('#add-note');
        appendModalToBody('#update-note');
        appendModalToBody('#add-fee-type');
        appendModalToBody('#update-fee-type');
        appendModalToBody('#add-installment');
        appendModalToBody('#update-installment');
        appendModalToBody('#add-invoice');
        appendModalToBody('#update-invoice');
        appendModalToBody('#print-installment-fee-receipt');
        appendModalToBody('#add-notice');
        appendModalToBody('#update-notice');
        appendModalToBody('#add-exam');
        appendModalToBody('#update-exam');

        /* On change notification channel */
        var wlimEmailTemplate = jQuery('.wlim-email-template');
        wlimEmailTemplate.hide();
        jQuery(document).on('change', '#wlim-email-notification', function () {
            if (this.checked) {
                wlimEmailTemplate.fadeIn();
            } else {
                wlimEmailTemplate.fadeOut();
            }
        });

        var wlimSMSTemplate = jQuery('.wlim-sms-template');
        wlimSMSTemplate.hide();
        jQuery(document).on('change', '#wlim-sms-notification', function () {
            if (this.checked) {
                wlimSMSTemplate.fadeIn();
            } else {
                wlimSMSTemplate.fadeOut();
            }
        });

        /* On change link to notice */
        jQuery('.wlim-notice-attachment').hide();
        jQuery('.wlim-notice-url').hide();
        jQuery(document).on('change', 'input[type=radio][name=notice_link_to]', function () {
            if (this.value == 'attachment') {
                jQuery('.wlim-notice-attachment').fadeIn();
                jQuery('.wlim-notice-url').hide();
            }
            else if (this.value == 'url') {
                jQuery('.wlim-notice-url').fadeIn();
                jQuery('.wlim-notice-attachment').hide();
            }
        });

        /* Hide link to notice fields on open modal */
        jQuery(document).on('shown.bs.modal', '#add-notice', function () {
            jQuery('.wlim-notice-attachment').hide();
            jQuery('.wlim-notice-url').hide();
        });

        /* Fee type rows */
        jQuery(document).on('click', '.add-more-fee-types', function () {
            jQuery('.fee_types_rows > tr:first').clone().find('input').val('').end().appendTo('.fee_types_rows');
        });
        jQuery(document).on('click', '.remove_row', function () {
            var rowCount = jQuery('.fee_types_rows tr').length;
            if (rowCount > 1) {
                jQuery(this).closest('tr').remove();
            }
        });

        /* Exam notes rows */
        jQuery(document).on('click', '.add-more-exam-notes', function () {
            jQuery('.exam_notes_rows > tr:first').clone().find('input').val('').end().appendTo('.exam_notes_rows');
        });
        jQuery(document).on('click', '.remove_row', function () {
            var rowCount = jQuery('.exam_notes_rows tr').length;
            if (rowCount > 1) {
                jQuery(this).closest('tr').remove();
            }
        });

        /* Exam marks rows */
        jQuery(document).on('click', '.add-more-exam-marks', function () {
            jQuery('.exam_marks_rows > tr:first').clone().find('input').val('').end().appendTo('.exam_marks_rows');
        });
        jQuery(document).on('click', '.remove_row', function () {
            var rowCount = jQuery('.exam_marks_rows tr').length;
            if (rowCount > 1) {
                jQuery(this).closest('tr').remove();
            }
        });

        /* On change custom duration */
        jQuery('.wlim-custom-duration').hide();
        jQuery(document).on('change', '#wlim-custom-duration-checkbox', function () {
            if (this.checked) {
                jQuery('.wlim-custom-duration').fadeIn();
                jQuery('.wlim-predefined-period').hide();
            } else {
                jQuery('.wlim-custom-duration').hide();
                jQuery('.wlim-predefined-period').fadeIn();
            }
        });

        jQuery(document).on('change', '#wlim-overall-report', function () {
            if (this.value == 'current-students' || this.value == 'pending-fees-by-batch') {
                jQuery('#wlim-duration-period').hide();
            } else {
                jQuery('#wlim-duration-period').fadeIn();
            }
        });
        jQuery(document).on('change', '#wlim-overall-report', function () {
            if (this.value == 'enquiries' ) {
                jQuery('#wlim-overall-report-selection').hide();
            } else {
                jQuery('#wlim-overall-report-selection').fadeIn();
            }
        });

        jQuery('.wlim-staff-record-fields').hide();
        jQuery(document).on('change', '.wlim-administrator-add_staff_record', function () {
            if (this.checked) {
                jQuery('.wlim-staff-record-fields').fadeIn();
            } else {
                jQuery('.wlim-staff-record-fields').hide();
            }
        });

        jQuery(document).on('shown.bs.modal', '#add-administrator', function () {
            var staffRecords = jQuery('.wlim-administrator-add_staff_record');
            if (staffRecords.checked) {
                jQuery('.wlim-staff-record-fields').fadeIn();
            } else {
                jQuery('.wlim-staff-record-fields').hide();
            }
        });

        jQuery('.wlim-manage-single-institute').hide();
        jQuery(document).on('change', '.wlim-manage_multi_institute', function () {
            if (this.checked) {
                jQuery('.wlim-manage-single-institute').hide();
            } else {
                jQuery('.wlim-manage-single-institute').fadeIn();
            }
        });

        jQuery(document).on('shown.bs.modal', '#add-user-administrator', function () {
            var manageMultiInstitute = jQuery('.wlim-manage_multi_institute');
            if (manageMultiInstitute.checked) {
                jQuery('.wlim-manage-single-institute').hide();
            } else {
                jQuery('.wlim-manage-single-institute').fadeIn();
            }
        });

        var paymentPaypalSetting = jQuery('.wlim-setting-payment_paypal');
        var paymentRazorpaySetting = jQuery('.wlim-setting-payment_razorpay');
        var paymentStripeSetting = jQuery('.wlim-setting-payment_stripe');
        var paymentPaystackSetting = jQuery('.wlim-setting-payment_paystack');
        paymentRazorpaySetting.hide();
        paymentStripeSetting.hide();
        /* On selecting payment method */
        jQuery(document).on('change', '#wlim-setting-payment_method', function () {
            if (this.value == 'paypal') {
                paymentRazorpaySetting.hide();
                paymentStripeSetting.hide();
                paymentPaystackSetting.hide();
                paymentPaypalSetting.fadeIn();
            } else if (this.value == 'razorpay') {
                paymentPaypalSetting.hide();
                paymentStripeSetting.hide();
                paymentPaystackSetting.hide();
                paymentRazorpaySetting.fadeIn();
            } else if (this.value == 'stripe') {
                paymentPaypalSetting.hide();
                paymentRazorpaySetting.hide();
                paymentPaystackSetting.hide();
                paymentStripeSetting.fadeIn();
            }else if (this.value == 'paystack') {
                paymentPaypalSetting.hide();
                paymentRazorpaySetting.hide();
                paymentStripeSetting.hide();
                paymentPaystackSetting.fadeIn();
            }
        });

        /* On selecting sms provider */
        var smsStrikerSetting = jQuery('.wlmim-sms-striker');
        var smsPointSMSSetting = jQuery('.wlmim-sms-pointsms');
        var smsauurumdigital = jQuery('.wlmim-sms-auurumdigital');
        var smsNexmoSetting = jQuery('.wlmim-sms-nexmo');
        var smsMsgclubSetting = jQuery('.wlmim-sms-msgclub');
        var smsTextlocalSetting = jQuery('.wlmim-sms-textlocal');
        var smsEBulkSMSSetting = jQuery('.wlmim-sms-ebulksms');

        var smsProvider = jQuery('#wlim-setting-sms_provider').val();

        smsStrikerSetting.hide();
        smsPointSMSSetting.hide();
        smsNexmoSetting.hide();
        smsMsgclubSetting.hide();
        smsTextlocalSetting.hide();
        smsEBulkSMSSetting.hide();
        smsauurumdigital.hide();

        if (smsProvider == 'smsstriker') {
            smsStrikerSetting.show();
        } else if (smsProvider == 'pointsms') {
            smsPointSMSSetting.show();
        } else if (smsProvider == 'nexmo') {
            smsNexmoSetting.show();
        } else if (smsProvider == 'msgclub') {
            smsMsgclubSetting.show();
        } else if (smsProvider == 'textlocal') {
            smsTextlocalSetting.show();
        } else if (smsProvider == 'ebulksms') {
            smsEBulkSMSSetting.show();
        }else if (smsProvider == 'auurumdigital') {
            smsauurumdigital.show();
        }

        jQuery(document).on('change', '#wlim-setting-sms_provider', function () {
            if (this.value == 'smsstriker') {
                smsPointSMSSetting.hide();
                smsNexmoSetting.hide();
                smsMsgclubSetting.hide();
                smsTextlocalSetting.hide();
                smsEBulkSMSSetting.hide();
                smsStrikerSetting.fadeIn();
            } else if (this.value == 'pointsms') {
                smsStrikerSetting.hide();
                smsNexmoSetting.hide();
                smsMsgclubSetting.hide();
                smsTextlocalSetting.hide();
                smsEBulkSMSSetting.hide();
                smsPointSMSSetting.fadeIn();
            } else if (this.value == 'nexmo') {
                smsStrikerSetting.hide();
                smsPointSMSSetting.hide();
                smsMsgclubSetting.hide();
                smsTextlocalSetting.hide();
                smsEBulkSMSSetting.hide();
                smsNexmoSetting.fadeIn();
            } else if (this.value == 'msgclub') {
                smsStrikerSetting.hide();
                smsPointSMSSetting.hide();
                smsNexmoSetting.hide();
                smsTextlocalSetting.hide();
                smsEBulkSMSSetting.hide();
                smsMsgclubSetting.fadeIn();
            } else if (this.value == 'textlocal') {
                smsStrikerSetting.hide();
                smsPointSMSSetting.hide();
                smsNexmoSetting.hide();
                smsMsgclubSetting.hide();
                smsEBulkSMSSetting.hide();
                smsTextlocalSetting.fadeIn();
            } else if (this.value == 'ebulksms') {
                smsStrikerSetting.hide();
                smsPointSMSSetting.hide();
                smsNexmoSetting.hide();
                smsMsgclubSetting.hide();
                smsTextlocalSetting.hide();
                smsEBulkSMSSetting.fadeIn();
            }else if (this.value == 'auurumdigital') {
                smsStrikerSetting.hide();
                smsPointSMSSetting.hide();
                smsNexmoSetting.hide();
                smsMsgclubSetting.hide();
                smsTextlocalSetting.hide();
                smsEBulkSMSSetting.hide();
                smsauurumdigital.fadeIn();
            }
        });

        /* On selecting sms template */
        var smsEnquiryReceived = jQuery('.wlmim-sms-enquiry_received');
        var smsEnquiryReceivedToAdmin = jQuery('.wlmim-sms-enquiry_received_to_admin');
        var smsStudentregistered = jQuery('.wlmim-sms-student_registered');
        var smsFeesSubmitted = jQuery('.wlmim-sms-fees_submitted');
        var smsStudentBirthday = jQuery('.wlmim-sms-student_birthday');

        var smsTemplate = jQuery('#wlim-setting-sms_template').val();

        smsEnquiryReceived.hide();
        smsEnquiryReceivedToAdmin.hide();
        smsStudentregistered.hide();
        smsFeesSubmitted.hide();
        smsStudentBirthday.hide();

        if (smsTemplate == 'enquiry_received') {
            smsEnquiryReceived.show();
        } else if (smsTemplate == 'enquiry_received_to_admin') {
            smsEnquiryReceivedToAdmin.show();
        } else if (smsTemplate == 'student_registered') {
            smsStudentregistered.show();
        } else if (smsTemplate == 'fees_submitted') {
            smsFeesSubmitted.show();
        } else if (smsTemplate == 'student_birthday') {
            smsStudentBirthday.show();
        }

        jQuery(document).on('change', '#wlim-setting-sms_template', function () {
            if (this.value == 'enquiry_received') {
                smsEnquiryReceivedToAdmin.hide();
                smsStudentregistered.hide();
                smsFeesSubmitted.hide();
                smsStudentBirthday.hide();
                smsEnquiryReceived.fadeIn();
            } else if (this.value == 'enquiry_received_to_admin') {
                smsEnquiryReceived.hide();
                smsStudentregistered.hide();
                smsFeesSubmitted.hide();
                smsStudentBirthday.hide();
                smsEnquiryReceivedToAdmin.fadeIn();
            } else if (this.value == 'student_registered') {
                smsEnquiryReceived.hide();
                smsEnquiryReceivedToAdmin.hide();
                smsFeesSubmitted.hide();
                smsStudentBirthday.hide();
                smsStudentregistered.fadeIn();
            } else if (this.value == 'fees_submitted') {
                smsEnquiryReceived.hide();
                smsEnquiryReceivedToAdmin.hide();
                smsStudentregistered.hide();
                smsStudentBirthday.hide();
                smsFeesSubmitted.fadeIn();
            } else if (this.value == 'student_birthday') {
                smsEnquiryReceived.hide();
                smsEnquiryReceivedToAdmin.hide();
                smsStudentregistered.hide();
                smsFeesSubmitted.hide();
                smsStudentBirthday.fadeIn();
            }
        });

        /* Remove note */
        jQuery(document).on('click', '.wlmim-remove-note', function (e) {
            jQuery(this).parent('li').fadeOut(300, function () {
                jQuery(this).remove();
            });
        });

        /* Initialize DataTable for notes in student dashboard */
        jQuery('#student-note-table').DataTable({
            aaSorting: [],
            responsive: true
        });

        /* On change fee payment in student dashboard */
        jQuery('.wlim-payment-individual-fee').hide();
        jQuery(document).on('change', 'input[name="fee_payment"]', function () {
            if (this.value == 'individual_fee') {
                jQuery('.wlim-payment-individual-fee').fadeIn();
            } else {
                jQuery('.wlim-payment-individual-fee').fadeOut();
            }
        });

        /* Allow student to login checkbox */
        jQuery(document).on('change', '#wlim-student-allow_login', function () {
            if (this.checked) {
                jQuery('.wlim-allow-login-fields').fadeIn();
            } else {
                jQuery('.wlim-allow-login-fields').fadeOut();
            }
        });


        jQuery(document).on('change', '#no-end-date', function () {
            if (this.checked) {
                jQuery('.no-end-date-div').fadeOut();
            } else {
                jQuery('.no-end-date-div').fadeIn();
            }
        });

        /* Print installment fee receipt */
        jQuery(document).on('click', '#wl-installment-fee-receipt-print', function () {
            jQuery.print("#wl-installment-fee-receipt");
        });

        /* Print ID card */
        jQuery(document).on('click', '#wl-id-card-print', function () {
            jQuery.print("#wl-id-card");
        });

        /* Print admission detail */
        jQuery(document).on('click', '#wl-admission-detail-print', function () {
            jQuery.print("#wl-admission-detail");
        });

        /* Print fees report */
        jQuery(document).on('click', '#wl-fees-report-print', function () {
            jQuery.print("#wl-fees-report");
        });

        /* Print pending fees */
        jQuery(document).on('click', '#wl-pending-fees-print', function () {
            jQuery.print("#wl-pending-fees");
        });

        /* Print certificate */
        jQuery(document).on('click', '#wl-certificate-print', function () {
            jQuery.print("#wl-certificate");
        });

        /* Print student result */
        jQuery(document).on('click', '#wlmim-admit-card-print-button', function () {
            jQuery.print("#wlmim-admit-card-print");
        });

        /* Print student result */
        jQuery(document).on('click', '#wlmim-exam-result-print-button', function () {
            jQuery.print("#wlmim-exam-result-print");
        });

        /* Print invoice */
        jQuery(document).on('click', '#wl-invoice-fee-invoice-print', function () {
            jQuery.print("#wl-invoice-fee-invoice");
        });

        /* Print id cards */
        jQuery(document).on('click', '#wl-id-cards-print', function () {
            jQuery.print("#wl-print-id-cards");
        });

        function wlmimDelay(callback, ms) {
          var timer = 0;
          return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
              callback.apply(context, args);
            }, ms || 0);
          };
        }

        jQuery(document).on('keyup', '.wlima-amount-payable', wlmimDelay(function() {
            var sum = 0;
            jQuery('.wlima-amount-payable').each(function() {
                sum += Math.abs(parseFloat(jQuery(this).val()));
            });
            if(isNaN(sum)) {
                sum = '-';
            }
            jQuery('.wlima-amount-payable-total').html(sum.toFixed(2));
        }, 500));
    });


})(jQuery);
