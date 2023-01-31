<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_PaymentHelper.php' );

class WL_MIM_Payment_Front {
	/* Paypal instant payment notification handler */
	public static function paypal_payments() {
		if ( ! ( ! isset( $_POST["txn_id"] ) && ! isset( $_POST["txn_type"] ) ) ) {

			$installment['paid'] = array();
			$i	=	1;
			while ( $_POST["mc_gross_$i"] ) {
				$installment['paid'][ $i - 1 ] = $_POST["mc_gross_$i"];
				$i ++;
			}

			$data = array(
				'payment_status'   => $_POST['payment_status'],
				'payment_currency' => $_POST['mc_currency'],
				'txn_id'           => $_POST['txn_id'],
				'receiver_email'   => $_POST['receiver_email'],
				'payer_email'      => $_POST['payer_email'],
				'student_id'       => $_POST['custom'],
				'amount'           => $installment['paid']
			);
			if ( WL_MIM_PaymentHelper::check_paypal_txnid( $data['txn_id'] ) ) {
            	WL_MIM_PaymentHelper::add_paypal_payment( $data );
            }
		}
	}
}
?>