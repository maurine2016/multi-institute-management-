<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

$institute_id = WL_MIM_Helper::get_current_institute_id();

$email = WL_MIM_SettingHelper::get_email_settings( $institute_id );

$notification_by_list = WL_MIM_Helper::get_notification_by_list();

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

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
                <span class="border-bottom"><i class="fa fa-bell"></i> <?php esc_html_e( 'Notifications', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can send notifications to students.', WL_MIM_DOMAIN ); ?>
            </div>
            <!-- end main header content -->
        </div>
    </div>
    <!-- end - row 1 -->

    <!-- row 2 -->
    <div class="row">
        <div class="card col">
            <div class="card-header bg-primary text-white">
                <!-- card header content -->
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <div class="h4"><?php esc_html_e( 'Send Notifications', WL_MIM_DOMAIN ); ?></div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col-xs-12 col-md-8">
                        <form id="wlim-notification-configure-form">
                            <div class="form-group">
                                <label for="wlim-notification_by" class="text-primary"><?php esc_html_e( 'Notification By', WL_MIM_DOMAIN ); ?>:</label>
                                <select name="notification_by" class="form-control" id="wlim-notification_by">
                                    <option value="">---- <?php esc_html_e( 'Select Notification By', WL_MIM_DOMAIN ); ?> ----</option>
									<?php
									foreach ( $notification_by_list as $key => $notification_by ) { ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( $notification_by, WL_MIM_DOMAIN ); ?></option>
									<?php
									} ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-10">
                        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="send-notification-form" enctype="multipart/form-data">
							<?php $nonce = wp_create_nonce( 'wl-mim-send-notification' ); ?>
                            <input type="hidden" name="security" value="<?php echo esc_attr( $nonce ); ?>">
                            <input type="hidden" name="action" value="wl-mim-send-notification">
                            <div id="wlim-notification-configure"></div>
                            <hr>
                            <label class="text-primary"><?php esc_html_e( 'Notification Channel', WL_MIM_DOMAIN ); ?>: </label>
                            <hr>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="email_notification" class="form-check-input mt-1" id="wlim-email-notification">
                                <label class="form-check-label mb-1 ml-4" for="wlim-email-notification"><?php esc_html_e( 'Email Notification', WL_MIM_DOMAIN ); ?></label>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="sms_notification" class="form-check-input mt-1" id="wlim-sms-notification">
                                <label class="form-check-label mb-1 ml-4" for="wlim-sms-notification"><?php esc_html_e( 'SMS Notification', WL_MIM_DOMAIN ); ?></label>
                            </div>
                            <div class="card col wlim-email-template">
                                <div class="card-header"><?php esc_html_e( 'Email Template', WL_MIM_DOMAIN ); ?></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="wlim-email-from" class="col-form-label"><?php esc_html_e( 'From', WL_MIM_DOMAIN ); ?>:</label>
                                        <input type="text" name="email_from" class="form-control" id="wlim-email-from" placeholder="<?php esc_html_e( "Email From", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $email['email_from'] ); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="wlim-email-subject" class="col-form-label">* <?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?>
                                            :</label>
                                        <input type="text" name="email_subject" class="form-control" id="wlim-email-subject" placeholder="<?php esc_html_e( "Email Subject", WL_MIM_DOMAIN ); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="wlim_email_body" class="col-form-label">* <?php esc_html_e( 'Body', WL_MIM_DOMAIN ); ?>:</label>
										<?php
										$settings = array(
											'media_buttons' => false,
											'textarea_name' => 'email_body',
											'textarea_rows' => 6,
											'quicktags'     => array( 'buttons' => 'strong,em,del,ul,ol,li,code,close' )
										);
										wp_editor( '', 'wlim_email_body', $settings ); ?>
                                    </div>
                                    <div class="form-group">
                                        <label for="wlim-email-attachment" class="col-form-label"><?php esc_html_e( 'Attachments', WL_MIM_DOMAIN ); ?>
                                            :</label><br>
                                        <input type="file" name="attachment[]" id="wlim-email-attachment" multiple>
                                        (<?php esc_html_e( 'Hold Ctrl to select multiple files', WL_MIM_DOMAIN ); ?>)
                                    </div>
                                </div>
                            </div>
                            <div class="card col wlim-sms-template">
                                <div class="card-header"><?php esc_html_e( 'SMS Template', WL_MIM_DOMAIN ); ?></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="wlim_sms_body" class="col-form-label">* <?php esc_html_e( 'Body', WL_MIM_DOMAIN ); ?>:</label>
                                        <textarea name="sms_body" class="form-control" id="wlim_sms_body" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary float-right send-notification-submit"><?php esc_html_e( 'Send!', WL_MIM_DOMAIN ); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
    <!-- end - row 2 -->
</div>