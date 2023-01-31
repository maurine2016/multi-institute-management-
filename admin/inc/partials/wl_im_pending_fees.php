<?php defined( 'ABSPATH' ) || die(); ?>
<div id="wl-pending-fees" class="row">
	<?php
	$institute_id = WL_MIM_Helper::get_current_institute_id();

    $registration_number = WL_MIM_Helper::get_institute_registration_number( $institute_id );

	$general_institute         = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
	$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
	$general_receipt_prefix    = WL_MIM_SettingHelper::get_general_receipt_prefix_settings( $institute_id );

    if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
        $student_id = $student->enrollment_id;
    } else {
        $student_id = $student->id;
    }
	$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
	$course        = $wpdb->get_row( "SELECT course_name, course_code, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $row->course_id AND institute_id = $institute_id" );
	$duration      = $course->duration;
	$duration_in   = $course->duration_in;
	$duration_in   = ( $duration < 2 ) ? esc_html__( substr( $duration_in, 0, - 1 ), WL_MIM_DOMAIN ) : esc_html__( $duration_in, 0, - 1, WL_MIM_DOMAIN );

	$name           = $row->first_name . " $row->last_name";
	$course         = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '-';
	$duration       = "{$duration} {$duration_in}";
	$phone          = ( ! empty ( $row->phone ) ) ? $row->phone : '-';
	$admission_date = ( ! empty ( $row->created_at ) ) ? date_format( date_create( $row->created_at ), "d M, Y" ) : '-';
	$fees_paid      = WL_MIM_Helper::get_fees_total( $fees['paid'] );

	$institute_advanced_logo    = wp_get_attachment_url( $general_institute['institute_logo'] );
	$institute_advanced_name    = $general_institute['institute_name'];
	$institute_advanced_address = $general_institute['institute_address'];
	$institute_advanced_phone   = $general_institute['institute_phone'];
	$institute_advanced_email   = $general_institute['institute_email'];
	$photo                      = $row->photo_id;
	$signature                  = $row->signature_id;
	$show_logo                  = $general_institute['institute_logo_enable'];

	if ( isset( $installments_recent ) ) {
		$installments = $installments_recent;
	}
	?>
    <div class="wl-pending-fees-section">
        <div class="wl-pending-fees-box">
            <div class="row">
				<?php
                if ( $show_logo ) { ?>
                    <div class="col-3 mx-auto">
                        <img src="<?php echo esc_url( $institute_advanced_logo ); ?>" class="wl-institute-pro-pending-fees-logo img-responsive float-right">
                    </div>
				<?php
				} ?>
                <div class="<?php echo boolval( $show_logo ) ? "col-9 " : "col-12 text-center "; ?>mx-auto">
                    <?php if ( $registration_number ) { ?>
                    <span class="float-right mr-2 wl-pending-fees-address"><?php esc_html_e( 'Registration No.', WL_MIM_DOMAIN ); ?>
                        <strong><?php echo esc_html( $registration_number ); ?></strong>
                    </span>
                    <?php } ?>
					<?php
                    if ( $show_logo ) { ?>
                        <span class="float-left">
					<?php
					} else { ?>
                        <span>
					<?php
					} ?>
                            <h3 class="wl-pending-fees-name mt-1"><?php echo esc_html( $institute_advanced_name ); ?></h3>
							<?php
							if ( ! empty( $institute_advanced_address ) ) { ?>
                                <span class="wl-pending-fees-address"><?php echo esc_html( $institute_advanced_address ); ?></span>
                                <br>
							<?php
							}
							if ( ! empty( $institute_advanced_phone ) ) { ?>
                                <span class="wl-pending-fees-contact-phone"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - 
                                    <strong><?php echo esc_html( $institute_advanced_phone ); ?></strong>
									<?php
									if ( ! empty( $institute_advanced_email ) ) { ?> | <?php } ?>
					            </span>
							<?php
							}
							if ( ! empty( $institute_advanced_email ) ) { ?>
                                <span class="wl-pending-fees-contact-email">
									<?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_html( $institute_advanced_email ); ?></strong>
								</span>
							<?php
							} ?>
					</span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 mx-auto">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Name', WL_MIM_DOMAIN ); ?>: </span>
                            <span class="list-group-value"><?php echo esc_html( $name ); ?></span>
                        </li>
                        <li class="list-group-item">
                            <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?>: </span>
                            <span class="list-group-value"><?php echo esc_html( $enrollment_id ); ?></span>
                        </li>
                        <li class="list-group-item">
                            <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?>: </span>
                            <span class="list-group-value"><?php echo esc_html( $course ); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col mx-auto">
                    <div class="mb-2 wl-pending-fees-subtitle text-white"><?php esc_html_e( 'Pending Fees', WL_MIM_DOMAIN ); ?></div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mx-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Fee Type', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Amount Payable', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Amount Paid', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Pending', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
						<?php
						$amount = unserialize( $row->fees );
						foreach ( $amount['type'] as $key => $value ) { ?>
                            <tr>
                                <td>
                                    <span class="text-dark"><?php echo esc_html( $value ); ?></span>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo esc_html( $amount['payable'][ $key ] ); ?></span>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo esc_html( $amount['paid'][ $key ] ); ?></span>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo number_format( $amount['payable'][ $key ] - $amount['paid'][ $key ], 2, '.', '' ); ?></span>
                                </td>
                            </tr>
						<?php
						}
						$total_payable = WL_MIM_Helper::get_fees_total( $amount['payable'] );
						$total_paid    = WL_MIM_Helper::get_fees_total( $amount['paid'] );
						$pending       = number_format( $total_payable - $total_paid, 2, '.', '' );
						?>
                        <tr>
                            <th><span class="text-dark"><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></span></th>
                            <th>
                                <span class="text-dark ml-2"><?php echo esc_html( $total_payable ); ?></span>
                            </th>
                            <th>
                                <span class="text-dark"><?php echo esc_html( $total_paid ); ?></span>
                            </th>
                            <th>
                                <span class="text-dark"><?php echo esc_html( $pending ); ?></span>
                            </th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row wl-pending-fees-authorised-by-row">
                <div class="font-weight-bold mr-3"><?php esc_html_e( 'Authorised Signed By', WL_MIM_DOMAIN ); ?></div>
            </div>
        </div>
    </div>
</div>