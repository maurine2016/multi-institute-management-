<?php defined( 'ABSPATH' ) || die(); ?>
<div id="wl-fees-report" class="row">
	<?php
	$institute_id = WL_MIM_Helper::get_current_institute_id();

    $registration_number = WL_MIM_Helper::get_institute_registration_number( $institute_id );

	$general_institute         = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
	$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );
	$general_receipt_prefix    = WL_MIM_SettingHelper::get_general_receipt_prefix_settings( $institute_id );
    if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
        $student_id = $row->enrollment_id;
    } else {
        $student_id = $row->id;
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
    <div class="wl-fees-report-section col">
        <div class="wl-fees-report-box">
            <div class="row">
                <div class="col mx-auto">
                    <?php if ( $registration_number ) { ?>
                    <span class="float-left wl-fees-report-address"><?php esc_html_e( 'Registration No.', WL_MIM_DOMAIN ); ?>
                        <strong><?php echo esc_html( $registration_number ); ?></strong>
                    </span>
                    <?php } ?>
					<span class="float-right text-right wl-fees-report-copy">
						<div class="text-secondary mb-2"><?php esc_html_e( 'Student Copy', WL_MIM_DOMAIN ); ?></div>
					</span>
                </div>
            </div>
            <div class="row">
				<?php
                if ( $show_logo ) { ?>
                    <div class="col-3 mx-auto">
                        <img src="<?php echo esc_url( $institute_advanced_logo ); ?>" class="wl-institute-pro-fees-report-logo img-responsive float-right">
                    </div>
				<?php
				} ?>
                <div class="<?php echo boolval( $show_logo ) ? "col-9 " : "col-12 text-center "; ?>mx-auto">
					<?php
                    if ( $show_logo ) { ?>
                        <span class="float-left">
					<?php
					} else { ?>
                        <span>
					<?php
					} ?>
                            <h3 class="wl-fees-report-name mt-1"><?php echo esc_html( $institute_advanced_name ); ?></h3>
							<?php
							if ( ! empty( $institute_advanced_address ) ) { ?>
                                <span class="wl-fees-report-address"><?php echo esc_html( $institute_advanced_address ); ?></span>
                                <br>
							<?php
							}
							if ( ! empty( $institute_advanced_phone ) ) { ?>
                                <span class="wl-fees-report-contact-phone"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - 
                                    <strong><?php echo esc_html( $institute_advanced_phone ); ?></strong>
									<?php
									if ( ! empty( $institute_advanced_email ) ) { ?> | <?php } ?>
					            </span>
							<?php
							}
							if ( ! empty( $institute_advanced_email ) ) { ?>
                                <span class="wl-fees-report-contact-email"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - 
                                    <strong><?php echo esc_html( $institute_advanced_email ); ?></strong>
                                </span>
							<?php
							} ?>
					</span>
                </div>
            </div>
            <div class="row text-center">
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
            <hr>
            <div class="row">
                <div class="col mx-auto">
                    <div class="mb-2 wl-fees-report-subtitle text-white"><?php esc_html_e( 'Fees Report', WL_MIM_DOMAIN ); ?></div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mx-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Receipt', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
						<?php
						if ( count( $installments ) !== 0 ) {
							foreach ( $installments as $installment ) {
								$fees     = unserialize( $installment->fees );
								$id       = $installment->id;
								$receipt  = WL_MIM_Helper::get_receipt_with_prefix( $id, $general_receipt_prefix );
								$amount   = WL_MIM_Helper::get_fees_total( $fees['paid'] );
								$date     = date_format( date_create( $installment->created_at ), "d-m-Y g:i A" );
								$added_by = ( $user = get_userdata( $installment->added_by ) ) ? $user->user_login : '-';
								?>
                                <tr>
                                    <td><?php echo esc_html( $receipt ); ?></td>
                                    <td><?php echo esc_html( $amount ); ?></td>
                                    <td><?php echo esc_html( $date ); ?></td>
                                </tr>
							<?php
							}
							if ( isset( $installments_remaining ) && count( $installments_remaining ) !== 0 ) {
								$amount = 0;
								foreach ( $installments_remaining as $installment ) {
									$fees   = unserialize( $installment->fees );
									$amount += WL_MIM_Helper::get_fees_total( $fees['paid'] );
								} ?>
                                <tr>
                                    <td><?php echo WL_MIM_Helper::get_receipt_with_prefix( $installments_remaining[0]->id, $general_receipt_prefix ) . " - " . WL_MIM_Helper::get_receipt_with_prefix( $installments_remaining[ count( $installments_remaining ) - 1 ]->id, $general_receipt_prefix ); ?></td>
                                    <td><?php echo esc_html( $amount ); ?></td>
                                    <td><?php echo date_format( date_create( $installments_remaining[0]->created_at ), "d-m-Y g:i A" ) . " - " . date_format( date_create( $installments_remaining[ count( $installments_remaining ) - 1 ]->created_at ), "d-m-Y g:i A" ); ?></td>
                                </tr>
							<?php
							}
						} ?>
                        <tr>
                            <th scope="col"><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></th>
                            <th scope="col"><?php echo esc_html( $fees_paid ); ?></th>
                            <th scope="col"></th>
                        </tr>
                        <tr>
                            <th scope="col">
                                <span class="text-danger"><?php esc_html_e( 'Pending Fees', WL_MIM_DOMAIN ); ?></span>
                            </th>
                            <th scope="col"><?php echo esc_html( $pending_fees ); ?></th>
                            <th scope="col"></th>
                        </tr>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row wl-fees-report-authorised-by-row">
                <div class="font-weight-bold mr-3"><?php esc_html_e( 'Authorised Signed By', WL_MIM_DOMAIN ); ?></div>
            </div>
        </div>
    </div>
    <div class="wl-fees-report-section col">
        <div class="wl-fees-report-box">
            <div class="row">
                <div class="col mx-auto">
                    <?php if ( $registration_number ) { ?>
                    <span class="float-left wl-fees-report-address"><?php esc_html_e( 'Registration No.', WL_MIM_DOMAIN ); ?>
                        <strong><?php echo esc_html( $registration_number ); ?></strong>
                    </span>
                    <?php } ?>
					<span class="float-right text-right wl-fees-report-copy">
						<div class="text-secondary mb-2"><?php esc_html_e( 'Institute Copy', WL_MIM_DOMAIN ); ?></div>
					</span>
                </div>
            </div>
            <div class="row">
				<?php
                if ( $show_logo ) { ?>
                    <div class="col-4 mx-auto">
                        <img src="<?php echo esc_url( $institute_advanced_logo ); ?>" class="wl-institute-pro-fees-report-logo img-responsive float-right">
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
                            <h3 class="wl-fees-report-name mt-1"><?php echo esc_html( $institute_advanced_name ); ?></h3>
							<?php
							if ( ! empty( $institute_advanced_address ) ) { ?>
                                <span class="wl-fees-report-address"><?php echo esc_html( $institute_advanced_address ); ?></span>
                                <br>
							<?php
							}
							if ( ! empty( $institute_advanced_phone ) ) { ?>
                                <span class="wl-fees-report-contact-phone"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - 
                                    <strong><?php echo esc_html( $institute_advanced_phone ); ?></strong>
									<?php
									if ( ! empty( $institute_advanced_email ) ) { ?> | <?php } ?>
						        </span>
							<?php
							}
							if ( ! empty( $institute_advanced_email ) ) { ?>
                                <span class="wl-fees-report-contact-email"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - 
                                    <strong><?php echo esc_html( $institute_advanced_email ); ?></strong>
                                </span>
							<?php
							} ?>
					</span>
                </div>
            </div>
            <div class="row text-center">
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
            <hr>
            <div class="row">
                <div class="col mx-auto">
                    <div class="mb-2 wl-fees-report-subtitle text-white"><?php esc_html_e( 'Fees Report', WL_MIM_DOMAIN ); ?></div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mx-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Receipt', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
						<?php
						if ( count( $installments ) !== 0 ) {
							foreach ( $installments as $installment ) {
								$fees     = unserialize( $installment->fees );
								$id       = $installment->id;
								$receipt  = WL_MIM_Helper::get_receipt_with_prefix( $id, $general_receipt_prefix );
								$amount   = WL_MIM_Helper::get_fees_total( $fees['paid'] );
								$date     = date_format( date_create( $installment->created_at ), "d-m-Y g:i A" );
								$added_by = ( $user = get_userdata( $installment->added_by ) ) ? $user->user_login : '-';
								?>
                                <tr>
                                    <td><?php echo esc_html( $receipt ); ?></td>
                                    <td><?php echo esc_html( $amount ); ?></td>
                                    <td><?php echo esc_html( $date ); ?></td>
                                </tr>
							<?php
							}
							if ( isset( $installments_remaining ) && count( $installments_remaining ) !== 0 ) {
								$amount = 0;
								foreach ( $installments_remaining as $installment ) {
									$fees   = unserialize( $installment->fees );
									$amount += WL_MIM_Helper::get_fees_total( $fees['paid'] );
								} ?>
                                <tr>
                                    <td><?php echo WL_MIM_Helper::get_receipt_with_prefix( $installments_remaining[0]->id, $general_receipt_prefix ) . " - " . WL_MIM_Helper::get_receipt_with_prefix( $installments_remaining[ count( $installments_remaining ) - 1 ]->id, $general_receipt_prefix ); ?></td>
                                    <td><?php echo esc_html( $amount ); ?></td>
                                    <td><?php echo date_format( date_create( $installments_remaining[0]->created_at ), "d-m-Y g:i A" ) . " - " . date_format( date_create( $installments_remaining[ count( $installments_remaining ) - 1 ]->created_at ), "d-m-Y g:i A" ); ?></td>
                                </tr>
							<?php
							}
						} ?>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php echo esc_html( $fees_paid ); ?></th>
                                <th scope="col"></th>
                            </tr>
                            <tr>
                                <th scope="col">
                                    <span class="text-danger"><?php esc_html_e( 'Pending Fees', WL_MIM_DOMAIN ); ?></span>
                                </th>
                                <th scope="col"><?php echo esc_html( $pending_fees ); ?></th>
                                <th scope="col"></th>
                            </tr>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col"></th>
                                <th scope="col"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row wl-fees-report-authorised-by-row">
                <div class="font-weight-bold mr-3"><?php esc_html_e( 'Authorised Signed By', WL_MIM_DOMAIN ); ?></div>
            </div>
        </div>
    </div>
</div>