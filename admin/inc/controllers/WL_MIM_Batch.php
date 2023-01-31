<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

class WL_MIM_Batch {
	/* Get batch data to display on table */
	public static function get_batch_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data        = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );
		$course_data = $wpdb->get_results( "SELECT id, course_name, course_code FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id         = $row->id;
				$batch_code = $row->batch_code;
				$batch_name = $row->batch_name ? $row->batch_name : '-';
				$time_from  = date( "g:i A", strtotime( $row->time_from ) );
				$time_to    = date( "g:i A", strtotime( $row->time_to ) );
				$timing     = "$time_from - $time_to";
				$start_date = date_format( date_create( $row->start_date ), "d-m-Y" );
				$end_date   = date_format( date_create( $row->end_date ), "d-m-Y" );
				$is_acitve  = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$added_on   = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
				$added_by   = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$count_total_students  = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $row->id AND institute_id = $institute_id" );
				$count_active_students = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $row->id AND is_active = 1 AND institute_id = $institute_id" );

				$batch_status = WL_MIM_Helper::get_batch_status( $row->start_date, $row->end_date );

				$course = '-';
				if ( $row->course_id && isset( $course_data[ $row->course_id ] ) ) {
					$course_name = $course_data[ $row->course_id ]->course_name;
					$course_code = $course_data[ $row->course_id ]->course_code;
					$course      = "$course_name ($course_code)";
				}

				$results["data"][] = array(
					'<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html( $batch_code ),
					esc_html( $batch_name ),
					esc_html( $course ),
					esc_html( $timing ),
					esc_html( $start_date ),
					esc_html( $end_date ),
					'<a class="text-primary" href="' . admin_url( 'admin.php?page=multi-institute-management-students' ) . '&status=all&course_id=' . $row->course_id . '&batch_id=' . $id . '">' . $count_total_students . '</a>',
					'<a class="text-primary" href="' . admin_url( 'admin.php?page=multi-institute-management-students' ) . '&status=active&course_id=' . $row->course_id . '&batch_id=' . $id . '">' . $count_active_students . '</a>',
					$batch_status,
					$is_acitve,
					$added_on,
					$added_by,
					'<a class="mr-3" href="#update-batch" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-batch-security="' . wp_create_nonce( "delete-batch-$id" ) . '"delete-batch-id="' . $id . '" class="delete-batch"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new batch */
	public static function add_batch() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-batch'], 'add-batch' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$course_id  = isset( $_POST['course'] ) ? intval( sanitize_text_field( $_POST['course'] ) ) : null;
		$batch_code = isset( $_POST['batch_code'] ) ? sanitize_text_field( $_POST['batch_code'] ) : '';
		$batch_name = isset( $_POST['batch_name'] ) ? sanitize_text_field( $_POST['batch_name'] ) : '';
		$time_from  = ( isset( $_POST['time_from'] ) && ! empty( $_POST['time_from'] ) ) ? date( "H:i:s", strtotime( sanitize_text_field( $_POST['time_from'] ) ) ) : null;
		$time_to    = ( isset( $_POST['time_to'] ) && ! empty( $_POST['time_to'] ) ) ? date( "H:i:s", strtotime( sanitize_text_field( $_POST['time_to'] ) ) ) : null;
		$start_date = ( isset( $_POST['start_date'] ) && ! empty( $_POST['start_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['start_date'] ) ) ) : null;
		$end_date   = ( isset( $_POST['end_date'] ) && ! empty( $_POST['end_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['end_date'] ) ) ) : null;
		$is_active  = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $course_id ) ) {
			$errors['course'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
		}

		if ( empty( $batch_code ) ) {
			$errors['batch_code'] = esc_html__( 'Please provide batch code.', WL_MIM_DOMAIN );
		}

		if ( strlen( $batch_code ) > 255 ) {
			$errors['batch_code'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $batch_name ) > 255 ) {
			$errors['batch_name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( empty( $start_date ) ) {
			$errors['start_date'] = esc_html__( 'Please provide starting date of a batch.', WL_MIM_DOMAIN );
		}

		if ( empty( $end_date ) ) {
			// $errors['end_date'] = esc_html__( 'Please provide ending date of a batch.', WL_MIM_DOMAIN );
			$end_date = '9999-12-31';
		}

		// if ( ! empty( $start_date ) && ( strtotime( $start_date ) >= strtotime( $end_date ) ) ) {
		// 	$errors['end_date'] = esc_html__( 'Ending date must be greater than starting date.', WL_MIM_DOMAIN );
		// }

		if ( empty( $time_from ) ) {
			$errors['time_from'] = esc_html__( 'Please provide start time of a batch.', WL_MIM_DOMAIN );
		}

		if ( empty( $time_to ) ) {
			$errors['time_to'] = esc_html__( 'Please provide end time of a batch.', WL_MIM_DOMAIN );
		}

		$course = $wpdb->get_row( "SELECT duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $course_id AND institute_id = $institute_id" );

		if ( ! $course ) {
			$errors['course'] = esc_html__( 'Please select a valid course.', WL_MIM_DOMAIN );
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count( $course->duration, $course->duration_in );

		// if ( WL_MIM_Helper::get_batch_months( $start_date, $end_date ) != $duration_in_month ) {
		// 	$errors['end_date'] = esc_html__( 'The batch duration should be same as course duration of ', WL_MIM_DOMAIN ) . $duration_in_month . esc_html__( ' month(s)', WL_MIM_DOMAIN );
		// }
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'course_id'    => $course_id,
					'batch_code'   => $batch_code,
					'batch_name'   => $batch_name,
					'time_from'    => $time_from,
					'time_to'      => $time_to,
					'start_date'   => $start_date,
					'end_date'     => $end_date,
					'is_active'    => $is_active,
					'added_by'     => get_current_user_id(),
					'institute_id' => $institute_id
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_batches", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Batch added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch batch to update */
	public static function fetch_batch() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		$wlim_active_courses = WL_MIM_Helper::get_active_courses();

		ob_start(); ?>
        <form id="wlim-update-batch-form">
			<?php $nonce = wp_create_nonce( "update-batch-$id" ); ?>
            <input type="hidden" name="update-batch-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
            <div class="form-group">
                <label for="wlim-batch-course_update" class="col-form-label">* <?php esc_html_e( "Course", WL_MIM_DOMAIN ); ?>:</label>
                <select name="course" class="form-control selectpicker" id="wlim-batch-course_update">
                    <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?> --------</option>
					<?php
					if ( count( $wlim_active_courses ) > 0 ) {
						foreach ( $wlim_active_courses as $active_course ) { ?>
                            <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?></option>
							<?php
						}
					} ?>
                </select>
            </div>
            <div class="row">
                <div class="col form-group">
                    <label for="wlim-batch-batch_code_update" class="col-form-label">* <?php esc_html_e( 'Batch Code', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="batch_code" type="text" class="form-control" id="wlim-batch-batch_code_update" placeholder="<?php esc_html_e( "Batch Code", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->batch_code ); ?>">
                </div>
                <div class="col form-group">
                    <label for="wlim-batch-batch_name_update" class="col-form-label"><?php esc_html_e( 'Batch Name', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="batch_name" type="text" class="form-control" id="wlim-batch-batch_name_update" placeholder="<?php esc_html_e( "Batch Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->batch_name ); ?>">
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="wlim-batch-time_from_update" class="col-form-label">* <?php esc_html_e( 'Timing From', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="time_from" type="text" class="form-control wlim-batch-time_from_update" id="wlim-batch-time_from_update" placeholder="<?php esc_html_e( "Timing From", WL_MIM_DOMAIN ); ?>" value="<?php echo date( "g:i A", strtotime( $row->time_from ) ); ?>">
                </div>
                <div class="col-sm-6 form-group">
                    <label for="wlim-batch-time_to_update" class="col-form-label">* <?php esc_html_e( 'Timing To', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="time_to" type="text" class="form-control wlim-batch-time_to_update" id="wlim-batch-time_to_update" placeholder="<?php esc_html_e( "Timing To", WL_MIM_DOMAIN ); ?>" value="<?php echo date( "g:i A", strtotime( $row->time_to ) ); ?>">
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-6 form-group">
                    <label for="wlim-batch-start_date_update" class="col-form-label">* <?php esc_html_e( 'Starting Date', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="start_date" type="text" class="form-control wlim-batch-start_date_update" id="wlim-batch-start_date_update" placeholder="<?php esc_html_e( "Starting Date", WL_MIM_DOMAIN ); ?>">
                </div>
                <div class="col-sm-6 form-group">
                    <label for="wlim-batch-end_date_update" class="col-form-label"> <?php esc_html_e( 'Ending Date', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="end_date" type="text" class="form-control wlim-batch-end_date_update" id="wlim-batch-end_date_update" placeholder="<?php esc_html_e( "Ending Date", WL_MIM_DOMAIN ); ?>">
                </div>
            </div>
            <hr>
            <div class="row" id="wlim-batch-batch_status">
                <div class="col">
                    <label class="col-form-label pb-0"><?php esc_html_e( 'Batch Status', WL_MIM_DOMAIN ); ?>:</label>
                    <div class="card mb-3 mt-2">
                        <div class="card-block">
							<?php echo WL_MIM_Helper::get_batch_status( $row->start_date, $row->end_date ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-check pl-0">
                <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-batch-is_active_update" <?php echo boolval( $row->is_active ) ? "checked" : ""; ?>>
                <label class="form-check-label" for="wlim-batch-is_active_update">
					<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                </label>
            </div>
            <input type="hidden" name="batch_id" value="<?php echo esc_attr( $row->id ); ?>">
        </form>
		<?php $html               = ob_get_clean();
		$wlim_start_date_selector = '.wlim-batch-start_date_update';
		$wlim_end_date_selector   = '.wlim-batch-end_date_update';
		$wlim_time_from_selector  = '.wlim-batch-time_from_update';
		$wlim_time_to_selector    = '.wlim-batch-time_to_update';

		$json = json_encode( array(
			'wlim_start_date_selector' => esc_attr( $wlim_start_date_selector ),
			'wlim_end_date_selector'   => esc_attr( $wlim_end_date_selector ),
			'wlim_time_from_selector'  => esc_attr( $wlim_time_from_selector ),
			'wlim_time_to_selector'    => esc_attr( $wlim_time_to_selector ),
			'course_id'                => esc_attr( $row->course_id ),
			'start_date'               => esc_attr( $row->start_date ),
			'end_date'                 => esc_attr( $row->end_date )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update batch */
	public static function update_batch() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['batch_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-batch-$id"], "update-batch-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$course_id  = isset( $_POST['course'] ) ? intval( sanitize_text_field( $_POST['course'] ) ) : null;
		$batch_code = isset( $_POST['batch_code'] ) ? sanitize_text_field( $_POST['batch_code'] ) : '';
		$batch_name = isset( $_POST['batch_name'] ) ? sanitize_text_field( $_POST['batch_name'] ) : '';
		$time_from  = ( isset( $_POST['time_from'] ) && ! empty( $_POST['time_from'] ) ) ? date( "H:i:s", strtotime( sanitize_text_field( $_POST['time_from'] ) ) ) : null;
		$time_to    = ( isset( $_POST['time_to'] ) && ! empty( $_POST['time_to'] ) ) ? date( "H:i:s", strtotime( sanitize_text_field( $_POST['time_to'] ) ) ) : null;
		$start_date = ( isset( $_POST['start_date'] ) && ! empty( $_POST['start_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['start_date'] ) ) ) : null;
		$end_date   = ( isset( $_POST['end_date'] ) && ! empty( $_POST['end_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['end_date'] ) ) ) : null;
		$is_active  = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $course_id ) ) {
			$errors['course'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
		}

		if ( empty( $batch_code ) ) {
			$errors['batch_code'] = esc_html__( 'Please provide batch code.', WL_MIM_DOMAIN );
		}

		if ( strlen( $batch_code ) > 255 ) {
			$errors['batch_code'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $batch_name ) > 255 ) {
			$errors['batch_name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( empty( $start_date ) ) {
			$errors['start_date'] = esc_html__( 'Please provide starting date of a batch.', WL_MIM_DOMAIN );
		}
		// var_dump($start_date); die;

		if ( empty( $end_date ) ) {
			// $errors['end_date'] = esc_html__( 'Please provide ending date of a batch.', WL_MIM_DOMAIN );
			$end_date = '9999-12-31';
		}

		// if ( ! empty( $start_date ) && ( strtotime( $start_date ) >= strtotime( $end_date ) ) ) {
		// 	$errors['end_date'] = esc_html__( 'Ending date must be greater than starting date.', WL_MIM_DOMAIN );
		// }

		if ( empty( $time_from ) ) {
			$errors['time_from'] = esc_html__( 'Please provide start time of a batch.', WL_MIM_DOMAIN );
		}

		if ( empty( $time_to ) ) {
			$errors['time_to'] = esc_html__( 'Please provide end time of a batch.', WL_MIM_DOMAIN );
		}

		$course = $wpdb->get_row( "SELECT duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $course_id" );

		if ( ! $course ) {
			$errors['course'] = esc_html__( 'Please select a valid course.', WL_MIM_DOMAIN );
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count( $course->duration, $course->duration_in );

		// if ( WL_MIM_Helper::get_batch_months( $start_date, $end_date ) != $duration_in_month ) {
		// 	$errors['end_date'] = esc_html__( 'The batch duration should be same as course duration of ', WL_MIM_DOMAIN ) . $duration_in_month . esc_html__( ' month(s)', WL_MIM_DOMAIN );
		// }
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'course_id'  => $course_id,
					'batch_code' => $batch_code,
					'batch_name' => $batch_name,
					'time_from'  => $time_from,
					'time_to'    => $time_to,
					'start_date' => $start_date,
					'end_date'   => $end_date,
					'is_active'  => $is_active,
					'updated_at' => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_batches", $data, array(
					'is_deleted'   => 0,
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Batch updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete batch */
	public static function delete_batch() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-batch-$id"], "delete-batch-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->update( "{$wpdb->prefix}wl_min_batches",
				array(
					'is_deleted' => 1,
					'deleted_at' => date( 'Y-m-d H:i:s' )
				), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
			);
			if ( ! $success ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Batch removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* Check permission to manage batch */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_batches' ) || ! $institute_id ) {
			die();
		}
	}
}
?>