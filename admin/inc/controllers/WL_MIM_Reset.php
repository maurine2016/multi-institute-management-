<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/WL_MIM_Database.php' );

class WL_MIM_Reset {
	/* Perform reset */
	public static function perform_reset() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die();
		}

		if ( ! wp_verify_nonce( $_POST["reset-plugin"], "reset-plugin" ) ) {
			die();
		}

		global $wpdb;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wl_min_enquiries'" ) == "{$wpdb->prefix}wl_min_enquiries" ) {
			/* Delete enquiry attachments */
			$enquiries = $wpdb->get_results( "SELECT id_proof, photo_id, signature_id FROM {$wpdb->prefix}wl_min_enquiries" );
			if ( $enquiries && count( $enquiries ) ) {
				foreach( $enquiries as $enquiry ) {
					if ( $enquiry->id_proof ) {
						wp_delete_attachment( $enquiry->id_proof, true );
					}
					if ( $enquiry->photo_id ) {
						wp_delete_attachment( $enquiry->photo_id, true );
					}
					if ( $enquiry->signature_id ) {
						wp_delete_attachment( $enquiry->signature_id, true );
					}
				}
			}
		}		

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wl_min_students'" ) == "{$wpdb->prefix}wl_min_students" ) {
			/* Delete student attachments and user account */
			$students = $wpdb->get_results( "SELECT user_id, id_proof, photo_id, signature_id FROM {$wpdb->prefix}wl_min_students" );
			if ( $students && count( $students ) ) {
				foreach( $students as $student ) {
					if ( $student->id_proof ) {
						wp_delete_attachment( $student->id_proof, true );
					}
					if ( $student->photo_id ) {
						wp_delete_attachment( $student->photo_id, true );
					}
					if ( $student->signature_id ) {
						wp_delete_attachment( $student->signature_id, true );
					}
					if ( $student->user_id ) {
						wp_delete_user( $student->user_id );
					}
				}
			}
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wl_min_staffs'" ) == "{$wpdb->prefix}wl_min_staffs" ) {
			/* Delete staff attachments */
			$staffs = $wpdb->get_results( "SELECT id_proof, photo_id, signature_id FROM {$wpdb->prefix}wl_min_staffs" );
			if ( $staffs && count( $staffs ) ) {
				foreach( $staffs as $staff ) {
					if ( $staff->id_proof ) {
						wp_delete_attachment( $staff->id_proof, true );
					}
					if ( $staff->photo_id ) {
						wp_delete_attachment( $staff->photo_id, true );
					}
					if ( $staff->signature_id ) {
						wp_delete_attachment( $staff->signature_id, true );
					}
				}
			}
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wl_min_notices'" ) == "{$wpdb->prefix}wl_min_notices" ) {
			/* Delete notice attachments */
			$notices = $wpdb->get_results( "SELECT attachment FROM {$wpdb->prefix}wl_min_notices" );
			if ( $notices && count( $notices ) ) {
				foreach( $notices as $notice ) {
					if ( $notice->attachment ) {
						wp_delete_attachment( $notice->attachment, true );
					}
				}
			}
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wl_min_notes'" ) == "{$wpdb->prefix}wl_min_notes" ) {
			/* Delete note attachments */
			$notes = $wpdb->get_results( "SELECT document_ids FROM {$wpdb->prefix}wl_min_notes" );
			if ( $notes && count( $notes ) ) {
				foreach( $notes as $note ) {
					if ( $note->document_ids ) {
						$document_ids = unserialize( $note->document_ids );
						foreach( $document_ids as $document_id ) {
							wp_delete_attachment( $document_id, true );
						}
					}
				}
			}
		}		

		/* Remove custom capabilities of admin */
		WL_MIM_Helper::remove_capabilities();
		$wpdb->query( "SET foreign_key_checks = 0" );
		/* Drop attendance table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_attendance" );

		/* Drop notes table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_notes" );

		/* Drop staffs table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_staffs" );

		/* Drop results table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_results" );

		/* Drop exams table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_exams" );

		/* Drop installments table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_installments" );

		/* Drop notices table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_notices" );

		/* Drop invoices table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_invoices" );

		/* Drop students table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_students" );

		/* Drop enquiries table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_enquiries" );

		/* Drop batches table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_batches" );

		/* Drop custom_fields table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_custom_fields" );

		/* Drop fee_types table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_fee_types" );

		/* Drop courses table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_courses" );

		/* Drop course_categories table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_course_categories" );

		/* Drop expense table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_expense" );

		/* Drop settings table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_settings" );

		/* Drop institutes table */
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wl_min_institutes" );

		/* Unregister settings */
		unregister_setting( 'wl_min_settings_group', 'multi_institute_enable_enquiry_form_title' );
		unregister_setting( 'wl_min_settings_group', 'multi_institute_enquiry_form_title' );
		unregister_setting( 'wl_min_settings_group', 'multi_institute_enable_university_header' );
		unregister_setting( 'wl_min_settings_group', 'multi_institute_university_logo' );
		unregister_setting( 'wl_min_settings_group', 'multi_institute_university_name' );
		unregister_setting( 'wl_min_settings_group', 'multi_institute_university_address' );
		unregister_setting( 'wl_min_settings_group', 'multi_institute_university_phone' );
		unregister_setting( 'wl_min_settings_group', 'multi_institute_university_email' );
		delete_option( 'multi_institute_enable_enquiry_form_title' );
		delete_option( 'multi_institute_enquiry_form_title' );
		delete_option( 'multi_institute_enable_university_header' );
		delete_option( 'multi_institute_university_logo' );
		delete_option( 'multi_institute_university_name' );
		delete_option( 'multi_institute_university_address' );
		delete_option( 'multi_institute_university_phone' );
		delete_option( 'multi_institute_university_email' );

		/* Delete user metadata */
		delete_metadata( 'user', 0, 'wlim_institute_id', '', true );

		WL_MIM_Database::activation();

		wp_send_json_success( array( 'message' => esc_html__( 'Plugin has been reset to its initial state.', WL_MIM_DOMAIN ) ) );
	}
}