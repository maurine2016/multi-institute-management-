<?php
defined( 'ABSPATH' ) || die();

require_once( 'inc/widgets/WL_MIM_Noticeboard_Widget.php' );

class WL_MIM_Widget {
	/* Register widgets */
	public static function register_widgets() {
		register_widget( 'WL_MIM_Noticeboard_Widget' );
	}
}
?>