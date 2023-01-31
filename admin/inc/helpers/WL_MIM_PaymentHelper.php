<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SMSHelper.php' );

class WL_MIM_PaymentHelper {
	public static function check_paypal_txnid( $txnid ) {
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$txnid       = $wpdb->_real_escape( $txnid );
		$installment = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_installments WHERE payment_method = '" . WL_MIM_Helper::get_payment_methods()['paypal'] . "' AND payment_id = $txnid AND institute_id = $institute_id" );

		return ! $installment;
	}

	/* PayPal add transaction to database */
	public static function add_paypal_payment( $data ) {
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$wpdb->query( 'BEGIN;' );

			$payment_status   = $data['payment_status'];
			$payment_currency = $data['payment_currency'];
			$txn_id           = $data['txn_id'];
			$receiver_email   = $data['receiver_email'];
			$payer_email      = $data['payer_email'];
			$student_id       = $data['student_id'];
			$amount           = $data['amount'];

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
					$installment['paid'][ $key ] = $amount[ $i ];
					$fees['paid'][ $key ]        += $amount[ $i ];
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

			$fees        = serialize( $fees );
			$installment = serialize( $installment );

			$data = array(
				'fees'           => $installment,
				'student_id'     => $student_id,
				'payment_method' => WL_MIM_Helper::get_payment_methods()['paypal'],
				'payment_id'     => $txn_id,
				'added_by'       => $student->user_id,
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

			return true;
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );

			return false;
		}
	}

	/* Get paypal url for institute */
	public static function get_paypal_url_institute( $institute_id ) {
		$payment_paypal = WL_MIM_SettingHelper::get_payment_paypal_settings( $institute_id );

		$paypal_mode = $payment_paypal['mode'];
		if ( $paypal_mode == 'live' ) {
			return 'https://www.paypal.com/cgi-bin/webscr';
		} else {
			return 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}
	}

	/* Get instamojo url for institute */
	public static function get_instamojo_url_institute( $institute_id ) {

		$payment_instamojo = WL_MIM_SettingHelper::get_payment_instamojo_settings( $institute_id );

		$instamojo_mode = $payment_instamojo['mode'];
		if ( $instamojo_mode == 'live' ) {
			return 'https://www.instamojo.com/api/1.1/';
		} else {
			return 'https://test.instamojo.com/api/1.1/';
		}
	}

	/* Get paypal notify url */
	public static function get_paypal_notify_url() {
		return admin_url() . 'admin-post.php?action=wl-mim-paypal-payments';
	}

	/* Paypal supported currencies */
	public static function get_paypal_supported_currencies() {
		return array(
			"ARS" => esc_html__( "Argentinian Peso", WL_MIM_DOMAIN ),
			"AUD" => esc_html__( "Australian Dollar", WL_MIM_DOMAIN ),
			"CAD" => esc_html__( "Canadian Dollar", WL_MIM_DOMAIN ),
			"CHF" => esc_html__( "Swiss Franc", WL_MIM_DOMAIN ),
			"CZK" => esc_html__( "Czech Koruna", WL_MIM_DOMAIN ),
			"DKK" => esc_html__( "Danish Krone", WL_MIM_DOMAIN ),
			"EUR" => esc_html__( "Euro", WL_MIM_DOMAIN ),
			"GBP" => esc_html__( "British Pound", WL_MIM_DOMAIN ),
			"HKD" => esc_html__( "Hong Kong Dollar", WL_MIM_DOMAIN ),
			"HUF" => esc_html__( "Hungarian Forint", WL_MIM_DOMAIN ),
			"ILS" => esc_html__( "Israeli New Shekel", WL_MIM_DOMAIN ),
			"JPY" => esc_html__( "Japanese Yen", WL_MIM_DOMAIN ),
			"MXN" => esc_html__( "Mexican Peso", WL_MIM_DOMAIN ),
			"MYR" => esc_html__( "Malaysian Ringgit", WL_MIM_DOMAIN ),
			"NOK" => esc_html__( "Norwegian Krone", WL_MIM_DOMAIN ),
			"NZD" => esc_html__( "New Zealand Dollar", WL_MIM_DOMAIN ),
			"PHP" => esc_html__( "Philippine Peso", WL_MIM_DOMAIN ),
			"PLN" => esc_html__( "Polish Zloty", WL_MIM_DOMAIN ),
			"RUB" => esc_html__( "Russian Ruble", WL_MIM_DOMAIN ),
			"SEK" => esc_html__( "Swedish Krona", WL_MIM_DOMAIN ),
			"SGD" => esc_html__( "Singapore Dollar", WL_MIM_DOMAIN ),
			"THB" => esc_html__( "Thai Baht", WL_MIM_DOMAIN ),
			"TWD" => esc_html__( "Taiwan New Dollar", WL_MIM_DOMAIN ),
			"USD" => esc_html__( "United States Dollar", WL_MIM_DOMAIN )
		);
	}

	/* Instamojo supported currencies */
	public static function get_instamojo_supported_currencies() {
		return array(
			"INR" => esc_html__( "Indian Rupee", WL_MIM_DOMAIN )
		);
	}

	/* Razorpay supported currencies */
	public static function get_razorpay_supported_currencies() {
		return array(
			"INR" => esc_html__( "Indian Rupee", WL_MIM_DOMAIN )
		);
	}

	/* paystack supported currencies */
    public static function get_paystack_supported_currencies() {
        return array(
            "NGN" => esc_html__( "Nigerian Naira", WL_MIM_DOMAIN )
        );
    }

	/* Stripe supported currencies */
	public static function get_stripe_supported_currencies() {
		return array(
			'AFN' => esc_html__( 'Afghan Afghani', WL_MIM_DOMAIN ),
			'ALL' => esc_html__( 'Albanian Lek', WL_MIM_DOMAIN ),
			'DZD' => esc_html__( 'Algerian Dinar', WL_MIM_DOMAIN ),
			'AOA' => esc_html__( 'Angolan Kwanza', WL_MIM_DOMAIN ),
			'ARS' => esc_html__( 'Argentine Peso', WL_MIM_DOMAIN ),
			'AMD' => esc_html__( 'Armenian Dram', WL_MIM_DOMAIN ),
			'AWG' => esc_html__( 'Aruban Florin', WL_MIM_DOMAIN ),
			'AUD' => esc_html__( 'Australian Dollar', WL_MIM_DOMAIN ),
			'AZN' => esc_html__( 'Azerbaijani Manat', WL_MIM_DOMAIN ),
			'BSD' => esc_html__( 'Bahamian Dollar', WL_MIM_DOMAIN ),
			'BDT' => esc_html__( 'Bangladeshi Taka', WL_MIM_DOMAIN ),
			'BBD' => esc_html__( 'Barbadian Dollar', WL_MIM_DOMAIN ),
			'BZD' => esc_html__( 'Belize Dollar', WL_MIM_DOMAIN ),
			'BMD' => esc_html__( 'Bermudian Dollar', WL_MIM_DOMAIN ),
			'BOB' => esc_html__( 'Bolivian Boliviano', WL_MIM_DOMAIN ),
			'BAM' => esc_html__( 'Bosnia & Herzegovina Convertible Mark', WL_MIM_DOMAIN ),
			'BWP' => esc_html__( 'Botswana Pula', WL_MIM_DOMAIN ),
			'BRL' => esc_html__( 'Brazilian Real', WL_MIM_DOMAIN ),
			'GBP' => esc_html__( 'British Pound', WL_MIM_DOMAIN ),
			'BND' => esc_html__( 'Brunei Dollar', WL_MIM_DOMAIN ),
			'BGN' => esc_html__( 'Bulgarian Lev', WL_MIM_DOMAIN ),
			'BIF' => esc_html__( 'Burundian Franc', WL_MIM_DOMAIN ),
			'KHR' => esc_html__( 'Cambodian Riel', WL_MIM_DOMAIN ),
			'CAD' => esc_html__( 'Canadian Dollar', WL_MIM_DOMAIN ),
			'CVE' => esc_html__( 'Cape Verdean Escudo', WL_MIM_DOMAIN ),
			'KYD' => esc_html__( 'Cayman Islands Dollar', WL_MIM_DOMAIN ),
			'XAF' => esc_html__( 'Central African Cfa Franc', WL_MIM_DOMAIN ),
			'XPF' => esc_html__( 'Cfp Franc', WL_MIM_DOMAIN ),
			'CLP' => esc_html__( 'Chilean Peso', WL_MIM_DOMAIN ),
			'CNY' => esc_html__( 'Chinese Renminbi Yuan', WL_MIM_DOMAIN ),
			'COP' => esc_html__( 'Colombian Peso', WL_MIM_DOMAIN ),
			'KMF' => esc_html__( 'Comorian Franc', WL_MIM_DOMAIN ),
			'CDF' => esc_html__( 'Congolese Franc', WL_MIM_DOMAIN ),
			'CRC' => esc_html__( 'Costa Rican Colón', WL_MIM_DOMAIN ),
			'HRK' => esc_html__( 'Croatian Kuna', WL_MIM_DOMAIN ),
			'CZK' => esc_html__( 'Czech Koruna', WL_MIM_DOMAIN ),
			'DKK' => esc_html__( 'Danish Krone', WL_MIM_DOMAIN ),
			'DJF' => esc_html__( 'Djiboutian Franc', WL_MIM_DOMAIN ),
			'DOP' => esc_html__( 'Dominican Peso', WL_MIM_DOMAIN ),
			'XCD' => esc_html__( 'East Caribbean Dollar', WL_MIM_DOMAIN ),
			'EGP' => esc_html__( 'Egyptian Pound', WL_MIM_DOMAIN ),
			'ETB' => esc_html__( 'Ethiopian Birr', WL_MIM_DOMAIN ),
			'EUR' => esc_html__( 'Euro', WL_MIM_DOMAIN ),
			'FKP' => esc_html__( 'Falkland Islands Pound', WL_MIM_DOMAIN ),
			'FJD' => esc_html__( 'Fijian Dollar', WL_MIM_DOMAIN ),
			'GMD' => esc_html__( 'Gambian Dalasi', WL_MIM_DOMAIN ),
			'GEL' => esc_html__( 'Georgian Lari', WL_MIM_DOMAIN ),
			'GIP' => esc_html__( 'Gibraltar Pound', WL_MIM_DOMAIN ),
			'GTQ' => esc_html__( 'Guatemalan Quetzal', WL_MIM_DOMAIN ),
			'GNF' => esc_html__( 'Guinean Franc', WL_MIM_DOMAIN ),
			'GYD' => esc_html__( 'Guyanese Dollar', WL_MIM_DOMAIN ),
			'HTG' => esc_html__( 'Haitian Gourde', WL_MIM_DOMAIN ),
			'HNL' => esc_html__( 'Honduran Lempira', WL_MIM_DOMAIN ),
			'HKD' => esc_html__( 'Hong Kong Dollar', WL_MIM_DOMAIN ),
			'HUF' => esc_html__( 'Hungarian Forint', WL_MIM_DOMAIN ),
			'ISK' => esc_html__( 'Icelandic Króna', WL_MIM_DOMAIN ),
			'INR' => esc_html__( 'Indian Rupee', WL_MIM_DOMAIN ),
			'IDR' => esc_html__( 'Indonesian Rupiah', WL_MIM_DOMAIN ),
			'ILS' => esc_html__( 'Israeli New Sheqel', WL_MIM_DOMAIN ),
			'JMD' => esc_html__( 'Jamaican Dollar', WL_MIM_DOMAIN ),
			'JPY' => esc_html__( 'Japanese Yen', WL_MIM_DOMAIN ),
			'KZT' => esc_html__( 'Kazakhstani Tenge', WL_MIM_DOMAIN ),
			'KES' => esc_html__( 'Kenyan Shilling', WL_MIM_DOMAIN ),
			'KGS' => esc_html__( 'Kyrgyzstani Som', WL_MIM_DOMAIN ),
			'LAK' => esc_html__( 'Lao Kip', WL_MIM_DOMAIN ),
			'LBP' => esc_html__( 'Lebanese Pound', WL_MIM_DOMAIN ),
			'LSL' => esc_html__( 'Lesotho Loti', WL_MIM_DOMAIN ),
			'LRD' => esc_html__( 'Liberian Dollar', WL_MIM_DOMAIN ),
			'MOP' => esc_html__( 'Macanese Pataca', WL_MIM_DOMAIN ),
			'MKD' => esc_html__( 'Macedonian Denar', WL_MIM_DOMAIN ),
			'MGA' => esc_html__( 'Malagasy Ariary', WL_MIM_DOMAIN ),
			'MWK' => esc_html__( 'Malawian Kwacha', WL_MIM_DOMAIN ),
			'MYR' => esc_html__( 'Malaysian Ringgit', WL_MIM_DOMAIN ),
			'MVR' => esc_html__( 'Maldivian Rufiyaa', WL_MIM_DOMAIN ),
			'MRO' => esc_html__( 'Mauritanian Ouguiya', WL_MIM_DOMAIN ),
			'MUR' => esc_html__( 'Mauritian Rupee', WL_MIM_DOMAIN ),
			'MXN' => esc_html__( 'Mexican Peso', WL_MIM_DOMAIN ),
			'MDL' => esc_html__( 'Moldovan Leu', WL_MIM_DOMAIN ),
			'MNT' => esc_html__( 'Mongolian Tögrög', WL_MIM_DOMAIN ),
			'MAD' => esc_html__( 'Moroccan Dirham', WL_MIM_DOMAIN ),
			'MZN' => esc_html__( 'Mozambican Metical', WL_MIM_DOMAIN ),
			'MMK' => esc_html__( 'Myanmar Kyat', WL_MIM_DOMAIN ),
			'NAD' => esc_html__( 'Namibian Dollar', WL_MIM_DOMAIN ),
			'NPR' => esc_html__( 'Nepalese Rupee', WL_MIM_DOMAIN ),
			'ANG' => esc_html__( 'Netherlands Antillean Gulden', WL_MIM_DOMAIN ),
			'TWD' => esc_html__( 'New Taiwan Dollar', WL_MIM_DOMAIN ),
			'NZD' => esc_html__( 'New Zealand Dollar', WL_MIM_DOMAIN ),
			'NIO' => esc_html__( 'Nicaraguan Córdoba', WL_MIM_DOMAIN ),
			'NGN' => esc_html__( 'Nigerian Naira', WL_MIM_DOMAIN ),
			'NOK' => esc_html__( 'Norwegian Krone', WL_MIM_DOMAIN ),
			'PKR' => esc_html__( 'Pakistani Rupee', WL_MIM_DOMAIN ),
			'PAB' => esc_html__( 'Panamanian Balboa', WL_MIM_DOMAIN ),
			'PGK' => esc_html__( 'Papua New Guinean Kina', WL_MIM_DOMAIN ),
			'PYG' => esc_html__( 'Paraguayan Guaraní', WL_MIM_DOMAIN ),
			'PEN' => esc_html__( 'Peruvian Nuevo Sol', WL_MIM_DOMAIN ),
			'PHP' => esc_html__( 'Philippine Peso', WL_MIM_DOMAIN ),
			'PLN' => esc_html__( 'Polish Złoty', WL_MIM_DOMAIN ),
			'QAR' => esc_html__( 'Qatari Riyal', WL_MIM_DOMAIN ),
			'RON' => esc_html__( 'Romanian Leu', WL_MIM_DOMAIN ),
			'RUB' => esc_html__( 'Russian Ruble', WL_MIM_DOMAIN ),
			'RWF' => esc_html__( 'Rwandan Franc', WL_MIM_DOMAIN ),
			'STD' => esc_html__( 'São Tomé and Príncipe Dobra', WL_MIM_DOMAIN ),
			'SHP' => esc_html__( 'Saint Helenian Pound', WL_MIM_DOMAIN ),
			'SVC' => esc_html__( 'Salvadoran Colón', WL_MIM_DOMAIN ),
			'WST' => esc_html__( 'Samoan Tala', WL_MIM_DOMAIN ),
			'SAR' => esc_html__( 'Saudi Riyal', WL_MIM_DOMAIN ),
			'RSD' => esc_html__( 'Serbian Dinar', WL_MIM_DOMAIN ),
			'SCR' => esc_html__( 'Seychellois Rupee', WL_MIM_DOMAIN ),
			'SLL' => esc_html__( 'Sierra Leonean Leone', WL_MIM_DOMAIN ),
			'SGD' => esc_html__( 'Singapore Dollar', WL_MIM_DOMAIN ),
			'SBD' => esc_html__( 'Solomon Islands Dollar', WL_MIM_DOMAIN ),
			'SOS' => esc_html__( 'Somali Shilling', WL_MIM_DOMAIN ),
			'ZAR' => esc_html__( 'South African Rand', WL_MIM_DOMAIN ),
			'KRW' => esc_html__( 'South Korean Won', WL_MIM_DOMAIN ),
			'LKR' => esc_html__( 'Sri Lankan Rupee', WL_MIM_DOMAIN ),
			'SRD' => esc_html__( 'Surinamese Dollar', WL_MIM_DOMAIN ),
			'SZL' => esc_html__( 'Swazi Lilangeni', WL_MIM_DOMAIN ),
			'SEK' => esc_html__( 'Swedish Krona', WL_MIM_DOMAIN ),
			'CHF' => esc_html__( 'Swiss Franc', WL_MIM_DOMAIN ),
			'TJS' => esc_html__( 'Tajikistani Somoni', WL_MIM_DOMAIN ),
			'TZS' => esc_html__( 'Tanzanian Shilling', WL_MIM_DOMAIN ),
			'THB' => esc_html__( 'Thai Baht', WL_MIM_DOMAIN ),
			'TOP' => esc_html__( 'Tongan Paʻanga', WL_MIM_DOMAIN ),
			'TTD' => esc_html__( 'Trinidad and Tobago Dollar', WL_MIM_DOMAIN ),
			'TRY' => esc_html__( 'Turkish Lira', WL_MIM_DOMAIN ),
			'UGX' => esc_html__( 'Ugandan Shilling', WL_MIM_DOMAIN ),
			'UAH' => esc_html__( 'Ukrainian Hryvnia', WL_MIM_DOMAIN ),
			'AED' => esc_html__( 'United Arab Emirates Dirham', WL_MIM_DOMAIN ),
			'USD' => esc_html__( 'United States Dollar', WL_MIM_DOMAIN ),
			'UYU' => esc_html__( 'Uruguayan Peso', WL_MIM_DOMAIN ),
			'UZS' => esc_html__( 'Uzbekistani Som', WL_MIM_DOMAIN ),
			'VUV' => esc_html__( 'Vanuatu Vatu', WL_MIM_DOMAIN ),
			'VND' => esc_html__( 'Vietnamese Đồng', WL_MIM_DOMAIN ),
			'XOF' => esc_html__( 'West African Cfa Franc', WL_MIM_DOMAIN ),
			'YER' => esc_html__( 'Yemeni Rial', WL_MIM_DOMAIN ),
			'ZMW' => esc_html__( 'Zambian Kwacha', WL_MIM_DOMAIN )
		);
	}

	/* If paypal support currency provided */
	public static function paypal_support_currency_provided( $currency ) {
		return in_array( $currency, array_keys( self::get_paypal_supported_currencies() ) );
	}

	/* If paypal support currency for institute */
	public static function paypal_support_currency_institute( $institute_id ) {
		$payment = WL_MIM_SettingHelper::get_payment_settings( $institute_id );

		return in_array( $payment['payment_currency'], array_keys( self::get_paypal_supported_currencies() ) );
	}

	/* If razorpay support currency provided */
	public static function razorpay_support_currency_provided( $currency ) {
		return in_array( $currency, array_keys( self::get_razorpay_supported_currencies() ) );
	}

	/* If razorpay support currency provided */
	public static function instamojo_support_currency_provided( $currency ) {
		return in_array( $currency, array_keys( self::get_instamojo_supported_currencies() ) );
	}

	    /* If paystack support currency provided */
    public static function paystack_support_currency_provided( $currency ) {
        return in_array( $currency, array_keys( self::get_paystack_supported_currencies() ) );
    }

	/* If razorpay support currency for institute */
	public static function razorpay_support_currency_institute( $institute_id ) {
		$payment = WL_MIM_SettingHelper::get_payment_settings( $institute_id );

		return in_array( $payment['payment_currency'], array_keys( self::get_razorpay_supported_currencies() ) );
	}

	/* If paystack support currency for institute */
	public static function paystack_support_currency_institute( $institute_id ) {
		$payment = WL_MIM_SettingHelper::get_payment_settings( $institute_id );

		return in_array( $payment['payment_currency'], array_keys( self::get_paystack_supported_currencies() ) );
	}

	/* If stripe support currency provided */
	public static function stripe_support_currency_provided( $currency ) {
		return in_array( $currency, array_keys( self::get_stripe_supported_currencies() ) );
	}

	/* If stripe support currency for institute */
	public static function stripe_support_currency_institute( $institute_id ) {
		$payment = WL_MIM_SettingHelper::get_payment_settings( $institute_id );

		return in_array( $payment['payment_currency'], array_keys( self::get_stripe_supported_currencies() ) );
	}

	/* If paypal is enabled and support currency for institute */
	public static function paypal_enabled_institute( $institute_id ) {
		$payment        = WL_MIM_SettingHelper::get_payment_settings( $institute_id );
		$payment_paypal = WL_MIM_SettingHelper::get_payment_paypal_settings( $institute_id );

		return $payment_paypal['enable'] && self::paypal_support_currency_provided( $payment['payment_currency'] );
	}

	/* If razorpay is enabled and support currency for institute */
	public static function razorpay_enabled_institute( $institute_id ) {
		$payment          = WL_MIM_SettingHelper::get_payment_settings( $institute_id );
		$payment_razorpay = WL_MIM_SettingHelper::get_payment_razorpay_settings( $institute_id );

		return $payment_razorpay['enable'] && self::razorpay_support_currency_provided( $payment['payment_currency'] );
	}

	/* If instamojo is enabled and support currency for institute */
	public static function instamojo_enabled_institute( $institute_id ) {
		$payment          = WL_MIM_SettingHelper::get_payment_settings( $institute_id );
		$payment_instamojo = WL_MIM_SettingHelper::get_payment_instamojo_settings( $institute_id );

		return $payment_instamojo['enable'] && self::instamojo_support_currency_provided( $payment['payment_currency'] );
	}

	/* If paystack is enabled and support currency for institute */
	public static function paystack_enabled_institute( $institute_id ) {
		$payment          = WL_MIM_SettingHelper::get_payment_settings( $institute_id );
		$payment_paystack = WL_MIM_SettingHelper::get_payment_paystack_settings( $institute_id );

		return $payment_paystack['enable'] && self::paystack_support_currency_provided( $payment['payment_currency'] );
	}

	/* If stripe is enabled and support currency for institute */
	public static function stripe_enabled_institute( $institute_id ) {
		$payment        = WL_MIM_SettingHelper::get_payment_settings( $institute_id );
		$payment_stripe = WL_MIM_SettingHelper::get_payment_stripe_settings( $institute_id );

		return $payment_stripe['enable'] && self::stripe_support_currency_provided( $payment['payment_currency'] );
	}

	/* Get all currencies */
	public static function get_all_currencies() {
		return array_merge( self::get_paypal_supported_currencies(), self::get_razorpay_supported_currencies(), self::get_paystack_supported_currencies(), self::get_stripe_supported_currencies() );
	}

	/* Check if all payment methods are unavailable for institute */
	public static function payment_methods_unavailable_institute( $institute_id ) {
		return ( ! self::paypal_enabled_institute( $institute_id ) && ! self::razorpay_enabled_institute( $institute_id ) && ! self::stripe_enabled_institute( $institute_id ) && ! self::paystack_enabled_institute( $institute_id ) && ! self::instamojo_enabled_institute( $institute_id ) );
	}

	public static function get_currency_symbol_institute( $institute_id ) {
		$payment = WL_MIM_SettingHelper::get_payment_settings( $institute_id );
		$code    = $payment['payment_currency'];
		if ( array_key_exists( $code, self::currency_symbols() ) ) {
			return self::currency_symbols()[ $code ];
		}

		return '';
	}

	/* Currency symbols */
	private static function currency_symbols() {
		return array(
			'AED' => '&#1583;.&#1573;',
			'AFN' => '&#65;&#102;',
			'ALL' => '&#76;&#101;&#107;',
			'ANG' => '&#402;',
			'AOA' => '&#75;&#122;',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => '&#402;',
			'AZN' => '&#1084;&#1072;&#1085;',
			'BAM' => '&#75;&#77;',
			'BBD' => '&#36;',
			'BDT' => '&#2547;',
			'BGN' => '&#1083;&#1074;',
			'BHD' => '.&#1583;.&#1576;',
			'BIF' => '&#70;&#66;&#117;',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => '&#36;&#98;',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTN' => '&#78;&#117;&#46;',
			'BWP' => '&#80;',
			'BYR' => '&#112;&#46;',
			'BZD' => '&#66;&#90;&#36;',
			'CAD' => '&#36;',
			'CDF' => '&#70;&#67;',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&#165;',
			'COP' => '&#36;',
			'CRC' => '&#8353;',
			'CUP' => '&#8396;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => '&#70;&#100;&#106;',
			'DKK' => '&#107;&#114;',
			'DOP' => '&#82;&#68;&#36;',
			'DZD' => '&#1583;&#1580;',
			'EGP' => '&#163;',
			'ETB' => '&#66;&#114;',
			'EUR' => '&#8364;',
			'FJD' => '&#36;',
			'FKP' => '&#163;',
			'GBP' => '&#163;',
			'GEL' => '&#4314;',
			'GHS' => '&#162;',
			'GIP' => '&#163;',
			'GMD' => '&#68;',
			'GNF' => '&#70;&#71;',
			'GTQ' => '&#81;',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => '&#76;',
			'HRK' => '&#107;&#110;',
			'HTG' => '&#71;',
			'HUF' => '&#70;&#116;',
			'IDR' => '&#82;&#112;',
			'ILS' => '&#8362;',
			'INR' => '&#8377;',
			'IQD' => '&#1593;.&#1583;',
			'IRR' => '&#65020;',
			'ISK' => '&#107;&#114;',
			'JEP' => '&#163;',
			'JMD' => '&#74;&#36;',
			'JOD' => '&#74;&#68;',
			'JPY' => '&#165;',
			'KES' => '&#75;&#83;&#104;',
			'KGS' => '&#1083;&#1074;',
			'KHR' => '&#6107;',
			'KMF' => '&#67;&#70;',
			'KPW' => '&#8361;',
			'KRW' => '&#8361;',
			'KWD' => '&#1583;.&#1603;',
			'KYD' => '&#36;',
			'KZT' => '&#1083;&#1074;',
			'LAK' => '&#8365;',
			'LBP' => '&#163;',
			'LKR' => '&#8360;',
			'LRD' => '&#36;',
			'LSL' => '&#76;',
			'LTL' => '&#76;&#116;',
			'LVL' => '&#76;&#115;',
			'LYD' => '&#1604;.&#1583;',
			'MAD' => '&#1583;.&#1605;.',
			'MDL' => '&#76;',
			'MGA' => '&#65;&#114;',
			'MKD' => '&#1076;&#1077;&#1085;',
			'MMK' => '&#75;',
			'MNT' => '&#8366;',
			'MOP' => '&#77;&#79;&#80;&#36;',
			'MRO' => '&#85;&#77;',
			'MUR' => '&#8360;',
			'MVR' => '.&#1923;',
			'MWK' => '&#77;&#75;',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => '&#77;&#84;',
			'NAD' => '&#36;',
			'NGN' => '&#8358;',
			'NIO' => '&#67;&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#65020;',
			'PAB' => '&#66;&#47;&#46;',
			'PEN' => '&#83;&#47;&#46;',
			'PGK' => '&#75;',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PYG' => '&#71;&#115;',
			'QAR' => '&#65020;',
			'RON' => '&#108;&#101;&#105;',
			'RSD' => '&#1044;&#1080;&#1085;&#46;',
			'RUB' => '&#1088;&#1091;&#1073;',
			'RWF' => '&#1585;.&#1587;',
			'SAR' => '&#65020;',
			'SBD' => '&#36;',
			'SCR' => '&#8360;',
			'SDG' => '&#163;',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&#163;',
			'SLL' => '&#76;&#101;',
			'SOS' => '&#83;',
			'SRD' => '&#36;',
			'STD' => '&#68;&#98;',
			'SVC' => '&#36;',
			'SYP' => '&#163;',
			'SZL' => '&#76;',
			'THB' => '&#3647;',
			'TJS' => '&#84;&#74;&#83;',
			'TMT' => '&#109;',
			'TND' => '&#1583;.&#1578;',
			'TOP' => '&#84;&#36;',
			'TRY' => '&#8356;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => '',
			'UAH' => '&#8372;',
			'UGX' => '&#85;&#83;&#104;',
			'USD' => '&#36;',
			'UYU' => '&#36;&#85;',
			'UZS' => '&#1083;&#1074;',
			'VEF' => '&#66;&#115;',
			'VND' => '&#8363;',
			'VUV' => '&#86;&#84;',
			'WST' => '&#87;&#83;&#36;',
			'XAF' => '&#70;&#67;&#70;&#65;',
			'XCD' => '&#36;',
			'XDR' => '',
			'XOF' => '',
			'XPF' => '&#70;',
			'YER' => '&#65020;',
			'ZAR' => '&#82;',
			'ZMK' => '&#90;&#75;',
			'ZWL' => '&#90;&#36;'
		);
	}
}