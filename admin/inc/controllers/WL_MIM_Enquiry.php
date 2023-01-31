<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SMSHelper.php' );

class WL_MIM_Enquiry {
	/* Get enquiry data to display on table */
	public static function get_enquiry_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$follow_up = ( isset( $_REQUEST['follow_up'] ) && ! empty( $_REQUEST['follow_up'] ) ) ? sanitize_text_field( $_REQUEST['follow_up'] ) : '';

		if ( ! empty( $follow_up ) ) {
			$follow_up = new DateTime( $follow_up );

			if ( $follow_up ) {
				$follow_up = $follow_up->format('Y-m-d');
			}
		}

		if ( $follow_up ) {
			$where .= (' AND follow_up_date = "' . $follow_up . '"');
		} else {
			$where = '';
		}

		$data        = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND institute_id = $institute_id $where ORDER BY id DESC" );
		$course_data = $wpdb->get_results( "SELECT id, course_name, course_code FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );

		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id         = $row->id;
				$enquiry_id = WL_MIM_Helper::get_enquiry_id( $row->id );
				$reference  = $row->reference ? $row->reference : '-';
				$first_name = $row->first_name ? $row->first_name : '-';
				$last_name  = $row->last_name ? $row->last_name : '-';
				$phone      = $row->phone ? $row->phone : '-';
				$email      = $row->email ? $row->email : '-';
				$is_acitve  = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$added_by   = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
				$date       = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );

				$course = '-';
				if ( $row->course_id && isset( $course_data[ $row->course_id ] ) ) {
					$course_name = $course_data[ $row->course_id ]->course_name;
					$course_code = $course_data[ $row->course_id ]->course_code;
					$course      = "$course_name ($course_code)";
				}

