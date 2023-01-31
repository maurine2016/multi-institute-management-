<?php
/*
 * Plugin Name: Multi Institute Management
 * Plugin URI: https://weblizar.com
 * Description: Manage multiple institutes directly from your WordPress website. Assign admins to manage individual institute.
 * Version: 6.9
 * Author: Weblizar
 * Author URI: https://weblizar.com
 * Text Domain: WL-MIM
*/

defined('ABSPATH') || die();

if (!defined('WL_MIM_DOMAIN')) {
	define('WL_MIM_DOMAIN', 'WL-MIM');
}

if (!defined('WL_MIM_PLUGIN_URL')) {
	define('WL_MIM_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('WL_MIM_PLUGIN_DIR_PATH')) {
	define('WL_MIM_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
}

define('WL_MIM_MENU_CERTIFICATES', 'multi-institute-management-certificate');

define('WL_MIM_WEBLIZAR_PLUGIN_URL', 'https://weblizar.com/plugins/multi-institute-management/');
define('WL_MIM_VERSION', '6.9');

include 'wlim-update-checker.php';

final class WL_MIM_InstituteManagementAdvanced {
	private static $instance = null;

	private function __construct() {
		$this->initialize_hooks();
		$this->setup_database();
	}

	public static function get_instance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function initialize_hooks() {
		if (is_admin()) {
			require_once('admin/admin.php');
		}
		require_once('public/public.php');
	}

	private function setup_database() {
		require_once('admin/WL_MIM_Database.php');
		register_activation_hook(__FILE__, array('WL_MIM_Database', 'activation'));
		register_deactivation_hook(__FILE__, array('WL_MIM_Database', 'deactivation'));
		register_uninstall_hook(__FILE__, array('WL_MIM_Database', 'deactivation'));
	}
}
WL_MIM_InstituteManagementAdvanced::get_instance();
