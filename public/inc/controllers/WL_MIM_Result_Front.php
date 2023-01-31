<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

class WL_MIM_Result_Front {
	/* Get admit card */
	public static function get_admit_card() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$institute_id  = isset( $_POST['institute'] ) ? intval( sanitize_text_field( $_POST['institute'] ) ) : null;
		$exam_id       = isset( $_REQUEST['exam'] ) ? intval( sanitize_text_field( $_REQUEST['exam'] ) ) : null;
		$enrollment_id = isset( $_REQUEST['enrollment_id'] ) ? sanitize_text_field( $_REQUEST['enrollment_id'] ) : null;

    	$data_of_birth_required = WL_MIM_SettingHelper::get_admit_card_dob_enable_settings( $institute_id );

		if ( $data_of_birth_required ) {
			$date_of_birth = ( isset( $_POST['date_of_birth'] ) && ! empty( $_POST['date_of_birth'] ) ) ? date( "Y-m-d", strtotime( sanitize_text_field( $_REQUEST['date_of_birth'] ) ) ) : null;
		}

		/* Validations */
		$errors = array();
		if ( empty( $institute_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select institute.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		if ( $data_of_birth_required ) {
			if ( empty( $date_of_birth ) ) { ?>
				<strong class="text-danger"><?php esc_html_e( 'Please provide date of birth.', WL_MIM_DOMAIN ); ?></strong>
				<?php
				die();
			}

			if ( ! empty( $date_of_birth ) && ( strtotime( date( 'Y' ) - 2 ) <= strtotime( $date_of_birth ) ) ) { ?>
				<strong class="text-danger"><?php esc_html_e( 'Please provide valid date of birth.', WL_MIM_DOMAIN ); ?></strong>
				<?php
				die();
			}
		}

		if ( empty( $exam_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select an exam.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		if ( empty( $enrollment_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please provide an enrollmend ID.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND is_published = 1 AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Invalid exam selection.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		$notes = unserialize( $exam->notes );
		/* End validations */

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
		$student_id                = WL_MIM_Helper::get_student_id_with_prefix( $enrollment_id, $general_enrollment_prefix );

		if ( $data_of_birth_required ) {
			$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND enrollment_id = $student_id AND institute_id = $institute_id AND date_of_birth = '$date_of_birth'" );
		} else {
			$student = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND enrollment_id = $student_id AND institute_id = $institute_id" );
		}

		if ( ! $student ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Student not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		$id            = $student->id;
		if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
			$student_id = $student->enrollment_id;
		} else {
			$student_id = $student->id;
		}
		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
		$name          = $student->first_name;
		if ( $student->last_name ) {
			$name .= " $student->last_name";
		}

		$father_name = $student->father_name;
		$photo       = $student->photo_id;

		$course = $wpdb->get_row( "SELECT course_code, course_name FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND id = $student->course_id AND institute_id = $institute_id" );
		$course = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '';
		?>
        <div class="wlim-exam-result card col-12 mb-3">
            <div class="card-header bg-primary text-white">
                <strong><?php esc_html_e( 'Admit Card', WL_MIM_DOMAIN ); ?></strong>
                <button type="button" id="wlmim-admit-card-print-button" class="btn btn-sm btn-outline-light float-right">
        			<i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print', WL_MIM_DOMAIN ); ?>
                </button>
            </div>
        </div>
		<?php
		require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_admit_card.php' );
		die();
	}

	/* Get result */
	public static function get_result() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$institute_id  = isset( $_POST['institute'] ) ? intval( sanitize_text_field( $_POST['institute'] ) ) : null;
		$exam_id       = isset( $_REQUEST['exam'] ) ? intval( sanitize_text_field( $_REQUEST['exam'] ) ) : null;
		$enrollment_id = isset( $_REQUEST['enrollment_id'] ) ? sanitize_text_field( $_REQUEST['enrollment_id'] ) : null;

		/* Validations */
		$errors = array();
		if ( empty( $institute_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select institute.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		if ( empty( $exam_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select an exam.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		if ( empty( $enrollment_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please provide an enrollmend ID.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND is_published = 1 AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Invalid exam selection.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}
		/* End validations */

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
		$student_id                = WL_MIM_Helper::get_student_id_with_prefix( $enrollment_id, $general_enrollment_prefix );

		$student   = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND enrollment_id = $student_id AND institute_id = $institute_id" );
		$studentId = $student->id;
		if ( ! $student ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Student not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND exam_id = $exam_id AND student_id = $studentId AND institute_id = $institute_id" );
		if ( ! $result ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Result not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		$id            = $student->id;
		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student->id, $general_enrollment_prefix );
		$name          = $student->first_name;
		if ( $student->last_name ) {
			$name .= " $student->last_name";
		}

		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND institute_id = $institute_id AND exam_id = " . $exam->id . " AND student_id = " . $id );

		$marks = unserialize( $exam->marks ); ?>
        <div class="wlim-exam-result card mb-3">
            <div class="card-header bg-primary text-white">
				<strong><?php esc_html_e( 'Exam Result', WL_MIM_DOMAIN ); ?></strong>
            </div>
			
            <div id="wlmim-exam-result-print">
			<?php require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/partials/wl_im_exam_result.php' ); ?>
            </div>
			<div class="text-center pt-3">
						<button type="button" id="wl-invoice-fee-invoice-print" class="btn btn-sm btn-success"><i class="fa fa-print text-white"></i>&nbsp;<?php esc_html_e( 'Print Exam Result', WL_MIM_DOMAIN ); ?></button><hr>
			</div>
        </div>
		<?php
		die();
	}

	/* Get results by name */
	public static function get_results_by_name() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;

		$institute_id  = isset( $_POST['institute'] ) ? intval( sanitize_text_field( $_POST['institute'] ) ) : null;
		$exam_id       = isset( $_REQUEST['exam'] ) ? intval( sanitize_text_field( $_REQUEST['exam'] ) ) : null;
		$name          = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : null;

		/* Validations */
		$errors = array();
		if ( empty( $institute_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select institute.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		if ( empty( $exam_id ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Please select an exam.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		if ( empty( $name ) ) { ?>
		    <strong class="text-danger"><?php esc_html_e( 'Please provide your first name or last name.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		$exam = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND id = $exam_id AND is_published = 1 AND institute_id = $institute_id" );
		if ( ! $exam ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Invalid exam selection.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}
		/* End validations */

		$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

		$names = explode( ' ', $name );
		$first_name = $names[0];

		$students = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0 AND institute_id = $institute_id AND ( ( first_name LIKE '$name%' ) OR ( first_name LIKE '$first_name%' ) OR ( last_name LIKE '$name%' ) )" );
		if ( ! count( $students ) ) { ?>
            <strong class="text-danger"><?php esc_html_e( 'Student not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		?>
        <ul class="list-group mt-4 mb-2">
			<?php
			foreach ( $students as $key => $student ) {
				$id            = $student->id;
				if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
					$student_id = $student->enrollment_id;
				} else {
					$student_id = $student->id;
				}
				$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
				$name          = $student->first_name;
				if ( $student->last_name ) {
					$name .= " $student->last_name";
				}

				$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND institute_id = $institute_id AND exam_id = " . $exam->id . " AND student_id = " . $id );

				$marks = unserialize( $exam->marks );

				$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
				$institute_logo    = wp_get_attachment_url( $general_institute['institute_logo'] );
				$show_logo         = $general_institute['institute_logo_enable'];
				$institute_name    = $general_institute['institute_name'];
				$institute_address = $general_institute['institute_address'];
				$institute_phone   = $general_institute['institute_phone'];
				$institute_email   = $general_institute['institute_email'];
				?>
                <li class="border p-2 list-group-item">
                    <strong><?php echo esc_attr( $key + 1 ); ?>. </strong><span class="text-dark"><?php echo "$name ($enrollment_id)"; ?></span>
                    <span class="ml-4">
						<a class="btn btn-success btn-sm" role="button" href="#wlim-result-student-marks-<?php echo esc_attr( $key ); ?>" data-keyboard="false" data-backdrop="static" data-toggle="modal">
							<?php
							if ( $result ) {
								esc_html_e( "View Result", WL_MIM_DOMAIN );
							}
							?>
							<script>
							jQuery(document).on('click', '#wlmim-admit-card-print-button-<?php echo esc_attr( $key ); ?>', function() {
								jQuery.print('#wlmim-exam-result-print-<?php echo esc_attr( $key ); ?>');
							});
							</script>
						</a>
					</span>
                    <!-- student marks modal -->
                    <div class="modal fade wlim-result-student-marks" id="wlim-result-student-marks-<?php echo esc_attr( $key ); ?>" tabindex="-1" role="dialog" aria-labelledby="wlim-result-student-marks-label-<?php echo esc_attr( $key ); ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered wlim-result-student-marks-dialog" id="wlim-result-student-marks-dialog-<?php echo esc_attr( $key ); ?>" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title w-100 text-center" id="wlim-result-student-marks-label-<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Student Marks', WL_MIM_DOMAIN ); ?>
						                <button type="button" id="wlmim-admit-card-print-button-<?php echo esc_attr( $key ); ?>" class="btn btn-sm btn-outline-primary float-right">
						        			<i class="fa fa-print"></i>&nbsp;<?php esc_html_e( 'Print', WL_MIM_DOMAIN ); ?>
						                </button>
                                    </h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body pr-4 pl-4">
						            <div id="wlmim-exam-result-print-<?php echo esc_attr( $key ); ?>" class="wl_im">
						                <div class="card-body">
						                    <div class="row">
						                        <div class="col">
						                            <div class="row">
						                                <div class="col-sm-12 mb-3">
						                                    <div class="row">
																<?php
																if ( $show_logo ) { ?>
						                                            <div class="col-3 text-right">
						                                                <img src="<?php echo esc_url( $institute_logo ); ?>" class="wlmim-result-institute-logo" class="img-responsive">
						                                            </div>
																<?php } ?>
						                                        <div class="<?php echo boolval( $show_logo ) ? "col-9 text-left" : "col-12 text-center"; ?>">
																	<?php if ( $show_logo ) { ?>
																	<span class="float-left">
																	<?php
																	} else { ?>
																	<span>
																	<?php
																	} ?>
																		<h4 class="wlmim-result-institute-name" class="mt-1"><?php echo esc_html( $institute_name ); ?></h4>
																		<?php
																		if ( ! empty( $institute_address ) ) { ?>
																			<span class="wlmim-result-institute-address"><?php echo esc_html( $institute_address ); ?></span>
																			<br>
																			<?php
																		}
																		if ( ! empty( $institute_phone ) ) { ?>
																			<span class="wlmim-result-institute-contact-phone"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_html( $institute_phone ); ?></strong>
																			<?php
																				if ( ! empty( $institute_email ) ) { ?> | <?php } ?>
																			</span>
																			<?php
																		}
																		if ( ! empty( $institute_email ) ) { ?>
																			<span class="wlmim-result-institute-contact-email"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_html( $institute_email ); ?></strong></span>
																			<?php
																		} ?>
																	</span>
						                                        </div>
						                                    </div>
						                                </div>
						                                <div class="col-sm-12">
						                                    <div class="border p-2 mb-2">
						                                        <span class="text-dark"><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?>:&nbsp;</span><strong><?php echo esc_html( "$exam->exam_title ( $exam->exam_code )" ); ?></strong>
						                                    </div>
						                                    <label class="col-form-label"><strong><?php esc_html_e( 'Student Details', WL_MIM_DOMAIN ); ?>:</strong></label>
						                                    <ul class="list-group m-0">
						                                        <li class="list-group-item ml-0">
																	<span class="text-dark"><?php esc_html_e( "Enrollment ID", WL_MIM_DOMAIN ); ?>:&nbsp;</span><strong><?php echo esc_html( $enrollment_id ); ?></strong>
						                                        </li>
						                                        <li class="list-group-item ml-0">
																	<span class="text-dark"><?php esc_html_e( "Name", WL_MIM_DOMAIN ); ?>:&nbsp;</span><strong><?php echo esc_html( $name ); ?></strong>
						                                        </li>
						                                    </ul>
						                                </div>
						                            </div>
						                        </div>
						                    </div>
						                    <div class="row">
						                        <div class="col">
						                            <div class="mb-3 mt-2">
						                                <label class="col-form-label"><strong><?php esc_html_e( 'Exam Marks', WL_MIM_DOMAIN ); ?>:</strong></label>
						                                <div class="exam_marks_obtained_box">
						                                    <table class="table table-bordered">
						                                        <thead>
						                                        <tr>
						                                            <th><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
						                                            <th><?php esc_html_e( 'Maximum Marks', WL_MIM_DOMAIN ); ?></th>
						                                            <th><?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?></th>
						                                        </tr>
						                                        </thead>
						                                        <tbody class="exam_marks_obtained_rows exam_marks_obtained_table">
																<?php
																$marks_obtained = null;
																if ( $result ) {
																	$marks_obtained = unserialize( $result->marks );
																}
																$total_maximum_marks  = 0;
																$total_marks_obtained = 0;
																foreach ( $marks['subject'] as $subject_key => $subject_value ) {
																	$marks_obtained_in_subject = 0;
																	if ( ! empty( $marks_obtained ) ) {
																		$marks_obtained_in_subject = $marks_obtained[ $subject_key ];
																	}
																	$total_maximum_marks  += $marks['maximum'][ $subject_key ];
																	$total_marks_obtained += $marks_obtained_in_subject;
																	?>
						                                            <tr>
						                                                <td>
						                                                    <span class="text-dark"><?php echo esc_html( $subject_value ); ?></span>
						                                                </td>
						                                                <td>
						                                                    <span class="text-dark"><?php echo esc_html( $marks['maximum'][ $subject_key ] ); ?></span>
						                                                </td>
						                                                <td>
						                                                    <span class="text-dark"><?php echo esc_html( $marks_obtained_in_subject ); ?></span>
						                                                </td>
						                                            </tr>
																	<?php
																} ?>
						                                        <tr>
						                                            <th><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></th>
						                                            <th><?php echo esc_html( $total_maximum_marks ); ?></th>
						                                            <th><?php echo esc_html( $total_marks_obtained ); ?></th>
						                                        </tr>
						                                        <tr>
						                                            <th></th>
						                                            <th><?php esc_html_e( 'Percentage', WL_MIM_DOMAIN ); ?></th>
						                                            <th><?php echo number_format( max( floatval( ( $total_marks_obtained / $total_maximum_marks ) * 100 ), 0 ), 2, '.', '' ); ?> % </th>
						                                        </tr>
						                                        </tbody>
						                                    </table>
						                                </div>
						                            </div>
						                        </div>
						                    </div>
						                </div>
						            </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Close', WL_MIM_DOMAIN ); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end - student marks modal -->
                </li>
				<?php
			} ?>
        </ul>
		<?php
		die();

		// var_dump($students); die();

		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND exam_id = $exam_id AND student_id = $student_id AND institute_id = $institute_id" );
		if ( ! $result ) { ?>
			<strong class="text-danger"><?php esc_html_e( 'Result not found.', WL_MIM_DOMAIN ); ?></strong>
			<?php
			die();
		}

		$id            = $student->id;
		if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
			$student_id = $student->enrollment_id;
		} else {
			$student_id = $student->id;
		}
		$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
		$name          = $student->first_name;
		if ( $student->last_name ) {
			$name .= " $student->last_name";
		}

		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_results WHERE is_deleted = 0 AND institute_id = $institute_id AND exam_id = " . $exam->id . " AND student_id = " . $id );

		$marks = unserialize( $exam->marks ); ?>
        <div class="wlim-exam-result card mb-3">
            <div class="card-header bg-primary text-white">
				<strong><?php esc_html_e( 'Exam Result', WL_MIM_DOMAIN ); ?></strong>
            </div>

        </div>
		<?php
		die();
	}

	/* Fetch institute's exams */
	public static function fetch_institute_exams() {
		if ( ! wp_verify_nonce( $_REQUEST['security'], 'wl-ima' ) ) {
			die();
		}
		global $wpdb;
		$institute_id = intval( sanitize_text_field( $_POST['id'] ) );

		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id AND is_active = '1'" );
		if ( ! $row ) {
			die();
		}

		$wlim_institute_exams = $wpdb->get_results( "SELECT id, exam_title, exam_code FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND is_published = 1 AND institute_id = $institute_id ORDER BY id DESC" );

		$dob = isset( $_POST['dob'] ) ? (bool) $_POST['dob'] : false;

		if ( $dob ) {
			$data_of_birth_required = WL_MIM_SettingHelper::get_admit_card_dob_enable_settings( $institute_id );
			if ( $data_of_birth_required ) {
		?>
        <div class="form-group">
            <label for="wlim-admit-card-date_of_birth" class="col-form-label">
                *<strong><?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?>:</strong>
            </label>
            <input name="date_of_birth" type="text" class="form-control wlim-date_of_birth" id="wlim-admit-card-date_of_birth" placeholder="<?php esc_html_e( "Date of Birth", WL_MIM_DOMAIN ); ?>">
        </div>
        <?php }
    	}
        ?>

        <div class="form-group wlim-selectpicker">
            <label for="wlim-result-exam" class="col-form-label">* <strong><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?></strong></label>
            <select name="exam" class="form-control" id="wlim-result-exam">
                <option value="">-------- <?php esc_html_e( "Select an Exam", WL_MIM_DOMAIN ); ?> --------</option>
               <?php
				if ( count( $wlim_institute_exams ) > 0 ) {
					foreach ( $wlim_institute_exams as $exam ) { ?>
                        <option value="<?php echo esc_attr( $exam->id ); ?>"><?php echo esc_html( "$exam->exam_title ( $exam->exam_code )" ); ?></option>
						<?php
					}
				} ?>
            </select>
        </div>
		<?php
		die();
	}
}
?>
