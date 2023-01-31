<?php 
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
global $wpdb;

if( isset( $_REQUEST['id'] ) && !empty( $_REQUEST['id'] ) ) {
    $id = sanitize_text_field( $_REQUEST['id'] ); 
    $result = WL_MIM_Helper::get_student_information_certificate( $id );
    // $page_url            = WL_MIM_Helper::get_certificates_page_url();
    // $institute_id        = WL_MIM_Helper::get_current_institute_id();
    // $certificate_student = WL_MIM_Helper::fetch_certificate_view($institute_id, $id);
    
    
    
    $institute_id              = $result[0]->institute_id;
    $general_institute         = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
    $general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
    $enrollment_id             = WL_MIM_Helper::get_enrollment_id_with_prefix( $id, $general_enrollment_prefix );
	$certificate_number        = WL_MIM_Helper::get_certificate_number( $id );
    $course_id                 = $result[0]->course_id;
    $batch_id                  = $result[0]->batch_id;
    $first_name                = $result[0]->first_name;
    $last_name                 = $result[0]->last_name;
    
	$institute_advanced_logo = wp_get_attachment_url( $general_institute['institute_logo'] );
	$institute_advanced_name = $general_institute['institute_name'];
	$show_logo               = $general_institute['institute_logo_enable'];
    $institute_advanced_address = $general_institute['institute_address'];
    $institute_advanced_phone   = $general_institute['institute_phone'];
    $institute_advanced_email   = $general_institute['institute_email'];
    if (!empty($show_logo)) {
    $institute_logo_url = esc_url(wp_get_attachment_url($institute_advanced_logo));
    }

    // $image_url               = wp_get_attachment_url($image_id);

    $course             = $wpdb->get_row( "SELECT course_name, course_code, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $course_id AND institute_id = $institute_id" );
	$batch              = $wpdb->get_row( "SELECT end_date FROM {$wpdb->prefix}wl_min_batches WHERE id = $batch_id  AND institute_id = $institute_id" );

	$name            = $first_name . " " . $last_name;
	$course          = ( ! empty ( $course ) ) ? $course->course_name : '-';
	$completion_date = ( ! empty ( $batch->end_date ) ) ? date_format( date_create( $batch->end_date ), "d M, Y" ) : '-';

    $certificate       = WL_MIM_SettingHelper::get_certificate_settings( $institute_id );
    $certificate_image = WL_MIM_Helper::fetch_certificate_student_dash( $institute_id, $id );

    $certificate_image_id = $certificate_image->image_id;
    $image_url            = wp_get_attachment_url($certificate_image_id);

    $css = <<<EOT
        #wlsm-print-certificate {
            font-size: 16px;
            color: #000;
        }
        .wlsm-certificate-fields {
            height: 31cm;
            overflow-y: auto;
        }
        .wlsm-print-certificate-container {
            box-sizing: border-box;
            position: relative;
            width: 21cm;
            height: 29.7cm;

        }
        .wlsm-certificate-image {
            width: 100%;
            height: 100%;
        }
        EOT;

    $fields = WL_MIM_Helper::get_certificate_dynamic_fields();
    foreach ( $fields as $field_key => $field_value ) {
        $css .= '.ctf-data-' . esc_attr( $field_key ) . ' { position: absolute; ';

        foreach ( $field_value['props'] as $key => $prop ) {
            $css .= $key . ': ' . $prop['value'] . $prop['unit'] . ';';
        }
        $css .= ' }';

        if ( $field_value['enable'] ) {
            $css .= '.ctf-data-' . esc_attr( $field_key ) . '{ visibility: visible; }';
        } else {
            $css .= '.ctf-data-' . esc_attr( $field_key ) . '{ visibility: hidden; }';
        }
    }
    ?>

    
	<div class="col-md-12 wlsm-flex wlsm-justify-center">
		<!-- Print certificate section. -->
		<div class="wlsm" id="wlsm-print-certificate">
			<div class="wlsm-print-certificate-container mx-auto">
				
				<img class="ctf-data-field wlsm-certificate-image" src="<?php echo esc_url($image_url); ?>">
				<?php			

				foreach ($fields as $field_key => $field_value) {
					if (isset($student)) {
						if ('name' === $field_key) {
							$field_output = WLSM_M_Staff_Class::get_name_text($student->student_name);
						} elseif ('certificate-number' === $field_key) {
							$field_output = $certificate_number;
						} elseif ('certificate-title' === $field_key) {
							$field_output = $certificate_title;
						} elseif ('photo' === $field_key) {
							if (!empty($student->photo_id)) {
								$field_output = wp_get_attachment_url($student->photo_id);
							} else {
								$field_output = '';
							}
						} elseif ('signature' === $field_key) {
							if (!empty($student->signature_id)) {
								$field_output = wp_get_attachment_url($student->signature_id);
							} else {
								$field_output = '';
							}
						} elseif ('enrollment-number' === $field_key) {
							$field_output = $student->enrollment_number;
						} elseif ('course-duration' === $field_key) {
							$field_output = "Course During " . WLSM_Config::get_date_text($session_start_date) . " To " . WLSM_Config::get_date_text($session_end_date);
							// $field_output = $student->enrollment_number;
						} elseif ('admission-number' === $field_key) {
							$field_output = WLSM_M_Staff_Class::get_admission_no_text($student->admission_number);
						} elseif ('roll-number' === $field_key) {
							$field_output = WLSM_M_Staff_Class::get_roll_no_text($student->roll_number);
						} elseif ('session-label' === $field_key) {
							$field_output = WLSM_M_Session::get_label_text($session_label);
						} elseif ('session-start-date' === $field_key) {
							$field_output = WLSM_Config::get_date_text($session_start_date);
						} elseif ('session-end-date' === $field_key) {
							$field_output = WLSM_Config::get_date_text($session_end_date);
						} elseif ('session-start-year' === $field_key) {
							$field_output = DateTime::createFromFormat('Y-m-d', $session_start_date);
							$field_output = $field_output->format('Y');
						} elseif ('session-end-year' === $field_key) {
							$field_output = DateTime::createFromFormat('Y-m-d', $session_end_date);
							$field_output = $field_output->format('Y');
						} elseif ('class' === $field_key) {
							$field_output = WLSM_M_Class::get_label_text($student->class_label);
						} elseif ('section' === $field_key) {
							$field_output = WLSM_M_Class::get_label_text($student->section_label);
						} elseif ('dob' === $field_key) {
							$field_output = WLSM_Config::get_date_text($student->dob);
						} elseif ('caste' === $field_key) {
							$field_output = stripcslashes($student->caste);
						} elseif ('blood-group' === $field_key) {
							$field_output = stripcslashes($student->blood_group);
						} elseif ('father-name' === $field_key) {
							$field_output = stripcslashes($student->father_name);
						} elseif ('mother-name' === $field_key) {
							$field_output = stripcslashes($student->mother_name);
						} elseif ('class-teacher' === $field_key) {
							$section_id = $student->section_id;

							$teacher = WLSM_M_Staff_Class::get_section_teachers($institute_id, $section_id, true);

							if ($teacher) {
								$teacher_name = $teacher->name;
							} else {
								$teacher_name = '';
							}

							$field_output = stripcslashes($teacher_name);
						} elseif ('school-name' === $field_key) {
							$field_output = $school_name;
						} elseif ('school-phone' === $field_key) {
							$field_output = $school_phone;
						} elseif ('school-email' === $field_key) {
							$field_output = $school_email;
						} elseif ('school-address' === $field_key) {
							$field_output = $school_address;
						} elseif ('school-logo' === $field_key) {
							$field_output = $school_logo_url;
						} else {
							$field_output = '';
						}
					} else {
						$field_output = WL_MIM_Helper::get_certificate_place_holder($field_key, $institute_id);
					}

					if ('text' === WL_MIM_Helper::get_certificate_place_holder_type($field_key)) {
				?>
						<span class="ctf-data-field ctf-data-<?php echo esc_attr($field_key); ?>"><?php echo esc_html($field_output); ?></span>
					<?php
					} elseif ('image' === WL_MIM_Helper::get_certificate_place_holder_type($field_key) && $field_output) {
					?>
						<img class="ctf-data-field ctf-data-<?php echo esc_attr($field_key); ?>" src="<?php echo esc_url($field_output); ?>">
					<?php
					}
					?>
				<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
<?php
}