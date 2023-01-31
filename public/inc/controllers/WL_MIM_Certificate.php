<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

class WL_MIM_Certificate {
	/* Get certificate */
	public static function get_certificate() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$institute_id  = isset( $_POST['institute'] ) ? intval( sanitize_text_field( $_POST['institute'] ) ) : null;
		$enrollment_id = isset( $_REQUEST['enrollment_id'] ) ? sanitize_text_field( $_REQUEST['enrollment_id'] ) : null;

    	$data_of_birth_required = WL_MIM_SettingHelper::get_certificate_dob_enable_settings( $institute_id );

		if ( $data_of_birth_required ) {
			$date_of_birth = ( isset( $_POST['date_of_birth'] ) && ! empty( $_POST['date_of_birth'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['date_of_birth'] ) ) ) : null;
		}

		/* Validations */
		$errors = array();
		if ( empty( $institute_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select institute.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		if ( $data_of_birth_required ) {
			if ( empty( $date_of_birth ) ) { ?>
				<strong class="text-danger"><?php esc_html_e( 'Please provide date of birth.', WL_MIM_DOMAIN ); ?></strong>
				<?php
				die();
			}

			if ( ! empty( $date_of_birth ) && ( strtotime( date( 'Y' ) - 2 ) <= strtotime( $date_of_birth ) ) ) { ?>
				<strong class="text-danger"><?php esc_html_e( 'Please provide valid date of birth.', WL_MIM_DOMAIN ); ?></strong>
				<?php
				die();
			}
		}

		if ( empty( $enrollment_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please provide an enrollmend ID.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		/* End validations */

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
		$student_id                = WL_MIM_Helper::get_student_id_with_prefix( $enrollment_id, $general_enrollment_prefix );

		if ( $data_of_birth_required ) {
			$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND enrollment_id = $student_id AND institute_id = $institute_id AND date_of_birth = '$date_of_birth'" );
		} else {
			$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND enrollment_id = $student_id AND institute_id = $institute_id" );
		}

		$skip_current_institute = true;

		if ( ! $row ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Student not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}


		$batch = WL_MIM_Helper::get_batch( $row->batch_id, $institute_id );
		if ( ! $batch ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Student is not assigned to any batch.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		if ( ! WL_MIM_Helper::is_batch_ended( $batch->start_date, $batch->end_date ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Unable to get completion certificate as batch is still on going.', WL_MIM_DOMAIN ); ?></strong>
		<?php
		die();
		}

		?>
        <a class="ml-2 mb-3 btn btn-outline-primary" href="#wlmim-print-certificate" data-keyboard="false" data-backdrop="static" data-toggle="modal">
        	<?php esc_html_e( 'Print Certificate', WL_MIM_DOMAIN ); ?>&nbsp;<i class="fa fa-print"></i>
        </a>
		<!-- print certificate modal -->
		<div class="modal fade" id="wlmim-print-certificate" tabindex="-1" role="dialog" aria-labelledby="print-certificate-label" aria-hidden="true">
		    <div class="modal-dialog modal-dialog-centered" id="print-certificate-dialog" role="document">
		        <div class="modal-content">
		            <div class="modal-header">
		                <h5 class="modal-title w-100 text-center" id="print-certificate-label"><?php esc_html_e( 'View and Print Certificate', WL_MIM_DOMAIN ); ?></h5>
		                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		                    <span aria-hidden="true">&times;</span>
		                </button>
		            </div>
		            <div class="modal-body pr-4 pl-4" id="wlmim_print_student">
				        <div class="row">
				            <div class="col">
				                <div class="mb-3 mt-2">
				                    <div class="text-center">
				                        <button type="button" id="wlmim-certificate-print-button" class="btn btn-sm btn-success">
				                            <i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Certificate', WL_MIM_DOMAIN ); ?>
				                        </button>
				                        <hr>
				                    </div>
									<?php
									require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_certificate.php' ); ?>
				                </div>
				            </div>
				        </div>
		            </div>
		            <div class="modal-footer">
		                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
		            </div>
		        </div>
		    </div>
		</div><!-- end - print certificate modal -->
		<?php
		die();
	}

	/* Fetch institute's exams */
	public static function fetch_institute_dob() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = intval( sanitize_text_field( $_POST['id'] ) );

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id AND is_active = '1'" );
		if ( ! $row ) {
			die();
		}
		$data_of_birth_required = WL_MIM_SettingHelper::get_certificate_dob_enable_settings( $institute_id );
		if ( ! $data_of_birth_required ) {
			die();
		}
		?>
        <div class="form-group">
            <label for="wlim-certificate-date_of_birth" class="col-form-label">
                *<strong><?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?>:</strong>
            </label>
            <input name="date_of_birth" type="text" class="form-control wlim-date_of_birth" id="wlim-certificate-date_of_birth" placeholder="<?php esc_html_e( "Date of Birth", WL_MIM_DOMAIN ); ?>">
        </div>
		<?php
		die();
	}
}
?>