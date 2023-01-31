<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SMSHelper.php' );

class WL_MIM_Fee {
	/* Get installment data to display on table */
	public static function get_installment_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id              = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
		$general_receipt_prefix    = WL_MIM_SettingHelper::get_general_receipt_prefix_settings( $institute_id );

		$data         = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );
		$student_data = $wpdb->get_results( "SELECT id, first_name, last_name, enrollment_id FROM {$wpdb->prefix}wl_min_students WHERE institute_id = $institute_id ORDER BY first_name, last_name, id DESC", OBJECT_K );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id             = $row->id;
				$receipt        = WL_MIM_Helper::get_receipt_with_prefix( $id, $general_receipt_prefix );
				$fees           = unserialize( $row->fees );
				$amount         = WL_MIM_Helper::get_fees_total( $fees['paid'] );
				$payment_method = $row->payment_method ? $row->payment_method : '-';
				$payment_id     = $row->payment_id ? $row->payment_id : '-';
				$date           = date_format( date_create( $row->created_at ), "d-m-Y" );
				$added_by       = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$student_name = '-';
				if ( $row->student_id && isset( $student_data[ $row->student_id ] ) ) {
					$student_name  = $student_data[ $row->student_id ]->first_name . " " . $student_data[ $row->student_id ]->last_name;
					
					if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
						$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_data[ $row->student_id ]->enrollment_id, $general_enrollment_prefix );
					}else {
						$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_data[ $row->student_id ]->id, $general_enrollment_prefix );
					}
				}

				$results["data"][] = array(
					'<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html( $receipt ) . '<a class="ml-2" href="#print-installment-fee-receipt" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-print"></i></a>',
					esc_html( $amount ),
					esc_html( $enrollment_id ),
					esc_html( $student_name ),
					esc_html( $payment_method ),
					esc_html( $payment_id ),
					esc_html( $date ),
					esc_html( $added_by ),
					'<a class="mr-3" href="#update-installment" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-installment-security="' . wp_create_nonce( "delete-installment-$id" ) . '"delete-installment-id="' . $id . '" class="delete-installment"> <i class="fa fa-trash text-danger"></i></a>',
					'<a href="#print-student-fees-report" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new installment */
	public static function add_installment() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-installment'], 'add-installment' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$amount     = ( isset( $_POST['amount'] ) && is_array( $_POST['amount'] ) ) ? $_POST['amount'] : null;
		$student_id = isset( $_POST['student'] ) ? intval( sanitize_text_field( $_POST['student'] ) ) : null;
		$invoice_id = isset( $_POST['invoice'] ) ? intval( sanitize_text_field( $_POST['invoice'] ) ) : NULL;
		$created_at = ( isset( $_POST['created_at'] ) && ! empty( $_POST['created_at'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['created_at'] ) ) ) : NULL;

		$errors = array();

		$student = $wpdb->get_row( "SELECT fees, course_id, phone FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND id = $student_id AND institute_id = $institute_id" );

		if ( ! $student ) {
			wp_send_json_error( esc_html__( 'Please select a valid student.', WL_MIM_DOMAIN ) );
		}

		$fees = unserialize( $student->fees );
		if ( $invoice_id ) {
			$invoice = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_invoices WHERE student_id = $student_id AND id = $invoice_id AND status = 'pending' AND institute_id = $institute_id" );
			if ( ! $invoice ) {
				wp_send_json_error( esc_html__( 'Please select a valid invoice.', WL_MIM_DOMAIN ) );
			}
			$invoice_fees = unserialize( $invoice->fees );

			foreach( $fees['paid'] as $key => $invoice_amount ) {
				$invoice_pending_amount = $fees['payable'][$key] - $invoice_amount;
				if ( ! ( $invoice_pending_amount > 0 ) ) {
					array_splice( $invoice_fees['paid'], $key, 1 );
				}
			}

			$amount = array();
			$amount['paid'] = $invoice_fees['paid'];
		}

		if ( empty( $amount ) ) {
			wp_send_json_error( esc_html__( 'Please provide a valid installment.', WL_MIM_DOMAIN ) );
		}

		if ( ! array_key_exists( 'paid', $amount ) ) {
			wp_send_json_error( esc_html__( 'Invalid installment.', WL_MIM_DOMAIN ) );
		}

		foreach ( $amount['paid'] as $key => $value ) {
			if ( ! is_numeric( $value ) ) {
				$value = 0;
			}
			if ( $value < 0 ) {
				wp_send_json_error( esc_html__( 'Please provide a valid installment.', WL_MIM_DOMAIN ) );
			} else {
				$amount['paid'][ $key ] = number_format( isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0, 2, '.', '' );
			}
		}

		$course = $wpdb->get_row( "SELECT fees FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $student->course_id AND institute_id = $institute_id" );

		if ( ! $course ) {
			$errors['student'] = esc_html__( 'Student is not enrolled in any course.', WL_MIM_DOMAIN );
		}

		$installment['type'] = $fees['type'];

		$i = 0;
		foreach ( $fees['paid'] as $key => $value ) {
			$pending_amount = $fees['payable'][ $key ] - $value;
			if ( $pending_amount > 0 ) {
				$installment['paid'][ $key ] = $amount['paid'][ $i ];
				$fees['paid'][ $key ]        += $amount['paid'][ $i ];
				if ( $fees['payable'][ $key ] < $fees['paid'][ $key ] ) {
					wp_send_json_error( esc_html__( "Total amount exceeded payable amount for " . $fees['type'][ $key ] . ".", WL_MIM_DOMAIN ) );
				}
				$i ++;
				$fees['paid'][ $key ]        = number_format( max( floatval( $fees['paid'][ $key ] ), 0 ), 2, '.', '' );
				$installment['paid'][ $key ] = number_format( max( floatval( $installment['paid'][ $key ] ), 0 ), 2, '.', '' );
			} else {
				$installment['paid'][ $key ] = number_format( 0, 2, '.', '' );
			}
		}

		if ( array_sum( $installment['paid'] ) <= 0 ) {
			wp_send_json_error( esc_html__( 'Invalid installment.', WL_MIM_DOMAIN ) );
		}

		/* SMS text */
		$installment_count = 0;
		$fees_data         = '';
		foreach ( $installment['type'] as $inst_key => $inst_type ) {
			if ( $installment['paid'][ $inst_key ] > 0 ) {
				$installment_count ++;
				$fees_data .= $inst_type . ": {$installment['paid'][$inst_key]} ";
			}
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$installment = serialize( $installment );
				$fees        = serialize( $fees );

				$data = array(
					'fees'         => $installment,
					'student_id'   => $student_id,
				    'created_at'   => $created_at,
					'added_by'     => get_current_user_id(),
					'institute_id' => $institute_id
				);

				if ( $invoice ) {
					$data['invoice_id'] = $invoice_id;
					$success = $wpdb->update( "{$wpdb->prefix}wl_min_invoices",
						array(
							'status'     => 'paid',
							'updated_at' => date('Y-m-d H:i:s')
						),
						array( 'id' => $invoice_id, 'institute_id' => $institute_id )
					);
					if ( $success === false ) {
			  			throw new Exception( __( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
					}
				}

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_installments", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$data = array(
					'fees'       => $fees,
					'updated_at' => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_students", $data, array(
					'is_deleted'   => 0,
					'id'           => $student_id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				if ( $installment_count > 0 ) {
					/* Get SMS template */
					$sms_template_fees_submitted = WL_MIM_SettingHelper::get_sms_template_fees_submitted( $institute_id );

					/* Get SMS settings */
					$sms = WL_MIM_SettingHelper::get_sms_settings( $institute_id );

					if ( $sms_template_fees_submitted['enable'] ) {
						$sms_message = $sms_template_fees_submitted['message'];
						$template_id = $sms_template_fees_submitted['template_id'];
						$sms_message = str_replace( '[FEES]', $fees_data, $sms_message );
						$sms_message = str_replace( '[DATE]', date_format( new DateTime( $data['updated_at'] ), "d-m-Y" ), $sms_message );
						/* Send SMS */
						WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_message, $student->phone, $template_id  );
					}
				}
				wp_send_json_success( array( 'message' => esc_html__( 'Installment added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch installment to update */
	public static function fetch_installment() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		$student = $wpdb->get_row( "SELECT id, course_id, fees, first_name, last_name FROM {$wpdb->prefix}wl_min_students WHERE id = $row->student_id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}
		$course = $wpdb->get_row( "SELECT course_code, course_name, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $student->course_id AND institute_id = $institute_id" );
		if ( ! $course ) {
			die();
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count( $course->duration, $course->duration_in );

		$invoice = NULL;
		if ( $row->invoice_id ) {
			$invoice = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_invoices WHERE student_id = {$student->id} AND id = {$row->invoice_id} AND status = 'paid' AND institute_id = $institute_id" );
		}

		$fees         = unserialize( $student->fees );
		$pending_fees = number_format( WL_MIM_Helper::get_fees_total( $fees['payable'] ) - WL_MIM_Helper::get_fees_total( $fees['paid'] ), 2, '.', '' );
		$installment = unserialize( $row->fees );

		$created_at = date_format( date_create( $row->created_at ), "d-m-Y" );
		ob_start(); ?>
        <form id="wlim-update-installment-form">
			<?php $nonce = wp_create_nonce( "update-installment-$id" ); ?>
            <input type="hidden" name="update-installment-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
            <div class="row" id="wlim-student-enrollment_id">
                <div class="col">
                    <label class="col-form-label pb-0"><?php esc_html_e( 'Student', WL_MIM_DOMAIN ); ?>:</label>
                    <div class="card mb-3 mt-2">
                        <div class="card-block">
						<?php if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
                                            $student_id = $student->enrollment_id;
                                        } else {
                                            $student_id = $student->id;
                                        } ?>
                            <span class="text-dark"><?php echo esc_html( $student->first_name ) . " " . esc_html( $student->last_name ); ?> (<?php echo WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ); ?>)</span>
                        </div>
                    </div>
                    <ul class="list-group border-bottom mt-4 mb-3">
                        <li class="list-group-item"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?>:
                            <strong><?php echo esc_html( "$course->course_name ($course->course_code)" ); ?></strong>
                        </li>
                        <li class="list-group-item"><?php esc_html_e( 'Total Fees Payable', WL_MIM_DOMAIN ); ?>:
                        	<strong><?php echo WL_MIM_Helper::get_fees_total( $fees['payable'] ); ?></strong>
                        </li>
                    </ul>
                    <div class="fee_types_box">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Fee Type', WL_MIM_DOMAIN ); ?></th>
                        		<th><?php esc_html_e( 'Fee Period', WL_MIM_DOMAIN ); ?></th>
                                <th><?php esc_html_e( 'Amount Payable', WL_MIM_DOMAIN ); ?></th>
                                <th><?php esc_html_e( 'Installment Received', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                            </thead>
                            <tbody class="fee_types_rows fee_types_table">
							<?php
							foreach ( $fees['paid'] as $key => $amount ) {
								unset( $monthly_amount_payable );
								if ( isset( $fees['period'] ) && $fees['period'][ $key ] == 'monthly' ) {
									$monthly_amount_payable = number_format( $fees['payable'][ $key ] / $duration_in_month, 2, '.', '' );
								}
							?>
                                <tr>
                                    <td>
                                    	<span class="text-dark"><?php echo esc_html( $fees['type'][ $key ] ); ?></span>
                                    </td>
                                    <td>
			                            <span class="text-dark"><?php echo esc_html( isset( $fees['period'] ) ? WL_MIM_Helper::get_period_in()[$fees['period'][ $key ]] : WL_MIM_Helper::get_period_in()['one-time'] ); ?>
			                            </span>
                                    </td>
                                    <td>
                                    	<span class="text-dark"><?php echo isset( $monthly_amount_payable ) ? esc_attr( $monthly_amount_payable ) : esc_html( $fees['payable'][ $key ] ); ?></span>
		                    		<?php if ( isset( $monthly_amount_payable ) ) { ?>
		                    			<br>
		                    			<span class="text-dark">
		                    			<?php echo "* " . esc_html( $duration_in_month ) . " = " . esc_html( $fees['payable'][ $key ] ); ?>
		                    			</span>
		                    		<?php } ?>
                                    </td>
                                    <td>
                                    	<?php
                                    	if ( $invoice ) { ?>
                                    	<input type="number" disabled name="amount[paid][]" class="form-control text-dark" placeholder="<?php esc_html_e( 'Paid', WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $installment['paid'][ $key ] ); ?>">
                                    	<?php
                                    	} else { ?>
                                    	<input type="number" name="amount[paid][]" class="form-control" placeholder="<?php esc_html_e( 'Paid', WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $installment['paid'][ $key ] ); ?>">
                                    	<?php
                                    	} ?>
                                    </td>
                                </tr>
								<?php
							} ?>
                            </tbody>
                            <tfoot>
	                            <tr>
	                                <th><span><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></span></th>
	                                <th>-</th>
	                                <th><span><?php echo WL_MIM_Helper::get_fees_total( $fees['payable'] ); ?></span></th>
	                                <th><span class="ml-2"><?php echo WL_MIM_Helper::get_fees_total( $installment['paid'] ); ?></span></th>
	                            </tr>
                            </tfoot>
                        </table>
                    </div>
			        <div class="form-group">
			            <label for="wlim-installment-created_at_update" class="col-form-label">* <strong><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?>:</strong></label>
			            <input name="created_at" type="text" class="form-control wlim-created_at_update" id="wlim-installment-created_at_update" placeholder="<?php esc_html_e( "Date", WL_MIM_DOMAIN ); ?>" <?php echo esc_attr( $created_at ); ?>>
			        </div>
                    <ul class="list-group border-bottom mt-4 mb-3">
                        <li class="list-group-item"><?php esc_html_e( 'Total Installment Received', WL_MIM_DOMAIN ); ?>:
                        	<strong><?php echo WL_MIM_Helper::get_fees_total( $installment['paid'] ); ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
            <input type="hidden" name="installment_id" value="<?php echo esc_attr( $row->id ); ?>">
        </form>
		<?php
		$html = ob_get_clean();

		$wlim_created_at_selector = '.wlim-created_at_update';

		$json = json_encode( array(
			'wlim_date_selector'       => esc_attr( $wlim_date_selector ),
			'wlim_created_at_selector' => esc_attr( $wlim_created_at_selector ),
			'created_at_exist'         => boolval( $row->created_at ),
			'created_at'               => esc_attr( $row->created_at ),
			'invoice'                  => boolval( $invoice ),
			'close'                    => esc_html__( 'Close', WL_MIM_DOMAIN ),
			'update'                   => esc_html__( 'Update Installment', WL_MIM_DOMAIN ),
			'cancel'                   => esc_html__( 'Cancel', WL_MIM_DOMAIN )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update installment */
	public static function update_installment() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['installment_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-installment-$id"], "update-installment-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$installment = $wpdb->get_row( "SELECT fees, student_id FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );

		if ( ! $installment ) {
			$errors['amount'] = esc_html__( 'Installment not found.', WL_MIM_DOMAIN );
		}

		$invoice = NULL;
		if ( $installment->invoice_id ) {
			$invoice = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_invoices WHERE student_id = {$student->id} AND id = {$installment->invoice_id} AND status = 'paid' AND institute_id = $institute_id" );
		}
		if ( $invoice ) {
			die();
		}

		$amount     = ( isset( $_POST['amount'] ) && is_array( $_POST['amount'] ) ) ? $_POST['amount'] : null;
		$created_at = ( isset( $_POST['created_at'] ) && ! empty( $_POST['created_at'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['created_at'] ) ) ) : NULL;

		$errors = array();

		if ( empty( $amount ) ) {
			wp_send_json_error( esc_html__( 'Please provide a valid installment.', WL_MIM_DOMAIN ) );
		}

		if ( ! array_key_exists( 'paid', $amount ) ) {
			wp_send_json_error( esc_html__( 'Invalid installment.', WL_MIM_DOMAIN ) );
		}

		foreach ( $amount['paid'] as $key => $value ) {
			if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
				wp_send_json_error( esc_html__( 'Please provide a valid installment.', WL_MIM_DOMAIN ) );
			} else {
				$amount['paid'][ $key ] = number_format( isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0, 2, '.', '' );
			}
		}

		$student = $wpdb->get_row( "SELECT id, fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE id = $installment->student_id AND institute_id = $institute_id" );

		if ( ! $student ) {
			$errors['student'] = esc_html__( 'Please select a valid student.', WL_MIM_DOMAIN );
		}

		$fees        = unserialize( $student->fees );
		$installment = unserialize( $installment->fees );

		if ( count( $fees['paid'] ) != count( $amount['paid'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid installment.', WL_MIM_DOMAIN ) );
		}

		foreach ( $fees['paid'] as $key => $value ) {
			$pending_amount              = $fees['payable'][ $key ] - $value;
			$fees['paid'][ $key ]        = $fees['paid'][ $key ] - $installment['paid'][ $key ] + $amount['paid'][ $key ];
			$installment['paid'][ $key ] = $amount['paid'][ $key ];
			if ( $fees['payable'][ $key ] < $fees['paid'][ $key ] ) {
				wp_send_json_error( esc_html__( "Total amount exceeded payable amount for " . $fees['type'][ $key ] . ".", WL_MIM_DOMAIN ) );
			}
			$fees['paid'][ $key ]        = number_format( max( floatval( $fees['paid'][ $key ] ), 0 ), 2, '.', '' );
			$installment['paid'][ $key ] = number_format( max( floatval( $installment['paid'][ $key ] ), 0 ), 2, '.', '' );
		}

		if ( array_sum( $installment['paid'] ) <= 0 ) {
			wp_send_json_error( esc_html__( 'Invalid installment.', WL_MIM_DOMAIN ) );
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$installment = serialize( $installment );
				$fees        = serialize( $fees );

				$data = array(
					'fees'       => $installment,
					'created_at' => $created_at,
					'updated_at' => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_installments", $data, array(
					'is_deleted'   => 0,
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$data = array(
					'fees'       => $fees,
					'updated_at' => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_students", $data, array(
					'id'           => $student->id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Installment updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete installment */
	public static function delete_installment() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-installment-$id"], "delete-installment-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$installment = $wpdb->get_row( "SELECT fees, student_id FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );

			if ( ! $installment ) {
				throw new Exception( esc_html__( 'Installment not found.', WL_MIM_DOMAIN ) );
			}

			$student = $wpdb->get_row( "SELECT id, fees FROM {$wpdb->prefix}wl_min_students WHERE id = $installment->student_id AND institute_id = $institute_id" );

			if ( ! $student ) {
				throw new Exception( esc_html__( 'Student not found for this installment.', WL_MIM_DOMAIN ) );
			}

			$fees        = unserialize( $student->fees );
			$installment = unserialize( $installment->fees );

			foreach ( $fees['paid'] as $key => $value ) {
				$fees['paid'][ $key ] -= $installment['paid'][ $key ];
				$fees['paid'][ $key ] = number_format( max( floatval( $fees['paid'][ $key ] ), 0 ), 2, '.', '' );
			}

			$fees = serialize( $fees );

			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->update( "{$wpdb->prefix}wl_min_installments",
				array(
					'is_deleted' => 1,
					'deleted_at' => date( 'Y-m-d H:i:s' )
				), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
			);
			if ( $success === false ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$data = array(
				'fees'       => $fees,
				'updated_at' => date( 'Y-m-d H:i:s' )
			);

			$success = $wpdb->update( "{$wpdb->prefix}wl_min_students", $data, array(
				'id'           => $student->id,
				'institute_id' => $institute_id
			) );
			if ( $success === false ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Installment removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* Fetch Fee */
	public static function fetch_fees() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id      = intval( sanitize_text_field( $_POST['id'] ) );
		$student = $wpdb->get_row( "SELECT id, fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}
		$course = $wpdb->get_row( "SELECT course_code, course_name, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $student->course_id AND institute_id = $institute_id" );
		if ( ! $course ) {
			die();
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count( $course->duration, $course->duration_in );

		$invoices = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_invoices WHERE student_id = {$student->id} AND status = 'pending' AND institute_id = $institute_id ORDER BY id DESC" );

		$fees         = unserialize( $student->fees );
		$pending_fees = number_format( WL_MIM_Helper::get_fees_total( $fees['payable'] ) - WL_MIM_Helper::get_fees_total( $fees['paid'] ), 2, '.', '' );

		ob_start();
		$is_invoice_available = false;
		if ( $invoices && count( $invoices ) ) {
			$is_invoice_available = true;
			?>
			<div class="form-group pt-3">
	            <label for="wlim-invoice-id" class="col-form-label"><?php esc_html_e( "From Invoice", WL_MIM_DOMAIN ); ?>:</label>
	            <select name="invoice" class="form-control selectpicker" id="wlim-invoice-id" data-student_id="<?php echo esc_attr( $student->id ); ?>">
	                <option value="">-------- <?php esc_html_e( "Select Invoice", WL_MIM_DOMAIN ); ?> --------</option>
	            <?php
	    			foreach ( $invoices as $invoice ) {
	    				$invoice_number = WL_MIM_Helper::get_invoice( $invoice->id );
	    				$invoice_title  = $invoice->invoice_title; ?>
	                <option value="<?php echo esc_attr( $invoice->id ); ?>"><?php echo esc_html( $invoice_title . " ( " . $invoice_number . " )" ); ?></option>
					<?php
    				} ?>
	            </select>
	        </div>
		<?php
		} ?>
        <ul class="list-group border-bottom mt-4 mb-3">
            <li class="list-group-item"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?>:
            	<strong><?php echo esc_html( "$course->course_name ($course->course_code)" ); ?></strong>
            </li>
            <li class="list-group-item"><?php esc_html_e( 'Total Fees Payable', WL_MIM_DOMAIN ); ?>:
            	<strong><?php echo WL_MIM_Helper::get_fees_total( $fees['payable'] ); ?></strong>
            </li>
            <li class="list-group-item"><?php esc_html_e( 'Total Fees Paid', WL_MIM_DOMAIN ); ?>:
            	<strong class="text-primary"><?php echo WL_MIM_Helper::get_fees_total( $fees['paid'] ); ?></strong>
            </li>
        </ul>
        <div class="fee_types_box">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Fee Type', WL_MIM_DOMAIN ); ?></th>
                    <th><?php esc_html_e( 'Fee Period', WL_MIM_DOMAIN ); ?></th>
                    <th><?php esc_html_e( 'Amount Pending', WL_MIM_DOMAIN ); ?></th>
                    <th><?php esc_html_e( 'New Installment', WL_MIM_DOMAIN ); ?></th>
                </tr>
                </thead>
                <tbody class="fee_types_rows fee_types_table">
				<?php
				foreach ( $fees['paid'] as $key => $amount ) {
					unset($monthly_amount_payable);
					if ( isset( $fees['period'] ) && $fees['period'][ $key ] == 'monthly' ) {
						$monthly_amount_payable = number_format( $fees['payable'][ $key ] / $duration_in_month, 2, '.', '' );
					}
					$pending_amount = $fees['payable'][ $key ] - $amount;
					if ( $pending_amount > 0 ) { ?>
                        <tr>
                            <td><span class="text-dark"><?php echo esc_html( $fees['type'][ $key ] ); ?></span></td>
	                        <td>
	                            <span class="text-dark"><?php echo esc_html( isset( $fees['period'] ) ? WL_MIM_Helper::get_period_in()[$fees['period'][ $key ]] : WL_MIM_Helper::get_period_in()['one-time'] ); ?><br>
	                            	<?php
	                            	if ( isset( $monthly_amount_payable ) ) {
	                            		echo esc_html( $monthly_amount_payable );
	                            	} ?>
	                            </span>
	                        </td>
                            <td><span class="text-dark"><?php echo number_format( $pending_amount, 2, '.', '' ); ?></span></td>
                            <td><input type="number" name="amount[paid][]" class="form-control wlima-invoice-amount-payable wlima-amount-payable" placeholder="<?php esc_html_e( 'Installment', WL_MIM_DOMAIN ); ?>" value="0.00"></td>
                        </tr>
					<?php
					}
				} ?>
                </tbody>
                <tfoot>
                <tr>
                    <th><span><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></span></th>
                    <th>-</th>
                    <th><span><?php echo esc_html( $pending_fees ); ?></span></th>
                    <th><span class="ml-2 wlima-amount-payable-total">0.00</span></th>
                </tr>
                </tfoot>
            </table>
        </div>
        <div class="form-group">
            <label for="wlim-installment-created_at" class="col-form-label">* <strong><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?>:</strong></label>
            <input name="created_at" type="text" class="form-control wlim-created_at" id="wlim-installment-created_at" placeholder="<?php esc_html_e( "Date", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( date('d-m-Y') ); ?>">
        </div>
        <ul class="list-group border-top mt-4">
            <li class="list-group-item"><?php esc_html_e( 'Pending Fees', WL_MIM_DOMAIN ); ?>:
            	<strong class="text-danger"><?php echo esc_html( $pending_fees ); ?></strong>
            </li>
        </ul>
		<?php
		$html = ob_get_clean();

		$wlim_created_at_selector = '.wlim-created_at';

		$json = json_encode( array(
			'created_at_exist'         => boolval( $row->created_at ),
			'wlim_created_at_selector' => esc_attr( $wlim_created_at_selector ),
			'created_at'               => esc_attr( $row->created_at ),
			'is_invoice_available'     => boolval( esc_attr( $is_invoice_available ) )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Fetch invoice amount */
	public static function fetch_invoice_amount() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id         = intval( sanitize_text_field( $_POST['id'] ) );
		$student_id = intval( sanitize_text_field( $_POST['student_id'] ) );
		$student    = $wpdb->get_row( "SELECT fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $student_id AND institute_id = $institute_id" );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_invoices WHERE id = $id AND status = 'pending' AND student_id = $student_id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		$fees    = unserialize( $student->fees );
		$invoice = unserialize( $row->fees );

		foreach( $fees['paid'] as $key => $amount ) {
			$pending_amount = $fees['payable'][$key] - $amount;
			if ( ! ( $pending_amount > 0 ) ) {
				array_splice( $invoice['paid'], $key, 1 );
			}
		}

		wp_send_json( $invoice['paid'] );
	}

	/* View and print installment fee receipt */
	public static function print_installment_fee_receipt() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE id = $row->student_id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}
		$fees         = unserialize( $student->fees );
		$installments = unserialize( $row->fees );
		?>
        <div class="row">
            <div class="col">
                <div class="mb-3 mt-2">
                    <div class="text-center">
                        <button type="button" id="wl-installment-fee-receipt-print" class="btn btn-sm btn-success">
                            <i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Installment Fee Receipt', WL_MIM_DOMAIN ); ?>
                        </button>
                        <hr>
                    </div>
                    <div>
						<?php require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_fee_receipt.php' ); ?>
                    </div>
                </div>
            </div>
        </div>
		<?php
		die();
	}

	/* Get fee type data to display on table */
	public static function get_fee_type_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_fee_types WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id        = $row->id;
				$fee_type  = $row->fee_type;
				$amount    = $row->amount;
				$period    = $row->period ? WL_MIM_Helper::get_period_in()[$row->period] : WL_MIM_Helper::get_period_in()['one-time'];
				$is_active = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$date      = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
				$added_by  = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$results["data"][] = array(
					
					esc_html( $fee_type ),
					esc_html( $amount ),
					esc_html( $period ),
					esc_html( $is_active ),
					esc_html( $date ),
					esc_html( $added_by ),
					'<a class="mr-3" href="#update-fee-type" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-fee-type-security="' . wp_create_nonce( "delete-fee-type-$id" ) . '"delete-fee-type-id="' . $id . '" class="delete-fee-type"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new fee type */
	public static function add_fee_type() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-fee-type'], 'add-fee-type' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$fee_type  = isset( $_POST['fee_type'] ) ? sanitize_text_field( $_POST['fee_type'] ) : NULL;
		$amount    = number_format( isset( $_POST['amount'] ) ? max( floatval( sanitize_text_field( $_POST['amount'] ) ), 0 ) : 0, 2, '.', '' );
		$period    = isset( $_POST['period'] ) ? sanitize_text_field( $_POST['period'] ) : WL_MIM_Helper::get_period_in()['one-time'];
		$is_active = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $fee_type ) ) {
			$errors['fee_type'] = esc_html__( 'Please specify fee type.', WL_MIM_DOMAIN );
		}

		if ( strlen( $fee_type ) > 191 ) {
			$errors['fee_type'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		if ( ! in_array( $period, array_keys( WL_MIM_Helper::get_period_in() ) ) ) {
			$errors['period'] = esc_html__( 'Please select valid period.', WL_MIM_DOMAIN );
		}

		if ( $amount < 0 ) {
			$errors['amount'] = esc_html__( 'Amount must be zero or positive.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_fee_types WHERE is_deleted = 0 AND fee_type = '$fee_type' AND institute_id = $institute_id" );

		if ( $count ) {
			$errors['fee_type'] = esc_html__( 'Fee type already exists.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'fee_type'     => $fee_type,
					'amount'       => $amount,
					'periods'       => $period,
					'is_active'    => $is_active,
					'added_by'     => get_current_user_id(),
					'institute_id' => $institute_id
				);
				// var_dump($data);
				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_fee_types", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Fee type added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch fee type to update */
	public static function fetch_fee_type() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_fee_types WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		?>
        <form id="wlim-update-fee-type-form">
			<?php $nonce = wp_create_nonce( "update-fee-type-$id" ); ?>
            <input type="hidden" name="update-fee-type-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
            <div class="form-group">
                <label for="wlim-fee-type-fee_type_update" class="col-form-label"><?php esc_html_e( 'Fee Type', WL_MIM_DOMAIN ); ?>:</label>
                <input name="fee_type" type="text" class="form-control" id="wlim-fee-type-fee_type_update" placeholder="<?php esc_html_e( "Fee Type", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->fee_type ); ?>">
            </div>
            <div class="form-group">
                <label for="wlim-fee-type-amount_update" class="col-form-label"><?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?> :</label>
                <input name="amount" type="number" class="form-control" id="wlim-fee-type-amount_update" placeholder="<?php esc_html_e( "Amount", WL_MIM_DOMAIN ); ?>" min="0" step="any" value="<?php echo esc_attr( $row->amount ); ?>">
            </div>
            <div class="form-group">
                <label for="wlim-fee-type-period_update"
                       class="pt-2"><?php esc_html_e( 'Period', WL_MIM_DOMAIN ); ?>:</label>
                <select name="period" class="form-control" id="wlim-fee-type-period_update">
					<?php
					foreach ( WL_MIM_Helper::get_period_in() as $key => $value ) { ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, esc_attr( $row->period ), true ); ?>><?php echo esc_html( $value ); ?></option>
						<?php
					} ?>
                </select>
            </div>
            <div class="form-check pl-0">
                <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-fee-type-is_active_update" <?php echo boolval( $row->is_active ) ? "checked" : ""; ?>>
                <label class="form-check-label" for="wlim-fee-type-is_active_update">
					<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                </label>
            </div>
            <input type="hidden" name="fee_type_id" value="<?php echo esc_attr( $row->id ); ?>">
        </form>
		<?php
		die();
	}

	/* Update fee type */
	public static function update_fee_type() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['fee_type_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-fee-type-$id"], "update-fee-type-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$fee_type  = isset( $_POST['fee_type'] ) ? sanitize_text_field( $_POST['fee_type'] ) : null;
		$amount    = number_format( isset( $_POST['amount'] ) ? max( floatval( sanitize_text_field( $_POST['amount'] ) ), 0 ) : 0, 2, '.', '' );
		$period    = isset( $_POST['period'] ) ? sanitize_text_field( $_POST['period'] ) : WL_MIM_Helper::get_period_in()['one-time'];
		$is_active = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $fee_type ) ) {
			$errors['fee_type'] = esc_html__( 'Please specify fee type.', WL_MIM_DOMAIN );
		}

		if ( strlen( $fee_type ) > 191 ) {
			$errors['fee_type'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		if ( ! in_array( $period, array_keys( WL_MIM_Helper::get_period_in() ) ) ) {
			$errors['period'] = esc_html__( 'Please select valid period.', WL_MIM_DOMAIN );
		}

		if ( $amount < 0 ) {
			$errors['amount'] = esc_html__( 'Amount must be zero or positive.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_fee_types WHERE is_deleted = 0 AND id != $id AND fee_type = '$fee_type' AND institute_id = $institute_id" );

		if ( $count ) {
			$errors['fee_type'] = esc_html__( 'Fee type already exists.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'fee_type'   => $fee_type,
					'amount'     => $amount,
					'periods'    => $period,
					'is_active'  => $is_active,
					'updated_at' => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_fee_types", $data, array(
					'is_deleted'   => 0,
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Fee type updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete fee type */
	public static function delete_fee_type() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-fee-type-$id"], "delete-fee-type-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$fee_type = $wpdb->get_row( "SELECT fee_type, amount FROM {$wpdb->prefix}wl_min_fee_types WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );

			if ( ! $fee_type ) {
				throw new Exception( esc_html__( 'Fee type not found.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->update( "{$wpdb->prefix}wl_min_fee_types",
				array(
					'is_deleted' => 1,
					'deleted_at' => date( 'Y-m-d H:i:s' )
				), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
			);
			if ( $success === false ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Fee type removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	/* Check permission to manage installment */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_fees' ) || ! $institute_id ) {
			die();
		}
	}
}
