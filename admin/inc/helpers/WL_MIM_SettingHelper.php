<?php
defined('ABSPATH') || die();

class WL_MIM_SettingHelper {
	/* General settings - enquiry */
	public static function get_general_enquiry_settings($institute_id) {
		global $wpdb;
		$enquiry_form_title_enable = '1';
		$enquiry_form_title        = esc_html__("Admission Enquiry", WL_MIM_DOMAIN);
		$general_enquiry           = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'general_enquiry'");
		if ($general_enquiry) {
			$general_enquiry           = unserialize($general_enquiry->mim_value);
			$enquiry_form_title_enable = isset($general_enquiry['enquiry_form_title_enable']) ? $general_enquiry['enquiry_form_title_enable'] : '';
			$enquiry_form_title        = isset($general_enquiry['enquiry_form_title']) ? $general_enquiry['enquiry_form_title'] : '';
			if (empty($enquiry_form_title)) {
				$enquiry_form_title = esc_html__("Admission Enquiry", WL_MIM_DOMAIN);
			}
		}

		return array(
			'enquiry_form_title_enable' => $enquiry_form_title_enable,
			'enquiry_form_title'        => $enquiry_form_title
		);
	}

	/* General settings - institute */
	public static function get_general_institute_settings($institute_id) {
		global $wpdb;
		$institute_logo        = '';
		$institute_logo_enable = '';
		$institute_name        = '';
		$institute_address     = '';
		$institute_center_code = '';
		$institute_phone       = '';
		$institute_email       = '';

		$admit_card_signature         = '';
		$admit_card_signature_enable  = '';
		$certificate_signature        = '';
		$certificate_signature_enable = '';

		$general_institute = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'general_institute'");
		if ($general_institute) {
			$general_institute     = unserialize($general_institute->mim_value);
			$institute_logo        = isset($general_institute['institute_logo']) ? $general_institute['institute_logo'] : '';
			$institute_logo_enable = isset($general_institute['institute_logo_enable']) ? $general_institute['institute_logo_enable'] : '';
			$institute_name        = isset($general_institute['institute_name']) ? $general_institute['institute_name'] : '';
			$institute_address     = isset($general_institute['institute_address']) ? $general_institute['institute_address'] : '';
			$institute_center_code = isset($general_institute['institute_center_code']) ? $general_institute['institute_center_code'] : '';
			$institute_phone       = isset($general_institute['institute_phone']) ? $general_institute['institute_phone'] : '';
			$institute_email       = isset($general_institute['institute_email']) ? $general_institute['institute_email'] : '';

			$admit_card_signature         = isset($general_institute['admit_card_signature']) ? $general_institute['admit_card_signature'] : '';
			$admit_card_signature_enable  = isset($general_institute['admit_card_signature_enable']) ? $general_institute['admit_card_signature_enable'] : '';
			$certificate_signature        = isset($general_institute['certificate_signature']) ? $general_institute['certificate_signature'] : '';
			$certificate_signature_enable = isset($general_institute['certificate_signature_enable']) ? $general_institute['certificate_signature_enable'] : '';
		}

		return array(
			'institute_logo'        => $institute_logo,
			'institute_logo_enable' => $institute_logo_enable,
			'institute_name'        => $institute_name,
			'institute_address'     => $institute_address,
			'institute_center_code' => $institute_center_code,
			'institute_phone'       => $institute_phone,
			'institute_email'       => $institute_email
		);
	}

	/* ID card settings */
	public static function get_id_card_settings($institute_id) {
		global $wpdb;
		$sign        = '';
		$sign_enable = '';
		$id_card = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'id_card'");
		if ($id_card) {
			$id_card     = unserialize($id_card->mim_value);
			$sign        = isset($id_card['sign']) ? $id_card['sign'] : '';
			$sign_enable = isset($id_card['sign_enable']) ? $id_card['sign_enable'] : '';
		}

		return array(
			'sign'        => $sign,
			'sign_enable' => $sign_enable,
		);
	}

	/* Admit card settings */
	public static function get_admit_card_settings($institute_id) {
		global $wpdb;
		$sign        = '';
		$sign_enable = '';
		$admit_card = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'admit_card'");
		if ($admit_card) {
			$admit_card  = unserialize($admit_card->mim_value);
			$sign        = isset($admit_card['sign']) ? $admit_card['sign'] : '';
			$sign_enable = isset($admit_card['sign_enable']) ? $admit_card['sign_enable'] : '';
		}

		return array(
			'sign'        => $sign,
			'sign_enable' => $sign_enable,
		);
	}

