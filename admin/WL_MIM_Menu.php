<?php
defined('ABSPATH') || die();
require_once WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_Helper.php';

class WL_MIM_Menu {
	/* Add menu */
	public static function create_menu() {
		if (WL_MIM_Helper::lm_valid()) {
			/* Multi-Institute menu */
			$dashboard = add_menu_page(esc_html__('Multi Institute Management', WL_MIM_DOMAIN), esc_html__('Multi Institute', WL_MIM_DOMAIN), 'wl_min_multi_institute', 'multi-institute', array(
				'WL_MIM_Menu',
				'institutes_dashboard'
			), 'dashicons-welcome-learn-more', 27);
			add_action('admin_print_styles-' . $dashboard, array('WL_MIM_Menu', 'institutes_dashboard_assets'));

			/* Multi Institute Dashboard submenu */
			$multi_dashboard_submenu = add_submenu_page('multi-institute', esc_html__('Dashboard', WL_MIM_DOMAIN), esc_html__('Dashboard', WL_MIM_DOMAIN), 'wl_min_multi_institute', 'multi-institute', array(
				'WL_MIM_Menu',
				'institutes_dashboard'
			));
			add_action('admin_print_styles-' . $multi_dashboard_submenu, array(
				'WL_MIM_Menu',
				'institutes_dashboard_assets'
			));

			/* Multi Institute submenu */
			$dashboard_submenu = add_submenu_page('multi-institute', esc_html__('Institutes', WL_MIM_DOMAIN), esc_html__('Institutes', WL_MIM_DOMAIN), 'wl_min_multi_institute', 'multi-institute-new', array(
				'WL_MIM_Menu',
				'institutes'
			));
			add_action('admin_print_styles-' . $dashboard_submenu, array('WL_MIM_Menu', 'institutes_assets'));

			/* Courses submenu */
			$dashboard_submenu = add_submenu_page('multi-institute', esc_html__('Courses', WL_MIM_DOMAIN), esc_html__('Courses', WL_MIM_DOMAIN), 'wl_min_multi_institute', 'multi-institute-courses', array(
				'WL_MIM_Menu',
				'main_courses'
			));
			add_action('admin_print_styles-' . $dashboard_submenu, array('WL_MIM_Menu', 'institutes_assets'));

			/* Users and Administrators submenu */
			$users_administrators = add_submenu_page('multi-institute', esc_html__('Administrators', WL_MIM_DOMAIN), esc_html__('Administrators', WL_MIM_DOMAIN), 'manage_options', 'multi-institute-users-administrators', array(
				'WL_MIM_Menu',
				'users_administrators'
			));
			add_action('admin_print_styles-' . $users_administrators, array(
				'WL_MIM_Menu',
				'users_administrators_assets'
			));

			/* Multi Institute Settings submenu */
			$settings = add_submenu_page('multi-institute', esc_html__('Settings', WL_MIM_DOMAIN), esc_html__('Settings', WL_MIM_DOMAIN), 'wl_min_multi_institute', 'multi-institute-settings', array(
				'WL_MIM_Menu',
				'multi_institute_settings'
			));
			add_action('admin_print_styles-' . $settings, array('WL_MIM_Menu', 'multi_institute_settings_assets'));

			/* Reset plugin submenu */
			$reset_submenu = add_submenu_page('multi-institute', esc_html__('Reset Plugin', WL_MIM_DOMAIN), esc_html__('Reset Plugin', WL_MIM_DOMAIN), 'manage_options', 'multi-institute-plugin-reset', array(
				'WL_MIM_Menu',
				'reset'
			));
			add_action('admin_print_styles-' . $reset_submenu, array(
				'WL_MIM_Menu',
				'reset_assets'
			));

			$institute_id = WL_MIM_Helper::get_current_institute_id();

			if ($institute_id) {

				/* Dashboard menu */
				$dashboard = add_menu_page(esc_html__('Institute', WL_MIM_DOMAIN), esc_html__('Institute', WL_MIM_DOMAIN), 'wl_min_manage_dashboard', 'multi-institute-management', array(
					'WL_MIM_Menu',
					'dashboard'
				), 'dashicons-welcome-learn-more', 28);
				add_action('admin_print_styles-' . $dashboard, array('WL_MIM_Menu', 'dashboard_assets'));

				/* Dashboard submenu */
				$dashboard_submenu = add_submenu_page('multi-institute-management', esc_html__('Multi Institute Management', WL_MIM_DOMAIN), esc_html__('Dashboard', WL_MIM_DOMAIN), 'wl_min_manage_dashboard', 'multi-institute-management', array(
					'WL_MIM_Menu',
					'dashboard'
				));
				add_action('admin_print_styles-' . $dashboard_submenu, array('WL_MIM_Menu', 'dashboard_assets'));

				/* Courses submenu */
				$courses = add_submenu_page('multi-institute-management', esc_html__('Courses', WL_MIM_DOMAIN), esc_html__('Courses', WL_MIM_DOMAIN), 'wl_min_manage_courses', 'multi-institute-management-courses', array(
					'WL_MIM_Menu',
					'courses'
				));
				add_action('admin_print_styles-' . $courses, array('WL_MIM_Menu', 'courses_assets'));

				/* Batches submenu */
				$batches = add_submenu_page('multi-institute-management', esc_html__('Batches', WL_MIM_DOMAIN), esc_html__('Batches', WL_MIM_DOMAIN), 'wl_min_manage_batches', 'multi-institute-management-batches', array(
					'WL_MIM_Menu',
					'batches'
				));
				add_action('admin_print_styles-' . $batches, array('WL_MIM_Menu', 'batches_assets'));

				/* Enquiries submenu */
				$enquiries = add_submenu_page('multi-institute-management', esc_html__('Enquiries', WL_MIM_DOMAIN), esc_html__('Enquiries', WL_MIM_DOMAIN), 'wl_min_manage_enquiries', 'multi-institute-management-enquiries', array(
					'WL_MIM_Menu',
					'enquiries'
				));
				add_action('admin_print_styles-' . $enquiries, array('WL_MIM_Menu', 'enquiries_assets'));

				/* Students submenu */
				$students = add_submenu_page('multi-institute-management', esc_html__('Students', WL_MIM_DOMAIN), esc_html__('Students', WL_MIM_DOMAIN), 'wl_min_manage_students', 'multi-institute-management-students', array(
					'WL_MIM_Menu',
					'students'
				));
				add_action('admin_print_styles-' . $students, array('WL_MIM_Menu', 'students_assets'));

				/* Attendance submenu */
				$attendance = add_submenu_page('multi-institute-management', esc_html__('Attendance', WL_MIM_DOMAIN), esc_html__('Attendance', WL_MIM_DOMAIN), 'wl_min_manage_attendance', 'multi-institute-management-attendance', array(
					'WL_MIM_Menu',
					'attendance'
				));
				add_action('admin_print_styles-' . $attendance, array('WL_MIM_Menu', 'attendance_assets'));

				/* Notes submenu */
				$notes = add_submenu_page('multi-institute-management', esc_html__('Study Material', WL_MIM_DOMAIN), esc_html__('Study Material', WL_MIM_DOMAIN), 'wl_min_manage_notes', 'multi-institute-management-notes', array(
					'WL_MIM_Menu',
					'notes'
				));
				add_action('admin_print_styles-' . $notes, array('WL_MIM_Menu', 'notes_assets'));

				/* Results submenu */
				$results = add_submenu_page('multi-institute-management', esc_html__('Exam Results', WL_MIM_DOMAIN), esc_html__('Exam Results', WL_MIM_DOMAIN), 'wl_min_manage_results', 'multi-institute-management-exam-results', array(
					'WL_MIM_Menu',
					'results'
				));
				add_action('admin_print_styles-' . $results, array('WL_MIM_Menu', 'results_assets'));

				/* Admin cards submenu */
				$admit_cards = add_submenu_page('multi-institute-management', esc_html__('Admit Cards', WL_MIM_DOMAIN), esc_html__('Admit Cards', WL_MIM_DOMAIN), 'wl_min_manage_admit_cards', 'multi-institute-management-exam-admit-cards', array(
					'WL_MIM_Menu',
					'admit_cards'
				));
				add_action('admin_print_styles-' . $admit_cards, array('WL_MIM_Menu', 'admit_cards_assets'));

				/* Invoices submenu */
				$invoices = add_submenu_page('multi-institute-management', esc_html__('Invoices', WL_MIM_DOMAIN), esc_html__('Invoices', WL_MIM_DOMAIN), 'wl_min_manage_fees', 'multi-institute-management-invoices', array(
					'WL_MIM_Menu',
					'invoices'
				));
				add_action('admin_print_styles-' . $invoices, array('WL_MIM_Menu', 'fees_assets'));

				/* Fees submenu */
				$fees = add_submenu_page('multi-institute-management', esc_html__('Fees', WL_MIM_DOMAIN), esc_html__('Fees', WL_MIM_DOMAIN), 'wl_min_manage_fees', 'multi-institute-management-fees', array(
					'WL_MIM_Menu',
					'fees'
				));
				add_action('admin_print_styles-' . $fees, array('WL_MIM_Menu', 'fees_assets'));

				/* Expense submenu */
				$expense = add_submenu_page('multi-institute-management', esc_html__('Expense', WL_MIM_DOMAIN), esc_html__('Expense', WL_MIM_DOMAIN), 'wl_min_manage_expense', 'multi-institute-management-expense', array(
					'WL_MIM_Menu',
					'expense'
				));
				add_action('admin_print_styles-' . $expense, array('WL_MIM_Menu', 'expense_assets'));

				/* Report submenu */
				$report = add_submenu_page('multi-institute-management', esc_html__('Report', WL_MIM_DOMAIN), esc_html__('Report', WL_MIM_DOMAIN), 'wl_min_manage_report', 'multi-institute-management-report', array(
					'WL_MIM_Menu',
					'report'
				));
				add_action('admin_print_styles-' . $report, array('WL_MIM_Menu', 'report_assets'));

				/* Notifications submenu */
				$notifications = add_submenu_page('multi-institute-management', esc_html__('Notifications', WL_MIM_DOMAIN), esc_html__('Notifications', WL_MIM_DOMAIN), 'wl_min_manage_notifications', 'multi-institute-management-notifications', array(
					'WL_MIM_Menu',
					'notifications'
				));
				add_action('admin_print_styles-' . $notifications, array('WL_MIM_Menu', 'notifications_assets'));

				/* Noticeboard submenu */
				$noticeboard = add_submenu_page('multi-institute-management', esc_html__('Noticeboard', WL_MIM_DOMAIN), esc_html__('Noticeboard', WL_MIM_DOMAIN), 'wl_min_manage_noticeboard', 'multi-institute-management-noticeboard', array(
					'WL_MIM_Menu',
					'noticeboard'
				));
				add_action('admin_print_styles-' . $noticeboard, array('WL_MIM_Menu', 'noticeboard_assets'));

				/* certificate submenu */
				$certificate = add_submenu_page('multi-institute-management', esc_html__('Certificate', WL_MIM_DOMAIN), esc_html__('Certificate', WL_MIM_DOMAIN), 'wl_min_manage_noticeboard', 'multi-institute-management-certificate', array(
					'WL_MIM_Menu',
					'certificate'
				));
				add_action('admin_print_styles-' . $certificate, array('WL_MIM_Menu', 'noticeboard_assets'));

				/* Administrators submenu */
				$administrators = add_submenu_page('multi-institute-management', esc_html__('Administrators', WL_MIM_DOMAIN), esc_html__('Administrators', WL_MIM_DOMAIN), 'wl_min_manage_administrators', 'multi-institute-management-administrators', array(
					'WL_MIM_Menu',
					'administrators'
				));
				add_action('admin_print_styles-' . $administrators, array('WL_MIM_Menu', 'administrators_assets'));

				/* Settings submenu */
				$settings = add_submenu_page('multi-institute-management', esc_html__('Settings', WL_MIM_DOMAIN), esc_html__('Settings', WL_MIM_DOMAIN), 'wl_min_manage_settings', 'multi-institute-management-settings', array(
					'WL_MIM_Menu',
					'settings'
				));
				add_action('admin_print_styles-' . $settings, array('WL_MIM_Menu', 'settings_assets'));

				if (!current_user_can('manage_options')) :
					/* Student dashboard */
					$student_dashboard = add_menu_page(esc_html__('Student Dashboard', WL_MIM_DOMAIN), esc_html__('Student Dashboard', WL_MIM_DOMAIN), 'wl_min_student', 'multi-institute-management-student-dashboard', array(
						'WL_MIM_Menu',
						'student_dashboard'
					), 'dashicons-welcome-learn-more', 28);
					add_action('admin_print_styles-' . $student_dashboard, array(
						'WL_MIM_Menu',
						'student_dashboard_assets'
					));

					/* Student dashboard submenu */
					$student_dashboard_submenu = add_submenu_page('multi-institute-management-student-dashboard', esc_html__('Student Dashboard', WL_MIM_DOMAIN), esc_html__('Dashboard', WL_MIM_DOMAIN), 'wl_min_student', 'multi-institute-management-student-dashboard', array(
						'WL_MIM_Menu',
						'student_dashboard'
					));
					add_action('admin_print_styles-' . $student_dashboard_submenu, array(
						'WL_MIM_Menu',
						'student_dashboard_assets'
					));

					/* Student noticeboard submenu */
					$student_noticeboard_submenu = add_submenu_page('multi-institute-management-student-dashboard', esc_html__('Noticeboard', WL_MIM_DOMAIN), esc_html__('Noticeboard', WL_MIM_DOMAIN), 'wl_min_student', 'multi-institute-management-student-noticeboard', array(
						'WL_MIM_Menu',
						'student_noticeboard'
					));
					add_action('admin_print_styles-' . $student_noticeboard_submenu, array(
						'WL_MIM_Menu',
						'student_noticeboard_assets'
					));

					/* Student notes submenu */
					$student_notes_submenu = add_submenu_page('multi-institute-management-student-dashboard', esc_html__('Study Material', WL_MIM_DOMAIN), esc_html__('Study Material', WL_MIM_DOMAIN), 'wl_min_student', 'multi-institute-management-student-notes', array(
						'WL_MIM_Menu',
						'student_notes'
					));
					add_action('admin_print_styles-' . $student_notes_submenu, array(
						'WL_MIM_Menu',
						'student_notes_assets'
					));

					/* Student attendance submenu */
					$student_attendance_submenu = add_submenu_page('multi-institute-management-student-dashboard', esc_html__('Attendance', WL_MIM_DOMAIN), esc_html__('Attendance', WL_MIM_DOMAIN), 'wl_min_student', 'multi-institute-management-student-attendance', array(
						'WL_MIM_Menu',
						'student_attendance'
					));
					add_action('admin_print_styles-' . $student_attendance_submenu, array(
						'WL_MIM_Menu',
						'student_attendance_assets'
					));

					/* Student admit_card submenu */
					$student_admit_card_submenu = add_submenu_page('multi-institute-management-student-dashboard', esc_html__('Admit Card', WL_MIM_DOMAIN), esc_html__('Admit Card', WL_MIM_DOMAIN), 'wl_min_student', 'multi-institute-management-student-admit-card', array(
						'WL_MIM_Menu',
						'student_admit_card'
					));
					add_action('admin_print_styles-' . $student_admit_card_submenu, array(
						'WL_MIM_Menu',
						'student_admit_card_assets'
					));

					/* Student results submenu */
					$student_results_submenu = add_submenu_page('multi-institute-management-student-dashboard', esc_html__('Exam Results', WL_MIM_DOMAIN), esc_html__('Exam Results', WL_MIM_DOMAIN), 'wl_min_student', 'multi-institute-management-student-results', array(
						'WL_MIM_Menu',
						'student_results'
					));
					add_action('admin_print_styles-' . $student_results_submenu, array(
						'WL_MIM_Menu',
						'student_results_assets'
					));
				endif;
			}

			$wl_admin_submenu = add_submenu_page('multi-institute', esc_html__('License', WL_MIM_DOMAIN), esc_html__('License', WL_MIM_DOMAIN), 'manage_options', 'multi-institute-license', array('WL_MIM_Menu', 'admin_menu'));
			add_action('admin_print_styles-' . $wl_admin_submenu, array('WL_MIM_Menu', 'admin_menu_assets'));
		} else {
			$wl_admin_menu = add_menu_page(esc_html__('Multi Institute Management', WL_MIM_DOMAIN), esc_html__('Multi Institute', WL_MIM_DOMAIN), 'manage_options', 'multi-institute-license', array('WL_MIM_Menu', 'admin_menu'), 'dashicons-welcome-learn-more', 27);
			add_action('admin_print_styles-' . $wl_admin_menu, array('WL_MIM_Menu', 'admin_menu_assets'));

			$wl_admin_submenu = add_submenu_page('multi-institute-license', esc_html__('License', WL_MIM_DOMAIN), esc_html__('License', WL_MIM_DOMAIN), 'manage_options', 'multi-institute-license', array('WL_MIM_Menu', 'admin_menu'));
			add_action('admin_print_styles-' . $wl_admin_submenu, array('WL_MIM_Menu', 'admin_menu_assets'));
		}
	}

