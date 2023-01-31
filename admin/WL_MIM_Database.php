<?php
defined( 'ABSPATH' ) || die();

require_once 'inc/helpers/WL_MIM_Helper.php';

class WL_MIM_Database {
	/* On plugin activation */
	public static function activation() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/* Create institutes table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_institutes (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) DEFAULT NULL,
				address text DEFAULT NULL,
				phone varchar(255) DEFAULT NULL,
				email varchar(255) DEFAULT NULL,
				extra_details text DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id)
				) $charset_collate";
		dbDelta( $sql );

		/* Add contact_person column if not exists to institutes table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_institutes' AND COLUMN_NAME = 'contact_person'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_institutes ADD contact_person varchar(255) DEFAULT NULL" );
		}

		/* Add registration_number column if not exists to institutes table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_institutes' AND COLUMN_NAME = 'registration_number'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_institutes ADD registration_number varchar(255) DEFAULT NULL" );
		}

		/* Add can_add_course, can_delete_course, can_update_course columns if not exists to institutes table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_institutes' AND COLUMN_NAME = 'can_delete_course'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_institutes ADD can_add_course tinyint(1) NOT NULL DEFAULT '1'" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_institutes ADD can_delete_course tinyint(1) NOT NULL DEFAULT '1'" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_institutes ADD can_update_course tinyint(1) NOT NULL DEFAULT '1'" );
		}

		/* Create settings table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_settings (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				mim_key varchar(191) DEFAULT NULL,
				mim_value text DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE (institute_id, mim_key),
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Create expense table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_expense (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title varchar(255) DEFAULT NULL,
				description text DEFAULT NULL,
				amount decimal(12,2) UNSIGNED DEFAULT NULL,
				consumption_date date NULL DEFAULT NULL,
				notes text DEFAULT NULL,
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				INDEX (added_by),
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Create main_courses table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_main_courses (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				course_code varchar(191) NOT NULL,
				course_name varchar(255) DEFAULT NULL,
				course_detail text DEFAULT NULL,
				duration int(11) UNSIGNED DEFAULT NULL,
				duration_in varchar(255) DEFAULT NULL,
				fees decimal(12,2) UNSIGNED DEFAULT NULL,
				period varchar(20) DEFAULT 'one-time',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE (course_code)
				) $charset_collate";
		dbDelta( $sql );

		/* Create course_categories table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_course_categories (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name varchar(191) NOT NULL,
				detail text DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE (name, institute_id),
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Create courses table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_courses (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				course_code varchar(191) NOT NULL,
				course_name varchar(255) DEFAULT NULL,
				course_detail text DEFAULT NULL,
				duration int(11) UNSIGNED DEFAULT NULL,
				duration_in varchar(255) DEFAULT NULL,
				fees decimal(12,2) UNSIGNED DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				period varchar(20) DEFAULT 'one-time',
				PRIMARY KEY (id),
				UNIQUE (course_code, is_deleted, deleted_at, institute_id),
				INDEX (added_by),
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Add course_category_id column if not exists to courses table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_courses' AND COLUMN_NAME = 'course_category_id'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_courses ADD course_category_id bigint(20) UNSIGNED DEFAULT NULL" );
			$wpdb->query( "CREATE INDEX course_category_id ON {$wpdb->prefix}wl_min_courses (course_category_id)" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_courses ADD FOREIGN KEY (course_category_id) REFERENCES {$wpdb->prefix}wl_min_course_categories (id) ON DELETE SET NULL" );
		}

		/* Create fee_types table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_fee_types (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				fee_type varchar(191) NOT NULL,
				amount text DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE (fee_type, is_deleted, deleted_at, institute_id),
				INDEX (added_by),
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Add periods column if not exists to courses table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_fee_types' AND COLUMN_NAME = 'periods'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_fee_types ADD periods varchar(191) NOT NULL" );
		}

		/* Create custom_fields table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_custom_fields (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				field_name varchar(50) NOT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Create enquiries table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_enquiries (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				course_id bigint(20) UNSIGNED DEFAULT NULL,
				first_name varchar(255) NOT NULL,
				last_name varchar(255) DEFAULT NULL,
				gender varchar(255) NOT NULL,
				date_of_birth date NULL DEFAULT NULL,
				id_proof bigint(20) UNSIGNED DEFAULT NULL,
				father_name varchar(255) DEFAULT NULL,
				mother_name varchar(255) DEFAULT NULL,
				address text DEFAULT NULL,
				city varchar(255) DEFAULT NULL,
				zip varchar(255) DEFAULT NULL,
				state varchar(255) DEFAULT NULL,
				nationality varchar(255) DEFAULT NULL,
				qualification varchar(255) DEFAULT NULL,
				photo_id bigint(20) UNSIGNED DEFAULT NULL,
				signature_id bigint(20) UNSIGNED DEFAULT NULL,
				phone varchar(255) DEFAULT NULL,
				email varchar(255) DEFAULT NULL,
				message text DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				INDEX (course_id),
				INDEX (added_by),
				FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}wl_min_courses (id) ON DELETE SET NULL,
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Add fields column follow up date and note if not exists to  enquiries table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_enquiries' AND COLUMN_NAME = 'follow_up_date'" );

		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_enquiries ADD follow_up_date date NULL DEFAULT NULL" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_enquiries ADD note text DEFAULT NULL" );
		}

		/* Add reference column if not exists to enquiries table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_enquiries' AND COLUMN_NAME = 'reference'" );

		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_enquiries ADD reference text DEFAULT NULL" );
		}

		/* Create students table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_students (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				course_id bigint(20) UNSIGNED DEFAULT NULL,
				first_name varchar(255) NOT NULL,
				last_name varchar(255) DEFAULT NULL,
				gender varchar(255) DEFAULT NULL,
				date_of_birth date NULL DEFAULT NULL,
				id_proof bigint(20) UNSIGNED DEFAULT NULL,
				father_name varchar(255) DEFAULT NULL,
				mother_name varchar(255) DEFAULT NULL,
				state varchar(255) DEFAULT NULL,
				nationality varchar(255) DEFAULT NULL,
				qualification varchar(255) DEFAULT NULL,
				photo_id bigint(20) UNSIGNED DEFAULT NULL,
				signature_id bigint(20) UNSIGNED DEFAULT NULL,
				phone varchar(255) DEFAULT NULL,
				email varchar(255) DEFAULT NULL,
				address text DEFAULT NULL,
				city varchar(255) DEFAULT NULL,
				zip varchar(255) DEFAULT NULL,
				fees text DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				inactive_at timestamp NULL DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				INDEX (course_id),
				INDEX (added_by),
				FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}wl_min_courses (id) ON DELETE SET NULL,
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Add custom_fields column if not exists to students and enquiries table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_students' AND COLUMN_NAME = 'custom_fields'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD custom_fields text DEFAULT NULL" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_enquiries ADD custom_fields text DEFAULT NULL" );
		}

		/* Add custom_fields column if not exists to students and enquiries table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_students' AND COLUMN_NAME = 'enrollment_id'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD enrollment_id varchar(255) DEFAULT NULL" );
		}

		/* Create installments table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_installments (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				student_id bigint(20) UNSIGNED DEFAULT NULL,
				fees text DEFAULT NULL,
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				INDEX (student_id),
				INDEX (added_by),
				FOREIGN KEY (student_id) REFERENCES {$wpdb->prefix}wl_min_students (id) ON DELETE SET NULL,
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Create invoices table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_invoices (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				invoice_title varchar(191) NOT NULL,
				student_id bigint(20) UNSIGNED DEFAULT NULL,
				fees text DEFAULT NULL,
				status varchar(50) DEFAULT 'pending',
				invoice_date date NULL DEFAULT NULL,
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE (invoice_title, student_id, institute_id),
				INDEX (student_id),
				INDEX (added_by),
				FOREIGN KEY (student_id) REFERENCES {$wpdb->prefix}wl_min_students (id) ON DELETE SET NULL,
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Add invoice_id column if not exists to installments table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_installments' AND COLUMN_NAME = 'invoice_id'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_installments ADD invoice_id bigint(20) UNSIGNED DEFAULT NULL" );
			$wpdb->query( "CREATE INDEX invoice_id ON {$wpdb->prefix}wl_min_installments (invoice_id)" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_installments ADD FOREIGN KEY (invoice_id) REFERENCES {$wpdb->prefix}wl_min_invoices (id) ON DELETE SET NULL" );
		}

		/* Create batches table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_batches (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				course_id bigint(20) UNSIGNED DEFAULT NULL,
				batch_code varchar(255) NOT NULL,
				batch_name varchar(255) DEFAULT NULL,
				time_from time DEFAULT NULL,
				time_to time DEFAULT NULL,
				start_date date NULL DEFAULT NULL,
				end_date date NULL DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				INDEX (course_id),
				INDEX (added_by),
				FOREIGN KEY (course_id) REFERENCES {$wpdb->prefix}wl_min_courses (id) ON DELETE SET NULL,
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Add batch_id column if not exists to students table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_students' AND COLUMN_NAME = 'batch_id'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD batch_id bigint(20) UNSIGNED DEFAULT NULL" );
			$wpdb->query( "CREATE INDEX batch_id ON {$wpdb->prefix}wl_min_students (batch_id)" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD FOREIGN KEY (batch_id) REFERENCES {$wpdb->prefix}wl_min_batches (id) ON DELETE SET NULL" );
		}

		/* Create notices table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_notices (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title text NOT NULL,
				attachment bigint(20) UNSIGNED DEFAULT NULL,
				url text DEFAULT NULL,
				link_to varchar(255) DEFAULT 'url',
				priority int(11) DEFAULT 10,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				INDEX (added_by),
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Add user_id, allow_login columns if not exists to students table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_students' AND COLUMN_NAME = 'user_id'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD user_id bigint(20) UNSIGNED DEFAULT NULL" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD CONSTRAINT UNIQUE (user_id, is_deleted, deleted_at)" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD FOREIGN KEY (user_id) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD allow_login tinyint(1) NOT NULL DEFAULT '0'" );
		}

		/* Add roll_number column if not exists to students table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_students' AND COLUMN_NAME = 'roll_number'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD roll_number varchar(191) DEFAULT NULL" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_students ADD CONSTRAINT UNIQUE (roll_number, is_deleted, deleted_at, institute_id)" );
		}

		/* Add payment_method, payment_id columns if not exists to installments table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_installments' AND COLUMN_NAME = 'payment_id'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_installments ADD payment_method varchar(255) DEFAULT NULL" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_installments ADD payment_id text DEFAULT NULL" );
		}

		/* Create exams table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_exams (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				exam_code varchar(191) NOT NULL,
				exam_title text DEFAULT NULL,
				exam_date date NULL DEFAULT NULL,
				marks text DEFAULT NULL,
				is_published tinyint(1) NOT NULL DEFAULT '0',
				published_at timestamp NULL DEFAULT NULL,
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE (exam_code, is_deleted, deleted_at, institute_id),
				INDEX (added_by),
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Create results table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_results (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				student_id bigint(20) UNSIGNED DEFAULT NULL,
				exam_id bigint(20) UNSIGNED DEFAULT NULL,
				marks text DEFAULT NULL,
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE (exam_id, student_id, is_deleted, deleted_at, institute_id),
				INDEX (exam_id),
				INDEX (added_by),
				FOREIGN KEY (student_id) REFERENCES {$wpdb->prefix}wl_min_students (id) ON DELETE SET NULL,
				FOREIGN KEY (exam_id) REFERENCES {$wpdb->prefix}wl_min_exams (id) ON DELETE SET NULL,
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

			/* Add period column if not exists to fee_types and courses table */
			$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_results' AND COLUMN_NAME = 'marks_p'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_results ADD marks_p text DEFAULT NULL" );
		}

		/* Create staffs table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_staffs (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				first_name varchar(255) NOT NULL,
				last_name varchar(255) DEFAULT NULL,
				gender varchar(255) DEFAULT NULL,
				date_of_birth date NULL DEFAULT NULL,
				id_proof bigint(20) UNSIGNED DEFAULT NULL,
				state varchar(255) DEFAULT NULL,
				nationality varchar(255) DEFAULT NULL,
				qualification varchar(255) DEFAULT NULL,
				photo_id bigint(20) UNSIGNED DEFAULT NULL,
				signature_id bigint(20) UNSIGNED DEFAULT NULL,
				phone varchar(255) DEFAULT NULL,
				email varchar(255) DEFAULT NULL,
				address text DEFAULT NULL,
				city varchar(255) DEFAULT NULL,
				zip varchar(255) DEFAULT NULL,
				salary decimal(12,2) UNSIGNED DEFAULT NULL,
				job_title text DEFAULT NULL,
				job_description text DEFAULT NULL,
				user_id bigint(20) UNSIGNED DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				is_deleted tinyint(1) NOT NULL DEFAULT '0',
				inactive_at timestamp NULL DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				deleted_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE (user_id, is_deleted, deleted_at),
				INDEX (added_by),
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (user_id) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Create notes table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_notes (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title varchar(255) DEFAULT NULL,
				description text DEFAULT NULL,
				document_ids text DEFAULT NULL,
				notes_date date NULL DEFAULT NULL,
				batch_id bigint(20) UNSIGNED DEFAULT NULL,
				is_active tinyint(1) NOT NULL DEFAULT '1',
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				INDEX (batch_id),
				INDEX (added_by),
				FOREIGN KEY (batch_id) REFERENCES {$wpdb->prefix}wl_min_batches (id) ON DELETE SET NULL,
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Create attendance table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_attendance (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				student_id bigint(20) UNSIGNED DEFAULT NULL,
				status varchar(1) NOT NULL DEFAULT 'a',
				added_by bigint(20) UNSIGNED DEFAULT NULL,
				institute_id bigint(20) UNSIGNED DEFAULT NULL,
				attendance_date date NULL DEFAULT NULL,
				created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at timestamp NULL DEFAULT NULL,
				PRIMARY KEY (id),
				INDEX (student_id),
				INDEX (added_by),
				FOREIGN KEY (student_id) REFERENCES {$wpdb->prefix}wl_min_students (id) ON DELETE SET NULL,
				FOREIGN KEY (added_by) REFERENCES {$wpdb->base_prefix}users (ID) ON DELETE SET NULL,
				FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE
				) $charset_collate";
		dbDelta( $sql );

		/* Create certificates table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_certificate (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			label varchar(191) DEFAULT NULL,
			fields text DEFAULT NULL,
			image_id bigint(20) UNSIGNED DEFAULT NULL,
			institute_id bigint(20) UNSIGNED DEFAULT NULL,
			created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at timestamp NULL DEFAULT NULL,
			PRIMARY KEY (ID),
			INDEX (institute_id),
			FOREIGN KEY (institute_id) REFERENCES {$wpdb->prefix}wl_min_institutes (id) ON DELETE CASCADE,
			FOREIGN KEY (image_id) REFERENCES {$wpdb->prefix}posts (ID) ON DELETE SET NULL

			) " . $charset_collate;
		dbDelta( $sql );

		/* Create certificate_student table */
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wl_min_certificate_student (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			certificate_number varchar(60) DEFAULT NULL,
			date_issued date NULL DEFAULT NULL,
			certificate_id bigint(20) UNSIGNED DEFAULT NULL,
			student_record_id bigint(20) UNSIGNED DEFAULT NULL,
			created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at timestamp NULL DEFAULT NULL,
			-- last_certificate_count bigint(20) NOT NULL DEFAULT,
			PRIMARY KEY (ID),
			INDEX (certificate_id),
			INDEX (student_record_id),
			FOREIGN KEY (certificate_id) REFERENCES {$wpdb->prefix}wl_min_certificate (id) ON DELETE CASCADE,
			FOREIGN KEY (student_record_id) REFERENCES {$wpdb->prefix}wl_min_students (id) ON DELETE CASCADE
			) " . $charset_collate;
		dbDelta( $sql );

		/* Add period column if not exists to fee_types and courses table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_courses' AND COLUMN_NAME = 'period'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_courses ADD period varchar(20) DEFAULT 'one-time'" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_fee_types ADD period varchar(20) DEFAULT 'one-time'" );
		}

		/* Add notes column if not exists to exams table */
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$wpdb->prefix}wl_min_exams' AND COLUMN_NAME = 'notes'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}wl_min_exams ADD notes text DEFAULT NULL" );
		}

		/* Assign custom capabilities to admin */
		WL_MIM_Helper::assign_capabilities();

		/* Add default options */
		self::add_options();
	}

	/* On plugin deactivation */
	public static function deactivation() {
		self::remove_items();
	}

	public static function add_options() {
		add_option( 'multi_institute_enable_enquiry_form_title', 'yes' );
		add_option( 'multi_institute_enquiry_form_title', esc_html__( 'Admission Enquiry', WL_MIM_DOMAIN ) );
		add_option( 'multi_institute_enable_university_header', '' );
		add_option( 'multi_institute_university_logo', '' );
		add_option( 'multi_institute_university_name', '' );
		add_option( 'multi_institute_university_address', '' );
		add_option( 'multi_institute_university_phone', '' );
		add_option( 'multi_institute_university_email', '' );
	}

	public static function remove_items() {
		delete_option( 'wl-mim-key' );
		delete_option( 'wl-mim-valid' );
		delete_option( 'wl-mim-cache' );
		delete_option( 'wl-multi-institute-updation-detail' );
	}
}
