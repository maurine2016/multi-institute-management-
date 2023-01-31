<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SMSHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

class WL_MIM_Enquiry_Front {
	/* Add new enquiry */
	public static function add_enquiry() {
		if ( ! wp_verify_nonce( $_POST['add-enquiry'], 'add-enquiry' ) ) {
			die();
		}
		global $wpdb;

		$institute_id  = isset( $_POST['institute'] ) ? intval( sanitize_text_field( $_POST['institute'] ) ) : null;
		$course_id     = isset( $_POST['course'] ) ? intval( sanitize_text_field( $_POST['course'] ) ) : null;
		$first_name    = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name     = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$gender        = isset( $_POST['gender'] ) ? sanitize_text_field( $_POST['gender'] ) : '';
		$date_of_birth = ( isset( $_POST['date_of_birth'] ) && ! empty( $_POST['date_of_birth'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['date_of_birth'] ) ) ) : null;
		$id_proof      = ( isset( $_FILES['id_proof'] ) && is_array( $_FILES['id_proof'] ) ) ? $_FILES['id_proof'] : null;
		$father_name   = isset( $_POST['father_name'] ) ? sanitize_text_field( $_POST['father_name'] ) : '';
		$mother_name   = isset( $_POST['mother_name'] ) ? sanitize_text_field( $_POST['mother_name'] ) : '';
		$address       = isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '';
		$city          = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
		$zip           = isset( $_POST['zip'] ) ? sanitize_text_field( $_POST['zip'] ) : '';
		$state         = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
		$nationality   = isset( $_POST['nationality'] ) ? sanitize_text_field( $_POST['nationality'] ) : '';
		$phone         = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$qualification = isset( $_POST['qualification'] ) ? sanitize_text_field( $_POST['qualification'] ) : '';
		$email         = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
		$photo         = ( isset( $_FILES['photo'] ) && is_array( $_FILES['photo'] ) ) ? $_FILES['photo'] : null;
		$signature     = ( isset( $_FILES['signature'] ) && is_array( $_FILES['signature'] ) ) ? $_FILES['signature'] : null;
		$message       = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
		$custom_fields = ( isset( $_POST['custom_fields'] ) && is_array( $_POST['custom_fields'] ) ) ? $_POST['custom_fields'] : array();

		/* Validations */
		$errors = array();
		if ( empty( $institute_id ) ) {
			$errors['institute'] = esc_html__( 'Please select institute.', WL_MIM_DOMAIN );
			wp_send_json_error( $errors );
		}

		if ( empty( $course_id ) ) {
			$errors['course'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
			wp_send_json_error( $errors );
		}

		if ( empty( $first_name ) ) {
			$errors['first_name'] = esc_html__( 'Please provide first name.', WL_MIM_DOMAIN );
		}

		if ( strlen( $first_name ) > 255 ) {
			$errors['first_name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $last_name ) > 255 ) {
			$errors['last_name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( ! in_array( $gender, WL_MIM_Helper::get_gender_data() ) ) {
			throw new Exception( esc_html__( 'Please select valid gender.', WL_MIM_DOMAIN ) );
		}

		if ( empty( $date_of_birth ) ) {
			$errors['date_of_birth'] = esc_html__( 'Please provide date of birth.', WL_MIM_DOMAIN );
		}

		if ( ! empty( $date_of_birth ) && ( strtotime( date( 'Y' ) - 2 ) <= strtotime( $date_of_birth ) ) ) {
			$errors['date_of_birth'] = esc_html__( 'Please provide valid date of birth.', WL_MIM_DOMAIN );
		}

		if ( ! empty( $id_proof ) ) {
			$file_name          = sanitize_file_name( $id_proof['name'] );
			$file_type          = $id_proof['type'];
			$allowed_file_types = WL_MIM_Helper::get_id_proof_file_types();

			if ( ! in_array( $file_type, $allowed_file_types ) ) {
				$errors['id_proof'] = esc_html__( 'Please provide Aadhaar ID in PDF, JPG, JPEG or PNG format.', WL_MIM_DOMAIN );
			}
		}

		if ( strlen( $father_name ) > 255 ) {
			$errors['father_name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $mother_name ) > 255 ) {
			$errors['mother_name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $city ) > 255 ) {
			$errors['city'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $zip ) > 255 ) {
			$errors['zip'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $state ) > 255 ) {
			$errors['state'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $nationality ) > 255 ) {
			$errors['nationality'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( empty( $phone ) ) {
			$errors['phone'] = esc_html__( 'Please provide phone number.', WL_MIM_DOMAIN );
		}

		if ( strlen( $phone ) > 255 ) {
			$errors['phone'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $qualification ) > 255 ) {
			$errors['qualification'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $email ) > 255 ) {
			$errors['email'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( ! empty( $email ) && ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$errors['email'] = esc_html__( 'Please provide a valid email address.', WL_MIM_DOMAIN );
		}

		if ( ! empty( $custom_fields ) ) {
			if ( ! array_key_exists( 'name', $custom_fields ) || ! array_key_exists( 'value', $custom_fields ) ) {
				wp_send_json_error( esc_html__( 'Invalid field.', WL_MIM_DOMAIN ) );
			} elseif ( ! is_array( $custom_fields['name'] ) || ! is_array( $custom_fields['value'] ) ) {
				wp_send_json_error( esc_html__( 'Invalid field.', WL_MIM_DOMAIN ) );
			} elseif ( count( $custom_fields['name'] ) != count( $custom_fields['value'] ) ) {
				wp_send_json_error( esc_html__( 'Invalid field.', WL_MIM_DOMAIN ) );
			} else {
				$custom_fields_data     = WL_MIM_Helper::get_active_custom_fields_institute( $institute_id );
				$custom_field_name_data = array();
				foreach ( $custom_fields_data as $custom_field_data ) {
					array_push( $custom_field_name_data, $custom_field_data->field_name );
				}
				foreach ( $custom_fields['name'] as $key => $field_name ) {
					$custom_fields['name'][ $key ]  = sanitize_text_field( $field_name );
					$custom_fields['value'][ $key ] = sanitize_text_field( $custom_fields['value'][ $key ] );
				}
				if ( ! array_intersect( $custom_fields['name'], $custom_field_name_data ) == $custom_fields['name'] ) {
					wp_send_json_error( esc_html__( 'Invalid field.', WL_MIM_DOMAIN ) );
				}
			}
		}

		if ( ! empty( $photo ) ) {
			$file_name          = sanitize_file_name( $photo['name'] );
			$file_type          = $photo['type'];
			$allowed_file_types = WL_MIM_Helper::get_image_file_types();

			if ( ! in_array( $file_type, $allowed_file_types ) ) {
				$errors['photo'] = esc_html__( 'Please provide photo in JPG, JPEG or PNG format.', WL_MIM_DOMAIN );
			}
		}

		if ( ! empty( $signature ) ) {
			$file_name          = sanitize_file_name( $signature['name'] );
			$file_type          = $signature['type'];
			$allowed_file_types = WL_MIM_Helper::get_image_file_types();
			if ( ! in_array( $file_type, $allowed_file_types ) ) {
				$errors['signature'] = esc_html__( 'Please provide signature in JPG, JPEG or PNG format.', WL_MIM_DOMAIN );
			}
		}

		$course = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $course_id AND institute_id = $institute_id" );

		if ( ! $course ) {
			$errors['course'] = esc_html__( 'Please select a valid course', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );
				if ( ! empty( $id_proof ) ) {
					$id_proof = media_handle_upload( 'id_proof', 0 );
					if ( is_wp_error( $id_proof ) ) {
						throw new Exception( esc_html__( $id_proof->get_error_message(), WL_MIM_DOMAIN ) );
					}
				}

				if ( ! empty( $photo ) ) {
					$photo = media_handle_upload( 'photo', 0 );
					if ( is_wp_error( $photo ) ) {
						throw new Exception( esc_html__( $photo->get_error_message(), WL_MIM_DOMAIN ) );
					}
				}

				if ( ! empty( $signature ) ) {
					$signature = media_handle_upload( 'signature', 0 );
					if ( is_wp_error( $signature ) ) {
						throw new Exception( esc_html__( $signature->get_error_message(), WL_MIM_DOMAIN ) );
					}
				}

				$custom_fields = serialize( $custom_fields );
				$data = array(
					'course_id'     => $course_id,
					'first_name'    => $first_name,
					'last_name'     => $last_name,
					'gender'        => $gender,
					'date_of_birth' => $date_of_birth,
					'id_proof'      => $id_proof,
					'father_name'   => $father_name,
					'mother_name'   => $mother_name,
					'address'       => $address,
					'city'          => $city,
					'zip'           => $zip,
					'state'         => $state,
					'nationality'   => $nationality,
					'phone'         => $phone,
					'qualification' => $qualification,
					'email'         => $email,
					'photo_id'      => $photo,
					'signature_id'  => $signature,
					'message'       => $message,
					'is_active'     => 1,
					'institute_id'  => $institute_id,
					'custom_fields' => $custom_fields
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_enquiries", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				/* Get SMS template */
				$sms_template_enquiry_received          = WL_MIM_SettingHelper::get_sms_template_enquiry_received( $institute_id );
				$sms_template_enquiry_received_to_admin = WL_MIM_SettingHelper::get_sms_template_enquiry_received_to_admin( $institute_id );

				/* Get SMS settings */
				$sms = WL_MIM_SettingHelper::get_sms_settings( $institute_id );

				if ( $sms_template_enquiry_received['enable'] ) {
					$sms_message = $sms_template_enquiry_received['message'];
					$sms_message = str_replace( '[COURSE_NAME]', $course->course_name, $sms_message );
					$sms_message = str_replace( '[COURSE_CODE]', $course->course_code, $sms_message );
					/* Send SMS */
					WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_message, $phone );
				}

				if ( $sms_template_enquiry_received_to_admin['enable'] ) {
					$sms_message = $sms_template_enquiry_received_to_admin['message'];
					$sms_message = str_replace( '[COURSE_NAME]', $course->course_name, $sms_message );
					$sms_message = str_replace( '[COURSE_CODE]', $course->course_code, $sms_message );
					/* Send SMS to admin */
					$sms_admin_number = $sms['sms_admin_number'];
					if ( ! empty( $sms_admin_number ) ) {
						WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_message, $sms_admin_number );
					}
				}

				wp_send_json_success( array( 'message' => esc_html__( 'Your enquiry has been received. Thank you.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch institute's categories */
	public static function fetch_institute_categories() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = intval( sanitize_text_field( $_POST['id'] ) );

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id AND is_active = '1'" );
		if ( ! $row ) {
			die();
		}

		$wlim_institute_active_categories = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}wl_min_course_categories WHERE is_active = 1 AND institute_id = $institute_id ORDER BY name" );
		?><?php
		if ( count( $wlim_institute_active_categories ) > 0 ) { ?>
            <div class="form-group">
                <label for="wlim-enquiry-category" class="col-form-label">* <?php esc_html_e( "Category", WL_MIM_DOMAIN ); ?>:</label>
                <select name="category" class="form-control" id="wlim-enquiry-category">
                    <option value="">-------- <?php esc_html_e( "Select a Category", WL_MIM_DOMAIN ); ?>--------</option>
					<?php
					if ( count( $wlim_institute_active_categories ) > 0 ) {
						foreach ( $wlim_institute_active_categories as $active_category ) { ?>
                            <option value="<?php echo esc_attr( $active_category->id ); ?>"><?php echo esc_html( $active_category->name ); ?></option>
							<?php
						}
					} ?>
                </select>
            </div>
            <div id="wlim-fetch-category-courses"></div>
		<?php } else {
			$wlim_institute_active_courses = $wpdb->get_results( "SELECT id, course_name, fees, course_code FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY course_name" ); ?>
            <div class="form-group">
                <label for="wlim-enquiry-course" class="col-form-label">* <?php esc_html_e( "Admission For", WL_MIM_DOMAIN ); ?>:</label>
                <select name="course" class="form-control" id="wlim-enquiry-course">
                    <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?>--------</option>
					<?php
					if ( count( $wlim_institute_active_courses ) > 0 ) {
						foreach ( $wlim_institute_active_courses as $active_course ) { ?>
                            <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?></option>
							<?php
						}
					} ?>
                </select>
            </div>
			<?php
		}
		die();
	}

	/* Fetch category courses */
	public static function fetch_category_courses() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$category_id = intval( sanitize_text_field( $_POST['id'] ) );

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_course_categories WHERE id = $category_id AND is_active = '1'" );
		if ( ! $row ) {
			die();
		}

		$wlim_institute_active_courses = $wpdb->get_results( "SELECT id, course_name, fees, course_code FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND course_category_id = $category_id ORDER BY course_name" );
		?>
        <div class="form-group">
            <label for="wlim-enquiry-course" class="col-form-label">* <?php esc_html_e( "Admission For", WL_MIM_DOMAIN ); ?>:</label>
            <select name="course" class="form-control" id="wlim-enquiry-course">
                <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?>--------</option>
				<?php
				if ( count( $wlim_institute_active_courses ) > 0 ) {
					foreach ( $wlim_institute_active_courses as $active_course ) { ?>
                        <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?></option>
						<?php
					}
				} ?>
            </select>
        </div>
		<?php
		die();
	}

	/* Fetch institute's courses */
	public static function fetch_institute_courses() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = intval( sanitize_text_field( $_POST['id'] ) );

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id AND is_active = '1'" );
		if ( ! $row ) {
			die();
		}

		$wlim_institute_active_courses = $wpdb->get_results( "SELECT id, course_name, fees, course_code FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY course_name" );
		?>
        <div class="form-group">
            <label for="wlim-enquiry-course" class="col-form-label">* <?php esc_html_e( "Admission For", WL_MIM_DOMAIN ); ?>:</label>
            <select name="course" class="form-control" id="wlim-enquiry-course">
                <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?>--------</option>
				<?php
				if ( count( $wlim_institute_active_courses ) > 0 ) {
					foreach ( $wlim_institute_active_courses as $active_course ) { ?>
                        <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?></option>
						<?php
					}
				} ?>
            </select>
        </div>
		<?php
		die();
	}

	/* Fetch institute's custom fields */
	public static function fetch_institute_custom_fields() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = intval( sanitize_text_field( $_POST['id'] ) );

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id AND is_active = '1'" );
		if ( ! $row ) {
			die();
		}

		$custom_fields = $wpdb->get_results( "SELECT field_name FROM {$wpdb->prefix}wl_min_custom_fields WHERE is_active = 1 AND institute_id = $institute_id" );
		if ( count( $custom_fields ) ) { ?>
            <div class="row">
				<?php foreach ( $custom_fields as $key => $custom_field ) { ?>
                    <div class="col-sm-6 form-group">
                        <label for="wlim-enquiry-custom_fields_<?php echo esc_attr( $key ); ?>" class="col-form-label"><?php echo esc_html( $custom_field->field_name ); ?>:</label>
                        <input type="hidden" name="custom_fields[name][]" value="<?php echo esc_attr( $custom_field->field_name ); ?>">
                        <input name="custom_fields[value][]" type="text" class="form-control" id="wlim-enquiry-custom_fields_<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $custom_field->field_name ); ?>">
                    </div>
				<?php } ?>
            </div>
		<?php
		}
		die();
	}
}
?>