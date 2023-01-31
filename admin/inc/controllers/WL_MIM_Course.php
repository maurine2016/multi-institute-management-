<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

class WL_MIM_Course {
	/* Get course data to display on table */
	public static function get_course_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		if ( current_user_can( WL_MIM_Helper::get_multi_institute_capability() ) ) {
			$can_delete_course = true;
		} else {
			$institute = $wpdb->get_row( "SELECT can_delete_course FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
			$can_delete_course = false;
			if($institute) {
				$can_delete_course = (bool) $institute->can_delete_course;
			}
		}

		$data          = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );
		$category_data = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}wl_min_course_categories WHERE institute_id = $institute_id ORDER BY name", OBJECT_K );

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

				$is_acitve   = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$added_on    = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
				$added_by    = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

				$category = '-';
				if ( $row->course_category_id && isset( $category_data[ $row->course_category_id ] ) ) {
					$category = $category_data[ $row->course_category_id ]->name;
				}

				$count_total_students  = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND course_id = $row->id AND institute_id = $institute_id" );
				$count_active_students = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND course_id = $row->id AND is_active = 1 AND institute_id = $institute_id" );

				$results["data"][] = array(
					'<input type="checkbox" class="wl-mim-select-single wl-mim-bulk-students" name="bulk_data[]" value="' . esc_attr($row->id) . '">',
					esc_html( $course_code ),
					esc_html( $course_name ),
					esc_html( $category ),
					esc_html( "$duration $duration_in" ),
					esc_html( $fees ),
					esc_html( $period ),
					'<a class="text-primary" href="' . admin_url( 'admin.php?page=multi-institute-management-students' ) . '&status=all&course_id=' . $id . '">' . $count_total_students . '</a>',
					'<a class="text-primary" href="' . admin_url( 'admin.php?page=multi-institute-management-students' ) . '&status=active&course_id=' . $id . '">' . $count_active_students . '</a>',
					esc_html( $is_acitve ),
					esc_html( $added_on ),
					esc_html( $added_by ),
					'<a class="mr-3" href="#update-course" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> ' . ( $can_delete_course ? '<a href="javascript:void(0)" delete-course-security="' . wp_create_nonce( "delete-course-$id" ) . '"delete-course-id="' . $id . '" class="delete-course"> <i class="fa fa-trash text-danger"></i></a>' : '' )
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
		if ( ! wp_verify_nonce( $_POST['add-course'], 'add-course' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		if ( current_user_can( WL_MIM_Helper::get_multi_institute_capability() ) ) {
			$can_add_course = true;
		} else {
			$institute = $wpdb->get_row( "SELECT can_add_course FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
			$can_add_course = false;
			if( $institute ) {
				$can_add_course = (bool) $institute->can_add_course;
			}
		}

		if ( ! $can_add_course ) {
			wp_send_json_error( esc_html__( 'Action restricted.', WL_MIM_DOMAIN ) );
		}

		$category_id   = isset( $_POST['category'] ) ? intval( sanitize_text_field( $_POST['category'] ) ) : null;
		$course_code   = isset( $_POST['course_code'] ) ? sanitize_text_field( $_POST['course_code'] ) : '';
		$course_name   = isset( $_POST['course_name'] ) ? sanitize_text_field( $_POST['course_name'] ) : '';
		$course_detail = isset( $_POST['course_detail'] ) ? sanitize_textarea_field( $_POST['course_detail'] ) : '';
		$duration      = isset( $_POST['duration'] ) ? intval( sanitize_text_field( $_POST['duration'] ) ) : 0;
		$duration_in   = isset( $_POST['duration_in'] ) ? sanitize_text_field( $_POST['duration_in'] ) : '';
		$fees          = number_format( isset( $_POST['fees'] ) ? max( floatval( sanitize_text_field( $_POST['fees'] ) ), 0 ) : 0, 2, '.', '' );
		$period        = isset( $_POST['period'] ) ? sanitize_text_field( $_POST['period'] ) : WL_MIM_Helper::get_period_in()['one-time'];
		$is_active     = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

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

		if ( ! empty( $category_id ) ) {
			$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_course_categories WHERE is_active = 1 AND id = $category_id AND institute_id = $institute_id" );

			if ( ! $count ) {
				$errors['category'] = __( 'Please select a valid category.', WL_MIM_DOMAIN );
			}
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND course_code = '$course_code' AND institute_id = $institute_id" );

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
					'is_active'     => $is_active,
					'added_by'      => get_current_user_id(),
					'institute_id'  => $institute_id
				);

				if ( ! empty( $category_id ) ) {
					$data['course_category_id'] = $category_id;
				}

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );
				// var_dump($success); die;
				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_courses", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Course added successfully.', WL_MIM_DOMAIN ) ) );
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
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		if ( current_user_can( WL_MIM_Helper::get_multi_institute_capability() ) ) {
			$can_update_course = true;
		} else {
			$institute = $wpdb->get_row( "SELECT can_update_course FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
			$can_update_course = false;
			if($institute) {
			    $can_update_course = (bool) $institute->can_update_course;
			}
		}

		if ( 'monthly' === $row->period ) {
			$duration_in_month = WL_MIM_Helper::get_course_months_count( $row->duration, $row->duration_in );
			$fees = number_format( $row->fees / $duration_in_month, 2, '.', '' );
		} else {
			$fees = number_format( $row->fees, 2, '.', '' );
		}

		$wlim_active_categories = WL_MIM_Helper::get_active_categories_institute( $institute_id );

		ob_start(); ?>
        <form id="wlim-update-course-form">
			<?php $nonce = wp_create_nonce( "update-course-$id" ); ?>
            <input type="hidden" name="update-course-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
            <div class="form-group">
                <label for="wlim-course-category_update" class="col-form-label"><?php esc_html_e( "Category", WL_MIM_DOMAIN ); ?>  :</label>
                <select name="category" class="form-control selectpicker" id="wlim-course-category_update">
                    <option value="">-------- <?php esc_html_e( "Select a Category", WL_MIM_DOMAIN ); ?> --------</option>
					<?php
					if ( count( $wlim_active_categories ) > 0 ) {
						foreach ( $wlim_active_categories as $active_category ) { ?>
                            <option value="<?php echo esc_attr( $active_category->id ); ?>"><?php echo esc_html( $active_category->name ); ?></option>
							<?php
						}
					} ?>
                </select>
            </div>
            <div class="row">
                <div class="col form-group">
                    <label for="wlim-course-course_code_update" class="col-form-label"><?php esc_html_e( 'Course Code', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="course_code" type="text" class="form-control" id="wlim-course-course_code_update" placeholder="<?php esc_html_e( "Course Code", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->course_code ); ?>" <?php if ( ! $can_update_course ) { echo 'disabled'; } ?>>
                </div>
                <div class="col form-group">
                    <label for="wlim-course-course_name_update" class="col-form-label"><?php esc_html_e( 'Course Name', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="course_name" type="text" class="form-control" id="wlim-course-course_name_update" placeholder="<?php esc_html_e( "Course Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->course_name ); ?>" <?php if ( ! $can_update_course ) { echo 'disabled'; } ?>>
                </div>
            </div>
            <div class="form-group">
                <label for="wlim-course-course_detail_update" class="col-form-label"><?php esc_html_e( 'Course Detail', WL_MIM_DOMAIN ); ?>:</label>
                <textarea name="course_detail" class="form-control" rows="3" id="wlim-course-course_detail_update" placeholder="<?php esc_html_e( "Course Detail", WL_MIM_DOMAIN ); ?>" <?php if ( ! $can_update_course ) { echo 'disabled'; } ?>><?php echo esc_html( $row->course_detail ); ?></textarea>
            </div>
            <div class="row">
                <div class="col form-group">
                    <label for="wlim-course-duration_update" class="col-form-label"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="duration" type="number" class="form-control" id="wlim-course-duration_update" placeholder="<?php esc_html_e( "Duration", WL_MIM_DOMAIN ); ?>" step="1" min="0" value="<?php echo esc_attr( $row->duration ); ?>" <?php if ( ! $can_update_course ) { echo 'disabled'; } ?>>
                </div>
                <div class="col form-group wlim_select_col">
                    <label for="wlim-course-duration_in_update" class="pt-2"><?php esc_html_e( 'Duration In', WL_MIM_DOMAIN ); ?>:</label>
                    <select name="duration_in" class="form-control" id="wlim-course-duration_in_update" <?php if ( ! $can_update_course ) { echo 'disabled'; } ?>>
						<?php
						foreach ( WL_MIM_Helper::get_duration_in() as $value ) { ?>
                            <option value="<?php echo esc_attr( $value ); ?>"><?php esc_html_e( $value, WL_MIM_DOMAIN ); ?></option>
							<?php
						} ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="wlim-course-fees_update" class="col-form-label"><?php esc_html_e( 'Fees', WL_MIM_DOMAIN ); ?>:</label>
                <input name="fees" type="number" class="form-control" id="wlim-course-fees_update" placeholder="<?php esc_html_e( "Fees", WL_MIM_DOMAIN ); ?>" min="0" step="any" value="<?php echo esc_attr( $fees ); ?>" <?php if ( ! $can_update_course ) { echo 'disabled'; } ?>>
            </div>
            <div class="form-group">
                <label for="wlim-course-period_update"
                       class="pt-2"><?php esc_html_e( 'Fees Period', WL_MIM_DOMAIN ); ?>:</label>
                <select name="period" class="form-control" id="wlim-course-period_update" <?php if ( ! $can_update_course ) { echo 'disabled'; } ?>>
					<?php
					foreach ( WL_MIM_Helper::get_period_in() as $key => $value ) { ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, esc_attr( $row->period ), true ); ?>><?php echo esc_html( $value ); ?></option>
						<?php
					} ?>
                </select>
            </div>
            <div class="form-check pl-0">
                <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-course-is_active_update" <?php echo boolval( $row->is_active ) ? "checked" : ""; ?> <?php if ( ! $can_update_course ) { echo 'onclick="return false"'; } ?>>
                <label class="form-check-label" for="wlim-course-is_active_update">
					<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                </label>
            </div>
            <input type="hidden" name="course_id" value="<?php echo esc_attr( $row->id ); ?>">
        </form>
		<?php
		$html = ob_get_clean();

		$json = json_encode( array(
			'course_category_id' => esc_attr( $row->course_category_id ),
			'duration_in'        => esc_attr( $row->duration_in )
		) );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Update course */
	public static function update_course() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['course_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-course-$id"], "update-course-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		if ( current_user_can( WL_MIM_Helper::get_multi_institute_capability() ) ) {
			$can_update_course = true;
		} else {
			$institute = $wpdb->get_row( "SELECT can_update_course FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
			$can_update_course = false;
			if($institute) {
			    $can_update_course = (bool) $institute->can_update_course;
			}
		}

		$category_id = isset( $_POST['category'] ) ? intval( sanitize_text_field( $_POST['category'] ) ) : null;

		if ( $can_update_course ) {
			$course_code   = isset( $_POST['course_code'] ) ? sanitize_text_field( $_POST['course_code'] ) : '';
			$course_name   = isset( $_POST['course_name'] ) ? sanitize_text_field( $_POST['course_name'] ) : '';
			$course_detail = isset( $_POST['course_detail'] ) ? sanitize_textarea_field( $_POST['course_detail'] ) : '';
			$duration      = isset( $_POST['duration'] ) ? intval( sanitize_text_field( $_POST['duration'] ) ) : 0;
			$duration_in   = isset( $_POST['duration_in'] ) ? sanitize_text_field( $_POST['duration_in'] ) : '';
			$fees          = number_format( isset( $_POST['fees'] ) ? max( floatval( sanitize_text_field( $_POST['fees'] ) ), 0 ) : 0, 2, '.', '' );
			$period        = isset( $_POST['period'] ) ? sanitize_text_field( $_POST['period'] ) : WL_MIM_Helper::get_period_in()['one-time'];
			$is_active     = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;
		}

		/* Validations */
		$errors = array();
		if ( $can_update_course ) {
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
		}

		if ( ! empty( $category_id ) ) {
			$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_course_categories WHERE is_active = 1 AND id = $category_id AND institute_id = $institute_id" );

			if ( ! $count ) {
				$errors['category'] = __( 'Please select a valid category.', WL_MIM_DOMAIN );
			}
		}

		if ( $can_update_course ) {
			$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id != $id AND course_code = '$course_code' AND institute_id = $institute_id" );

			if ( $count ) {
				$errors['course_code'] = esc_html__( 'Course code already exists.', WL_MIM_DOMAIN );
			}
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				if ( $can_update_course ) {
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
						'is_active'     => $is_active,
						'updated_at'    => date( 'Y-m-d H:i:s' )
					);
				} else {
					$data = array(
						'updated_at' => date( 'Y-m-d H:i:s' )
					);
				}

				if ( ! empty( $category_id ) ) {
					$data['course_category_id'] = $category_id;
				} else {
					$data['course_category_id'] = null;
				}

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_courses", $data, array(
					'is_deleted'   => 0,
					'id'           => $id,
					'institute_id' => $institute_id
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
		if ( ! wp_verify_nonce( $_POST["delete-course-$id"], "delete-course-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		if ( current_user_can( WL_MIM_Helper::get_multi_institute_capability() ) ) {
			$can_delete_course = true;
		} else {
			$institute = $wpdb->get_row( "SELECT can_delete_course FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
			$can_delete_course = false;
			if($institute) {
				$can_delete_course = (bool) $institute->can_delete_course;
			}
		}

		if ( ! $can_delete_course ) {
			wp_send_json_error( esc_html__( 'Action restricted.', WL_MIM_DOMAIN ) );
		}

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->update( "{$wpdb->prefix}wl_min_courses",
				array(
					'is_deleted' => 1,
					'deleted_at' => date( 'Y-m-d H:i:s' )
				), array( 'is_deleted' => 0, 'id' => $id, 'institute_id' => $institute_id )
			);
			if ( $success === false ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Course removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( esc_html__( $exception->getMessage(), WL_MIM_DOMAIN ) );
		}
	}

	/* Get category data to display on table */
	public static function get_category_data() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_course_categories WHERE institute_id = $institute_id ORDER BY id DESC" );
		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$id        = $row->id;
				$name      = $row->name;
				$is_acitve = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
				$added_on  = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );

				$count_total_courses = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND course_category_id = $row->id AND institute_id = $institute_id" );

				$results["data"][] = array(
					esc_html( $name ),
					$count_total_courses,
					esc_html( $is_acitve ),
					esc_html( $added_on ),
					'<a class="mr-3" href="#update-category" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-edit"></i></a> <a href="javascript:void(0)" delete-category-security="' . wp_create_nonce( "delete-category-$id" ) . '"delete-category-id="' . $id . '" class="delete-category"> <i class="fa fa-trash text-danger"></i></a>'
				);
			}
		} else {
			$results["data"] = array();
		}
		wp_send_json( $results );
	}

	/* Add new category */
	public static function add_category() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['add-category'], 'add-category' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$name      = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$detail    = isset( $_POST['detail'] ) ? sanitize_textarea_field( $_POST['detail'] ) : '';
		$is_active = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $name ) ) {
			$errors['name'] = esc_html__( 'Please provide category name.', WL_MIM_DOMAIN );
		}

		if ( strlen( $name ) > 191 ) {
			$errors['name'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_course_categories WHERE name = '$name' AND institute_id = $institute_id" );

		if ( $count ) {
			$errors['name'] = esc_html__( 'Category name already exists.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'name'         => $name,
					'detail'       => $detail,
					'is_active'    => $is_active,
					'institute_id' => $institute_id
				);

				$data['created_at'] = current_time( 'Y-m-d H:i:s' );

				$success = $wpdb->insert( "{$wpdb->prefix}wl_min_course_categories", $data );
				if ( ! $success ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Category added successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Fetch category to update */
	public static function fetch_category() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_course_categories WHERE id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}
		ob_start(); ?>
        <form id="wlim-update-category-form">
			<?php $nonce = wp_create_nonce( "update-category-$id" ); ?>
            <input type="hidden" name="update-category-<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
            <div class="row">
                <div class="col form-group">
                    <label for="wlim-category-name_update" class="col-form-label"><?php esc_html_e( 'Category Name', WL_MIM_DOMAIN ); ?>:</label>
                    <input name="name" type="text" class="form-control" id="wlim-category-name_update" placeholder="<?php esc_html_e( "Category Name", WL_MIM_DOMAIN ); ?>" value="<?php echo esc_attr( $row->name ); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="wlim-category-detail_update" class="col-form-label"><?php esc_html_e( 'Detail', WL_MIM_DOMAIN ); ?>:</label>
                <textarea name="detail" class="form-control" rows="3" id="wlim-category-detail_update" placeholder="<?php esc_html_e( "Detail", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( $row->detail ); ?></textarea>
            </div>
            <div class="form-check pl-0">
                <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-category-is_active_update" <?php echo boolval( $row->is_active ) ? "checked" : ""; ?>>
                <label class="form-check-label" for="wlim-category-is_active_update">
					<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                </label>
            </div>
            <input type="hidden" name="category_id" value="<?php echo esc_attr( $row->id ); ?>">
        </form>
		<?php $html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/* Update category */
	public static function update_category() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['category_id'] ) );
		if ( ! wp_verify_nonce( $_POST["update-category-$id"], "update-category-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$name      = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$detail    = isset( $_POST['detail'] ) ? sanitize_textarea_field( $_POST['detail'] ) : '';
		$is_active = isset( $_POST['is_active'] ) ? boolval( sanitize_text_field( $_POST['is_active'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $name ) ) {
			$errors['name'] = esc_html__( 'Please provide category name.', WL_MIM_DOMAIN );
		}

		if ( strlen( $name ) > 191 ) {
			$errors['name'] = esc_html__( 'Maximum length cannot exceed 191 characters.', WL_MIM_DOMAIN );
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) as count FROM {$wpdb->prefix}wl_min_course_categories WHERE name = '$name' AND institute_id = $institute_id AND id != $id" );

		if ( $count ) {
			$errors['name'] = esc_html__( 'Category name already exists.', WL_MIM_DOMAIN );
		}
		/* End validations */

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				$data = array(
					'name'       => $name,
					'detail'     => $detail,
					'is_active'  => $is_active,
					'updated_at' => date( 'Y-m-d H:i:s' )
				);

				$success = $wpdb->update( "{$wpdb->prefix}wl_min_course_categories", $data, array(
					'id'           => $id,
					'institute_id' => $institute_id
				) );
				if ( $success === false ) {
					throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
				}

				$wpdb->query( 'COMMIT;' );
				wp_send_json_success( array( 'message' => esc_html__( 'Category updated successfully.', WL_MIM_DOMAIN ) ) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	/* Delete category */
	public static function delete_category() {
		self::check_permission();
		$id = intval( sanitize_text_field( $_POST['id'] ) );
		if ( ! wp_verify_nonce( $_POST["delete-category-$id"], "delete-category-$id" ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( "{$wpdb->prefix}wl_min_course_categories", array(
				'id'           => $id,
				'institute_id' => $institute_id
			) );
			if ( $success === false ) {
				throw new Exception( esc_html__( 'An unexpected error occurred.', WL_MIM_DOMAIN ) );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => esc_html__( 'Category removed successfully.', WL_MIM_DOMAIN ) ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	/* Check permission to manage course */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_courses' ) || ! $institute_id ) {
			die();
		}
	}
}
?>
