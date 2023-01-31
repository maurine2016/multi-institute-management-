<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

class WL_MIM_Custom_Field {
	/* Get custom field data to display on table */
	public static function get_custom_field_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_custom_fields WHERE institute_id = $institute_id ORDER BY id DESC" );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id         = $row->id;
				$field_name = $row->field_name;
				$is_active  = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$date       = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );

				$results["data"][] = array(
					esc_html( $field_name ),
					esc_html( $is_active ),
					'<a class="mr-3" href="#update-custom-field" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-custom-field-security="' . wp_create_nonce( "delete-custom-field-$id" ) . '"delete-custom-field-id="' . $id . '" class="delete-custom-field"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new custom field */
	public static function add_custom_field() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-custom-field'], 'add-custom-field' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$field_name = isset( $_POST['field_name'] ) ? sanitize_text_field( $_POST['field_name'] ) : null;
		$is_active  = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $field_name ) ) {
			$errors['field_name'] = esc_html__( 'Please specify field name.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'field_name'   => $field_name,
					'is_active'    => $is_active,
					'institute_id' => $institute_id
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_custom_fields", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Custom field added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch custom field to update */
	public static function fetch_custom_field() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_custom_fields WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		?>
        <form id="wlim-update-custom-field-form">
			<?php $nonce = wp_create_nonce( "update-custom-field-$id" ); ?>
            <input type="hidden" name="update-custom-field-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
            <div class="form-group">
                <label for="wlim-fee-type-field_name_update" class="col-form-label"><?php esc_html_e( 'Field Name', WL_MIM_DOMAIN ); ?>:</label>
                <input name="field_name" type="text" class="form-control" id="wlim-fee-type-field_name_update" placeholder="<?php esc_html_e( "Field Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->field_name ); ?>">
            </div>
            <div class="form-check pl-0">
                <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-custom-field-is_active_update" <?php echo boolval( $row->is_active ) ? "checked" : ""; ?>>
                <label class="form-check-label" for="wlim-custom-field-is_active_update">
					<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                </label>
            </div>
            <input type="hidden" name="custom_field_id" value="<?php echo esc_attr( $row->id ); ?>">
        </form>
		<?php
		die();
	}

	/* Update custom field */
	public static function update_custom_field() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['custom_field_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-custom-field-$id"], "update-custom-field-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$field_name = isset( $_POST['field_name'] ) ? sanitize_text_field( $_POST['field_name'] ) : null;
		$is_active  = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $field_name ) ) {
			$errors['field_name'] = esc_html__( 'Please specify field name.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'field_name' => $field_name,
					'is_active'  => $is_active,
					'updated_at' => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_custom_fields", $data, array(
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Custom field updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete custom field */
	public static function delete_custom_field() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-custom-field-$id"], "delete-custom-field-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$custom_field = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_custom_fields WHERE id = $id AND institute_id = $institute_id" );

			if ( ! $custom_field ) {
				throw new Exception( esc_html__( 'Custom field not found.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( "{$wpdb->prefix}wl_min_custom_fields", array( 'id' => $id )
			);
			if ( ! $success ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Custom field removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* Check permission to manage custom field */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_settings' ) || ! $institute_id ) {
			die();
		}
	}
}
?>