<?php defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
?>
<div id="wl-admission-detail">
    <?php
    $institute_id = WL_MIM_Helper::get_current_institute_id();

    $registration_number = WL_MIM_Helper::get_institute_registration_number( $institute_id );

    $general_institute         = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
    $general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

    $general_enable_signature_in_admission_detail = WL_MIM_SettingHelper::get_general_enable_signature_in_admission_detail( $institute_id );

    $enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $row->id, $general_enrollment_prefix );
    $form_number   = WL_MIM_Helper::get_form_number( $row->id );
    $course        = $wpdb->get_row( "SELECT course_name, course_code, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $row->course_id AND institute_id = $institute_id" );
    $duration      = $course->duration;
    $duration_in   = $course->duration_in;
    $duration_in   = ( $duration < 2 ) ? esc_html__( substr( $duration_in, 0, - 1 ), WL_MIM_DOMAIN ) : esc_html__( $duration_in, 0, - 1, WL_MIM_DOMAIN );

    $name           = $row->first_name . " $row->last_name";
    $father_name    = $row->father_name;
    $mother_name    = $row->mother_name;
    $course         = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '-';
    $duration       = "{$duration} {$duration_in}";
    $phone          = ( ! empty ( $row->phone ) ) ? $row->phone : '-';
    $email          = ( ! empty ( $row->email ) ) ? $row->email : '-';
    $gender         = ( $row->gender == 'male' ) ? esc_html__( 'Male', WL_MIM_DOMAIN ) : esc_html__( 'Female', WL_MIM_DOMAIN );
    $date_of_birth  = ( ! empty ( $row->date_of_birth ) ) ? date_format( date_create( $row->date_of_birth ), "d M, Y" ) : '-';
    $nationality    = ( ! empty ( $row->nationality ) ) ? $row->nationality : '-';
    $address        = ( ! empty ( $row->address ) ) ? $row->address : '-';
    $city           = ( ! empty ( $row->city ) ) ? $row->city : '-';
    $state          = ( ! empty ( $row->state ) ) ? $row->state : '-';
    $zip            = ( ! empty ( $row->zip ) ) ? $row->zip : '-';
    $admission_date = ( ! empty ( $row->created_at ) ) ? date_format( date_create( $row->created_at ), "d M, Y" ) : '-';

    $institute_advanced_logo    = wp_get_attachment_url( $general_institute['institute_logo'] );
    $institute_advanced_name    = $general_institute['institute_name'];
    $institute_advanced_address = $general_institute['institute_address'];
    $institute_advanced_phone   = $general_institute['institute_phone'];
    $institute_advanced_email   = $general_institute['institute_email'];
    $photo                      = $row->photo_id;
    $signature                  = $row->signature_id;
    $show_logo                  = $general_institute['institute_logo_enable'];
    ?>
    <div id="wl-admission-detail-box">
        <div class="row">
            <div class="col mx-auto">
                <?php if ( $registration_number ) { ?>
                <span class="float-left" id="wl-admission-detail-registration-number"><?php esc_html_e( 'Registration No.', WL_MIM_DOMAIN ); ?>
                    <strong><?php echo esc_html( $registration_number ); ?></strong>
                </span>
                <?php } ?>
                <span class="float-right" id="wl-admission-detail-form-number"><?php esc_html_e( 'Form No.', WL_MIM_DOMAIN ); ?>
                    <strong><?php echo esc_html( $form_number ); ?></strong>
                </span>
            </div>
        </div>
        <div class="row">
            <?php
            if ( $show_logo ) { ?>
                <div class="col-4 mx-auto">
                    <img src="<?php echo esc_url( $institute_advanced_logo ); ?>" id="wl-institute-pro-admission-detail-logo" class="img-responsive float-right">
                </div>
            <?php
            } ?>
            <div class="<?php echo boolval( $show_logo ) ? "col-8 " : "col-12 text-center "; ?>mx-auto">
                <?php
                if ( $show_logo ) { ?>
                <span class="float-left">
                <?php
                } else { ?>
                <span>
                <?php
                } ?>
                <h3 class="mt-1" id="wl-admission-detail-name"><?php echo esc_html( $institute_advanced_name ); ?></h3>
                <?php
                if ( ! empty( $institute_advanced_address ) ) { ?>
                    <span id="wl-admission-detail-address"><?php echo esc_html( $institute_advanced_address ); ?></span>
                    <br>
                <?php
                }
                if ( ! empty( $institute_advanced_phone ) ) { ?>
                    <span id="wl-admission-detail-contact-phone"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - 
                        <strong><?php echo esc_html( $institute_advanced_phone ); ?></strong>
                        <?php
                        if ( ! empty( $institute_advanced_email ) ) { ?> | <?php } ?>
                    </span>
                <?php
                }
                if ( ! empty( $institute_advanced_email ) ) { ?>
                    <span id="wl-admission-detail-contact-email"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - 
                        <strong><?php echo esc_html( $institute_advanced_email ); ?></strong>
                    </span>
                <?php
                } ?>
                </span>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col mx-auto">
                <div class="wl-admission-detail-subtitle"><?php esc_html_e( 'Basic Details', WL_MIM_DOMAIN ); ?></div>
            </div>
        </div>
        <div class="row">
            <div class="col-9 mx-auto">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $enrollment_id ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Student Name', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $name ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( "Father's Name", WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $father_name ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( "Mother's Name", WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $mother_name ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $course ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $duration ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Registration Date', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $admission_date ); ?></span>
                    </li>
                </ul>
            </div>
            <div class="col-3 mx-auto">
                <div class="ml-5">
                    <div id="wl-admission-detail-photo-box" class="mt-2">
                        <?php
                        if ( ! empty ( $photo ) ) { ?>
                            <img src="<?php echo wp_get_attachment_url( $photo ); ?>" id="wl-admission-detail-photo" class="img-responsive">
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col mx-auto">
                <div class="wl-admission-detail-subtitle"><?php esc_html_e( 'Contact Details', WL_MIM_DOMAIN ); ?></div>
            </div>
        </div>
        <div class="row">
            <div class="col-9 mx-auto">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Address', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $address ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'City / State', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( "$city, $state" ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Zip', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $zip ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $phone ); ?></span>
                    </li>
                    <li class="list-group-item">
                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?>: </span>
                        <span class="list-group-value float-right"><?php echo esc_html( $email ); ?></span>
                    </li>
                </ul>
            </div>
            <div class="col-3 mx-auto"></div>
        </div>
        <hr>
        <div class="row">
            <div class="col mx-auto">
                <div class="wl-admission-detail-subtitle"><?php esc_html_e( 'Personal Details', WL_MIM_DOMAIN ); ?></div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 mx-auto">
                <table class="table">
                    <thead>
                        <tr class="d-flex">
                            <th class="col" scope="col"><?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?></th>
                            <th class="col" scope="col"><?php esc_html_e( 'Gender', WL_MIM_DOMAIN ); ?></th>
                            <th class="col" scope="col"><?php esc_html_e( 'Nationality', WL_MIM_DOMAIN ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="d-flex">
                            <td class="col"><?php echo esc_html( $date_of_birth ); ?></td>
                            <td class="col"><?php echo esc_html( $gender ); ?></td>
                            <td class="col"><?php echo esc_html( $nationality ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
        <div class="row wl-admission-detail-signature-box-row">
            <div class="col-6 text-left">
                <div class="wl-admission-detail-signature-box mt-1">
                <?php if ( $general_enable_signature_in_admission_detail ) { ?>
                    <?php if ( $url = wp_get_attachment_url( $signature ) ) { ?>
                    <img src="<?php echo esc_url( $url ); ?>">
                    <?php } ?>
                <?php } ?>
                </div>
                <div class="font-weight-bold"><?php esc_html_e( 'Candidate Signature', WL_MIM_DOMAIN ); ?></div>
            </div>
            <div class="col-6 text-right">
                <div class="wl-admission-detail-signature-box mt-1 float-right"></div>
                <div class="font-weight-bold"><?php esc_html_e( 'Authorized By', WL_MIM_DOMAIN ); ?></div>
            </div>
        </div>
    </div>
</div>
