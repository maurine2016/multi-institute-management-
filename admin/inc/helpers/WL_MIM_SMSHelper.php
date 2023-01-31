<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/vendor/autoload.php' );

class WL_MIM_SMSHelper {
	/* Send SMS */
	public static function send_sms( $sms, $institute_id, $message, $numbers, $template_id = null  ) {
		$sms_provider = $sms['sms_provider'];
		$sms_sent     = false;
		switch ( $sms_provider ) {
			case 'smsstriker':
				$sms_sent = self::smsstriker( $institute_id, $message, $numbers );
				break;
			case 'pointsms':
				$sms_sent = self::pointsms( $institute_id, $message, $numbers, $template_id );
				break;
			case 'auurumdigital':
				$sms_sent = self::auurumdigital( $institute_id, $message, $numbers );
				break;
			case 'msgclub':
				$sms_sent = self::msgclub( $institute_id, $message, $numbers, $template_id );
				break;
			case 'nexmo':
				$sms_sent = self::nexmo( $institute_id, $message, $numbers );
				break;
			case 'textlocal':
				$sms_sent = self::textlocal( $institute_id, $message, $numbers );
				break;
			case 'ebulksms':
				$sms_sent = self::ebulksms( $institute_id, $message, $numbers );
				break;
			default:
				$sms_sent = false;
				break;
		}

		return $sms_sent;
	}

	/* Send SMS using SMSStriker */
	public static function smsstriker( $institute_id, $message, $numbers ) {
		$sms_striker = WL_MIM_SettingHelper::get_sms_smsstriker_settings( $institute_id );
		try {
			$username  = $sms_striker['username'];
			$password  = $sms_striker['password'];
			$sender_id = $sms_striker['sender_id'];

			if ( is_array( $numbers ) ) {
				foreach ( $numbers as $key => $number ) {
					if ( strlen( $number ) == 12 && substr( $number, 0, 2 ) == "91" ) {
						$numbers[ $key ] = substr( $number, 2, 10 );
					} elseif ( strlen( $number ) == 13 && substr( $number, 0, 3 ) == "+91" ) {
						$numbers[ $key ] = substr( $number, 3, 10 );
					}
				}
				$number = implode( ', ', $numbers );
			} else {
				if ( strlen( $numbers ) == 12 && substr( $numbers, 0, 2 ) == "91" ) {
					$number = substr( $numbers, 2, 10 );
				} elseif ( strlen( $numbers ) == 13 && substr( $numbers, 0, 3 ) == "+91" ) {
					$number = substr( $numbers, 3, 10 );
				} elseif ( strlen( $numbers ) == 11 && substr( $numbers, 0, 1 ) == "0" ) {
					$number = substr( $numbers, 1, 10 );
				} else {
					$number = $numbers;
				}
			}

			if ( ! ( $username && $password && $sender_id ) ) {
				return false;
			}

			$data = array(
				"username"  => $username,
				"password"  => $password,
				"to"        => $number,
				"from"      => $sender_id,
				"msg"       => $message,
				"type"      => 1,
				"dnd_check" => 0,
			);

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, "https://www.smsstriker.com/API/sms.php" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			$result = curl_exec( $ch );
			curl_close( $ch );

			if ( $result ) {
				return true;
			}
		} catch ( \Exception $e ) {
			return false;
		}

		return false;
	}

	/* Send SMS using PointSMS */
	public static function pointsms( $institute_id, $message, $numbers ) {
		$sms_pointsms = WL_MIM_SettingHelper::get_sms_pointsms_settings( $institute_id );
		try {
			$username  = $sms_pointsms['username'];
			$password  = $sms_pointsms['password'];
			$sender_id = $sms_pointsms['sender_id'];
			$channel   = $sms_pointsms['channel'];
			$route     = $sms_pointsms['route'];

			if ( is_array( $numbers ) ) {
				foreach ( $numbers as $key => $number ) {
					if ( ( 12 == strlen( $number ) ) && ( '91' == substr( $number, 0, 2 ) ) ) {
						$numbers[ $key ] = substr( $number, 2, 10 );
					} elseif ( ( 13 == strlen( $number ) ) && ( '+91' == substr( $number, 0, 3 ) ) ) {
						$numbers[ $key ] = substr( $number, 3, 10 );
					} elseif ( ( 11 == strlen( $number ) ) && ( '0' == substr( $number, 0, 1 ) ) ) {
						$numbers[ $key ] = substr( $number, 3, 10 );
					}
				}
				$number = implode( ',', $numbers );
			} else {
				if ( ( 12 == strlen( $numbers ) ) && ( '91' == substr( $numbers, 0, 2 ) ) ) {
					$number = substr( $numbers, 2, 10 );
				} elseif ( ( 13 == strlen( $numbers ) ) && ( '+91' == substr( $numbers, 0, 3 ) ) ) {
					$number = substr( $numbers, 3, 10 );
				} elseif ( ( 11 == strlen( $numbers ) ) && ( '0' == substr( $numbers, 0, 1 ) ) ) {
					$number = substr( $numbers, 1, 10 );
				} else {
					$number = $numbers;
				}
			}

			if ( ! ( $username && $password && $sender_id ) ) {
				return false;
			}

			$url = add_query_arg(
				array(
					"user"     => $username,
					"password" => $password,
					"number"   => $number,
					"senderid" => $sender_id,
					"channel"  => $channel,
					"DCS"      => 0,
					"flashsms" => 0,
					"text"     => $message,
					"route"    => $route,
				),
				'http://smslogin.pcexpert.in/api/mt/SendSMS'
			);

			$response = wp_remote_get( $url );
			$result   = wp_remote_retrieve_body( $response );

			if ( $result ) {
				return true;
			}
		} catch ( \Exception $e ) {
			return false;
		}

		return false;
	}