				$results["data"][] = array(
					'<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html( $enquiry_id ),
					esc_html( $course ),
					esc_html( $first_name ),
					esc_html( $last_name ),
					esc_html( $phone ),
					esc_html( $email ),
					esc_html( $reference ),
					esc_html( $is_acitve ),
					esc_html( $added_by ),
					esc_html( $date ),
					'<a class="mr-3" href="#update-enquiry" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-enquiry-security="' . wp_create_nonce( "delete-enquiry-$id" ) . '"delete-enquiry-id="' . $id . '" class="delete-enquiry"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new enquiry */
	public static function add_enquiry() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-enquiry'], 'add-enquiry' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$course_id     = isset( $_POST['course'] ) ? intval( sanitize_text_field( $_POST['course'] ) ) : null;
		$reference     = isset( $_POST['reference'] ) ? sanitize_text_field( $_POST['reference'] ) : '';
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
		$is_active     = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;
		$custom_fields = ( isset( $_POST['custom_fields'] ) && is_array( $_POST['custom_fields'] ) ) ? $_POST['custom_fields'] : array();
		$follow_up_date= ( isset( $_POST['follow_up_date'] ) && ! empty( $_POST['follow_up_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['follow_up_date'] ) ) ) : null;
		$note          = isset( $_POST['note'] ) ? sanitize_textarea_field( $_POST['note'] ) : '';

		/* Validations */
		$errors = array();
		if ( empty( $course_id ) ) {
			$errors['course'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
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
				$errors['id_proof'] = esc_html__( 'Please provide ID Proof in PDF, JPG, JPEG or PNG format.', WL_MIM_DOMAIN );
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

		$course = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $course_id AND institute_id = $institute_id" );

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
					'reference'     => $reference,
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
					'is_active'     => $is_active,
					'added_by'      => get_current_user_id(),
					'institute_id'  => $institute_id,
					'custom_fields' => $custom_fields,
					'follow_up_date'=> $follow_up_date,
					'note'          => $note
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
					$template_id = $sms_template_enquiry_received['template_id'];
					$sms_message = str_replace( '[COURSE_NAME]', $course->course_name, $sms_message );
					$sms_message = str_replace( '[COURSE_CODE]', $course->course_code, $sms_message );

					/* Send SMS */
					WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_message, $phone, $template_id );
				}

				if ( $sms_template_enquiry_received_to_admin['enable'] ) {
					$sms_message = $sms_template_enquiry_received_to_admin['message'];
					$template_id = $sms_template_enquiry_received['template_id'];
					$sms_message = str_replace( '[COURSE_NAME]', $course->course_name, $sms_message );
					$sms_message = str_replace( '[COURSE_CODE]', $course->course_code, $sms_message );
					/* Send SMS to admin */
					$sms_admin_number = $sms['sms_admin_number'];
					$sms_admin_number = $sms['sms_admin_number'];
					if ( ! empty( $sms_admin_number ) ) {
						WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_message, $sms_admin_number, $template_id );
					}
				}

				wp_send_json_success( array( 'message' => esc_html__( 'Enquiry added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch enquiry to update */
	public static function fetch_enquiry() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		$custom_fields = unserialize( $row->custom_fields );

		$wlim_institute_active_categories = WL_MIM_Helper::get_active_categories_institute( $institute_id );
		$wlim_active_courses              = WL_MIM_Helper::get_active_courses();

		$course = $wpdb->get_row( "SELECT course_category_id FROM {$wpdb->prefix}wl_min_courses WHERE id = $row->course_id AND institute_id = $institute_id" );
		?><?php $nonce = wp_create_nonce( "update-enquiry-$id" );
		ob_start(); ?>
        <input type="hidden" name="update-enquiry-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-update-enquiry">
        <div class="row" id="wlim-enquiry-enquiry_id">
            <div class="col">
                <label class="col-form-label pb-0"><?php esc_html_e( 'Enquiry ID', WL_MIM_DOMAIN ); ?>:</label>
                <div class="card mb-3 mt-2">
                    <div class="card-block">
                        <span class="text-dark"><?php echo WL_MIM_Helper::get_enquiry_id( $row->id ); ?></span>
                    </div>
                </div>
            </div>
        </div>
		<?php
		if ( count( $wlim_institute_active_categories ) > 0 ) { ?>
            <div class="form-group">
                <label for="wlim-enquiry-category_update" class="col-form-label">* <?php esc_html_e( "Category", WL_MIM_DOMAIN ); ?>:</label>
                <select name="category" class="form-control" id="wlim-enquiry-category_update">
                    <option value="">-------- <?php esc_html_e( "Select a Category", WL_MIM_DOMAIN ); ?>--------</option>
					<?php
					foreach ( $wlim_institute_active_categories as $active_category ) { ?>
                        <option <?php selected( $course ? $course->course_category_id : '', $active_category->id, true ); ?> value="<?php echo esc_attr( $active_category->id ); ?>"><?php echo esc_html( $active_category->name ); ?></option>
						<?php
					} ?>
                </select>
            </div>
            <div id="wlim-fetch-category-courses_update">
                <div class="form-group">
                    <label for="wlim-enquiry-course_update" class="col-form-label">* <?php esc_html_e( "Admission For", WL_MIM_DOMAIN ); ?>:</label>
                    <select name="course" class="form-control selectpicker" id="wlim-enquiry-course_update">
                        <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?> --------</option>
						<?php
						if ( count( $wlim_active_courses ) > 0 ) {
							foreach ( $wlim_active_courses as $active_course ) { ?>
                                <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?></option>
								<?php
							}
						} ?>
                    </select>
                </div>
            </div>
			<?php
		} else { ?>
            <div id="wlim-fetch-category-courses_update">
                <div class="form-group">
                    <label for="wlim-enquiry-course_update" class="col-form-label">* <?php _e( "Admission For", WL_MIM_DOMAIN ); ?>:</label>
                    <select name="course" class="form-control selectpicker" id="wlim-enquiry-course_update">
                        <option value="">-------- <?php _e( "Select a Course", WL_MIM_DOMAIN ); ?> --------</option>
						<?php
						if ( count( $wlim_active_courses ) > 0 ) {
							foreach ( $wlim_active_courses as $active_course ) { ?>
                                <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?></option>
								<?php
							}
						} ?>
                    </select>
                </div>
            </div>
			<?php
		} ?>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-first_name_update" class="col-form-label">* <?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?>:</label>
                <input name="first_name" type="text" class="form-control" id="wlim-enquiry-first_name_update" placeholder="<?php esc_html_e( "First Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->first_name ); ?>">
            </div>
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-last_name_update" class="col-form-label"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?>:</label>
                <input name="last_name" type="text" class="form-control" id="wlim-enquiry-last_name_update" placeholder="<?php esc_html_e( "Last Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->last_name ); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label class="col-form-label">* <?php esc_html_e( 'Gender', WL_MIM_DOMAIN ); ?>:</label><br>
                <div class="row mt-2">
                    <div class="col-sm-12">
                        <label class="radio-inline mr-3"><input type="radio" name="gender" class="mr-2" value="male" id="wlim-enquiry-male_update"><?php esc_html_e( 'Male', WL_MIM_DOMAIN ); ?></label>
                        <label class="radio-inline"><input type="radio" name="gender" class="mr-2" value="female" id="wlim-enquiry-female_update"><?php esc_html_e( 'Female', WL_MIM_DOMAIN ); ?></label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-date_of_birth_update" class="col-form-label">* <?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?>:</label>
                <input name="date_of_birth" type="text" class="form-control wlim-date_of_birth_update" id="wlim-enquiry-date_of_birth_update" placeholder="<?php esc_html_e( "Date of Birth", WL_MIM_DOMAIN ); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-father_name_update" class="col-form-label"><?php esc_html_e( "Father's Name", WL_MIM_DOMAIN ); ?>:</label>
                <input name="father_name" type="text" class="form-control" id="wlim-enquiry-father_name_update" placeholder="<?php esc_html_e( "Father's Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->father_name ); ?>">
            </div>
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-mother_name_update" class="col-form-label"><?php esc_html_e( "Mother's Name", WL_MIM_DOMAIN ); ?>:</label>
                <input name="mother_name" type="text" class="form-control" id="wlim-enquiry-mother_name_update" placeholder="<?php esc_html_e( "Mother's Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->mother_name ); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-address_update" class="col-form-label"><?php esc_html_e( 'Address', WL_MIM_DOMAIN ); ?> :</label>
                <textarea name="address" class="form-control" rows="4" id="wlim-enquiry-address_update" placeholder="<?php esc_html_e( "Address", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->address ); ?></textarea>
            </div>
            <div class="col-sm-6 form-group">
                <div>
                    <label for="wlim-enquiry-city_update" class="col-form-label"><?php esc_html_e( 'City', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="city" type="text" class="form-control" id="wlim-enquiry-city_update" placeholder="<?php esc_html_e( "City", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->city ); ?>">
                </div>
                <div>
                    <label for="wlim-enquiry-zip_update" class="col-form-label"><?php esc_html_e( 'Zip Code', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="zip" type="text" class="form-control" id="wlim-enquiry-zip_update" placeholder="<?php esc_html_e( "Zip Code", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->zip ); ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-state_update" class="col-form-label"><?php esc_html_e( 'State', WL_MIM_DOMAIN ); ?>:</label>
                <input name="state" type="text" class="form-control" id="wlim-enquiry-state_update" placeholder="<?php esc_html_e( "State", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->state ); ?>">
            </div>
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-nationality_update" class="col-form-label"><?php esc_html_e( 'Nationality', WL_MIM_DOMAIN ); ?>:</label>
                <input name="nationality" type="text" class="form-control" id="wlim-enquiry-nationality_update" placeholder="<?php esc_html_e( "Nationality", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->nationality ); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-phone_update" class="col-form-label">* <?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?>:</label>
                <input name="phone" type="text" class="form-control" id="wlim-enquiry-phone_update" placeholder="<?php esc_html_e( "Phone", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->phone ); ?>">
            </div>
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-email_update" class="col-form-label"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?>:</label>
                <input name="email" type="text" class="form-control" id="wlim-enquiry-email_update" placeholder="<?php esc_html_e( "Email", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->email ); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-qualification_update" class="col-form-label"><?php esc_html_e( 'Qualification', WL_MIM_DOMAIN ); ?>:</label>
                <input name="qualification" type="text" class="form-control" id="wlim-enquiry-qualification_update" placeholder="<?php esc_html_e( "Qualification", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->qualification ); ?>">
            </div>
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-id_proof_update" class="col-form-label"><?php esc_html_e( 'ID Proof', WL_MIM_DOMAIN ); ?>:</label><br>
				<?php if ( ! empty ( $row->id_proof ) ) { ?>
                    <a href="<?php echo wp_get_attachment_url( $row->id_proof ); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><?php esc_html_e( 'View ID Proof', WL_MIM_DOMAIN ); ?></a>
                    <input type="hidden" name="id_proof_in_db" value="<?php echo esc_attr( $row->id_proof ); ?>">
				<?php } ?>
                <input name="id_proof" type="file" id="wlim-enquiry-id_proof_update">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-photo_update" class="col-form-label"><?php esc_html_e( 'Photo', WL_MIM_DOMAIN ); ?>:</label><br>
				<?php if ( ! empty ( $row->photo_id ) ) { ?>
                    <img src="<?php echo wp_get_attachment_url( $row->photo_id ); ?>" class="img-responsive photo-signature">
                    <input type="hidden" name="photo_in_db" value="<?php echo esc_attr( $row->photo_id ); ?>">
				<?php } ?>
                <input name="photo" type="file" id="wlim-enquiry-photo_update">
            </div>
            <div class="col-sm-6 form-group">
                <label for="wlim-enquiry-signature_update" class="col-form-label"><?php esc_html_e( 'Signature', WL_MIM_DOMAIN ); ?>:</label><br>
				<?php if ( ! empty ( $row->signature_id ) ) { ?>
                    <img src="<?php echo wp_get_attachment_url( $row->signature_id ); ?>" class="img-responsive photo-signature">
                    <input type="hidden" name="signature_in_db" value="<?php echo esc_attr( $row->signature_id ); ?>">
				<?php } ?>
                <input name="signature" type="file" id="wlim-enquiry-signature_update">
            </div>
        </div>
		<?php
		if ( isset( $custom_fields['name'] ) && is_array( $custom_fields['name'] ) && count( $custom_fields['name'] ) ) { ?>
            <div class="row">
				<?php
				foreach ( $custom_fields['name'] as $key => $custom_field_name ) { ?>
                    <div class="col-sm-6 form-group">
                        <label for="wlim-enquiry-custom_fields_<?php echo esc_attr( $key ); ?>_update" class="col-form-label"><?php echo esc_html( $custom_field_name ); ?>:</label>
                        <input type="hidden" name="custom_fields[name][]" value="<?php echo esc_attr( $custom_field_name ); ?>">
                        <input name="custom_fields[value][]" type="text" class="form-control" id="wlim-enquiry-custom_fields_<?php echo esc_attr( $key ); ?>_update" placeholder="<?php echo esc_attr( $custom_field_name ); ?>" value="<?php echo esc_attr( $custom_fields['value'][ $key ] ); ?>">
                    </div>
					<?php
				} ?>
            </div>
			<?php
		} ?>
        <div class="form-group">
            <label for="wlim-enquiry-message_update" class="col-form-label"><?php esc_html_e( 'Message', WL_MIM_DOMAIN ); ?>:</label>
            <textarea name="message" class="form-control" rows="3" id="wlim-enquiry-message_update" placeholder="<?php esc_html_e( "Message", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->message ); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6">
				<div class="form-group">
					<label for="wlim-enquiry-follow_up_date" class="col-form-label"><?php esc_html_e( 'Follow Up Date', WL_MIM_DOMAIN ); ?>:</label>
					<input name="follow_up_update_date" type="text" class="form-control wlim-enquiry-follow_up_date" id="wlim-enquiry-follow_up_date" value="<?php echo esc_attr( $row->follow_up_date ); ?>"> 
				</div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="wlim-enquiry-reference" class="col-form-label"><?php esc_html_e( "Reference", WL_MIM_DOMAIN ); ?>:</label>
                    <input name="reference" type="text" class="form-control" id="wlim-enquiry-reference" placeholder="<?php esc_html_e( "Reference", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->reference ); ?>">
                </div>
            </div>
        </div>

		<div class="form-group">
			<label for="wlim-enquiry-note" class="col-form-label"><?php esc_html_e( 'Note', WL_MIM_DOMAIN ); ?>:</label>
			<textarea name="note" class="form-control" rows="3" id="wlim-enquiry-note_update" placeholder="<?php esc_html_e( "Note", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->note ); ?></textarea>
		</div>

        <div class="form-check pl-0">
            <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-enquiry-is_active_update" <?php echo boolval( $row->is_active ) ? "checked" : ""; ?>>
            <label class="form-check-label" for="wlim-enquiry-is_active_update"><?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?></label>
        </div>
		<input type="hidden" name="enquiry_id" value="<?php echo esc_attr( $row->id ); ?>">
		<?php $html         = ob_get_clean();
		$wlim_date_selector = '.wlim-date_of_birth_update';
		$wlim_follow_selector = '.wlim-enquiry-follow_up_date';
	

		$json = json_encode( array(
			'wlim_date_selector'  => esc_attr( $wlim_date_selector ),
			'wlim_follow_selector'=> esc_attr( $wlim_follow_selector ),
			'course_id'           => esc_attr( $row->course_id ),
			'gender'              => esc_attr( $row->gender ),
			'date_of_birth_exist' => boolval( $row->date_of_birth ),
			'date_of_birth'       => esc_attr( $row->date_of_birth )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update enquiry */
	public static function update_enquiry() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['enquiry_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-enquiry-$id"], "update-enquiry-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$course_id       = isset( $_POST['course'] ) ? intval( sanitize_text_field( $_POST['course'] ) ) : null;
		$reference       = isset( $_POST['reference'] ) ? sanitize_text_field( $_POST['reference'] ) : '';
		$first_name      = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
		$last_name       = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
		$gender          = isset( $_POST['gender'] ) ? sanitize_text_field( $_POST['gender'] ) : '';
		$date_of_birth   = ( isset( $_POST['date_of_birth'] ) && ! empty( $_POST['date_of_birth'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['date_of_birth'] ) ) ) : null;
		$id_proof        = ( isset( $_FILES['id_proof'] ) && is_array( $_FILES['id_proof'] ) ) ? $_FILES['id_proof'] : null;
		$id_proof_in_db  = isset( $_POST['id_proof_in_db'] ) ? intval( sanitize_text_field( $_POST['id_proof_in_db'] ) ) : null;
		$father_name     = isset( $_POST['father_name'] ) ? sanitize_text_field( $_POST['father_name'] ) : '';
		$mother_name     = isset( $_POST['mother_name'] ) ? sanitize_text_field( $_POST['mother_name'] ) : '';
		$address         = isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '';
		$city            = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
		$zip             = isset( $_POST['zip'] ) ? sanitize_text_field( $_POST['zip'] ) : '';
		$state           = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
		$nationality     = isset( $_POST['nationality'] ) ? sanitize_text_field( $_POST['nationality'] ) : '';
		$phone           = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$qualification   = isset( $_POST['qualification'] ) ? sanitize_text_field( $_POST['qualification'] ) : '';
		$email           = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
		$photo           = ( isset( $_FILES['photo'] ) && is_array( $_FILES['photo'] ) ) ? $_FILES['photo'] : null;
		$photo_in_db     = isset( $_POST['photo_in_db'] ) ? intval( sanitize_text_field( $_POST['photo_in_db'] ) ) : null;
		$signature       = ( isset( $_FILES['signature'] ) && is_array( $_FILES['signature'] ) ) ? $_FILES['signature'] : null;
		$signature_in_db = isset( $_POST['signature_in_db'] ) ? intval( sanitize_text_field( $_POST['signature_in_db'] ) ) : null;
		$message         = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
		$is_active       = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;
		$custom_fields   = ( isset( $_POST['custom_fields'] ) && is_array( $_POST['custom_fields'] ) ) ? $_POST['custom_fields'] : array();
		$follow_up_date  = ( isset( $_POST['follow_up_update_date'] ) && ! empty( $_POST['follow_up_update_date'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['follow_up_update_date'] ) ) ) : null;
		
		$note            = isset( $_POST['note'] ) ? sanitize_textarea_field( $_POST['note'] ) : '';

		/* Validations */
		$errors = array();
		if ( empty( $course_id ) ) {
			$errors['course'] = esc_html__( 'Please select a course.', WL_MIM_DOMAIN );
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
				$errors['id_proof'] = esc_html__( 'Please provide ID Proof in PDF, JPG, JPEG or PNG format.', WL_MIM_DOMAIN );
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

		$enquiry = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $enquiry ) {
			die();
		}

		if ( ! empty( $custom_fields ) ) {
			if ( ! array_key_exists( 'name', $custom_fields ) || ! array_key_exists( 'value', $custom_fields ) ) {
				wp_send_json_error( esc_html__( 'Invalid field.', WL_MIM_DOMAIN ) );
			} elseif ( ! is_array( $custom_fields['name'] ) || ! is_array( $custom_fields['value'] ) ) {
				wp_send_json_error( esc_html__( 'Invalid field.', WL_MIM_DOMAIN ) );
			} elseif ( count( $custom_fields['name'] ) != count( $custom_fields['value'] ) ) {
				wp_send_json_error( esc_html__( 'Invalid field.', WL_MIM_DOMAIN ) );
			} else {
				$custom_field_name_data = array();
				$custom_fields_data     = unserialize( $enquiry->custom_fields );
				$custom_field_name_data = isset( $custom_fields_data['name'] ) ? $custom_fields_data['name'] : array();
				foreach ( $custom_fields['name'] as $key => $field_name ) {
					$custom_fields['name'][ $key ]  = sanitize_text_field( $field_name );
					$custom_fields['value'][ $key ] = sanitize_text_field( $custom_fields['value'][ $key ] );
				}
				if ( $custom_fields['name'] !== $custom_field_name_data ) {
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

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND id = $course_id AND institute_id = $institute_id" );

		if ( ! $count ) {
			$errors['course'] = esc_html__( 'Please select a valid course.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$custom_fields = serialize( $custom_fields );

				$data = array(
					'course_id'     => $course_id,
					'reference'     => $reference,
					'first_name'    => $first_name,
					'last_name'     => $last_name,
					'gender'        => $gender,
					'date_of_birth' => $date_of_birth,
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
					'message'       => $message,
					'is_active'     => $is_active,
					'custom_fields' => $custom_fields,
					'updated_at'    => date( 'Y-m-d H:i:s' ),
					'follow_up_date'=> $follow_up_date,
					'note'          => $note,
				);

				if ( ! empty( $id_proof ) ) {
					$id_proof = media_handle_upload( 'id_proof', 0 );
					if ( is_wp_error( $id_proof ) ) {
						throw new Exception( esc_html__( $id_proof->get_error_message(), WL_MIM_DOMAIN ) );
					}
					$data['id_proof'] = $id_proof;
				}

				if ( ! empty( $photo ) ) {
					$photo = media_handle_upload( 'photo', 0 );
					if ( is_wp_error( $photo ) ) {
						throw new Exception( esc_html__( $photo->get_error_message(), WL_MIM_DOMAIN ) );
					}
					$data['photo_id'] = $photo;
				}

				if ( ! empty( $signature ) ) {
					$signature = media_handle_upload( 'signature', 0 );
					if ( is_wp_error( $signature ) ) {
						throw new Exception( esc_html__( $signature->get_error_message(), WL_MIM_DOMAIN ) );
					}
					$data['signature_id'] = $signature;
				}

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_enquiries", $data, array(
					'is_deleted'   => 0,
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );

				if ( ! empty( $id_proof ) && ! empty( $id_proof_in_db ) ) {
					wp_delete_attachment( $id_proof_in_db, true );
				}

				if ( ! empty( $photo ) && ! empty( $photo_in_db ) ) {
					wp_delete_attachment( $photo_in_db, true );
				}

				if ( ! empty( $signature ) && ! empty( $signature_in_db ) ) {
					wp_delete_attachment( $signature_in_db, true );
				}

				wp_send_json_success( array( 'message' => esc_html__( 'Enquiry updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete enquiry */
	public static function delete_enquiry() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-enquiry-$id"], "delete-enquiry-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->update( "{$wpdb->prefix}wl_min_enquiries",
				array(
					'is_deleted' => 1,
					'deleted_at' => date( 'Y-m-d H:i:s' )
				), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
			);
			if ( ! $success ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Enquiry removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
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
		ob_start();
		?>
        <div class="form-group">
            <label for="wlim-enquiry-course" class="col-form-label">* <?php esc_html_e( "Admission For", WL_MIM_DOMAIN ); ?>:</label>
            <select name="course" class="form-control" id="wlim-enquiry-course">
                <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?>--------</option>
				<?php
				if ( count( $wlim_institute_active_courses ) > 0 ) {
					foreach ( $wlim_institute_active_courses as $active_course ) { ?>
                        <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( $active_course->course_name ) . " (" . esc_html( $active_course->course_code ) . ")"; ?></option>
						<?php
					}
				} ?>
            </select>
        </div>
		<?php $html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/* Fetch category courses update */
	public static function fetch_category_courses_update() {
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
		ob_start();
		?>
        <div class="form-group">
            <label for="wlim-enquiry-course_update" class="col-form-label">* <?php esc_html_e( "Admission For", WL_MIM_DOMAIN ); ?>:</label>
            <select name="course" class="form-control" id="wlim-enquiry-course_update">
                <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?>--------</option>
				<?php
				if ( count( $wlim_institute_active_courses ) > 0 ) {
					foreach ( $wlim_institute_active_courses as $active_course ) { ?>
                        <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( $active_course->course_name ) . " (" . esc_html( $active_course->course_code ) . ")"; ?></option>
						<?php
					}
				} ?>
            </select>
        </div>
		<?php $html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/* Check permission to manage enquiry */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_enquiries' ) || ! $institute_id ) {
			die();
		}
	}
}
?>