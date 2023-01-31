<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

class WL_MIM_Result {
	/* Get exam data to display on table */
	public static function get_exam_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id           = $row->id;
				$exam_code    = $row->exam_code;
				$exam_title   = $row->exam_title ? $row->exam_title : '-';
				$exam_date    = date_format( date_create( $row->exam_date ), "d-m-Y" );
				$is_published = $row->is_published ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$published_at = $row->published_at ? date_format( date_create( $row->published_at ), "d-m-Y g:i A" ) : '-';
				$added_on     = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
				$added_by     = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$results["data"][] = array(
					'<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html( $exam_code ),
					esc_html( $exam_title ),
					esc_html( $exam_date ),
					esc_html( $is_published ),
					esc_html( $published_at ),
					esc_html( $added_on ),
					esc_html( $added_by ),
					'<a class="mr-3" href="#update-exam" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-exam-security="' . wp_create_nonce( "delete-exam-$id" ) . '"delete-exam-id="' . $id . '" class="delete-exam"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new exam */
	public static function add_exam() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-exam'], 'add-exam' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$exam_code    = isset( $_POST['exam_code'] ) ? sanitize_text_field( $_POST['exam_code'] ) : '';
		$exam_title   = isset( $_POST['exam_title'] ) ? sanitize_text_field( $_POST['exam_title'] ) : '';
		$exam_date    = ( isset( $_POST['exam_date'] ) && ! empty( $_POST['exam_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['exam_date'] ) ) ) : null;
		$marks        = ( isset( $_POST['marks'] ) && is_array( $_POST['marks'] ) ) ? $_POST['marks'] : null;
		$notes        = ( isset( $_POST['notes'] ) && is_array( $_POST['notes'] ) ) ? $_POST['notes'] : null;
		$is_published = isset( $_POST['is_published'] ) ? boolval( sanitize_text_field( $_POST['is_published'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $exam_code ) ) {
			$errors['exam_code'] = esc_html__( 'Please provide exam code.', WL_MIM_DOMAIN );
		}

		if ( strlen( $exam_code ) > 191 ) {
			$errors['exam_code'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		if ( empty( $exam_title ) ) {
			$errors['exam_title'] = esc_html__( 'Please provide exam title.', WL_MIM_DOMAIN );
		}

		if ( empty( $exam_date ) ) {
			$errors['exam_date'] = esc_html__( 'Please specify exam date.', WL_MIM_DOMAIN );
		}

		if ( empty( $marks ) ) {
			wp_send_json_error( esc_html__( 'Please specify subjects and maximum marks.', WL_MIM_DOMAIN ) );
		}

		if ( ! array_key_exists( 'subject', $marks ) || ! array_key_exists( 'maximum', $marks ) ) {
			wp_send_json_error( esc_html__( 'Invalid subjects or maximum marks.', WL_MIM_DOMAIN ) );
		}

		if ( count( $marks['subject'] ) < 1 || ( count( $marks['subject'] ) != count( $marks['maximum'] ) ) ) {
			wp_send_json_error( esc_html__( 'Invalid subjects or maximum marks.', WL_MIM_DOMAIN ) );
		}

		if ( array_search( '', $marks['subject'] ) !== false ) {
			wp_send_json_error( esc_html__( 'Please specify subject.', WL_MIM_DOMAIN ) );
		}

		if ( is_array( $notes ) && count( $notes ) ) {
			foreach( $notes as $key => $note ) {
				$note[ $key ] = sanitize_text_field( $note );
			}
		} else {
			$notes = array();
		}

		foreach ( $marks['maximum'] as $key => $value ) {
			if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
				wp_send_json_error( esc_html__( 'Please provide a valid maximum mark for a subject.', WL_MIM_DOMAIN ) );
			} else {
				$marks['maximum'][ $key ] = isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0;
				
			}
		}
		foreach ( $marks['maximum_p'] as $key => $value ) {
			if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
				wp_send_json_error( esc_html__( 'Please provide a valid maximum mark for a subject.', WL_MIM_DOMAIN ) );
			} else {
				$marks['maximum_p'][ $key ] = isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0;
			}
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND exam_code = '$exam_code' AND institute_id = $institute_id" );

		if ( $count ) {
			$errors['exam_code'] = esc_html__( 'Exam code already exists.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$marks = serialize( $marks );
				$notes = serialize( $notes );

				$published_at = null;
				if ( $is_published ) {
					$published_at = date( 'Y-m-d H:i:s' );
				}

				$data = array(
					'exam_code'    => $exam_code,
					'exam_title'   => $exam_title,
					'exam_date'    => $exam_date,
					'marks'        => $marks,
					'notes'        => $notes,
					'is_published' => $is_published,
					'published_at' => $published_at,
					'added_by'     => get_current_user_id(),
					'institute_id' => $institute_id
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_exams", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array(
					'message' => esc_html__( 'Exam added successfully.', WL_MIM_DOMAIN ),
					'reload'  => true
				) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch exam to update */
	public static function fetch_exam() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		$results        = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND exam_id = $id AND institute_id = $institute_id" );
		$results_exists = false;
		if ( count( $results ) ) {
			$results_exists = true;
		}
		?><?php $nonce = wp_create_nonce( "update-exam-$id" ); ?><?php ob_start(); ?>
        <input type="hidden" name="update-exam-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-update-exam">
        <div class="form-group">
            <label for="wlim-exam-exam_code_update" class="col-form-label">* <?php esc_html_e( 'Exam Code', WL_MIM_DOMAIN ); ?>:</label>
            <input name="exam_code" type="text" class="form-control" id="wlim-exam-exam_code_update" placeholder="<?php esc_html_e( "Exam Code", WL_MIM_DOMAIN ); ?>" min="0" step="any" value="<?php echo esc_attr( $row->exam_code ); ?>">
        </div>
        <div class="form-group">
            <label for="wlim-exam-exam_title_update" class="col-form-label">* <?php esc_html_e( 'Exam Title', WL_MIM_DOMAIN ); ?>:</label>
            <input name="exam_title" type="text" class="form-control" id="wlim-exam-exam_title_update" placeholder="<?php esc_html_e( "Exam Title", WL_MIM_DOMAIN ); ?>" min="0" step="any" value="<?php echo esc_attr( $row->exam_title ); ?>">
        </div>
        <div class="form-group">
            <label for="wlim-exam-exam_date_update" class="col-form-label">* <?php esc_html_e( 'Exam Date', WL_MIM_DOMAIN ); ?>:</label>
            <input name="exam_date" type="text" class="form-control wlim-exam-exam_date_update" id="wlim-exam-exam_date" placeholder="<?php esc_html_e( "Exam Date", WL_MIM_DOMAIN ); ?>">
        </div>
        <label class="col-form-label">* <?php esc_html_e( 'Exam Marks', WL_MIM_DOMAIN ); ?>:</label>
        <div class="exam_marks_box">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
                    <th><?php esc_html_e( 'Maximum Theory Marks', WL_MIM_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Maximum Practical Marks', WL_MIM_DOMAIN ); ?></th>
					<?php
					if ( ! $results_exists ) { ?>
                        <th></th>
						<?php
					} ?>
                </tr>
                </thead>
                <tbody class="exam_marks_rows exam_marks_table">
				<?php
				$marks = unserialize( $row->marks );
				foreach ( $marks['subject'] as $key => $value ) { ?>
                    <tr>
                        <td>
                            <input type="text" name="marks[subject][]" class="form-control" placeholder="<?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $marks['subject'][ $key ] ); ?>">
                        </td>
                        <td>
                            <input<?php echo ( $results_exists ) ? " disabled " : " "; ?>type="number" min="0"
                            step="any" name="marks[maximum][]" class="form-control"
                            placeholder="<?php esc_html_e( 'Maximum Marks', WL_MIM_DOMAIN ); ?>"
                            value="<?php echo esc_attr( $marks['maximum'][ $key ] ); ?>">
                        </td>
						<td>
                            <input<?php echo ( $results_exists ) ? " disabled " : " "; ?>type="number" min="0"
                            step="any" name="marks[maximum_p][]" class="form-control"
                            placeholder="<?php esc_html_e( 'Maximum Marks', WL_MIM_DOMAIN ); ?>"
                            value="<?php echo esc_attr( $marks['maximum_p'][ $key ] ); ?>">
                        </td>
						<?php
						if ( ! $results_exists ) { ?>
                            <td>
                                <button class="remove_row btn btn-danger btn-sm" type="button">
                                    <i class="fa fa-remove" aria-hidden="true"></i>
                                </button>
                            </td>
							<?php
						} ?>
                    </tr>
					<?php
				} ?>
                </tbody>
            </table>
			<?php
			if ( ! $results_exists ) { ?>
                <div class="text-right">
                    <button type="button" class="add-more-exam-marks btn btn-success btn-sm"><?php esc_html_e( 'Add More', WL_MIM_DOMAIN ); ?></button>
                </div>
				<?php
			} ?>
        </div>
        <label class="col-form-label">* <?php esc_html_e( 'Exam Marks', WL_MIM_DOMAIN ); ?>:</label>
        <div class="exam_notes_box">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Note', WL_MIM_DOMAIN ); ?></th>
					<?php
					if ( ! $results_exists ) { ?>
                        <th></th>
						<?php
					} ?>
                </tr>
                </thead>
                <tbody class="exam_notes_rows exam_notes_table">
				<?php
				$notes = unserialize( $row->notes );
                if ( is_array( $notes ) && count( $notes ) ) {
    				foreach ( $notes as $key => $value ) { ?>
                        <tr>
                            <td>
                                <input type="text" name="notes[]" class="form-control" placeholder="<?php esc_html_e( 'Add Note', WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $notes[ $key ] ); ?>">
                            </td>
    						<?php
    						if ( ! $results_exists ) { ?>
                                <td>
                                    <button class="remove_row btn btn-danger btn-sm" type="button">
                                        <i class="fa fa-remove" aria-hidden="true"></i>
                                    </button>
                                </td>
    							<?php
    						} ?>
                        </tr>
    					<?php
    				}
                }
                ?>
                </tbody>
            </table>
            <div class="text-right">
                <button type="button" class="add-more-exam-notes btn btn-success btn-sm"><?php esc_html_e( 'Add More', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
        <div class="form-check pl-0">
            <input name="is_published" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-exam-is_published_update" <?php echo boolval( $row->is_published ) ? "checked" : ""; ?>>
            <label class="form-check-label" for="wlim-exam-is_published_update">
				<?php esc_html_e( 'Is Published?', WL_MIM_DOMAIN ); ?>
            </label>
        </div><input type="hidden" name="exam_id" value="<?php echo esc_attr( $row->id ); ?>">
		<?php $html = ob_get_clean();

		$json = json_encode( array(
			'exam_date_exist' => boolval( $row->exam_date ),
			'exam_date'       => esc_attr( $row->exam_date )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update exam */
	public static function update_exam() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['exam_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-exam-$id"], "update-exam-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$exam_code    = isset( $_POST['exam_code'] ) ? sanitize_text_field( $_POST['exam_code'] ) : '';
		$exam_title   = isset( $_POST['exam_title'] ) ? sanitize_text_field( $_POST['exam_title'] ) : '';
		$exam_date    = ( isset( $_POST['exam_date'] ) && ! empty( $_POST['exam_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['exam_date'] ) ) ) : null;
		$marks        = ( isset( $_POST['marks'] ) && is_array( $_POST['marks'] ) ) ? $_POST['marks'] : null;
		$notes        = ( isset( $_POST['notes'] ) && is_array( $_POST['notes'] ) ) ? $_POST['notes'] : null;
		$is_published = isset( $_POST['is_published'] ) ? boolval( sanitize_text_field( $_POST['is_published'] ) ) : 0;

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		$results        = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND exam_id = $id AND institute_id = $institute_id" );
		$results_exists = false;
		if ( count( $results ) ) {
			$results_exists = true;
		}

		/* Validations */
		$errors = array();
		if ( empty( $exam_code ) ) {
			$errors['exam_code'] = esc_html__( 'Please provide exam code.', WL_MIM_DOMAIN );
		}

		if ( strlen( $exam_code ) > 191 ) {
			$errors['exam_code'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		if ( empty( $exam_title ) ) {
			$errors['exam_title'] = esc_html__( 'Please provide exam title.', WL_MIM_DOMAIN );
		}

		if ( empty( $exam_date ) ) {
			$errors['exam_date'] = esc_html__( 'Please specify exam date.', WL_MIM_DOMAIN );
		}

		if ( is_array( $notes ) && count( $notes ) ) {
			foreach( $notes as $key => $note ) {
				$note[ $key ] = sanitize_text_field( $note );
			}
		} else {
			$notes = array();
		}

		if ( $results_exists ) {
			if ( empty( $marks ) ) {
				wp_send_json_error( esc_html__( 'Please specify subjects.', WL_MIM_DOMAIN ) );
			}

			if ( ! array_key_exists( 'subject', $marks ) ) {
				wp_send_json_error( esc_html__( 'Invalid subjects.', WL_MIM_DOMAIN ) );
			}

			if ( count( $marks['subject'] ) < 1 ) {
				wp_send_json_error( esc_html__( 'Invalid subjects.', WL_MIM_DOMAIN ) );
			}

			if ( array_search( '', $marks['subject'] ) !== false ) {
				wp_send_json_error( esc_html__( 'Please specify subject.', WL_MIM_DOMAIN ) );
			}

			$maximum_marks    = unserialize( $row->marks );
			$marks['maximum'] = $maximum_marks['maximum'];
		} else {
			if ( empty( $marks ) ) {
				wp_send_json_error( esc_html__( 'Please specify subjects and maximum marks.', WL_MIM_DOMAIN ) );
			}

			if ( ! array_key_exists( 'subject', $marks ) || ! array_key_exists( 'maximum', $marks ) ) {
				wp_send_json_error( esc_html__( 'Invalid subjects or maximum marks.', WL_MIM_DOMAIN ) );
			}

			if ( count( $marks['subject'] ) < 1 || ( count( $marks['subject'] ) != count( $marks['maximum'] ) ) ) {
				wp_send_json_error( esc_html__( 'Invalid subjects or maximum marks.', WL_MIM_DOMAIN ) );
			}

			if ( array_search( '', $marks['subject'] ) !== false ) {
				wp_send_json_error( esc_html__( 'Please specify subject.', WL_MIM_DOMAIN ) );
			}

			foreach ( $marks['maximum'] as $key => $value ) {
				if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
					wp_send_json_error( esc_html__( 'Please provide a valid maximum mark for a subject.', WL_MIM_DOMAIN ) );
				} else {
					$marks['maximum'][ $key ] = isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0;
				}
			}
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND exam_code = '$exam_code' AND id != $id AND institute_id = $institute_id" );

		if ( $count ) {
			$errors['exam_code'] = esc_html__( 'Exam code already exists.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$marks = serialize( $marks );
				$notes = serialize( $notes );

				$data = array(
					'exam_code'    => $exam_code,
					'exam_title'   => $exam_title,
					'exam_date'    => $exam_date,
					'marks'        => $marks,
					'notes'        => $notes,
					'is_published' => $is_published,
					'updated_at'   => date( 'Y-m-d H:i:s' )
				);

				if ( ! $is_published ) {
					$data['published_at'] = null;
				} elseif ( ! $row->published_at ) {
					$data['published_at'] = date( 'Y-m-d H:i:s' );
				}

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_exams", $data, array(
					'is_deleted'   => 0,
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array(
					'message' => esc_html__( 'Exam updated successfully.', WL_MIM_DOMAIN ),
					'reload'  => true
				) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete exam */
	public static function delete_exam() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-exam-$id"], "delete-exam-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->update( "{$wpdb->prefix}wl_min_exams",
				array(
					'is_deleted' => 1,
					'deleted_at' => date( 'Y-m-d H:i:s' )
				), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
			);
			if ( $success === false ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$sql = "DELETE FROM {$wpdb->prefix}wl_min_results WHERE exam_id = $id AND institute_id = $institute_id";

			$success = $wpdb->query( $sql );

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array(
				'message' => esc_html__( 'Exam removed successfully.', WL_MIM_DOMAIN ),
				'reload'  => true
			) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* Fetch course batches */
	public static function fetch_course_batches() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$course_id = intval( sanitize_text_field( $_POST['id'] ) );
		$row       = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id = $course_id AND institute_id = $institute_id" );

		if ( ! $row ) {
			die();
		}

		$batches = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND course_id = $course_id AND institute_id = $institute_id ORDER BY id DESC" );
		ob_start();
		if ( count( $batches ) > 0 ) {
			?>
            <div class="form-group pt-3">
                <label for="wlim-result-batch" class="col-form-label">* <?php esc_html_e( "Batch", WL_MIM_DOMAIN ); ?>:</label>
                <select name="batch" class="form-control selectpicker" id="wlim-result-batch">
                    <option value="">-------- <?php esc_html_e( "Select a Batch", WL_MIM_DOMAIN ); ?> --------</option>
					<?php
					foreach ( $batches as $batch ) {
						$is_batch_ended = WL_MIM_Helper::is_batch_ended( $batch->start_date, $batch->end_date );
						if ( ! $is_batch_ended ) {
							$time_from  = date( "g:i A", strtotime( $batch->time_from ) );
							$time_to    = date( "g:i A", strtotime( $batch->time_to ) );
							$timing     = "$time_from - $time_to";
							$batch_info = $batch->batch_code;
							if ( $batch->batch_name ) {
								$batch_info .= " ( $batch->batch_name )";
							} ?>
                            <option value="<?php echo esc_attr( $batch->id ); ?>"><?php echo esc_html( $batch_info ) . " ( " . esc_html( $timing ) . " ) ( " . WL_MIM_Helper::get_batch_status( $batch->start_date, $batch->end_date ) . " )"; ?></option>
							<?php
						}
					} ?>
                </select>
            </div>
            <div id="wlim-add-result-batch-students"></div>
			<?php
			$json = json_encode( array(
				'element' => '#wlim-result-batch'
			) );
		} else { ?>
            <div class="text-danger pt-3 pb-3 border-bottom"><?php esc_html_e( "Batches not found.", WL_MIM_DOMAIN ); ?></div>
			<?php
		}
		$json = json_encode( array(
			'element' => ''
		) );

		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Fetch batch students */
	public static function fetch_batch_students() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings( $institute_id );

		$batch_id = intval( sanitize_text_field( $_POST['id'] ) );
		$exam_id  = intval( sanitize_text_field( $_POST['exam_id'] ) );
		$row      = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $batch_id AND institute_id = $institute_id" );

		if ( ! $row ) {
			die();
		}

		if ( ! $exam_id ) { ?>
            <span class="text-danger"><?php esc_html_e( "Please select an exam.", WL_MIM_DOMAIN ); ?></span>
			<?php
			die();
		}

		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );

		if ( ! $exam ) { ?>
            <span class="text-danger"><?php esc_html_e( "Exam not found.", WL_MIM_DOMAIN ); ?></span>
			<?php
			die();
		}

		$marks = unserialize( $exam->marks );

		$students = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $batch_id AND institute_id = $institute_id ORDER BY id ASC" );
		if ( count( $students ) > 0 ) { ?>
            <ul class="list-group mt-4">
				<?php
				foreach ( $students as $key => $student ) {
					$id            = $student->id;
					if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
						$student_id = $student->enrollment_id;
					} else {
						$student_id = $student->id;
					}
					$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
					$name          = $student->first_name;
					if ( $student->last_name ) {
						$name .= " $student->last_name";
					}
					$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND institute_id = $institute_id AND exam_id = " . $exam->id . " AND student_id = " . $id );
					?>
                    <li class="border p-2">
                        <strong><?php echo esc_attr( $key + 1 ); ?>. </strong><span class="text-dark"><?php echo "$name ($enrollment_id)"; ?></span>
                        <span class="ml-3">
					<a class="btn btn-success btn-sm" role="button" href="#wlim-result-add-student-marks-<?php echo esc_attr( $key ); ?>" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="<?php echo esc_attr( $id ); ?>" data-exam_id="<?php echo esc_attr( $exam_id ); ?>">
						<?php
						if ( $result ) {
							esc_html_e( "Update Marks", WL_MIM_DOMAIN );
						} else {
							esc_html_e( "Add Marks", WL_MIM_DOMAIN );
						} ?>
					</a>
				</span>
                        <!-- add student marks modal -->
                        <div class="modal fade wlim-result-add-student-marks" id="wlim-result-add-student-marks-<?php echo esc_attr( $key ); ?>" tabindex="-1" role="dialog" aria-labelledby="wlim-result-add-student-marks-label-<?php echo esc_attr( $key ); ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered wlim-result-add-student-marks-dialog" id="wlim-result-add-student-marks-dialog-<?php echo esc_attr( $key ); ?>" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title w-100 text-center" id="wlim-result-add-student-marks-label-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Add Student Marks', WL_MIM_DOMAIN ); ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body pr-4 pl-4">
                                        <div class="row">
                                            <div class="col">
                                                <label class="col-form-label"><?php esc_html_e( 'Student Details', WL_MIM_DOMAIN ); ?>:</label>
                                                <ul class="list-group">
                                                    <li class="list-group-item">
														<span class="text-dark"><?php esc_html_e( "Enrollment ID", WL_MIM_DOMAIN ); ?> : </span><strong><?php echo esc_attr( $enrollment_id ); ?></strong>
                                                    </li>
                    								<?php if ( $general_enable_roll_number ) { ?>
                                                    <li class="list-group-item">
														<span class="text-dark"><?php esc_html_e( "Roll Number", WL_MIM_DOMAIN ); ?> : </span><strong><?php echo esc_attr( $student->roll_number ); ?></strong>
                                                    </li>
													<?php } ?>
                                                    <li class="list-group-item">
														<span class="text-dark"><?php esc_html_e( "Name", WL_MIM_DOMAIN ); ?> : </span><strong><?php echo esc_attr( $name ); ?></strong>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                      
										<div class="row">
											<div class="col">
												<div class="mb-3 mt-2">
													<label class="col-form-label"><strong>* <?php esc_html_e( 'Exam Marks', WL_MIM_DOMAIN ); ?>:</strong></label>
													<div class="exam_marks_obtained_box">
														<table class="table table-bordered">
															<thead>
															<tr>
																<th><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
																<th><?php esc_html_e( 'Maximum Theory Marks', WL_MIM_DOMAIN ); ?></th>
																<th><?php esc_html_e( 'Maximum Practical Marks', WL_MIM_DOMAIN ); ?></th>
																<th><?php esc_html_e( 'Marks Obtained Theory', WL_MIM_DOMAIN ); ?></th>
																<th><?php esc_html_e( 'Marks Obtained Practical', WL_MIM_DOMAIN ); ?></th>
																<!-- <th></th> -->
															</tr>
															</thead>
															<tbody class="exam_marks_obtained_rows exam_marks_obtained_table">
															<?php
															$marks_obtained   = null;
															$marks_obtained_p = null;
															if ( $result ) {
																$marks_obtained   = unserialize( $result->marks );
																$marks_obtained_p = unserialize( $result->marks_p );
															}
															$total_maximum_marks  = 0;
															$total_marks_obtained = 0;
															foreach ( $marks['subject'] as $subject_key => $subject_value ) {
																$marks_obtained_in_subject = 0;
																$marks_obtained_in_subject_p = 0;
																if ( ! empty( $marks_obtained ) ) {
																	$marks_obtained_in_subject   = $marks_obtained[ $subject_key ];
																	$marks_obtained_in_subject_p = $marks_obtained_p[ $subject_key ];
																}
																$total_maximum_marks    += $marks['maximum'][ $subject_key ];
																$total_maximum_marks_p  += $marks['maximum_p'][ $subject_key ];
																$total_marks_obtained   += $marks_obtained_in_subject;
																$total_marks_obtained_p += $marks_obtained_in_subject_p;
																?>
																<tr>
																	<td>
																		<span class="text-dark"><?php echo esc_attr( $subject_value ); ?></span>
																	</td>
																	<td>
																		<span class="text-dark"><?php echo esc_attr( $marks['maximum'][ $subject_key ] ); ?></span>
																	</td> 
																	
																	<td>
																		<span class="text-dark"><?php echo esc_attr( $marks['maximum_p'][ $subject_key ] ); ?></span>
																	</td>
																	<td>
																		<input required type="number" min="0" step="any" name="marks_obtained[]" class="form-control" placeholder="<?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $marks_obtained_in_subject ); ?>">
																	</td>
																	<td>
																		<input required type="number" min="0" step="any" name="marks_obtained_p[]" class="form-control" placeholder="<?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $marks_obtained_in_subject_p ); ?>">
																	</td>
																</tr>
																<?php
															} ?>
															<tr>
																<th><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></th>
																<th><?php echo esc_attr( $total_maximum_marks ); ?></th>
																<th><?php echo esc_attr( $total_maximum_marks_p ); ?></th>
																<th><?php echo esc_attr( $total_marks_obtained ); ?></th>
																<th><?php echo esc_attr( $total_marks_obtained_p ); ?></th>
															</tr>
															<tr>
																<th></th>
																<th></th>
																<th><?php esc_html_e( 'Percentage', WL_MIM_DOMAIN ); ?></th>
																<th><?php echo number_format( max( floatval( ( $total_marks_obtained / $total_maximum_marks ) * 100 ), 0 ), 2, '.', '' ); ?>%</th>
																<th><?php echo number_format( max( floatval( ( $total_marks_obtained_p / $total_maximum_marks_p ) * 100 ), 0 ), 2, '.', '' ); ?>%</th>
															</tr>
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                                        <button type="button" class="btn btn-success" data-dismiss="modal"><?php esc_html_e( 'Done', WL_MIM_DOMAIN ); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end - add student marks modal -->
                    </li>
					<?php
				} ?>
            </ul>
			<?php
		} else { ?>
            <div class="text-danger pt-3 pb-3 border-bottom">
				<?php esc_html_e( "Students not found.", WL_MIM_DOMAIN ); ?>
			</div>
			<?php
		}
		die();
	}

	/* Save result */
	public static function save_result() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['save-result'], 'save-result' ) ) {
			die();
		}

		$course_id      = intval( sanitize_text_field( $_POST['course'] ) );
		$batch_id       = intval( sanitize_text_field( $_POST['batch'] ) );
		$exam_id        = intval( sanitize_text_field( $_POST['exam'] ) );
		$marks_obtained = ( isset( $_POST['marks_obtained'] ) && is_array( $_POST['marks_obtained'] ) ) ? $_POST['marks_obtained'] : null;
		$marks_obtained_p = ( isset( $_POST['marks_obtained_p'] ) && is_array( $_POST['marks_obtained_p'] ) ) ? $_POST['marks_obtained_p'] : null;

		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		/* Validations */
		$errors = array();
		if ( empty( $course_id ) ) {
			wp_send_json_error( esc_html__( 'Please select a course.', WL_MIM_DOMAIN ) );
		}

		if ( empty( $batch_id ) ) {
			wp_send_json_error( esc_html__( 'Please select a batch.', WL_MIM_DOMAIN ) );
		}

		if ( empty( $exam_id ) ) {
			wp_send_json_error( esc_html__( 'Please select an exam.', WL_MIM_DOMAIN ) );
		}

		$course = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id = $course_id AND institute_id = $institute_id" );
		if ( ! $course ) {
			$errors['course'] = esc_html__( 'Please select a valid course.', WL_MIM_DOMAIN );
		}

		$batch = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $batch_id AND course_id = $course_id AND institute_id = $institute_id" );
		if ( ! $batch ) {
			$errors['batch'] = esc_html__( 'Please select a valid batch.', WL_MIM_DOMAIN );
		}

		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );
		if ( ! $exam ) {
			$errors['exam'] = esc_html__( 'Please select a valid exam.', WL_MIM_DOMAIN );
		}

		$marks = unserialize( $exam->marks );

		if ( empty( $marks_obtained ) ) {
			wp_send_json_error( esc_html__( 'Please specify marks obtained.', WL_MIM_DOMAIN ) );
		}

		$students = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $batch_id AND institute_id = $institute_id ORDER BY id ASC" );
		if ( ! count( $students ) ) {
			wp_send_json_error( esc_html__( 'There is no student in this batch.', WL_MIM_DOMAIN ) );
		}

		$result_data = array();
		foreach ( $students as $key => $student ) {
			if ( ! array_key_exists( $student->id, $marks_obtained ) ) {
				if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
					$student_id = $student->enrollment_id;
				} else {
					$student_id = $student->id;
				}
				wp_send_json_error( esc_html__( 'Invalid marks obtained for student with enrollment ID: ', WL_MIM_DOMAIN ) . WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ) );
			} else {
				foreach ( $marks_obtained[ $student->id ] as $key => $value ) {
					if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
						wp_send_json_error( esc_html__( 'Please provide a valid marks obtained.', WL_MIM_DOMAIN ) );
					} else {
						if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
							$student_id = $student->enrollment_id;
						} else {
							$student_id = $student->id;
						}
						if ( $marks['maximum'][ $key ] < $value ) {
							wp_send_json_error( esc_html__( 'Marks obtained exceeded maximum marks for enrollment ID: ', WL_MIM_DOMAIN ) . WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ) );
						}
						$marks_obtained[ $student->id ][ $key ] = isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0;
					}
				}
				foreach ( $marks_obtained_p[ $student->id ] as $key => $value ) {
					if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
						wp_send_json_error( esc_html__( 'Please provide a valid marks obtained.', WL_MIM_DOMAIN ) );
					} else {
						if ( $marks['maximum'][ $key ] < $value ) {
							if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
								$student_id = $student->enrollment_id;
							} else {
								$student_id = $student->id;
							}
							wp_send_json_error( esc_html__( 'Marks obtained exceeded maximum marks for enrollment ID: ', WL_MIM_DOMAIN ) . WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ) );
						}
						$marks_obtained_p[ $student->id ][ $key ] = isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0;
					}
				}
			}
			array_push( $result_data, array(
				'student_id' => $student->id,
				'exam_id'    => $exam->id,
				'marks'      => serialize( $marks_obtained[ $student->id ] ),
				'marks_p'    => serialize( $marks_obtained_p[ $student->id ] ),
				'created_at' => date( 'Y-m-d H:i:s' ),
				'updated_at' => date( 'Y-m-d H:i:s' )
			) );
		}
		/* End validations */

		?><pre>
		<?php var_dump( $marks_obtained);?>
		</pre><?php die; 

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				foreach ( $result_data as $data ) {
					$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND institute_id = $institute_id AND exam_id = " . $data['exam_id'] . " AND student_id = " . $data['student_id'] );
					if ( $result ) {
						/* Update result */
						$success = $wpdb->update( "{$wpdb->prefix}wl_min_results", $data, array(
							'is_deleted'   => 0,
							'exam_id'      => $data['exam_id'],
							'student_id'   => $data['student_id'],
							'institute_id' => $institute_id
						) );
					} else {
						/* Insert result */
						$data['added_by']     = get_current_user_id();
						$data['institute_id'] = $institute_id;
						$data['created_at'] = current_time( 'Y-m-d H:i:s' );
						$success              = $wpdb->insert( "{$wpdb->prefix}wl_min_results", $data );
					}
					if ( ! $success ) {
						throw new Exception( esc_html__( $wpdb->last_error, WL_MIM_DOMAIN ) );
					}
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => esc_html__( 'Results saved successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Add new result */
	public static function add_result() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-result'], 'add-result' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$student_id       = isset( $_REQUEST['student'] ) ? intval( sanitize_text_field( $_REQUEST['student'] ) ) : null;
		$exam_id          = isset( $_REQUEST['exam'] ) ? intval( sanitize_text_field( $_REQUEST['exam'] ) ) : null;
		$marks_obtained   = ( isset( $_POST['marks_obtained'] ) && is_array( $_POST['marks_obtained'] ) ) ? $_POST['marks_obtained'] : null;
		$marks_obtained_p = ( isset( $_POST['marks_obtained_p'] ) && is_array( $_POST['marks_obtained_p'] ) ) ? $_POST['marks_obtained_p'] : null;

		/* Validations */
		$errors = array();

		if ( empty( $student_id ) ) {
			$errors['student'] = esc_html__( 'Please select a student.', WL_MIM_DOMAIN );
		}

		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );

		if ( ! $exam ) {
			wp_send_json_error( esc_html__( 'Exam not found.', WL_MIM_DOMAIN ) );
		}

		$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $student_id AND institute_id = $institute_id" );

		if ( ! $student ) {
			wp_send_json_error( esc_html__( 'Student not found.', WL_MIM_DOMAIN ) );
		}

		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND exam_id = $exam_id AND student_id = $student_id AND institute_id = $institute_id" );

		if ( $result ) {
			wp_send_json_error( esc_html__( 'Result for this exam already exists.', WL_MIM_DOMAIN ) );
		}

		$marks = unserialize( $exam->marks );

		if ( empty( $marks_obtained ) ) {
			wp_send_json_error( esc_html__( 'Please specify marks obtained.', WL_MIM_DOMAIN ) );
		}

		foreach ( $marks_obtained as $key => $value ) {
			if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
				wp_send_json_error( esc_html__( 'Please provide a valid marks obtained.', WL_MIM_DOMAIN ) );
			} else {
				// if ( $marks['maximum'][ $key ] < $value ) {
				// 	wp_send_json_error( esc_html__( 'Marks obtained exceeded maximum marks.', WL_MIM_DOMAIN ) );
				// }
				$marks_obtained  [ $key ] = isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0;
			}
		}

		foreach ( $marks_obtained_p as $key => $value ) {
			if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
				wp_send_json_error( esc_html__( 'Please provide a valid marks obtained.', WL_MIM_DOMAIN ) );
			} else {
				// if ( $marks['maximum'][ $key ] < $value ) {
				// 	wp_send_json_error( esc_html__( 'Marks obtained exceeded maximum marks.', WL_MIM_DOMAIN ) );
				// }
				$marks_obtained_p  [ $key ] = isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0;
			}
		}

		$data = array(
			'student_id'   => $student_id,
			'exam_id'      => $exam_id,
			'marks'        => serialize( $marks_obtained ),
			'marks_p'      => serialize( $marks_obtained_p ),
			'added_by'     => get_current_user_id(),
			'institute_id' => $institute_id
		);
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_results", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => esc_html__( 'Result added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Get exam results */
	public static function get_exam_results() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$exam_id = intval( sanitize_text_field( $_REQUEST['exam'] ) );

		if ( empty( $exam_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select an exam.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}
		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Exam not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}
		$marks = unserialize( $exam->marks );

		$results = $wpdb->get_col( "SELECT student_id FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND exam_id = $exam_id AND institute_id = $institute_id" );

		$filter = "";
		if ( count( $results ) ) {
			$student_ids = implode( ',', $results );
			$filter      = " AND id NOT IN ($student_ids)";
		}

		$students = $wpdb->get_results( "SELECT id, first_name, last_name, enrollment_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id$filter ORDER BY id DESC" );
		ob_start();
		?>
        <div class="row">
            <div class="card col">
                <div class="card-header bg-info text-white">
                    <div class="row">
                        <div class="col-md-9 col-xs-12">
                            <h6><?php esc_html_e( 'Exam Results', WL_MIM_DOMAIN ); ?>:
                                <mark><?php echo "$exam->exam_title ( $exam->exam_code )"; ?></mark>
                            </h6>
                        </div>
                        <div class="col-md-3 col-xs-12">
                            <button type="button" class="btn btn-outline-light float-right add-result" data-toggle="modal" data-target="#add-result" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Result', WL_MIM_DOMAIN ); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-hover table-striped table-bordered" id="result-table">
                        <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Name', WL_MIM_DOMAIN ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Percentage', WL_MIM_DOMAIN ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Result', WL_MIM_DOMAIN ); ?></th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div><!-- add new result modal -->
        <div class="modal fade" id="add-result" tabindex="-1" role="dialog" aria-labelledby="add-result-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="add-result-label"><?php esc_html_e( 'Add New Result', WL_MIM_DOMAIN ); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-result-form">
                        <div class="modal-body pr-4 pl-4">
                            <div class="border p-2 mb-2">
                                <span class="text-dark"><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?> : </span><strong><?php echo "$exam->exam_title ( $exam->exam_code )"; ?></strong>
                            </div>
							<?php $nonce = wp_create_nonce( 'add-result' ); ?>
                            <input type="hidden" name="add-result" value="<?php echo esc_attr( $nonce ); ?>">
                            <input type="hidden" name="action" value="wl-mim-add-result">
                            <input type="hidden" name="exam" value="<?php echo esc_attr( $exam_id ); ?>">
							<?php
							if ( count( $students ) ) { ?>
                                <div class="form-group wlim-selectpicker">
                                    <label for="wlim-students" class="col-form-label"><?php esc_html_e( "Student", WL_MIM_DOMAIN ); ?>:</label>
                                    <select name="student" class="form-control selectpicker" id="wlim-students">
                                        <option value="">-------- <?php esc_html_e( "Select a Student", WL_MIM_DOMAIN ); ?> --------</option>
										<?php
										foreach ( $students as $row ) {
											$id            = $row->id;
											if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
												$student_id = $student->enrollment_id;
											} else {
												$student_id = $student->id;
											}
											$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
											$name          = $row->first_name;
											if ( $row->last_name ) {
												$name .= " $row->last_name";
											}
											$student = "$name ($enrollment_id)"; ?>
                                            <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_attr( $student ); ?></option>
											<?php
										} ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3 mt-2">
                                            <label class="col-form-label">* <?php esc_html_e( 'Exam Marks', WL_MIM_DOMAIN ); ?>:</label>
                                            <div class="exam_marks_obtained_box">
                                                <table class="table table-bordered">
                                                    <thead>
                                                    <tr>
                                                        <th><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
                                                        <th><?php esc_html_e( 'Maximum Theory Marks', WL_MIM_DOMAIN ); ?></th>
                                                        <th><?php esc_html_e( 'Maximum Practical Marks', WL_MIM_DOMAIN ); ?></th>
                                                        <th><?php esc_html_e( 'Marks Obtained Theory', WL_MIM_DOMAIN ); ?></th>
                                                        <th><?php esc_html_e( 'Marks Obtained Practical', WL_MIM_DOMAIN ); ?></th>
                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody class="exam_marks_obtained_rows exam_marks_obtained_table">
													<?php
													foreach ( $marks['subject'] as $subject_key => $subject_value ) { ?>
                                                        <tr>
                                                            <td>
                                                                <span class="text-dark"><?php echo esc_attr( $subject_value ); ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="text-dark"><?php echo esc_attr( $marks['maximum'][ $subject_key ] ); ?></span>
                                                            </td>
															<td>
                                                                <span class="text-dark"><?php echo esc_attr( $marks['maximum_p'][ $subject_key ] ); ?></span>
                                                            </td>
                                                            <td>
                                                                <input required type="number" min="0" step="any" name="marks_obtained[]" class="form-control" placeholder="<?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?>">
                                                            </td>
															<td>
                                                                <input required type="number" min="0" step="any" name="marks_obtained_p[]" class="form-control" placeholder="<?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?>">
                                                            </td>
                                                        </tr>
														<?php
													} ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<?php
							} else { ?>
                                <strong class="text-danger">
									<?php
									esc_html_e( 'There is no student.', WL_MIM_DOMAIN ); ?>
                                </strong>
								<?php
							} ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                            <button type="submit" class="btn btn-primary add-result-submit"><?php esc_html_e( 'Add New Result', WL_MIM_DOMAIN ); ?></button>
                        </div>
					</form>
                </div>
            </div>
        </div><!-- end - add new exam modal --><!-- update result modal -->
        <div class="modal fade" id="update-result" tabindex="-1" role="dialog" aria-labelledby="update-result-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="update-result-label"><?php esc_html_e( 'Update Result', WL_MIM_DOMAIN ); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-result-form">
                        <div class="modal-body pr-4 pl-4" id="fetch_result"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                            <button type="submit" class="btn btn-primary update-result-submit"><?php esc_html_e( 'Update Result', WL_MIM_DOMAIN ); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- end - update result modal -->

		<!-- print exam result -->
		<div class="modal fade" id="print-exam-result" tabindex="-1" role="dialog" aria-labelledby="print-exam-result-label" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" id="print-exam-result-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title w-100 text-center" id="print-exam-result-label"><?php esc_html_e( 'View and Print Fee Invoice Fee', WL_MIM_DOMAIN ); ?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body pr-4 pl-4" id="print_exam_result"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<!-- end - print exam result -->
		<?php $html = ob_get_clean();

		$json = json_encode( array(
			'exam_id' => esc_attr( $exam_id ),
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Get exam results */
	public static function save_exam_results() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$exam_id = intval( sanitize_text_field( $_REQUEST['exam'] ) );

		if ( empty( $exam_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select an exam.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}
		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Exam not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}
		$marks = unserialize( $exam->marks );

		$results = $wpdb->get_col( "SELECT student_id FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND exam_id = $exam_id AND institute_id = $institute_id" );

		$filter = "";
		if ( count( $results ) ) {
			$student_ids = implode( ',', $results );
			$filter      = " AND id NOT IN ($student_ids)";
		}

		$students = $wpdb->get_results( "SELECT id, first_name, last_name FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id$filter ORDER BY id DESC" );
		ob_start();
		?>
        <div class="row">
		
           
			<div class="col-md-3 col-xs-12">
                            <button type="button" class="btn add-result" data-toggle="modal" data-target="#add-result" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Result', WL_MIM_DOMAIN ); ?>
                            </button>
                        </div>
                <!-- <div class="card-header bg-info text-white">
                    <div class="row">
                        <div class="col-md-9 col-xs-12">
                            <h6><?php esc_html_e( 'Exam Results', WL_MIM_DOMAIN ); ?>:
                                <mark><?php echo "$exam->exam_title ( $exam->exam_code )"; ?></mark>
                            </h6>
                        </div>
                        
                    </div>
                </div> -->
               
            
        </div><!-- add new result modal -->
        <div class="modal fade" id="add-result" tabindex="-1" role="dialog" aria-labelledby="add-result-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="add-result-label"><?php esc_html_e( 'Add New Result', WL_MIM_DOMAIN ); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-result-form">
                        <div class="modal-body pr-4 pl-4">
                            <div class="border p-2 mb-2">
                                <span class="text-dark"><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?> : </span><strong><?php echo "$exam->exam_title ( $exam->exam_code )"; ?></strong>
                            </div>
							<?php $nonce = wp_create_nonce( 'add-result' ); ?>
                            <input type="hidden" name="add-result" value="<?php echo esc_attr( $nonce ); ?>">
                            <input type="hidden" name="action" value="wl-mim-add-result">
                            <input type="hidden" name="exam" value="<?php echo esc_attr( $exam_id ); ?>">
							<?php
							if ( count( $students ) ) { ?>
                                <div class="form-group wlim-selectpicker">
                                    <label for="wlim-students" class="col-form-label"><?php esc_html_e( "Student", WL_MIM_DOMAIN ); ?>:</label>
                                    <select name="student" class="form-control selectpicker" id="wlim-students">
                                        <option value="">-------- <?php esc_html_e( "Select a Student", WL_MIM_DOMAIN ); ?> --------</option>
										<?php
										foreach ( $students as $row ) {
											$id            = $row->id;
											$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $id, $general_enrollment_prefix );
											$name          = $row->first_name;
											if ( $row->last_name ) {
												$name .= " $row->last_name";
											}
											$student = "$name ($enrollment_id)"; ?>
                                            <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_attr( $student ); ?></option>
											<?php
										} ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-3 mt-2">
                                            <label class="col-form-label">* <?php esc_html_e( 'Exam Marks', WL_MIM_DOMAIN ); ?>:</label>
                                            <div class="exam_marks_obtained_box">
                                                <table class="table table-bordered">
                                                    <thead>
                                                    <tr>
                                                        <th><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
                                                        <th><?php esc_html_e( 'Maximum Theory Marks', WL_MIM_DOMAIN ); ?></th>
                                                        <th><?php esc_html_e( 'Maximum Practical Marks', WL_MIM_DOMAIN ); ?></th>
                                                        <th><?php esc_html_e( 'Marks Obtained Theory', WL_MIM_DOMAIN ); ?></th>
                                                        <th><?php esc_html_e( 'Marks Obtained Practical', WL_MIM_DOMAIN ); ?></th>
                                                        <th></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody class="exam_marks_obtained_rows exam_marks_obtained_table">
													<?php
													foreach ( $marks['subject'] as $subject_key => $subject_value ) { ?>
                                                        <tr>
                                                            <td>
                                                                <span class="text-dark"><?php echo esc_attr( $subject_value ); ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="text-dark"><?php echo esc_attr( $marks['maximum'][ $subject_key ] ); ?></span>
                                                            </td>
															<td>
                                                                <span class="text-dark"><?php echo esc_attr( $marks['maximum_p'][ $subject_key ] ); ?></span>
                                                            </td>
                                                            <td>
                                                                <input required type="number" min="0" step="any" name="marks_obtained[]" class="form-control" placeholder="<?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?>">
                                                            </td>
															<td>
                                                                <input required type="number" min="0" step="any" name="marks_obtained_p[]" class="form-control" placeholder="<?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?>">
                                                            </td>
                                                        </tr>
														<?php
													} ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<?php
							} else { ?>
                                <strong class="text-danger">
									<?php
									esc_html_e( 'There is no student.', WL_MIM_DOMAIN ); ?>
                                </strong>
								<?php
							} ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                            <button type="submit" class="btn btn-primary add-result-submit"><?php esc_html_e( 'Add New Result', WL_MIM_DOMAIN ); ?></button>
                        </div>
					</form>
                </div>
            </div>
        </div><!-- end - add new exam modal --><!-- update result modal -->
        <div class="modal fade" id="update-result" tabindex="-1" role="dialog" aria-labelledby="update-result-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="update-result-label"><?php esc_html_e( 'Update Result', WL_MIM_DOMAIN ); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-result-form">
                        <div class="modal-body pr-4 pl-4" id="fetch_result"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                            <button type="submit" class="btn btn-primary update-result-submit"><?php esc_html_e( 'Update Result', WL_MIM_DOMAIN ); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- end - update result modal -->

		<!-- print exam result -->
		<div class="modal fade" id="print-exam-result" tabindex="-1" role="dialog" aria-labelledby="print-exam-result-label" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" id="print-exam-result-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title w-100 text-center" id="print-exam-result-label"><?php esc_html_e( 'View and Print Fee Invoice Fee', WL_MIM_DOMAIN ); ?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body pr-4 pl-4" id="print_exam_result"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<!-- end - print exam result -->
		<?php $html = ob_get_clean();

		$json = json_encode( array(
			'exam_id' => esc_attr( $exam_id ),
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}




	/* Get result data to display on table */
	public static function get_result_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$exam_id     = intval( sanitize_text_field( $_REQUEST['exam'] ) );
		$exam        = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );
		$data        = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND exam_id = $exam_id AND institute_id = $institute_id" );
		$marks       = unserialize( $exam->marks );
		$course_data = $wpdb->get_results( "SELECT id, course_name, course_code, fees, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );

		$batch_data = $wpdb->get_results( "SELECT id, batch_code, batch_name, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K );

		$total_maximum_marks = array_sum( $marks['maximum'] );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id                   = $row->id;
				$marks_obtained       = unserialize( $row->marks );
				$total_marks_obtained = array_sum( $marks_obtained );
				$student              = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $row->student_id AND institute_id = $institute_id" );
				if ( ! $student ) {
					continue;
				}
				$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student->enrollment_id, $general_enrollment_prefix );
				$name          = $student->first_name;
				if ( $student->last_name ) {
					$name .= " $student->last_name";
				}
				$course = '-';
				$batch  = '-';
				if ( $student->course_id && isset( $course_data[ $student->course_id ] ) ) {
					$course_name = $course_data[ $student->course_id ]->course_name;
					$course_code = $course_data[ $student->course_id ]->course_code;
					$course      = "$course_name ($course_code)";
				}

				if ( $student->batch_id && isset( $batch_data[ $student->batch_id ] ) ) {
					$time_from    = date( "g:i A", strtotime( $batch_data[ $student->batch_id ]->time_from ) );
					$time_to      = date( "g:i A", strtotime( $batch_data[ $student->batch_id ]->time_to ) );
					$timing       = "$time_from - $time_to";
					$batch        = $batch_data[ $student->batch_id ]->batch_code . ' ( ' . $batch_data[ $student->batch_id ]->batch_name . ' )<br>( ' . $timing . ' )';
					$batch_status = WL_MIM_Helper::get_batch_status( $batch_data[ $student->batch_id ]->start_date, $batch_data[ $student->batch_id ]->end_date );
				}

				$percentage = number_format( max( floatval( ( $total_marks_obtained / $total_maximum_marks ) * 100 ), 0 ), 2, '.', '' );

				$results["data"][] = array(
					$enrollment_id . '<a class="ml-2" href="#print-exam-result" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' .$id. '"><i class="fa fa-print"></i></a>',
					$name,
					$course,
					$batch,
					$batch_status,
					"$percentage %",
					'<a class="mr-3" href="#update-result" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-result-security="' . wp_create_nonce( "delete-result-$id" ) . '"delete-result-id="' . $id . '" class="delete-result"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Fetch result to update */
	public static function fetch_result() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings( $institute_id );

		$id     = intval( sanitize_text_field( $_POST['id'] ) );
		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $result ) {
			die();
		}
		$exam_id = $result->exam_id;
		$exam    = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Exam not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}
		$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $result->student_id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}
		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student->id, $general_enrollment_prefix );
		$name          = $student->first_name;
		if ( $student->last_name ) {
			$name .= " $student->last_name";
		}
		$marks = unserialize( $exam->marks );
		?>
        <div class="row">
            <div class="col">
                <div class="border p-2 mb-2">
                    <span class="text-dark"><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?> : </span><strong><?php echo "$exam->exam_title ( $exam->exam_code )"; ?></strong>
                </div>
                <label class="col-form-label"><strong><?php esc_html_e( 'Student Details', WL_MIM_DOMAIN ); ?>:</strong></label>
                <ul class="list-group">
                    <li class="list-group-item"><span class="text-dark"><?php esc_html_e( "Enrollment ID", WL_MIM_DOMAIN ); ?>: </span><strong><?php echo esc_attr( $enrollment_id ); ?></strong></li>
					<?php if ( $general_enable_roll_number ) { ?>
                    <li class="list-group-item"><span class="text-dark"><?php esc_html_e( "Roll Number", WL_MIM_DOMAIN ); ?>: </span><strong><?php echo esc_attr( $student->roll_number ); ?></strong></li>
					<?php } ?>
                    <li class="list-group-item"><span class="text-dark"><?php esc_html_e( "Name", WL_MIM_DOMAIN ); ?>: </span><strong><?php echo esc_attr( $name ); ?></strong></li>
                </ul>
            </div>
        </div>
		<?php $nonce = wp_create_nonce( "update-result-$id" ); ?>
        <input type="hidden" name="update-result-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-update-result">
        <div class="row">
            <div class="col">
                <div class="mb-3 mt-2">
                    <label class="col-form-label"><strong>* <?php esc_html_e( 'Exam Marks', WL_MIM_DOMAIN ); ?>:</strong></label>
                    <div class="exam_marks_obtained_box">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
								<th><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
								<th><?php esc_html_e( 'Maximum Theory Marks', WL_MIM_DOMAIN ); ?></th>
								<th><?php esc_html_e( 'Maximum Practical Marks', WL_MIM_DOMAIN ); ?></th>
								<th><?php esc_html_e( 'Marks Obtained Theory', WL_MIM_DOMAIN ); ?></th>
								<th><?php esc_html_e( 'Marks Obtained Practical', WL_MIM_DOMAIN ); ?></th>
                                <!-- <th></th> -->
                            </tr>
                            </thead>
                            <tbody class="exam_marks_obtained_rows exam_marks_obtained_table">
							<?php
							$marks_obtained   = null;
							$marks_obtained_p = null;
							if ( $result ) {
								$marks_obtained   = unserialize( $result->marks );
								$marks_obtained_p = unserialize( $result->marks_p );
							}
							$total_maximum_marks  = 0;
							$total_marks_obtained = 0;
							foreach ( $marks['subject'] as $subject_key => $subject_value ) {
								$marks_obtained_in_subject = 0;
								$marks_obtained_in_subject_p = 0;
								if ( ! empty( $marks_obtained ) ) {
									$marks_obtained_in_subject   = $marks_obtained[ $subject_key ];
									$marks_obtained_in_subject_p = $marks_obtained_p[ $subject_key ];
								}
								$total_maximum_marks    += $marks['maximum'][ $subject_key ];
								$total_maximum_marks_p  += $marks['maximum_p'][ $subject_key ];
								$total_marks_obtained   += $marks_obtained_in_subject;
								$total_marks_obtained_p += $marks_obtained_in_subject_p;
								?>
                                <tr>
                                    <td>
                                        <span class="text-dark"><?php echo esc_attr( $subject_value ); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-dark"><?php echo esc_attr( $marks['maximum'][ $subject_key ] ); ?></span>
                                    </td> 
									
									<td>
                                        <span class="text-dark"><?php echo esc_attr( $marks['maximum_p'][ $subject_key ] ); ?></span>
                                    </td>
                                    <td>
                                        <input required type="number" min="0" step="any" name="marks_obtained[]" class="form-control" placeholder="<?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $marks_obtained_in_subject ); ?>">
                                    </td>
									<td>
                                        <input required type="number" min="0" step="any" name="marks_obtained_p[]" class="form-control" placeholder="<?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $marks_obtained_in_subject_p ); ?>">
                                    </td>
                                </tr>
								<?php
							} ?>
                            <tr>
                                <th><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></th>
                                <th><?php echo esc_attr( $total_maximum_marks ); ?></th>
                                <th><?php echo esc_attr( $total_maximum_marks_p ); ?></th>
                                <th><?php echo esc_attr( $total_marks_obtained ); ?></th>
                                <th><?php echo esc_attr( $total_marks_obtained_p ); ?></th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th><?php esc_html_e( 'Percentage', WL_MIM_DOMAIN ); ?></th>
                                <th><?php echo number_format( max( floatval( ( $total_marks_obtained / $total_maximum_marks ) * 100 ), 0 ), 2, '.', '' ); ?>%</th>
                                <th><?php echo number_format( max( floatval( ( $total_marks_obtained_p / $total_maximum_marks_p ) * 100 ), 0 ), 2, '.', '' ); ?>%</th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
		<input type="hidden" name="result_id" value="<?php echo esc_attr( $id ); ?>">
		<?php
		die();
	}

	/* View and print invoice fee invoice */
	public static function print_exam_result() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings( $institute_id );

		$id     = intval( sanitize_text_field( $_POST['id'] ) );
		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $result ) {
			die();
		}
		$exam_id = $result->exam_id;
		$exam    = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Exam not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}
		$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $result->student_id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}
		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student->enrollment_id, $general_enrollment_prefix );
		$name          = $student->first_name;
		if ( $student->last_name ) {
			$name .= " $student->last_name";
		}
		$marks = unserialize( $exam->marks );
		?>
		<div class="row">
			<div class="col">
				<div class="mb-3 mt-2">
					<div class="text-center">
						<button type="button" id="wl-invoice-fee-invoice-print" class="btn btn-sm btn-success"><i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Exam Result', WL_MIM_DOMAIN ); ?></button><hr>
					</div>
					<div>
						<?php require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_exam_result.php' ); ?>
						</div>
				</div>
			</div>
		</div>
	<?php
		die();
	}

	/* Update result */
	public static function update_result() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['result_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-result-$id"], "update-result-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$marks_obtained   = ( isset( $_POST['marks_obtained'] ) && is_array( $_POST['marks_obtained'] ) ) ? $_POST['marks_obtained'] : null;
		$marks_obtained_p = ( isset( $_POST['marks_obtained_p'] ) && is_array( $_POST['marks_obtained_p'] ) ) ? $_POST['marks_obtained_p'] : null;
		

		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $result ) {
			die();
		}

		$exam_id = $result->exam_id;
		$exam    = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );
		if ( ! $exam ) {
			wp_send_json_error( esc_html__( 'Exam not found.', WL_MIM_DOMAIN ) );
		}
		$marks = unserialize( $exam->marks );

		/* Validations */
		$errors = array();
		if ( empty( $marks_obtained ) ) {
			wp_send_json_error( esc_html__( 'Please specify marks obtained.', WL_MIM_DOMAIN ) );
		}

		foreach ( $marks_obtained as $key => $value ) {
			if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
				wp_send_json_error( esc_html__( 'Please provide a valid marks obtained.', WL_MIM_DOMAIN ) );
			} else {
				if ( $marks['maximum'][ $key ] < $value ) {
					wp_send_json_error( esc_html__( 'Marks obtained exceeded maximum marks.', WL_MIM_DOMAIN ) );
				}
				$marks_obtained[ $key ] = isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0;
			}
		}

		foreach ( $marks_obtained_p as $key => $value ) {
			if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
				wp_send_json_error( esc_html__( 'Please provide a valid marks obtained.', WL_MIM_DOMAIN ) );
			} else {
				if ( $marks['maximum'][ $key ] < $value ) {
					wp_send_json_error( esc_html__( 'Marks obtained exceeded maximum marks.', WL_MIM_DOMAIN ) );
				}
				$marks_obtained_p[ $key ] = isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0;
			}
		}

		$data = array(
			'marks'      => serialize( $marks_obtained ),
			'marks_p'    => serialize( $marks_obtained_p ),
			'updated_at' => date( 'Y-m-d H:i:s' )
		);
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_results", $data, array(
					'is_deleted'   => 0,
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => esc_html__( 'Result updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete result */
	public static function delete_result() {
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-result-$id"], "delete-result-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->update( "{$wpdb->prefix}wl_min_results",
				array(
					'is_deleted' => 1,
					'deleted_at' => date( 'Y-m-d H:i:s' )
				), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
			);
			if ( $success === false ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Result removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* View admit card */
	public static function view_admit_card() {
		if ( ! current_user_can( 'wl_min_manage_admit_cards' ) ) {
			die();
		}
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$enrollment_id = isset( $_REQUEST['enrollment_id'] ) ? sanitize_text_field( $_REQUEST['enrollment_id'] ) : null;
		$exam_id = isset( $_REQUEST['exam'] ) ? intval( sanitize_text_field( $_REQUEST['exam'] ) ) : null;

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
		$student_id                = WL_MIM_Helper::get_student_id_with_prefix( $enrollment_id, $general_enrollment_prefix );
		

		if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
			$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND enrollment_id = $student_id AND institute_id = $institute_id" );
		} else {
			$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $student_id AND institute_id = $institute_id" );
		}

		if ( ! $student ) { ?>
			<div class="text-danger mb-2"><?php esc_html_e( 'Student not found.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}

		
		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );

		global $wpdb;

		/* Validations */
		$errors = array();
		if ( empty( $exam_id ) ) { ?>
            <div class="text-danger mb-2"><?php esc_html_e( 'Please select an exam.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}

		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <div class="text-danger mb-2"><?php esc_html_e( 'Invalid exam selection.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}

		$notes = unserialize( $exam->notes );
		/* End validations */

		$id    = $student->id;
		$name  = $student->first_name;
		if ( $student->last_name ) {
			$name .= " $student->last_name";
		}

		$father_name = $student->father_name;
		$photo       = $student->photo_id;

		$course = WL_MIM_Helper::get_course( $id );
		$course = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '';
		?>

        <div class="wlim-exam-result card col-12 mb-3">
            <div class="card-header bg-primary text-white">
                <strong><?php esc_html_e( 'Admit Card', WL_MIM_DOMAIN ); ?></strong>
                <button type="button" id="wlmim-admit-card-print-button" class="btn btn-sm btn-outline-light float-right">
        			<i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print', WL_MIM_DOMAIN ); ?>
                </button>
            </div>
        </div>
		<?php
		require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_admit_card.php' );
		die();
	}

	/* Get student admit card */
	public static function get_admit_card() {
		if ( ! current_user_can( WL_MIM_Helper::get_student_capability() ) ) {
			die();
		}
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$exam_id = isset( $_REQUEST['exam'] ) ? intval( sanitize_text_field( $_REQUEST['exam'] ) ) : null;
		$student = WL_MIM_StudentHelper::get_student();
		if ( ! $student ) { ?>
            <div class="text-danger mb-2"><?php esc_html_e( 'Student not found.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}
		$student_id = $student->enrollment_id;
		if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
			$student_id = $student->enrollment_id;
		} else {
			$student_id = $student->id;
		}

		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );

		global $wpdb;

		/* Validations */
		$errors = array();
		if ( empty( $exam_id ) ) { ?>
            <div class="text-danger mb-2"><?php esc_html_e( 'Please select an exam.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}

		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <div class="text-danger mb-2"><?php esc_html_e( 'Invalid exam selection.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}

		$notes = unserialize( $exam->notes );
		/* End validations */

		$id            = $student->id;
		if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
			$student_id = $student->enrollment_id;
		} else {
			$student_id = $student->id;
		}
		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
		$name          = $student->first_name;
		if ( $student->last_name ) {
			$name .= " $student->last_name";
		}

		$father_name = $student->father_name;
		$photo       = $student->photo_id;

		$course = WL_MIM_Helper::get_course( $id );
		$course = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '';
		?>

        <div class="wlim-exam-result card col-12 mb-3">
            <div class="card-header bg-primary text-white">
                <strong><?php esc_html_e( 'Admit Card', WL_MIM_DOMAIN ); ?></strong>
                <button type="button" id="wlmim-admit-card-print-button" class="btn btn-sm btn-outline-light float-right">
        			<i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print', WL_MIM_DOMAIN ); ?>
                </button>
            </div>
        </div>
		<?php
		require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_admit_card.php' );
		die();
	}

	/* Get student result */
	public static function get_student_result() {
		if ( ! current_user_can( WL_MIM_Helper::get_student_capability() ) ) {
			die();
		}
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings( $institute_id );

		$exam_id = isset( $_REQUEST['exam'] ) ? intval( sanitize_text_field( $_REQUEST['exam'] ) ) : null;
		$student = WL_MIM_StudentHelper::get_student();
		if ( ! $student ) { ?>
            <div class="text-danger mb-2"><?php esc_html_e( 'Student not found.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}
		$student_id = $student->enrollment_id;

		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );

		global $wpdb;

		/* Validations */
		$errors = array();
		if ( empty( $exam_id ) ) { ?>
            <div class="text-danger mb-2"><?php esc_html_e( 'Please select an exam.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}

		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND is_published = 1 AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <div class="text-danger mb-2"><?php esc_html_e( 'Invalid exam selection.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}
		/* End validations */

		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND exam_id = $exam_id AND student_id = $student_id AND institute_id = $institute_id" );
		if ( ! $result ) { ?>
            <div class="text-danger mb-2"><?php esc_html_e( 'Result not found.', WL_MIM_DOMAIN ); ?></div>
			<?php
			die();
		}

		$id            = $student->id;
		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student->enrollment_id, $general_enrollment_prefix );
		$name          = $student->first_name;
		if ( $student->last_name ) {
			$name .= " $student->last_name";
		}

		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND institute_id = $institute_id AND exam_id = " . $exam->id . " AND student_id = " . $id );

		$marks = unserialize( $exam->marks ); ?>
        <div class="wlim-exam-result card col-12 mb-3">
            <div class="card-header bg-primary text-white">
                <strong><?php esc_html_e( 'Exam Result', WL_MIM_DOMAIN ); ?></strong>
                <button type="button" id="wlmim-exam-result-print-button" class="btn btn-sm btn-outline-light float-right">
        			<i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print', WL_MIM_DOMAIN ); ?>
                </button>
            </div>
            <div id="wlmim-exam-result-print">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <div class="row">
								<?php
								$enable_university_header = get_option( 'multi_institute_enable_university_header' );
								if ( $enable_university_header ) {
									$university_logo    = get_option( 'multi_institute_university_logo' );
									$university_name    = get_option( 'multi_institute_university_name' );
									$university_address = get_option( 'multi_institute_university_address' );
									$university_phone   = get_option( 'multi_institute_university_phone' );
									$university_email   = get_option( 'multi_institute_university_email' );
								}

								$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
								$institute_logo    = wp_get_attachment_url( $general_institute['institute_logo'] );
								$show_logo         = $general_institute['institute_logo_enable'];
								$institute_name    = $general_institute['institute_name'];
								$institute_address = $general_institute['institute_address'];
								$institute_phone   = $general_institute['institute_phone'];
								$institute_email   = $general_institute['institute_email']; ?>
                                <div class="col-sm-12 mb-3">
                                    <div class="row">
										<?php
										if ( $enable_university_header ) { ?>
	                                        <div class="col-3 text-right">
	                                            <img src="<?php echo esc_url( $university_logo ); ?>" id="wlmim-result-institute-logo" class="img-responsive">
	                                        </div>
										<?php
										} else {
											if ( $show_logo ) { ?>
	                                            <div class="col-3 text-right">
	                                                <img src="<?php echo esc_url( $institute_logo ); ?>" id="wlmim-result-institute-logo" class="img-responsive">
	                                            </div>
											<?php }
										} ?>

                                        <div class="<?php echo boolval( $enable_university_header || $show_logo ) ? "col-9 text-left" : "col-12 text-center"; ?>">
											<?php if ( $enable_university_header || $show_logo ) { ?>
											<span class="float-left">
											<?php
											} else { ?>
											<span>
											<?php
											}
											if ( $enable_university_header ) { ?>
											<h4 id="wlmim-result-institute-name" class="mt-1"><?php echo esc_attr( $university_name ); ?></h4>
											<?php } else { ?>
											<h4 id="wlmim-result-institute-name" class="mt-1"><?php echo esc_attr( $institute_name ); ?></h4>
											<?php }
											if ( $enable_university_header ) {
												if ( ! empty( $university_address ) ) { ?>
													<span id="wlmim-result-institute-address"><?php echo esc_attr( $university_address ); ?></span>
													<br>
													<?php
												}
												if ( ! empty( $university_phone ) ) { ?>
													<span id="wlmim-result-institute-contact-phone">
													<?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_attr( $university_phone ); ?></strong>
													<?php
														if ( ! empty( $university_email ) ) { ?> | <?php } ?>
													</span>
													<?php
												}
												if ( ! empty( $university_email ) ) { ?>
												<span id="wlmim-result-institute-contact-email">
													<?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_attr( $university_email ); ?></strong>
												</span>
												<?php
												}
											} else {
												if ( ! empty( $institute_address ) ) { ?>
													<span id="wlmim-result-institute-address"><?php echo esc_attr( $institute_address ); ?></span>
													<br>
													<?php
												}
												if ( ! empty( $institute_phone ) ) { ?>
													<span id="wlmim-result-institute-contact-phone">
													<?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_attr( $institute_phone ); ?></strong>
													<?php
														if ( ! empty( $institute_email ) ) { ?> | <?php } ?>
													</span>
													<?php
												}
												if ( ! empty( $institute_email ) ) { ?>
												<span id="wlmim-result-institute-contact-email">
													<?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_attr( $institute_email ); ?></strong>
												</span>
												<?php
												}
											} ?>
											</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="border p-2 mb-2">
                                        <span class="text-dark"><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?>:&nbsp;</span><strong><?php echo esc_html( "$exam->exam_title ( $exam->exam_code )" ); ?></strong>
                                    </div>
                                    <label class="col-form-label"><strong><?php esc_html_e( 'Student Details', WL_MIM_DOMAIN ); ?>:</strong></label>
                                    <ul class="list-group m-0">
                                        <li class="list-group-item ml-0">
                                            <span class="text-dark"><?php esc_html_e( "Enrollment ID", WL_MIM_DOMAIN ); ?>:&nbsp;</span><strong><?php echo esc_html( $enrollment_id ); ?></strong>
                                        </li>
        								<?php if ( $general_enable_roll_number ) { ?>
                                        <li class="list-group-item ml-0">
                                            <span class="text-dark"><?php esc_html_e( "Roll Number", WL_MIM_DOMAIN ); ?>:&nbsp;</span><strong><?php echo esc_html( $student->roll_number ); ?></strong>
                                        </li>
										<?php } ?>
                                        <li class="list-group-item ml-0">
                                            <span class="text-dark"><?php esc_html_e( "Name", WL_MIM_DOMAIN ); ?>:&nbsp;</span><strong><?php echo esc_html( $name ); ?></strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3 mt-2">
                                <label class="col-form-label"><strong><?php esc_html_e( 'Exam Marks', WL_MIM_DOMAIN ); ?>:</strong></label>
                                <div class="exam_marks_obtained_box">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
                                            <th><?php esc_html_e( 'Maximum Marks', WL_MIM_DOMAIN ); ?></th>
                                            <th><?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody class="exam_marks_obtained_rows exam_marks_obtained_table">
										<?php
										$marks_obtained = null;
										if ( $result ) {
											$marks_obtained = unserialize( $result->marks );
										}
										$total_maximum_marks  = 0;
										$total_marks_obtained = 0;
										foreach ( $marks['subject'] as $subject_key => $subject_value ) {
											$marks_obtained_in_subject = 0;
											if ( ! empty( $marks_obtained ) ) {
												$marks_obtained_in_subject = $marks_obtained[ $subject_key ];
											}
											$total_maximum_marks  += $marks['maximum'][ $subject_key ];
											$total_marks_obtained += $marks_obtained_in_subject;
											?>
                                            <tr>
                                                <td>
                                                    <span class="text-dark"><?php echo esc_html( $subject_value ); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-dark"><?php echo esc_html( $marks['maximum'][ $subject_key ] ); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-dark"><?php echo esc_html( $marks_obtained_in_subject ); ?></span>
                                                </td>
                                            </tr>
											<?php
										} ?>
                                        <tr>
                                            <th><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></th>
                                            <th><?php echo esc_html( $total_maximum_marks ); ?></th>
                                            <th><?php echo esc_html( $total_marks_obtained ); ?></th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th><?php esc_html_e( 'Percentage', WL_MIM_DOMAIN ); ?></th>
                                            <th><?php echo number_format( max( floatval( ( $total_marks_obtained / $total_maximum_marks ) * 100 ), 0 ), 2, '.', '' ); ?>%</th>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
		die();
	}

	/* Check permission to manage result */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_results' ) || ! $institute_id ) {
			die();
		}
	}
}
?>