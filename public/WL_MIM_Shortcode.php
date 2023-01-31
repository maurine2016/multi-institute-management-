<?php
defined( 'ABSPATH' ) || die();

class WL_MIM_Shortcode {
	public static function create_enquiry_form( $attr ) {
		ob_start();
		require_once( 'inc/wl_im_enquiry_form.php' );
		return ob_get_clean();
	}

	public static function create_id_card_form( $attr ) {
		ob_start();
		require_once( 'inc/wl_im_id_card_form.php' );
		return ob_get_clean();
	}

	public static function create_admit_card_form( $attr ) {
		ob_start();
		require_once( 'inc/wl_im_admit_card_form.php' );
		return ob_get_clean();
	}

	public static function noticeboard( $attr ) {
		ob_start();
		require_once( 'inc/wl_im_noticeboard.php' );
		return ob_get_clean();
	}

	public static function create_certificate_form( $attr ) {
		ob_start();
		require_once( 'inc/wl_im_certificate_form.php' );
		return ob_get_clean();
	}

	public static function create_result_form( $attr ) {
		ob_start();
		require_once( 'inc/wl_im_result_form.php' );
		return ob_get_clean();
	}

	public static function create_results_by_name_form( $attr ) {
		ob_start();
		require_once( 'inc/wl_im_results_by_name_form.php' );
		return ob_get_clean();
	}

	public static function certificate_verify_page( $attr ) {
		ob_start();
		require_once( 'inc/wl_im_certificate_verify_page.php' );
		return ob_get_clean();
	}

	public static function shortcode_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) ) {
			if (
				has_shortcode( $post->post_content, 'institute_admission_enquiry_form' ) ||
				has_shortcode( $post->post_content, 'institute_exam_result' ) ||
				has_shortcode( $post->post_content, 'institute_exam_results_by_name' ) ||
				has_shortcode( $post->post_content, 'institute_admit_card' ) ||
				has_shortcode( $post->post_content, 'institute_certificate' ) ||
				has_shortcode( $post->post_content, 'institute_id_card' ) || 
				has_shortcode( $post->post_content, 'certificate_verify' )
			) {
				/* Enqueue styles */
				wp_enqueue_style( 'wlmim-bootstrap', WL_MIM_PLUGIN_URL . 'public/css/bootstrap.min.css' );
				wp_enqueue_style( 'font-awesome', WL_MIM_PLUGIN_URL . 'assets/css/font-awesome.min.css' );
				wp_enqueue_style( 'toastr', WL_MIM_PLUGIN_URL . 'assets/css/toastr.min.css' );
				wp_enqueue_style( 'bootstrap-datetimepicker', WL_MIM_PLUGIN_URL . 'assets/css/bootstrap-datetimepicker.min.css' );
				wp_enqueue_style( 'wl-mim-style', WL_MIM_PLUGIN_URL . 'public/css/wl-mim-style.css', array(), '3.9', 'all' );

				/* Enqueue scripts */
				wp_enqueue_script( 'jquery-form' );
				wp_enqueue_script( 'popper', WL_MIM_PLUGIN_URL . 'assets/js/popper.min.js', array( 'jquery' ), true, true );
				wp_enqueue_script( 'wlmim-bootstrap', WL_MIM_PLUGIN_URL . 'assets/js/bootstrap.min.js', array( 'jquery' ), true, true );
				wp_enqueue_script( 'toastr', WL_MIM_PLUGIN_URL . 'assets/js/toastr.min.js', array( 'jquery' ), true, true );
				wp_enqueue_script( 'moment', WL_MIM_PLUGIN_URL . 'assets/js/moment.min.js', array(), true, true );
				wp_enqueue_script( 'bootstrap-datetimepicker', WL_MIM_PLUGIN_URL . 'assets/js/bootstrap-datetimepicker.min.js', array( 'wlmim-bootstrap' ), true, true );
				wp_enqueue_script( 'jquery-print', WL_MIM_PLUGIN_URL . 'assets/js/jQuery.print.js', array( 'jquery' ), true, true );
				wp_enqueue_script( 'wl-mim-script-js', WL_MIM_PLUGIN_URL . 'public/js/wl-mim-script.js', array( 'jquery' ), '3.9', true );
				wp_enqueue_script( 'wl-mim-ajax-js', WL_MIM_PLUGIN_URL . 'public/js/wl-mim-ajax.js', array( 'jquery' ), '3.9', true );
				wp_localize_script( 'wl-mim-ajax-js', 'WLIMAjax', array( 'security' => wp_create_nonce( 'wl-ima' ) ) );
				wp_localize_script( 'wl-mim-ajax-js', 'wlimajaxurl', array(esc_url( admin_url( 'admin-ajax.php' ) ) ));
			}
		}
	}
}
