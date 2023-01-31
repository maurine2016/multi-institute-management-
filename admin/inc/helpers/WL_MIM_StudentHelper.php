<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

class WL_MIM_StudentHelper {
	/* Get student */
	public static function get_student() {
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( $user_id = get_current_user_id() ) {
			$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND user_id = $user_id AND institute_id = $institute_id" );

			return $student;
		}

		return null;
	}

	public static function fetch_student($institute_id, $user_id) {
		global $wpdb;
	
			$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students as ms
			JOIN {$wpdb->prefix}wl_min_batches as mb ON mb.id = ms.batch_id
			JOIN {$wpdb->prefix}wl_min_certificate_student as cs ON cs.student_record_id = ms.id
			JOIN {$wpdb->prefix}wl_min_courses as mc ON mc.id = ms.course_id
			WHERE ms.id = $user_id AND ms.institute_id = $institute_id" );

			return $student;
		}

		public static function check_buffer( $show_buffer_error = true ) {
			$buffer = ob_get_clean();
			if ( ! empty( $buffer ) ) {
				if ( $show_buffer_error ) {
					throw new Exception( $buffer );
				}
	
				throw new Exception( esc_html__( 'Unexpected error occurred!', WL_MIM_DOMAIN ) );
			}
		}

	/* Get notices */
	public static function get_notices( $limit = null ) {
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		$limit        = $limit ? "LIMIT $limit" : "";

		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_notices WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY priority ASC, id DESC $limit" );
	}
}

?>