	/* Send SMS using auurumdigital */
	public static function auurumdigital( $institute_id, $message, $numbers ) {
		$sms_auurumdigital = WL_MIM_SettingHelper::get_sms_auurumdigital_settings( $institute_id );

		try {
			$username  = $sms_auurumdigital['username'];
			$password  = $sms_auurumdigital['password'];
			$sender_id = $sms_auurumdigital['sender_id'];
			$channel   = $sms_auurumdigital['channel'];
			$route     = $sms_auurumdigital['route'];

			if ( is_array( $numbers ) ) {
				foreach ( $numbers as $key => $number ) {
					if ( ( 12 == strlen( $number ) ) && ( '91' == substr( $number, 0, 2 ) ) ) {
						$numbers[ $key ] = substr( $number, 2, 10 );
					} elseif ( ( 13 == strlen( $number ) ) && ( '+91' == substr( $number, 0, 3 ) ) ) {
						$numbers[ $key ] = substr( $number, 3, 10 );
					} elseif ( ( 11 == strlen( $number ) ) && ( '0' == substr( $number, 0, 1 ) ) ) {
						$numbers[ $key ] = substr( $number, 3, 10 );
					}
				}
				$number = implode( ',', $numbers );
			} else {
				if ( ( 12 == strlen( $numbers ) ) && ( '91' == substr( $numbers, 0, 2 ) ) ) {
					$number = substr( $numbers, 2, 10 );
				} elseif ( ( 13 == strlen( $numbers ) ) && ( '+91' == substr( $numbers, 0, 3 ) ) ) {
					$number = substr( $numbers, 3, 10 );
				} elseif ( ( 11 == strlen( $numbers ) ) && ( '0' == substr( $numbers, 0, 1 ) ) ) {
					$number = substr( $numbers, 1, 10 );
				} else {
					$number = $numbers;
				}
			}

			if ( ! ( $username && $password && $sender_id ) ) {
				return false;
			}

			$url = add_query_arg(
				array(
					"user"     => $username,
					"password" => $password,
					"number"   => $number,
					"senderid" => $sender_id,
					"channel"  => $channel,
					"DCS"      => 0,
					"flashsms" => 0,
					"text"     => $message,
					"route"    => $route,
				),
				'http://sms.auurumdigital.com/api/mt/SendSMS'
			);
			$response = wp_remote_get( $url );
			$result   = wp_remote_retrieve_body( $response );
			// var_dump($url); die;
			if ( $result ) {
				return true;
			}
		} catch ( \Exception $e ) {
			return false;
		}

		return false;
	}

	/* Send SMS using MsgClub */
	public static function msgclub( $institute_id, $message, $numbers, $template_id ) {
		$sms_msgclub = WL_MIM_SettingHelper::get_sms_msgclub_settings( $institute_id );
		try {
			$auth_key     = $sms_msgclub['auth_key'];
			$sender_id    = $sms_msgclub['sender_id'];
			$route_id     = $sms_msgclub['route_id'];
			$content_type = $sms_msgclub['content_type'];
			$peid         = $sms_msgclub['peid'];
			$tel_id       = $sms_msgclub['tel_id'];

			if ( is_array( $numbers ) ) {
				foreach ( $numbers as $key => $number ) {
					if ( strlen( $number ) == 12 && substr( $number, 0, 2 ) == "91" ) {
						$numbers[ $key ] = substr( $number, 2, 10 );
					} elseif ( strlen( $number ) == 13 && substr( $number, 0, 3 ) == "+91" ) {
						$numbers[ $key ] = substr( $number, 3, 10 );
					}
				}
				$number = implode( ', ', $numbers );
			} else {
				if ( strlen( $numbers ) == 12 && substr( $numbers, 0, 2 ) == "91" ) {
					$number = substr( $numbers, 2, 10 );
				} elseif ( strlen( $numbers ) == 13 && substr( $numbers, 0, 3 ) == "+91" ) {
					$number = substr( $numbers, 3, 10 );
				} elseif ( strlen( $numbers ) == 11 && substr( $numbers, 0, 1 ) == "0" ) {
					$number = substr( $numbers, 1, 10 );
				} else {
					$number = $numbers;
				}
			}

			if ( ! ( $auth_key && $sender_id && $route_id && $content_type ) ) {
				return false;
			}

			$url = add_query_arg(
				array(
					'AUTH_KEY'       => $auth_key,
					'message'        => urlencode( $message ),
					'senderId'       => $sender_id,
					'routeId'        => $route_id,
					'mobileNos'      => $number,
					'smsContentType' => $content_type,
					'entityid'       => $peid,
					'tmid'           => $tel_id,
					'templateid'     => $template_id,
				),
				'http://167.114.117.218/rest/services/sendSMS/sendGroupSms'
			);

			$response = wp_remote_get( $url );
			$result   = wp_remote_retrieve_body( $response );

			if ( $result ) {
				return true;
			}
		} catch ( Exception $e ) {
		}

		return false;
	}

