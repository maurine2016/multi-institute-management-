<?php defined( 'ABSPATH' ) || die(); ?>
<div id="wl-invoice-fee-invoice">
	<?php
    	$registration_number = WL_MIM_Helper::get_institute_registration_number( $institute_id );

		$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

		$enrollment_id  = WL_MIM_Helper::get_enrollment_id_with_prefix( $student->enrollment_id, $general_enrollment_prefix );
		$course         = $wpdb->get_row( "SELECT course_name, course_code, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $student->course_id AND institute_id = $institute_id" );
		$batch          = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $student->batch_id AND institute_id = $institute_id" );

		$invoice_number = WL_MIM_Helper::get_invoice( $row->id );
		$name           = $student->first_name . " $student->last_name";
		$father_name    = $student->father_name ? $student->father_name : '';
		$date_of_birth    = $student->date_of_birth;
		$course         = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '';

		if ( $batch ) {
			$time_from    = date( "g:i A", strtotime( $batch->time_from ) );
			$time_to      = date( "g:i A", strtotime( $batch->time_to ) );
			$timing       = "$time_from - $time_to";
			$batch        = $batch->batch_code . ' ( ' . $timing . ' )';
		} else {
			$batch = '';
		}

		$phone          = ( ! empty ( $student->phone ) ) ? $student->phone : '';
		$email          = ( ! empty ( $student->email ) ) ? $student->email : '';
		$address        = ( ! empty ( $student->address ) ) ? $student->address : '';
		if ( $student->city ) {
			$address .= ", $student->city";
		}
		if ( $student->zip ) {
			$address .= " - $student->zip";
		}
		if ( $student->state ) {
			$address .= ", $student->state";
		}
		$date           = ( ! empty ( $row->created_at ) ) ? date_format( date_create( $row->created_at ), "d M, Y" ) : '';

		$institute_advanced_logo    = wp_get_attachment_url( $general_institute['institute_logo'] );
		$institute_advanced_name    = $general_institute['institute_name'];
		$institute_advanced_address = $general_institute['institute_address'];
		$institute_advanced_phone   = $general_institute['institute_phone'];
		$institute_advanced_email   = $general_institute['institute_email'];
		$show_logo                  = $general_institute['institute_logo_enable'];
	?>
	<div id="wl-invoice-fee-invoice-box" style="margin: 0px; ">
		<div class="row">
			<?php
			if ( $show_logo ) { ?>
			<div class="col-3 mx-auto">
				<img src="<?php echo esc_url( $institute_advanced_logo ); ?>" id="wl-institute-pro-fee-invoice-logo" width="70" class="img-responsive float-right">
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
						<h4 class="mt-1" id="wl-fee-invoice-name"><?php echo esc_html( $institute_advanced_name ); ?></h4>
						<?php
						if ( ! empty( $institute_advanced_address ) ) { ?>
							<span id="wl-fee-invoice-address"><?php echo esc_html( $institute_advanced_address ); ?></span>
							<br>
						<?php
						}
						if ( ! empty( $institute_advanced_phone ) ) { ?>
							<span id="wl-fee-invoice-contact-phone"><?php esc_html_e( 'Phone', WL_MIM_Helper ); ?> - 
								<strong><?php echo esc_html( $institute_advanced_phone ); ?></strong>
							<?php
							if ( ! empty( $institute_advanced_email ) ) { ?> | <?php } ?>
							</span>
						<?php
						}
						if ( ! empty( $institute_advanced_email ) ) { ?>
							<span id="wl-fee-invoice-contact-email"><?php esc_html_e( 'Email', WL_MIM_Helper ); ?> - 
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
							<th scope="col"><?php esc_html_e( 'Name', WL_MIM_Helper ); ?></th>
							<td><?php echo esc_html( $name ); ?></td>
							<th scope="col"><?php esc_html_e( 'Course', WL_MIM_Helper ); ?></th>
							<td><?php echo esc_html( $course ); ?></td>
						</tr>
						<tr class="border-bottom">
							<th scope="col"><?php esc_html_e( "Father's Name", WL_MIM_Helper ); ?></th>
							<td><?php echo esc_html( $father_name ); ?></td>
							<th scope="col"><?php esc_html_e( 'Batch', WL_MIM_Helper ); ?></th>
							<td><?php echo esc_html( $batch ); ?></td>
						</tr>
						<tr class="border-bottom">
							<th scope="col"><?php esc_html_e( "Date Of Birth", WL_MIM_Helper ); ?></th>
							<td><?php echo esc_html( $date_of_birth ); ?></td>
							<th scope="col"></th>
							<td></td>
						</tr>
                       
						<div class="text-center">
                                        <span class="text-dark"><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?>:&nbsp;</span><strong><?php echo esc_html( "$exam->exam_title ( $exam->exam_code )" ); ?></strong>
                                    </div>
                                <div class="exam_marks_obtained_box">
                                    <table class="table table-bordered">
                                        <thead>
										<tr>
											<th><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
											<th colspan = "2"><?php esc_html_e( 'Maximum Marks', WL_MIM_DOMAIN ); ?></th>
											<th colspan = "3"><?php esc_html_e( 'Marks Obtained', WL_MIM_DOMAIN ); ?></th>
											
                                        </tr>
                                        <tr>
											<th></th>
											<th><?php esc_html_e( 'Theory', WL_MIM_DOMAIN ); ?></th>
											<th><?php esc_html_e( 'Practical', WL_MIM_DOMAIN ); ?></th>
											<th><?php esc_html_e( 'Theory', WL_MIM_DOMAIN ); ?></th>
											<th><?php esc_html_e( 'Practical', WL_MIM_DOMAIN ); ?></th>
											<th><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody class="exam_marks_obtained_rows exam_marks_obtained_table">
										<?php
										$marks_obtained = null;
										$marks_obtained_p = null;
										if ( $result ) {
											$marks_obtained   = unserialize( $result->marks );
											$marks_obtained_p = unserialize( $result->marks_p );
										}
										$total_maximum_marks         = 0;
										$marks_obtained_in_subject_p = 0;
										foreach ( $marks['subject'] as $subject_key => $subject_value ) {
											$marks_obtained_in_subject = 0;
											$marks_obtained_in_subject = 0;
											if ( ! empty( $marks_obtained ) ) {
												$marks_obtained_in_subject   = $marks_obtained[ $subject_key ];
												$marks_obtained_in_subject_p = $marks_obtained_p[ $subject_key ];
											}
											$total_maximum_marks    += $marks['maximum'][ $subject_key ];
											$total_maximum_marks_p  += $marks['maximum_p'][ $subject_key ];
											$total_marks_obtained   += $marks_obtained_in_subject;
											$total_marks_obtained_p += $marks_obtained_in_subject_p;
											?>
                                            <tr>
                                                <td>
                                                    <span class="text-dark"><?php echo esc_html( $subject_value ); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-dark"><?php echo esc_html( $marks['maximum'][ $subject_key ] ); ?></span>
                                                </td>
												 <td>
                                                    <span class="text-dark"><?php echo esc_html( $marks['maximum_p'][ $subject_key ] ); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-dark"><?php echo esc_html( $marks_obtained_in_subject ); ?></span>
                                                </td>
												 <td>
                                                    <span class="text-dark"><?php echo esc_html( $marks_obtained_in_subject_p ); ?></span>
                                                </td> 
												<td>
                                                    <span class="text-dark"><?php echo esc_html($marks_obtained_in_subject + $marks_obtained_in_subject_p ); ?></span>
                                                </td>
                                            </tr>
											<?php
										} ?>
                                         <tr>
											<th><?php esc_html_e( 'Total', WL_MIM_DOMAIN ); ?></th>
											<th><?php echo esc_attr( $total_maximum_marks ); ?></th>
											<th><?php echo esc_attr( $total_maximum_marks_p ); ?></th>
											<th><?php echo esc_attr( $total_marks_obtained ); ?></th>
											<th><?php echo esc_attr( $total_marks_obtained_p ); ?></th>
											<th><?php echo esc_attr( $total_marks_obtained + $total_marks_obtained_p ); 
											$t = $total_marks_obtained + $total_marks_obtained_p;
											$t_m = $total_maximum_marks + $total_maximum_marks_p;
											?></th>
										</tr>
										<tr>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
											<th><?php esc_html_e( 'Percentage', WL_MIM_DOMAIN ); ?></th>
											<!-- <th><?php echo number_format( max( floatval( ( $total_marks_obtained / $total_maximum_marks ) * 100 ), 0 ), 2, '.', '' ); ?>%</th> -->
											<th><?php echo number_format( max( floatval( ( $t / $t_m ) * 100 ), 0 ), 2, '.', '' ); ?>%</th>
										</tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        
                   
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>