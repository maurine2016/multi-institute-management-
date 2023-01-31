<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

class WL_MIM_Report {
	/* View report */
	public static function view_report() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$id  = intval( sanitize_text_field( $_REQUEST['student'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) { ?>
            <span class="text-danger"><?php esc_html_e( 'Student not found.', WL_MIM_DOMAIN ); ?></span>
			<?php
			die();
		}
		$course = WL_MIM_Helper::get_course( $row->course_id );
		$course = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '';
		$batch  = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $row->batch_id" );
		if ( ! $batch ) {
			$batch_status = '<strong class="text-warning">' . esc_html__( 'Unknown', WL_MIM_DOMAIN ) . '</strong>';
			$batch_info   = '-';
		} else {
			$batch_status = WL_MIM_Helper::get_batch_status( $batch->start_date, $batch->end_date );
			$time_from    = date( "g:i A", strtotime( $batch->time_from ) );
			$time_to      = date( "g:i A", strtotime( $batch->time_to ) );
			$timing       = "$time_from - $time_to";
			$batch_info   = esc_html__( $batch->batch_code );
			if ( $batch->batch_name ) {
				$batch_info .= " ( $batch->batch_name ) ( " . $timing . " )";
			}
		}
		?>
        <div class="row">
            <div class="col">
                <div class="mb-3 mt-2">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></strong>:&nbsp;
							<?php echo WL_MIM_Helper::get_enrollment_id_with_prefix( $id, $general_enrollment_prefix ); ?>
                        </li>
                        <li class="list-group-item">
                            <strong><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></strong>:&nbsp;
							<?php echo esc_html( $course ); ?>
                        </li>
                        <li class="list-group-item">
                            <strong><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></strong>:&nbsp;
							<?php echo esc_html( $batch_info ); ?>
                        </li>
                        <li class="list-group-item">
                            <strong><?php esc_html_e( 'Batch Status', WL_MIM_DOMAIN ); ?></strong>:&nbsp;
							<?php echo wp_kses( $batch_status, array(
								'strong' => array(
									'class' => 'text-danger',
									'text-primary',
									'text-success',
									'text-warning'
								)
							) ); ?>
                        </li>
                        <li class="list-group-item">
                            <strong><?php esc_html_e( 'ID Card', WL_MIM_DOMAIN ); ?></strong>:
                            <a class="ml-2" href="#print-student" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="<?php echo esc_attr( $id ); ?>"><i class="fa fa-print"></i></a>
                        </li>
                        <li class="list-group-item">
                            <strong><?php esc_html_e( 'Admission Detail', WL_MIM_DOMAIN ); ?></strong>:
                            <a class="ml-2" href="#print-student-admission-detail" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="<?php echo esc_attr( $id ); ?>"><i class="fa fa-print"></i></a>
                        </li>
                        <li class="list-group-item">
                            <strong><?php esc_html_e( 'Fees Report', WL_MIM_DOMAIN ); ?></strong>:
                            <a class="ml-2" href="#print-student-fees-report" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="<?php echo esc_attr( $id ); ?>"><i class="fa fa-print"></i></a>
                        </li>
                        <li class="list-group-item">
                            <strong><?php esc_html_e( 'Pending Fees', WL_MIM_DOMAIN ); ?></strong>:
                            <a class="ml-2" href="#print-student-pending-fees" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="<?php echo esc_attr( $id ); ?>"><i class="fa fa-print"></i></a>
                        </li>
						
                    </ul>
                </div>
            </div>
        </div>
		<?php
		die();
	}

	/* View and print student */
	public static function print_student() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		$user = null;
		if ( $row->user_id ) {
			$user              = $wpdb->get_row( "SELECT * FROM {$wpdb->base_prefix}users WHERE ID = $row->user_id" );
			$user_institute_id = get_user_meta( $user->ID, 'wlim_institute_id', true );
			if ( $user_institute_id !== $institute_id ) {
				die();
			}
		}
		$authorized = false;
		if ( current_user_can( 'wl_min_manage_report' ) ) {
			$authorized = true;
		} else {
			if ( current_user_can( 'wl_min_student' ) ) {
				if ( ( get_current_user_id() != 0 ) && ( get_current_user_id() == $user->ID ) ) {
					$authorized = true;
				}
			} else {
				$authorized = false;
			}
		}
		if ( ! $authorized ) {
			die();
		}
		?>
        <div class="row">
            <div class="col">
                <div class="mb-3 mt-2">
                    <div class="text-center">
                        <button type="button" id="wl-id-card-print" class="btn btn-sm btn-success">
                            <i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print ID Card', WL_MIM_DOMAIN ); ?>
                        </button>
                        <hr>
                    </div>
					<?php
					require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_id_card.php' ); ?>
                </div>
            </div>
        </div>
		<?php
		die();
	}

	/* View and print student admission detail */
	public static function print_student_admission_detail() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		if ( ! $row ) {
			die();
		}