	/* Send SMS using Nexmo */
	public static function nexmo( $institute_id, $message, $numbers ) {
		$sms_nexmo = WL_MIM_SettingHelper::get_sms_nexmo_settings( $institute_id );
		try {
			$api_key    = $sms_nexmo['api_key'];
			$api_secret = $sms_nexmo['api_secret'];
			$from       = $sms_nexmo['from'];

			if ( ! ( $api_key && $api_secret && $from ) ) {
				return false;
			}

			$basic  = new \Nexmo\Client\Credentials\Basic( $api_key, $api_secret );
			$client = new \Nexmo\Client( $basic );

			$response = array();
			if ( is_array( $numbers ) ) {
				foreach ( $numbers as $number ) {
					$message = $client->message()->send( array(
						'to'   => $number,
						'from' => $from,
						'text' => $message
					) );
					array_push( $response, $message->getResponseData() );
				}
			} else {
				$message = $client->message()->send( array(
					'to'   => $numbers,
					'from' => $from,
					'text' => $message
				) );
				array_push( $response, $message->getResponseData() );
			}

			if ( count( $response ) > 0 ) {
				return true;
			}

			return false;

		} catch ( \Exception $e ) {
			return false;
		}

		return false;
	}

	/* Send SMS using Textlocal */
	public static function textlocal( $institute_id, $message, $numbers ) {
		$sms_textlocal = WL_MIM_SettingHelper::get_sms_textlocal_settings( $institute_id );
		try {
			$api_key = $sms_textlocal['api_key'];
			$sender  = $sms_textlocal['sender'];

			if ( is_array( $numbers ) ) {
				$numbers = implode( ',', $numbers );
			}

            $message = urlencode($message);
            $sender  = urlencode($sender);

			if ( ! ( $api_key && $sender ) ) {
				return false;
			}

			$data = array(
				"apikey"  => $api_key,
				"numbers" => $numbers,
				"sender"  => $sender,
				"message" => $message,
			);

			$ch = curl_init('https://api.textlocal.in/send/');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec( $ch );
			curl_close( $ch );

			if ( $result ) {
				return true;
			}
		} catch ( \Exception $e ) {
			return false;
		}

		return false;
	}

	/* Send SMS using EBulkSMS */
	public static function ebulksms( $institute_id, $message, $numbers ) {
		$sms_ebulksms = WL_MIM_SettingHelper::get_sms_ebulksms_settings( $institute_id );
		try {
			$username = $sms_ebulksms['username'];
			$api_key  = $sms_ebulksms['api_key'];
			$sender   = $sms_ebulksms['sender'];
			$flash    = 0;

			if ( ! is_array( $numbers ) ) {
				$numbers = array( $numbers );
			}

			if ( ! ( $username && $api_key && $sender ) ) {
				return false;
			}

			$gsm = array();

			$country_code = '234';

			foreach ( $numbers as $number ) {
				$mobilenumber = trim( $number );
				if ( '0' === substr( $mobilenumber, 0, 1 ) ) {
					$mobilenumber = $country_code . substr( $mobilenumber, 1 );
				} elseif ( '+' === substr( $mobilenumber, 0, 1 ) ) {
					$mobilenumber = substr( $mobilenumber, 1 );
				}

				$generated_id = uniqid( 'int_', false );
				$generated_id = substr( $generated_id, 0, 30 );
				$gsm['gsm'][] = array( 'msidn' => $mobilenumber, 'msgid' => $generated_id );
			}

			$message = array(
				'sender'      => $sender,
				'messagetext' => $message,
				'flash'       => "{$flash}",
			);

			$request = array(
				'SMS' => array(
					'auth' => array(
						'username' => $username,
						'apikey'   => $api_key
					),
					'message'    => $message,
					'recipients' => $gsm
				)
			);

			$json_data = json_encode( $request );

			if ( is_array( $json_data ) ) {
				$json_data = http_build_query( $json_data, '', '&' );
			}

			$response = wp_remote_post(
				'http://api.ebulksms.com:8080/sendsms.json',
				array(
					'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
					'body'    => $json_data,
					'method'  => 'POST',
				)
			);
			$result = wp_remote_retrieve_body( $response );

			if ( $result ) {
				return true;
			}
		} catch ( \Exception $e ) {
			return false;
		}

		return false;
	}
}