	/* Certificate settings */
	public static function get_certificate_settings($institute_id) {
		global $wpdb;
		$sign        = '';
		$sign_enable = '';
		$certificate = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'certificate'");
		if ($certificate) {
			$certificate = unserialize($certificate->mim_value);
			$sign        = isset($certificate['sign']) ? $certificate['sign'] : '';
			$sign_enable = isset($certificate['sign_enable']) ? $certificate['sign_enable'] : '';
		}

		return array(
			'sign'        => $sign,
			'sign_enable' => $sign_enable,
		);
	}

	/* Admit card dob enable settings */
	public static function get_admit_card_dob_enable_settings($institute_id) {
		global $wpdb;
		$admit_card_dob = false;
		$admit_card_dob_enable = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'admit_card_dob_enable'");
		if ($admit_card_dob_enable) {
			$admit_card_dob = $admit_card_dob_enable->mim_value;
		}

		return $admit_card_dob;
	}

	/* ID card dob enable settings */
	public static function get_id_card_dob_enable_settings($institute_id) {
		global $wpdb;
		$id_card_dob = false;
		$id_card_dob_enable = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'id_card_dob_enable'");
		if ($id_card_dob_enable) {
			$id_card_dob = $id_card_dob_enable->mim_value;
		}

		return $id_card_dob;
	}

	/* Certificate dob enable settings */
	public static function get_certificate_dob_enable_settings($institute_id) {
		global $wpdb;
		$certificate_dob = false;
		$certificate_dob_enable = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'certificate_dob_enable'");
		if ($certificate_dob_enable) {
			$certificate_dob = $certificate_dob_enable->mim_value;
		}

		return $certificate_dob;
	}

	/* General settings - enrollment prefix */
	public static function get_general_enrollment_prefix_settings($institute_id) {
		global $wpdb;
		$general_enrollment_prefix = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'general_enrollment_prefix'");
		if (!$general_enrollment_prefix || empty($general_enrollment_prefix->mim_value)) {
			$general_enrollment_prefix = 'EN';
		} else {
			$general_enrollment_prefix = $general_enrollment_prefix->mim_value;
		}

		return $general_enrollment_prefix;
	}

	/* General settings - receipt prefix */
	public static function get_general_receipt_prefix_settings($institute_id) {
		global $wpdb;
		$general_receipt_prefix = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'general_receipt_prefix'");
		if (!$general_receipt_prefix || empty($general_receipt_prefix->mim_value)) {
			$general_receipt_prefix = 'R';
		} else {
			$general_receipt_prefix = $general_receipt_prefix->mim_value;
		}

		return $general_receipt_prefix;
	}

	/* General settings - enable roll number */
	public static function get_general_enable_roll_number_settings($institute_id) {
		global $wpdb;
		$general_enable_roll_number = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'general_enable_roll_number'");

		if (!$general_enable_roll_number) {
			$general_enable_roll_number = false;
		} else {
			$general_enable_roll_number = (bool) ($general_enable_roll_number->mim_value);
		}

		return $general_enable_roll_number;
	}

	/* General settings - enable enrollment_id */
	public static function get_general_enable_enrollment_id_settings($institute_id) {
		global $wpdb;
		$general_enable_roll_number = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'general_enable_roll_number'");

		if (!$general_enable_roll_number) {
			$general_enable_roll_number = false;
		} else {
			$general_enable_roll_number = (bool) ($general_enable_roll_number->mim_value);
		}

		return $general_enable_roll_number;
	}

	/* General settings - enable signature in admission detail */
	public static function get_general_enable_signature_in_admission_detail($institute_id) {
		global $wpdb;
		$general_enable_signature_in_admission_detail = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'general_enable_signature_in_admission_detail'");

		if (!$general_enable_signature_in_admission_detail) {
			$general_enable_signature_in_admission_detail = true;
		} else {
			$general_enable_signature_in_admission_detail = (bool) ($general_enable_signature_in_admission_detail->mim_value);
		}

		return $general_enable_signature_in_admission_detail;
	}

