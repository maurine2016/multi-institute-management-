<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_StudentHelper.php' );

class WL_MIM_Attendance {
	public static function get_batch_students() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		$batch_ids = ( isset( $_REQUEST['batch'] ) && is_array( $_POST['batch'] ) ) ? $_REQUEST['batch'] : array();

		$attendance_date = ( isset( $_REQUEST['attendance_date'] ) && ! empty( $_REQUEST['attendance_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['attendance_date'] ) ) ) : null;

		if ( empty( $attendance_date ) ) { ?>
            <div class="text-center alert alert-info">
				<?php esc_html_e( 'Please select date of attendance.', WL_MIM_DOMAIN ); ?>
            </div>
			<?php
			die();
		}

		if ( date( "Y-m-d" ) < $attendance_date ) { ?>
            <div class="text-center alert alert-info">
				<?php esc_html_e( 'Please select valid attendance date.', WL_MIM_DOMAIN ); ?>
            </div>
			<?php
			die();
		}

		if ( empty( $batch_ids ) ) { ?>
            <div class="text-center alert alert-info">
				<?php esc_html_e( 'Please select a batch.', WL_MIM_DOMAIN ); ?>
            </div>
			<?php
			die();
		}

		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$batch_ids = implode(',', $batch_ids);

		$query    = "SELECT s.id, s.enrollment_id, s.first_name, s.last_name, b.batch_name, b.batch_code, c.course_name, c.course_code FROM {$wpdb->prefix}wl_min_students as s, {$wpdb->prefix}wl_min_batches as b, {$wpdb->prefix}wl_min_courses as c WHERE s.batch_id = b.id AND b.course_id = c.id AND s.is_deleted = 0 AND s.batch_id IN($batch_ids) AND s.institute_id = $institute_id AND s.is_active = 1 ORDER BY s.id ASC";
		$students = $wpdb->get_results( $query );

		$query = "SELECT s.id as student_id, b.batch_name, b.batch_code, c.course_name, c.course_code, b.id as batch_id, s.first_name, s.last_name, a.attendance_date, a.status FROM {$wpdb->prefix}wl_min_attendance as a, {$wpdb->prefix}wl_min_students as s, {$wpdb->prefix}wl_min_batches as b, {$wpdb->prefix}wl_min_courses as c WHERE s.id = a.student_id AND s.batch_id = b.id AND b.course_id = c.id AND a.attendance_date = '$attendance_date' AND batch_id IN($batch_ids) AND b.is_deleted = 0 AND a.institute_id = $institute_id ORDER BY s.id ASC";

		$attendance = $wpdb->get_results( $query, OBJECT_K );
		?><?php $nonce = wp_create_nonce( 'add-attendance' ); ?>
        <input type="hidden" name="add-attendance" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-add-attendance">
        <div class="card col">
            <div class="card-header bg-primary text-white">
                <span class="float-right">
        			<strong><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?>:</strong>&nbsp;<?php echo date_format( date_create( $attendance_date ), 'd-m-Y' ); ?>
        		</span>
            </div>
            <div class="card-body">
                <table class="table w-100 table-hover">
                    <thead>
						<tr>
							<th colspan="1"><?php esc_html_e( 'S.No', WL_MIM_DOMAIN ); ?></th>
							<th colspan="3"><?php esc_html_e( 'Student', WL_MIM_DOMAIN ); ?></th>
							<th colspan="1"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
							<th colspan="1"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
							<th colspan="1"><?php esc_html_e( 'Attendance', WL_MIM_DOMAIN ); ?></th>
						</tr>
                    </thead>
                    <tbody>
					<?php
					if ( count( $students ) ) {
						foreach ( $students as $key => $student ) {
							if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
								$student_id = $student->enrollment_id;
							} else {
								$student_id = $student->id;
							}
							$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
							$name          = $student->first_name;
							$student_id    = $student->id;

							if ( $student->last_name ) {
								$name .= " $student->last_name";
							}
							?>
                            <tr>
                                <td colspan="1"><?php echo esc_html( $key + 1 ); ?></td>
                                <td colspan="3"><?php echo esc_html( $name . " ($enrollment_id)" ); ?></td>
                                <td colspan="1"><?php echo esc_html( $student->course_name . ' (' . $student->course_code . ') ' ); ?></td>
                                <td colspan="1"><?php echo esc_html( '(' . $student->batch_code . ') ' . $student->batch_name ); ?></td>
                                <td colspan="1">
                                    <div class="row mt-2">
                                        <div class="col-sm-12">
                                            <label class="radio-inline mr-3"><input <?php echo isset( $attendance[ $student_id ] ) ? checked( $attendance[ $student_id ]->status, 'a', false ) : 'checked'; ?> type="radio" name="status[<?php echo esc_attr( $key ); ?>]" class="mr-2" value="a" id="wlim-attendance-status-absent-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Absent', WL_MIM_DOMAIN ); ?></label>
                                            <label class="radio-inline"><input <?php echo isset( $attendance[ $student_id ] ) ? checked( $attendance[ $student_id ]->status, 'p', false ) : ''; ?> type="radio" name="status[<?php echo esc_attr( $key ); ?>]" class="mr-2" value="p" id="wlim-attendance-status-present-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Present', WL_MIM_DOMAIN ); ?></label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
						<?php
						}
					} else { ?>
                        <tr class="text-center">
                            <td colspan="1"></td>
                            <td colspan="3"><?php esc_html_e( "There is no student in this batch.", WL_MIM_DOMAIN ); ?></td>
                            <td colspan="1"></td>
                            <td colspan="1"></td>
                            <td colspan="1"></td>
                        </tr>
					<?php
					} ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-right mt-2">
            <button type="submit" class="btn btn-primary add-attendance-submit"><?php esc_html_e( 'Save Attendance', WL_MIM_DOMAIN ); ?></button>
        </div>
		<?php
		die();
	}

	public static function save_attendance() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-attendance'], 'add-attendance' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$batch_ids = ( isset( $_REQUEST['batch'] ) && is_array( $_POST['batch'] ) ) ? $_REQUEST['batch'] : array();
		$status    = ( isset( $_POST['status'] ) && is_array( $_POST['status'] ) ) ? $_POST['status'] : array();

		$attendance_date = ( isset( $_REQUEST['attendance_date'] ) && ! empty( $_REQUEST['attendance_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['attendance_date'] ) ) ) : null;

		/* Validations */
		$errors = array();
		if ( empty( $attendance_date ) ) {
			$errors['attendance_date'] = esc_html__( 'Please provide date of attendance.', WL_MIM_DOMAIN );
		}

		if ( date( "Y-m-d" ) < $attendance_date ) {
			$errors['attendance_date'] = esc_html__( 'Please select valid attendance date.', WL_MIM_DOMAIN );
		}

		if ( empty( $batch_ids ) ) { ?>
            <div class="text-center alert alert-info">
				<?php esc_html_e( 'Please select a batch.', WL_MIM_DOMAIN ); ?>
            </div>
			<?php
			die();
		}

		/* End validations */

		if ( count( $errors ) < 1 ) {

			$batch_ids = implode(',', $batch_ids);

			$query    = "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id IN($batch_ids) AND institute_id = $institute_id ORDER BY id ASC";
			$students = $wpdb->get_results( $query );

			$query = "SELECT s.id as student_id, b.batch_name, b.batch_code, b.id as batch_id, s.first_name, s.last_name, a.attendance_date, a.status, a.id as attendance_id FROM {$wpdb->prefix}wl_min_attendance as a, {$wpdb->prefix}wl_min_students as s, {$wpdb->prefix}wl_min_batches as b WHERE s.id = a.student_id AND s.batch_id = b.id AND a.attendance_date = '$attendance_date' AND batch_id IN($batch_ids) AND a.institute_id = $institute_id ORDER BY s.id ASC";

			$attendance = $wpdb->get_results( $query, OBJECT_K );

			if ( count( $students ) < 1 || ( count( $students ) != count( $status ) ) ) {
				wp_send_json_error( esc_html__( 'Invalid attendance.', WL_MIM_DOMAIN ) );
			}

			try {
				$wpdb->query( 'BEGIN;' );
				if ( count( $students ) ) {
					foreach ( $students as $key => $student ) {
						if ( isset( $status[ $key ] ) ) {
							$attendance_status = ( $status[ $key ] == 'p' ) ? 'p' : 'a';
						} else {
							$attendance_status = 'a';
						}

						$student_id = $student->id;
						if ( isset( $attendance[ $student->id ] ) ) {
							/* Attendance exists */
							$attendance_id = $attendance[ $student->id ]->attendance_id;
							$data          = array(
								'student_id'      => $student_id,
								'attendance_date' => $attendance_date,
								'status'          => $attendance_status,
								'added_by'        => get_current_user_id(),
								'institute_id'    => $institute_id,
								'updated_at'      => date( 'Y-m-d H:i:s' )
							);
							$success       = $wpdb->update( "{$wpdb->prefix}wl_min_attendance", $data, array(
								'id'           => $attendance_id,
								'institute_id' => $institute_id
							) );
							if ( $success === false ) {
								throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
							}
						} else {
							/* Attendance does not exist */
							$data    = array(
								'student_id'      => $student_id,
								'attendance_date' => $attendance_date,
								'status'          => $attendance_status,
								'added_by'        => get_current_user_id(),
								'institute_id'    => $institute_id
							);
							$data['created_at'] = current_time( 'Y-m-d H:i:s' );
							$success = $wpdb->insert( "{$wpdb->prefix}wl_min_attendance", $data );
							if ( ! $success ) {
								throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
							}
						}
					}
				}
				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Attendance saved.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function get_student_attendance() {
		if ( ! current_user_can( WL_MIM_Helper::get_student_capability() ) ) {
			die();
		}
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}

		global $wpdb;

		$student = WL_MIM_StudentHelper::get_student();

		if ( ! $student ) {
			die();
		}

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $student->institute_id );

		if (get_option( 'multi_institute_enable_seprate_enrollment_id', 'yes' )) {
			$student_id = $student->enrollment_id;
		} else {
			$student_id = $student->id;
		}

		WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );

		$custom_duration = isset( $_REQUEST['custom_duration'] ) ? boolval( sanitize_text_field( $_REQUEST['custom_duration'] ) ) : 0;

		if ( $custom_duration ) {
			$duration_from = ( isset( $_REQUEST['duration_from'] ) && ! empty( $_REQUEST['duration_from'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['duration_from'] ) ) ) : null;
			$duration_to   = ( isset( $_REQUEST['duration_to'] ) && ! empty( $_REQUEST['duration_to'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['duration_to'] ) ) ) : null;

			if ( empty( $duration_from ) || empty( $duration_to ) ) {
				ob_start();
				?>
                <strong class="text-danger">
					<?php
					esc_html_e( 'Please provide valid custom duration.', WL_MIM_DOMAIN ); ?>
                </strong>
				<?php
				$json = json_encode( array(
					'element' => ''
				) );
				$html = ob_get_clean();
				wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
			}
		} else {
			$predefined_period = isset( $_REQUEST['predefined_period'] ) ? sanitize_text_field( $_REQUEST['predefined_period'] ) : null;

			if ( ! in_array( $predefined_period, array_keys( WL_MIM_Helper::get_report_period() ) ) ) {
				ob_start();
				?>
                <strong class="text-danger">
					<?php
					esc_html_e( 'Please select valid option.', WL_MIM_DOMAIN ); ?>
                </strong>
				<?php
				$json = json_encode( array(
					'element' => ''
				) );
				$html = ob_get_clean();
				wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
			}
		}

		if ( ! $custom_duration ) {
			if ( $predefined_period == 'today' ) {
				$query = "AND a.attendance_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 day) ";
			} elseif ( $predefined_period == 'yesterday' ) {
				$query = "AND DATE(a.attendance_date) = DATE(NOW() - INTERVAL 1 DAY) ";
			} elseif ( $predefined_period == 'this-week' ) {
				$query = "AND a.attendance_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 1-DAYOFWEEK(CURDATE()) DAY) AND DATE_ADD(CURDATE(), INTERVAL 7-DAYOFWEEK(CURDATE()) DAY) ";
			} elseif ( $predefined_period == 'this-month' ) {
				$query = "AND a.attendance_date BETWEEN DATE_SUB(CURDATE(),INTERVAL (DAY(CURDATE())-1) DAY) AND LAST_DAY(NOW()) ";
			} elseif ( $predefined_period == 'this-year' ) {
				$query = "AND YEAR(a.attendance_date) = YEAR(CURDATE()) ";
			} elseif ( $predefined_period == 'last-year' ) {
				$query = "AND YEAR(a.attendance_date) = YEAR(CURDATE()) - 1 ";
			} else {
				$query = '';
			}
		} else {
			$query = "AND CAST(a.attendance_date AS DATE) BETWEEN '$duration_from' AND '$duration_to' ";
		}

		$query = "SELECT s.ID as student_id, s.first_name, s.last_name,
		COUNT(a.status) as attendance_count,
		COUNT(IF(a.status='p',1, NULL)) as present_count,
		COUNT(IF(a.status='a',1, NULL)) as absent_count
		FROM {$wpdb->prefix}wl_min_attendance as a
		JOIN {$wpdb->prefix}wl_min_students as s
		ON s.ID = a.student_id
		WHERE s.is_deleted = 0
		AND s.ID = {$student->id} " . $query . 'GROUP BY s.ID';

		$row = $wpdb->get_row( $query );

		if (get_option( 'multi_institute_enable_seprate_enrollment_id', 'yes' )) {
			$student_id = $row->enrollment_id;
		} else {
			$student_id = $row->id;
		}
		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix )
		?>
		<ul class="list-group mb-3">
			<li class="list-group-item list-group-flush">
				<span class="font-weight-bold"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></span>:&nbsp;
				<span><?php echo esc_html( $enrollment_id ); ?></span>
			</li>
			<li class="list-group-item list-group-flush">
				<span class="font-weight-bold"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></span>:&nbsp;
				<span><?php echo esc_html( $row->first_name ); ?></span>
			</li>
			<li class="list-group-item list-group-flush">
				<span class="font-weight-bold"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></span>:&nbsp;
				<span><?php echo esc_html( $row->last_name ); ?></span>
			</li>
			<li class="list-group-item list-group-flush">
				<span class="font-weight-bold"><?php esc_html_e( 'Total Attendance Count', WL_MIM_DOMAIN ); ?></span>:&nbsp;
				<span><?php echo esc_html( $row->attendance_count ); ?></span>
			</li>
			<li class="list-group-item list-group-flush">
				<span class="font-weight-bold"><?php esc_html_e( 'Total Present Count', WL_MIM_DOMAIN ); ?></span>:&nbsp;
				<span><?php echo esc_html( $row->present_count ); ?></span>
			</li>
			<li class="list-group-item list-group-flush">
				<span class="font-weight-bold"><?php esc_html_e( 'Total Absent Count', WL_MIM_DOMAIN ); ?></span>:&nbsp;
				<span><?php echo esc_html( $row->absent_count ); ?></span>
			</li>
		</ul>

		<?php
		die();
	}

	/* Check permission to manage noticeboard */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_attendance' ) || ! $institute_id ) {
			die();
		}
	}
}
?>