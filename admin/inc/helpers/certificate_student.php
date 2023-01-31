<?php
defined( 'ABSPATH' ) || die();


// var_dump($certificate_student);
// die;
$certificate_id = $certificate_student->certificate_id;
$student_id     = $certificate_student->student_record_id;

$certificate_number = $certificate_student->certificate_id;
// $certificate_title  = WLSM_M_Staff_Class::get_certificate_label_text( $certificate_student->label );

$fields       = WL_MIM_Helper::get_certificate_dynamic_fields();

// $certificate = WL_MIM_Helper::fetch_certificate($institute_id, $id );

$image_id  = $certificate_student->image_id;
$image_url = wp_get_attachment_url( $image_id );

if ( $certificate_student->fields ) {
	$saved_fields = unserialize( $certificate_student->fields );

	if ( is_array( $saved_fields ) && count( $saved_fields ) ) {
		foreach ( $fields as $field_key => $field_value ) {
			if ( array_key_exists( $field_key, $saved_fields ) ) {
				$fields[ $field_key ] = $saved_fields[ $field_key ];
			}
		}
	}
}
