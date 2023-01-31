<?php
defined( 'ABSPATH' ) || die();

class WL_MIM_Language {
	public static function load_translation() {
		load_plugin_textdomain( WL_MIM_DOMAIN, false, basename( WL_MIM_PLUGIN_DIR_PATH ) . '/languages' );
	}
}
?>