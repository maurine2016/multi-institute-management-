<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

class WL_MIM_Notice {
	/* Get notice data to display on table */
	public static function get_notice_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_notices WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );
		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id        = $row->id;
				$title     = stripcslashes( $row->title );
				$url       = $row->url;
				$priority  = $row->priority;
				$is_acitve = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$added_on  = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
				$added_by  = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$results["data"][] = array(
					'<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html( $title ),
					esc_html( $url ),
					esc_html( $priority ),
					esc_html( $is_acitve ),
					esc_html( $added_on ),
					esc_html( $added_by ),
					'<a class="mr-3" href="#update-notice" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-notice-security="' . wp_create_nonce( "delete-notice-$id" ) . '"delete-notice-id="' . $id . '" class="delete-notice"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new notice */
	public static function add_notice() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-notice'], 'add-notice' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$title      = isset( $_POST['title'] ) ? sanitize_textarea_field( $_POST['title'] ) : '';
		$link_to    = isset( $_POST['notice_link_to'] ) ? sanitize_text_field( $_POST['notice_link_to'] ) : 'url';
		$attachment = ( isset( $_FILES['attachment'] ) && is_array( $_FILES['attachment'] ) ) ? $_FILES['attachment'] : null;
		$url        = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : null;
		$priority   = isset( $_POST['priority'] ) ? intval( sanitize_text_field( $_POST['priority'] ) ) : 10;
		$is_active  = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $title ) ) {
			$errors['title'] = esc_html__( 'Please provide notice title.', WL_MIM_DOMAIN );
		}

		if ( empty( $link_to ) ) {
			wp_send_json_error( esc_html__( 'Please select notice link.', WL_MIM_DOMAIN ) );
		} else {
			if ( $link_to == 'attachment' ) {
				if ( ! empty( $attachment ) ) {
					$file_name          = sanitize_file_name( $attachment['name'] );
					$file_type          = $attachment['type'];
					$allowed_file_types = WL_MIM_Helper::get_notice_attachment_file_types();

					if ( ! in_array( $file_type, $allowed_file_types ) ) {
						$errors['attachment'] = esc_html__( 'Please provide attachment in PDF, JPG, JPEG PNG, DOC, DOCX, XLS, XLSX, PPT or PPTX format.', WL_MIM_DOMAIN );
					}
				} else {
					$errors['attachment'] = esc_html__( 'Please provide valid attachment.', WL_MIM_DOMAIN );
				}
			} elseif ( $link_to == 'url' ) {
				if ( empty( $url ) ) {
					$errors['url'] = esc_html__( 'Please provide valid url.', WL_MIM_DOMAIN );
				}
			} else {
				wp_send_json_error( esc_html__( 'Please select valid notice link.', WL_MIM_DOMAIN ) );
			}
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				if ( ! empty( $attachment ) ) {
					$attachment = media_handle_upload( 'attachment', 0 );
					if ( is_wp_error( $attachment ) ) {
						throw new Exception( esc_html__( $attachment->get_error_message(), WL_MIM_DOMAIN ) );
					}
				}

				$data = array(
					'title'        => $title,
					'url'          => $url,
					'attachment'   => $attachment,
					'link_to'      => $link_to,
					'priority'     => $priority,
					'is_active'    => $is_active,
					'added_by'     => get_current_user_id(),
					'institute_id' => $institute_id
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_notices", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Notice added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch notice to update */
	public static function fetch_notice() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_notices WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		?><?php $nonce = wp_create_nonce( "update-notice-$id" );
		ob_start();
		?>
        <input type="hidden" name="update-notice-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-update-notice">
        <div class="form-group">
            <label for="wlim-notice-title_update" class="col-form-label">* <?php esc_html_e( 'Notice Title', WL_MIM_DOMAIN ); ?>:</label>
            <textarea name="title" class="form-control" rows="3" id="wlim-notice-title_update" placeholder="<?php esc_html_e( "Notice Title", WL_MIM_DOMAIN ); ?>"><?php echo stripcslashes( $row->title ); ?></textarea>
        </div>
        <div class="form-group mt-3 pl-0 pt-3 border-top">
            <label><?php esc_html_e( 'Link to', WL_MIM_DOMAIN ); ?>:</label><br>
            <div class="row">
                <div class="col">
                    <label class="radio-inline"><input type="radio" name="notice_link_to" value="attachment" id="wlim-notice-attachment_update"><?php esc_html_e( 'Attachment', WL_MIM_DOMAIN ); ?></label>
                </div>
                <div class="col">
                    <label class="radio-inline"><input type="radio" name="notice_link_to" value="url" id="wlim-notice-url_update"><?php esc_html_e( 'URL', WL_MIM_DOMAIN ); ?></label>
                </div>
            </div>
        </div>
        <div class="form-group wlim-notice-attachment">
            <input type="hidden" name="attachment_in_db" value="<?php echo esc_attr( $row->attachment ); ?>">
            <label for="wlim-notice-attachment_update" class="col-form-label"><?php esc_html_e( 'Attachment', WL_MIM_DOMAIN ); ?>:</label><br>
			<?php if ( ! empty ( $row->attachment ) ) { ?>
                <a href="<?php echo wp_get_attachment_url( $row->attachment ); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><?php esc_html_e( 'View Attachment', WL_MIM_DOMAIN ); ?></a>
                <br><input type="hidden" name="attachment_in_db" value="<?php echo esc_attr( $row->attachment ); ?>">
			<?php } ?>
            <input name="attachment" type="file" id="wlim-notice-attachment_update">
        </div>
        <div class="form-group wlim-notice-url">
            <label for="wlim-notice-url_update" class="col-form-label"><?php esc_html_e( 'URL', WL_MIM_DOMAIN ); ?>:</label>
            <input name="url" type="text" class="form-control" id="wlim-notice-url_update" placeholder="<?php esc_html_e( "Notice URL", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->url ); ?>">
        </div>
        <div class="form-group">
            <label for="wlim-notice-priority_update" class="col-form-label"><?php esc_html_e( 'Priority', WL_MIM_DOMAIN ); ?>:</label>
            <input name="priority" type="number" class="form-control" id="wlim-notice-priority_update" placeholder="<?php esc_html_e( "Notice Priority", WL_MIM_DOMAIN ); ?>" step="1" value="<?php echo esc_attr( $row->priority ); ?>">
        </div>
        <div class="form-check pl-0">
            <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-notice-is_active_update" <?php echo boolval( $row->is_active ) ? "checked" : ""; ?>>
            <label class="form-check-label" for="wlim-notice-is_active_update">
				<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
            </label>
        </div>
		<input type="hidden" name="notice_id" value="<?php echo esc_attr( $row->id ); ?>">
		<?php $html  = ob_get_clean();
		$link_to_url = esc_attr( $row->link_to ) == 'url';

		$json = json_encode( array(
			'link_to_url' => boolval( $link_to_url )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update notice */
	public static function update_notice() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['notice_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-notice-$id"], "update-notice-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$title            = isset( $_POST['title'] ) ? sanitize_textarea_field( $_POST['title'] ) : '';
		$link_to          = isset( $_POST['notice_link_to'] ) ? sanitize_text_field( $_POST['notice_link_to'] ) : 'url';
		$attachment       = ( isset( $_FILES['attachment'] ) && is_array( $_FILES['attachment'] ) ) ? $_FILES['attachment'] : null;
		$attachment_in_db = isset( $_POST['attachment_in_db'] ) ? intval( sanitize_text_field( $_POST['attachment_in_db'] ) ) : null;
		$url              = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : null;
		$priority         = isset( $_POST['priority'] ) ? intval( sanitize_text_field( $_POST['priority'] ) ) : 10;
		$is_active        = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $link_to ) ) {
			wp_send_json_error( esc_html__( 'Please select notice link.', WL_MIM_DOMAIN ) );
		} else {
			if ( $link_to == 'attachment' ) {
				if ( ! empty( $attachment ) ) {
					$file_name          = sanitize_file_name( $attachment['name'] );
					$file_type          = $attachment['type'];
					$allowed_file_types = WL_MIM_Helper::get_notice_attachment_file_types();

					if ( ! in_array( $file_type, $allowed_file_types ) ) {
						$errors['attachment'] = esc_html__( 'Please provide attachment in PDF, JPG, JPEG PNG, DOC, DOCX, XLS, XLSX, PPT or PPTX format.', WL_MIM_DOMAIN );
					}
				} else {
					if ( empty( $attachment_in_db ) ) {
						$errors['attachment'] = esc_html__( 'Please provide valid attachment.', WL_MIM_DOMAIN );
					}
				}
			} elseif ( $link_to == 'url' ) {
				if ( empty( $url ) ) {
					$errors['url'] = esc_html__( 'Please provide valid url.', WL_MIM_DOMAIN );
				}
			} else {
				wp_send_json_error( esc_html__( 'Please select valid notice link.', WL_MIM_DOMAIN ) );
			}
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'title'      => $title,
					'url'        => $url,
					'link_to'    => $link_to,
					'priority'   => $priority,
					'is_active'  => $is_active,
					'updated_at' => date( 'Y-m-d H:i:s' )
				);

				if ( ! empty( $attachment ) ) {
					$attachment = media_handle_upload( 'attachment', 0 );
					if ( is_wp_error( $attachment ) ) {
						throw new Exception( esc_html__( $attachment->get_error_message(), WL_MIM_DOMAIN ) );
					}
					$data['attachment'] = $attachment;
				}

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_notices", $data, array(
					'is_deleted'   => 0,
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				if ( ! empty( $attachment ) && ! empty( $attachment_in_db ) ) {
					wp_delete_attachment( $attachment_in_db, true );
				}

				wp_send_json_success( array( 'message' => esc_html__( 'Notice updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete notice */
	public static function delete_notice() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-notice-$id"], "delete-notice-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->update( "{$wpdb->prefix}wl_min_notices",
				array(
					'is_deleted' => 1,
					'deleted_at' => date( 'Y-m-d H:i:s' )
				), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
			);
			if ( $success === false ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Notice removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* Check permission to manage noticeboard */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_noticeboard' ) || ! $institute_id ) {
			die();
		}
	}
}
?>