	public static function admin_menu() {
		require_once(WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/admin_menu.php');
	}

	public static function admin_menu_assets() {
		wp_enqueue_style('wp_mim_lc', WL_MIM_PLUGIN_URL . 'assets/css/admin_menu.css');
	}

	/* Institutes reset menu/submenu callback */
	public static function reset() {
		require_once('inc/wl_im_reset.php');
	}

	/* Institutes reset menu/submenu assets */
	public static function reset_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Institutes dashboard menu/submenu callback */
	public static function institutes_dashboard() {
		require_once('inc/wl_im_institutes_dashboard.php');
	}

	/* Institutes dashboard menu/submenu assets */
	public static function institutes_dashboard_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Institutes menu/submenu callback */
	public static function institutes() {
		require_once('inc/wl_im_institutes.php');
	}

	/* Institutes menu/submenu callback */
	public static function main_courses() {
		require_once('inc/wl_im_main_courses.php');
	}

	/* Institutes menu/submenu assets */
	public static function institutes_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Dashboard menu/submenu callback */
	public static function dashboard() {
		require_once('inc/wl_im_dashboard.php');
	}

	/* Dashboard menu/submenu assets */
	public static function dashboard_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Courses menu callback */
	public static function courses() {
		require_once('inc/wl_im_courses.php');
	}

	/* Courses menu assets */
	public static function courses_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Batches menu callback */
	public static function batches() {
		require_once('inc/wl_im_batches.php');
	}

	/* Batches menu assets */
	public static function batches_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Enquiries menu callback */
	public static function enquiries() {
		require_once('inc/wl_im_enquiries.php');
	}

	/* Enquiries menu assets */
	public static function enquiries_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Students menu callback */
	public static function students() {
		require_once('inc/wl_im_students.php');
	}

	/* Students menu assets */
	public static function students_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_datatable_export_assets();
		self::enqueue_filters_assets();
		self::enqueue_custom_assets();
	}

