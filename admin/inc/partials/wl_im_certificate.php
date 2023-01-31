<?php defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
?>
<div id="wl-certificate" class="wl_im">
	<?php
    if ( ! isset( $skip_current_institute ) ) {
        $institute_id = WL_MIM_Helper::get_current_institute_id();
    }

    $registration_number = WL_MIM_Helper::get_institute_registration_number( $institute_id );

	$general_institute         = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
	$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

    if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
        $student_id = $row->enrollment_id;
    } else {
        $student_id = $row->id;
    }
	$enrollment_id      = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
	$certificate_number = WL_MIM_Helper::get_certificate_number( $row->id );
	$course             = $wpdb->get_row( "SELECT course_name, course_code, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $row->course_id AND institute_id = $institute_id" );
	$batch              = $wpdb->get_row( "SELECT end_date FROM {$wpdb->prefix}wl_min_batches WHERE id = $row->batch_id AND institute_id = $institute_id" );

	$name            = $row->first_name . " $row->last_name";
	$course          = ( ! empty ( $course ) ) ? $course->course_name : '-';
	$completion_date = ( ! empty ( $batch->end_date ) ) ? date_format( date_create( $batch->end_date ), "d M, Y" ) : '-';

	$institute_advanced_logo = wp_get_attachment_url( $general_institute['institute_logo'] );
	$institute_advanced_name = $general_institute['institute_name'];
	$show_logo               = $general_institute['institute_logo_enable'];

    $certificate = WL_MIM_SettingHelper::get_certificate_settings( $institute_id );
	?>
    <div id="wl-certificate-box">
        <div class="row">
            <div class="col mx-auto">
                <?php if ( $registration_number ) { ?>
                <span class="float-left wl-certificate-number mb-1"><?php esc_html_e( 'Registration No.', WL_MIM_DOMAIN ); ?>
                    <strong><?php echo esc_html( $registration_number ); ?></strong>
                </span>
                <?php } ?>
                <span class="float-right" id="wl-certificate-number"><?php esc_html_e( 'Certificate No.', WL_MIM_DOMAIN ); ?>
                    <strong><?php echo esc_html( $certificate_number ); ?></strong>
                </span>
            </div>
        </div>
        <div id="wl-certificate-content-box">
            <div class="row mt-4 pt-4">
                <div class="col-12">
                    <h1 class="text-center display-3"><?php esc_html_e( 'Certificate of Completion', WL_MIM_DOMAIN ); ?></h1>
                </div>
            </div>
            <div class="row mt-4 pt-4">
                <div class="col-12 text-center">
                    <p class="wl-certificate-text display-4"><?php echo esc_html__( 'This is to certify that', WL_MIM_DOMAIN ) . " " . esc_html( $name ) . " " . esc_html__( 'successfully completed', WL_MIM_DOMAIN ) . " " . esc_html( $course ) . " " . esc_html__( 'course on', WL_MIM_DOMAIN ) . " " . esc_html( $completion_date ); ?>.</p>
                </div>
            </div>

			<?php
            if ( $show_logo ) { ?>
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <img src="<?php echo esc_url( $institute_advanced_logo ); ?>" id="wl-institute-pro-certificate-logo" class="img-responsive mx-auto d-block">
                    </div>
                </div>
			<?php
			} ?>
            <div class="row justify-content-center align-items-center">
                <div class="col-12">
                    <h3 class="text-center" id="wl-certificate-name"><?php echo esc_html( $institute_advanced_name ); ?></h3>
                </div>
            </div>
        </div>
        <div class="row wl-certificate-signature-box-row">
            <div class="col-6 text-left"></div>
            <div class="col-6">
                <?php if ( $certificate['sign_enable'] && $url = wp_get_attachment_url( $certificate['sign'] ) ) { ?>
                 <img class="wl-authorized-by" src="<?php echo esc_url( $url ); ?>">
                <?php } ?>
                <div class="text-right mr-5"><?php esc_html_e( 'Authorised By', WL_MIM_DOMAIN ); ?></div>
            </div>
        </div>
    </div>
</div>
