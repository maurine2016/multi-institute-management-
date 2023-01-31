<div id="wlmim-admit-card-print">
    <div class="card-body">
        <div class="row">
            <div class="col">
                <div class="row">
					<?php

   					$registration_number = WL_MIM_Helper::get_institute_registration_number( $institute_id );

					$enable_university_header = get_option( 'multi_institute_enable_university_header' );
					if ( $enable_university_header ) {
						$university_logo    = get_option( 'multi_institute_university_logo' );
						$university_name    = get_option( 'multi_institute_university_name' );
						$university_address = get_option( 'multi_institute_university_address' );
						$university_phone   = get_option( 'multi_institute_university_phone' );
						$university_email   = get_option( 'multi_institute_university_email' );
					}

					$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
					$institute_logo    = wp_get_attachment_url( $general_institute['institute_logo'] );
					$show_logo         = $general_institute['institute_logo_enable'];
					$institute_name    = $general_institute['institute_name'];
					$institute_address = $general_institute['institute_address'];
					$institute_phone   = $general_institute['institute_phone'];
					$institute_email   = $general_institute['institute_email'];
   					$date_of_birth     = ( ! empty ( $student->date_of_birth ) ) ? date_format( date_create( $student->date_of_birth ), "d M, Y" ) : '-';

    				$general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings( $institute_id );

					$admit_card = WL_MIM_SettingHelper::get_admit_card_settings( $institute_id );
					?>
                    <div class="col-sm-12 mb-3">
		                <?php if ( $registration_number ) { ?>
		                <span class="float-right mr-2 wl-id-card-registration-number"><?php esc_html_e( 'Registration No.', WL_MIM_DOMAIN ); ?>
		                    <strong><?php echo esc_html( $registration_number ); ?></strong>
		                </span>
		                <?php } ?>
                        <div class="row">
							<?php
							if ( $enable_university_header ) { ?>
                            <div class="col-3 text-right">
                                <img src="<?php echo esc_url( $university_logo ); ?>" id="wlmim-result-institute-logo" class="img-responsive">
                            </div>
							<?php
							} else {
								if ( $show_logo ) { ?>
	                                <div class="col-3 text-right">
	                                    <img src="<?php echo esc_url( $institute_logo ); ?>" id="wlmim-result-institute-logo" class="img-responsive">
	                                </div>
								<?php }
							} ?>

                            <div class="<?php echo boolval( $enable_university_header || $show_logo ) ? "col-9 text-left" : "col-12 text-center"; ?>">
								<?php if ( $enable_university_header || $show_logo ) { ?>
								<span class="float-left">
								<?php
								} else { ?>
								<span>
								<?php
								}
								if ( $enable_university_header ) { ?>
								<h4 id="wlmim-result-institute-name" class="mt-1"><?php echo esc_attr( $university_name ); ?></h4>
								<?php } else { ?>
								<h4 id="wlmim-result-institute-name" class="mt-1"><?php echo esc_attr( $institute_name ); ?></h4>
								<?php }
								if ( $enable_university_header ) {
									if ( ! empty( $university_address ) ) { ?>
										<span id="wlmim-result-institute-address"><?php echo esc_attr( $university_address ); ?></span>
										<br>
										<?php
									}
									if ( ! empty( $university_phone ) ) { ?>
										<span id="wlmim-result-institute-contact-phone">
										<?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_attr( $university_phone ); ?></strong>
										<?php
											if ( ! empty( $university_email ) ) { ?> | <?php } ?>
										</span>
										<?php
									}
									if ( ! empty( $university_email ) ) { ?>
									<span id="wlmim-result-institute-contact-email">
										<?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_attr( $university_email ); ?></strong>
									</span>
									<?php
									}
								} else {
									if ( ! empty( $institute_address ) ) { ?>
										<span id="wlmim-result-institute-address"><?php echo esc_attr( $institute_address ); ?></span>
										<br>
										<?php
									}
									if ( ! empty( $institute_phone ) ) { ?>
										<span id="wlmim-result-institute-contact-phone">
										<?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_attr( $institute_phone ); ?></strong>
										<?php
											if ( ! empty( $institute_email ) ) { ?> | <?php } ?>
										</span>
										<?php
									}
									if ( ! empty( $institute_email ) ) { ?>
									<span id="wlmim-result-institute-contact-email">
										<?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?> - <strong><?php echo esc_attr( $institute_email ); ?></strong>
									</span>
									<?php
									}
								} ?>
								</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                    	<h5 class="text-center mb-3"><?php esc_html_e( "ADMIT CARD", WL_MIM_DOMAIN ); ?></h5>
                    </div>
                    <div class="col-sm-9 mx-auto">
						<table class="table">
							<tbody>
								<tr>
									<th class="p-1"><u><?php esc_html_e( "Exam Title", WL_MIM_DOMAIN ); ?></u></th>
									<td class="p-1"><?php echo esc_html( $exam->exam_title ); ?></td>
								</tr>
								<tr>
									<th class="p-1"><u><?php esc_html_e( "Exam Code", WL_MIM_DOMAIN ); ?></u></th>
									<td class="p-1"><?php echo esc_html( $exam->exam_code ); ?></td>
								</tr>
								<tr>
									<th class="p-1"><u><?php esc_html_e( "Enrollment ID", WL_MIM_DOMAIN ); ?></u></th>
									<td class="p-1"><?php echo esc_html( $enrollment_id ); ?></td>
								</tr>
                    			<?php if ( $general_enable_roll_number ) { ?>
								<tr>
									<th class="p-1"><u><?php esc_html_e( "Roll Number", WL_MIM_DOMAIN ); ?></u></th>
									<td class="p-1"><?php echo esc_html( $student->roll_number ); ?></td>
								</tr>
								<?php } ?>
								<tr>
									<th class="p-1"><u><?php esc_html_e( "Course", WL_MIM_DOMAIN ); ?></u></th>
									<td class="p-1"><?php echo esc_html( $course ); ?></td>
								</tr>
								<tr>
									<th class="p-1"><u><?php esc_html_e( "Name of Candidate", WL_MIM_DOMAIN ); ?></u></th>
									<td class="p-1"><?php echo esc_html( $name ); ?></td>
								</tr>
								<tr>
									<th class="p-1"><u><?php esc_html_e( "Father's Name", WL_MIM_DOMAIN ); ?></u></th>
									<td class="p-1"><?php echo esc_html( $father_name ); ?></td>
								</tr>
								<tr class="mt-3">
									<th class="p-1"><u><?php esc_html_e( "Exam Center", WL_MIM_DOMAIN ); ?></u></th>
									<td class="p-1"><?php echo esc_html( $institute_name . ' - ' . $institute_address ); ?></td>
								</tr>
							</tbody>
						</table>
                    </div>
                    <div class="col-sm-3 mx-auto">
	                    <div id="wl-admit-card-photo-box" class="mt-2">
							<?php
	                        if ( ! empty ( $photo ) ) { ?>
	                            <img src="<?php echo wp_get_attachment_url( $photo ); ?>" id="wl-admit-card-photo" class="img-responsive">
							<?php } ?>
	                    </div>
	                    <div class="mt-5 pt-3 ml-2">
							<?php if ( $admit_card['sign_enable'] && $url = wp_get_attachment_url( $admit_card['sign'] ) ) { ?>
							<img class="wl-authorized-by" src="<?php echo esc_url( $url ); ?>">
							<?php } ?>
	                    	<span class="font-weight-bold"><?php esc_html_e( "Exam Controller", WL_MIM_DOMAIN ); ?></span>
	                    </div>
                    </div>
                    <?php if ( is_array( $notes ) && count( $notes ) ) { ?>
                    <div class="col-sm-12 wlmim-notes">
                    	<div class="font-weight-bold"><?php esc_html_e( "Note", WL_MIM_DOMAIN ); ?>:</div>
                    	<ol>
                    		<?php
                    		foreach( $notes as $note ) {
                    			if ( ! empty( $note ) ) {
                    			?>
                    			<li><?php echo esc_html( $note ); ?></li>
								<?php
                    			}
                    		} ?>
                    	</ol>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>