<?php

use Nexmo\Numbers\Number;

defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_StudentHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_PaymentHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SMSHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/vendor/autoload.php' );
// require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/lib/instamojo/Instamojo.php';

class WL_MIM_Payment {
	/* Pay fees */
	public static function pay_fees() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['pay-fees'], 'pay-fees' ) ) {
			die();
		}
		$student = WL_MIM_StudentHelper::get_student();
		if ( ! $student ) {
			die();
		}
		$institute_id      = $student->institute_id;
		$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
		$payment           = WL_MIM_SettingHelper::get_payment_settings( $institute_id );
		$payment_paypal    = WL_MIM_SettingHelper::get_payment_paypal_settings( $institute_id );
		$payment_razorpay  = WL_MIM_SettingHelper::get_payment_razorpay_settings( $institute_id );
		$payment_instamojo  = WL_MIM_SettingHelper::get_payment_instamojo_settings( $institute_id );
		$payment_stripe    = WL_MIM_SettingHelper::get_payment_stripe_settings( $institute_id );

		$fees = unserialize( $student->fees );

		$pending_fees   = number_format( WL_MIM_Helper::get_fees_total( $fees['payable'] ) - WL_MIM_Helper::get_fees_total( $fees['paid'] ), 2, '.', '' );
		$amount         = ( isset( $_POST['amount'] ) && is_array( $_POST['amount'] ) ) ? $_POST['amount'] : null;
		$fee_payment    = isset( $_POST['fee_payment'] ) ? sanitize_text_field( $_POST['fee_payment'] ) : '';
		$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';

		/* Validations */
		$errors = array();
		if ( empty( $payment_method ) ) {
			$errors['payment_method'] = esc_html__( 'Please specify payment method.', WL_MIM_DOMAIN );
		}

		if ( $fee_payment == 'total_pending_fee' ) {
			$amount['paid'] = array();
			foreach ( $fees['paid'] as $key => $value ) {
				$pending_amount = $fees['payable'][ $key ] - $value;
				if ( $pending_amount > 0 ) {
					array_push( $amount['paid'], $pending_amount );
				}
			}
		} elseif ( $fee_payment == 'individual_fee' ) {
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
					wp_send_json_error( esc_html__( 'Please provide a valid amount.', WL_MIM_DOMAIN ) );
				} else {
					$amount['paid'][ $key ] = number_format( isset( $value ) ? max( floatval( sanitize_text_field( $value ) ), 0 ) : 0, 2, '.', '' );
				}
			}
		} else {
			wp_send_json_error( esc_html__( 'Please select a valid fee payment.', WL_MIM_DOMAIN ) );
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

		$amount_total = WL_MIM_Helper::get_fees_total( $installment['paid'] );

		$student_id = $student->id;
		$custom     = $student_id;

		if ( count( $errors ) < 1 ) {
			$amount_in_paisa = $amount_total * 100;
			$amount_in_cents = $amount_total * 100;
			$name            = $student->first_name;
			$email           = $student->email;
			$address         = $student->address;
			$city            = $student->city;
			$phone           = $student->phone;
			$zip             = $student->zip;
			$state           = $student->state;

			if ( $student->last_name ) {
				$name .= " $student->last_name";
			}
			if ( $student->city ) {
				$address = "$student->address - $student->city";
			}
			if ( $student->state ) {
				$address = "$address, $student->state";
			}
			if ( $student->zip ) {
				$address = "$address - $student->zip";
			}

			$institute_advanced_logo = esc_url( wp_get_attachment_url( $general_institute['institute_logo'] ) );
			$institute_advanced_name = $general_institute['institute_name'];
			$description             = esc_html__( 'Pending Fees', WL_MIM_DOMAIN );

            if ($payment_method == 'razorpay' && WL_MIM_PaymentHelper::razorpay_enabled_institute($institute_id)) {
                $currency_symbol   = WL_MIM_PaymentHelper::get_currency_symbol_institute($institute_id);
                $pay_with_razorpay = esc_html__('Pay Amount', WL_MIM_DOMAIN) . ' ' . $currency_symbol . $amount_total . ' ' . esc_html__('with Razorpay', WL_MIM_DOMAIN);
                $security          = wp_create_nonce('pay-razorpay');
                $back_button       = esc_html__('Go Back', WL_MIM_DOMAIN);
                $razorpay_key      = $payment_razorpay['key'];
                $currency          = $payment['payment_currency'];

                $html = <<<EOT
<button class='mt-2 float-left btn btn-info' onclick='location.reload()'>$back_button</button>
<button class='mt-2 float-right btn btn-success' id='rzp-button1'>$pay_with_razorpay</button>
EOT;
                $json = json_encode(array(
                    'payment_method'  => esc_attr($payment_method),
                    'razorpay_key'    => esc_attr($razorpay_key),
                    'amount_in_paisa' => esc_attr($amount_in_paisa),
                    'currency'        => esc_attr($currency),
                    'institute_name'  => esc_attr($institute_advanced_name),
                    'institute_logo'  => esc_attr($institute_advanced_logo),
                    'security'        => esc_attr($security),
                    'name'            => esc_attr($name),
                    'email'           => esc_attr($email),
                    'address'         => esc_attr($address),
                    'student_id'      => esc_attr($student_id),
                    'amount_paid'     => json_encode($amount['paid']),
                ));
                wp_send_json_success(array( 'html' => $html, 'json' => $json ));
            }elseif ( $payment_method == 'instamojo' && WL_MIM_PaymentHelper::instamojo_enabled_institute( $institute_id ) ) {
				$currency_symbol    = WL_MIM_PaymentHelper::get_currency_symbol_institute( $institute_id );
				$payment_instamojo  = WL_MIM_SettingHelper::get_payment_instamojo_settings( $institute_id );

				$pay_with_instamojo = esc_html__( 'Pay Amount', WL_MIM_DOMAIN ) . ' ' . $currency_symbol . $amount_total . ' ' . esc_html__( 'with instamojo', WL_MIM_DOMAIN );
				$cancel_url         = $_POST['current_page_url'];
				$return_url         = $_POST['current_page_url'];
				$instamojo_url      = WL_MIM_PaymentHelper::get_instamojo_url_institute( $institute_id );
				$security           = wp_create_nonce( 'pay-instamojo' );
				$back_button        = esc_html__( 'Go Back', WL_MIM_DOMAIN );
				$instamojo_client_id      = $payment_instamojo['client_id'];
				$currency           = $payment['payment_currency'];
				$amount_x_100       = $amount_in_paisa;
				$amount_paid        = json_encode( $amount['paid'] );
	
				$ajax_url_admin = esc_url( admin_url( 'admin-ajax.php' ) );

				$items = array();
				foreach ( $amount['paid'] as $key => $value ) {
					$i = $key + 1;
					array_push( $items, "<input type='hidden' name='item_name_$i' value='" . $installment['type'][ $i - 1 ] . "'><input type='hidden' name='item_number_$i' value='$i'><input type='hidden' name='amount_$i' value='$value'>" );
				}
				$items = implode( '', $items );
				$no_items = count($items);
				$amount_paid = implode( ', ', $amount['paid'] );


				$html = <<<EOT
<form action="$ajax_url_admin" method="post" id="pay-with-instamojo">
    <input type="hidden" name="payment_method" value="$payment_method">
    <input type="hidden" name="action" value="wl-mim-pay-instamojo">
	$items
    <input type="hidden" name="instamojo_client_id" value="$instamojo_client_id">
    <input type="hidden" name="no_items" value="$no_items">
    <input type="hidden" name="instamojo_client_id" value="$instamojo_client_id">
    <input type="hidden" name="amount_x_100" value="$amount_x_100">
    <input type="hidden" name="amount_total" value="$amount_total">
    <input type="hidden" name="security" value="$security">
    <input type='hidden' name='student_id' value='$student_id'>
    <input type="hidden" name="name" value="$name">
    <input type="hidden" name="email" value="$email">
    <input type="hidden" name="phone" value="$phone">
    <input type="hidden" name="address" value="$address">
    <input type="hidden" name="amount_paid" value="$amount_paid">
    <input type="hidden" name="currency_code" value="$currency">
    <input type='hidden' name='cancel_return' value='$cancel_url'>
    <input type='hidden' name='return' value='$return_url'>
    <input type="hidden" name="custom" value="$custom">
	<button class='mt-2 float-left btn btn-info' onclick='location.reload()'>$back_button</button>
	<button id="instamojo-pay-btn" type="submit" class='mt-2 float-right btn btn-success'>$pay_with_instamojo</button>
</form>
EOT;

				$json = json_encode( array() );
				wp_send_json_success(array( 'html' => $html, 'json' => $json ));


			} elseif ( $payment_method == 'paystack' && WL_MIM_PaymentHelper::paystack_enabled_institute( $institute_id ) ) {
				$currency_symbol   = WL_MIM_PaymentHelper::get_currency_symbol_institute( $institute_id );
				$payment_paystack  = WL_MIM_SettingHelper::get_payment_paystack_settings( $institute_id );
				$pay_with_paystack = esc_html__( 'Pay Amount', WL_MIM_DOMAIN ) . ' ' . $currency_symbol . $amount_total . ' ' . esc_html__( 'with paystack', WL_MIM_DOMAIN );
				$security          = wp_create_nonce( 'pay-paystack' );
				$back_button       = esc_html__( 'Go Back', WL_MIM_DOMAIN );
				$paystack_key      = $payment_paystack['key'];
				$currency          = $payment['payment_currency'];
				$amount_x_100      = $amount_in_paisa;

				$html = <<<EOT
<button class='mt-2 float-left btn btn-info' onclick='location.reload()'>$back_button</button>
<button class='mt-2 float-right btn btn-success' id='paystack-btn'>$pay_with_paystack</button>
EOT;
				$json = json_encode( array(
					'payment_method'  => esc_attr( $payment_method ),
					'paystack_key'    => esc_attr( $paystack_key ),
					'amount_x_100'    => esc_attr( $amount_x_100 ),
					'currency'        => esc_attr( $currency ),
					'institute_name'  => esc_attr( $institute_advanced_name ),
					'institute_logo'  => esc_attr( $institute_advanced_logo ),
					'security'        => esc_attr( $security ),
					'name'            => esc_attr( $name ),
					'email'           => esc_attr( $email ),
					'address'         => esc_attr( $address ),
					'student_id'      => esc_attr( $student_id ),
					'amount_paid'     => json_encode( $amount['paid'] ),
				) );
				wp_send_json_success( array( 'html' => $html, 'json' => $json ) );

			} elseif ( $payment_method == 'stripe' && WL_MIM_PaymentHelper::stripe_enabled_institute( $institute_id ) ) {
				$currency_symbol = WL_MIM_PaymentHelper::get_currency_symbol_institute( $institute_id );
				$pay_with_stripe = esc_html__( 'Pay Amount', WL_MIM_DOMAIN ) . ' ' . $currency_symbol . $amount_total . ' ' . esc_html__( 'with Stripe', WL_MIM_DOMAIN );
				$security        = wp_create_nonce( 'pay-stripe' );
				$back_button     = esc_html__( 'Go Back', WL_MIM_DOMAIN );
				$stripe_key      = $payment_stripe['publishable_key'];
				$currency        = $payment['payment_currency'];
				$amount_to_pay   = "<div class='mb-2'><span class='font-weight-bold'>" . esc_html__( 'Amount to Pay', WL_MIM_DOMAIN ) . ":</span>&nbsp;<strong>$currency_symbol$amount_total</strong></div>";
				$html            = <<<EOT
<button class='mt-2 float-left btn btn-info' onclick='location.reload()'>$back_button</button>
<button class='mt-2 float-right btn btn-success' id='stripe-button'>$pay_with_stripe</button>
EOT;
				$json            = json_encode( array(
					'payment_method'  => esc_attr( $payment_method ),
					'stripe_key'      => esc_attr( $stripe_key ),
					'institute_logo'  => esc_attr( $institute_advanced_logo ),
					'security'        => esc_attr( $security ),
					'amount_paid'     => json_encode( $amount['paid'] ),
					'name'            => esc_attr( $institute_advanced_name ),
					'description'     => esc_attr__( 'Pending Fees', WL_MIM_DOMAIN ),
					'currency'        => esc_attr( $currency ),
					'amount_in_cents' => esc_attr( $amount_in_cents )
				) );
				wp_send_json_success( array( 'html' => $html, 'json' => $json ) );

			} elseif ( $payment_method == 'paypal' && WL_MIM_PaymentHelper::paypal_enabled_institute( $institute_id ) ) {
				$currency_symbol = WL_MIM_PaymentHelper::get_currency_symbol_institute( $institute_id );
				$pay_with_paypal = esc_html__( 'Pay Amount', WL_MIM_DOMAIN ) . ' ' . $currency_symbol . $amount_total . ' ' . esc_html__( 'with PayPal', WL_MIM_DOMAIN );
				$back_button     = esc_html__( 'Go Back', WL_MIM_DOMAIN );
				$item_number     = uniqid();
				$notify_url      = WL_MIM_PaymentHelper::get_paypal_notify_url();
				$cancel_url      = menu_page_url( 'multi-institute-management-student-dashboard', false );
				$return_url      = menu_page_url( 'multi-institute-management-student-dashboard', false );
				$paypal_url      = WL_MIM_PaymentHelper::get_paypal_url_institute( $institute_id );
				$business_email  = $payment_paypal['business_email'];
				$currency        = $payment['payment_currency'];

				$items = array();
				foreach ( $amount['paid'] as $key => $value ) {
					$i = $key + 1;
					array_push( $items, "<input type='hidden' name='item_name_$i' value='" . $installment['type'][ $i ] . "'><input type='hidden' name='item_number_$i' value='$i'><input type='hidden' name='amount_$i' value='$value'>" );
				}
				$items = implode( '', $items );

				$data = <<<EOT
<form action="$paypal_url" method="post">
    <input type="hidden" name="business" value="$business_email">
    <input type="hidden" name="cmd" value="_cart">
    <input type="hidden" name="upload" value="1">
    $items
    <input type="hidden" name="currency_code" value="$currency">
    <input type='hidden' name='cancel_return' value='$cancel_url'>
    <input type='hidden' name='return' value='$return_url'>
    <input type="hidden" name="notify_url" value="$notify_url">
    <input type="hidden" name="custom" value="$custom">
	<button class='mt-2 float-left btn btn-info' onclick='location.reload()'>$back_button</button>
	<button type="submit" class='mt-2 float-right btn btn-success'>$pay_with_paypal</button>
</form>
EOT;
				wp_send_json_success( array( 'html' => $data, 'json' => json_encode( array() ) ) );
			} else {
				wp_send_json_error( esc_html__( 'Please select a valid payment method.', WL_MIM_DOMAIN ) );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function process_razorpay() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'pay-razorpay' ) ) {
			die();
		}

		if ( isset( $_POST['payment_id'] ) ) {
			$institute_id     = WL_MIM_Helper::get_current_institute_id();
			$payment_razorpay = WL_MIM_SettingHelper::get_payment_razorpay_settings( $institute_id );

			$payment_id      = $_POST['payment_id'];
			$amount_in_paisa = $_POST['amount'];
			$key             = $payment_razorpay['key'];
			$secret          = $payment_razorpay['secret'];
			$url             = "https://$key:$secret@api.razorpay.com/v1/payments";

			$response = wp_remote_post( "$url/$payment_id/capture", array(
				'method'  => 'POST',
				'headers' => array(),
				'body'    => array( 'amount' => $amount_in_paisa ),
				'cookies' => array()
			) );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				wp_send_json_error( $error_message );
			}

			$data = json_decode( $response['body'] );

			if ( ! $data->captured ) {
				wp_send_json_error( esc_html__( 'Unable to capture the payment.', WL_MIM_DOMAIN ) );
			}

			global $wpdb;
			$amount     = ( $data->amount ) / 100;
			$student_id = $data->notes->student_id;

			$amount_paid = array();
			$i           = 1;
			while ( $data->notes->{"fee_$i"} ) {
				$amount_paid[ $i - 1 ] = $data->notes->{"fee_$i"};
				$i ++;
			}

			if ( $amount != WL_MIM_Helper::get_fees_total( $amount_paid ) ) {
				throw new Exception( esc_html__( 'Invalid payment.', WL_MIM_DOMAIN ) );
			}

			$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $student_id AND institute_id = $institute_id" );
			if ( ! $student ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$fees = unserialize( $student->fees );

			$installment['type'] = $fees['type'];

			$i = 0;
			foreach ( $fees['paid'] as $key => $value ) {
				$pending_amount = $fees['payable'][ $key ] - $value;
				if ( $pending_amount > 0 ) {
					$installment['paid'][ $key ] = $amount_paid[ $i ];
					$fees['paid'][ $key ]        += $amount_paid[ $i ];
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

			/* SMS text */
			$installment_count = 0;
			$fees_data         = '';
			foreach ( $installment['type'] as $inst_key => $inst_type ) {
				if ( $installment['paid'][ $inst_key ] > 0 ) {
					$installment_count ++;
					$fees_data .= $inst_type . ": {$installment['paid'][$inst_key]} ";
				}
			}

			try {
				$wpdb->query( 'BEGIN;' );

				$fees        = serialize( $fees );
				$installment = serialize( $installment );

				$data = array(
					'fees'           => $installment,
					'student_id'     => $student_id,
					'payment_method' => WL_MIM_Helper::get_payment_methods()['razorpay'],
					'payment_id'     => $payment_id,
					'added_by'       => get_current_user_id(),
					'institute_id'   => $institute_id
				);

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
						$sms_message = str_replace( '[FEES]', $fees_data, $sms_message );
						$sms_message = str_replace( '[DATE]', date_format( new DateTime( $data['updated_at'] ), "d-m-Y" ), $sms_message );
						/* Send SMS */
						WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_message, $student->phone );
					}
				}
				wp_send_json_success( array( 'message' => esc_html__( 'Payment made successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		} else {
			die();
		}
	}

	public static function pay_instamojo() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'pay-instamojo' ) ) {
			die();
		}
		extract($_POST);

		$institute_id      = WL_MIM_Helper::get_current_institute_id();
		$currency_symbol   = WL_MIM_PaymentHelper::get_currency_symbol_institute( $institute_id );
		$payment_instamojo = WL_MIM_SettingHelper::get_payment_instamojo_settings( $institute_id );
		$return        = $_POST['return'];
		$cancel_return = $_POST['cancel_return'];
		$amount_total  = $_POST['amount_total'];
		$student_id    = $_POST['student_id'];
		$name          = $_POST['name'];
		$amount_paid   = $_POST['amount_paid'];
		$email         = $_POST['email'];
		$phone         = $_POST['phone'];
		$address       = $_POST['address'];
		$currency_code = $_POST['currency_code'];
		$custom        = $_POST['custom'];

		// $amount_paid = explode(', ', $amount_paid);
		// $return = admin_url( 'admin-ajax.php' ) . '?action=wl-mim-pay-with-instamojo';

		$api = Instamojo\Instamojo::init('app',[
			"client_id" =>  $payment_instamojo['client_id'],
			"client_secret" => $payment_instamojo['client_secret'],
		],($payment_instamojo['mode'] == 'test') ? true : false ); /** true for sandbox enviorment**/


		try {
			$response = $api->createPaymentRequest(array(
				"purpose"      => time().'+FE_'.$student_id.'-'.$amount_paid,
				"amount"       => $amount_total,
				"send_email"   => true,
				'buyer_name'   => $name,
				'phone'        => $phone,
				"email"        => $email,
				"redirect_url" => $return
			));
			wp_send_json_success( $response );
		}
		catch (Exception $e) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
	public static function process_instamojo() {

		$institute_id     = WL_MIM_Helper::get_current_institute_id();
		$currency_symbol    = WL_MIM_PaymentHelper::get_currency_symbol_institute( $institute_id );
		$payment_instamojo  = WL_MIM_SettingHelper::get_payment_instamojo_settings( $institute_id );

		if ( isset( $_REQUEST['payment_request_id'] ) ) {
			extract($_REQUEST);
			
			if( $payment_status === 'Failed') {
				try {
					$api = Instamojo\Instamojo::init('app',[
						"client_id" =>  $payment_instamojo['client_id'],
						"client_secret" => $payment_instamojo['client_secret'],
					],($payment_instamojo['mode'] == 'test') ? true : false ); /** true for sandbox enviorment**/
					$response = $api->getPaymentRequestDetails($payment_request_id);
				}
				catch (Exception $e) {
					print('Error: ' . $e->getMessage());
				}
				session_start();
				$_SESSION['payment'] = array(
					'type' => 'error',
					'amount' => $response['amount'],
					'message' => esc_html__( 'Your Payment Failed Please retry your payment', WL_MIM_DOMAIN ),
				);
			}

			global $wpdb;
			$payment_id = $_REQUEST['payment_id'];
			$pay_row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_installments WHERE payment_id = '$payment_id'" );
			if( !empty($pay_row)) return;
			
			
			$response = null;
			try {
				$api = Instamojo\Instamojo::init('app',[
					"client_id" =>  $payment_instamojo['client_id'],
					"client_secret" => $payment_instamojo['client_secret'],
				],($payment_instamojo['mode'] == 'test') ? true : false ); /** true for sandbox enviorment**/
				$response = $api->getPaymentRequestDetails($payment_request_id);
			}
			catch (Exception $e) {
				print('Error: ' . $e->getMessage());
			}
			if ( $response['status'] !== 'Completed' ) {
				session_start();
				$_SESSION['payment'] = array(
					'type' => 'error',
					'amount' => $response['amount'],
					'message' => esc_html__( 'Unable to capture the payment Transcation are failed', WL_MIM_DOMAIN ),
				);
				return;

				wp_send_json_error( esc_html__( 'Unable to capture the payment.', WL_MIM_DOMAIN ) );
			}

			$purpose_arr = explode('-', $response['purpose']);
			$amount_paid = explode(', ', $purpose_arr[1]);
			$student_id = explode('_', $purpose_arr[0])[1];
			// print_r($amount_paid);

			if ( $response['amount'] != WL_MIM_Helper::get_fees_total( $amount_paid ) ) {
				throw new Exception( esc_html__( 'Invalid payment.', WL_MIM_DOMAIN ) );
			}
			$data = $response;

			$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $student_id AND institute_id = $institute_id" );
			if ( ! $student ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$fees = unserialize( $student->fees );
			$installment['type'] = $fees['type'];

			$i = 0;
			foreach ( $fees['paid'] as $key => $value ) {
				$pending_amount = $fees['payable'][ $key ] - $value;
				if ( $pending_amount > 0 ) {
					$installment['paid'][ $key ] = $amount_paid[ $i ];
					$fees['paid'][ $key ]        += $amount_paid[ $i ];
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
			/* fees_data */
			$installment_count = 0;
			$fees_data         = '';
			foreach ( $installment['type'] as $inst_key => $inst_type ) {
				if ( $installment['paid'][ $inst_key ] > 0 ) {
					$installment_count ++;
					$fees_data .= $inst_type . ": {$installment['paid'][$inst_key]} ";
				}
			}

			try {
				$wpdb->query( 'BEGIN;' );

				$fees        = serialize( $fees );
				$installment = serialize( $installment );

				$data = array(
					'fees'           => $installment,
					'student_id'     => $student_id,
					'payment_method' => WL_MIM_Helper::get_payment_methods()['instamojo'],
					'payment_id'     => $payment_id,
					'added_by'       => get_current_user_id(),
					'institute_id'   => $institute_id
				);

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
						$sms_message = str_replace( '[FEES]', $fees_data, $sms_message );
						$sms_message = str_replace( '[DATE]', date_format( new DateTime( $data['updated_at'] ), "d-m-Y" ), $sms_message );
						/* Send SMS */
						WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_message, $student->phone );
					}
				}
				session_start();
				$_SESSION['payment'] = array(
					'payment_id' => $payment_id,
					'type' => 'success',
					'amount' => $response['amount'],
					'message' => esc_html__( 'Payment made successfully.', WL_MIM_DOMAIN ),
				);

			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				$_SESSION['payment'] = array(
					'type' => 'error',
					'message' => esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ),
				);
			}
		} else {
			return;
		}
		
	}

	public static function process_paystack() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'pay-paystack' ) ) {
			die();
		}

		if ( isset( $_POST['reference'] ) ) {
			$institute_id     = WL_MIM_Helper::get_current_institute_id();
			$payment_paystack = WL_MIM_SettingHelper::get_payment_paystack_settings( $institute_id );

			$payment_id      = $_POST['reference'];
			$amount_in_paisa = $_POST['amount'];
			$key             = $payment_paystack['key'];
			$secret          = $payment_paystack['secret'];
			$url             = "https://api.paystack.co/transaction/verify/$payment_id";

			$response = wp_remote_get(
				$url,
				array(
					'headers' => array( 'Authorization' => 'Bearer ' . $secret )
				)
			);

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				wp_send_json_error( $error_message );
			}

			$data = json_decode( $response['body'] );

			if ( ! $data->status || ( 'success' !== $data->data->status ) ) {
				wp_send_json_error( esc_html__( 'Unable to verify the transaction.', WL_MIM_DOMAIN ) );
			}

			global $wpdb;
			$amount     = ( $data->data->amount ) / 100;
			$student_id = absint( $_POST['student_id'] );

			$amount_paid = array();
			$i           = 1;
			while ( isset( $_POST["fee_$i"] ) ) {
				$amount_paid[ $i - 1 ] = $_POST["fee_$i"];
				$i++;
			}

			if ( $amount != WL_MIM_Helper::get_fees_total( $amount_paid ) ) {
				throw new Exception( esc_html__( 'Invalid payment.', WL_MIM_DOMAIN ) );
			}

			$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $student_id AND institute_id = $institute_id" );
			if ( ! $student ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$fees = unserialize( $student->fees );

			$installment['type'] = $fees['type'];

			$i = 0;
			foreach ( $fees['paid'] as $key => $value ) {
				$pending_amount = $fees['payable'][ $key ] - $value;
				if ( $pending_amount > 0 ) {
					$installment['paid'][ $key ] = $amount_paid[ $i ];
					$fees['paid'][ $key ]        += $amount_paid[ $i ];
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

			/* SMS text */
			$installment_count = 0;
			$fees_data         = '';
			foreach ( $installment['type'] as $inst_key => $inst_type ) {
				if ( $installment['paid'][ $inst_key ] > 0 ) {
					$installment_count ++;
					$fees_data .= $inst_type . ": {$installment['paid'][$inst_key]} ";
				}
			}

			try {
				$wpdb->query( 'BEGIN;' );

				$fees        = serialize( $fees );
				$installment = serialize( $installment );

				$data = array(
					'fees'           => $installment,
					'student_id'     => $student_id,
					'payment_method' => WL_MIM_Helper::get_payment_methods()['paystack'],
					'payment_id'     => $payment_id,
					'added_by'       => get_current_user_id(),
					'institute_id'   => $institute_id
				);

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
						$sms_message = str_replace( '[FEES]', $fees_data, $sms_message );
						$sms_message = str_replace( '[DATE]', date_format( new DateTime( $data['updated_at'] ), "d-m-Y" ), $sms_message );
						/* Send SMS */
						WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_message, $student->phone );
					}
				}
				wp_send_json_success( array( 'message' => esc_html__( 'Payment made successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		} else {
			die();
		}
	}

	public static function process_stripe() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'pay-stripe' ) ) {
			die();
		}

		if ( isset( $_POST['stripeToken'] ) ) {
			$stripe_token   = $_POST['stripeToken'];
			$institute_id   = WL_MIM_Helper::get_current_institute_id();
			$payment_stripe = WL_MIM_SettingHelper::get_payment_stripe_settings( $institute_id );
			$payment        = WL_MIM_SettingHelper::get_payment_settings( $institute_id );
			$currency       = $payment['payment_currency'];
			$description    = esc_html__( 'Pending Fees', WL_MIM_DOMAIN );

			global $wpdb;

			$student = WL_MIM_StudentHelper::get_student();
			if ( ! $student ) {
				die();
			}

			$student_id = $student->id;

			$amount_paid = array();
			$i           = 1;
			while ( $_POST["fee_$i"] ) {
				$amount_paid[ $i - 1 ] = $_POST["fee_$i"];
				$i ++;
			}

			$fees = unserialize( $student->fees );

			$installment['type'] = $fees['type'];

			$i = 0;
			foreach ( $fees['paid'] as $key => $value ) {
				$pending_amount = $fees['payable'][ $key ] - $value;
				if ( $pending_amount > 0 ) {
					$installment['paid'][ $key ] = $amount_paid[ $i ];
					$fees['paid'][ $key ]        += $amount_paid[ $i ];
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

			$amount_total          = WL_MIM_Helper::get_fees_total( $installment['paid'] );
			$amount_total_in_cents = $amount_total * 100;

			$secret_key = $payment_stripe['secret_key'];

			\Stripe\Stripe::setApiKey( $secret_key );
			$charge = \Stripe\Charge::create( array(
				'amount'      => $amount_total_in_cents,
				'currency'    => $currency,
				'description' => $description,
				'source'      => $stripe_token
			) );

			if ( ! ( $charge && $charge->captured && ( $charge->amount == $amount_total_in_cents ) ) ) {
				wp_send_json_error( esc_html__( 'Unable to capture the payment.', WL_MIM_DOMAIN ) );
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

			try {
				$wpdb->query( 'BEGIN;' );

				$fees        = serialize( $fees );
				$installment = serialize( $installment );

				$data = array(
					'fees'           => $installment,
					'student_id'     => $student_id,
					'payment_method' => WL_MIM_Helper::get_payment_methods()['stripe'],
					'payment_id'     => $charge->id,
					'added_by'       => get_current_user_id(),
					'institute_id'   => $institute_id
				);

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
						$sms_message = str_replace( '[FEES]', $fees_data, $sms_message );
						$sms_message = str_replace( '[DATE]', date_format( new DateTime( $data['updated_at'] ), "d-m-Y" ), $sms_message );
						/* Send SMS */
						WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_message, $student->phone );
					}
				}
				wp_send_json_success( array( 'message' => esc_html__( 'Payment made successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		} else {
			die();
		}
	}

	/* Check permission to make payment */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( WL_MIM_Helper::get_student_capability() ) || ! $institute_id ) {
			die();
		}
	}
}
?>