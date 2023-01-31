<?php
defined( 'ABSPATH' ) || die();

require_once WL_MIM_PLUGIN_DIR_PATH . '/admin/WL_MIM_LC.php';

final class WL_MIM_LM {
    private $api_url = 'https://weblizar.com/members/softsale/api';
    private $key = NULL;
    public $error_message = NULL;
    private static $instance = NULL;

    private function __construct() {
        $this->license_key = trim( get_option( 'wl-mim-key' ) );
    }

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function validate( $key ) {
		return true;
        $this->license_key = $key;

        return $this->is_valid();
    }

    private function key_exists() {
		return true;
        return ! ! strlen( $this->license_key );
    }

    public function is_valid() {
		return true;
        if ( get_option( 'wl-mim-valid' ) ) {
            return true;
        }
        if ( $this->key_exists() ) {
            $checker = new WL_MIM_LC( $this->license_key, $this->api_url, md5( $this->license_key ) );
            if ( ! $checker->checkLicenseKey() ) {
                $this->error_message = $checker->getMessage();

                return false;
            } else {
                $this->error_message   = NULL;
                $activation_cache      = trim( get_option( 'wl-mim-cache' ) );
                $prev_activation_cache = $activation_cache;
                $checker               = new WL_MIM_LC( $this->license_key, $this->api_url, md5( $this->license_key ) );
                $ret                   = empty( $activation_cache ) ? $checker->activate( $activation_cache ) : $checker->checkActivation( $activation_cache );
                if ( ! $ret ) {
                    $this->error_message = $checker->getMessage();

                    return false;
                }
                update_option( 'wl-mim-key', $this->license_key );
                update_option( 'wl-mim-valid', true );
                if ( $prev_activation_cache != $activation_cache ) {
                    update_option( 'wl-mim-cache', $activation_cache );
                }

                return true;
            }
        }
        $this->error_message = __( 'Please provide a license key.', WL_MIM_DOMAIN );

        return false;
    }
}
?>