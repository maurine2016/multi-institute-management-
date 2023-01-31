<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SMSHelper.php' );

class WL_MIM_Notification {
	/* Notification Configure */
	public static function notification_configure() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}

		$notification_by = isset( $_REQUEST['notification_by'] ) ? sanitize_text_field( $_REQUEST['notification_by'] ) : '';

		if ( ! in_array( $notification_by, array_keys( WL_MIM_Helper::get_notification_by_list() ) ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select valid option.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		ob_start();
		if ( $notification_by == 'by-batch' ) {
			/* Get batches */
			$data        = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );
			$course_data = $wpdb->get_results( "SELECT id, course_name, course_code FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );
			if ( count( $data ) !== 0 ) { ?>
                <input type="hidden" name="notification_for" value="batch">
                <div class="form-group wlim-selectpicker">
                    <label for="wlim-batch" class="col-form-label text-primary"><?php esc_html_e( "Batch", WL_MIM_DOMAIN ); ?>:</label>
                    <select name="batch" class="form-control selectpicker" id="wlim-batch">
                        <option value="">-------- <?php esc_html_e( "Select a Batch", WL_MIM_DOMAIN ); ?> --------</option>
						<?php
						foreach ( $data as $row ) {
							$id         = $row->id;
							$time_from  = date( "g:i A", strtotime( $row->time_from ) );
							$time_to    = date( "g:i A", strtotime( $row->time_to ) );
							$timing     = "$time_from - $time_to";
							$batch_info = $row->batch_code;
							if ( $row->batch_name ) {
								$batch_info .= " ( $row->batch_name )";
							}
							$course = '-';
							if ( $row->course_id && isset( $course_data[ $row->course_id ] ) ) {
								$course_name = $course_data[ $row->course_id ]->course_name;
								$course_code = $course_data[ $row->course_id ]->course_code;
								$course      = "$course_name ($course_code)";
							} ?>
                            <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $batch_info ) . " ( " . esc_html( $timing ) . " ) ( " . WL_MIM_Helper::get_batch_status( $row->start_date, $row->end_date ) . " ) - $course"; ?></option>
							<?php
						} ?>
                    </select>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" checked name="active_students" class="form-check-input mt-1" id="wlim-active_students">
                    <label class="form-check-label mb-1 ml-4" for="wlim-active_students"><?php esc_html_e( 'To Active Students', WL_MIM_DOMAIN ); ?></label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="inactive_students" class="form-check-input mt-1" id="wlim-inactive_students">
                    <label class="form-check-label mb-1 ml-4" for="wlim-inactive_students"><?php esc_html_e( 'To Inactive Students', WL_MIM_DOMAIN ); ?></label>
                </div>
				<?php
				$element = '#wlim-batch';
			} else { ?>
                <strong class="text-danger">
					<?php
					esc_html_e( 'There is no batch.', WL_MIM_DOMAIN ); ?>
                </strong>
				<?php
				$element = '';
			}

			$json_data = array(
				'element' => esc_attr( $element ),
			);

		} elseif ( $notification_by == 'by-course' ) {
			/* Get courses */
			$data = $wpdb->get_results( "SELECT id, course_code, course_name FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );
			if ( count( $data ) !== 0 ) { ?>
                <input type="hidden" name="notification_for" value="course">
                <div class="form-group wlim-selectpicker">
                    <label for="wlim-course" class="col-form-label text-primary"><?php esc_html_e( "Course", WL_MIM_DOMAIN ); ?>:</label>
                    <select name="course" class="form-control selectpicker" id="wlim-course">
                        <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?> --------</option>
						<?php
						foreach ( $data as $row ) {
							$id     = $row->id;
							$course = "$row->course_name ($row->course_code)"; ?>
                            <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $course ); ?></option>
							<?php
						} ?>
                    </select>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" checked name="active_students" class="form-check-input mt-1" id="wlim-active_students">
                    <label class="form-check-label mb-1 ml-4" for="wlim-active_students"><?php esc_html_e( 'To Active Students', WL_MIM_DOMAIN ); ?></label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="inactive_students" class="form-check-input mt-1" id="wlim-inactive_students">
                    <label class="form-check-label mb-1 ml-4" for="wlim-inactive_students"><?php esc_html_e( 'To Inactive Students', WL_MIM_DOMAIN ); ?></label>
                </div>
				<?php
				$element = '#wlim-course';
			} else { ?>
                <strong class="text-danger">
					<?php
					esc_html_e( 'There is no course.', WL_MIM_DOMAIN ); ?>
                </strong>
				<?php
				$element = '';
			}

			$json_data = array(
				'element' => esc_attr( $element ),
			);

		} elseif ( $notification_by == 'by-pending-fees' ) {
			/* Get students having pending fees */
			$data = $wpdb->get_results( "SELECT id, first_name, last_name, fees, enrollment_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY id DESC" );
			if ( count( $data ) !== 0 ) { ?>
                <input type="hidden" name="notification_for" value="pending-fees">
                <div class="form-group wlim-selectpicker">
                    <label for="wlim-students" class="col-form-label text-primary"><?php esc_html_e( "Students", WL_MIM_DOMAIN ); ?>:</label>
                    <select name="student[]" class="form-control selectpicker" id="wlim-students" multiple>
						<?php
						foreach ( $data as $row ) {
							$fees = unserialize( $row->fees );
							if ( WL_MIM_Helper::get_fees_total( $fees['payable'] ) > WL_MIM_Helper::get_fees_total( $fees['paid'] ) ) {
								if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
									$student_id = $row->enrollment_id;
								} else {
									$student_id = $row->id;
								}
								$id            = $row->id;
								$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
								$name          = $row->first_name;
								if ( $row->last_name ) {
									$name .= " $row->last_name";
								}
								$student = "$name ($enrollment_id)"; ?>
                                <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $student ); ?></option>
								<?php
							}
						} ?>
                    </select>
                </div>
				<?php
				$element = '#wlim-students';
			} else { ?>
                <strong class="text-danger">
					<?php
					esc_html_e( 'There is no student with pending fees.', WL_MIM_DOMAIN ); ?>
                </strong>
				<?php
				$element = '';
			}

			$json_data = array(
				'element' => esc_attr( $element ),
			);

		} elseif ( $notification_by == 'by-active-students' ) {
			/* Get active students */
			$data = $wpdb->get_results( "SELECT id, first_name, last_name, enrollment_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY id DESC" );
			if ( count( $data ) !== 0 ) { ?>
                <input type="hidden" name="notification_for" value="active-students">
                <div class="form-group wlim-selectpicker">
                    <label for="wlim-students" class="col-form-label text-primary"><?php esc_html_e( "Students", WL_MIM_DOMAIN ); ?>:</label>
                    <select name="student[]" class="form-control selectpicker" id="wlim-students" multiple>
						<?php
						foreach ( $data as $row ) {
							$id            = $row->id;
							if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
								$student_id = $row->enrollment_id;
							} else {
								$student_id = $row->id;
							}
							$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
							$name          = $row->first_name;
							if ( $row->last_name ) {
								$name .= " $row->last_name";
							}
							$student = "$name ($enrollment_id)"; ?>
                            <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $student ); ?></option>
							<?php
						} ?>
                    </select>
                </div>
				<?php
				$element = '#wlim-students';
			} else { ?>
                <strong class="text-danger">
					<?php
					esc_html_e( 'There is no active student.', WL_MIM_DOMAIN ); ?>
                </strong>
				<?php
				$element = '';
			}

			$json_data = array(
				'element' => esc_attr( $element ),
			);

		} elseif ( $notification_by == 'by-inactive-students' ) {
			/* Get inactive students */
			$data = $wpdb->get_results( "SELECT id, first_name, last_name, enrollment_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND is_active = 0 AND institute_id = $institute_id ORDER BY id DESC" );
			if ( count( $data ) !== 0 ) { ?>
                <input type="hidden" name="notification_for" value="inactive-students">
                <div class="form-group wlim-selectpicker">
                    <label for="wlim-students" class="col-form-label text-primary"><?php esc_html_e( "Students", WL_MIM_DOMAIN ); ?>:</label>
                    <select name="student[]" class="form-control selectpicker" id="wlim-students" multiple>
						<?php
						foreach ( $data as $row ) {
							$id            = $row->id;
							if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
								$student_id = $row->enrollment_id;
							} else {
								$student_id = $row->id;
							}
							$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
							$name          = $row->first_name;
							if ( $row->last_name ) {
								$name .= " $row->last_name";
							}
							$student = "$name ($enrollment_id)"; ?>
                            <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $student ); ?></option>
							<?php
						} ?>
                    </select>
                </div>
				<?php
				$element = '#wlim-students';
			} else { ?>
                <strong class="text-danger">
					<?php
					esc_html_e( 'There is no inactive student.', WL_MIM_DOMAIN ); ?>
                </strong>
				<?php
				$element = '';
			}

			$json_data = array(
				'element' => esc_attr( $element ),
			);

		} elseif ( $notification_by == 'by-individual-students' ) {
			/* Get individual students */
			$data = $wpdb->get_results( "SELECT id, first_name, last_name, enrollment_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );
			if ( count( $data ) !== 0 ) { ?>
                <input type="hidden" name="notification_for" value="individual-students">
                <div class="form-group wlim-selectpicker">
                    <label for="wlim-students" class="col-form-label text-primary"><?php esc_html_e( "Students", WL_MIM_DOMAIN ); ?>:</label>
                    <select name="student[]" class="form-control selectpicker" id="wlim-students" multiple>
						<?php
						foreach ( $data as $row ) {
							$id            = $row->id;
							if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
								$student_id = $row->enrollment_id;
							} else {
								$student_id = $row->id;
							}
							$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
							$name          = $row->first_name;
							if ( $row->last_name ) {
								$name .= " $row->last_name";
							}
							$student = "$name ($enrollment_id)"; ?>
                            <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $student ); ?></option>
							<?php
						} ?>
                    </select>
                </div>
				<?php
				$element = '#wlim-students';
			} else { ?>
                <strong class="text-danger">
					<?php
					esc_html_e( 'There is no student.', WL_MIM_DOMAIN ); ?>
                </strong>
				<?php
				$element = '';
			}

			$json_data = array(
				'element' => esc_attr( $element ),
			);
		}

		$html                         = ob_get_clean();
		$json_data['notification_by'] = esc_attr( $notification_by );
		$json                         = json_encode( $json_data );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Send Notification */
	public static function send_notification() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_POST['security'], 'wl-mim-send-notification' ) ) {
			die();
		}

		$institute_id = WL_MIM_Helper::get_current_institute_id();
		$email        = WL_MIM_SettingHelper::get_email_settings( $institute_id );

		$notification_for   = isset( $_POST['notification_for'] ) ? sanitize_text_field( $_POST['notification_for'] ) : null;
		$email_notification = isset( $_POST['email_notification'] ) ? boolval( sanitize_text_field( $_POST['email_notification'] ) ) : 0;
		$sms_notification   = isset( $_POST['sms_notification'] ) ? boolval( sanitize_text_field( $_POST['sms_notification'] ) ) : 0;

		if ( empty( $email_notification ) && empty( $sms_notification ) ) {
			wp_send_json_error( esc_html__( 'Please select notification channel.', WL_MIM_DOMAIN ) );
		}

		$errors = array();
		if ( $email_notification ) {
			$email_from    = isset( $_POST['email_from'] ) ? sanitize_text_field( $_POST['email_from'] ) : '';
			$email_subject = isset( $_POST['email_subject'] ) ? sanitize_text_field( $_POST['email_subject'] ) : '';
			$email_body    = isset( $_POST['email_body'] ) ? wp_kses_post( $_POST['email_body'] ) : '';
			$attachments   = ( isset( $_FILES['attachment'] ) && is_array( $_FILES['attachment'] ) ) ? $_FILES['attachment'] : null;
			$email_from    = empty( $email_from ) ? $email['email_from'] : '';

			/* Validations */
			if ( empty( $email_subject ) ) {
				$errors['email_subject'] = esc_html__( 'Please specify email subject.', WL_MIM_DOMAIN );
			}

			if ( empty( $email_body ) ) {
				$errors['email_body'] = esc_html__( 'Please specify email body.', WL_MIM_DOMAIN );
			}
			/* End validations */

			if ( ! ( count( $errors ) < 1 ) ) {
				wp_send_json_error( $errors );
			}
		} else {
			$email_notification = false;
			$email_subject      = '';
			$email_body         = '';
			$email_from         = '';
			$attachments        = array();
		}

		if ( $sms_notification ) {
			$sms_body = isset( $_POST['sms_body'] ) ? $_POST['sms_body'] : '';
			$template_id = isset( $_POST['template_id'] ) ? $_POST['template_id'] : '';

			/* Validations */
			if ( empty( $sms_body ) ) {
				$errors['sms_body'] = esc_html__( 'Please specify SMS body.', WL_MIM_DOMAIN );
			}
			/* End validations */
		} else {
			$sms_notification = false;
			$sms_body         = '';
		}

		if ( ! ( count( $errors ) < 1 ) ) {
			wp_send_json_error( $errors );
		}

		if ( $notification_for == 'batch' ) {
			self::send_batch_notification( $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id );
		} elseif ( $notification_for == 'course' ) {
			self::send_course_notification( $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id  );
		} elseif ( $notification_for == 'pending-fees' ) {
			self::send_students_notification( $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id  );
		} elseif ( $notification_for == 'active-students' ) {
			self::send_students_notification( $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id  );
		} elseif ( $notification_for == 'inactive-students' ) {
			self::send_students_notification( $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id  );
		} elseif ( $notification_for == 'individual-students' ) {
			self::send_students_notification( $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id  );
		} else {
			wp_send_json_error( esc_html__( 'Invalid notification.', WL_MIM_DOMAIN ) );
		}
	}

	/* Send Batch Notification */
	private static function send_batch_notification( $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id  ) {
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$batch_id          = isset( $_POST['batch'] ) ? intval( sanitize_text_field( $_POST['batch'] ) ) : null;
		$active_students   = isset( $_POST['active_students'] ) ? boolval( sanitize_text_field( $_POST['active_students'] ) ) : 0;
		$inactive_students = isset( $_POST['inactive_students'] ) ? boolval( sanitize_text_field( $_POST['inactive_students'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $active_students ) && empty( $inactive_students ) ) {
			wp_send_json_error( esc_html__( 'Please specify either active students or inactive students or both.', WL_MIM_DOMAIN ) );
		}

		if ( empty( $batch_id ) ) {
			$errors['batch'] = esc_html__( 'Please select a valid batch.', WL_MIM_DOMAIN );
		} else {
			$data = null;
			if ( $active_students && $inactive_students ) {
				$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $batch_id AND institute_id = $institute_id" );
			} elseif ( $active_students ) {
				$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $batch_id AND is_active = 1 AND institute_id = $institute_id" );
			} elseif ( $inactive_students ) {
				$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $batch_id AND is_active = 0 AND institute_id = $institute_id" );
			}

			if ( count( $data ) == 0 ) {
				wp_send_json_error( esc_html__( 'There is no student in this batch.', WL_MIM_DOMAIN ) );
			}
		}
		/* End validations */

		self::submit_notification( $errors, $data, $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body );
	}

	/* Send Course Notification */
	private static function send_course_notification( $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body ) {
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$course_id         = isset( $_POST['course'] ) ? intval( sanitize_text_field( $_POST['course'] ) ) : null;
		$active_students   = isset( $_POST['active_students'] ) ? boolval( sanitize_text_field( $_POST['active_students'] ) ) : 0;
		$inactive_students = isset( $_POST['inactive_students'] ) ? boolval( sanitize_text_field( $_POST['inactive_students'] ) ) : 0;

		/* Validations */
		$errors = array();
		if ( empty( $active_students ) && empty( $inactive_students ) ) {
			wp_send_json_error( esc_html__( 'Please specify either active students or inactive students or both.', WL_MIM_DOMAIN ) );
		}

		if ( empty( $course_id ) ) {
			$errors['course'] = esc_html__( 'Please select a valid course.', WL_MIM_DOMAIN );
		} else {
			$data = null;
			if ( $active_students && $inactive_students ) {
				$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND course_id = $course_id AND institute_id = $institute_id" );
			} elseif ( $active_students ) {
				$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND course_id = $course_id AND is_active = 1 AND institute_id = $institute_id" );
			} elseif ( $inactive_students ) {
				$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND course_id = $course_id AND is_active = 0 AND institute_id = $institute_id" );
			}

			if ( count( $data ) == 0 ) {
				wp_send_json_error( esc_html__( 'There is no student in this course.', WL_MIM_DOMAIN ) );
			}
		}
		/* End validations */

		self::submit_notification( $errors, $data, $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body );
	}

	/* Send Students Notification */
	private static function send_students_notification( $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id  ) {
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$students    = ( isset( $_POST['student'] ) && is_array( $_POST['student'] ) ) ? $_POST['student'] : array();
		$students    = array_map( 'esc_attr', $students );
		$student_ids = implode( $students, ',' );

		/* Validations */
		$errors = array();

		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id IN ($student_ids) AND institute_id = $institute_id" );

		if ( count( $data ) == 0 ) {
			wp_send_json_error( esc_html__( 'There is no student.', WL_MIM_DOMAIN ) );
		}
		/* End validations */

		self::submit_notification( $errors, $data, $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id  );
	}

	private static function submit_notification( $errors, $data, $email_notification, $email_subject, $email_body, $email_from, $attachments, $sms_notification, $sms_body, $template_id  ) {
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		if ( count( $errors ) < 1 ) {
			try {
				if ( $email_notification ) {
					/* Send email notification */
					$mail = self::initialize_email( $institute_id );
					$mail->setFrom( $mail->Username, $email_from );
					$mail->Subject = $email_subject;
					$mail->Body    = $email_body;
					$mail->IsHTML( true );
					if ( isset( $attachments["tmp_name"] ) && is_array( $attachments ) ) {
						foreach ( $attachments["tmp_name"] as $key => $attachment ) {
							$mail->addAttachment( $attachment, sanitize_file_name( $attachments["name"][ $key ] ) );
						}
					}

					foreach ( $data as $row ) {
						$email      = $row->email ? $row->email : null;
						$first_name = $row->first_name ? $row->first_name : null;
						$last_name  = $row->last_name ? $row->last_name : null;
						$name       = $first_name;
						if ( $last_name ) {
							$name .= " $last_name";
						}

						if ( $email ) {
							$mail->AddAddress( $email, $name );
						}
					}
					$email_notification_sent = $mail->Send();
				}

				$sms_notification_sent = false;
				if ( $sms_notification && $sms_body ) {
					$phone_numbers = array();
					foreach ( $data as $row ) {
						$phone = $row->phone ? $row->phone : null;
						if ( ! empty( $phone ) ) {
							array_push( $phone_numbers, $phone );
						}
					}

					/* Get SMS settings */
					$sms = WL_MIM_SettingHelper::get_sms_settings( $institute_id );

					/* Send SMS */
					$sms_notification_sent = WL_MIM_SMSHelper::send_sms( $sms, $institute_id, $sms_body, $phone_numbers, $template_id  );
				}

				if ( $email_notification_sent && $sms_notification_sent ) {
					$message = esc_html__( 'Email and SMS notification sent successfully.', WL_MIM_DOMAIN );
				} elseif ( $email_notification_sent ) {
					$message = esc_html__( 'Email notification sent successfully.', WL_MIM_DOMAIN );
				} elseif ( $sms_notification_sent ) {
					$message = esc_html__( 'SMS notification sent successfully.', WL_MIM_DOMAIN );
				} else {
					$message = esc_html__( 'Unable to send notification.', WL_MIM_DOMAIN );
					wp_send_json_error( $message );
				}
				wp_send_json_success( array( 'message' => $message ) );
			} catch ( Exception $exception ) {
				wp_send_json_error( $exception->getMessage() );
			}
		} else {
			wp_send_json_error( $errors );
		}
	}

	private static function initialize_email( $institute_id ) {
		$email = WL_MIM_SettingHelper::get_email_settings( $institute_id );
		global $wp_version;
	
		require_once(ABSPATH . WPINC . '/PHPMailer/PHPMailer.php');
		require_once(ABSPATH . WPINC . '/PHPMailer/SMTP.php');
		require_once(ABSPATH . WPINC . '/PHPMailer/Exception.php');
		$mail = new PHPMailer\PHPMailer\PHPMailer( true );
		
		$mail->CharSet  = 'UTF-8';
		$mail->Encoding = 'base64';
		$mail->IsSMTP();
		$mail->Host       = $email['email_host'];
		$mail->SMTPAuth   = true;
		$mail->Username   = $email['email_username'];
		$mail->Password   = $email['email_password'];
		$mail->SMTPSecure = $email['email_encryption'];
		$mail->Port       = $email['email_port'];

		if ( empty( $mail->Host ) || empty( $mail->Username ) || empty( $mail->Password ) || empty( $mail->SMTPSecure ) || empty( $mail->Port ) ) {
			wp_send_json_error( esc_html__( 'Please configure SMTP Settings to send email notifications.', WL_MIM_DOMAIN ) );
		}

		return $mail;
	}

	/* Check permission to manage notification */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_notifications' ) || ! $institute_id ) {
			die();
		}
	}
}
?>
