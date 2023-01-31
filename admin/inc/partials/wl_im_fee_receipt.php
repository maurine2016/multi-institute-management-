<?php defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
?>
<div id="wl-installment-fee-receipt">
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
	$course        = $wpdb->get_row( "SELECT course_name, course_code, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $student->course_id AND institute_id = $institute_id" );
	$batch         = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $student->batch_id AND institute_id = $institute_id" );

	$receipt_number = WL_MIM_Helper::get_receipt_with_prefix( $row->id, $general_receipt_prefix );
	$name           = $student->first_name . " $student->last_name";
	$father_name    = $student->father_name ? $student->father_name : '';
	$course         = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '';

	if ( $batch ) {
		$time_from = date( "g:i A", strtotime( $batch->time_from ) );
		$time_to   = date( "g:i A", strtotime( $batch->time_to ) );
		$timing    = "$time_from - $time_to";
		$batch     = $batch->batch_code . ' ( ' . $timing . ' )';
	} else {
		$batch = '';
	}

	$phone   = ( ! empty ( $student->phone ) ) ? $student->phone : '';
	$email   = ( ! empty ( $student->email ) ) ? $student->email : '';
	$address = ( ! empty ( $student->address ) ) ? $student->address : '';
	if ( $student->city ) {
		$address .= ", $student->city";
	}
	if ( $student->zip ) {
		$address .= " - $student->zip";
	}
	if ( $student->state ) {
		$address .= ", $student->state";
	}
	$date = ( ! empty ( $row->created_at ) ) ? date_format( date_create( $row->created_at ), "d M, Y" ) : '';

	$institute_advanced_logo    = wp_get_attachment_url( $general_institute['institute_logo'] );
	$institute_advanced_name    = $general_institute['institute_name'];
	$institute_advanced_address = $general_institute['institute_address'];
	$institute_advanced_phone   = $general_institute['institute_phone'];
	$institute_advanced_email   = $general_institute['institute_email'];
	$show_logo                  = $general_institute['institute_logo_enable'];
	?>
    <div id="wl-installment-fee-receipt-box">
        <div class="row">
            <div class="col mx-auto">
                <?php if ( $registration_number ) { ?>
                <span class="float-left wl-receipt-number mb-1"><?php esc_html_e( 'Registration No.', WL_MIM_DOMAIN ); ?>
                    <strong><?php echo esc_html( $registration_number ); ?></strong>
                </span>
                <?php } ?>
                <span class="float-right wl-receipt-number"><?php esc_html_e( 'Receipt No.', WL_MIM_DOMAIN ); ?>
                    <strong><?php echo esc_html( $receipt_number ); ?></strong>
                </span>
            </div>
        </div>
        <div class="row">
			<?php
            if ( $show_logo ) { ?>
                <div class="col-3 mx-auto">
                    <img src="<?php echo esc_url( $institute_advanced_logo ); ?>" id="wl-institute-pro-fee-receipt-logo" class="img-responsive float-right">
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
                        <h4 class="mt-1" id="wl-fee-receipt-name"><?php echo esc_html( $institute_advanced_name ); ?></h4>
        				<?php
        				if ( ! empty( $institute_advanced_address ) ) { ?>
                            <span id="wl-fee-receipt-address"><?php echo esc_html( $institute_advanced_address ); ?></span>
                            <br>
        				<?php
        				}
        				if ( ! empty( $institute_advanced_phone ) ) { ?>
                            <span id="wl-fee-receipt-contact-phone"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - 
                                <strong><?php echo esc_html( $institute_advanced_phone ); ?></strong>
        						<?php
        						if ( ! empty( $institute_advanced_email ) ) { ?> | <?php } ?>
        			       </span>
        				<?php
        				}
        				if ( ! empty( $institute_advanced_email ) ) { ?>
                            <span id="wl-fee-receipt-contact-email"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - 
                                <strong><?php echo esc_html( $institute_advanced_email ); ?></strong>
                            </span>
        				<?php
        				} ?>
				</span>
            </div>
        </div>
        <div class="row">
            <div class="col-10 col-offset-1 mx-auto">
                <table class="table mt-3">
                    <tbody>
                        <tr class="border-bottom">
                            <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
                            <td><?php echo esc_html( $enrollment_id ); ?></td>
                            <th><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?></th>
                            <td><?php echo esc_html( $date ); ?></td>
                        </tr>
                        <tr class="border-bottom">
                            <th scope="col"><?php esc_html_e( 'Name', WL_MIM_DOMAIN ); ?></th>
                            <td><?php echo esc_html( $name ); ?></td>
                            <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                            <td><?php echo esc_html( $course ); ?></td>
                        </tr>
                        <tr class="border-bottom">
                            <th scope="col"><?php esc_html_e( "Father's Name", WL_MIM_DOMAIN ); ?></th>
                            <td><?php echo esc_html( $father_name ); ?></td>
                            <th scope="col"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
                            <td><?php echo esc_html( $batch ); ?></td>
                        </tr>
                        <tr class="border-bottom">
                            <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                            <td><?php echo esc_html( $phone ); ?></td>
                            <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
                            <td><?php echo esc_html( $email ); ?></td>
                        </tr>
                        <tr class="border-bottom-0">
                            <th scope="col"><?php esc_html_e( 'Address', WL_MIM_DOMAIN ); ?></th>
                            <td colspan="3"><?php echo esc_html( $address ); ?></td>
                        </tr>
                        <tr>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php esc_html_e( 'S.No.', WL_MIM_DOMAIN ); ?></th>
                                        <th scope="col"><?php esc_html_e( 'Particulars', WL_MIM_DOMAIN ); ?></th>
                                        <th scope="col"><?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
    							<?php
    							$i = 0;
    							foreach ( $installments['paid'] as $key => $amount ) {
    								if ( $amount > 0 ) {
    									$i ++;
    									?>
                                        <tr>
                                            <td><?php echo esc_html( $i ); ?></td>
                                            <td><?php echo esc_html( $installments['type'][ $key ] ); ?></td>
                                            <td><?php echo esc_html( $amount ); ?></td>
                                        </tr>
									<?php
    								}
    							} ?>
                                    <tr>
                                        <td></td>
                                        <th scope="col"><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></th>
                                        <th><?php echo WL_MIM_Helper::get_fees_total( $installments['paid'] ); ?></th>
                                    </tr>
                                </tbody>
                            </table>
                        </tr>
                        <tr>
                            <th scope="col"></th>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row wl-fee-receipt-signature-box-row">
            <div class="col-6 text-left"></div>
            <div class="col-6 text-right">
                <div class="font-weight-bold">
                    <span class="border-dark border-top pt-1 pl-3 pr-3"><?php esc_html_e( 'Accountant', WL_MIM_DOMAIN ); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>