	/* Email settings */
	public static function get_email_settings($institute_id) {
		global $wpdb;
		$email_host       = '';
		$email_username   = '';
		$email_password   = '';
		$email_encryption = '';
		$email_port       = '';
		$email_from       = '';
		$email            = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'email'");
		if ($email) {
			$email            = unserialize($email->mim_value);
			$email_host       = isset($email['email_host']) ? $email['email_host'] : '';
			$email_username   = isset($email['email_username']) ? $email['email_username'] : '';
			$email_password   = isset($email['email_password']) ? $email['email_password'] : '';
			$email_encryption = isset($email['email_encryption']) ? $email['email_encryption'] : '';
			$email_port       = isset($email['email_port']) ? $email['email_port'] : '';
			$email_from       = isset($email['email_from']) ? $email['email_from'] : '';
		}

		return array(
			'email_host'       => $email_host,
			'email_username'   => $email_username,
			'email_password'   => $email_password,
			'email_encryption' => $email_encryption,
			'email_port'       => $email_port,
			'email_from'       => $email_from
		);
	}

	/* Payment settings */
	public static function get_payment_settings($institute_id) {
		global $wpdb;
		$payment_currency = '';
		if (!empty($institute_id)) :
			$payment          = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = {$institute_id} AND mim_key = 'payment'");
			if ($payment) {
				$payment          = unserialize($payment->mim_value);
				$payment_currency = isset($payment['payment_currency']) ? $payment['payment_currency'] : '';
			}
		endif;
		return array(
			'payment_currency' => $payment_currency
		);
	}

	/* Payment settings: PayPal */
	public static function get_payment_paypal_settings($institute_id) {
		global $wpdb;
		$payment_paypal_enable         = '';
		$payment_paypal_mode           = '';
		$payment_paypal_business_email = '';
		$payment_paypal                = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'payment_paypal'");
		if ($payment_paypal) {
			$payment_paypal                = unserialize($payment_paypal->mim_value);
			$payment_paypal_enable         = isset($payment_paypal['enable']) ? $payment_paypal['enable'] : '';
			$payment_paypal_mode           = isset($payment_paypal['mode']) ? $payment_paypal['mode'] : '';
			$payment_paypal_business_email = isset($payment_paypal['business_email']) ? $payment_paypal['business_email'] : '';
		}

		return array(
			'enable'         => $payment_paypal_enable,
			'mode'           => $payment_paypal_mode,
			'business_email' => $payment_paypal_business_email
		);
	}

	/* Payment settings: Razorpay */
	public static function get_payment_razorpay_settings($institute_id) {
		global $wpdb;
		$payment_razorpay_enable = '';
		$payment_razorpay_key    = '';
		$payment_razorpay_secret = '';
		$payment_razorpay        = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'payment_razorpay'");
		if ($payment_razorpay) {
			$payment_razorpay        = unserialize($payment_razorpay->mim_value);
			$payment_razorpay_enable = isset($payment_razorpay['enable']) ? $payment_razorpay['enable'] : '';
			$payment_razorpay_key    = isset($payment_razorpay['key']) ? $payment_razorpay['key'] : '';
			$payment_razorpay_secret = isset($payment_razorpay['secret']) ? $payment_razorpay['secret'] : '';
		}
		return array(
			'enable' => $payment_razorpay_enable,
			'key'    => $payment_razorpay_key,
			'secret' => $payment_razorpay_secret
		);
	}

	/* Payment settings: Instamojo */
	public static function get_payment_instamojo_settings($institute_id) {
		global $wpdb;
		$payment_instamojo_enable = '';
		$payment_instamojo_client_id    = '';
		$payment_instamojo_client_secret = '';
		$payment_instamojo_mode = '';
		$payment_instamojo = null;
		if (!empty($institute_id)) :
			$payment_instamojo        = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = {$institute_id} AND mim_key = 'payment_instamojo'");
		endif;
		if ($payment_instamojo) {
			$payment_instamojo        = unserialize($payment_instamojo->mim_value);
			$payment_instamojo_enable = isset($payment_instamojo['enable']) ? $payment_instamojo['enable'] : '';
			$payment_instamojo_client_id    = isset($payment_instamojo['client_id']) ? $payment_instamojo['client_id'] : '';
			$payment_instamojo_client_secret = isset($payment_instamojo['client_secret']) ? $payment_instamojo['client_secret'] : '';
			$payment_instamojo_mode = isset($payment_instamojo['mode']) ? $payment_instamojo['mode'] : 'test';
		}

		return array(
			'enable' => $payment_instamojo_enable,
			'client_id'    => $payment_instamojo_client_id,
			'client_secret' => $payment_instamojo_client_secret,
			'mode' => $payment_instamojo_mode
		);
	}

