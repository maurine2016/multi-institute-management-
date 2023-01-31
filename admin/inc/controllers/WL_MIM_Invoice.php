<?php
defined( 'ABSPATH' ) or die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

class WL_MIM_Invoice {
	/* Get invoice data to display on table */
	public static function get_invoice_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id              = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$data = $wpdb->get_results( "SELECT i.id, i.fees, i.invoice_title, i.status, i.created_at, i.added_by, s.first_name, s.last_name, s.enrollment_id as student_id FROM {$wpdb->prefix}wl_min_invoices as i, {$wpdb->prefix}wl_min_students as s WHERE i.student_id = s.id AND i.institute_id = $institute_id ORDER BY i.id DESC" );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id             = $row->id;
				$invoice_number = WL_MIM_Helper::get_invoice( $id );
				$fees           = unserialize( $row->fees );
				$invoice_title  = $row->invoice_title;
				$status_text    = ucwords( $row->status );
				$status         = ( $row->status == 'paid' ) ? "<strong class='text-success'>$status_text</strong>" : "<strong class='text-danger'>$status_text</strong>";
				$amount         = WL_MIM_Helper::get_fees_total( $fees['paid'] );
				$date           = date_format( date_create( $row->created_at ), "d-m-Y" );
				$added_by       = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$student_name = $row->first_name;
				if ( $row->last_name ) {
					$student_name .= " $row->last_name";
				}
				if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
					$student_id = $student->enrollment_id;
				} else {
					$student_id = $student->id;
				}
				$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );

				$results["data"][] = array(
					esc_html( $invoice_number ) . '<a class="ml-2" href="#print-invoice-fee-invoice" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . esc_html( $id ) . '"><i class="fa fa-print"></i></a>',
					esc_html( $invoice_title ),
					esc_html( $amount ),
					esc_html( $enrollment_id ),
					esc_html( $student_name ),
					wp_kses( $status, array( 'strong' => array( 'class' => 'text-danger', 'text-success' ) ) ),
					esc_html( $added_by ),
					esc_html( $date ),
					$row->status != 'paid' ? '<a class="mr-3" href="#update-invoice" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . esc_html( $id ) . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-invoice-security="' . wp_create_nonce( "delete-invoice-$id" ) . '"delete-invoice-id="' . esc_html( $id ) . '" class="delete-invoice"> <i class="fa fa-trash text-danger"></i></a>' : '' . '<a href="javascript:void(0)" delete-invoice-security="' . wp_create_nonce( "delete-invoice-$id" ) . '"delete-invoice-id="' . esc_html( $id ) . '" class="delete-invoice"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
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
		$student = $wpdb->get_row( "SELECT fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE institute_id = $institute_id AND is_deleted = 0 AND is_active = 1 AND id = $id" );
		if ( ! $student ) {
			die();
		}

		$course = $wpdb->get_row( "SELECT course_code, course_name, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $student->course_id AND institute_id = $institute_id" );
		if ( ! $course ) {
			die();
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count( $course->duration, $course->duration_in );

		$fees         = unserialize( $student->fees );
		$pending_fees = number_format( WL_MIM_Helper::get_fees_total( $fees['payable'] ) - WL_MIM_Helper::get_fees_total( $fees['paid'] ), 2, '.', '' );

		ob_start();
		?>
		<ul class="list-group border-bottom mt-4 mb-3">
			<li class="list-group-item"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?>: <strong><?php echo esc_html( "$course->course_name ($course->course_code)" ); ?></strong></li>
			<li class="list-group-item"><?php esc_html_e( 'Total Fees Payable', WL_MIM_DOMAIN ); ?>: <strong><?php echo esc_html( WL_MIM_Helper::get_fees_total( $fees['payable'] ) ); ?></strong></li>
			<li class="list-group-item"><?php esc_html_e( 'Total Fees Paid', WL_MIM_DOMAIN ); ?>: <strong class="text-primary"><?php echo esc_html( WL_MIM_Helper::get_fees_total( $fees['paid'] ) ); ?></strong></li>
		</ul>
		<div class="form-group">
			<label for="wlim-invoice-title" class="col-form-label"><?php esc_html_e( 'Invoice Title', WL_MIM_DOMAIN ); ?>:</label>
			<input name="invoice_title" type="text" class="form-control" id="wlim-invoice-title"  value="<?php echo date_i18n( "F Y" ); ?>"  placeholder="<?php esc_attr_e( "Invoice Title", WL_MIM_DOMAIN ); ?>">
		</div>
        <div class="fee_types_box">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Fee Type', WL_MIM_DOMAIN ); ?></th>
                    	<th><?php esc_html_e( 'Fee Period', WL_MIM_DOMAIN ); ?></th>
                        <th><?php esc_html_e( 'Amount Pending', WL_MIM_DOMAIN ); ?></th>
                        <th><?php esc_html_e( 'Amount Payable', WL_MIM_DOMAIN ); ?></th>
                    </tr>
                </thead>
                <tbody class="fee_types_rows fee_types_table">
            	<?php
            	$total_payable_amount = 0;
            	foreach( $fees['paid'] as $key => $amount ) {
            		unset( $monthly_amount_payable );
					if ( isset( $fees['period'] ) && $fees['period'][ $key ] == 'monthly' ) {
						$monthly_amount_payable = number_format( $fees['payable'][ $key ] / $duration_in_month, 2, '.', '' );
					}
            		$pending_amount = $fees['payable'][$key] - $amount;
            		if ( $pending_amount > 0 ) { ?>
	                    <tr>
	                        <td><span class="text-dark"><?php echo esc_html( $fees['type'][$key] ); ?></span></td>
	                        <td>
	                            <span class="text-dark"><?php echo esc_html( isset( $fees['period'] ) ? WL_MIM_Helper::get_period_in()[$fees['period'][ $key ]] : WL_MIM_Helper::get_period_in()['one-time'] ); ?><br>
	                            	<?php
	                            	if ( isset( $monthly_amount_payable ) ) {
	                            		$total_payable_amount += $monthly_amount_payable;
	                            		echo esc_html( $monthly_amount_payable );
	                            	} else {
	                            		$total_payable_amount += $pending_amount;
	                            	} ?>
	                            </span>
	                        </td>
                            <td><span class="text-dark"><?php echo number_format( $pending_amount, 2, '.', '' ); ?></span></td>
	                        <td>
                        		<input type="number" name="amount[paid][]" class="form-control wlima-amount-payable" placeholder="<?php esc_attr_e( 'Amount Payable', WL_MIM_DOMAIN ); ?>" value="<?php echo isset( $monthly_amount_payable ) ? $monthly_amount_payable : esc_attr( $pending_amount ); ?>">
	                        </td>
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
                		<th><div class="ml-2 wlima-amount-payable-total"><?php echo esc_html( number_format( $total_payable_amount, 2, '.', '' ) ); ?></div></th>
                	</tr>
                </tfoot>
            </table>
        </div>
        <div class="form-group">
            <label for="wlim-invoice-created_at" class="col-form-label">* <strong><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?>:</strong></label>
            <input name="created_at" type="text" class="form-control wlim-created_at" id="wlim-invoice-created_at" placeholder="<?php esc_html_e( "Date", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( date('d-m-Y') ); ?>">
        </div>
		<?php
		$html = ob_get_clean();

		$wlim_created_at_selector = '.wlim-created_at';

		$json = json_encode( array(
			'created_at_exist'         => boolval( $row->created_at ),
			'wlim_created_at_selector' => esc_attr( $wlim_created_at_selector ),
			'created_at'               => esc_attr( $row->created_at ),
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Add new invoice */
	public static function add_invoice() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-invoice'], 'add-invoice' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$amount        = ( isset( $_POST['amount'] ) && is_array( $_POST['amount'] ) ) ? $_POST['amount'] : NULL;
		$student_id    = isset( $_POST['student'] ) ? intval( sanitize_text_field( $_POST['student'] ) ) : NULL;
		$invoice_title = isset( $_POST['invoice_title'] ) ? sanitize_text_field( $_POST['invoice_title'] ) : '';
		$created_at    = ( isset( $_POST['created_at'] ) && ! empty( $_POST['created_at'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['created_at'] ) ) ) : NULL;

		$errors = array();
		if ( empty( $invoice_title ) ) {
			$errors['invoice_title'] = esc_html__( 'Please provide a unique invoice title.', WL_MIM_DOMAIN );
		}

		if ( strlen( $invoice_title ) > 191 ) {
			$errors['invoice_title'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_invoices WHERE invoice_title = '$invoice_title' AND student_id = $student_id AND institute_id = $institute_id" );

		if ( $count ) {
			$errors['invoice_title'] = esc_html__( 'Invoice title already exists.', WL_MIM_DOMAIN );
		}

		if ( empty( $amount ) ) {
			wp_send_json_error( esc_html__( 'Please provide a valid invoice amount.', WL_MIM_DOMAIN ) );
		}

		if ( ! array_key_exists( 'paid', $amount ) ) {
			wp_send_json_error( esc_html__( 'Invalid invoice.', WL_MIM_DOMAIN ) );
		}

		foreach( $amount['paid'] as $key => $value ) {
			if ( ! is_numeric( $value ) ) {
				$value = 0;
			}
			if ( $value < 0 ) {
				wp_send_json_error( esc_html__( 'Please provide a valid invoice amount.', WL_MIM_DOMAIN ) );
			} else {
				$amount['paid'][$key] = number_format( isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0, 2, '.', '' );
			}
		}

		$student = $wpdb->get_row( "SELECT fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND id = $student_id AND institute_id = $institute_id" );

		if ( ! $student ) {
			$errors['student'] = esc_html__( 'Please select a valid student.', WL_MIM_DOMAIN );
		}

		$course = $wpdb->get_row( "SELECT fees FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $student->course_id AND institute_id = $institute_id" );

		if ( ! $course ) {
			$errors['student'] = esc_html__( 'Student is not enrolled in any course.', WL_MIM_DOMAIN );
		}

		$fees = unserialize( $student->fees );
		$invoice['type'] = $fees['type'];

		$i = 0;
    	foreach( $fees['paid'] as $key => $value ) {
    		$pending_amount = $fees['payable'][$key] - $value;
    		if ( $pending_amount > 0 ) {
    			$invoice['paid'][$key] = $amount['paid'][$i];
    			$fees['paid'][$key] += $amount['paid'][$i];
    			if ( $fees['payable'][$key] < $fees['paid'][$key] ) {
					wp_send_json_error( esc_html__( "Amount exceeded total pending amount for " . $fees['type'][$key] . ".", WL_MIM_DOMAIN ) );
    			}
    			$i++;
				$fees['paid'][$key]    = number_format( max( floatval( $fees['paid'][$key] ), 0 ), 2, '.', '' );
				$invoice['paid'][$key] = number_format( max( floatval( $invoice['paid'][$key] ), 0 ), 2, '.', '' );
    		} else {
    			$invoice['paid'][$key] = number_format( 0, 2, '.', '' );
    		}
    	}

		if ( count( $errors ) < 1 ) {
			try {
			  	$wpdb->query( 'BEGIN;' );

			  	$invoice = serialize( $invoice );
			  	$fees    = serialize( $fees );

				$data = array(
					'invoice_title' => $invoice_title,
					'fees'          => $invoice,
					'student_id'    => $student_id,
				    'created_at'   => $created_at,
				    'added_by'      => get_current_user_id(),
					'institute_id'  => $institute_id
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_invoices", $data );
				if ( ! $success ) {
		  			throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

		  		$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Invoice added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
		  		$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch invoice to update */
	public static function fetch_invoice() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$id   = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_invoices WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		$student = $wpdb->get_row( "SELECT id, course_id, fees, first_name, last_name FROM {$wpdb->prefix}wl_min_students WHERE id = $row->student_id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}
		$course = $wpdb->get_row( "SELECT course_code, course_name, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $student->course_id" );
		if ( ! $course ) {
			die();
		}

		$duration_in_month = WL_MIM_Helper::get_course_months_count( $course->duration, $course->duration_in );

		$fees         = unserialize( $student->fees );
		$pending_fees = number_format( WL_MIM_Helper::get_fees_total( $fees['payable'] ) - WL_MIM_Helper::get_fees_total( $fees['paid'] ), 2, '.', '' );
		$invoice      = unserialize( $row->fees );

		$created_at = date_format( date_create( $row->created_at ), "d-m-Y" );
		?>
		<form id="wlim-update-invoice-form">
			<?php $nonce = wp_create_nonce( "update-invoice-$id" ); ?>
		    <input type="hidden" name="update-invoice-<?php echo $id; ?>" value="<?php echo $nonce; ?>">
			<div class="row" id="wlim-student-enrollment_id">
				<div class="col">
					<label  class="col-form-label pb-0"><?php _e( 'Student', WL_MIM_DOMAIN ); ?>:</label>
					<div class="card mb-3 mt-2">
						<div class="card-block">
						<?php if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
                                            $student_id = $student->enrollment_id;
                                        } else {
                                            $student_id = $student->id;
                                        } ?>
		    				<span class="text-dark"><?php echo $student->first_name . " " . $student->last_name; ?> (<?php echo WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ); ?>)</span>
		  				</div>
					</div>
					<ul class="list-group border-bottom mt-4 mb-3">
						<li class="list-group-item"><?php _e( 'Course', WL_MIM_DOMAIN ); ?>: <strong><?php echo "$course->course_name ($course->course_code)"; ?></strong></li>
					</ul>
					<div class="form-group">
						<label for="wlim-invoice-title_update" class="col-form-label"><?php _e( 'Invoice Title', WL_MIM_DOMAIN ); ?>:</label>
						<input name="invoice_title" type="text" class="form-control" id="wlim-invoice-title_update" placeholder="<?php _e( "Invoice Title", WL_MIM_DOMAIN ); ?>" value="<?php echo $row->invoice_title; ?>">
					</div>
			        <div class="fee_types_box">
			            <table class="table table-bordered">
			                <thead>
			                    <tr>
			                        <th><?php _e( 'Fee Type', WL_MIM_DOMAIN ); ?></th>
			                        <th><?php _e( 'Fee Period', WL_MIM_DOMAIN ); ?></th>
			                        <th><?php _e( 'Amount Pending', WL_MIM_DOMAIN ); ?></th>
			                        <th><?php _e( 'Amount Payable', WL_MIM_DOMAIN ); ?></th>
			                    </tr>
			                </thead>
			                <tbody class="fee_types_rows fee_types_table">
			            	<?php
			            	$total_payable_amount = 0;
			            	foreach( $fees['paid'] as $key => $amount ) {
			            		unset( $monthly_amount_payable );
								if ( isset( $fees['period'] ) && $fees['period'][ $key ] == 'monthly' ) {
									$monthly_amount_payable = number_format( $fees['payable'][ $key ] / $duration_in_month, 2, '.', '' );
								}
			            		$pending_amount = $fees['payable'][$key] - $amount;
			            		if ( $pending_amount > 0 ) { ?>
		                        <tr>
		                            <td>
		                            	<span class="text-dark"><?php echo $fees['type'][$key]; ?></span>
		                            </td>
			                        <td>
			                            <span class="text-dark"><?php echo esc_html( isset( $fees['period'] ) ? WL_MIM_Helper::get_period_in()[$fees['period'][ $key ]] : WL_MIM_Helper::get_period_in()['one-time'] ); ?><br>
			                            	<?php
			                            	if ( isset( $monthly_amount_payable ) ) {
			                            		$total_payable_amount += $monthly_amount_payable;
			                            		echo esc_html( $monthly_amount_payable );
			                            	} else {
			                            		$total_payable_amount += $pending_amount;
			                            	} ?>
			                            </span>
			                        </td>
		                            <td>
		                            	<span class="text-dark"><?php echo number_format( $pending_amount, 2, '.', '' ); ?></span>
		                            </td>
		                            <td>
		                            	<input type="number" name="amount[paid][]" class="form-control" placeholder="<?php _e( 'Paid', WL_MIM_DOMAIN ); ?>" value="<?php echo $invoice['paid'][$key]; ?>">
		                            </td>
		                        </tr>
		                    	<?php
		                		}
	                		} ?>
			                </tbody>
			                <tfoot>
			                	<tr>
			                		<th><span><?php _e( 'Total', WL_MIM_DOMAIN ); ?></span></th>
			                		<th>-</th>
			                		<th><?php echo esc_html( $pending_fees ); ?></th>
                					<th><div class="ml-2"><?php echo WL_MIM_Helper::get_fees_total( $invoice['paid'] ); ?></div></th>
			                	</tr>
			                </tfoot>
			            </table>
			        </div>
					<ul class="list-group border-bottom mt-4 mb-3">
						<li class="list-group-item"><?php _e( 'Total Amount Payable', WL_MIM_DOMAIN ); ?>: <strong><?php echo WL_MIM_Helper::get_fees_total( $invoice['paid'] ); ?></strong></li>
					</ul>
			        <div class="form-group">
			            <label for="wlim-installment-created_at_update" class="col-form-label">* <strong><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?>:</strong></label>
			            <input name="created_at" type="text" class="form-control wlim-created_at_update" id="wlim-installment-created_at_update" placeholder="<?php esc_html_e( "Date", WL_MIM_DOMAIN ); ?>" <?php echo esc_attr( $created_at ); ?>>
			        </div>
				</div>
			</div>
			<input type="hidden" name="invoice_id" value="<?php echo $row->id; ?>">
		</form>
	<?php
		$html = ob_get_clean();

		$wlim_created_at_selector = '.wlim-created_at_update';

		$json = json_encode( array(
			'wlim_date_selector'       => esc_attr( $wlim_date_selector ),
			'wlim_created_at_selector' => esc_attr( $wlim_created_at_selector ),
			'created_at_exist'         => boolval( $row->created_at ),
			'created_at'               => esc_attr( $row->created_at ),
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update invoice */
	public static function update_invoice() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['invoice_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-invoice-$id"], "update-invoice-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$amount        = ( isset( $_POST['amount'] ) && is_array( $_POST['amount'] ) ) ? $_POST['amount'] : NULL;
		$invoice_title = isset( $_POST['invoice_title'] ) ? sanitize_text_field( $_POST['invoice_title'] ) : '';
		$created_at    = ( isset( $_POST['created_at'] ) && ! empty( $_POST['created_at'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['created_at'] ) ) ) : NULL;

		$errors = array();
		$invoice = $wpdb->get_row( "SELECT fees, student_id, status FROM {$wpdb->prefix}wl_min_invoices WHERE id = $id AND status = 'pending' AND institute_id = $institute_id" );

		if ( ! $invoice ) {
			wp_send_json_error( esc_html__( "Invoice not found.", WL_MIM_DOMAIN ) );
		}

		$student = $wpdb->get_row( "SELECT id, fees, course_id FROM {$wpdb->prefix}wl_min_students WHERE id = $invoice->student_id AND institute_id = $institute_id" );

		if ( ! $student ) {
			wp_send_json_error( esc_html__( "Student doesn't exist for this invoice.", WL_MIM_DOMAIN ) );
		}

		if ( empty( $invoice_title ) ) {
			$errors['invoice_title'] = esc_html__( 'Please provide a unique invoice title.', WL_MIM_DOMAIN );
		}

		if ( strlen( $invoice_title ) > 191 ) {
			$errors['invoice_title'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_invoices WHERE invoice_title = '$invoice_title' AND id != $id AND student_id = {$student->id} AND institute_id = $institute_id" );

		if ( $count ) {
			$errors['invoice_title'] = esc_html__( 'Invoice title already exists.', WL_MIM_DOMAIN );
		}

		if ( empty( $amount ) ) {
			wp_send_json_error( esc_html__( 'Please provide a valid invoice.', WL_MIM_DOMAIN ) );
		}

		if ( ! array_key_exists( 'paid', $amount ) ) {
			wp_send_json_error( esc_html__( 'Invalid invoice.', WL_MIM_DOMAIN ) );
		}

		foreach( $amount['paid'] as $key => $value ) {
			if ( $value < 0 || ( ! is_numeric( $value ) ) ) {
				wp_send_json_error( esc_html__( 'Please provide a valid invoice.', WL_MIM_DOMAIN ) );
			} else {
				$amount['paid'][ $key ] = number_format( isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0, 2, '.', '' );
			}
		}

		$fees    = unserialize( $student->fees );
		$invoice = unserialize( $invoice->fees );

		$i = 0;
    	foreach( $fees['paid'] as $key => $value ) {
			$pending_amount = $fees['payable'][ $key ] - $value;
			if ( ! ( $pending_amount > 0 ) ) {
				$invoice['paid'][ $key ] = '0.00';
			} else {
				if ( $pending_amount < $amount['paid'][ $i ] ) {
					wp_send_json_error( esc_html__( "Amount exceeded total pending amount for " . $fees['type'][$key] . ".", WL_MIM_DOMAIN ) );
				}
				$invoice['paid'][ $key ] = $amount['paid'][ $i ];
				$i++;
			}
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

			  	$invoice = serialize( $invoice );

				$data = array(
					'fees'          => $invoice,
					'invoice_title' => $invoice_title,
					'created_at'    => $created_at,
				    'updated_at'    => date('Y-m-d H:i:s')
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_invoices", $data, array( 'id' => $id, 'institute_id' => $institute_id ) );
				if ( $success === false ) {
		  			throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Invoice updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
		  		$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* View and print invoice fee invoice */
	public static function print_invoice_fee_invoice() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_invoices WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE id = $row->student_id AND institute_id = $institute_id" );
		if ( ! $student ) {
			die();
		}
		$fees     = unserialize( $student->fees );
		$invoices = unserialize( $row->fees );
		?>
		<div class="row">
			<div class="col">
				<div class="mb-3 mt-2">
					<div class="text-center">
						<button type="button" id="wl-invoice-fee-invoice-print" class="btn btn-sm btn-success"><i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Fee Invoice', WL_MIM_DOMAIN ); ?></button><hr>
					</div>
					<div>
						<?php require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_fee_invoice.php' ); ?>
	  				</div>
				</div>
			</div>
		</div>
	<?php
		die();
	}

	/* Delete invoice */
	public static function delete_invoice() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-invoice-$id"], "delete-invoice-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$invoice = $wpdb->get_row( "SELECT fees, student_id FROM {$wpdb->prefix}wl_min_invoices WHERE id = $id AND institute_id = $institute_id" );

			if ( ! $invoice ) {
	  			throw new Exception( esc_html__( 'Invoice not found.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( "{$wpdb->prefix}wl_min_invoices", array( 'id' => $id, 'institute_id' => $institute_id ) );
			if ( $success === false ) {
	  			throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Invoice removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
	  		$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	/* Check permission to manage invoice */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_fees' ) || ! $institute_id ) {
			die();
		}
	}
}
