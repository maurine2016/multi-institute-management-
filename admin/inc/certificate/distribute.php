<?php
defined( 'ABSPATH' ) || die();

$page_url = WL_MIM_Helper::get_certificates_page_url();

$institute_id = WL_MIM_Helper::get_current_institute_id();

$certificate = NULL;
global $wpdb;
if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id          = absint( $_GET['id'] );
	$certificate = WL_MIM_Helper::fetch_certificate($institute_id, $id );
}

if ( ! $certificate ) {
	die;
}

$nonce_action = 'distribute-certificate-' . $certificate->ID;

$label = $certificate->label;

$courses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY id DESC" );

$batches = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY id DESC" );

?>


<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					printf(
						wp_kses(
							/* translators: %s: certificate title */
							__( 'Distribute Certificate: %s', WL_MIM_DOMAIN ),
							array(
								'span' => array( 'Batch' => array() )
							)
						),
						esc_html( $certificate->label )
					);
					?>
				</span>
			</span>
			<span class="float-md-right">
				<a href="<?php echo esc_url( $page_url . "&action=students&id=" . $certificate->ID ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-certificate"></i>&nbsp;
					<?php esc_html_e( 'Total Certificates Distributed', WL_MIM_DOMAIN ); ?>
				</a>
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-certificate"></i>&nbsp;
					<?php esc_html_e( 'View All', WL_MIM_DOMAIN ); ?>
				</a>
			</span>
		</div>

		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wl-mim-distribute-certificate-form">

			<?php $nonce = wp_create_nonce( $nonce_action ); ?>
			<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

			<input type="hidden" name="action" value="wl-mim-distribute-certificate">

			<input type="hidden" name="certificate_id" value="<?php echo esc_attr( $certificate->ID ); ?>">

			<div class="wlsm-form-section">
				<div class="form-row">
				<div class="form-group col-md-4">
						<label for="wlsm_course" class="wlsm-font-bold">
							<?php esc_html_e( 'course', WL_MIM_DOMAIN ); ?>:
						</label>
						<select name="course_id" class="form-control selectpicker" data-nonce="<?php echo esc_attr( wp_create_nonce( 'get-class-sections' ) ); ?>" id="wl_mim_course" data-live-search="true">
							<option value=""><?php esc_html_e( 'Select course', WL_MIM_DOMAIN ); ?></option>
							<?php foreach ( $courses as $course ) { ?>
							<option value="<?php echo esc_attr( $course->id ); ?>">
								<?php echo esc_html( $course->course_name." [$course->course_code]" ) ; ?>
							</option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_class" class="wlsm-font-bold">
							<?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?>:
						</label>
						<select name="batch_id" class="form-control selectpicker" data-nonce="<?php echo esc_attr( wp_create_nonce( 'get-class-sections' ) ); ?>" id="wl_mim_batch" data-live-search="true">
							
						</select>
					</div>
					
					<div class="form-group col-md-4 wlsm-student-select-block">
						<label for="wlsm_student" class="wlsm-font-bold">
							<?php esc_html_e( 'Students', WL_MIM_DOMAIN ); ?>:
						</label>
						<select name="student[]" multiple class="form-control selectpicker" id="wl_min_student" data-live-search="true" data-actions-box="true" data-none-selected-text="<?php esc_attr_e( 'Select Students', WL_MIM_DOMAIN ); ?>">
						</select>
					</div>
				</div>
			</div>

			<div class="wlsm-form-section">
				<div class="form-row justify-content-md-center">
					<div class="form-group col-md-4">
						<label for="wlsm_date_issued" class="font-weight-bold">
							<?php esc_html_e( 'Date Issued', WL_MIM_DOMAIN ); ?>:
						</label>
						<input type="text" name="date_issued" class="form-control wl_min_date_issued" id="wl_min_date_issued" placeholder="<?php esc_attr_e( 'Enter date issued', WL_MIM_DOMAIN ); ?>">
					</div>
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-primary" id="wl-mim-distribute-certificate-btn">
						<?php esc_html_e( 'Distribute Certificate', WL_MIM_DOMAIN ); ?>
					</button>
				</div>
			</div>

		</form>
	</div>
</div>