	/* Payment settings: paystack */
	public static function get_payment_paystack_settings($institute_id) {
		global $wpdb;
		$payment_paystack_enable = '';
		$payment_paystack_key    = '';
		$payment_paystack_secret = '';
		$payment_paystack        = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'payment_paystack'");
		if ($payment_paystack) {
			$payment_paystack        = unserialize($payment_paystack->mim_value);
			$payment_paystack_enable = isset($payment_paystack['enable']) ? $payment_paystack['enable'] : '';
			$payment_paystack_key    = isset($payment_paystack['key']) ? $payment_paystack['key'] : '';
			$payment_paystack_secret = isset($payment_paystack['secret']) ? $payment_paystack['secret'] : '';
		}

		return array(
			'enable' => $payment_paystack_enable,
			'key'    => $payment_paystack_key,
			'secret' => $payment_paystack_secret
		);
	}

	/* Payment settings: Stripe */
	public static function get_payment_stripe_settings($institute_id) {
		global $wpdb;
		$payment_stripe_enable          = '';
		$payment_stripe_publishable_key = '';
		$payment_stripe_secret_key      = '';
		$payment_stripe                 = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'payment_stripe'");
		if ($payment_stripe) {
			$payment_stripe                 = unserialize($payment_stripe->mim_value);
			$payment_stripe_enable          = isset($payment_stripe['enable']) ? $payment_stripe['enable'] : '';
			$payment_stripe_publishable_key = isset($payment_stripe['publishable_key']) ? $payment_stripe['publishable_key'] : '';
			$payment_stripe_secret_key      = isset($payment_stripe['secret_key']) ? $payment_stripe['secret_key'] : '';
		}

		return array(
			'enable'          => $payment_stripe_enable,
			'publishable_key' => $payment_stripe_publishable_key,
			'secret_key'      => $payment_stripe_secret_key
		);
	}

	/* SMS settings */
	public static function get_sms_settings($institute_id) {
		global $wpdb;
		$sms_provider     = '';
		$sms_admin_number = '';
		$sms              = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'sms'");
		if ($sms) {
			$sms              = unserialize($sms->mim_value);
			$sms_provider     = isset($sms['sms_provider']) ? $sms['sms_provider'] : '';
			$sms_admin_number = isset($sms['sms_admin_number']) ? $sms['sms_admin_number'] : '';
		}

		return array(
			'sms_provider'     => $sms_provider,
			'sms_admin_number' => $sms_admin_number
		);
	}

	/* SMS settings: SMSStriker */
	public static function get_sms_smsstriker_settings($institute_id) {
		global $wpdb;
		$sms_striker_username  = '';
		$sms_striker_password  = '';
		$sms_striker_sender_id = '';
		$sms_striker           = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'sms_striker'");
		if ($sms_striker) {
			$sms_striker           = unserialize($sms_striker->mim_value);
			$sms_striker_username  = isset($sms_striker['username']) ? $sms_striker['username'] : '';
			$sms_striker_password  = isset($sms_striker['password']) ? $sms_striker['password'] : '';
			$sms_striker_sender_id = isset($sms_striker['sender_id']) ? $sms_striker['sender_id'] : '';
		}

		return array(
			'username'  => $sms_striker_username,
			'password'  => $sms_striker_password,
			'sender_id' => $sms_striker_sender_id
		);
	}

	/* SMS settings: PointSMS */
	public static function get_sms_pointsms_settings($institute_id) {
		global $wpdb;
		$sms_pointsms_username  = '';
		$sms_pointsms_password  = '';
		$sms_pointsms_sender_id = '';
		$sms_pointsms_channel = '';
		$sms_pointsms_route = '';
		$sms_pointsms_peid = '';
		$sms_pointsms           = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'sms_pointsms'");
		if ($sms_pointsms) {
			$sms_pointsms           = unserialize($sms_pointsms->mim_value);
			$sms_pointsms_username  = isset($sms_pointsms['username']) ? $sms_pointsms['username'] : '';
			$sms_pointsms_password  = isset($sms_pointsms['password']) ? $sms_pointsms['password'] : '';
			$sms_pointsms_sender_id = isset($sms_pointsms['sender_id']) ? $sms_pointsms['sender_id'] : '';
			$sms_pointsms_channel   = isset($sms_pointsms['channel']) ? $sms_pointsms['channel'] : '';
			$sms_pointsms_route     = isset($sms_pointsms['route']) ? $sms_pointsms['route'] : '';
			$sms_pointsms_peid      = isset($sms_pointsms['peid']) ? $sms_pointsms['peid'] : '';
		}

		return array(
			'username'  => $sms_pointsms_username,
			'password'  => $sms_pointsms_password,
			'sender_id' => $sms_pointsms_sender_id,
			'channel'   => $sms_pointsms_channel,
			'route'     => $sms_pointsms_route,
			'peid'      => $sms_pointsms_peid,
		);
	}

