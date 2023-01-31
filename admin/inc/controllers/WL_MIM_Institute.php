<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

class WL_MIM_Institute {
	/* Get institute data to display on table */
	public static function get_institute_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_institutes ORDER BY id DESC" );
		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id                  = $row->id;
				$name                = $row->name;
				$registration_number = $row->registration_number ? $row->registration_number : '-';
				$address             = $row->address ? $row->address : '-';
				$phone               = $row->phone ? $row->phone : '-';
				$email               = $row->email ? $row->email : '-';
				$contact_person      = $row->contact_person;
				$extra_details       = $row->extra_details ? substr( $row->extra_details, 0, 50 ) : '-';
				$is_acitve           = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$added_on            = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );

				$enquiry_shortcode = '<span>[institute_admission_enquiry_form id=' . $id . ']</span>';
				$result_shortcode  = '<span>[institute_exam_result id=' . $id . ']</span>';

				$results["data"][] = array(
					esc_html( $name ),
					esc_html( $registration_number ),
					esc_html( $address ),
					esc_html( $phone ),
					esc_html( $email ),
					esc_html( $contact_person ),
					$enquiry_shortcode,
					$result_shortcode,
					esc_html( $is_acitve ),
					esc_html( $added_on ),
					esc_html( $extra_details ),
					'<a class="mr-3" href="#update-institute" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-institute-security="' . wp_create_nonce( "delete-institute-$id" ) . '"delete-institute-id="' . $id . '" class="delete-institute"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Set institute */
	public static function set_institute() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['set-institute'], 'set-institute' ) ) {
			die();
		}
		global $wpdb;

		$institute_id = isset( $_POST['institute'] ) ? intval( sanitize_text_field( $_POST['institute'] ) ) : null;

		/* Validations */
		$errors = array();
		if ( empty( $institute_id ) ) {
			$errors['institute'] = esc_html__( 'Please select institute.', WL_MIM_DOMAIN );
		}

		$institute = $wpdb->get_row( "SELECT id, name FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
		if ( ! $institute ) {
			$errors['institute'] = esc_html__( 'Please select valid institute.', WL_MIM_DOMAIN );
		}

		if ( count( $errors ) < 1 ) {
			update_user_meta( get_current_user_id(), 'wlim_institute_id', $institute->id );
			wp_send_json_success( array(
				'message' => esc_html__( 'Current institute is set to', WL_MIM_DOMAIN ) . "<br>$institute->name",
				'reload'  => true,
				'url'     => admin_url() . 'admin.php?page=multi-institute-management'
			) );
		}
		wp_send_json_error( $errors );
	}

	/* Add new institute */
	public static function add_institute() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-institute'], 'add-institute' ) ) {
			die();
		}
		global $wpdb;

		$name                = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$registration_number = isset( $_POST['registration_number'] ) ? sanitize_text_field( $_POST['registration_number'] ) : '';
		$address             = isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '';
		$phone               = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$email               = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$contact_person      = isset( $_POST['contact_person'] ) ? sanitize_text_field( $_POST['contact_person'] ) : '';
		$extra_details       = isset( $_POST['extra_details'] ) ? sanitize_textarea_field( $_POST['extra_details'] ) : '';
		$is_active           = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;
		$courses             = ( isset( $_POST['course'] ) && is_array( $_POST['course'] ) ) ? array_map( 'absint', $_POST['course'] ) : array();
		$can_add_course      = isset( $_POST['can_add_course'] ) ? boolval( sanitize_text_field( $_POST['can_add_course'] ) ) : 0;
		$can_delete_course   = isset( $_POST['can_delete_course'] ) ? boolval( sanitize_text_field( $_POST['can_delete_course'] ) ) : 0;
		$can_update_course   = isset( $_POST['can_update_course'] ) ? boolval( sanitize_text_field( $_POST['can_update_course'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $name ) ) {
			$errors['name'] = esc_html__( 'Please provide institute name.', WL_MIM_DOMAIN );
		}

		if ( strlen( $name ) > 255 ) {
			$errors['name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( ! empty( $registration_number ) ) {
			$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_institutes WHERE registration_number = '$registration_number'" );

			if ( $count ) {
				$errors['registration_number'] = esc_html__( 'Already exists.', WL_MIM_DOMAIN );
			}
		}

		$courses_to_assign = array();

		foreach ( $courses as $course_id ) {
			// Get main course detail.
			$main_course = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_main_courses WHERE id = $course_id" );
			if ( $main_course ) {
				$course_to_assign = array();

				$course_to_assign['course_code']   = $main_course->course_code;
				$course_to_assign['course_name']   = $main_course->course_name;
				$course_to_assign['course_detail'] = $main_course->course_detail;
				$course_to_assign['duration']      = $main_course->duration;
				$course_to_assign['duration_in']   = $main_course->duration_in;
				$course_to_assign['fees']          = $main_course->fees;
				$course_to_assign['period']        = $main_course->period;

				array_push( $courses_to_assign, $course_to_assign );
			}
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'name'                => $name,
					'registration_number' => $registration_number,
					'address'             => $address,
					'phone'               => $phone,
					'email'               => $email,
					'contact_person'      => $contact_person,
					'extra_details'       => $extra_details,
					'is_active'           => $is_active,
					'can_add_course'      => $can_add_course,
					'can_delete_course'   => $can_delete_course,
					'can_update_course'   => $can_update_course
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_institutes", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$institute_id = $wpdb->insert_id;

				// Assign courses to institute.
				foreach ( $courses_to_assign as $course_to_assign ) {
					$data = array(
						'course_code'   => $course_to_assign['course_code'],
						'course_name'   => $course_to_assign['course_name'],
						'course_detail' => $course_to_assign['course_detail'],
						'duration'      => $course_to_assign['duration'],
						'duration_in'   => $course_to_assign['duration_in'],
						'fees'          => $course_to_assign['fees'],
						'period'        => $course_to_assign['period'],
						'is_active'     => 1,
						'added_by'      => get_current_user_id(),
						'institute_id'  => $institute_id
					);

					$data['created_at'] = current_time( 'Y-m-d H:i:s' );

					$success = $wpdb->insert( "{$wpdb->prefix}wl_min_courses", $data );
					if ( ! $success ) {
						throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
					}
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array(
					'message' => esc_html__( 'Institute added successfully.', WL_MIM_DOMAIN ),
					'reload'  => true
				) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch institute to update */
	public static function fetch_institute() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $id" );
		if ( ! $row ) {
			die();
		}
		?><?php $nonce = wp_create_nonce( "update-institute-$id" );
		ob_start(); ?>
        <input type="hidden" name="update-institute-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action" value="wl-mim-update-institute">
        <div class="row">
            <div class="col-12 col-md-8 form-group">
                <label for="wlim-institute-name_update" class="col-form-label"><?php esc_html_e( 'Institute Name', WL_MIM_DOMAIN ); ?>:</label>
                <input name="name" type="text" class="form-control" id="wlim-institute-name_update" placeholder="<?php esc_html_e( "Institute Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->name ); ?>">
            </div>
            <div class="col-12 col-md-4 form-group">
                <label for="wlim-institute-registration_number_update" class="col-form-label"><?php esc_html_e( 'Registration Number', WL_MIM_DOMAIN ); ?>:</label>
                <input name="registration_number" type="text" class="form-control" id="wlim-institute-registration_number_update" placeholder="<?php esc_html_e( "Registration Number", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->registration_number ); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="wlim-institute-address_update" class="col-form-label"><?php esc_html_e( 'Address', WL_MIM_DOMAIN ); ?>:</label>
            <textarea name="address" class="form-control" rows="3" id="wlim-institute-address_update" placeholder="<?php esc_html_e( "Address", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->address ); ?></textarea>
        </div>
        <div class="row">
            <div class="col form-group">
                <label for="wlim-institute-phone_update" class="col-form-label"><?php esc_html_e( 'Phone Number', WL_MIM_DOMAIN ); ?>:</label>
                <input name="phone" type="text" class="form-control" id="wlim-institute-phone_update" placeholder="<?php esc_html_e( "Phone Number", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->phone ); ?>">
            </div>
            <div class="col form-group">
                <label for="wlim-institute-email_update" class="col-form-label"><?php esc_html_e( 'Email Address', WL_MIM_DOMAIN ); ?>:</label>
                <input name="email" type="email" class="form-control" id="wlim-institute-email_update" placeholder="<?php esc_html_e( "Email Address", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->email ); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="wlim-institute-contact_person_update" class="col-form-label"><?php esc_html_e( 'Contact Person', WL_MIM_DOMAIN ); ?>:</label>
            <input name="contact_person" type="text" class="form-control" id="wlim-institute-contact_person_update" placeholder="<?php esc_html_e( "Contact Person", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->contact_person ); ?>">
        </div>
        <div class="form-group">
            <label for="wlim-institute-extra_details_update" class="col-form-label"><?php esc_html_e( 'Extra Details', WL_MIM_DOMAIN ); ?>:</label>
            <textarea name="extra_details" class="form-control" rows="3" id="wlim-institute-extra_details_update" placeholder="<?php esc_html_e( "Extra Details", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->extra_details ); ?></textarea>
        </div>
        <div class="form-check pl-0">
            <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-institute-is_active_update" <?php echo boolval( $row->is_active ) ? "checked" : ""; ?>>
            <label class="form-check-label" for="wlim-institute-is_active_update">
				<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
            </label>
        </div><input type="hidden" name="institute_id" value="<?php echo esc_attr( $row->id ); ?>">
        <hr>
        <h5><?php esc_html_e( 'Manage Courses', WL_MIM_DOMAIN ); ?></h4>
        <div class="form-group">
            <select multiple name="course[]" class="selectpicker form-control" id="wlim-institute-update-course" data-none-selected-text="-------- <?php esc_html_e( "Select Courses", WL_MIM_DOMAIN ); ?> --------" data-live-search="true" data-actions-box="true">
                <?php
                $courses = WL_MIM_Helper::get_courses( $id );
                if ( count( $courses ) > 0 ) {
                    foreach ( $courses as $course ) { ?>
                   		<option <?php selected( $course->is_active, '1', true ); ?> value="<?php echo esc_attr( $course->id ); ?>"><?php echo esc_html( "$course->course_name ($course->course_code)" ); ?></option>
                    <?php
                    }
                } ?>
				?>
            </select>
            <small><?php esc_html_e( 'This will mark as selected courses as active.', WL_MIM_DOMAIN ); ?></small>
        </div>

		<hr>
		<h5><?php esc_html_e( 'Add Courses', WL_MIM_DOMAIN ); ?></h4>
		<div class="form-group">
		<select multiple name="i_course[]" class="selectpicker form-control" id="wlim-institute-add-course" data-none-selected-text="-------- <?php esc_html_e( "Select Courses", WL_MIM_DOMAIN ); ?> --------" data-live-search="true" data-actions-box="true">
                <?php
                $institute_courses = WL_MIM_Helper::get_main_courses( $id ); 
                if ( count( $institute_courses ) > 0 ) {
                    foreach ( $institute_courses as $value ) { ?>
						<option <?php selected( $value->is_active, '1', true ); ?> value="<?php echo esc_attr( $value->id ); ?>"><?php echo esc_html( "$value->course_name ($value->course_code)" ); ?></option>
				 <?php
				 }
                } ?>
				?>
            </select>
            <small><?php esc_html_e( 'This will mark as selected courses as active.', WL_MIM_DOMAIN ); ?></small>
        </div>
        <hr>
        <h5><?php esc_html_e( 'Course Restriction', WL_MIM_DOMAIN ); ?></h4>
        <div class="form-check pl-0">
            <label class="form-check-label" for="wlim-institute-can_add_course">
            	<input name="can_add_course" type="checkbox" id="wlim-institute-can_add_course" <?php checked( $row->can_add_course, '1', true ); ?>>
                <?php esc_html_e( 'Institute can add course?', WL_MIM_DOMAIN ); ?>
            </label>
        </div>
        <div class="form-check pl-0">
            <label class="form-check-label" for="wlim-institute-can_update_course">
            	<input name="can_update_course" type="checkbox" id="wlim-institute-can_update_course" <?php checked( $row->can_update_course, '1', true ); ?>>
                <?php esc_html_e( 'Institute can update course?', WL_MIM_DOMAIN ); ?>
            </label>
        </div>
        <div class="form-check pl-0">
            <label class="form-check-label" for="wlim-institute-can_delete_course">
            	<input name="can_delete_course" type="checkbox" id="wlim-institute-can_delete_course" <?php checked( $row->can_delete_course, '1', true ); ?>>
                <?php esc_html_e( 'Institute can delete course?', WL_MIM_DOMAIN ); ?>
            </label>
        </div>
		<?php
		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/* Update institute */
	public static function update_institute() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['institute_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-institute-$id"], "update-institute-$id" ) ) {
			die();
		}
		global $wpdb;

		$name                = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$registration_number = isset( $_POST['registration_number'] ) ? sanitize_text_field( $_POST['registration_number'] ) : '';
		$address             = isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '';
		$phone               = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$email               = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$contact_person      = isset( $_POST['contact_person'] ) ? sanitize_text_field( $_POST['contact_person'] ) : '';
		$extra_details       = isset( $_POST['extra_details'] ) ? sanitize_textarea_field( $_POST['extra_details'] ) : '';
		$is_active           = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'  ] ) ) : 0;
		$courses             = ( isset( $_POST['course'] ) && is_array( $_POST['course'] ) ) ? array_map( 'absint', $_POST['course'] ) : array();
		$i_courses             = ( isset( $_POST['i_course'] ) && is_array( $_POST['i_course'] ) ) ? array_map( 'absint', $_POST['i_course'] ) : array();
		$can_add_course      = isset( $_POST['can_add_course'] ) ? boolval( sanitize_text_field( $_POST['can_add_course'] ) ) : 0;
		$can_delete_course   = isset( $_POST['can_delete_course'] ) ? boolval( sanitize_text_field( $_POST['can_delete_course'] ) ) : 0;
		$can_update_course   = isset( $_POST['can_update_course'] ) ? boolval( sanitize_text_field( $_POST['can_update_course'] ) ) : 0;
		/* Validations */
		$errors = array();
		if ( empty( $name ) ) {
			$errors['name'] = esc_html__( 'Please provide institute name.', WL_MIM_DOMAIN );
		}

		if ( strlen( $name ) > 255 ) {
			$errors['name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( ! empty( $registration_number ) ) {
			$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_institutes WHERE registration_number = '$registration_number' AND id != $id" );

			if ( $count ) {
				$errors['registration_number'] = esc_html__( 'Already exists.', WL_MIM_DOMAIN );
			}
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'name'                => $name,
					'registration_number' => $registration_number,
					'address'             => $address,
					'phone'               => $phone,
					'email'               => $email,
					'contact_person'      => $contact_person,
					'extra_details'       => $extra_details,
					'is_active'           => $is_active,
					'can_add_course'      => $can_add_course,
					'can_delete_course'   => $can_delete_course,
					'can_update_course'   => $can_update_course,
					'updated_at'          => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_institutes", $data, array( 'id' => $id ) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

                

				$institute_courses = WL_MIM_Helper::get_main_courses( $id ); 

				// add course .
				foreach ( $institute_courses as $i_courses_id ) {
					if (in_array($i_courses_id->id, $i_courses )) {
					$i_data	= array( 
						'course_code'   => $i_courses_id->course_code,
						'course_name'   => $i_courses_id->course_name,
						'course_detail' => $i_courses_id->course_detail,
						'duration'      => $i_courses_id->duration,
						'duration_in'   => $i_courses_id->duration_in,
						'fees'          => $i_courses_id->fees,
						'is_active'     => 1,
						'institute_id'  => $id,
						'is_deleted'    => 0,
						'created_at'    => current_time( 'Y-m-d H:i:s' ),
						'period'        => $i_courses_id->period,
						);
						$success = $wpdb->insert( "{$wpdb->prefix}wl_min_courses", $i_data );
					}
				}
				//

				$courses_ids = WL_MIM_Helper::get_courses_ids( $id );
				// Update course status.
				foreach ( $courses_ids as $courses_id ) {
					
                	if ( in_array( $courses_id, $courses ) ) {
                		// Mark as active.
						$wpdb->update( "{$wpdb->prefix}wl_min_courses", array( 'is_active' => 1 ), array( 'id' => $courses_id ) );
                	} else {
                		// Mark as inactive.
						$wpdb->update( "{$wpdb->prefix}wl_min_courses", array( 'is_active' => 0 ), array( 'id' => $courses_id ) );
					}
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array(
					'message' => esc_html__( 'Institute updated successfully.', WL_MIM_DOMAIN ),
					'reload'  => true
				) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete institute */
	public static function delete_institute() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-institute-$id"], "delete-institute-$id" ) ) {
			die();
		}
		global $wpdb;

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( "{$wpdb->prefix}wl_min_institutes", array( 'id' => $id )
			);
			if ( ! $success ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$users = get_users( array(
				'meta_key'   => 'wlim_institute_id',
				'meta_value' => $id
			) );
			foreach ( $users as $user ) {
				delete_user_meta( $user->ID, 'wlim_institute_id' );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array(
				'message' => esc_html__( 'Institute removed successfully.', WL_MIM_DOMAIN ),
				'reload'  => true
			) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* Add new main course */
	public static function get_course_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_main_courses ORDER BY id DESC" );
		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id          = $row->id;
				$course_code = $row->course_code;
				$course_name = $row->course_name ? $row->course_name : '-';
				$duration    = $row->duration;
				$duration_in = $row->duration_in;
				$duration_in = ( $duration < 2 ) ? esc_html__( substr( $duration_in, 0, - 1 ), WL_MIM_DOMAIN ) : esc_html__( $duration_in, 0, - 1, WL_MIM_DOMAIN );

				if ( 'monthly' === $row->period ) {
					$duration_in_month = WL_MIM_Helper::get_course_months_count( $row->duration, $row->duration_in );
					$fees = number_format( $row->fees / $duration_in_month, 2, '.', '' );
				} else {
					$fees = number_format( $row->fees, 2, '.', '' );
				}

				$period = $row->period ? WL_MIM_Helper::get_period_in()[$row->period] : WL_MIM_Helper::get_period_in()['one-time'];

				$results["data"][] = array(
					esc_html( $course_code ),
					esc_html( $course_name ),
					esc_html( "$duration $duration_in" ),
					esc_html( $fees ),
					esc_html( $period ),
					'<a class="mr-3" href="#update-main-course" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-main-course-security="' . wp_create_nonce( "delete-main-course-$id" ) . '"delete-main-course-id="' . $id . '" class="delete-main-course"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new course */
	public static function add_course() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-main-course'], 'add-main-course' ) ) {
			die();
		}
		global $wpdb;

		$course_code   = isset( $_POST['course_code'] ) ? sanitize_text_field( $_POST['course_code'] ) : '';
		$course_name   = isset( $_POST['course_name'] ) ? sanitize_text_field( $_POST['course_name'] ) : '';
		$course_detail = isset( $_POST['course_detail'] ) ? sanitize_textarea_field( $_POST['course_detail'] ) : '';
		$duration      = isset( $_POST['duration'] ) ? intval( sanitize_text_field( $_POST['duration'] ) ) : 0;
		$duration_in   = isset( $_POST['duration_in'] ) ? sanitize_text_field( $_POST['duration_in'] ) : '';
		$fees          = number_format( isset( $_POST['fees'] ) ? max( floatval( sanitize_text_field( $_POST['fees'] ) ), 0 ) : 0, 2, '.', '' );
		$period        = isset( $_POST['period'] ) ? sanitize_text_field( $_POST['period'] ) : WL_MIM_Helper::get_period_in()['one-time'];

		/* Validations */
		$errors = array();
		if ( empty( $course_code ) ) {
			$errors['course_code'] = esc_html__( 'Please provide course code.', WL_MIM_DOMAIN );
		}

		if ( strlen( $course_code ) > 191 ) {
			$errors['course_code'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $course_name ) > 255 ) {
			$errors['course_name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( ! in_array( $period, array_keys( WL_MIM_Helper::get_period_in() ) ) ) {
			$errors['period'] = esc_html__( 'Please select valid period.', WL_MIM_DOMAIN );
		}

		if ( $duration < 1 ) {
			$errors['duration'] = esc_html__( 'Duration must be at least 1.', WL_MIM_DOMAIN );
		}

		if ( ! in_array( $duration_in, WL_MIM_Helper::get_duration_in() ) ) {
			$errors['duration_in'] = esc_html__( 'Please select valid duration in.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_main_courses WHERE course_code = '$course_code'" );

		if ( $count ) {
			$errors['course_code'] = esc_html__( 'Course code already exists.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				if ( 'monthly' === $period ) {
					$duration_in_month = WL_MIM_Helper::get_course_months_count( $duration, $duration_in );
					$fees = number_format( $duration_in_month * $fees, 2, '.', '' );
				} else {
					$fees = number_format( $fees, 2, '.', '' );
				}

				$data = array(
					'course_code'   => $course_code,
					'course_name'   => $course_name,
					'course_detail' => $course_detail,
					'duration'      => $duration,
					'duration_in'   => $duration_in,
					'fees'          => $fees,
					'period'        => $period,
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );
				// var_dump($data); die;
				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_main_courses", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array(
					'message' => esc_html__( 'Course added successfully.', WL_MIM_DOMAIN )
				) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch course to update */
	public static function fetch_course() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_main_courses WHERE id = $id" );
		if ( ! $row ) {
			die();
		}

		if ( 'monthly' === $row->period ) {
			$duration_in_month = WL_MIM_Helper::get_course_months_count( $row->duration, $row->duration_in );
			$fees = number_format( $row->fees / $duration_in_month, 2, '.', '' );
		} else {
			$fees = number_format( $row->fees, 2, '.', '' );
		}

		ob_start(); ?>
        <form id="wlim-update-main-course-form">
			<?php $nonce = wp_create_nonce( "update-main-course-$id" ); ?>
            <input type="hidden" name="update-main-course-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
            <div class="row">
                <div class="col form-group">
                    <label for="wlim-main-course-main-course_code_update" class="col-form-label"><?php esc_html_e( 'Course Code', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="course_code" type="text" class="form-control" id="wlim-main-course-main-course_code_update" placeholder="<?php esc_html_e( "Course Code", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->course_code ); ?>">
                </div>
                <div class="col form-group">
                    <label for="wlim-main-course-main-course_name_update" class="col-form-label"><?php esc_html_e( 'Course Name', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="course_name" type="text" class="form-control" id="wlim-main-course-main-course_name_update" placeholder="<?php esc_html_e( "Course Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->course_name ); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="wlim-main-course-main-course_detail_update" class="col-form-label"><?php esc_html_e( 'Course Detail', WL_MIM_DOMAIN ); ?>:</label>
                <textarea name="course_detail" class="form-control" rows="3" id="wlim-main-course-main-course_detail_update" placeholder="<?php esc_html_e( "Course Detail", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->course_detail ); ?></textarea>
            </div>
            <div class="row">
                <div class="col form-group">
                    <label for="wlim-main-course-duration_update" class="col-form-label"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="duration" type="number" class="form-control" id="wlim-main-course-duration_update" placeholder="<?php esc_html_e( "Duration", WL_MIM_DOMAIN ); ?>" step="1" min="0" value="<?php echo esc_attr( $row->duration ); ?>">
                </div>
                <div class="col form-group wlim_select_col">
                    <label for="wlim-main-course-duration_in_update" class="pt-2"><?php esc_html_e( 'Duration In', WL_MIM_DOMAIN ); ?>:</label>
                    <select name="duration_in" class="form-control" id="wlim-main-course-duration_in_update">
						<?php
						foreach ( WL_MIM_Helper::get_duration_in() as $value ) { ?>
                            <option value="<?php echo esc_attr( $value ); ?>"><?php esc_html_e( $value, WL_MIM_DOMAIN ); ?></option>
							<?php
						} ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="wlim-main-course-fees_update" class="col-form-label"><?php esc_html_e( 'Fees', WL_MIM_DOMAIN ); ?>:</label>
                <input name="fees" type="number" class="form-control" id="wlim-main-course-fees_update" placeholder="<?php esc_html_e( "Fees", WL_MIM_DOMAIN ); ?>" min="0" step="any" value="<?php echo esc_attr( $fees ); ?>">
            </div>
            <div class="form-group">
                <label for="wlim-main-course-period_update"
                       class="pt-2"><?php esc_html_e( 'Fees Period', WL_MIM_DOMAIN ); ?>:</label>
                <select name="period" class="form-control" id="wlim-main-course-period_update">
					<?php
					foreach ( WL_MIM_Helper::get_period_in() as $key => $value ) { ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, esc_attr( $row->period ), true ); ?>><?php echo esc_html( $value ); ?></option>
						<?php
					} ?>
                </select>
            </div>
            <input type="hidden" name="course_id" value="<?php echo esc_attr( $row->id ); ?>">
        </form>
		<?php
		$html = ob_get_clean();

		$json = json_encode( array(
			'duration_in' => esc_attr( $row->duration_in )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update course */
	public static function update_course() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['course_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-main-course-$id"], "update-main-course-$id" ) ) {
			die();
		}
		global $wpdb;

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_main_courses WHERE id = $id" );
		if ( ! $row ) {
			die();
		}

		$course_code   = isset( $_POST['course_code'] ) ? sanitize_text_field( $_POST['course_code'] ) : '';
		$course_name   = isset( $_POST['course_name'] ) ? sanitize_text_field( $_POST['course_name'] ) : '';
		$course_detail = isset( $_POST['course_detail'] ) ? sanitize_textarea_field( $_POST['course_detail'] ) : '';
		$duration      = isset( $_POST['duration'] ) ? intval( sanitize_text_field( $_POST['duration'] ) ) : 0;
		$duration_in   = isset( $_POST['duration_in'] ) ? sanitize_text_field( $_POST['duration_in'] ) : '';
		$fees          = number_format( isset( $_POST['fees'] ) ? max( floatval( sanitize_text_field( $_POST['fees'] ) ), 0 ) : 0, 2, '.', '' );
		$period        = isset( $_POST['period'] ) ? sanitize_text_field( $_POST['period'] ) : WL_MIM_Helper::get_period_in()['one-time'];

		/* Validations */
		$errors = array();
		if ( empty( $course_code ) ) {
			$errors['course_code'] = esc_html__( 'Please provide course code.', WL_MIM_DOMAIN );
		}

		if ( strlen( $course_code ) > 191 ) {
			$errors['course_code'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		if ( strlen( $course_name ) > 255 ) {
			$errors['course_name'] = esc_html__( 'Maximum length cannot exceed 255 characters.', WL_MIM_DOMAIN );
		}

		if ( ! in_array( $period, array_keys( WL_MIM_Helper::get_period_in() ) ) ) {
			$errors['period'] = esc_html__( 'Please select valid period.', WL_MIM_DOMAIN );
		}

		if ( $duration < 1 ) {
			$errors['duration'] = esc_html__( 'Duration must be at least 1.', WL_MIM_DOMAIN );
		}

		if ( ! in_array( $duration_in, WL_MIM_Helper::get_duration_in() ) ) {
			$errors['duration_in'] = esc_html__( 'Please select valid duration in.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_main_courses WHERE id != $id AND course_code = '$course_code'" );

		if ( $count ) {
			$errors['course_code'] = esc_html__( 'Course code already exists.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				if ( 'monthly' === $period ) {
					$duration_in_month = WL_MIM_Helper::get_course_months_count( $duration, $duration_in );
					$fees = number_format( $duration_in_month * $fees, 2, '.', '' );
				} else {
					$fees = number_format( $fees, 2, '.', '' );
				}

				$data = array(
					'course_code'   => $course_code,
					'course_name'   => $course_name,
					'course_detail' => $course_detail,
					'duration'      => $duration,
					'duration_in'   => $duration_in,
					'fees'          => $fees,
					'period'        => $period,
					'updated_at'    => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_main_courses", $data, array(
					'id' => $id,
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Course updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete course */
	public static function delete_course() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-main-course-$id"], "delete-main-course-$id" ) ) {
			die();
		}
		global $wpdb;

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( "{$wpdb->prefix}wl_min_main_courses", array( 'id' => $id )
			);
			if ( ! $success ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array(
				'message' => esc_html__( 'Course removed successfully.', WL_MIM_DOMAIN )
			) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* Check permission to manage institute */
	private static function check_permission() {
		if ( ! current_user_can( 'wl_min_multi_institute' ) ) {
			die();
		}
	}
}
?>
