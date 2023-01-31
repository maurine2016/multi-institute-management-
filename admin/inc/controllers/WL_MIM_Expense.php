<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

class WL_MIM_Expense {
	/* Get expense data to display on table */
	public static function get_expense_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_expense WHERE institute_id = $institute_id ORDER BY id DESC" );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id          = $row->id;
				$title       = $row->title;
				$amount      = $row->amount;
				$date        = date_format( date_create( $row->consumption_date ), "d-m-Y" );
				$description = $row->description ? $row->description : '-';
				$notes       = $row->notes ? $row->notes : '-';
				$added_by    = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
				$added_on    = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );

				$results["data"][] = array(
					'<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html( $title ),
					esc_html( $amount ),
					esc_html( $date ),
					esc_html( $added_by ),
					esc_html( $added_on ),
					'<a class="mr-3" href="#update-expense" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-expense-security="' . wp_create_nonce( "delete-expense-$id" ) . '"delete-expense-id="' . $id . '" class="delete-expense"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new expense */
	public static function add_expense() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-expense'], 'add-expense' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$title            = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$description      = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';
		$amount           = number_format( isset( $_POST['amount'] ) ? max( floatval( sanitize_text_field( $_POST['amount'] ) ), 0 ) : 0, 2, '.', '' );
		$consumption_date = ( isset( $_POST['consumption_date'] ) && ! empty( $_POST['consumption_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['consumption_date'] ) ) ) : null;
		$notes            = isset( $_POST['notes'] ) ? sanitize_textarea_field( $_POST['notes'] ) : '';

		/* Validations */
		$errors = array();
		if ( empty( $title ) ) {
			$errors['title'] = esc_html__( 'Please provide title.', WL_MIM_DOMAIN );
		}

		if ( strlen( $title ) > 255 ) {
			$errors['title'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( $amount <= 0 ) {
			$errors['amount'] = esc_html__( 'Please specify a valid amount.', WL_MIM_DOMAIN );
		}

		if ( empty( $consumption_date ) ) {
			$errors['consumption_date'] = esc_html__( 'Please provide consumption date.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'title'            => $title,
					'description'      => $description,
					'amount'           => $amount,
					'consumption_date' => $consumption_date,
					'notes'            => $notes,
					'added_by'         => get_current_user_id(),
					'institute_id'     => $institute_id
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_expense", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => esc_html__( 'Expense added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch expense to update */
	public static function fetch_expense() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_expense WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		?><?php $nonce = wp_create_nonce( "update-expense-$id" );
		ob_start();
		?>
        <input type="hidden" name="update-expense-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-update-expense">
        <div class="row">
            <div class="col-sm-12 form-group">
                <label for="wlim-expense-title_update" class="col-form-label">* <?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?>:</label>
                <input name="title" type="text" class="form-control" id="wlim-expense-title_update" placeholder="<?php esc_html_e( "Title", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->title ); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 form-group">
                <label for="wlim-expense-description_update" class="col-form-label"><?php esc_html_e( 'Description', WL_MIM_DOMAIN ); ?>:</label>
                <textarea name="description" class="form-control" rows="3" id="wlim-expense-description_update" placeholder="<?php esc_html_e( "Description", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->description ); ?></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="wlim-expense-amount_update" class="col-form-label">* <?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?>:</label>
                <input name="amount" type="number" class="form-control" id="wlim-expense-amount_update" placeholder="<?php esc_html_e( "Amount", WL_MIM_DOMAIN ); ?>" min="0" step="any" value="<?php echo esc_attr( $row->amount ); ?>">
            </div>
            <div class="col-sm-6 form-group">
                <label for="wlim-expense-consumption_date_update" class="col-form-label">* <?php esc_html_e( 'Consumption Date', WL_MIM_DOMAIN ); ?>:</label>
                <input name="consumption_date" type="text" class="form-control wlim-consumption_date_update" id="wlim-expense-consumption_date_update" placeholder="<?php esc_html_e( "Consumption Date", WL_MIM_DOMAIN ); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 form-group">
                <label for="wlim-expense-notes_update" class="col-form-label"><?php esc_html_e( 'Notes', WL_MIM_DOMAIN ); ?>:</label>
                <textarea name="notes" class="form-control" rows="3" id="wlim-expense-notes_update" placeholder="<?php esc_html_e( "Note", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->notes ); ?></textarea>
            </div>
        </div>
		<input type="hidden" name="expense_id" value="<?php echo esc_attr( $row->id ); ?>">

		<?php $html         = ob_get_clean();
		$wlim_date_selector = '.wlim-consumption_date_update';

		$json = json_encode( array(
			'wlim_date_selector'     => esc_attr( $wlim_date_selector ),
			'consumption_date_exist' => boolval( $row->consumption_date ),
			'consumption_date'       => esc_attr( $row->consumption_date )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update expense */
	public static function update_expense() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['expense_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-expense-$id"], "update-expense-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$title            = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$description      = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';
		$amount           = number_format( isset( $_POST['amount'] ) ? max( floatval( sanitize_text_field( $_POST['amount'] ) ), 0 ) : 0, 2, '.', '' );
		$consumption_date = ( isset( $_POST['consumption_date'] ) && ! empty( $_POST['consumption_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['consumption_date'] ) ) ) : null;
		$notes            = isset( $_POST['notes'] ) ? sanitize_textarea_field( $_POST['notes'] ) : '';

		/* Validations */
		$errors = array();
		if ( empty( $title ) ) {
			$errors['title'] = esc_html__( 'Please provide title.', WL_MIM_DOMAIN );
		}

		if ( strlen( $title ) > 255 ) {
			$errors['title'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( $amount <= 0 ) {
			$errors['amount'] = esc_html__( 'Please specify a valid amount.', WL_MIM_DOMAIN );
		}

		if ( empty( $consumption_date ) ) {
			$errors['consumption_date'] = esc_html__( 'Please provide consumption date.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'title'            => $title,
					'description'      => $description,
					'amount'           => $amount,
					'consumption_date' => $consumption_date,
					'notes'            => $notes,
					'updated_at'       => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_expense", $data, array(
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array( 'message' => esc_html__( 'Expense updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete expense */
	public static function delete_expense() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-expense-$id"], "delete-expense-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( "{$wpdb->prefix}wl_min_expense", array(
				'id'           => $id,
				'institute_id' => $institute_id
			) );
			if ( ! $success ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Expense removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* Check permission to manage expense */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_expense' ) || ! $institute_id ) {
			die();
		}
	}
}
?>