	/* SMS settings: auurumdigital */
	public static function get_sms_auurumdigital_settings($institute_id) {
		global $wpdb;
		$sms_auurumdigital_username  = '';
		$sms_auurumdigital_password  = '';
		$sms_auurumdigital_sender_id = '';
		$sms_auurumdigital_channel   = '';
		$sms_auurumdigital_route     = '';
		$sms_auurumdigital           = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'sms_auurumdigital'");
		if ($sms_auurumdigital) {
			$sms_auurumdigital           = unserialize($sms_auurumdigital->mim_value);
			$sms_auurumdigital_username  = isset($sms_auurumdigital['username']) ? $sms_auurumdigital['username'] : '';
			$sms_auurumdigital_password  = isset($sms_auurumdigital['password']) ? $sms_auurumdigital['password'] : '';
			$sms_auurumdigital_sender_id = isset($sms_auurumdigital['sender_id']) ? $sms_auurumdigital['sender_id'] : '';
			$sms_auurumdigital_channel   = isset($sms_auurumdigital['channel']) ? $sms_auurumdigital['channel'] : '';
			$sms_auurumdigital_route     = isset($sms_auurumdigital['route']) ? $sms_auurumdigital['route'] : '';
		}

		return array(
			'username'  => $sms_auurumdigital_username,
			'password'  => $sms_auurumdigital_password,
			'sender_id' => $sms_auurumdigital_sender_id,
			'channel'   => $sms_auurumdigital_channel,
			'route'     => $sms_auurumdigital_route
		);
	}

	/* SMS settings: MsgClub */
	public static function get_sms_msgclub_settings($institute_id) {
		global $wpdb;
		$sms_msgclub_auth_key     = '';
		$sms_msgclub_sender_id    = '';
		$sms_msgclub_route_id     = '';
		$sms_msgclub_content_type = '';
		$sms_msgclub_peid         = '';
		$sms_msgclub_tel_id       = '';
		$sms_msgclub               = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'sms_msgclub'");
		if ($sms_msgclub) {
			$sms_msgclub              = unserialize($sms_msgclub->mim_value);
			$sms_msgclub_auth_key     = isset($sms_msgclub['auth_key']) ? $sms_msgclub['auth_key'] : '';
			$sms_msgclub_sender_id    = isset($sms_msgclub['sender_id']) ? $sms_msgclub['sender_id'] : '';
			$sms_msgclub_route_id     = isset($sms_msgclub['route_id']) ? $sms_msgclub['route_id'] : '';
			$sms_msgclub_content_type = isset($sms_msgclub['content_type']) ? $sms_msgclub['content_type'] : '';
			$sms_msgclub_peid         = isset($sms_msgclub['peid']) ? $sms_msgclub['peid'] : '';
			$sms_msgclub_tel_id       = isset($sms_msgclub['tel_id']) ? $sms_msgclub['tel_id'] : '';

		}

		return array(
			'auth_key'     => $sms_msgclub_auth_key,
			'sender_id'    => $sms_msgclub_sender_id,
			'route_id'     => $sms_msgclub_route_id,
			'content_type' => $sms_msgclub_content_type,
			'peid'         => $sms_msgclub_peid,
			'tel_id'       => $sms_msgclub_tel_id,
		);
	}

	/* SMS settings: Nexmo */
	public static function get_sms_nexmo_settings($institute_id) {
		global $wpdb;
		$sms_nexmo_api_key    = '';
		$sms_nexmo_api_secret = '';
		$sms_nexmo_from       = '';
		$sms_nexmo            = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'sms_nexmo'");
		if ($sms_nexmo) {
			$sms_nexmo            = unserialize($sms_nexmo->mim_value);
			$sms_nexmo_api_key    = isset($sms_nexmo['api_key']) ? $sms_nexmo['api_key'] : '';
			$sms_nexmo_api_secret = isset($sms_nexmo['api_secret']) ? $sms_nexmo['api_secret'] : '';
			$sms_nexmo_from       = isset($sms_nexmo['from']) ? $sms_nexmo['from'] : '';
		}

		return array(
			'api_key'    => $sms_nexmo_api_key,
			'api_secret' => $sms_nexmo_api_secret,
			'from'       => $sms_nexmo_from
		);
	}

