<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_PaymentHelper.php' );

$institute_id = WL_MIM_Helper::get_current_institute_id();
global $wpdb;
$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );

$general_enquiry           = WL_MIM_SettingHelper::get_general_enquiry_settings( $institute_id );
$general_institute         = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
$general_receipt_prefix    = WL_MIM_SettingHelper::get_general_receipt_prefix_settings( $institute_id );

$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings( $institute_id );

$general_enable_signature_in_admission_detail = WL_MIM_SettingHelper::get_general_enable_signature_in_admission_detail( $institute_id );

$payment          = WL_MIM_SettingHelper::get_payment_settings( $institute_id );
$payment_paypal   = WL_MIM_SettingHelper::get_payment_paypal_settings( $institute_id );
$payment_razorpay = WL_MIM_SettingHelper::get_payment_razorpay_settings( $institute_id );
$payment_instamojo = WL_MIM_SettingHelper::get_payment_instamojo_settings( $institute_id );
$payment_paystack = WL_MIM_SettingHelper::get_payment_paystack_settings( $institute_id );
$payment_stripe   = WL_MIM_SettingHelper::get_payment_stripe_settings( $institute_id );

$email = WL_MIM_SettingHelper::get_email_settings( $institute_id );

$sms               = WL_MIM_SettingHelper::get_sms_settings( $institute_id );
$sms_striker       = WL_MIM_SettingHelper::get_sms_smsstriker_settings( $institute_id );
$sms_pointsms      = WL_MIM_SettingHelper::get_sms_pointsms_settings( $institute_id );
$sms_auurumdigital = WL_MIM_SettingHelper::get_sms_auurumdigital_settings( $institute_id );
$sms_msgclub       = WL_MIM_SettingHelper::get_sms_msgclub_settings( $institute_id );
$sms_nexmo         = WL_MIM_SettingHelper::get_sms_nexmo_settings( $institute_id );
$sms_textlocal     = WL_MIM_SettingHelper::get_sms_textlocal_settings( $institute_id );
$sms_ebulksms      = WL_MIM_SettingHelper::get_sms_ebulksms_settings( $institute_id );

$sms_template_enquiry_received          = WL_MIM_SettingHelper::get_sms_template_enquiry_received( $institute_id );
$sms_template_enquiry_received_to_admin = WL_MIM_SettingHelper::get_sms_template_enquiry_received_to_admin( $institute_id );
$sms_template_student_registered        = WL_MIM_SettingHelper::get_sms_template_student_registered( $institute_id );
$sms_template_fees_submitted            = WL_MIM_SettingHelper::get_sms_template_fees_submitted( $institute_id );
$sms_template_student_birthday          = WL_MIM_SettingHelper::get_sms_template_student_birthday( $institute_id );

$admit_card_dob_enable  = WL_MIM_SettingHelper::get_admit_card_dob_enable_settings( $institute_id );
$id_card_dob_enable     = WL_MIM_SettingHelper::get_id_card_dob_enable_settings( $institute_id );
$certificate_dob_enable = WL_MIM_SettingHelper::get_certificate_dob_enable_settings( $institute_id );

$id_card     = WL_MIM_SettingHelper::get_id_card_settings( $institute_id );
$admit_card  = WL_MIM_SettingHelper::get_admit_card_settings( $institute_id );
$certificate = WL_MIM_SettingHelper::get_certificate_settings( $institute_id );

if ( empty( $general_institute['institute_name'] ) ) {
	$institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
	$institute_name = $general_institute['institute_name'];
}
?>

