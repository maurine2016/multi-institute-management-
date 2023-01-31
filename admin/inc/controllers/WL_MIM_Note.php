<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_StudentHelper.php' );

class WL_MIM_Note {
	/* Get note data to display on table */
	public static function get_note_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_notes WHERE institute_id = $institute_id ORDER BY id DESC" );

		$course_data = $wpdb->get_results( "SELECT id, course_name, course_code, fees, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );

		$batch_data = $wpdb->get_results( "SELECT id, batch_code, batch_name, course_id FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id         = $row->id;
				$title      = $row->title;
				$notes_date = date_format( date_create( $row->notes_date ), "d-m-Y" );
				$is_acitve  = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$added_by   = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
				$added_on   = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );

				$course = '-';
				$batch  = '-';
				if ( $row->batch_id && isset( $batch_data[ $row->batch_id ] ) ) {
					$batch        = $batch_data[ $row->batch_id ]->batch_code . ' ( ' . $batch_data[ $row->batch_id ]->batch_name . ' )';
					$batch_status = WL_MIM_Helper::get_batch_status( $batch_data[ $row->batch_id ]->start_date, $batch_data[ $row->batch_id ]->end_date );

					$course_id = $batch_data[ $row->batch_id ]->course_id;
					if ( $course_id && isset( $course_data[ $course_id ] ) ) {
						$course_name = $course_data[ $course_id ]->course_name;
						$course_code = $course_data[ $course_id ]->course_code;
						$course      = "$course_name ($course_code)";
					}
				}

				$batch = $batch . " ( $course ) ";

				$results["data"][] = array(
					'<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html( $title ),
					esc_html( $batch ),
					esc_html( $notes_date ),
					esc_html( $is_acitve ),
					esc_html( $added_by ),
					esc_html( $added_on ),
					'<a class="mr-3" href="#update-note" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-note-security="' . wp_create_nonce( "delete-note-$id" ) . '"delete-note-id="' . $id . '" class="delete-note"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new note */
	public static function add_note() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-note'], 'add-note' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$title       = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$batch_id    = isset( $_POST['batch'] ) ? intval( sanitize_text_field( $_POST['batch'] ) ) : null;
		$documents   = ( isset( $_FILES['documents'] ) && is_array( $_FILES['documents'] ) ) ? $_FILES['documents'] : null;
		$notes_date  = ( isset( $_POST['notes_date'] ) && ! empty( $_POST['notes_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_POST['notes_date'] ) ) ) : null;
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';
		$is_active   = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $batch_id ) ) {
			$errors['batch'] = esc_html__( 'Please select a batch.', WL_MIM_DOMAIN );
		}

		if ( empty( $title ) ) {
			$errors['title'] = esc_html__( 'Please provide note title.', WL_MIM_DOMAIN );
		}

		if ( strlen( $title ) > 255 ) {
			$errors['title'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( empty( $documents ) || ! is_array( $documents ) || ! count( $documents ) ) {
			wp_send_json_error( esc_html__( 'Please provide note file(s).', WL_MIM_DOMAIN ) );
		} else {
			if ( isset( $documents["tmp_name"] ) && is_array( $documents ) && count( $documents ) ) {
				foreach ( $documents["tmp_name"] as $key => $document ) {
					$file_type          = $documents['type'][ $key ];
					$allowed_file_types = WL_MIM_Helper::get_notice_attachment_file_types();
					if ( ! in_array( $file_type, $allowed_file_types ) ) {
						wp_send_json_error( esc_html__( 'Please provide attachment in PDF, JPG, JPEG PNG, DOC, DOCX, XLS, XLSX, PPT or PPTX format.', WL_MIM_DOMAIN ) );
					}
				}
			} else {
				wp_send_json_error( esc_html__( 'Please provide valid file(s).', WL_MIM_DOMAIN ) );
			}
		}

		if ( empty( $notes_date ) ) {
			$errors['notes_date'] = esc_html__( 'Please provide notes issued date.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND id = $batch_id AND institute_id = $institute_id" );

		if ( ! $count ) {
			$errors['batch'] = esc_html__( 'Please select a valid batch.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$document_ids = array();
				if ( isset( $documents["tmp_name"] ) && is_array( $documents ) && count( $documents ) ) {
					foreach ( $documents["tmp_name"] as $key => $document ) {
						if ( $documents['name'][ $key ] ) {
							$document    = array(
								'name'     => sanitize_file_name( $documents['name'][ $key ] ),
								'type'     => $documents['type'][ $key ],
								'tmp_name' => $documents['tmp_name'][ $key ],
								'error'    => $documents['error'][ $key ],
								'size'     => $documents['size'][ $key ]
							);
							$_FILES      = array( 'document' => $document );
							$document_id = media_handle_upload( 'document', 0 );
							if ( is_wp_error( $document_id ) ) {
								throw new Exception( $document_id->get_error_message() );
							}
							array_push( $document_ids, $document_id );
						}
					}
				}
				$document_ids = serialize( $document_ids );

				$data = array(
					'title'        => $title,
					'description'  => $description,
					'document_ids' => $document_ids,
					'notes_date'   => $notes_date,
					'batch_id'     => $batch_id,
					'is_active'    => $is_active,
					'added_by'     => get_current_user_id(),
					'institute_id' => $institute_id
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_notes", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => esc_html__( 'Note added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch note to update */
	public static function fetch_note() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_notes WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		$document_ids = unserialize( $row->document_ids );

		$course_data        = WL_MIM_Helper::get_active_courses_institute( $institute_id );
		$get_active_batches = WL_MIM_Helper::get_active_batches_institute( $institute_id );
		?><?php $nonce = wp_create_nonce( "update-note-$id" );
		ob_start(); ?>
        <input type="hidden" name="update-note-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-update-note">
        <div class="row">
            <div class="col-sm-12 form-group">
                <label for="wlim-note-title_update" class="col-form-label">* <?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?>:</label>
                <input name="title" type="text" class="form-control" id="wlim-note-title_update" placeholder="<?php esc_html_e( "Title", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->title ); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="wlim-note-batch_update" class="col-form-label">* <?php esc_html_e( "Batch", WL_MIM_DOMAIN ); ?>:</label>
            <select name="batch" class="form-control selectpicker" id="wlim-note-batch_update">
                <option value=""><?php esc_html_e( "-------- Select a Batch --------", WL_MIM_DOMAIN ); ?></option>
				<?php
				if ( count( $get_active_batches ) > 0 ) {
					foreach ( $get_active_batches as $active_batch ) {
						$batch  = $active_batch->batch_code . ' ( ' . $active_batch->batch_name . ' )';
						$course = '-';
						if ( $active_batch->course_id && isset( $course_data[ $active_batch->course_id ] ) ) {
							$course_name = $course_data[ $active_batch->course_id ]->course_name;
							$course_code = $course_data[ $active_batch->course_id ]->course_code;
							$course      = "$course_name ($course_code)";
						} ?>
                        <option value="<?php echo esc_attr( $active_batch->id ); ?>"><?php echo esc_html( "$batch ( $course )" ); ?></option>
						<?php
					}
				} ?>
            </select>
        </div>
        <div class="row">
            <div class="col-sm-12 form-group">
                <label for="wlim-note-description_update" class="col-form-label"><?php esc_html_e( 'Description', WL_MIM_DOMAIN ); ?>:</label>
                <textarea name="description" class="form-control" rows="4" id="wlim-note-description_update" placeholder="<?php esc_html_e( "Description", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->description ); ?></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 form-group">
                <label for="wlim-note-date_update" class="col-form-label">* <?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?>:</label>
                <input name="notes_date" type="text" class="form-control wlim-notes_date_update" id="wlim-note-date_update" placeholder="<?php esc_html_e( "Date", WL_MIM_DOMAIN ); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 form-group">
                <label for="wlim-note-documents_update" class="col-form-label">* <?php esc_html_e( 'File(s)', WL_MIM_DOMAIN ); ?>
                    :</label><br>
                <ul class="list-group ml-0 mb-3">
					<?php
					foreach ( $document_ids as $key => $document_id ) : ?>
                        <li class="list-group-item">
                            <strong class="mr-4"><?php echo esc_html( $key + 1 ); ?>.</strong><a href="<?php echo wp_get_attachment_url( $document_id ); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><?php echo basename( get_attached_file( $document_id ) ); ?></a>
                            <input type="hidden" name="existing_document_ids[]" value="<?php echo esc_attr( $document_id ); ?>">
                            <a href="javascript:void(0)" class="float-right text-danger wlmim-remove-note"><?php esc_html_e( 'Remove', WL_MIM_DOMAIN ); ?></a>
                        </li>
					<?php
					endforeach; ?>
                </ul>
                <input type="file" name="documents[]" id="wlim-note-documents_update" multiple>
                (<?php esc_html_e( 'Hold Ctrl to select multiple files', WL_MIM_DOMAIN ); ?>)
            </div>
        </div>
        <hr>
        <div class="form-check pl-0">
            <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-note-is_active_update" <?php echo boolval( $row->is_active ) ? "checked" : ""; ?>>
            <label class="form-check-label" for="wlim-note-is_active_update">
				<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
            </label>
        </div>
		<input type="hidden" name="note_id" value="<?php echo esc_attr( $row->id ); ?>">
		<?php $html         = ob_get_clean();
		$wlim_date_selector = '.wlim-notes_date_update';

		$json = json_encode( array(
			'wlim_date_selector' => esc_attr( $wlim_date_selector ),
			'notes_date_exist'   => boolval( $row->notes_date ),
			'notes_date'         => esc_attr( $row->notes_date ),
			'batch_id'           => esc_attr( $row->batch_id )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update note */
	public static function update_note() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['note_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-note-$id"], "update-note-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_notes WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		$title       = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$batch_id    = isset( $_POST['batch'] ) ? intval( sanitize_text_field( $_POST['batch'] ) ) : null;
		$documents   = ( isset( $_FILES['documents'] ) && is_array( $_FILES['documents'] ) ) ? $_FILES['documents'] : null;
		$notes_date  = ( isset( $_POST['notes_date'] ) && ! empty( $_POST['notes_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_POST['notes_date'] ) ) ) : null;
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';
		$is_active   = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		$existing_document_ids = ( isset( $_POST['existing_document_ids'] ) && is_array( $_POST['existing_document_ids'] ) ) ? $_POST['existing_document_ids'] : array();

		/* Validations */
		$errors = array();
		if ( empty( $batch_id ) ) {
			$errors['batch'] = esc_html__( 'Please select a batch.', WL_MIM_DOMAIN );
		}

		if ( empty( $title ) ) {
			$errors['title'] = esc_html__( 'Please provide note title.', WL_MIM_DOMAIN );
		}

		if ( strlen( $title ) > 255 ) {
			$errors['title'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( count( $existing_document_ids ) < 1 && ( empty( $documents ) || ! is_array( $documents ) || ! count( $documents ) ) ) {
			wp_send_json_error( esc_html__( 'Please provide note file(s).', WL_MIM_DOMAIN ) );
		} else {
			if ( isset( $documents["tmp_name"] ) && is_array( $documents ) && count( $documents ) ) {
				foreach ( $documents["tmp_name"] as $key => $document ) {
					$file_type          = $documents['type'][ $key ];
					$allowed_file_types = WL_MIM_Helper::get_notice_attachment_file_types();
					if ( ! in_array( $file_type, $allowed_file_types ) ) {
						wp_send_json_error( esc_html__( 'Please provide attachment in PDF, JPG, JPEG PNG, DOC, DOCX, XLS, XLSX, PPT or PPTX format.', WL_MIM_DOMAIN ) );
					}
				}
			}
		}

		$saved_documents_ids = unserialize( $row->document_ids );
		if ( count( array_diff( $existing_document_ids, $saved_documents_ids ) ) ) {
			wp_send_json_error( esc_html__( 'Please provide valid documents.', WL_MIM_DOMAIN ) );
		}

		$document_ids_to_delete = array_diff( $saved_documents_ids, $existing_document_ids );
		$document_ids_to_save   = array_intersect( $saved_documents_ids, $existing_document_ids );

		if ( empty( $notes_date ) ) {
			$errors['notes_date'] = esc_html__( 'Please provide notes issued date.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND id = $batch_id AND institute_id = $institute_id" );

		if ( ! $count ) {
			$errors['batch'] = esc_html__( 'Please select a valid batch.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$document_ids = array();
				if ( isset( $documents["tmp_name"] ) && is_array( $documents ) && count( $documents ) ) {
					foreach ( $documents["tmp_name"] as $key => $document ) {
						if ( $documents['name'][ $key ] ) {
							$document    = array(
								'name'     => sanitize_file_name( $documents['name'][ $key ] ),
								'type'     => $documents['type'][ $key ],
								'tmp_name' => $documents['tmp_name'][ $key ],
								'error'    => $documents['error'][ $key ],
								'size'     => $documents['size'][ $key ]
							);
							$_FILES      = array( 'document' => $document );
							$document_id = media_handle_upload( 'document', 0 );
							if ( is_wp_error( $document_id ) ) {
								throw new Exception( $document_id->get_error_message() );
							}
							array_push( $document_ids, $document_id );
						}
					}
				}

				$document_ids = array_merge( $document_ids_to_save, $document_ids );
				$document_ids = serialize( $document_ids );

				$data = array(
					'title'        => $title,
					'description'  => $description,
					'document_ids' => $document_ids,
					'notes_date'   => $notes_date,
					'batch_id'     => $batch_id,
					'is_active'    => $is_active,
					'updated_at'   => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_notes", $data, array(
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				if ( count( $document_ids_to_delete ) ) {
					foreach ( $document_ids_to_delete as $document_id_to_delete ) {
						wp_delete_attachment( $document_id_to_delete, true );
					}
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => esc_html__( 'Note updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete note */
	public static function delete_note() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-note-$id"], "delete-note-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_notes WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( "{$wpdb->prefix}wl_min_notes", array(
				'id'           => $id,
				'institute_id' => $institute_id
			) );
			if ( ! $success ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$document_ids = unserialize( $row->document_ids );
			if ( is_array( $document_ids ) && count( $document_ids ) ) {
				foreach ( $document_ids as $document_id ) {
					wp_delete_attachment( $document_id, true );
				}
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Note removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	/* View student note */
	public static function view_student_note() {
		if ( ! current_user_can( WL_MIM_Helper::get_student_capability() ) ) {
			die();
		}
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$student      = WL_MIM_StudentHelper::get_student();
		$institute_id = $student->institute_id;

		$id  = intval( sanitize_text_field( $_REQUEST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_notes WHERE id = $id AND institute_id = $institute_id AND batch_id = $student->batch_id AND is_active = 1" );

		if ( ! $row ) {
			die();
		}

		$document_ids = unserialize( $row->document_ids );

		$course_data        = WL_MIM_Helper::get_active_courses_institute( $institute_id );
		$get_active_batches = WL_MIM_Helper::get_active_batches_institute( $institute_id );
		ob_start(); ?>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <h5><?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?>:&nbsp;<span class="font-weight-normal"><?php echo esc_html( $row->title ); ?></span></h5>
			</li>
            <li class="list-group-item">
                <h6><?php esc_html_e( 'Description', WL_MIM_DOMAIN ); ?>:&nbsp;<span class="font-weight-normal"><?php echo esc_html( $row->description ); ?></span></h6>
			</li>
            <li class="list-group-item">
                <h6><?php esc_html_e( 'File(s)', WL_MIM_DOMAIN ); ?>:&nbsp;
                    <ul class="list-group list-group-flush">
						<?php
						if ( $document_ids ) :
							foreach ( $document_ids as $key => $document_id ) : ?>
                                <li class="list-group-item">
                                    <strong class="mr-4"><?php echo esc_html( $key + 1 ); ?>.</strong><a href="<?php echo wp_get_attachment_url( $document_id ); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><?php echo basename( get_attached_file( $document_id ) ); ?></a>
                                </li>
							<?php
							endforeach;
						endif;
						?>
                    </ul>
                </h6>
            </li>
            <li class="list-group-item">
                <h6><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?>:&nbsp;<span class="font-weight-normal"><?php echo date_format( date_create( $row->notes_date ), "d-m-Y" ); ?></span></h6>
			</li>
            <li class="list-group-item">
                <h6><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?>:&nbsp;<span class="font-weight-normal"><?php echo date_format( date_create( $row->created_at ), "d-m-Y g:i A" ); ?></span></h6>
			</li>
        </ul>
		<?php $html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/* Check permission to manage note */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_notes' ) || ! $institute_id ) {
			die();
		}
	}
}
?>