	/* SMS settings: Textlocal */
	public static function get_sms_textlocal_settings($institute_id) {
		global $wpdb;
		$sms_textlocal_api_key = '';
		$sms_textlocal_sender  = '';
		$sms_textlocal         = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'sms_textlocal'");
		if ($sms_textlocal) {
			$sms_textlocal         = unserialize($sms_textlocal->mim_value);
			$sms_textlocal_api_key = isset($sms_textlocal['api_key']) ? $sms_textlocal['api_key'] : '';
			$sms_textlocal_sender  = isset($sms_textlocal['sender']) ? $sms_textlocal['sender'] : '';
		}

		return array(
			'api_key' => $sms_textlocal_api_key,
			'sender'  => $sms_textlocal_sender
		);
	}

	/* SMS settings: EBulkSMS */
	public static function get_sms_ebulksms_settings($institute_id) {
		global $wpdb;
		$sms_ebulksms_username = '';
		$sms_ebulksms_api_key  = '';
		$sms_ebulksms_sender   = '';
		$sms_ebulksms          = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = 'sms_ebulksms'");
		if ($sms_ebulksms) {
			$sms_ebulksms          = unserialize($sms_ebulksms->mim_value);
			$sms_ebulksms_username = isset($sms_ebulksms['username']) ? $sms_ebulksms['username'] : '';
			$sms_ebulksms_api_key  = isset($sms_ebulksms['api_key']) ? $sms_ebulksms['api_key'] : '';
			$sms_ebulksms_sender   = isset($sms_ebulksms['sender']) ? $sms_ebulksms['sender'] : '';
		}

		return array(
			'username' => $sms_ebulksms_username,
			'api_key'  => $sms_ebulksms_api_key,
			'sender'   => $sms_ebulksms_sender
		);
	}

	/* SMS template settings */
	public static function get_sms_template($key, $institute_id, $message) {
		global $wpdb;
		$enable = 0;

		$sms_template = $wpdb->get_row("SELECT id, mim_value FROM {$wpdb->prefix}wl_min_settings WHERE institute_id = $institute_id AND mim_key = '$key'");
		if ($sms_template) {
			$sms_template = unserialize($sms_template->mim_value);

			$enable      = isset($sms_template['enable']) ? $sms_template['enable'] : 0;
			$message     = isset($sms_template['message']) ? $sms_template['message'] : $message;
			$template_id = isset($sms_template['template_id']) ? ($sms_template['template_id']) : '';
		} else {
			$template_id =  '';
		}

		return array(
			'enable'      => $enable,
			'message'     => $message,
			'template_id' => $template_id,
		);
	}

	/* SMS template settings: enquiry received */
	public static function get_sms_template_enquiry_received($institute_id) {
		$message = esc_html__('Your enquiry for course [COURSE_NAME] [COURSE_CODE] has been received. Thank you.', WL_MIM_DOMAIN);

		return self::get_sms_template('sms_template_enquiry_received', $institute_id, $message);
	}

	/* SMS template settings: enquiry received to admin */
	public static function get_sms_template_enquiry_received_to_admin($institute_id) {
		$message = esc_html__('New enquiry for course [COURSE_NAME] [COURSE_CODE] has been received.', WL_MIM_DOMAIN);

		return self::get_sms_template('sms_template_enquiry_received_to_admin', $institute_id, $message);
	}

	/* SMS template settings: student registered */
	public static function get_sms_template_student_registered($institute_id) {
		$message = esc_html__('Your enrollment number is [ENROLLMENT_ID]. You can login with username: [USERNAME] and password: [PASSWORD] using url: [LOGIN_URL]. Thank you.', WL_MIM_DOMAIN);

		return self::get_sms_template('sms_template_student_registered', $institute_id, $message);
	}

	/* SMS template settings: fees submitted */
	public static function get_sms_template_fees_submitted($institute_id) {
		$message = esc_html__('Your [FEES] was submitted on [DATE].', WL_MIM_DOMAIN);

		return self::get_sms_template('sms_template_fees_submitted', $institute_id, $message);
	}

	/* SMS template settings: student birthday */
	public static function get_sms_template_student_birthday($institute_id) {
		$message = '';

		return self::get_sms_template('sms_template_student_birthday', $institute_id, $message);
	}
}
