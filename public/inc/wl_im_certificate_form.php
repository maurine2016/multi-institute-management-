<?php
defined( 'ABSPATH' ) || die();
require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php';
require_once WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php';
require_once WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_StudentHelper.php';

$data_of_birth_required = false;

if ( isset( $attr['id'] ) ) {
	global $wpdb;
	$institute_id = intval( $attr['id'] );
	$institute    = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id AND is_active = 1" );
	if ( ! $institute ) {
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
		} else {
			$screen = '';
		}
		if ( ! $screen || ! in_array( $screen->post_type, array( 'page', 'post' ) ) ) {
			die( esc_html__( 'Institute is either invalid or not active. If you are owner of this institute, then please contact the administrator.', WL_MIM_DOMAIN ) );
		}
	}

	$data_of_birth_required = WL_MIM_SettingHelper::get_certificate_dob_enable_settings( $institute_id );
} else {
	$institute              = null;
	$wlim_active_institutes = WL_MIM_Helper::get_active_institutes();
}
if ( isset( $_REQUEST['id'] ) && ! empty( $_REQUEST['id'] ) ) {
	global $wpdb;
	$id     = sanitize_text_field( $_REQUEST['id'] );
	$result = WL_MIM_Helper::get_student_information_certificate( $id );


	$institute_id = $result[0]->institute_id;
	// WL_MIM_Helper::get_certificate($institute_id, $id);
	$student = WL_MIM_StudentHelper::fetch_student( $institute_id, $id );

	$institute_id              = $result[0]->institute_id;
	$general_institute         = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
	$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
	$enrollment_id             = WL_MIM_Helper::get_enrollment_id_with_prefix( $id, $general_enrollment_prefix );
	$certificate_number        = WL_MIM_Helper::get_certificate_number( $id );
	$course_id                 = $result[0]->course_id;
	$batch_id                  = $result[0]->batch_id;
	$first_name                = $result[0]->first_name;
	$last_name                 = $result[0]->last_name;

	$institute_advanced_logo = wp_get_attachment_url( $general_institute['institute_logo'] );
	// $institute_advanced_name = $general_institute['institute_name'];
	$institute_name    = $general_institute['institute_name'];
	$show_logo         = $general_institute['institute_logo_enable'];
	$institute_address = $general_institute['institute_address'];
	$institute_phone   = $general_institute['institute_phone'];
	$institute_email   = $general_institute['institute_email'];
	if ( ! empty( $show_logo ) ) {
		$institute_logo_url = esc_url( wp_get_attachment_url( $institute_advanced_logo ) );
	}

	// $image_url               = wp_get_attachment_url($image_id);

	$course = $wpdb->get_row( "SELECT course_name, course_code, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $course_id AND institute_id = $institute_id" );
	$batch  = $wpdb->get_row( "SELECT end_date FROM {$wpdb->prefix}wl_min_batches WHERE id = $batch_id  AND institute_id = $institute_id" );

	$name            = $first_name . ' ' . $last_name;
	$course          = ( ! empty( $course ) ) ? $course->course_name : '-';
	$completion_date = ( ! empty( $batch->end_date ) ) ? date_format( date_create( $batch->end_date ), 'd M, Y' ) : '-';

	$certificate        = WL_MIM_SettingHelper::get_certificate_settings( $institute_id );
	$certificate_detail = WL_MIM_Helper::fetch_certificate_student_dash( $institute_id, $id );

	// ANCHOR Stduents certificates Query
	global $wpdb;
	$certificates = $wpdb->get_results(
		'SELECT cfsr.ID as id, cfsr.certificate_number, cfsr.date_issued, sr.ID as student_id, sr.first_name as student_name, mb.batch_code, mb.batch_name, mc.course_code, mc.course_name, sr.phone, cf.label FROM ' . "{$wpdb->prefix}" . 'wl_min_certificate_student' . ' as cfsr
        JOIN ' . "{$wpdb->prefix}" . 'wl_min_certificate' . ' as cf ON cf.ID = cfsr.certificate_id
        JOIN ' . "{$wpdb->prefix}" . 'wl_min_students' . ' as sr ON sr.ID = cfsr.student_record_id
        JOIN ' . "{$wpdb->prefix}" . 'wl_min_batches' . ' as mb ON mb.id = sr.batch_id
        JOIN ' . "{$wpdb->prefix}" . 'wl_min_courses' . ' as mc ON mc.id = sr.course_id
        WHERE sr.ID = ' . absint( $id )
	);

	?>

	<div class="wl_im_container wl_im">
		<div class="row justify-content-md-center">
			<div class="col-xs-12 col-md-12">
				<div id="wlim-get-certificate"></div>

				<div class="wlim-table-responsive" style="overflow-x:auto;">
					<table class="">
						<th><?php esc_html_e( 'Student Name', WL_MIM_DOMAIN ); ?></th>
						<th><?php esc_html_e( 'Certificate Title', WL_MIM_DOMAIN ); ?></th>
						<th><?php esc_html_e( 'Certificate Number', WL_MIM_DOMAIN ); ?></th>
						<th><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
						<th><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
						<th><?php esc_html_e( 'Issued Date', WL_MIM_DOMAIN ); ?></th>
						<?php foreach ( $certificates as $certificate ) : ?>
							<?php
							$student_name       = $certificate->student_name;
							$label              = $certificate->label;
							$certificate_number = $certificate->id;
							$course_code        = $certificate->course_name;
							$batch_code         = $certificate->batch_name;
							$date_issued        = $certificate->date_issued;
							?>

							<tr>
								<td><?php echo $student_name; ?></td>
								<td> <?php echo $label; ?> </td>
								<td><?php echo $certificate_number; ?></td>
								<td><?php echo $course_code; ?></td>
								<td><?php echo $batch_code; ?></td>
								<td><?php echo $date_issued; ?></td>
							</tr>
						<?php endforeach ?>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?php
} else {
	?>
	<div class="wl_im_container wl_im">
		<div class="row justify-content-md-center">
			<div class="col-xs-12 col-md-12">
				<div id="wlim-get-certificate"></div>
				<form id="wlim-certificate-form">
					<?php if ( ! $institute ) { ?>
						<div class="form-group">
							<label for="wlim-certificate-institute" class="col-form-label">
								*<strong><?php esc_html_e( 'Select Institute', WL_MIM_DOMAIN ); ?>:</strong>
							</label>
							<select name="institute" class="form-control" id="wlim-certificate-institute" data-dob="<?php echo esc_attr( $data_of_birth_required ); ?>">
								<option value="">-------- <?php esc_html_e( 'Select Institute', WL_MIM_DOMAIN ); ?> --------</option>
								<?php
								if ( count( $wlim_active_institutes ) > 0 ) {
									foreach ( $wlim_active_institutes as $active_institute ) {
										?>
										<option value="<?php echo esc_attr( $active_institute->id ); ?>"><?php echo esc_html( $active_institute->name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<div id="wlim-fetch-institute-dob-certificate"></div>
					<?php } else { ?>
						<input type="hidden" name="institute" value="<?php echo esc_attr( $institute->id ); ?>">
					<?php } ?>
					<div class="form-group">
						<label for="wlim-certificate-enrollment_id" class="col-form-label">
							*<strong><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?>:</strong>
						</label>
						<input name="enrollment_id" type="text" class="form-control" id="wlim-certificate-enrollment_id" placeholder="<?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?>">
					</div>
					<?php if ( $data_of_birth_required ) { ?>
						<div class="form-group">
							<label for="wlim-certificate-date_of_birth" class="col-form-label">
								*<strong><?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?>:</strong>
							</label>
							<input name="date_of_birth" type="text" class="form-control wlim-date_of_birth" id="wlim-certificate-date_of_birth" placeholder="<?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?>">
						</div>
					<?php } ?>
					<div class="mt-3 float-right">
						<button type="submit" class="btn btn-primary view-certificate-submit"><?php esc_html_e( 'Get Certificate', WL_MIM_DOMAIN ); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php
}
?>
