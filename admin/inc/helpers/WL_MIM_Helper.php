<?php
defined('ABSPATH') || die();

require_once(WL_MIM_PLUGIN_DIR_PATH . 'admin/WL_MIM_LM.php');
require_once(WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php');
require_once(WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SMSHelper.php');

class WL_MIM_Helper {
	public static $core_capability = 'manage_options';

	/* Get capabilities */
	public static function get_capabilities() {
		return array(
			'wl_min_manage_dashboard'      => esc_html__('Manage Dashboard', WL_MIM_DOMAIN),
			'wl_min_manage_courses'        => esc_html__('Manage Courses', WL_MIM_DOMAIN),
			'wl_min_manage_batches'        => esc_html__('Manage Batches', WL_MIM_DOMAIN),
			'wl_min_manage_enquiries'      => esc_html__('Manage Enquiries', WL_MIM_DOMAIN),
			'wl_min_manage_students'       => esc_html__('Manage Students', WL_MIM_DOMAIN),
			'wl_min_manage_attendance'     => esc_html__('Manage Attendance', WL_MIM_DOMAIN),
			'wl_min_manage_notes'          => esc_html__('Manage Notes', WL_MIM_DOMAIN),
			'wl_min_manage_expense'        => esc_html__('Manage Expense', WL_MIM_DOMAIN),
			'wl_min_manage_report'         => esc_html__('Manage Report', WL_MIM_DOMAIN),
			'wl_min_manage_admit_cards'    => esc_html__('Manage Admit Cards', WL_MIM_DOMAIN),
			'wl_min_manage_results'        => esc_html__('Manage Results', WL_MIM_DOMAIN),
			'wl_min_manage_fees'           => esc_html__('Manage Fees', WL_MIM_DOMAIN),
			'wl_min_manage_notifications'  => esc_html__('Manage Notifications', WL_MIM_DOMAIN),
			'wl_min_manage_noticeboard'    => esc_html__('Manage Noticeboard', WL_MIM_DOMAIN),
			'wl_min_manage_administrators' => esc_html__('Manage Administrators', WL_MIM_DOMAIN),
			'wl_min_manage_settings'       => esc_html__('Manage Settings', WL_MIM_DOMAIN)
		);
	}

	/* Get student capability */
	public static function get_student_capability() {
		return 'wl_min_student';
	}

	/* Get multi institute capability */
	public static function get_multi_institute_capability() {
		return 'wl_min_multi_institute';
	}

	/* Assign custom capabilities to admin */
	public static function assign_capabilities() {
		$roles = get_editable_roles();
		foreach ($GLOBALS['wp_roles']->role_objects as $key => $role) {
			if (isset($roles[$key]) && $role->has_cap(self::$core_capability)) {
				foreach (self::get_capabilities() as $capability_key => $capability_value) {
					$role->add_cap($capability_key);
				}
				$role->add_cap(self::get_multi_institute_capability());
			}
		}
	}

	/* Remove custom capabilities of admin */
	public static function remove_capabilities() {
		$roles = get_editable_roles();
		foreach ($GLOBALS['wp_roles']->role_objects as $key => $role) {
			if (isset($roles[$key]) && $role->has_cap(self::$core_capability)) {
				foreach (self::get_capabilities() as $capability_key => $capability_value) {
					$role->remove_cap($capability_key);
				}
				$role->remove_cap(self::get_multi_institute_capability());
			}
		}
	}

	/* Get duration in */
	public static function get_duration_in() {
		return array(
			esc_html__('Days', WL_MIM_DOMAIN),
			esc_html__('Months', WL_MIM_DOMAIN),
			esc_html__('Years', WL_MIM_DOMAIN)
		);
	}

	public static function get_period_in() {
		return array(
			'one-time' => esc_html__('One-time', WL_MIM_DOMAIN),
			'monthly'  => esc_html__('Monthly', WL_MIM_DOMAIN)
		);
	}

	/* Get notification by list */
	public static function get_notification_by_list() {
		return array(
			'by-batch'               => esc_html__('By Batch', WL_MIM_DOMAIN),
			'by-course'              => esc_html__('By Course', WL_MIM_DOMAIN),
			'by-pending-fees'        => esc_html__('By Pending Fees', WL_MIM_DOMAIN),
			'by-active-students'     => esc_html__('By Active Students', WL_MIM_DOMAIN),
			'by-inactive-students'   => esc_html__('By Inactive Students', WL_MIM_DOMAIN),
			'by-individual-students' => esc_html__('By Individual Student', WL_MIM_DOMAIN)
		);
	}

	/* Get overall report by list */
	public static function get_report_by_list() {
		return array(
			'student-registrations' => esc_html__('Student Registrations', WL_MIM_DOMAIN),
			'current-students'      => esc_html__('Current Students', WL_MIM_DOMAIN),
			'students-drop-out'     => esc_html__('Students Drop-out (Inactive)', WL_MIM_DOMAIN),
			'fees-collection'       => esc_html__('Fees Collection', WL_MIM_DOMAIN),
			'outstanding-fees'      => esc_html__('Outstanding Fees', WL_MIM_DOMAIN),
			'pending-fees-by-batch' => esc_html__('Pending Fees By Batch', WL_MIM_DOMAIN),
			'attendance-by-batch'   => esc_html__('Attendance By Batch', WL_MIM_DOMAIN),
			'expense'               => esc_html__('Expense', WL_MIM_DOMAIN),
			'enquiries'             => esc_html__('Enquiries', WL_MIM_DOMAIN)
		);
	}

	/* Get report period */
	public static function get_report_period() {
		return array(
			'today'      => esc_html__('Today', WL_MIM_DOMAIN),
			'yesterday'  => esc_html__('Yesterday', WL_MIM_DOMAIN),
			'this-week'  => esc_html__('This Week', WL_MIM_DOMAIN),
			'this-month' => esc_html__('This Month', WL_MIM_DOMAIN),
			'this-year'  => esc_html__('This Year', WL_MIM_DOMAIN),
			'last-year'  => esc_html__('Last Year', WL_MIM_DOMAIN)
		);
	}

	/* Get number of course months */
	public static function get_course_months_count($duration, $duration_in) {
		$course_duration_in_month = 1;
		if ($duration_in == 'Months') {
			$course_duration_in_month = intval($duration);
		} elseif ($duration_in == 'Days') {
			$course_duration_in_month = floor($duration / 30);
		} elseif ($duration_in == 'Years') {
			$course_duration_in_month = intval($duration * 12);
		}
		if ($course_duration_in_month < 1) {
			return 0;
		}
		return $course_duration_in_month;
	}

	/* Get batch months */
	public static function get_batch_months($batch_start_date, $batch_end_date) {
		$ts1 = strtotime($batch_start_date);
		$ts2 = strtotime($batch_end_date);

		$year1 = date('Y', $ts1);
		$year2 = date('Y', $ts2);

		$month1 = date('m', $ts1);
		$month2 = date('m', $ts2);

		$diff = (($year2 - $year1) * 12) + ($month2 - $month1);
		return $diff;
	}

	/* Get active categories of institute */
	public static function get_active_categories_institute($institute_id) {
		global $wpdb;

		return $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}wl_min_course_categories WHERE is_active = 1 AND institute_id = $institute_id ORDER BY name");
	}

	/* Get active courses */
	public static function get_active_courses() {
		global $wpdb;
		$institute_id = self::get_current_institute_id();

		return $wpdb->get_results("SELECT id, course_name, fees, course_code FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY course_name");
	}

	/* Get courses */
	public static function get_courses($institute_id = '') {
		global $wpdb;
		if (!$institute_id) {
			$institute_id = self::get_current_institute_id();
		}

		return $wpdb->get_results("SELECT id, course_name, fees, course_code, is_active FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY course_name");
	}

	/* Get courses ids */
	public static function get_courses_ids($institute_id = '') {
		global $wpdb;
		if (!$institute_id) {
			$institute_id = self::get_current_institute_id();
		}

		return $wpdb->get_col("SELECT id FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY course_name");
	}

	/* Get active courses of institute */
	public static function get_active_courses_institute($institute_id) {
		global $wpdb;

		return $wpdb->get_results("SELECT id, course_name, fees, course_code FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY course_name", OBJECT_K);
	}

	/* Get active batches of institute */
	public static function get_active_batches_institute($institute_id) {
		global $wpdb;

		return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id");
	}

	/* Get institutes */
	public static function get_institutes() {
		global $wpdb;

		return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_institutes ORDER BY name");
	}

	/* Get main courses */
	public static function get_main_courses() {
		global $wpdb;

		return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_main_courses ORDER BY course_name");
	}

	/* Get active institutes */
	public static function get_active_institutes() {
		global $wpdb;

		return $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}wl_min_institutes WHERE is_active = '1' ORDER BY name");
	}

	/* Get current institute id */
	public static function get_current_institute_id() {
		global $wpdb;
		$institute_id = get_user_meta(get_current_user_id(), 'wlim_institute_id', true);
		if ($institute_id) {
			if ($institute_id_from_cache = wp_cache_get('mim_current_institute_id')) {
				return $institute_id_from_cache;
			}
			$institute = $wpdb->get_row("SELECT id FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id");
			if ($institute) {
				wp_cache_add('mim_current_institute_id', $institute->id);
				return $institute->id;
			}
		}

		return false;
	}

	public static function get_certificate($institute_id, $id) {
		global $wpdb;
		$certificate = $wpdb->get_row($wpdb->prepare('SELECT cf.ID, cf.image_id FROM ' . "{$wpdb->prefix}" . 'wl_min_certificate' . ' as cf
		WHERE cf.institute_id = %d AND cf.ID = %d', $institute_id, $id));
		return $certificate;
	}

	public static function get_image_mime() {
		return array('image/jpg', 'image/jpeg', 'image/png');
	}

	public static function get_csv_mime() {
		return array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
	}

	public static function get_attachment_mime() {
		return array('image/jpg', 'image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/x-rar-compressed', 'application/octet-stream', 'application/zip', 'application/octet-stream', 'application/x-zip-compressed', 'multipart/x-zip', 'video/x-flv', 'video/mp4', 'application/x-mpegURL', 'video/MP2T', 'video/3gpp', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv');
	}

	public static function is_valid_file($file, $type = 'attachment') {
		$get_mime = 'get_' . $type . '_mime';

		if (extension_loaded('fileinfo')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime  = finfo_file($finfo, $file['tmp_name']);
			finfo_close($finfo);
		} else {
			$mime = $file['type'];
		}

		if (!in_array($mime, self::$get_mime())) {
			return false;
		}

		return true;
	}

	/* Get institute registration number */
	public static function get_institute_registration_number($institute_id) {
		global $wpdb;
		$institute = $wpdb->get_row("SELECT registration_number FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id");
		if ($institute) {
			return $institute->registration_number;
		}

		return '';
	}

	/* Get current institute status */
	public static function get_current_institute_status() {
		global $wpdb;
		$institute_id = get_user_meta(get_current_user_id(), 'wlim_institute_id', true);
		if ($institute_id) {
			$institute = $wpdb->get_row("SELECT is_active FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id");
			if ($institute) {
				return $institute->is_active;
			}
		}

		return false;
	}

	/* Get current institute name */
	public static function get_current_institute_name() {
		global $wpdb;
		$institute_id = get_user_meta(get_current_user_id(), 'wlim_institute_id', true);
		$institute    = $wpdb->get_row("SELECT id, name FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id");
		if ($institute) {
			return $institute->name;
		}

		return false;
	}

	/* Get students */
	public static function get_students() {
		global $wpdb;
		$institute_id = self::get_current_institute_id();

		return $wpdb->get_results("SELECT id, first_name, last_name, enrollment_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY first_name, last_name, id DESC");
	}

	/* Get active students */
	public static function get_active_students() {
		global $wpdb;
		$institute_id = self::get_current_institute_id();

		return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY first_name, last_name, id DESC");
	}

	/* Get custom fields */
	public static function get_active_custom_fields() {
		global $wpdb;
		$institute_id = self::get_current_institute_id();

		return $wpdb->get_results("SELECT field_name FROM {$wpdb->prefix}wl_min_custom_fields WHERE is_active = 1 AND institute_id = $institute_id");
	}

	/* Get custom fields of institute */
	public static function get_active_custom_fields_institute($institute_id) {
		global $wpdb;

		return $wpdb->get_results("SELECT field_name FROM {$wpdb->prefix}wl_min_custom_fields WHERE is_active = 1 AND institute_id = $institute_id");
	}

	/* Get fee types */
	public static function get_active_fee_types() {
		global $wpdb;
		$institute_id = self::get_current_institute_id();

		return $wpdb->get_results("SELECT fee_type, amount, periods FROM {$wpdb->prefix}wl_min_fee_types WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id");
	}

	/* Get fees total */
	public static function get_fees_total($fees) {
		if (count($fees)) {
			$total = array_sum($fees);

			return number_format(max(floatval($total), 0), 2, '.', '');
		}

		return number_format(0, 2, '.', '');
	}

	/* Get batch status */
	public static function get_batch_status($start_date, $end_date) {
		if (date('Y-m-d') < date('Y-m-d', strtotime($start_date))) {
			return '<strong class="text-danger">' . esc_html__('To Be Started', WL_MIM_DOMAIN) . '</strong>';
		} else {
			if (self::is_current_batch($start_date, $end_date)) {
				return '<strong class="text-primary">' . esc_html__('Current Batch', WL_MIM_DOMAIN) . '</strong>';
			} else {
				return '<strong class="text-success">' . esc_html__('Batch Ended', WL_MIM_DOMAIN) . '</strong>';
			}
		}
	}

	/* Is current batch */
	public static function is_current_batch($start_date, $end_date) {
		$today      = date('Y-m-d');
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date   = date('Y-m-d', strtotime($end_date));

		if (($today >= $start_date) && ($today < $end_date)) {
			return true;
		} else {
			return false;
		}
	}

	/* Is batch ended */
	public static function is_batch_ended($start_date, $end_date) {
		$today    = date('Y-m-d');
		$end_date = date('Y-m-d', strtotime($end_date));

		if ($today >= $end_date) {
			return true;
		} else {
			return false;
		}
	}

	/* Get payment methods */
	public static function get_payment_methods() {
		return array(
			'razorpay' => esc_html__('Razorpay', WL_MIM_DOMAIN),
			'paystack' => esc_html__('Paystack', WL_MIM_DOMAIN),
			'paypal'   => esc_html__('PayPal', WL_MIM_DOMAIN),
			'stripe'   => esc_html__('Stripe', WL_MIM_DOMAIN),
			'instamojo'   => esc_html__('Instamojo', WL_MIM_DOMAIN)
		);
	}

	/* Get enquiry ID */
	public static function get_enquiry_id($id) {
		return "E" . ($id + 10000);
	}

	/* Get invoice number */
	public static function get_invoice($id) {
		return ($id + 10000);
	}

	/* Get valid gender data */
	public static function get_gender_data() {
		return array('male', 'female');
	}

	/* Get sms providers */
	public static function get_sms_providers() {
		return array(
			'nexmo'         => esc_html__('Nexmo', WL_MIM_DOMAIN),
			'smsstriker'    => esc_html__('SMS Striker', WL_MIM_DOMAIN),
			'pointsms'      => esc_html__('Intechno Point', WL_MIM_DOMAIN),
			'msgclub'       => esc_html__('Intechno Msg', WL_MIM_DOMAIN),
			'textlocal'     => esc_html__('Textlocal', WL_MIM_DOMAIN),
			'ebulksms'      => esc_html__('EBulkSMS', WL_MIM_DOMAIN),
			'auurumdigital' => esc_html__('auurumdigital', WL_MIM_DOMAIN),
		);
	}

	/* Get sms templates */
	public static function get_sms_templates() {
		return array(
			'enquiry_received'          => esc_html__('Enquiry received confirmation to inquisitor', WL_MIM_DOMAIN),
			'enquiry_received_to_admin' => esc_html__('Enquiry received confirmation to admin', WL_MIM_DOMAIN),
			'student_registered'        => esc_html__('Student registered confirmation to student', WL_MIM_DOMAIN),
			'fees_submitted'            => esc_html__('Fees submitted confirmation to student', WL_MIM_DOMAIN),
			'student_birthday'          => esc_html__('Birthday message to student', WL_MIM_DOMAIN),
		);
	}

	/* Get valid enquiry action data */
	public static function get_enquiry_action_data() {
		return array('delete_enquiry', 'mark_enquiry_inactive');
	}

	/* Get id_proof file types */
	public static function get_id_proof_file_types() {
		return array('image/jpg', 'image/jpeg', 'image/png', 'application/pdf');
	}

	/* Get image file types */
	public static function get_image_file_types() {
		return array('image/jpg', 'image/jpeg', 'image/png');
	}

	/* Get Notice attachment file types */
	public static function get_notice_attachment_file_types() {
		return array(
			'image/jpg',
			'image/jpeg',
			'image/png',
			'application/pdf',
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.ms-powerpoint',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'application/x-rar-compressed',
			'application/octet-stream',
			'application/zip',
			'application/octet-stream',
			'application/x-zip-compressed',
			'multipart/x-zip',
			'video/x-flv',
			'video/mp4',
			'application/x-mpegURL',
			'video/MP2T',
			'video/3gpp',
			'video/quicktime',
			'video/x-msvideo',
			'video/x-ms-wmv'
		);
	}

	/* Get enrollment ID with prefix */
	public static function get_enrollment_id_with_prefix($id, $prefix) {
		if (!$prefix) {
			$prefix = 'EN';
		}
		return $prefix . ($id);
	}

	/* Get student ID with prefix */
	public static function get_student_id_with_prefix($enrollment_id, $prefix) {
		if (!$prefix) {
			$prefix = 'EN';
		}

		// return intval(substr($enrollment_id, strlen($prefix), strlen($enrollment_id))) - 10000;
		return intval(substr($enrollment_id, strlen($prefix), strlen($enrollment_id)));
	}

	/* Get form number */
	public static function get_form_number($id) {
		return ($id + 10000);
	}

	/* Get certificate number */
	public static function get_certificate_number($id) {
		return ($id + 10000);
	}

	/* Get receipt number */
	public static function get_receipt($id) {
		$prefix = get_option("institute_advanced_settings")['receipt_number_prefix'];
		if (!$prefix) {
			$prefix = 'R';
		}

		return $prefix . ($id + 10000);
	}

	/* Get receipt number with prefix */
	public static function get_receipt_with_prefix($id, $prefix) {
		if (!$prefix) {
			$prefix = 'R';
		}

		return $prefix . ($id + 10000);
	}

	/* Get exams */
	public static function get_exams() {
		global $wpdb;
		$institute_id = self::get_current_institute_id();

		return $wpdb->get_results("SELECT id, exam_title, exam_code FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC");
	}

	/* Get published exams */
	public static function get_published_exams() {
		global $wpdb;
		$institute_id = self::get_current_institute_id();

		return $wpdb->get_results("SELECT id, exam_title, exam_code FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND is_published = 1 AND institute_id = $institute_id ORDER BY id DESC");
	}

	/* Get course */
	public static function get_course($id) {
		global $wpdb;
		$institute_id = self::get_current_institute_id();
		$id           = intval(sanitize_text_field($id));
		$row          = $wpdb->get_row("SELECT course_code, course_name FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id");
		if (!$row) {
			return null;
		}

		return $row;
	}

	/* Get course  By Course Code */
	public static function get_course_by_code($code) {
		global $wpdb;
		$institute_id = self::get_current_institute_id();
		$code           = intval(sanitize_text_field($code));
		$row          = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND course_code = $code AND institute_id = $institute_id");
		if (!$row) {
			return null;
		}
		return $row;
	}

	/* Get batch */
	public static function get_batch($id, $institute_id = '') {
		global $wpdb;
		if (!$institute_id) {
			$institute_id = self::get_current_institute_id();
		}
		$id           = intval(sanitize_text_field($id));
		$row          = $wpdb->get_row("SELECT batch_code, batch_name, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id");
		if (!$row) {
			return null;
		}

		return $row;
	}

	/* Get batch by course_id */
	public static function get_batch_by_course_id($id, $institute_id = '') {
		global $wpdb;
		if (!$institute_id) {
			$institute_id = self::get_current_institute_id();
		}
		$id           = intval(sanitize_text_field($id));
		$row          = $wpdb->get_row("SELECT id, batch_code, batch_name, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND course_id = $id AND institute_id = $institute_id");
		if (!$row) {
			return null;
		}

		return $row;
	}

	/* Send birthday messages */
	public static function send_birthday_messages() {
		global $wpdb;

		$institues = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}wl_min_institutes ORDER BY id DESC");

		foreach ($institues as $institute) {
			$institute_id = $institute->id;

			/* Get SMS template */
			$sms_template_student_birthday = WL_MIM_SettingHelper::get_sms_template_student_birthday($institute_id);

			/* Get SMS settings */
			$sms = WL_MIM_SettingHelper::get_sms_settings($institute_id);

			if ($sms_template_student_birthday['enable'] && !empty($sms_template_student_birthday['message'])) {

				$data = $wpdb->get_results("SELECT first_name, last_name, phone FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id AND MONTH(date_of_birth) = MONTH(NOW()) AND DAY(date_of_birth) = DAY(NOW())");

				foreach ($data as $row) {
					$sms_message = $sms_template_student_birthday['message'];
					$template_id = $sms_template_student_birthday['template_id'];
					$sms_message = str_replace('[FIRST_NAME]', $row->first_name, $sms_message);
					$sms_message = str_replace('[LAST_NAME]', $row->last_name, $sms_message);
					/* Send SMS */
					WL_MIM_SMSHelper::send_sms($sms, $institute_id, $sms_message, $row->phone, $template_id);
				}
			}
		}
	}

	/* Get data for dashboard */
	public static function get_data() {
		global $wpdb;
		$institute_id = self::get_current_institute_id();
		$sql          = "SELECT
		  ( SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND institute_id = $institute_id ) as courses,
		  ( SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND institute_id = $institute_id ) as batches,
		  ( SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND institute_id = $institute_id ) as enquiries,
		  ( SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id ) as students,
		  ( SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0 AND institute_id = $institute_id ) as installments,
		  ( SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ) as courses_active,
		  ( SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ) as batches_active,
		  ( SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ) as enquiries_active,
		  ( SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id) as students_active";

		$count = $wpdb->get_row($sql);

		$students              = $wpdb->get_results("SELECT id, fees FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id");
		$students_fees_paid    = 0;
		$students_fees_pending = 0;
		foreach ($students as $student) {
			$fees = unserialize($student->fees);
			if (self::get_fees_total($fees['payable']) > self::get_fees_total($fees['paid'])) {
				$students_fees_pending++;
			} else {
				$students_fees_paid++;
			}
		}

		$count->students_fees_paid    = $students_fees_paid;
		$count->students_fees_pending = $students_fees_pending;

		$course_data = $wpdb->get_results("SELECT id, course_name, course_code, is_deleted, is_active FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K);

		$sql              = "SELECT id, course_id, created_at FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY id DESC LIMIT 5";
		$recent_enquiries = $wpdb->get_results($sql);

		$sql                       = "SELECT course_id, COUNT( course_id ) as students FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id GROUP BY course_id ORDER BY COUNT( course_id ) DESC LIMIT 5";
		$popular_courses_enquiries = $wpdb->get_results($sql);

		$installments = $wpdb->get_results("SELECT id, fees FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0 AND institute_id = $institute_id");
		$revenue      = 0;
		if (count($installments)) {
			foreach ($installments as $installment) {
				$fees    = unserialize($installment->fees);
				$revenue += array_sum($fees['paid']);
			}
		}
		$revenue = number_format(max(floatval($revenue), 0), 2, '.', '');

		return array(
			'count'                     => $count,
			'course_data'               => $course_data,
			'recent_enquiries'          => $recent_enquiries,
			'popular_courses_enquiries' => $popular_courses_enquiries,
			'revenue'                   => $revenue
		);
	}

	public static function lm_valid() {
		$wl_mim_lm = WL_MIM_LM::get_instance();
		$wl_mim_lm_val = $wl_mim_lm->is_valid();
		if (isset($wl_mim_lm_val) && $wl_mim_lm_val) {
			return true;
		}
		return false;
	}
	// Certificate

	public static function get_certificate_place_holder($key, $institute_id = '') {
		if (array_key_exists($key, self::certificate_place_holders($institute_id))) {
			return self::certificate_place_holders($institute_id)[$key];
		}
		return '';
	}

	public static function certificate_place_holders($institute_id = '') {

		$institute_advanced_logo    = '';
		$institute_advanced_name    = '';
		$institute_advanced_address = '';
		$institute_advanced_phone   = '';
		$institute_advanced_email   = '';
		$institute_logo_url         = '';
		$certificate_qr_code		= '';
		if ($institute_id) {
			$general_institute          = WL_MIM_SettingHelper::get_general_institute_settings($institute_id);
			$institute_advanced_logo    = wp_get_attachment_url($general_institute['institute_logo']);
			$institute_advanced_name    = $general_institute['institute_name'];
			$institute_advanced_address = $general_institute['institute_address'];
			$institute_advanced_phone   = $general_institute['institute_phone'];
			$institute_advanced_email   = $general_institute['institute_email'];
			$show_logo                  = $general_institute['institute_logo_enable'];
			//$show_certificate_qr_code	= $general_institute['certificate_qr_code'];

			// $settings_general = WLSM_M_Setting::get_settings_general( $school_id );
			// $school_logo      = $settings_general['school_logo'];
			if (!empty($institute_advanced_logo)) {
				$institute_logo_url = esc_url(wp_get_attachment_url($institute_advanced_logo));
				if (!$institute_logo_url) {
					$institute_logo_url = '';
				}
			}
		}

		return array(
			'name'               => '[STUDENT_NAME]',
			'certificate-number' => '[CERTIFICATE_NO]',
			'certificate-title'  => '[CERTIFICATE_TITLE]',
			'enrollment-number'  => '[ENROLLMENT_NUMBER]',
			'photo'              => WL_MIM_PLUGIN_URL . 'assets/images/student.jpg',
			'signature'          => WL_MIM_PLUGIN_URL . 'assets/images/signature.jpg',
			'batch'              => '[BATCH]',
			'course-duration'    => '[COURSE_DURATION]',
			'marks'              => '[MARKS]',
			'course'             => '[COURSE]',
			'dob'                => '[DATE_OF_BIRTH]',
			'caste'              => '[CASTE]',
			'blood-group'        => '[BLOOD_GROUP]',
			'father-name'        => '[FATHER_NAME]',
			'mother-name'        => '[MOTHER_NAME]',
			'class-teacher'      => '[CLASS_TEACHER]',
			'institute-name'     => '[INSTITUTE_NAME]',
			'institute-phone'    => '[INSTITUTE_PHONE]',
			'institute-email'    => '[INSTITUTE_EMAIL]',
			'institute-address'  => '[INSTITUTE_ADDRESS]',
			'issued-date'        => '[ISSUED_DATE]',
			'institute-logo'     => WL_MIM_PLUGIN_URL . 'assets/images/logo.png',
			'certificate-qr-code' => WL_MIM_PLUGIN_URL . 'assets/images/dummy_qr_code.png',
			// 'certificate-qr-code' => 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=http%3A%2F%2Fwww.google.com%2F&choe=UTF-8',
		);
	}

	public static function get_certificate_place_holder_type($key) {
		if (array_key_exists($key, self::certificate_place_holder_types())) {
			return self::certificate_place_holder_types()[$key];
		}
		return 'text';
	}

	public static function certificate_place_holder_types() {
		return array(
			'name'                => 'text',
			'certificate-number'  => 'text',
			'certificate-title'   => 'text',
			'photo'               => 'image',
			'signature'           => 'image',
			'enrollment-number'   => 'text',
			'course-duration'     => 'text',
			'admission-number'    => 'text',
			'roll-number'         => 'text',
			'session-label'       => 'text',
			'session-start-date'  => 'text',
			'session-end-date'    => 'text',
			'session-start-year'  => 'text',
			'session-end-year'    => 'text',
			'class'               => 'text',
			'section'             => 'text',
			'dob'                 => 'text',
			'caste'               => 'text',
			'blood-group'         => 'text',
			'father-name'         => 'text',
			'mother-name'         => 'text',
			'class-teacher'       => 'text',
			'institute-name'      => 'text',
			'institute-phone'     => 'text',
			'institute-email'     => 'text',
			'institute-address'   => 'text',
			'issued-date'         => 'text',
			'institute-logo'      => 'image',
			'certificate-qr-code' => 'image',
		);
	}



	public static function get_certificate_field_label($key) {
		if (array_key_exists($key, self::certificate_field_labels())) {
			return self::certificate_field_labels()[$key];
		}
		return '';
	}


	public static function get_certificate_property($key) {
		if (array_key_exists($key, self::certificate_properties())) {
			return self::certificate_properties()[$key];
		}
		return '';
	}
	public static function get_certificate_field_type($key) {
		if (array_key_exists($key, self::certificate_field_types())) {
			return self::certificate_field_types()[$key];
		}
		return 'text';
	}

	public static function certificate_field_types() {
		return array(
			'left'        => 'number',
			'top'         => 'number',
			'font-weight' => 'number',
			'font-size'   => 'number',
			'width'       => 'number',
			'height'      => 'number'
		);
	}
	public static function certificate_properties() {
		return array(
			'left'        => esc_html__('Position X', WL_MIM_DOMAIN),
			'top'         => esc_html__('Position Y', WL_MIM_DOMAIN),
			'font-weight' => esc_html__('Font Weight', WL_MIM_DOMAIN),
			'font-size'   => esc_html__('Font Size', WL_MIM_DOMAIN),
			'width'       => esc_html__('Width', WL_MIM_DOMAIN),
			'height'      => esc_html__('Height', WL_MIM_DOMAIN),
		);
	}
	public static function certificate_field_labels() {
		return array(
			'name'               => esc_html__('Name', WL_MIM_DOMAIN),
			'certificate-number' => esc_html__('Certificate Number', WL_MIM_DOMAIN),
			'certificate-title'  => esc_html__('Certificate Title', WL_MIM_DOMAIN),
			'photo'              => esc_html__('Photo', WL_MIM_DOMAIN),
			'signature'          => esc_html__('Signature', WL_MIM_DOMAIN),
			'enrollment-number'      => esc_html__('Enrollment ID', WL_MIM_DOMAIN),
			'batch'              => esc_html__('Batch', WL_MIM_DOMAIN),
			'course'             => esc_html__('Course', WL_MIM_DOMAIN),
			'course-duration'    => esc_html__('Course Duration', WL_MIM_DOMAIN),
			'marks'              => esc_html__('Marks', WL_MIM_DOMAIN),
			'session-start-date' => esc_html__('Session Start Date', WL_MIM_DOMAIN),
			'session-end-date'   => esc_html__('Session End Date', WL_MIM_DOMAIN),
			'class'              => esc_html__('Class', WL_MIM_DOMAIN),
			'section'            => esc_html__('Section', WL_MIM_DOMAIN),
			'dob'                => esc_html__('Date of Birth', WL_MIM_DOMAIN),
			'father-name'        => esc_html__('Father Name', WL_MIM_DOMAIN),
			'mother-name'        => esc_html__('Mother Name', WL_MIM_DOMAIN),
			'institute-name'     => esc_html__('institute Name', WL_MIM_DOMAIN),
			'institute-phone'    => esc_html__('institute Phone', WL_MIM_DOMAIN),
			'institute-email'    => esc_html__('institute Email', WL_MIM_DOMAIN),
			'institute-address'  => esc_html__('institute Address', WL_MIM_DOMAIN),
			'issued-date'        => esc_html__('Certificate Issued Date', WL_MIM_DOMAIN),
			'institute-logo'     => esc_html__('institute Logo', WL_MIM_DOMAIN),
			'certificate-qr-code' => esc_html__('Certificate QR Code', WL_MIM_DOMAIN),
		);
	}

	public static function get_certificates_page_url() {
		return admin_url('admin.php?page=' . WL_MIM_MENU_CERTIFICATES);
	}

	public static function fetch_certificates_distributed_query($institute_id, $certificate_id) {
		global $wpdb;
		$query = 'SELECT cfsr.ID as id, cfsr.certificate_number, cfsr.date_issued, sr.ID as student_id, sr.first_name as student_name, mb.batch_code, mb.batch_name, mc.course_code, mc.course_name, sr.phone FROM ' . "{$wpdb->prefix}" . 'wl_min_certificate_student' . ' as cfsr
		JOIN '  . "{$wpdb->prefix}" . 'wl_min_certificate' . ' as cf ON cf.ID = cfsr.certificate_id
		JOIN ' . "{$wpdb->prefix}" . 'wl_min_students' . ' as sr ON sr.ID = cfsr.student_record_id
		JOIN ' . "{$wpdb->prefix}" . 'wl_min_batches' . ' as mb ON mb.id = sr.batch_id
		JOIN ' . "{$wpdb->prefix}" . 'wl_min_courses' . ' as mc ON mc.id = sr.course_id
		WHERE cf.ID = ' . absint($certificate_id);
		return $query;
	}


	public static function fetch_certificates_distributed_query_count($institute_id, $certificate_id) {
		global $wpdb;
		$query = 'SELECT COUNT(DISTINCT cfsr.ID) FROM ' . "{$wpdb->prefix}" . 'wl_min_certificate_student' . ' as cfsr
		JOIN '  . "{$wpdb->prefix}" . 'wl_min_certificate' . ' as cf ON cf.ID = cfsr.certificate_id
		JOIN ' . "{$wpdb->prefix}" . 'wl_min_students' . ' as sr ON sr.ID = cfsr.student_record_id

		WHERE cf.ID = ' . absint($certificate_id);
		return $query;
	}

	public static function fetch_certificates_distributed_query_group_by() {
		$group_by = 'GROUP BY cfsr.ID';
		return $group_by;
	}

	public static function fetch_certificate($institute_id, $id) {
		global $wpdb;
		$certificate = $wpdb->get_row($wpdb->prepare('SELECT cf.ID, cf.label, cf.image_id, cf.fields FROM ' . "{$wpdb->prefix}" . 'wl_min_certificate' . ' as cf
		WHERE cf.institute_id = %d AND cf.ID = %d', $institute_id, $id));
		return $certificate;
	}
	public static function fetch_certificate_view($institute_id, $id) {
		global $wpdb;
		$certificate = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . "{$wpdb->prefix}" . 'wl_min_certificate' . ' as cf
		JOIN ' . "{$wpdb->prefix}" . 'wl_min_certificate_student' . ' as cfsr ON cfsr.certificate_id = cf.id
		WHERE cf.institute_id = %d AND cfsr.id = %d', $institute_id, $id));
		return $certificate;
	}

	public static function fetch_certificate_student_dash($institute_id, $id) {
		global $wpdb;
		$certificate = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . "{$wpdb->prefix}" . 'wl_min_certificate' . ' as cf
		JOIN ' . "{$wpdb->prefix}" . 'wl_min_certificate_student' . ' as cfsr ON cfsr.certificate_id = cf.id
		WHERE cf.institute_id = %d AND cfsr.id = %d', $institute_id, $id));
		return $certificate;
	}

	public static function fetch_certificate_query($institute_id) {
		global $wpdb;
		$query = 'SELECT cf.id, cf.label FROM ' . "{$wpdb->prefix}" . 'wl_min_certificate' . ' as cf
		WHERE cf.institute_id = ' . absint($institute_id);
		return $query;
	}

	/**
	 * Get following information from the student id
	 * Institute Id 
	 * student certificate id
	 */
	public static function get_student_information_certificate( $student_id ) {
		global $wpdb;
		$a = $wpdb->prefix . 'wl_min_students';
		$query = 'SELECT * FROM ' . $a . ' WHERE id = '. absint($student_id);
		// return $wpdb->get_results("SELECT institute_id, first_name FROM {$wpdb->prefix}wl_min_students WHERE id = $student_id");
		return $wpdb->get_results( $query );		
		// return $a;
	}

	public static function get_certificate_dynamic_fields() {
		return array(
			'name' => array(
				'enable' => 1,
				'props'  => array(
					'left' => array(
						'value' => '370',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '310',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '18',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'course-duration' => array(
				'enable' => 1,
				'props'  => array(
					'left' => array(
						'value' => '400',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '24',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '14',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'certificate-number' => array(
				'enable' => 1,
				'props'  => array(
					'left' => array(
						'value' => '58',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '24',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '14',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'certificate-title' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '190',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '24',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '20',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'photo' => array(
				'enable' => 1,
				'props'  => array(
					'left' => array(
						'value' => '460',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '319',
						'unit'  => 'pt'
					),
					'width' => array(
						'value' => '98',
						'unit'  => 'pt'
					),
					'height' => array(
						'value' => '135',
						'unit'  => 'pt'
					)
				),
				'type' => 'image'
			),
			'signature' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '460',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '319',
						'unit'  => 'pt'
					),
					'width' => array(
						'value' => '120',
						'unit'  => 'pt'
					),
					'height' => array(
						'value' => '60',
						'unit'  => 'pt'
					)
				),
				'type' => 'image'
			),
			'batch' => array(
				'enable' => 1,
				'props'  => array(
					'left' => array(
						'value' => '370',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '590',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '14',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'enrollment-number' => array(
				'enable' => 1,
				'props'  => array(
					'left' => array(
						'value' => '119',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '355',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '14',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'course' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '433',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '590',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '16',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),

			'dob' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '165',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '643',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '16',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),

			'father-name' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '187',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '544',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '18',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'mother-name' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '187',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '544',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '18',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),

			'institute-name' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '165',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '643',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '16',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'institute-phone' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '165',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '643',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '12',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'institute-email' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '165',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '643',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '12',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'institute-address' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '165',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '643',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '12',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),

			'issued-date' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '130',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '415',
						'unit'  => 'pt'
					),
					'font-weight' => array(
						'value' => '600',
						'unit'  => ''
					),
					'font-size' => array(
						'value' => '12',
						'unit'  => 'pt'
					)
				),
				'type' => 'text'
			),
			'institute-logo' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '150',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '50',
						'unit'  => 'pt'
					),
					'width' => array(
						'value' => '90',
						'unit'  => 'pt'
					),
					'height' => array(
						'value' => '90',
						'unit'  => 'pt'
					)
				),
				'type' => 'image'
			),
			'certificate-qr-code' => array(
				'enable' => 0,
				'props'  => array(
					'left' => array(
						'value' => '400',
						'unit'  => 'pt'
					),
					'top' => array(
						'value' => '400',
						'unit'  => 'pt'
					),
					'width' => array(
						'value' => '60',
						'unit'  => 'pt'
					),
					'height' => array(
						'value' => '60',
						'unit'  => 'pt'
					)
				),
				'type' => 'image'
			)
		);
	}
}