	/* Attendance menu callback */
	public static function attendance() {
		require_once('inc/wl_im_attendance.php');
	}

	/* Attendance menu assets */
	public static function attendance_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Notes menu callback */
	public static function notes() {
		require_once('inc/wl_im_notes.php');
	}

	/* Notes menu assets */
	public static function notes_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Invoices menu callback */
	public static function invoices() {
		require_once('inc/wl_im_invoices.php');
	}

	/* Fees menu callback */
	public static function fees() {
		require_once('inc/wl_im_fees.php');
	}

	/* Fees menu assets */
	public static function fees_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Admit cards menu callback */
	public static function admit_cards() {
		require_once('inc/wl_im_admit_cards.php');
	}

	/* Admit cards menu assets */
	public static function admit_cards_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Results menu callback */
	public static function results() {
		require_once('inc/wl_im_results.php');
	}

	/* Results menu assets */
	public static function results_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Expense menu callback */
	public static function expense() {
		require_once('inc/wl_im_expense.php');
	}

	/* Expense menu assets */
	public static function expense_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Report menu callback */
	public static function report() {
		require_once('inc/wl_im_report.php');
	}

	/* Report menu assets */
	public static function report_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_datatable_export_assets();
		self::enqueue_custom_assets();
	}

	/* Notifications menu callback */
	public static function notifications() {
		require_once('inc/wl_im_notifications.php');
	}

	/* Notifications menu assets */
	public static function notifications_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Noticeboard menu callback */
	public static function noticeboard() {
		require_once('inc/wl_im_noticeboard.php');
	}

	public static function certificate() {
		require_once('inc/certificate/route.php');
	}

	/* Noticeboard menu assets */
	public static function noticeboard_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Administrators menu callback */
	public static function administrators() {
		require_once('inc/wl_im_administrators.php');
	}

	/* Administrators menu assets */
	public static function administrators_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Users and administrators menu callback */
	public static function users_administrators() {
		require_once('inc/wl_im_users_administrators.php');
	}

	/* Users and administrators menu assets */
	public static function users_administrators_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Multi institute settings menu callback */
	public static function multi_institute_settings() {
		require_once('inc/wl_im_multi_institute_settings.php');
	}

	/* Multi institute settings menu assets */
	public static function multi_institute_settings_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Settings menu callback */
	public static function settings() {
		require_once('inc/wl_im_settings.php');
	}

	/* Settings menu assets */
	public static function settings_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Student dashboard menu/submenu callback */
	public static function student_dashboard() {
		require_once('inc/wl_im_student_dashboard.php');
	}

	/* Student dashboard menu/submenu assets */
	public static function student_dashboard_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
		wp_enqueue_script('razorpay-checkout', 'https://checkout.razorpay.com/v1/checkout.js', array(), true, true);
		wp_enqueue_script('paystack-checkout', 'https://js.paystack.co/v1/inline.js', array(), true, true);
		wp_enqueue_script('stripe-checkout', 'https://checkout.stripe.com/checkout.js', array(), true, true);
		wp_enqueue_script('instamojo-checkout', 'https://js.instamojo.com/v1/checkout.js', array(), true, true);
	}

	/* Student noticeboard menu/submenu callback */
	public static function student_noticeboard() {
		require_once('inc/wl_im_student_noticeboard.php');
	}

	/* Student noticeboard menu/submenu assets */
	public static function student_noticeboard_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Student attendance menu/submenu callback */
	public static function student_attendance() {
		require_once('inc/wl_im_student_attendance.php');
	}

	/* Student attendance menu/submenu assets */
	public static function student_attendance_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Admit card menu callback */
	public static function student_admit_card() {
		require_once('inc/wl_im_student_admit_card.php');
	}

	/* Admit card menu assets */
	public static function student_admit_card_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Student results menu/submenu callback */
	public static function student_results() {
		require_once('inc/wl_im_student_results.php');
	}

	/* Student results menu/submenu assets */
	public static function student_results_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Student notes menu/submenu callback */
	public static function student_notes() {
		require_once('inc/wl_im_student_notes.php');
	}

	/* Student notes menu/submenu assets */
	public static function student_notes_assets() {
		self::enqueue_libraries();
		self::enqueue_datatable_assets();
		self::enqueue_custom_assets();
	}

	/* Enqueue third party libraties */
	public static function enqueue_libraries() {
		/* Enqueue styles */
		wp_enqueue_style('bootstrap', WL_MIM_PLUGIN_URL . 'admin/css/bootstrap.min.css');
		wp_enqueue_style('font-awesome', WL_MIM_PLUGIN_URL . 'assets/css/font-awesome.min.css');
		wp_enqueue_style('bootstrap-select', WL_MIM_PLUGIN_URL . 'assets/css/bootstrap-select.min.css');
		wp_enqueue_style('bootstrap-datetimepicker', WL_MIM_PLUGIN_URL . 'assets/css/bootstrap-datetimepicker.min.css');
		wp_enqueue_style('toastr', WL_MIM_PLUGIN_URL . 'assets/css/toastr.min.css');
		wp_enqueue_style('jquery-confirm', WL_MIM_PLUGIN_URL . 'admin/css/jquery-confirm.min.css');

		/* Enqueue scripts */
		wp_enqueue_script('jquery-form');
		wp_enqueue_script('popper', WL_MIM_PLUGIN_URL . 'assets/js/popper.min.js', array('jquery'), true, true);
		wp_enqueue_script('bootstrap', WL_MIM_PLUGIN_URL . 'assets/js/bootstrap.min.js', array('jquery'), true, true);
		wp_enqueue_script('bootstrap-select', WL_MIM_PLUGIN_URL . 'assets/js/bootstrap-select.min.js', array('bootstrap'), true, true);
		wp_enqueue_script('moment', WL_MIM_PLUGIN_URL . 'assets/js/moment.min.js', array(), true, true);
		wp_enqueue_script('bootstrap-datetimepicker', WL_MIM_PLUGIN_URL . 'assets/js/bootstrap-datetimepicker.min.js', array('bootstrap'), true, true);
		wp_enqueue_script('toastr', WL_MIM_PLUGIN_URL . 'assets/js/toastr.min.js', array('jquery'), true, true);
		wp_enqueue_script('jquery-confirm', WL_MIM_PLUGIN_URL . 'admin/js/jquery-confirm.min.js', array('jquery'), true, true);
		wp_enqueue_script('jquery-print', WL_MIM_PLUGIN_URL . 'assets/js/jQuery.print.js', array('jquery'), true, true);
		wp_enqueue_script('alpine', WL_MIM_PLUGIN_URL . 'assets/js/alpine.min.js', array(), true, true);
	}

	/* Enqueue datatable assets */
	public static function enqueue_datatable_assets() {
		/* Enqueue styles */
		wp_enqueue_style('dataTables-bootstrap4', WL_MIM_PLUGIN_URL . 'admin/css/dataTables.bootstrap4.min.css');
		wp_enqueue_style('responsive-bootstrap4', WL_MIM_PLUGIN_URL . 'admin/css/responsive.bootstrap4.min.css');

		/* Enqueue scripts */
		wp_enqueue_script('jquery-dataTables', WL_MIM_PLUGIN_URL . 'admin/js/jquery.dataTables.min.js', array('jquery'), true, true);
		wp_enqueue_script('wl-mim-dataTables-select-js', WL_MIM_PLUGIN_URL . 'admin/js/dataTables.select.min.js', array('jquery'), true, true);
		wp_enqueue_script('dataTables-bootstrap4', WL_MIM_PLUGIN_URL . 'admin/js/dataTables.bootstrap4.min.js', array('jquery'), true, true);
		wp_enqueue_script('dataTables-responsive', WL_MIM_PLUGIN_URL . 'admin/js/dataTables.responsive.min.js', array('jquery'), true, true);
		wp_enqueue_script('responsive-bootstrap4', WL_MIM_PLUGIN_URL . 'admin/js/responsive.bootstrap4.min.js', array('jquery'), true, true);
	}

	/* Enqueue datatable export assets */
	public static function enqueue_datatable_export_assets() {
		/* Enqueue styles */
		wp_enqueue_style('wl-mim-jquery-dataTables', WL_MIM_PLUGIN_URL . 'admin/css/jquery.dataTables.min.css');
		wp_enqueue_style('wl-mim-buttons-bootstrap4', WL_MIM_PLUGIN_URL . 'admin/css/buttons.bootstrap4.min.css');

		/* Enqueue scripts */
		wp_enqueue_script('wl-mim-dataTables-buttons-js', WL_MIM_PLUGIN_URL . 'admin/js/dataTables.buttons.min.js', array('jquery'), true, true);
		wp_enqueue_script('wl-mim-buttons-bootstrap4-js', WL_MIM_PLUGIN_URL . 'admin/js/buttons.bootstrap4.min.js', array('jquery'), true, true);
		wp_enqueue_script('wl-mim-jszip-js', WL_MIM_PLUGIN_URL . 'admin/js/jszip.min.js', array('jquery'), true, true);
		wp_enqueue_script('wl-mim-pdfmake-js', WL_MIM_PLUGIN_URL . 'admin/js/pdfmake.min.js', array('jquery'), true, true);
		wp_enqueue_script('wl-mim-vfs_fonts-js', WL_MIM_PLUGIN_URL . 'admin/js/vfs_fonts.js', array('jquery'), true, true);
		wp_enqueue_script('wl-mim-buttons-html5-js', WL_MIM_PLUGIN_URL . 'admin/js/buttons.html5.min.js', array('wl-mim-jszip-js'), true, true);
		wp_enqueue_script('wl-mim-buttons-print-js', WL_MIM_PLUGIN_URL . 'admin/js/buttons.print.min.js', array('jquery'), true, true);
		wp_enqueue_script('wl-mim-buttons-colVis-js', WL_MIM_PLUGIN_URL . 'admin/js/buttons.colVis.min.js', array('jquery'), true, true);
		wp_enqueue_script('wl-mim-datatable-export-js', WL_MIM_PLUGIN_URL . 'admin/js/wl-mim-datatable-export.js', array('jquery'), true, true);
		wp_enqueue_script('wl-mim-dataTables-select-js', WL_MIM_PLUGIN_URL . 'admin/js/dataTables.select.min.js', array('jquery'), true, true);
	}

	/* Enqueue filters assets */
	public static function enqueue_filters_assets() {
		/* Enqueue scripts */
		wp_enqueue_script('wl-mim-filters-js', WL_MIM_PLUGIN_URL . 'admin/js/wl-mim-filters.js', array('jquery'), true, true);
	}

	/* Enqueue custom assets */
	public static function enqueue_custom_assets() {
		/* Enqueue styles */
		wp_enqueue_style('wl-mim-style', WL_MIM_PLUGIN_URL . 'admin/css/wl-mim-style.css', array(), '3.9', 'all');

		/* Enqueue scripts */
		wp_enqueue_script('wl-mim-script-js', WL_MIM_PLUGIN_URL . 'admin/js/wl-mim-script.js', array('jquery'), '3.9', true);
		wp_enqueue_script('wl-mim-ajax-js', WL_MIM_PLUGIN_URL . 'admin/js/wl-mim-ajax.js', array('jquery'), '3.9', true);
		wp_localize_script('wl-mim-ajax-js', 'WLIMAjax', array('security' => wp_create_nonce('wl-ima')));
		wp_localize_script('wl-mim-ajax-js', 'WL_MIM_PLUGIN_URL', array(WL_MIM_PLUGIN_URL));
		wp_localize_script('wl-mim-ajax-js', 'WL_MIM_ADMIN_URL', array(admin_url()));
	}
}