		$user = null;
		if ( $row->user_id ) {
			$user              = $wpdb->get_row( "SELECT * FROM {$wpdb->base_prefix}users WHERE ID = $row->user_id" );
			$user_institute_id = get_user_meta( $user->ID, 'wlim_institute_id', true );
			if ( $user_institute_id !== $institute_id ) {
				die();
			}
		}
		$authorized = false;
		if ( current_user_can( 'wl_min_manage_report' ) ) {
			$authorized = true;
		} else {
			if ( current_user_can( 'wl_min_student' ) ) {
				if ( ( get_current_user_id() != 0 ) && ( get_current_user_id() == $user->ID ) ) {
					$authorized = true;
				}
			} else {
				$authorized = false;
			}
		}
		if ( ! $authorized ) {
			die();
		}
		?>
        <div class="row">
            <div class="col">
                <div class="mb-3 mt-2">
                    <div class="text-center">
                        <button type="button" id="wl-admission-detail-print" class="btn btn-sm btn-success">
                            <i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Admission Detail', WL_MIM_DOMAIN ); ?>
                        </button>
                        <hr>
                    </div>
					<?php
					require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_admission_detail.php' ); ?>
                </div>
            </div>
        </div>
		<?php
		die();
	}

	/* View and print student fees report */
	public static function print_student_fees_report() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id           = intval( sanitize_text_field( $_POST['id'] ) );
		$row          = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		$installments = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0  AND student_id = $id AND institute_id = $institute_id ORDER BY id DESC" );
		if ( ! $row ) {
			die();
		}

		if ( count( $installments ) > 20 ) {
			$installments_recent    = array_slice( $installments, 0, 20 );
			$installments_remaining = array_slice( $installments, 20, count( $installments ) - 20 );
		}

		$user = null;
		if ( $row->user_id ) {
			$user              = $wpdb->get_row( "SELECT * FROM {$wpdb->base_prefix}users WHERE ID = $row->user_id" );
			$user_institute_id = get_user_meta( $user->ID, 'wlim_institute_id', true );
			if ( $user_institute_id !== $institute_id ) {
				die();
			}
		}
		$authorized = false;
		$is_student = false;
		if ( current_user_can( 'wl_min_manage_report' ) ) {
			$authorized = true;
		} else {
			if ( current_user_can( 'wl_min_student' ) ) {
				$is_student = true;
				if ( ( get_current_user_id() != 0 ) && ( get_current_user_id() == $user->ID ) ) {
					$authorized = true;
				}
			} else {
				$authorized = false;
			}
		}
		if ( ! $authorized ) {
			die();
		}

		$fees         = unserialize( $row->fees );
		$pending_fees = number_format( WL_MIM_Helper::get_fees_total( $fees['payable'] ) - WL_MIM_Helper::get_fees_total( $fees['paid'] ), 2, '.', '' );
		?>
        <div class="row">
            <div class="col">
                <div class="mb-3 mt-2">
                    <div class="text-center">
                        <button type="button" id="wl-fees-report-print" class="btn btn-sm btn-success">
                            <i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Fees Report', WL_MIM_DOMAIN ); ?>
                        </button>
                        <hr>
                    </div>
                    <div>
						<?php
						if ( $is_student ) {
							require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_student_fees_report.php' );
						} else {
							require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_fees_report.php' );
						} ?>
                    </div>
                </div>
            </div>
        </div>
		<?php
		die();
	}

	/* View and print student pending fees */
	public static function print_student_pending_fees() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$id  = intval( sanitize_text_field( $_POST['id'] ) );
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		?>
        <div class="row">
            <div class="col">
                <div class="mb-3 mt-2">
                    <div class="text-center">
                        <button type="button" id="wl-pending-fees-print" class="btn btn-sm btn-success">
                            <i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Pending Fees', WL_MIM_DOMAIN ); ?>
                        </button>
                        <hr>
                    </div>
                    <div>
					<?php require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_pending_fees.php' ); ?>
                    </div>
                </div>
            </div>
        </div>
		<?php
		die();
	}

	/* View and print student certificate */
	public static function print_student_certificate() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}


		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$page_url = WL_MIM_Helper::get_certificates_page_url();

		$institute_id = WL_MIM_Helper::get_current_institute_id();
		$certificate_student_id  = intval( sanitize_text_field( $_POST['id'] ) );
		

		$certificate_student = WL_MIM_Helper::fetch_certificate_view($institute_id, $certificate_student_id );
	
		if ( ! $certificate_student ) {
			die;
		}
		$user_id = $certificate_student->student_record_id;

		require_once WL_MIM_PLUGIN_DIR_PATH . "admin/inc/helpers/certificate_student.php";
		// $student = WLSM_M_Staff_General::fetch_student( $school_id, $session_id, $student_id );
		$student = WL_MIM_StudentHelper::fetch_student($institute_id, $user_id);

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
		

		// $id  = intval( sanitize_text_field( $_POST['id'] ) );
		// $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND id = $id AND institute_id = $institute_id" );
		// if ( ! $row ) {
		// 	die();
		// }
		// $batch = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $row->batch_id AND institute_id = $institute_id" );
		// if ( ! $batch || ! WL_MIM_Helper::is_batch_ended( $batch->start_date, $batch->end_date ) ) {
		// 	die();
		// }

		// $user = null;
		// if ( $row->user_id ) {
		// 	$user              = $wpdb->get_row( "SELECT * FROM {$wpdb->base_prefix}users WHERE ID = $row->user_id" );
		// 	$user_institute_id = get_user_meta( $user->ID, 'wlim_institute_id', true );
		// 	if ( $user_institute_id !== $institute_id ) {
		// 		die();
		// 	}
		// }
		// $authorized = false;
		// if ( current_user_can( 'wl_min_manage_report' ) ) {
		// 	$authorized = true;
		// } else {
		// 	if ( current_user_can( 'wl_min_student' ) ) {
		// 		if ( ( get_current_user_id() != 0 ) && ( get_current_user_id() == $user->ID ) ) {
		// 			$authorized = true;
		// 		}
		// 	} else {
		// 		$authorized = false;
		// 	}
		// }
		// if ( ! $authorized ) {
		// 	die();
		// }
		// ?>
        <div class="row">
            <div class="col">
                <div class="mb-3 mt-2">
                    <div class="text-center">
                        <!-- <button type="button" id="wl-certificate-print" class="btn btn-sm btn-success">
                            <i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Certificate', WL_MIM_DOMAIN ); ?>
                        </button> -->
						
                        <hr>
                    </div>
					<?php require_once WL_MIM_PLUGIN_DIR_PATH . "admin/inc/helpers/certificate.php"; ?>
                </div>
            </div>
        </div>
		<?php
		die();
	}

	/* Overall report selection */
	public static function overall_report_selection() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}

		$report_by = isset( $_REQUEST['report_by'] ) ? sanitize_text_field( $_REQUEST['report_by'] ) : '';

		if ( ! in_array( $report_by, array_keys( WL_MIM_Helper::get_report_by_list() ) ) ) {
			ob_start();
			?>
            <strong class="text-danger">
				<?php
				esc_html_e( 'Please select valid option.', WL_MIM_DOMAIN ); ?>
            </strong>
			<?php
			$json = json_encode( array(
				'element' => ''
			) );

			$html = ob_get_clean();
			wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
		}

		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		ob_start();
		$json = json_encode( array(
			'element' => ''
		) );
		if ( $report_by == 'pending-fees-by-batch' || $report_by == 'attendance-by-batch' ) {
			/* Get batches */
			$data        = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC" );
			$course_data = $wpdb->get_results( "SELECT id, course_name, course_code FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );
			if ( count( $data ) !== 0 ) { ?>
                <div class="form-group wlim-selectpicker">
                    <label for="wlim-batch" class="col-form-label"><?php esc_html_e( "Batch", WL_MIM_DOMAIN ); ?>:</label>
                    <?php if ( $report_by == 'attendance-by-batch' ) { ?>
                    <select name="batch[]" class="form-control selectpicker" id="wlim-batch" multiple data-actions-box="true" data-none-selected-text="<?php esc_attr_e( 'Select Batch', WL_MIM_DOMAIN ); ?>">
                    <?php } else { ?>
                    <select name="batch" class="form-control selectpicker" id="wlim-batch">
                        <option value="">-------- <?php esc_html_e( "Select a Batch", WL_MIM_DOMAIN ); ?> --------</option>
                    <?php } ?>
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
                            <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $batch_info ) . " ( " . esc_html( $timing ) . " ) ( " . WL_MIM_Helper::get_batch_status( $row->start_date, $row->end_date ) . " ) - " . esc_html( $course ); ?></option>
							<?php
						} ?>
                    </select>
                </div>
				<?php
				$json = json_encode( array(
					'element' => '#wlim-batch'
				) );
			} else { ?>
                <strong class="text-danger">
					<?php
					esc_html_e( 'There is no batch.', WL_MIM_DOMAIN ); ?>
                </strong>
				<?php
				$json = json_encode( array(
					'element' => ''
				) );
			}
		}
		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* View overall report */
	public static function view_overall_report() {
		self::check_permission();
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}

		$report_by = isset( $_REQUEST['report_by'] ) ? sanitize_text_field( $_REQUEST['report_by'] ) : null;

		if ( ! in_array( $report_by, array_keys( WL_MIM_Helper::get_report_by_list() ) ) ) {
			ob_start();
			?>
            <strong class="text-danger">
				<?php
				esc_html_e( 'Please select valid option.', WL_MIM_DOMAIN ); ?>
            </strong>
			<?php
			$json = json_encode( array(
				'element' => ''
			) );
			$html = ob_get_clean();
			wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
		}

		$custom_duration = isset( $_REQUEST['custom_duration'] ) ? boolval( sanitize_text_field( $_REQUEST['custom_duration'] ) ) : 0;

		if ( ! in_array( $report_by, array( 'current-students', 'pending-fees-by-batch' ) ) ) {

			if ( $custom_duration ) {
				$duration_from = ( isset( $_REQUEST['duration_from'] ) && ! empty( $_REQUEST['duration_from'] ) ) ? date( "Y-m-d H:i:s", strtotime( sanitize_text_field( $_REQUEST['duration_from'] ) ) ) : null;
				$duration_to   = ( isset( $_REQUEST['duration_to'] ) && ! empty( $_REQUEST['duration_to'] ) ) ? date( "Y-m-d H:i:s", strtotime( sanitize_text_field( $_REQUEST['duration_to'] ) ) ) : null;

				if ( empty( $duration_from ) || empty( $duration_to ) ) {
					ob_start();
					?>
                    <strong class="text-danger">
						<?php
						esc_html_e( 'Please provide valid custom duration.', WL_MIM_DOMAIN ); ?>
                    </strong>
					<?php
					$json = json_encode( array(
						'element' => ''
					) );
					$html = ob_get_clean();
					wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
				}
			} else {
				$predefined_period = isset( $_REQUEST['predefined_period'] ) ? sanitize_text_field( $_REQUEST['predefined_period'] ) : null;

				if ( $report_by !== 'outstanding-fees' && ! in_array( $predefined_period, array_keys( WL_MIM_Helper::get_report_period() ) ) ) {
					ob_start();
					?>
                    <strong class="text-danger">
						<?php
						esc_html_e( 'Please select valid option.', WL_MIM_DOMAIN ); ?>
                    </strong>
					<?php
					$json = json_encode( array(
						'element' => ''
					) );
					$html = ob_get_clean();
					wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
				}
			}

			if ( ! $custom_duration ) {
				if ( $predefined_period == 'today' ) {
					$query = "AND created_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 day) ";
				} elseif ( $predefined_period == 'yesterday' ) {
					$query = "AND DATE(created_at) = DATE(NOW() - INTERVAL 1 DAY) ";
				} elseif ( $predefined_period == 'this-week' ) {
					$query = "AND created_at BETWEEN DATE_ADD(CURDATE(), INTERVAL 1-DAYOFWEEK(CURDATE()) DAY) AND DATE_ADD(CURDATE(), INTERVAL 7-DAYOFWEEK(CURDATE()) DAY) ";
				} elseif ( $predefined_period == 'this-month' ) {
					$query = "AND created_at BETWEEN DATE_SUB(CURDATE(),INTERVAL (DAY(CURDATE())-1) DAY) AND LAST_DAY(NOW()) ";
				} elseif ( $predefined_period == 'this-year' ) {
					$query = "AND YEAR(created_at) = YEAR(CURDATE()) ";
				} elseif ( $predefined_period == 'last-year' ) {
					$query = "AND YEAR(created_at) = YEAR(CURDATE()) - 1 ";
				} else {
					$query = '';
				}
			} else {
				$query = "AND created_at BETWEEN '$duration_from' AND '$duration_to' ";
			}
		}

		global $wpdb;
		$institute_id = WL_MIM_Helper::get_current_institute_id();

		$general_receipt_prefix    = WL_MIM_SettingHelper::get_general_receipt_prefix_settings( $institute_id );
		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$json_data = array(
			'element' => '',
		);
		ob_start();
		if ( $report_by == 'attendance-by-batch' ) {
			$duration_from = ( isset( $_REQUEST['duration_from'] ) && ! empty( $_REQUEST['duration_from'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['duration_from'] ) ) ) : null;
			$duration_to   = ( isset( $_REQUEST['duration_to'] ) && ! empty( $_REQUEST['duration_to'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['duration_to'] ) ) ) : null;
			if ( $custom_duration ) {
				$query = "AND CAST(created_at AS DATE) BETWEEN '$duration_from' AND '$duration_to' ";
			}

			$batch_ids = ( isset( $_REQUEST['batch'] ) && is_array( $_REQUEST['batch'] ) ) ? array_map( 'absint', $_REQUEST['batch'] ) : array();

			$batch_ids_string = implode(',', $batch_ids);

			$date_query = $query;

			$query = "SELECT s.ID as student_id, s.enrollment_id, s.first_name, s.last_name, s.phone, s.is_active, b.batch_name, b.batch_code, c.course_name, c.course_code,
			COUNT(a.status) as attendance_count,
			COUNT(IF(a.status='p',1, NULL)) as present_count,
			COUNT(IF(a.status='a',1, NULL)) as absent_count
			FROM {$wpdb->prefix}wl_min_attendance as a
			JOIN {$wpdb->prefix}wl_min_students as s ON s.ID = a.student_id
			JOIN {$wpdb->prefix}wl_min_batches as b ON s.batch_id = b.id
			JOIN {$wpdb->prefix}wl_min_courses as c ON b.course_id = c.id
			WHERE s.is_deleted = 0";

			if ( ! empty( $batch_ids ) ) {
				$query .= " AND s.batch_id IN($batch_ids_string)";
			}

			$query .= (" AND s.institute_id = $institute_id " . str_replace( 'created_at', 'a.attendance_date', $date_query ) . 'GROUP BY s.ID');

			$data = $wpdb->get_results( $query );

			$batch_data = $wpdb->get_results( "SELECT id, batch_code, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K );
			?>
            <div class="row">
                <div class="card col">
                    <div class="card-header bg-info text-white">
                        <h6><?php esc_html_e( 'Attendance By Batch', WL_MIM_DOMAIN ); ?></h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped table-bordered" id="attendance-by-batch-table-report">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Attendance', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Present', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Absent', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							if ( count( $data ) ) {
								foreach ( $data as $row ) {
									if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
										$student_id = $row->enrollment_id;
									} else {
										$student_id = $row->id;
									}
									$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
									$first_name       = $row->first_name ? $row->first_name : '-';
									$last_name        = $row->last_name ? $row->last_name : '-';
									$course           = $row->course_name . ' (' . $row->course_code . ') ';
									$batch            = '(' . $row->batch_code . ') ' . $row->batch_name;
									$attendance_count = $row->attendance_count ? $row->attendance_count : '0';
									$present_count    = $row->present_count ? $row->present_count : '0';
									$absent_count     = $row->absent_count ? $row->absent_count : '0';
									$phone            = $row->phone ? $row->phone : '-';
									$is_acitve        = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
									$date             = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
									?>
                                    <tr>
                                        <td><?php echo esc_html( $enrollment_id ); ?></td>
                                        <td><?php echo esc_html( $first_name ); ?></td>
                                        <td><?php echo esc_html( $last_name ); ?></td>
                                        <td><?php echo esc_html( $course ); ?></td>
                                        <td><?php echo esc_html( $batch ); ?></td>
                                        <td><?php echo esc_html( $attendance_count ); ?></td>
                                        <td><?php echo esc_html( $present_count ); ?></td>
                                        <td><?php echo esc_html( $absent_count ); ?></td>
                                        <td><?php echo esc_html( $phone ); ?></td>
                                        <td><?php echo esc_html( $is_acitve ); ?></td>
                                    </tr>
									<?php
								}
							}
							$total = count( $data );
							?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12 col-xs-12"><?php esc_html_e( 'Total Students in this Batch', WL_MIM_DOMAIN ); ?>: <strong><?php echo esc_html( $total ); ?></strong></div>
                        </div>
                    </div>
                </div>
           </div>
		<?php
			$element = '#attendance-by-batch-table-report';
		} elseif ( $report_by == 'pending-fees-by-batch' ) {
			$batch_id   = isset( $_REQUEST['batch'] ) ? intval( sanitize_text_field( $_REQUEST['batch'] ) ) : null;
			$print_mode = isset( $_REQUEST['print_mode'] ) ? sanitize_text_field( $_REQUEST['print_mode'] ) : 'portrait';
			$query      = "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND batch_id = $batch_id AND institute_id = $institute_id ORDER BY id DESC";
			$data       = $wpdb->get_results( $query );

			$course_data = $wpdb->get_results( "SELECT id, course_name, course_code, fees, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );

			$batch_data = $wpdb->get_results( "SELECT id, batch_code, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K );
			?>
            <div class="row">
                <div class="card col">
                    <div class="card-header bg-info text-white">
                        <h6><?php esc_html_e( 'Pending Fees By Batch', WL_MIM_DOMAIN ); ?></h6>
                    </div>
                    <div class="card-body">
						<?php
						if ( count( $data ) ) { ?>
                            <div class="row">
                                <div class="col-md-12 col-xs-12">
                                    <a class="mr-2 btn btn-primary" role="button"
                                       href="#print-id-cards"
                                       data-keyboard="false" data-backdrop="static" data-toggle="modal"><?php _e( 'Print ID Cards', WL_MIM_DOMAIN ); ?></a>
                                    <!-- print id cards modal -->
                                    <div class="modal fade" id="print-id-cards"
                                         tabindex="-1" role="dialog"
                                         aria-labelledby="print-id-cards-label"
                                         aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered"
                                             id="print-id-cards-dialog"
                                             role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title w-100 text-center"
                                                        id="print-id-cards-label"><?php _e( 'View and Print ID Cards', WL_MIM_DOMAIN ); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body pr-4 pl-4">
                                                    <div class="row">
                                                        <div class="col">
                                                            <div class="mb-3 mt-2">
                                                                <div class="text-center">
                                                                    <button type="button"
                                                                            id="wl-id-cards-print"
                                                                            class="btn btn-sm btn-success"><i
                                                                                class="fa fa-print text-white"></i>&nbsp;<?php _e( 'Print ID Cards', WL_MIM_DOMAIN ); ?>
                                                                    </button>
                                                                    <hr>
                                                                </div>
																<?php
																require( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_id_cards.php' ); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal"><?php _e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end - print id cards modal -->
                                </div>
                            </div>
                            <hr>
							<?php
						}
						?>
                        <table class="table table-hover table-striped table-bordered" id="pending-fees-by-batch-table-report">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Fees Payable', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Fees Paid', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Fees Status', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Registration Date', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							if ( count( $data ) ) {
								foreach ( $data as $row ) {
									$fees          = unserialize( $row->fees );
									$fees_payable  = WL_MIM_Helper::get_fees_total( $fees['payable'] );
									$fees_paid     = WL_MIM_Helper::get_fees_total( $fees['paid'] );
									$pending_fees  = number_format( $fees_payable - $fees_paid, 2, '.', '' );
									$id            = $row->id;
									if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
										$student_id = $row->enrollment_id;
									} else {
										$student_id = $row->id;
									}
									$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
									$first_name    = $row->first_name ? $row->first_name : '-';
									$last_name     = $row->last_name ? $row->last_name : '-';
									$phone         = $row->phone ? $row->phone : '-';
									$email         = $row->email ? $row->email : '-';
									$is_acitve     = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
									$date          = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
									$added_by      = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

									$course   = '-';
									$duration = '-';
									$batch    = '-';
									if ( $row->course_id && isset( $course_data[ $row->course_id ] ) ) {
										$course_name = $course_data[ $row->course_id ]->course_name;
										$course_code = $course_data[ $row->course_id ]->course_code;
										$course      = "$course_name ($course_code)";
										$duration    = $course_data[ $row->course_id ]->duration . " " . $course_data[ $row->course_id ]->duration_in;
									}

									if ( $row->batch_id && isset( $batch_data[ $row->batch_id ] ) ) {
										$time_from    = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_from ) );
										$time_to      = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_to ) );
										$timing       = "$time_from - $time_to";
										$batch        = esc_html( $batch_data[ $row->batch_id ]->batch_code ) . '<br>( ' . esc_html( $timing ) . ' )';
										$batch_status = WL_MIM_Helper::get_batch_status( $batch_data[ $row->batch_id ]->start_date, $batch_data[ $row->batch_id ]->end_date );
									}

									if ( $pending_fees > 0 ) {
										$fees_status = '<strong class="text-danger">' . esc_html__( 'Pending', WL_MIM_DOMAIN ) . ': </strong><br><strong>' . $pending_fees . '</strong>';
									} else {
										$fees_status = '<strong class="text-success">' . esc_html__( 'Paid', WL_MIM_DOMAIN ) . '</strong>';
									}
									?>
                                    <tr>
                                        <td><?php echo esc_html( $enrollment_id ); ?></td>
                                        <td><?php echo esc_html( $first_name ); ?></td>
                                        <td><?php echo esc_html( $last_name ); ?></td>
                                        <td><?php echo esc_html( $course ); ?></td>
                                        <td><?php echo wp_kses( $batch, array( 'br' ) ); ?></td>
                                        <td><?php echo esc_html( $duration ); ?></td>
                                        <td><?php echo wp_kses( $batch_status, array(
												'strong' => array(
													'class' => 'text-danger',
													'text-primary',
													'text-success',
													'text-warning'
												)
											) ); ?></td>
                                        <td><?php echo esc_html( $fees_payable ); ?></td>
                                        <td><?php echo esc_html( $fees_paid ); ?></td>
                                        <td><?php echo wp_kses( $fees_status, array(
												'strong' => array(
													'class' => 'text-danger',
													'text-primary',
													'text-success',
													'text-warning'
												),
												'br'
											) ); ?></td>
                                        <td><?php echo esc_html( $phone ); ?></td>
                                        <td><?php echo esc_html( $email ); ?></td>
                                        <td><?php echo esc_html( $is_acitve ); ?></td>
                                        <td><?php echo esc_html( $added_by ); ?></td>
                                        <td><?php echo esc_html( $date ); ?></td>
                                    </tr>
									<?php
								}
							}
							$total = count( $data );
							?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12 col-xs-12"><?php esc_html_e( 'Total Students in this Batch', WL_MIM_DOMAIN ); ?>: <strong><?php echo esc_html( $total ); ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			$element = '#pending-fees-by-batch-table-report';
		} elseif ( $report_by == 'student-registrations' ) {
			$query = "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id " . $query . "ORDER BY id DESC";
			$data  = $wpdb->get_results( $query );

			$course_data = $wpdb->get_results( "SELECT id, course_name, course_code, fees, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );

			$batch_data = $wpdb->get_results( "SELECT id, batch_code, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K );
			?>
            <div class="row">
                <div class="card col">
                    <div class="card-header bg-info text-white">
                        <h6><?php esc_html_e( 'Student Registrations', WL_MIM_DOMAIN ); ?></h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped table-bordered" id="student-registrations-table-report">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Fees Payable', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Fees Paid', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Fees Status', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Registration Date', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							if ( count( $data ) ) {
								foreach ( $data as $row ) {
									$id            = $row->id;
									if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
										$student_id = $row->enrollment_id;
									} else {
										$student_id = $row->id;
									}
									$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
									$first_name    = $row->first_name ? $row->first_name : '-';
									$last_name     = $row->last_name ? $row->last_name : '-';
									$fees          = unserialize( $row->fees );
									$fees_payable  = WL_MIM_Helper::get_fees_total( $fees['payable'] );
									$fees_paid     = WL_MIM_Helper::get_fees_total( $fees['paid'] );
									$pending_fees  = number_format( $fees_payable - $fees_paid, 2, '.', '' );
									$phone         = $row->phone ? $row->phone : '-';
									$email         = $row->email ? $row->email : '-';
									$is_acitve     = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
									$date          = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
									$added_by      = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

									$course   = '-';
									$duration = '-';
									$batch    = '-';
									if ( $row->course_id && isset( $course_data[ $row->course_id ] ) ) {
										$course_name = $course_data[ $row->course_id ]->course_name;
										$course_code = $course_data[ $row->course_id ]->course_code;
										$course      = "$course_name ($course_code)";
										$duration    = $course_data[ $row->course_id ]->duration . " " . $course_data[ $row->course_id ]->duration_in;
									}

									if ( $row->batch_id && isset( $batch_data[ $row->batch_id ] ) ) {
										$time_from    = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_from ) );
										$time_to      = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_to ) );
										$timing       = "$time_from - $time_to";
										$batch        = esc_html( $batch_data[ $row->batch_id ]->batch_code ) . '<br>( ' . esc_html( $timing ) . ' )';
										$batch_status = WL_MIM_Helper::get_batch_status( $batch_data[ $row->batch_id ]->start_date, $batch_data[ $row->batch_id ]->end_date );
									}

									if ( $pending_fees > 0 ) {
										$fees_status = '<strong class="text-danger">' . esc_html__( 'Pending', WL_MIM_DOMAIN ) . ': </strong><br><strong>' . $pending_fees . '</strong>';
									} else {
										$fees_status = '<strong class="text-success">' . esc_html__( 'Paid', WL_MIM_DOMAIN ) . '</strong>';
									}
									?>
                                    <tr>
                                        <td><?php echo esc_html( $enrollment_id ); ?></td>
                                        <td><?php echo esc_html( $first_name ); ?></td>
                                        <td><?php echo esc_html( $last_name ); ?></td>
                                        <td><?php echo esc_html( $course ); ?></td>
                                        <td><?php echo wp_kses( $batch, array( 'br' ) ); ?></td>
                                        <td><?php echo esc_html( $duration ); ?></td>
                                        <td><?php echo wp_kses( $batch_status, array(
												'strong' => array(
													'class' => 'text-danger',
													'text-primary',
													'text-success',
													'text-warning'
												)
											) ); ?></td>
                                        <td><?php echo esc_html( $fees_payable ); ?></td>
                                        <td><?php echo esc_html( $fees_paid ); ?></td>
                                        <td><?php echo wp_kses( $fees_status, array(
												'strong' => array(
													'class' => 'text-danger',
													'text-primary',
													'text-success',
													'text-warning'
												),
												'br'
											) ); ?></td>
                                        <td><?php echo esc_html( $phone ); ?></td>
                                        <td><?php echo esc_html( $email ); ?></td>
                                        <td><?php echo esc_html( $is_acitve ); ?></td>
                                        <td><?php echo esc_html( $added_by ); ?></td>
                                        <td><?php echo esc_html( $date ); ?></td>
                                    </tr>
									<?php
								}
							}
							$total = $data ? count( $data ) : 0;
							?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12 col-xs-12"><?php esc_html_e( 'Total Student Registrations', WL_MIM_DOMAIN ); ?> : <strong><?php echo esc_html( $total ); ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			$element = '#student-registrations-table-report';
		} elseif ( $report_by == 'current-students' ) {
			$query = "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC";
			$data  = $wpdb->get_results( $query );

			$course_data = $wpdb->get_results( "SELECT id, course_name, course_code, fees, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );

			$batch_data = $wpdb->get_results( "SELECT id, batch_code, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K );
			?>
            <div class="row">
                <div class="card col">
                    <div class="card-header bg-info text-white">
                        <h6><?php esc_html_e( 'Current Students', WL_MIM_DOMAIN ); ?></h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped table-bordered" id="current-students-table-report">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Fees Payable', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Fees Paid', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Fees Status', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Registration Date', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							$total = 0;
							if ( count( $data ) ) {
								foreach ( $data as $row ) {
									$batch = '-';
									if ( $row->batch_id && isset( $batch_data[ $row->batch_id ] ) ) {
										$is_current_batch = WL_MIM_Helper::is_current_batch( $batch_data[ $row->batch_id ]->start_date, $batch_data[ $row->batch_id ]->end_date );
										if ( $is_current_batch ) {
											$total ++;

											$time_from    = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_from ) );
											$time_to      = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_to ) );
											$timing       = "$time_from - $time_to";
											$batch        = esc_html( $batch_data[ $row->batch_id ]->batch_code ) . '<br>( ' . esc_html( $timing ) . ' )';
											$batch_status = WL_MIM_Helper::get_batch_status( $batch_data[ $row->batch_id ]->start_date, $batch_data[ $row->batch_id ]->end_date );

											$id            = $row->id;
											if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
												$student_id = $row->enrollment_id;
											} else {
												$student_id = $row->id;
											}
											$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
											$first_name    = $row->first_name ? $row->first_name : '-';
											$last_name     = $row->last_name ? $row->last_name : '-';
											$fees          = unserialize( $row->fees );
											$fees_payable  = WL_MIM_Helper::get_fees_total( $fees['payable'] );
											$fees_paid     = WL_MIM_Helper::get_fees_total( $fees['paid'] );
											$pending_fees  = number_format( $fees_payable - $fees_paid, 2, '.', '' );
											$phone         = $row->phone ? $row->phone : '-';
											$email         = $row->email ? $row->email : '-';
											$is_acitve     = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
											$date          = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
											$added_by      = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

											$course   = '-';
											$duration = '-';
											if ( $row->course_id && isset( $course_data[ $row->course_id ] ) ) {
												$course_name = $course_data[ $row->course_id ]->course_name;
												$course_code = $course_data[ $row->course_id ]->course_code;
												$course      = "$course_name ($course_code)";
												$duration    = $course_data[ $row->course_id ]->duration . " " . $course_data[ $row->course_id ]->duration_in;

												if ( $pending_fees > 0 ) {
													$fees_status = '<strong class="text-danger">' . esc_html__( 'Pending', WL_MIM_DOMAIN ) . ': </strong><br><strong>' . $pending_fees . '</strong>';
												} else {
													$fees_status = '<strong class="text-success">' . esc_html__( 'Paid', WL_MIM_DOMAIN ) . '</strong>';
												}
											} ?>
                                            <tr>
                                                <td><?php echo esc_html( $enrollment_id ); ?></td>
                                                <td><?php echo esc_html( $first_name ); ?></td>
                                                <td><?php echo esc_html( $last_name ); ?></td>
                                                <td><?php echo esc_html( $course ); ?></td>
                                                <td><?php echo wp_kses( $batch, array( 'br' ) ); ?></td>
                                                <td><?php echo esc_html( $duration ); ?></td>
                                                <td><?php echo wp_kses( $batch_status, array(
														'strong' => array(
															'class' => 'text-danger',
															'text-primary',
															'text-success',
															'text-warning'
														)
													) ); ?></td>
                                                <td><?php echo esc_html( $fees_payable ); ?></td>
                                                <td><?php echo esc_html( $fees_paid ); ?></td>
                                                <td><?php echo wp_kses( $fees_status, array(
														'strong' => array(
															'class' => 'text-danger',
															'text-primary',
															'text-success',
															'text-warning'
														),
														'br'
													) ); ?></td>
                                                <td><?php echo esc_html( $phone ); ?></td>
                                                <td><?php echo esc_html( $email ); ?></td>
                                                <td><?php echo esc_html( $is_acitve ); ?></td>
                                                <td><?php echo esc_html( $added_by ); ?></td>
                                                <td><?php echo esc_html( $date ); ?></td>
                                            </tr>
											<?php
										}
									}
								}
							} ?>
                            </tbody>
                        </table>
                        <div class="row">
							<div class="col-md-12 col-xs-12">
								<?php esc_html_e( 'Total Current Students', WL_MIM_DOMAIN ); ?> : <strong><?php echo esc_html( $total ); ?></strong>
							</div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			$element = '#current-students-table-report';
		} elseif ( $report_by == 'students-drop-out' ) {
			$query = str_replace( 'created_at', 'inactive_at', $query );
			$query = "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id AND is_active = 0 " . $query . "ORDER BY id DESC";
			$data  = $wpdb->get_results( $query );

			$course_data = $wpdb->get_results( "SELECT id, course_name, course_code, fees, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );

			$batch_data = $wpdb->get_results( "SELECT id, batch_code, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K );
			?>
            <div class="row">
                <div class="card col">
                    <div class="card-header bg-info text-white">
                        <h6><?php esc_html_e( 'Students Drop-Out', WL_MIM_DOMAIN ); ?></h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped table-bordered" id="students-drop-out-table-report">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Fees Payable', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Fees Paid', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Fees Status', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Dropout At', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Registration Date', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							if ( count( $data ) ) {
								foreach ( $data as $row ) {
									$id            = $row->id;
									if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
										$student_id = $row->enrollment_id;
									} else {
										$student_id = $row->id;
									}
									$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
									$first_name    = $row->first_name ? $row->first_name : '-';
									$last_name     = $row->last_name ? $row->last_name : '-';
									$fees          = unserialize( $row->fees );
									$fees_payable  = WL_MIM_Helper::get_fees_total( $fees['payable'] );
									$fees_paid     = WL_MIM_Helper::get_fees_total( $fees['paid'] );
									$pending_fees  = number_format( $fees_payable - $fees_paid, 2, '.', '' );
									$phone         = $row->phone ? $row->phone : '-';
									$email         = $row->email ? $row->email : '-';
									$is_acitve     = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
									$dropout_at    = $row->inactive_at ? date_format( date_create( $row->inactive_at ), "d-m-Y g:i A" ) : '-';
									$date          = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
									$added_by      = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

									$course   = '-';
									$duration = '-';
									$batch    = '-';
									if ( $row->course_id && isset( $course_data[ $row->course_id ] ) ) {
										$course_name = $course_data[ $row->course_id ]->course_name;
										$course_code = $course_data[ $row->course_id ]->course_code;
										$course      = "$course_name ($course_code)";
										$duration    = $course_data[ $row->course_id ]->duration . " " . $course_data[ $row->course_id ]->duration_in;
									}

									if ( $row->batch_id && isset( $batch_data[ $row->batch_id ] ) ) {
										$time_from    = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_from ) );
										$time_to      = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_to ) );
										$timing       = "$time_from - $time_to";
										$batch        = esc_html( $batch_data[ $row->batch_id ]->batch_code ) . '<br>( ' . esc_html( $timing ) . ' )';
										$batch_status = WL_MIM_Helper::get_batch_status( $batch_data[ $row->batch_id ]->start_date, $batch_data[ $row->batch_id ]->end_date );
									}

									if ( $pending_fees > 0 ) {
										$fees_status = '<strong class="text-danger">' . esc_html__( 'Pending', WL_MIM_DOMAIN ) . ': </strong><br><strong>' . $pending_fees . '</strong>';
									} else {
										$fees_status = '<strong class="text-success">' . esc_html__( 'Paid', WL_MIM_DOMAIN ) . '</strong>';
									}
									?>
                                    <tr>
                                        <td><?php echo esc_html( $enrollment_id ); ?></td>
                                        <td><?php echo esc_html( $first_name ); ?></td>
                                        <td><?php echo esc_html( $last_name ); ?></td>
                                        <td><?php echo esc_html( $course ); ?></td>
                                        <td><?php echo wp_kses( $batch, array( 'br' ) ); ?></td>
                                        <td><?php echo esc_html( $duration ); ?></td>
                                        <td><?php echo wp_kses( $batch_status, array(
												'strong' => array(
													'class' => 'text-danger',
													'text-primary',
													'text-success',
													'text-warning'
												)
											) ); ?></td>
                                        <td><?php echo esc_html( $fees_payable ); ?></td>
                                        <td><?php echo esc_html( $fees_paid ); ?></td>
                                        <td><?php echo wp_kses( $fees_status, array(
												'strong' => array(
													'class' => 'text-danger',
													'text-primary',
													'text-success',
													'text-warning'
												),
												'br'
											) ); ?></td>
                                        <td><?php echo esc_html( $phone ); ?></td>
                                        <td><?php echo esc_html( $email ); ?></td>
                                        <td><?php echo esc_html( $dropout_at ); ?></td>
                                        <td><?php echo esc_html( $added_by ); ?></td>
                                        <td><?php echo esc_html( $date ); ?></td>
                                    </tr>
									<?php
								}
							}
							$total = $data ? count( $data ) : 0;
							?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
								<?php esc_html_e( 'Total Students Dropout', WL_MIM_DOMAIN ); ?>:<strong><?php echo esc_html( $total ); ?></strong>
							</div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			$element = '#students-drop-out-table-report';
		} elseif ( $report_by == 'fees-collection' ) {
			$query = "SELECT * FROM {$wpdb->prefix}wl_min_installments WHERE is_deleted = 0 AND institute_id = $institute_id " . $query . "ORDER BY id DESC";
			$data  = $wpdb->get_results( $query );

			$student_data = $wpdb->get_results( "SELECT id, first_name, last_name FROM {$wpdb->prefix}wl_min_students WHERE institute_id = $institute_id ORDER BY first_name, last_name, id DESC", OBJECT_K );
			?>
            <div class="row">
                <div class="card col">
                    <div class="card-header bg-info text-white">
                        <h6><?php esc_html_e( 'Fees Collection', WL_MIM_DOMAIN ); ?></h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped table-bordered" id="fees-collection-table-report">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Receipt', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Total Amount', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Student Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Payment Method', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Payment ID', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							$revenue = 0;
							if ( count( $data ) ) {
								foreach ( $data as $row ) {
									$id             = $row->id;
									$receipt        = WL_MIM_Helper::get_receipt_with_prefix( $id, $general_receipt_prefix );
									$fees           = unserialize( $row->fees );
									$amount         = WL_MIM_Helper::get_fees_total( $fees['paid'] );
									$payment_method = $row->payment_method ? $row->payment_method : '-';
									$payment_id     = $row->payment_id ? $row->payment_id : '-';
									$date           = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
									$added_by       = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

									$student_name = '-';
									if ( $row->student_id && isset( $student_data[ $row->student_id ] ) ) {
										$student_name  = $student_data[ $row->student_id ]->first_name . " " . $student_data[ $row->student_id ]->last_name;
										$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_data[ $row->student_id ]->id, $general_enrollment_prefix );
									}

									$revenue += array_sum( $fees['paid'] );
									?>
                                    <tr>
                                        <td><?php echo esc_html( $receipt ) . '<a class="ml-2" href="#print-installment-fee-receipt" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . $id . '"><i class="fa fa-print"></i></a>'; ?></td>
                                        <td><?php echo esc_html( $amount ); ?></td>
                                        <td><?php echo esc_html( $enrollment_id ); ?></td>
                                        <td><?php echo esc_html( $student_name ); ?></td>
                                        <td><?php echo esc_html( $payment_method ); ?></td>
                                        <td><?php echo esc_html( $payment_id ); ?></td>
                                        <td><?php echo esc_html( $date ); ?></td>
                                        <td><?php echo esc_html( $added_by ); ?></td>
                                    </tr>
									<?php
								}
							}
							$revenue = number_format( max( floatval( $revenue ), 0 ), 2, '.', '' );
							?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
								<?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?>: <strong><?php echo esc_html( $revenue ); ?></strong>
							</div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			$element = '#fees-collection-table-report';
		} elseif ( $report_by == 'outstanding-fees' ) {
			$batch_id = isset( $_REQUEST['batch'] ) ? intval( sanitize_text_field( $_REQUEST['batch'] ) ) : null;

			$invoice_date_range_query = $query;

			if ( empty( $invoice_date_range_query ) ) {
				if ( $batch_id ) {
					$query = "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id AND batch_id = $batch_id ORDER BY id DESC";
				} else {
					$query = "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id ORDER BY id DESC";
				}

				$data = $wpdb->get_results( $query );

				$course_data = $wpdb->get_results( "SELECT id, course_name, course_code, fees, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE institute_id = $institute_id ORDER BY course_name", OBJECT_K );

				$batch_data = $wpdb->get_results( "SELECT id, batch_code, time_from, time_to, start_date, end_date FROM {$wpdb->prefix}wl_min_batches WHERE institute_id = $institute_id ORDER BY id", OBJECT_K );
				?>
	            <div class="row">
	                <div class="card col">
	                    <div class="card-header bg-info text-white">
	                        <h6><?php esc_html_e( 'Students having Outstanding Fees', WL_MIM_DOMAIN ); ?></h6>
	                    </div>
	                    <div class="card-body">
	                        <table class="table table-hover table-striped table-bordered" id="outstanding-fees-table-report">
	                            <thead>
	                            <tr>
	                                <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Total Fees Payable', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Total Fees Paid', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Fees Status', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Registration Date', WL_MIM_DOMAIN ); ?></th>
	                            </tr>
	                            </thead>
	                            <tbody>
								<?php
								if ( count( $data ) ) {
									$total = 0;
									foreach ( $data as $row ) {
										$fees         = unserialize( $row->fees );
										$fees_payable = WL_MIM_Helper::get_fees_total( $fees['payable'] );
										$fees_paid    = WL_MIM_Helper::get_fees_total( $fees['paid'] );
										$pending_fees = number_format( $fees_payable - $fees_paid, 2, '.', '' );

										if ( $pending_fees > 0 ) {
											$total ++;

											$id            = $row->id;
											if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
												$student_id = $row->enrollment_id;
											} else {
												$student_id = $row->id;
											}
											$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
											$first_name    = $row->first_name ? $row->first_name : '-';
											$last_name     = $row->last_name ? $row->last_name : '-';
											$phone         = $row->phone ? $row->phone : '-';
											$email         = $row->email ? $row->email : '-';
											$is_acitve     = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
											$date          = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
											$added_by      = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

											$course   = '-';
											$duration = '-';
											$batch    = '-';
											if ( $row->course_id && isset( $course_data[ $row->course_id ] ) ) {
												$course_name = $course_data[ $row->course_id ]->course_name;
												$course_code = $course_data[ $row->course_id ]->course_code;
												$course      = "$course_name ($course_code)";
												$duration    = $course_data[ $row->course_id ]->duration . " " . $course_data[ $row->course_id ]->duration_in;
											}

											if ( $row->batch_id && isset( $batch_data[ $row->batch_id ] ) ) {
												$time_from    = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_from ) );
												$time_to      = date( "g:i A", strtotime( $batch_data[ $row->batch_id ]->time_to ) );
												$timing       = "$time_from - $time_to";
												$batch        = esc_html( $batch_data[ $row->batch_id ]->batch_code ) . '<br>( ' . esc_html( $timing ) . ' )';
												$batch_status = WL_MIM_Helper::get_batch_status( $batch_data[ $row->batch_id ]->start_date, $batch_data[ $row->batch_id ]->end_date );
											}

											if ( $pending_fees > 0 ) {
												$fees_status = '<strong class="text-danger">' . esc_html__( 'Pending', WL_MIM_DOMAIN ) . ': </strong><br><strong>' . $pending_fees . '</strong>';
											} else {
												$fees_status = '<strong class="text-success">' . esc_html__( 'Paid', WL_MIM_DOMAIN ) . '</strong>';
											}
											?>
	                                        <tr>
	                                            <td><?php echo esc_html( $enrollment_id ); ?></td>
	                                            <td><?php echo esc_html( $first_name ); ?></td>
	                                            <td><?php echo esc_html( $last_name ); ?></td>
	                                            <td><?php echo esc_html( $course ); ?></td>
	                                            <td><?php echo wp_kses( $batch, array( 'br' ) ); ?></td>
	                                            <td><?php echo esc_html( $duration ); ?></td>
	                                            <td><?php echo wp_kses( $batch_status, array(
														'strong' => array(
															'class' => 'text-danger',
															'text-primary',
															'text-success',
															'text-warning'
														)
													) ); ?></td>
	                                            <td><?php echo esc_html( $fees_payable ); ?></td>
	                                            <td><?php echo esc_html( $fees_paid ); ?></td>
	                                            <td><?php echo wp_kses( $fees_status, array(
														'strong' => array(
															'class' => 'text-danger',
															'text-primary',
															'text-success',
															'text-warning'
														),
														'br'
													) ); ?></td>
	                                            <td><?php echo esc_html( $phone ); ?></td>
	                                            <td><?php echo esc_html( $email ); ?></td>
	                                            <td><?php echo esc_html( $is_acitve ); ?></td>
	                                            <td><?php echo esc_html( $added_by ); ?></td>
	                                            <td><?php echo esc_html( $date ); ?></td>
	                                        </tr>
											<?php
										}
									}
								}
								?>
	                            </tbody>
	                        </table>
	                        <div class="row">
	                            <div class="col-md-12 col-xs-12">
									<?php esc_html_e( 'Students having Outstanding Fees', WL_MIM_DOMAIN ); ?> : <strong><?php echo esc_html( $total ); ?></strong>
								</div>
	                        </div>
	                    </div>
	                </div>
	            </div>
				<?php
			} else {

				if ( $custom_duration ) {
					$invoice_date_range_query = "AND i.created_at BETWEEN CAST('$duration_from' AS DATE) AND CAST('$duration_to' AS DATE) ";
				} else {
					$invoice_date_range_query = str_replace( 'created_at', 'i.created_at', $invoice_date_range_query );
				}

				if ( $batch_id ) {
					$invoice_date_range_query = $invoice_date_range_query . "AND s.batch_id = $batch_id ";
				}

				$query = "SELECT i.id, i.fees, i.invoice_title, i.status, i.created_at, i.added_by, s.first_name, s.last_name, s.id as student_id, s.enrollment_id FROM {$wpdb->prefix}wl_min_invoices as i, {$wpdb->prefix}wl_min_students as s WHERE i.student_id = s.id AND i.status = 'pending' {$invoice_date_range_query}AND i.institute_id = $institute_id GROUP BY i.id ORDER BY i.id DESC";

				$data = $wpdb->get_results( $query );
				?>
	            <div class="row">
	                <div class="card col">
	                    <div class="card-header bg-info text-white">
	                        <h6><?php esc_html_e( 'Outstanding Fee Invoices', WL_MIM_DOMAIN ); ?></h6>
	                    </div>
	                    <div class="card-body">
	                        <table class="table table-hover table-striped table-bordered" id="outstanding-fees-table-report">
	                            <thead>
	                            <tr>
	                                <th scope="col"><?php esc_html_e( 'Invoice No.', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Invoice Title', WL_MIM_DOMAIN ); ?></th>
	                                <th scope="col"><?php esc_html_e( 'Amount Payable', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Student Name', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?></th>
	                            </tr>
	                            </thead>
	                            <tbody>
								<?php
								$total = count( $data );
								if ( count( $data ) ) {
									foreach ( $data as $row ) {
										$id             = $row->id;
										$invoice_number = WL_MIM_Helper::get_invoice( $id );
										$fees           = unserialize( $row->fees );
										$invoice_title  = $row->invoice_title;
										$status_text    = ucwords( $row->status );
										$status         = "<strong class='text-danger'>$status_text</strong>";
										$amount         = WL_MIM_Helper::get_fees_total( $fees['paid'] );
										$date           = date_format( date_create( $row->created_at ), "d-m-Y" );
										$added_by       = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';

										$student_name = $row->first_name;
										if ( $row->last_name ) {
											$student_name .= " $row->last_name";
										}
										if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
											$student_id = $row->enrollment_id;
										} else {
											$student_id = $row->id;
										}
										$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
										?>
                                        <tr>
                                            <td><?php echo esc_html( $invoice_number ) . '<a class="ml-2" href="#print-invoice-fee-invoice" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="' . esc_html( $id ) . '"><i class="fa fa-print"></i></a>'; ?></td>
                                            <td><?php echo esc_html( $invoice_title ); ?></td>
                                            <td><?php echo esc_html( $amount ); ?></td>
                                            <td><?php echo esc_html( $enrollment_id ); ?></td>
                                            <td><?php echo esc_html( $student_name ); ?></td>
                                            <td><?php echo esc_html( $date ); ?></td>
                                        </tr>
									<?php
									}
								}
								?>
	                            </tbody>
	                        </table>
	                        <div class="row">
	                            <div class="col-md-12 col-xs-12">
									<?php esc_html_e( 'Outstanding Fee Invoices', WL_MIM_DOMAIN ); ?> : <strong><?php echo esc_html( $total ); ?></strong>
								</div>
	                        </div>
	                    </div>
	                </div>
	            </div>
				<?php
			}

			$element = '#outstanding-fees-table-report';
		} elseif ( $report_by == 'expense' ) {
			$query_consumption_date = str_replace( 'created_at', 'consumption_date', $query );
			$query                  = "SELECT * FROM {$wpdb->prefix}wl_min_expense WHERE institute_id = $institute_id " . $query_consumption_date . "ORDER BY id DESC";
			$data                   = $wpdb->get_results( $query );
			?>
            <div class="row">
                <div class="card col">
                    <div class="card-header bg-info text-white">
                        <h6><?php esc_html_e( 'Expense', WL_MIM_DOMAIN ); ?></h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped table-bordered" id="expense-table-report">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Consumption Date', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							if ( count( $data ) ) {
								$total = 0;
								foreach ( $data as $row ) {
									$id          = $row->id;
									$title       = $row->title;
									$amount      = $row->amount;
									$total       += $amount;
									$date        = date_format( date_create( $row->consumption_date ), "d-m-Y" );
									$description = $row->description ? $row->description : '-';
									$notes       = $row->notes ? $row->notes : '-';
									$added_by    = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
									$added_on    = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
									?>
                                    <tr>
                                        <td><?php echo esc_html( $title ); ?></td>
                                        <td><?php echo esc_html( $amount ); ?></td>
                                        <td><?php echo esc_html( $date ); ?></td>
                                        <td><?php echo esc_html( $added_by ); ?></td>
                                        <td><?php echo esc_html( $added_on ); ?></td>
                                    </tr>
									<?php
								}
							}
							$total = number_format( $total, 2, '.', '' );
							?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
								<?php esc_html_e( 'Total Expense', WL_MIM_DOMAIN ); ?>: <strong><?php echo esc_html( $total ); ?></strong>
							</div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
			$element = '#expense-table-report';
		}elseif ( $report_by == 'enquiries' ) {
            $query_consumption_date = str_replace( 'created_at', 'created_at', $query );
			$query                  = "SELECT * FROM {$wpdb->prefix}wl_min_enquiries WHERE institute_id = $institute_id " . $query_consumption_date . "ORDER BY id DESC";
			$data                   = $wpdb->get_results( $query );
            ?>

            <div class="row">
                <div class="card col">
                    <div class="card-header bg-info text-white">
                        <h6><?php esc_html_e( 'Enquiry Report', WL_MIM_DOMAIN ); ?></h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-striped table-bordered" id="enquiries-table-report">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Equiry ID', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Message', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Follow Up Date', WL_MIM_DOMAIN ); ?>
                                </th> <th scope="col"><?php esc_html_e( 'Note', WL_MIM_DOMAIN ); ?></th>
                              
                            </tr>
                            </thead>
                            <tbody>

                            <?php 
                            if ( count( $data ) ) {
                                foreach ( $data as $row ) {
                                    $enquiry_id       = $row->id ? $row->id: '-';
                                    $first_name       = $row->first_name ? $row->first_name : '-';
                                    $last_name        = $row->last_name ? $row->last_name : '-';
                                    $phone            = $row->phone ? $row->phone : '-';
                                    $email            = $row->email ? $row->email : '-';
                                    $is_acitve        = $row->is_active ? esc_html__( 'Yes', WL_MIM_DOMAIN ) : esc_html__( 'No', WL_MIM_DOMAIN );
                                    $added_by         = ( $user = get_userdata( $row->added_by ) ) ? $user->user_login : '-';
                                    $message          = $row->message ? $row->message : '-';
                                    $follow_up_date   = $row->follow_up_date ? $row->follow_up_date : '-';
                                    $note             = $row->note ? $row->note : '-';
                                    $date             = date_format( date_create( $row->created_at ), "d-m-Y g:i A" );
                                    $course           = WL_MIM_Helper::get_course( $row->course_id );
                                    $course           = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '';
                                   
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html( $enquiry_id ); ?></td>
                                        <td><?php echo esc_html( $first_name ); ?></td>
                                        <td><?php echo esc_html( $last_name ); ?></td>
                                        <td><?php echo esc_html( $course ); ?></td>
                                        <td><?php echo esc_html( $phone ); ?></td>
                                        <td><?php echo esc_html( $email ); ?></td>
                                        <td><?php echo esc_html( $is_acitve ); ?></td>
                                        <td><?php echo esc_html( $added_by ); ?></td>
                                        <td><?php echo esc_html( $date ); ?></td>
                                        <td><?php echo esc_html( $message ); ?></td>
                                        <td><?php echo esc_html( $follow_up_date ); ?></td>
                                        <td><?php echo esc_html( $note ); ?></td>
                                       
                                    </tr>
                                    <?php
                                }
                            }
                          
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
           </div>
        <?php
            $element = '#enquiries-table-report';

        }
		$json_data['element'] = esc_attr( $element );
		$html                 = ob_get_clean();
		$json                 = json_encode( $json_data );
		wp_send_json_success( array( 'html' => $html, 'json' => $json ) );
	}

	/* Check permission to manage report */
	private static function check_permission() {
		$institute_id = WL_MIM_Helper::get_current_institute_id();
		if ( ! current_user_can( 'wl_min_manage_report' ) || ! $institute_id ) {
			die();
		}
	}
}
?>