<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-cog"></i> <?php esc_html_e( 'Settings', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can view and modify institute settings.', WL_MIM_DOMAIN ); ?>
            </div>
            <!-- end main header content -->
        </div>
    </div>
    <!-- end - row 1 -->

    <!-- row 2 -->
    <div class="row">
        <div class="col-xs-12 col-sm-4">
            <div class="list-group" id="list-tab" role="tablist">
                <a class="list-group-item list-group-item-action active" id="list-general-list" data-toggle="list" href="#list-general" role="tab" aria-controls="general"><?php esc_html_e( 'General', WL_MIM_DOMAIN ); ?></a>
                <a class="list-group-item list-group-item-action" id="list-payment-list" data-toggle="list" href="#list-payment" role="tab" aria-controls="payment"><?php esc_html_e( 'Payment', WL_MIM_DOMAIN ); ?></a>
                <a class="list-group-item list-group-item-action" id="list-email-list" data-toggle="list" href="#list-email" role="tab" aria-controls="email"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></a>
                <a class="list-group-item list-group-item-action" id="list-sms-list" data-toggle="list" href="#list-sms" role="tab" aria-controls="sms"><?php esc_html_e( 'SMS', WL_MIM_DOMAIN ); ?></a>
                <a class="list-group-item list-group-item-action" id="list-custom-fields-list" data-toggle="list" href="#list-custom-fields" role="tab" aria-controls="custom-fields"><?php esc_html_e( 'Custom Fields', WL_MIM_DOMAIN ); ?></a>
                <a class="list-group-item list-group-item-action" id="list-admit-card-list" data-toggle="list" href="#list-admit-card" role="tab" aria-controls="admit-card"><?php esc_html_e( 'Admit Card', WL_MIM_DOMAIN ); ?></a>
                <a class="list-group-item list-group-item-action" id="list-id-card-list" data-toggle="list" href="#list-id-card" role="tab" aria-controls="id-card"><?php esc_html_e( 'ID Card', WL_MIM_DOMAIN ); ?></a>
                <!-- <a class="list-group-item list-group-item-action" id="list-certificate-list" data-toggle="list" href="#list-certificate" role="tab" aria-controls="certificate"><?php esc_html_e( 'Certificate', WL_MIM_DOMAIN ); ?></a> -->
                <a class="list-group-item list-group-item-action" id="list-noticeboard-list" data-toggle="list" href="#list-noticeboard" role="tab" aria-controls="noticeboard"><?php esc_html_e( 'Noticeboard', WL_MIM_DOMAIN ); ?></a>
            </div>
        </div>
        <div class="col-xs-12 col-sm-8">
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="list-general" role="tabpanel" aria-labelledby="list-general-list">
                    <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-general-settings-form" enctype="multipart/form-data">
						<?php $nonce = wp_create_nonce( 'save-general-settings' ); ?>
                        <input type="hidden" name="save-general-settings" value="<?php echo esc_attr( $nonce ); ?>">
                        <input type="hidden" name="action" value="wl-mim-save-general-settings">

                        <div class="row">

                            <div class="form-check col-sm-12 mb-2">
                                <input name="enquiry_form_title_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-enquiry_form_title_enable" <?php checked( $general_enquiry['enquiry_form_title_enable'], '1', true ); ?>>
                                <label class="form-check-label" for="wlim-setting-enquiry_form_title_enable">
									<?php esc_html_e( 'Enable Enquiry Form Title?', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-enquiry_form_title" class="col-form-label">
									<?php esc_html_e( 'Enquiry Form Title', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="enquiry_form_title" type="text" class="form-control" id="wlim-setting-enquiry_form_title" placeholder="<?php esc_html_e( "Enquiry Form Title", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $general_enquiry['enquiry_form_title'] ); ?>">
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-institute_logo" class="col-form-label"><?php esc_html_e( 'Institute Logo', WL_MIM_DOMAIN ); ?>:</label><br>
								<?php if ( ! empty ( $general_institute['institute_logo'] ) ) { ?>
                                    <img src="<?php echo wp_get_attachment_url( $general_institute['institute_logo'] ); ?>" class="img-responsive" id="wlim-institute_advanced_logo">
								<?php } ?>
                                <input name="institute_logo" type="file" id="wlim-setting-institute_logo">
                            </div>

                            <div class="form-check col-sm-12 mb-2">
                                <input name="institute_logo_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-institute_logo_enable" <?php checked( $general_institute['institute_logo_enable'], '1', true ); ?>>
                                <label class="form-check-label" for="wlim-setting-institute_logo_enable">
									<?php esc_html_e( 'Enable Institute Logo?', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-institute_name" class="col-form-label">
									<?php esc_html_e( 'Institute Name', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="institute_name" type="text" class="form-control" id="wlim-setting-institute_name" placeholder="<?php esc_html_e( "Institute Name", WL_MIM_DOMAIN ); ?>" value="<?php
                                // echo esc_attr( $general_institute['institute_name'] ); 
                                echo esc_attr( $row->name ); 
                                 
                                 ?>">
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-institute_address" class="col-form-label">
									<?php esc_html_e( 'Institute Address', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <textarea name="institute_address" type="text" class="form-control" id="wlim-setting-institute_address" placeholder="<?php esc_html_e( "Institute Address", WL_MIM_DOMAIN ); ?>"><?php 
                                // echo esc_html( $general_institute['institute_address'] ); 
                                echo esc_attr( $row->address ); 
                                ?></textarea>
                            </div>

                            <div class="form-group col-xs-12 col-sm-6">
                                <label for="wlim-setting-institute_center_code" class="col-form-label">
                                    <?php esc_html_e( 'Institute Center Code', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="institute_center_code" type="text" class="form-control" id="wlim-setting-institute_center_code" placeholder="<?php esc_html_e( "Institute Center Code", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $general_institute['institute_center_code'] ); ?>">
                            </div>

                            <div class="form-group col-xs-12 col-sm-6">
                                <label for="wlim-setting-institute_phone" class="col-form-label">
									<?php esc_html_e( 'Institute Phone', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="institute_phone" type="text" class="form-control" id="wlim-setting-institute_phone" placeholder="<?php esc_html_e( "Institute Phone", WL_MIM_DOMAIN ); ?>" value="<?php 
                                // echo esc_attr( $general_institute['institute_phone'] ); 
                                echo esc_attr( $row->phone ); 
                                
                                ?>">
                            </div>

                            <div class="form-group col-xs-12 col-sm-6">
                                <label for="wlim-setting-institute_email" class="col-form-label">
									<?php esc_html_e( 'Institute Email', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="institute_email" type="email" class="form-control" id="wlim-setting-institute_email" placeholder="<?php esc_html_e( "Institute Email", WL_MIM_DOMAIN ); ?>" value="<?php
                                //  echo esc_attr( $general_institute['institute_email'] ); 
                                 echo esc_attr( $row->email ); 
                                 ?>">
                            </div>

                            <div class="form-group col-xs-12 col-sm-6">
                                <label for="wlim-setting-enrollment_id_prefix" class="col-form-label">
                                    <?php 
									    esc_html_e( 'Enrollment ID Prefix', WL_MIM_DOMAIN ); 
                                    ?>:
                                </label>
                                <input name="enrollment_id_prefix" type="text" class="form-control" id="wlim-setting-enrollment_id_prefix" placeholder="<?php esc_html_e( "Enrollment ID Prefix", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $general_enrollment_prefix ); ?>">
                            </div>

                            <div class="form-group col-xs-12 col-sm-6">
                                <label for="wlim-setting-receipt_number_prefix" class="col-form-label">
									<?php esc_html_e( 'Receipt Number Prefix', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="receipt_number_prefix" type="text" class="form-control" id="wlim-setting-receipt_number_prefix" placeholder="<?php esc_html_e( "Receipt Number Prefix", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $general_receipt_prefix ); ?>">
                            </div>

                            <div class="form-check col-xs-12 col-sm-6 mt-2">
                                <input name="enable_roll_number" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-enable_roll_number" value="1" <?php checked( $general_enable_roll_number, true, true ); ?>>
                                <label class="form-check-label" for="wlim-setting-enable_roll_number">
                                    <?php esc_html_e( 'Enable Roll Number?', WL_MIM_DOMAIN ); ?>
                                </label>

                                <div class="mb-1"></div>

                                <input name="enable_signature_admission" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-enable_signature_in_admission_detail" value="1" <?php checked( $general_enable_signature_in_admission_detail, true, true ); ?>>
                                <label class="form-check-label" for="wlim-setting-enable_signature_in_admission_detail">
                                    <?php esc_html_e( 'Enable Candidate Signature in Admission Detail?', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary save-general-settings-submit"><?php esc_html_e( 'Save Settings', WL_MIM_DOMAIN ); ?></button>
                    </form>
                </div>
                <div class="tab-pane fade" id="list-payment" role="tabpanel" aria-labelledby="list-payment-list">
                    <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-payment-settings-form" x-data="{ payment_method :'paypal'}">
						<?php $nonce = wp_create_nonce( 'save-payment-settings' ); ?>
                        <input type="hidden" name="save-payment-settings" value="<?php echo esc_attr( $nonce ); ?>">
                        <input type="hidden" name="action" value="wl-mim-save-payment-settings">

                        <div class="row">
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_currency" class="col-form-label"><?php esc_html_e( "Currency", WL_MIM_DOMAIN ); ?>:</label>
                                <select name="payment_currency" class="form-control" id="wlim-setting-payment_currency">
                                    <option value=""><?php esc_html_e( "None", WL_MIM_DOMAIN ); ?></option>
									<?php
									foreach ( WL_MIM_PaymentHelper::get_all_currencies() as $code => $value ) { ?>
                                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $payment['payment_currency'], $code ); ?>><?php echo esc_html( $value . " (" . $code . ")" ); ?></option>
									<?php
									} ?>
                                </select>
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_method" class="col-form-label">
									<?php esc_html_e( 'Payment Methods', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <select name="payment_method" class="form-control" id="wlim-setting-payment_method" x-on:change="payment_method = $event.target.value">
                                    <option value="paypal"><?php esc_html_e( "PayPal", WL_MIM_DOMAIN ); ?></option>
                                    <option value="razorpay"><?php esc_html_e( "Razorpay", WL_MIM_DOMAIN ); ?></option>
                                    <option value="stripe"><?php esc_html_e( "Stripe", WL_MIM_DOMAIN ); ?></option>
                                    <option value="paystack"><?php esc_html_e( "Paystack", WL_MIM_DOMAIN ); ?></option>
                                    <option value="instamojo"><?php esc_html_e( "Instamojo", WL_MIM_DOMAIN ); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row wlim-setting-payment_paypal" x-show="payment_method === 'paypal'">
                            <div class="form-check col-sm-12 mb-3">
                                <input name="payment_paypal_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-payment_paypal_enable" <?php checked( $payment_paypal['enable'], true ); ?>>
                                <label for="wlim-setting-payment_paypal_enable" class="form-check-label">
									<?php esc_html_e( 'Enable PayPal', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_paypal_mode" class="col-form-label"><?php esc_html_e( "PayPal Mode", WL_MIM_DOMAIN ); ?>:</label>
                                <select name="payment_paypal_mode" class="form-control" id="wlim-setting-payment_paypal_mode">
                                    <option value="sandbox" <?php selected( $payment_paypal['mode'], 'sandbox' ); ?>><?php esc_html_e( "Sandbox", WL_MIM_DOMAIN ); ?></option>
                                    <option value="live" <?php selected( $payment_paypal['mode'], 'live' ); ?>><?php esc_html_e( "Live", WL_MIM_DOMAIN ); ?></option>
                                </select>
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_paypal_business_email" class="col-form-label">
									<?php esc_html_e( 'Business Email', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="payment_paypal_business_email" type="email" class="form-control" id="wlim-setting-payment_paypal_business_email" placeholder="<?php esc_html_e( "PayPal Business Email", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $payment_paypal['business_email'] ); ?>">
                            </div>
                            <div class="col-sm-12">
                                <label><?php esc_html_e( "PayPal Notify URL", WL_MIM_DOMAIN ); ?>: </label><br>
                                <span class="text-primary"><?php echo WL_MIM_PaymentHelper::get_paypal_notify_url(); ?></span><br>
                                <small class="font-weight-bold">
                                    ( <?php esc_html_e( 'To save transactions, you need to enable PayPal IPN (Instant Payment Notification) in your PayPal Business Account and use this notify URL' ); ?>
                                    )
                                </small>
                                <small>
                                    <ol>
                                        <li><?php esc_html_e( 'Log into your PayPal account.' ); ?></li>
                                        <li><?php esc_html_e( 'Go to Profile then "My Selling Tools".' ); ?></li>
                                        <li><?php esc_html_e( 'Look for an option labelled "Instant Payment Notification". Click on the update button for that option.' ); ?></li>
                                        <li><?php esc_html_e( 'Click "Choose IPN Settings".' ); ?></li>
                                        <li><?php esc_html_e( 'Enter the URL given above and hit "Save".' ); ?></li>
                                    </ol>
                                </small>
                            </div>
							<?php
							if ( empty( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
									<?php esc_html_e( "Paypal is disabled.", WL_MIM_DOMAIN ); ?><?php esc_html_e( "Please select a valid currency.", WL_MIM_DOMAIN ); ?>
                                </div>
							<?php
							} elseif ( ! WL_MIM_PaymentHelper::paypal_support_currency_provided( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
									<?php esc_html_e( "PayPal is disabled.", WL_MIM_DOMAIN ); ?>
									<?php esc_html_e( "Currency", WL_MIM_DOMAIN ); ?>:
                                    <strong><?php echo esc_html( $payment['payment_currency'] ); ?></strong>&nbsp;<?php esc_html_e( "is not supported.", WL_MIM_DOMAIN ); ?>
                                </div>
							<?php
							}
							?>
                        </div>

                        <div class="row wlim-setting-payment_stripe" x-show="payment_method === 'stripe'">
                            <div class="form-check col-sm-12 mb-3">
                                <input name="payment_stripe_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-payment_stripe_enable" <?php checked( $payment_stripe['enable'], true ); ?>>
                                <label for="wlim-setting-payment_stripe_enable" class="form-check-label">
									<?php esc_html_e( 'Enable Stripe', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_stripe_publishable_key" class="col-form-label">
									<?php esc_html_e( 'Stripe Publishable Key', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="payment_stripe_publishable_key" type="text" class="form-control" id="wlim-setting-payment_stripe_publishable_key" placeholder="<?php esc_html_e( "Stripe Publishable Key", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $payment_stripe['publishable_key'] ); ?>">
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_stripe_secret_key" class="col-form-label">
									<?php esc_html_e( 'Stripe Secret Key', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="payment_stripe_secret_key" type="text" class="form-control" id="wlim-setting-payment_stripe_secret_key" placeholder="<?php esc_html_e( "Stripe Secret Key", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $payment_stripe['secret_key'] ); ?>">
                            </div>
							<?php
							if ( empty( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
									<?php esc_html_e( "Stripe is disabled.", WL_MIM_DOMAIN ); ?><?php esc_html_e( "Please select a valid currency.", WL_MIM_DOMAIN ); ?>
                                </div>
							<?php
							} elseif ( ! WL_MIM_PaymentHelper::stripe_support_currency_provided( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
									<?php esc_html_e( "Stripe is disabled.", WL_MIM_DOMAIN ); ?>
									<?php esc_html_e( "Currency", WL_MIM_DOMAIN ); ?>:
                                    <strong><?php echo esc_html( $payment['payment_currency'] ); ?></strong>&nbsp;<?php esc_html_e( "is not supported.", WL_MIM_DOMAIN ); ?>
                                </div>
							<?php
							}
							?>
                        </div>

                        <div class="row wlim-setting-payment_razorpay" x-show="payment_method === 'razorpay'">
                            <div class="form-check col-sm-12 mb-3">
                                <input name="payment_razorpay_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-payment_razorpay_enable" <?php checked( $payment_razorpay['enable'], true ); ?>>
                                <label for="wlim-setting-payment_razorpay_enable" class="form-check-label">
									<?php esc_html_e( 'Enable Razorpay', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_razorpay_key" class="col-form-label">
									<?php esc_html_e( 'Razorpay Key', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="payment_razorpay_key" type="text" class="form-control" id="wlim-setting-payment_razorpay_key" placeholder="<?php esc_html_e( "Razorpay Key", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $payment_razorpay['key'] ); ?>">
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_razorpay_secret" class="col-form-label">
									<?php esc_html_e( 'Razorpay Secret', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="payment_razorpay_secret" type="text" class="form-control" id="wlim-setting-payment_razorpay_secret" placeholder="<?php esc_html_e( "Razorpay Secret", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $payment_razorpay['secret'] ); ?>">
                            </div>
							<?php
							if ( empty( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
									<?php esc_html_e( "Razorpay is disabled.", WL_MIM_DOMAIN ); ?><?php esc_html_e( "Please select a valid currency.", WL_MIM_DOMAIN ); ?>
                                </div>
							<?php
							} elseif ( ! WL_MIM_PaymentHelper::razorpay_support_currency_provided( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
									<?php esc_html_e( "Razorpay is disabled.", WL_MIM_DOMAIN ); ?>
									<?php esc_html_e( "Currency", WL_MIM_DOMAIN ); ?>:
                                    <strong><?php echo esc_html( $payment['payment_currency'] ); ?></strong>&nbsp;<?php esc_html_e( "is not supported.", WL_MIM_DOMAIN ); ?>
                                </div>
							<?php
							}
							?>
                        </div>

                        <div class="row wlim-setting-payment_instamojo" x-show="payment_method === 'instamojo'">
                            <div class="form-check col-sm-12 mb-3">
                                <input name="payment_instamojo_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-payment_instamojo_enable" <?php checked( $payment_instamojo['enable'], true ); ?>>
                                <label for="wlim-setting-payment_instamojo_enable" class="form-check-label">
									<?php esc_html_e( 'Enable Instamojo', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_instamojo_mode" class="col-form-label"><?php esc_html_e( "PayPal Mode", WL_MIM_DOMAIN ); ?>:</label>
                                <select name="payment_instamojo_mode" class="form-control" id="wlim-setting-payment_instamojo_mode">
                                    <option value="test" <?php selected( $payment_instamojo['mode'], 'test' ); ?>><?php esc_html_e( "Test", WL_MIM_DOMAIN ); ?></option>
                                    <option value="live" <?php selected( $payment_instamojo['mode'], 'live' ); ?>><?php esc_html_e( "Live", WL_MIM_DOMAIN ); ?></option>
                                </select>
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_instamojo_client_id" class="col-form-label">
									<?php esc_html_e( 'Instamojo Client ID', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="payment_instamojo_client_id" type="text" class="form-control" id="wlim-setting-payment_instamojo_client_id" placeholder="<?php esc_html_e( "Instamojo Client ID", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $payment_instamojo['client_id'] ); ?>">
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_instamojo_client_secret" class="col-form-label">
									<?php esc_html_e( 'Instamojo Client Secret', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="payment_instamojo_client_secret" type="text" class="form-control" id="wlim-setting-payment_instamojo_client_secret" placeholder="<?php esc_html_e( "Instamojo Client Secret", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $payment_instamojo['client_secret'] ); ?>">
                            </div>
							<?php
							if ( empty( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
									<?php esc_html_e( "Instamojo is disabled.", WL_MIM_DOMAIN ); ?><?php esc_html_e( "Please select a valid currency.", WL_MIM_DOMAIN ); ?>
                                </div>
							<?php
							} elseif ( ! WL_MIM_PaymentHelper::instamojo_support_currency_provided( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
									<?php esc_html_e( "Instamojo is disabled.", WL_MIM_DOMAIN ); ?>
									<?php esc_html_e( "Currency", WL_MIM_DOMAIN ); ?>:
                                    <strong><?php echo esc_html( $payment['payment_currency'] ); ?></strong>&nbsp;<?php esc_html_e( "is not supported.", WL_MIM_DOMAIN ); ?>
                                </div>
							<?php
							}
							?>
                        </div>

                        <div class="row wlim-setting-payment_paystack" x-show="payment_method === 'paystack'">
                            <div class="form-check col-sm-12 mb-3">
                                <input name="payment_paystack_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-payment_paystack_enable" <?php checked( $payment_paystack['enable'], true ); ?>>
                                <label for="wlim-setting-payment_paystack_enable" class="form-check-label">
                                    <?php esc_html_e( 'Enable Paystack', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_paystack_key" class="col-form-label">
                                    <?php esc_html_e( 'Paystack Key', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="payment_paystack_key" type="text" class="form-control" id="wlim-setting-payment_paystack_key" placeholder="<?php esc_html_e( "Paystack Key", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $payment_paystack['key'] ); ?>">
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-payment_paystack_secret" class="col-form-label">
                                    <?php esc_html_e( 'Paystack Secret', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="payment_paystack_secret" type="text" class="form-control" id="wlim-setting-payment_paystack_secret" placeholder="<?php esc_html_e( "Paystack Secret", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $payment_paystack['secret'] ); ?>">
                            </div>
                            <?php
                            if ( empty( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
                                    <?php esc_html_e( "Paystack is disabled.", WL_MIM_DOMAIN ); ?>&nbsp;<?php esc_html_e( "Please select a valid currency.", WL_MIM_DOMAIN ); ?>
                                </div>
                            <?php
                            } elseif ( ! WL_MIM_PaymentHelper::paystack_support_currency_provided( $payment['payment_currency'] ) ) { ?>
                                <div class="text-danger col-sm-12 mt-1 mb-3">
                                    <?php esc_html_e( "Paystack is disabled.", WL_MIM_DOMAIN ); ?>
                                    <?php esc_html_e( "Currency", WL_MIM_DOMAIN ); ?>:
                                    <strong><?php echo esc_html( $payment['payment_currency'] ); ?></strong>&nbsp;<?php esc_html_e( "is not supported.", WL_MIM_DOMAIN ); ?>
                                </div>
                            <?php
                            }
                            ?>
                        </div>

                        <button type="submit" class="btn btn-primary save-payment-settings-submit"><?php esc_html_e( 'Save Settings', WL_MIM_DOMAIN ); ?></button>
                    </form>
                </div>
                <div class="tab-pane fade" id="list-email" role="tabpanel" aria-labelledby="list-email-list">
                    <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-email-settings-form">
						<?php $nonce = wp_create_nonce( 'save-email-settings' ); ?>
                        <input type="hidden" name="save-email-settings" value="<?php echo esc_attr( $nonce ); ?>">
                        <input type="hidden" name="action" value="wl-mim-save-email-settings">

                        <div class="row">

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-email_host" class="col-form-label">
									<?php esc_html_e( 'Host', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="email_host" type="text" class="form-control" id="wlim-setting-email_host" placeholder="<?php esc_html_e( "Host", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $email['email_host'] ); ?>">
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-email_username" class="col-form-label">
									<?php esc_html_e( 'Username', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="email_username" type="text" class="form-control" id="wlim-setting-email_username" placeholder="<?php esc_html_e( "Username", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $email['email_username'] ); ?>">
                            </div>

                            <div class="form-group col-xs-12 col-sm-6">
                                <label for="wlim-setting-email_password" class="col-form-label">
									<?php esc_html_e( 'Password', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="email_password" type="password" class="form-control" id="wlim-setting-email_password" placeholder="<?php esc_html_e( "Password", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $email['email_password'] ); ?>">
                            </div>

                            <div class="form-group col-xs-12 col-sm-6">
                                <label for="wlim-setting-email_encryption" class="col-form-label">
									<?php esc_html_e( 'Encryption', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="email_encryption" type="text" class="form-control" id="wlim-setting-email_encryption" placeholder="<?php esc_html_e( "Encryption", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $email['email_encryption'] ); ?>">
                            </div>

                            <div class="form-group col-xs-12 col-sm-6">
                                <label for="wlim-setting-email_port" class="col-form-label">
									<?php esc_html_e( 'Port', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="email_port" type="text" class="form-control" id="wlim-setting-email_port" placeholder="<?php esc_html_e( "Port", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $email['email_port'] ); ?>">
                            </div>

                            <div class="form-group col-xs-12 col-sm-6">
                                <label for="wlim-setting-email_from" class="col-form-label">
									<?php esc_html_e( 'From', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="email_from" type="text" class="form-control" id="wlim-setting-email_from" placeholder="<?php esc_html_e( "From", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $email['email_from'] ); ?>">
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary save-email-settings-submit"><?php esc_html_e( 'Save Settings', WL_MIM_DOMAIN ); ?></button>
                    </form>
                </div>
                <div class="tab-pane fade" id="list-sms" role="tabpanel" aria-labelledby="list-sms-list">
                    <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-sms-settings-form">
						<?php $nonce = wp_create_nonce( 'save-sms-settings' ); ?>
                        <input type="hidden" name="save-sms-settings" value="<?php echo esc_attr( $nonce ); ?>">
                        <input type="hidden" name="action" value="wl-mim-save-sms-settings">

                        <div class="row">

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-sms_admin_number" class="col-form-label">
									<?php esc_html_e( 'Admin Mobile Number', WL_MIM_DOMAIN ); ?>:
                                </label>
                                <input name="sms_admin_number" type="text" class="form-control" id="wlim-setting-sms_admin_number" placeholder="<?php esc_html_e( "Admin mobile number to receive enquiries", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms['sms_admin_number'] ); ?>">
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-sms_provider" class="col-form-label">
                                    <strong><?php esc_html_e( 'SMS Provider', WL_MIM_DOMAIN ); ?>:</strong>
                                </label>
                                <select class="form-control" name="sms_provider" id="wlim-setting-sms_provider">
									<?php
									foreach ( WL_MIM_Helper::get_sms_providers() as $key => $value ) { ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $sms['sms_provider'], $key ); ?>><?php echo esc_html( $value ); ?></option>
									<?php
									} ?>
                                </select>
                            </div>

                            <div class="wlmim-sms-nexmo col-sm-12">
                                <div class="alert alert-danger mt-2">
                                    <h6><?php esc_html_e( 'The Nexmo SMS API requires phone numbers in E.164 format.', WL_MIM_DOMAIN ); ?></h6>
                                </div>
                                <div class="form-group">
                                    <label for="wlim-setting-sms_nexmo_api_key" class="col-form-label">
										<?php esc_html_e( 'SMS Nexmo API Key', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_nexmo_api_key" type="text" class="form-control" id="wlim-setting-sms_nexmo_api_key" placeholder="<?php esc_html_e( "SMS Nexmo API Key", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_nexmo['api_key'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_nexmo_api_secret" class="col-form-label">
										<?php esc_html_e( 'SMS Nexmo API Secret', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_nexmo_api_secret" type="text" class="form-control" id="wlim-setting-sms_nexmo_api_secret" placeholder="<?php esc_html_e( "SMS Nexmo API Secret", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_nexmo['api_secret'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_nexmo_from" class="col-form-label">
										<?php esc_html_e( 'From', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_nexmo_from" type="text" class="form-control" id="wlim-setting-sms_nexmo_from" placeholder="<?php esc_html_e( "From", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_nexmo['from'] ); ?>">
                                </div>
                            </div>

                            <div class="wlmim-sms-striker col-sm-12">
                                <div class="alert alert-danger mt-2">
                                    <h6><?php esc_html_e( 'Only Indian phone numbers are supported with SMS Striker.', WL_MIM_DOMAIN ); ?></h6>
                                    <a class="font-weight-bold" target="_blank" href="https://intechnosoftware.com/bulk-sms-service/">
                                        <?php esc_html_e( 'Click for SMS Package Features and Pricing', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </div>
                                <div class="form-group">
                                    <label for="wlim-setting-sms_striker_username" class="col-form-label">
										<?php esc_html_e( 'SMS Striker Username', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_striker_username" type="text" class="form-control" id="wlim-setting-sms_striker_username" placeholder="<?php esc_html_e( "SMS Striker Username", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_striker['username'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_striker_password" class="col-form-label">
										<?php esc_html_e( 'SMS Striker Password', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_striker_password" type="password" class="form-control" id="wlim-setting-sms_striker_password" placeholder="<?php esc_html_e( "SMS Striker Password", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_striker['password'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_striker_sender_id" class="col-form-label">
										<?php esc_html_e( 'SMS Striker Sender ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_striker_sender_id" type="text" class="form-control" id="wlim-setting-sms_striker_sender_id" placeholder="<?php esc_html_e( "SMS Striker Sender ID", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_striker['sender_id'] ); ?>">
                                </div>
                            </div>

                            <div class="wlmim-sms-pointsms col-sm-12">
                                <div class="alert alert-danger mt-2">
                                    <h6><?php esc_html_e( 'Only Indian phone numbers are supported with Intechno Point.', WL_MIM_DOMAIN ); ?></h6>
                                    <a class="font-weight-bold" target="_blank" href="https://intechnosoftware.com/bulk-sms-service/">
                                        <?php esc_html_e( 'Click for SMS Package Features and Pricing', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </div>
                                <div class="form-group">
                                    <label for="wlim-setting-sms_pointsms_username" class="col-form-label">
										<?php esc_html_e( 'Intechno Point Username', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_pointsms_username" type="text" class="form-control" id="wlim-setting-sms_pointsms_username" placeholder="<?php esc_html_e( "Intechno Point Username", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_pointsms['username'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_pointsms_password" class="col-form-label">
										<?php esc_html_e( 'Intechno Point Password', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_pointsms_password" type="password" class="form-control" id="wlim-setting-sms_pointsms_password" placeholder="<?php esc_html_e( "Intechno Point Password", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_pointsms['password'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_pointsms_sender_id" class="col-form-label">
										<?php esc_html_e( 'Intechno Point Sender ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_pointsms_sender_id" type="text" class="form-control" id="wlim-setting-sms_pointsms_sender_id" placeholder="<?php esc_html_e( "Intechno Point Sender ID", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_pointsms['sender_id'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_pointsms_channel" class="col-form-label">
										<?php esc_html_e( 'channel', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_pointsms_channel" type="text" class="form-control" id="wlim-setting-sms_pointsms_channel" placeholder="<?php esc_html_e( "channel", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_pointsms['channel'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_pointsms_route" class="col-form-label">
										<?php esc_html_e( 'route', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_pointsms_route" type="text" class="form-control" id="wlim-setting-sms_pointsms_route" placeholder="<?php esc_html_e( "route", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_pointsms['route'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_pointsms_peid" class="col-form-label">
										<?php esc_html_e( 'peid', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_pointsms_peid" type="text" class="form-control" id="wlim-setting-sms_pointsms_peid" placeholder="<?php esc_html_e( "peid", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_pointsms['peid'] ); ?>">
                                </div>
                            </div>

                            <div class="wlmim-sms-auurumdigital col-sm-12">
                                <div class="alert alert-danger mt-2">
                                    <h6><?php esc_html_e( 'Only Indian phone numbers are supported with auurumdigital.', WL_MIM_DOMAIN ); ?></h6>
                                    <a class="font-weight-bold" target="_blank" href="https://intechnosoftware.com/bulk-sms-service/">
                                        <?php esc_html_e( 'Click for SMS Package Features and Pricing', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </div>
                                <div class="form-group">
                                    <label for="wlim-setting-sms_auurumdigital_username" class="col-form-label">
                                        <?php esc_html_e( 'auurumdigital Username', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_auurumdigital_username" type="text" class="form-control" id="wlim-setting-sms_auurumdigital_username" placeholder="<?php esc_html_e( "auurumdigital Username", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_auurumdigital['username'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_auurumdigital_password" class="col-form-label">
                                        <?php esc_html_e( 'auurumdigital Password', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_auurumdigital_password" type="password" class="form-control" id="wlim-setting-sms_auurumdigital_password" placeholder="<?php esc_html_e( "auurumdigital Password", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_auurumdigital['password'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_auurumdigital_sender_id" class="col-form-label">
                                        <?php esc_html_e( 'auurumdigital Sender ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_auurumdigital_sender_id" type="text" class="form-control" id="wlim-setting-sms_auurumdigital_sender_id" placeholder="<?php esc_html_e( "auurumdigital Sender ID", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_auurumdigital['sender_id'] ); ?>">
                                </div>

                                 <div class="form-group">
                                    <label for="wlim-setting-sms_auurumdigital_channel" class="col-form-label">
                                        <?php esc_html_e( 'auurumdigital Channel', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_auurumdigital_channel" type="text" class="form-control" id="wlim-setting-sms_auurumdigital_channel" placeholder="<?php esc_html_e( "auurumdigital channel", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_auurumdigital['channel'] ); ?>">
                                </div>

                                 <div class="form-group">
                                    <label for="wlim-setting-sms_auurumdigital_route" class="col-form-label">
                                        <?php esc_html_e( 'auurumdigital route', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_auurumdigital_route" type="text" class="form-control" id="wlim-setting-sms_auurumdigital_route" placeholder="<?php esc_html_e( "auurumdigital route", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_auurumdigital['route'] ); ?>">
                                </div>
                            </div>

                            <div class="wlmim-sms-msgclub col-sm-12">
                                <div class="alert alert-danger mt-2">
                                    <h6><?php esc_html_e( 'Only Indian phone numbers are supported with Intechno Msg.', WL_MIM_DOMAIN ); ?></h6>
                                    <a class="font-weight-bold" target="_blank" href="https://intechnosoftware.com/bulk-sms-service/">
                                        <?php esc_html_e( 'Click for SMS Package Features and Pricing', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </div>
                                <div class="form-group">
                                    <label for="wlim-setting-sms_msgclub_auth_key" class="col-form-label">
                                        <?php esc_html_e( 'Intechno Msg Auth Key', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_msgclub_auth_key" type="text" class="form-control" id="wlim-setting-sms_msgclub_auth_key" placeholder="<?php esc_html_e( "Intechno Msg Auth Key", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_msgclub['auth_key'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_msgclub_sender_id" class="col-form-label">
                                        <?php esc_html_e( 'Intechno Msg Sender ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_msgclub_sender_id" type="text" class="form-control" id="wlim-setting-sms_msgclub_sender_id" placeholder="<?php esc_html_e( "Intechno Msg Sender ID", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_msgclub['sender_id'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_msgclub_route_id" class="col-form-label">
                                        <?php esc_html_e( 'Intechno Msg Route ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_msgclub_route_id" type="text" class="form-control" id="wlim-setting-sms_msgclub_route_id" placeholder="<?php esc_html_e( "Intechno Msg Route ID", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_msgclub['route_id'] ); ?>">
                                </div>   

                                <div class="form-group">
                                    <label for="wlim-setting-sms_msgclub_peid" class="col-form-label">
                                        <?php esc_html_e( 'PEID or Entity id should be of 19 digit', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_msgclub_peid" type="text" class="form-control" id="wlim-setting-sms_msgclub_peid" placeholder="<?php esc_html_e( "PEID or Entity id should be of 19 digit", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_msgclub['peid'] ); ?>">
                                </div>       

                                <div class="form-group">
                                    <label for="wlim-setting-sms_msgclub_tel_id" class="col-form-label">
                                        <?php esc_html_e( 'Telemarketer id', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_msgclub_tel_id" type="text" class="form-control" id="wlim-setting-sms_msgclub_tel_id" placeholder="<?php esc_html_e( "Telemarketer id", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_msgclub['tel_id'] ); ?>">
                                </div>                              

                                <div class="form-group">
                                    <label for="wlim-setting-sms_msgclub_content_type" class="col-form-label">
                                        <?php esc_html_e( 'Intechno Msg SMS Content Type', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_msgclub_content_type" type="text" class="form-control" id="wlim-setting-sms_msgclub_content_type" placeholder="<?php esc_html_e( "Intechno Msg SMS Content Type", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_msgclub['content_type'] ); ?>">
                                </div>
                            </div>

                            <div class="wlmim-sms-textlocal col-sm-12">
                                <div class="form-group">
                                    <label for="wlim-setting-sms_textlocal_api_key" class="col-form-label">
                                        <?php esc_html_e( 'Textlocal API Key', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_textlocal_api_key" type="text" class="form-control" id="wlim-setting-sms_textlocal_api_key" placeholder="<?php esc_html_e( "Textlocal API Key", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_textlocal['api_key'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_textlocal_sender" class="col-form-label">
                                        <?php esc_html_e( 'Textlocal Sender ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_textlocal_sender" type="text" class="form-control" id="wlim-setting-sms_textlocal_sender" placeholder="<?php esc_html_e( "Textlocal Sender ID", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_textlocal['sender'] ); ?>">
                                </div>
                            </div>

                            <div class="wlmim-sms-ebulksms col-sm-12">
                                <div class="form-group">
                                    <label for="wlim-setting-sms_ebulksms_username" class="col-form-label">
                                        <?php esc_html_e( 'EBulkSMS API Key', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_ebulksms_username" type="text" class="form-control" id="wlim-setting-sms_ebulksms_username" placeholder="<?php esc_html_e( "EBulkSMS Username", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_ebulksms['username'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_ebulksms_api_key" class="col-form-label">
                                        <?php esc_html_e( 'EBulkSMS API Key', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_ebulksms_api_key" type="text" class="form-control" id="wlim-setting-sms_ebulksms_api_key" placeholder="<?php esc_html_e( "EBulkSMS API Key", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_ebulksms['api_key'] ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_ebulksms_sender" class="col-form-label">
                                        <?php esc_html_e( 'EBulkSMS Sender ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="sms_ebulksms_sender" type="text" class="form-control" id="wlim-setting-sms_ebulksms_sender" placeholder="<?php esc_html_e( "EBulkSMS Sender ID", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $sms_ebulksms['sender'] ); ?>">
                                </div>
                            </div>

                            <div class="form-group col-sm-12">
                                <label for="wlim-setting-sms_template" class="col-form-label">
                                    <strong><?php esc_html_e( 'SMS Template', WL_MIM_DOMAIN ); ?>:</strong>
                                </label>
                                <select class="form-control" name="sms_template" id="wlim-setting-sms_template">
									<?php
									foreach ( WL_MIM_Helper::get_sms_templates() as $key => $value ) { ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
									<?php
									} ?>
                                </select>
                            </div>

                            <div class="wlmim-sms-enquiry_received col-sm-12">
                                <div class="form-check pl-0">
                                    <input name="sms_enquiry_received_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-sms_enquiry_received_enable" <?php checked( $sms_template_enquiry_received['enable'], true, true ); ?>>
                                    <label class="form-check-label" for="wlim-setting-sms_enquiry_received_enable">
										<?php esc_html_e( 'Enable?', WL_MIM_DOMAIN ); ?>
                                    </label>
                                </div>

                                <div class="alert alert-info mt-2">
                                    <h6><?php esc_html_e( 'You can use the following vairable(s)', WL_MIM_DOMAIN ); ?>:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <strong>[COURSE_NAME]</strong> - <?php esc_html_e( "to replace with course name", WL_MIM_DOMAIN ); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>[COURSE_CODE]</strong> - <?php esc_html_e( "to replace with course code", WL_MIM_DOMAIN ); ?>
                                        </li>
                                    </ul>
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_enquiry_template_id" class="col-form-label">
										<?php esc_html_e( 'Template ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input type="text" name="sms_enquiry_template_id"  placeholder="Template ID" value="<?php echo $sms_template_enquiry_received['template_id']; ?>"><br>
                                    
                                    <label for="wlim-setting-sms_enquiry_received_message" class="col-form-label">
										<?php esc_html_e( 'Message when enquiry is received', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <textarea name="sms_enquiry_received_message" class="form-control" id="wlim-setting-sms_enquiry_received_message" rows="4" placeholder="<?php esc_html_e( "Message when enquiry is received", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $sms_template_enquiry_received['message'] ); ?></textarea>
                                </div>
                            </div>

                            <div class="wlmim-sms-enquiry_received_to_admin col-sm-12">
                                <div class="form-check pl-0">
                                    <input name="sms_enquiry_received_to_admin_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-sms_enquiry_received_to_admin_enable" <?php checked( $sms_template_enquiry_received_to_admin['enable'], true, true ); ?>>
                                    <label class="form-check-label" for="wlim-setting-sms_enquiry_received_to_admin_enable">
										<?php esc_html_e( 'Enable?', WL_MIM_DOMAIN ); ?>
                                    </label>
                                </div>

                                <div class="alert alert-info mt-2">
                                    <h6><?php esc_html_e( 'You can use the following vairable(s)', WL_MIM_DOMAIN ); ?>:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <strong>[COURSE_NAME]</strong> - <?php esc_html_e( "to replace with course name", WL_MIM_DOMAIN ); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>[COURSE_CODE]</strong> - <?php esc_html_e( "to replace with course code", WL_MIM_DOMAIN ); ?>
                                        </li>
                                    </ul>
                                </div>

                                <div class="form-group">
                                <label for="wlim-setting-sms_template_id" class="col-form-label">
										<?php esc_html_e( 'Template ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input type="text" name="sms_received_to_admin_template_id"  placeholder="Template ID" value="<?php echo $sms_template_enquiry_received_to_admin['template_id']; ?>"><br>
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_enquiry_received_to_admin_message" class="col-form-label">
										<?php esc_html_e( 'Message to admin when enquiry is received', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <textarea name="sms_enquiry_received_to_admin_message" class="form-control" id="wlim-setting-sms_enquiry_received_to_admin_message" rows="4" placeholder="<?php esc_html_e( "Message to admin when enquiry is received", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $sms_template_enquiry_received_to_admin['message'] ); ?></textarea>
                                </div>
                            </div>

                            <div class="wlmim-sms-student_registered col-sm-12">
                                <div class="form-check pl-0">
                                    <input name="sms_student_registered_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-sms_student_registered_enable" <?php checked( $sms_template_student_registered['enable'], true, true ); ?>>
                                    <label class="form-check-label" for="wlim-setting-sms_student_registered_enable">
										<?php esc_html_e( 'Enable?', WL_MIM_DOMAIN ); ?>
                                    </label>
                                </div>

                                <div class="alert alert-info mt-2">
                                    <h6><?php esc_html_e( 'You can use the following vairable(s)', WL_MIM_DOMAIN ); ?>:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <strong>[ENROLLMENT_ID]</strong> - <?php esc_html_e( "to replace with Enrollment ID", WL_MIM_DOMAIN ); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>[USERNAME]</strong> - <?php esc_html_e( "to replace with student's username", WL_MIM_DOMAIN ); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>[PASSWORD]</strong> - <?php esc_html_e( "to replace with student's password", WL_MIM_DOMAIN ); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>[LOGIN_URL]</strong> - <?php esc_html_e( "to replace with login URL", WL_MIM_DOMAIN ); ?>
                                        </li>
                                    </ul>
                                </div>

                                <div class="form-group">
                                <label for="wlim-setting-sms_template_id" class="col-form-label">
										<?php esc_html_e( 'Template ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input type="text" name="sms_registered_template_id"  placeholder="Template ID" value="<?php echo $sms_template_student_registered['template_id']; ?>"><br>
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_student_registered_message" class="col-form-label">
										<?php esc_html_e( 'Message on student registration', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <textarea name="sms_student_registered_message" class="form-control" id="wlim-setting-sms_student_registered_message" rows="4" placeholder="<?php esc_html_e( "Message on student registration", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $sms_template_student_registered['message'] ); ?></textarea>
                                </div>
                            </div>

                            <div class="wlmim-sms-fees_submitted col-sm-12">
                                <div class="form-check pl-0">
                                    <input name="sms_fees_submitted_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-sms_fees_submitted_enable" <?php checked( $sms_template_fees_submitted['enable'], true, true ); ?>>
                                    <label class="form-check-label" for="wlim-setting-sms_fees_submitted_enable">
										<?php esc_html_e( 'Enable?', WL_MIM_DOMAIN ); ?>
                                    </label>
                                </div>

                                <div class="alert alert-info mt-2">
                                    <h6><?php esc_html_e( 'You can use the following vairable(s)', WL_MIM_DOMAIN ); ?>:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <strong>[FEES]</strong> - <?php esc_html_e( "to replace with fees type and amount", WL_MIM_DOMAIN ); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>[DATE]</strong> - <?php esc_html_e( "to replace with fees submission date", WL_MIM_DOMAIN ); ?>
                                        </li>
                                    </ul>
                                </div>

                                <div class="form-group">
                                <label for="wlim-setting-sms_template_id" class="col-form-label">
										<?php esc_html_e( 'Template ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input type="text" name="sms_fees_submitted_template_id"  placeholder="Template ID" value="<?php echo $sms_template_fees_submitted['template_id']; ?>"><br>
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_fees_submitted_message" class="col-form-label">
										<?php esc_html_e( 'Message on fees submission', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <textarea name="sms_fees_submitted_message" class="form-control" id="wlim-setting-sms_fees_submitted_message" rows="4" placeholder="<?php esc_html_e( "Message on fees submission", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $sms_template_fees_submitted['message'] ); ?></textarea>
                                </div>
                            </div>

                            <div class="wlmim-sms-student_birthday col-sm-12">
                                <div class="form-check pl-0">
                                    <input name="sms_student_birthday_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-sms_student_birthday_enable" <?php checked( $sms_template_student_birthday['enable'], true, true ); ?>>
                                    <label class="form-check-label" for="wlim-setting-sms_student_birthday_enable">
                                        <?php esc_html_e( 'Enable?', WL_MIM_DOMAIN ); ?>
                                    </label>
                                </div>

                                <div class="alert alert-info mt-2">
                                    <h6><?php esc_html_e( 'You can use the following vairable(s)', WL_MIM_DOMAIN ); ?>:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <strong>[FIRST_NAME]</strong> - <?php esc_html_e( "to replace with student's first name", WL_MIM_DOMAIN ); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>[LAST_NAME]</strong> - <?php esc_html_e( "to replace with student's last name", WL_MIM_DOMAIN ); ?>
                                        </li>
                                    </ul>
                                </div>

                                <div class="form-group">
                                <label for="wlim-setting-sms_template_id" class="col-form-label">
										<?php esc_html_e( 'Template ID', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input type="text" name="sms_birthday_template_id"  placeholder="Template ID" value="<?php echo $sms_template_student_birthday['template_id']; ?>"><br>
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-sms_student_birthday_message" class="col-form-label">
                                        <?php esc_html_e( 'Message on student birthday', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <textarea name="sms_student_birthday_message" class="form-control" id="wlim-setting-sms_student_birthday_message" rows="4" placeholder="<?php esc_html_e( "Message on student birthday", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $sms_template_student_birthday['message'] ); ?></textarea>
                                </div>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary save-sms-settings-submit"><?php esc_html_e( 'Save Settings', WL_MIM_DOMAIN ); ?></button>
                    </form>
                </div>
                <div class="tab-pane fade" id="list-custom-fields" role="tabpanel" aria-labelledby="list-custom-fields-list">
                    <div class="row">
                        <div class="card col">
                            <div class="card-header bg-primary text-white">
                                <!-- card header content -->
                                <div class="row">
                                    <div class="col-md-7 col-xs-12">
                                        <div class="h5"><?php esc_html_e( 'Manage Custom Fields', WL_MIM_DOMAIN ); ?></div>
                                    </div>
                                    <div class="col-md-5 col-xs-12">
                                        <div class="btn-group float-right" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-light add-custom-field" data-toggle="modal" data-target="#add-custom-field" data-backdrop="static" data-keyboard="false">
                                                <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Custom Field', WL_MIM_DOMAIN ); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- end - card header content -->
                            </div>
                            <div class="card-body">
                                <!-- card body content -->
                                <div class="row">
                                    <div class="col">
                                        <table class="table table-hover table-striped table-bordered w-100" id="custom-field-table">
                                            <thead>
                                                <tr>
                                                    <th scope="col"><?php esc_html_e( 'Field Name', WL_MIM_DOMAIN ); ?></th>
                                                    <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                                    <th scope="col"><?php esc_html_e( 'Edit', WL_MIM_DOMAIN ); ?></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <!-- end - card body content -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-admit-card" role="tabpanel" aria-labelledby="list-admit-card-list">
                    <div class="row">
                        <div class="card col">
                            <div class="card-header bg-primary text-white">
                                <!-- card header content -->
                                <div class="row">
                                    <div class="col-md-12 col-xs-12">
                                        <div class="h5"><?php esc_html_e( 'Admit Card', WL_MIM_DOMAIN ); ?></div>
                                    </div>
                                </div>
                                <!-- end - card header content -->
                            </div>
                            <div class="card-body">
                                <!-- card body content -->
                                <div class="row">
                                    <div class="col">
                                        <div class="text-center">
                                            <?php esc_html_e( 'To Display Admit Card Form on Front End, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                            <div class="col-12 justify-content-center align-items-center">
                                                <span class="col-6">
                                                    <strong id="wl_im_admit_card_form_shortcode">[institute_admit_card id=<?php echo esc_html( $institute_id ); ?>]</strong>
                                                </span>
                                                <span class="col-6">
                                                    <button id="wl_im_admit_card_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-admit-card-settings-form">
                                            <?php $nonce = wp_create_nonce( 'save-admit-card-settings' ); ?>
                                            <input type="hidden" name="save-admit-card-settings" value="<?php echo esc_attr( $nonce ); ?>">
                                            <input type="hidden" name="action" value="wl-mim-save-admit-card-settings">

                                            <div class="form-check col-sm-12 mb-3">
                                                <input name="admit_card_dob_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-admit_card_dob_enable" <?php checked( $admit_card_dob_enable, true ); ?>>
                                                <label for="wlim-setting-admit_card_dob_enable" class="form-check-label">
                                                    <?php esc_html_e( 'Requrie Date of Birth', WL_MIM_DOMAIN ); ?>
                                                </label>
                                            </div>

                                            <div class="form-group col-sm-12">
                                                <label for="wlim-setting-admit_card_signature" class="col-form-label"><?php esc_html_e( 'Upload Signature', WL_MIM_DOMAIN ); ?>:</label><br>
                                                <?php if ( ! empty ( $admit_card['sign'] ) ) { ?>
                                                    <img src="<?php echo wp_get_attachment_url( $admit_card['sign'] ); ?>" class="img-responsive wlim-institute_sign">
                                                <?php } ?>
                                                <input name="admit_card_signature" type="file" id="wlim-setting-admit_card_signature">
                                            </div>

                                            <div class="form-check col-sm-12 mb-3">
                                                <input name="admit_card_signature_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-admit_card_signature_enable" <?php checked( $admit_card['sign_enable'], '1', true ); ?>>
                                                <label class="form-check-label" for="wlim-setting-admit_card_signature_enable">
                                                    <?php esc_html_e( 'Enable Signature Image?', WL_MIM_DOMAIN ); ?>
                                                </label>
                                            </div>
                                            <button type="submit" class="ml-3 btn btn-primary save-admit-card-settings-submit"><?php esc_html_e( 'Save Settings', WL_MIM_DOMAIN ); ?></button>
                                        </form>
                                    </div>
                                </div>
                                <!-- end - card body content -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-id-card" role="tabpanel" aria-labelledby="list-id-card-list">
                    <div class="row">
                        <div class="card col">
                            <div class="card-header bg-primary text-white">
                                <!-- card header content -->
                                <div class="row">
                                    <div class="col-md-12 col-xs-12">
                                        <div class="h5"><?php esc_html_e( 'ID Card', WL_MIM_DOMAIN ); ?></div>
                                    </div>
                                </div>
                                <!-- end - card header content -->
                            </div>
                            <div class="card-body">
                                <!-- card body content -->
                                <div class="row">
                                    <div class="col">
                                        <div class="text-center">
                                            <?php esc_html_e( 'To Display ID Card Form on Front End, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                            <div class="col-12 justify-content-center align-items-center">
                                                <span class="col-6">
                                                    <strong id="wl_im_id_card_form_shortcode">[institute_id_card id=<?php echo esc_html( $institute_id ); ?>]</strong>
                                                </span>
                                                <span class="col-6">
                                                    <button id="wl_im_id_card_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-id-card-settings-form">
                                            <?php $nonce = wp_create_nonce( 'save-id-card-settings' ); ?>
                                            <input type="hidden" name="save-id-card-settings" value="<?php echo esc_attr( $nonce ); ?>">
                                            <input type="hidden" name="action" value="wl-mim-save-id-card-settings">

                                            <div class="form-check col-sm-12 mb-3">
                                                <input name="id_card_dob_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-id_card_dob_enable" <?php checked( $id_card_dob_enable, true ); ?>>
                                                <label for="wlim-setting-id_card_dob_enable" class="form-check-label">
                                                    <?php esc_html_e( 'Requrie Date of Birth', WL_MIM_DOMAIN ); ?>
                                                </label>
                                            </div>

                                            <div class="form-group col-sm-12">
                                                <label for="wlim-setting-id_card_signature" class="col-form-label"><?php esc_html_e( 'Upload Signature', WL_MIM_DOMAIN ); ?>:</label><br>
                                                <?php if ( ! empty ( $id_card['sign'] ) ) { ?>
                                                    <img src="<?php echo wp_get_attachment_url( $id_card['sign'] ); ?>" class="img-responsive wlim-institute_sign">
                                                <?php } ?>
                                                <input name="id_card_signature" type="file" id="wlim-setting-id_card_signature">
                                            </div>

                                            <div class="form-check col-sm-12 mb-3">
                                                <input name="id_card_signature_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-id_card_signature_enable" <?php checked( $id_card['sign_enable'], '1', true ); ?>>
                                                <label class="form-check-label" for="wlim-setting-id_card_signature_enable">
                                                    <?php esc_html_e( 'Enable Signature Image?', WL_MIM_DOMAIN ); ?>
                                                </label>
                                            </div>

                                            <button type="submit" class="ml-3 btn btn-primary save-id-card-settings-submit"><?php esc_html_e( 'Save Settings', WL_MIM_DOMAIN ); ?></button>
                                        </form>
                                    </div>
                                </div>
                                <!-- end - card body content -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-certificate" role="tabpanel" aria-labelledby="list-certificate-list">
                    <div class="row">
                        <div class="card col">
                            <div class="card-header bg-primary text-white">
                                <!-- card header content -->
                                <div class="row">
                                    <div class="col-md-12 col-xs-12">
                                        <div class="h5"><?php esc_html_e( 'Certificate', WL_MIM_DOMAIN ); ?></div>
                                    </div>
                                </div>
                                <!-- end - card header content -->
                            </div>
                            <div class="card-body">
                                <!-- card body content -->
                                <div class="row">
                                    <div class="col">
                                        <div class="text-center">
                                            <?php esc_html_e( 'To Display Certificate Form on Front End, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                            <div class="col-12 justify-content-center align-items-center">
                                                <span class="col-6">
                                                    <strong id="wl_im_certificate_form_shortcode">[institute_certificate id=<?php echo esc_html( $institute_id ); ?>]</strong>
                                                </span>
                                                <span class="col-6">
                                                    <button id="wl_im_certificate_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-certificate-settings-form">
                                            <?php $nonce = wp_create_nonce( 'save-certificate-settings' ); ?>
                                            <input type="hidden" name="save-certificate-settings" value="<?php echo esc_attr( $nonce ); ?>">
                                            <input type="hidden" name="action" value="wl-mim-save-certificate-settings">

                                            <div class="form-check col-sm-12 mb-3">
                                                <input name="certificate_dob_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-certificate_dob_enable" <?php checked( $certificate_dob_enable, true ); ?>>
                                                <label for="wlim-setting-certificate_dob_enable" class="form-check-label">
                                                    <?php esc_html_e( 'Requrie Date of Birth', WL_MIM_DOMAIN ); ?>
                                                </label>
                                            </div>

                                            <div class="form-group col-sm-12">
                                                <label for="wlim-setting-certificate_signature" class="col-form-label"><?php esc_html_e( 'Upload Signature', WL_MIM_DOMAIN ); ?>:</label><br>
                                                <?php if ( ! empty ( $certificate['sign'] ) ) { ?>
                                                    <img src="<?php echo wp_get_attachment_url( $certificate['sign'] ); ?>" class="img-responsive wlim-institute_sign">
                                                <?php } ?>
                                                <input name="certificate_signature" type="file" id="wlim-setting-certificate_signature">
                                            </div>

                                            <div class="form-check col-sm-12 mb-3">
                                                <input name="certificate_signature_enable" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-certificate_signature_enable" <?php checked( $certificate['sign_enable'], '1', true ); ?>>
                                                <label class="form-check-label" for="wlim-setting-certificate_signature_enable">
                                                    <?php esc_html_e( 'Enable Signature Image?', WL_MIM_DOMAIN ); ?>
                                                </label>
                                            </div>

                                            <button type="submit" class="ml-3 btn btn-primary save-certificate-settings-submit"><?php esc_html_e( 'Save Settings', WL_MIM_DOMAIN ); ?></button>
                                        </form>
                                    </div>
                                </div>
                                <!-- end - card body content -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Noticeboard -->
                 <div class="tab-pane fade" id="list-noticeboard" role="tabpanel" aria-labelledby="list-noticeboard-list">
                    <div class="row">
                        <div class="card col">
                            <div class="card-header bg-primary text-white">
                                <!-- card header content -->
                                <div class="row">
                                    <div class="col-md-12 col-xs-12">
                                        <div class="h5"><?php esc_html_e( 'Noticeboard', WL_MIM_DOMAIN ); ?></div>
                                    </div>
                                </div>
                                <!-- end - card header content -->
                            </div>
                            <div class="card-body">
                                <!-- card body content -->
                                <div class="row">
                                    <div class="col">
                                        <div class="text-center">
                                            <?php esc_html_e( 'To Display noticeboard Form on Front End, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                            <div class="col-12 justify-content-center align-items-center">
                                                <span class="col-6">
                                                    <strong id="wl_im_noticeboard_form_shortcode">[institute_noticeboard id=<?php echo esc_html( $institute_id ); ?>]</strong>
                                                </span>
                                                <span class="col-6">
                                                    <button id="wl_im_noticeboard_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- end - card body content -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end - row 2 -->
</div>

<!-- add new custom field modal -->
<div class="modal fade" id="add-custom-field" tabindex="-1" role="dialog" aria-labelledby="add-custom-field-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-custom-field-label"><?php esc_html_e( 'Add New Custom Field', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4">
                <form id="wlim-add-custom-field-form">
					<?php $nonce = wp_create_nonce( 'add-custom-field' ); ?>
                    <input type="hidden" name="add-custom-field" value="<?php echo esc_attr( $nonce ); ?>">
                    <div class="form-group">
                        <label for="wlim-custom-field-name" class="col-form-label"><?php esc_html_e( 'Field Name', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="field_name" type="text" class="form-control" id="wlim-custom-field-field_name" placeholder="<?php esc_html_e( "Field Name", WL_MIM_DOMAIN ); ?>" min="0">
                    </div>
                    <div class="form-check pl-0">
                        <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-custom-field-is_active" checked>
                        <label class="form-check-label" for="wlim-custom-field-is_active">
							<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary add-custom-field-submit"><?php esc_html_e( 'Add New Custom Field', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - add new custom field modal -->

<!-- update custom field modal -->
<div class="modal fade" id="update-custom-field" tabindex="-1" role="dialog" aria-labelledby="update-custom-field-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-custom-field-label"><?php esc_html_e( 'Custom Field', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="fetch_custom-field"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary update-custom-field-submit"><?php esc_html_e( 'Update Custom Field', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - update custom field modal -->
