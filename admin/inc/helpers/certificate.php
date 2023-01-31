<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
if ( isset( $from_front ) ) {
	$print_button_classes = 'button btn-sm btn-success';
} else {
	$print_button_classes = 'btn btn-sm btn-success';
}

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

if ( isset( $from_ajax ) ) {
?>
<style>
	<?php echo esc_attr( $css ); ?>
</style>
<?php
} else {
	wp_register_style( 'wlsm-certificate', false );
	wp_enqueue_style( 'wlsm-certificate' );
	wp_add_inline_style( 'wlsm-certificate', $css );
}
?>

<!-- Print certificate. -->
<div class="wlsm-container d-flex mb-2">
	<div class="col-md-12 text-center">
		<br>
		<button type="button" data-css="<?php echo esc_attr( $css ); ?>" class="<?php echo esc_attr( $print_button_classes ); ?>" id="wlsm-print-certificate-btn" data-title="<?php esc_attr_e( 'Print Certificate', WL_MIM_DOMAIN ); ?>" data-styles='["<?php echo esc_url( WL_MIM_PLUGIN_URL . 'assets/css/bootstrap.min.css' ); ?>"]'><?php esc_html_e( 'Print Certificate', WL_MIM_DOMAIN ); ?></button>
	</div>
</div>

<div class="wlsm-container row">
	<div class="col-md-12 wlsm-flex wlsm-justify-center">
		<!-- Print certificate section. -->
		<div class="wlsm" id="wlsm-print-certificate">
			<div class="wlsm-print-certificate-container mx-auto">
				<?php
				if ( ! $image_url ) {
					$image_url = WL_MIM_PLUGIN_URL . 'assets/images/certificate.png';
				}
				?>
				<img class="ctf-data-field wlsm-certificate-image" src="<?php echo esc_url( $image_url ); ?>">
				<?php
				$institute_name      = '';
				$institute_phone     = '';
				$institute_email     = '';
				$institute_address   = '';
				$institute_logo_url  = '';
				$institute_logo_url  = '';
				$certificate_qr_code = '';

				$data_for_qr_code = home_url() . '/certificate-verfiy?id=' . $student->student_record_id;
				$qr_code_url 	  = esc_url('https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . $data_for_qr_code . '&choe=UTF-8');

				if ( $institute_id ) { 
					$general_institute 		   = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
					$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
					$institute_logo    = wp_get_attachment_url( $general_institute['institute_logo'] );
					$show_logo         = $general_institute['institute_logo_enable'];
					$institute_name    = $general_institute['institute_name'];
					$institute_address = $general_institute['institute_address'];
					$institute_phone   = $general_institute['institute_phone'];
					$institute_email   = $general_institute['institute_email'];
					if ( ! empty ( $institute_logo ) ) {
						$institute_logo_url = esc_url( wp_get_attachment_url( $institute_logo ) );
					}
					$certificate_qr_code = $qr_code_url;
				}
				
				foreach ( $fields as $field_key => $field_value ) {
					if ( isset( $student ) ) {
						if ( 'name' === $field_key ) {
							$first_name = $student->first_name;
							$last_name = $student->last_name;
							$field_output =  $first_name.' '.$last_name;

						} elseif ( 'certificate-number' === $field_key ) {
							$field_output = $certificate_student->id;

						} elseif ( 'certificate-title' === $field_key ) {
							$field_output = $certificate_student->label;

						} elseif ( 'photo' === $field_key ) {
							if ( ! empty ( $student->photo_id ) ) {
								$field_output = wp_get_attachment_url( $student->photo_id );
							} else {
								$field_output = '';
							}
						} elseif ( 'institute-logo' === $field_key ) {
							if ( ! empty ( $institute_logo ) ) {
								$field_output = $institute_logo;
							} else {
								$field_output = '';
							}
						} elseif ( 'enrollment-number' === $field_key ) {
							if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
								$student_id = $student->enrollment_id;
							} else {
								$student_id = $student->id;
							}
							$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
							$field_output = $enrollment_id;

						} elseif ( 'roll_number' === $field_key ) {
							$field_output = stripcslashes( $student->roll_number );

						} elseif ( 'dob' === $field_key ) {
							$field_output = ( $student->date_of_birth );

						}  elseif ( 'batch' === $field_key ) {
							$field_output = stripcslashes( $student->batch_name );

						}  elseif ( 'father-name' === $field_key ) {
							$field_output = stripcslashes( $student->father_name );

						} elseif ( 'mother-name' === $field_key ) {
							$field_output = stripcslashes( $student->mother_name );

						} elseif ( 'course' === $field_key ) {
							$field_output = stripcslashes( $student->course_name );

						} elseif ( 'course-duration' === $field_key ) {
							$field_output = stripcslashes( $student->duration ." Month" );

						} elseif ( 'institute-name' === $field_key ) {
							$field_output = $institute_name;

						} elseif ( 'institute-phone' === $field_key ) {
							$field_output = $institute_phone;

						} elseif ( 'institute-email' === $field_key ) {
							$field_output = $institute_email;

						} elseif ( 'institute-address' === $field_key ) {
							$field_output = $institute_address;

						}elseif ( 'issued-date' === $field_key ) {
							$field_output = $student->date_issued;

						} elseif ( 'institute-logo' === $field_key ) {
							$field_output = $institute_logo_url;

						} elseif ( 'certificate-qr-code' === $field_key ) {
							$field_output = $certificate_qr_code;
						}
						else {
							$field_output = '';
						}
					} else {
						$field_output = WL_MIM_Helper::get_certificate_place_holder( $field_key, $institute_id );
					}

					if ( 'text' === WL_MIM_Helper::get_certificate_place_holder_type( $field_key ) ) {
					?>
					<span class="ctf-data-field ctf-data-<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field_output ); ?></span>
					<?php
					} elseif ( 'image' === WL_MIM_Helper::get_certificate_place_holder_type( $field_key ) && $field_output ) {
					?>
					<img class="ctf-data-field ctf-data-<?php echo esc_attr( $field_key ); ?>" src="<?php echo esc_url( $field_output ); ?>">
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
