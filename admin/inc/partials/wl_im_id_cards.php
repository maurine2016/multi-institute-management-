<?php defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
?>
<div id="wl-print-id-cards" class="mx-auto">
	<style>
	    #wl-print-id-cards .list-group-item {
	        padding: 2px 0;
	    }

	    #wl-print-id-cards {
	        width: 1240px;
	        height: auto;
	        margin: 0 auto !important;
	    }

	    .wl-id-cards-box {
	        width: 530px;
	        height: 328px;
	        border: 1px solid #b3b3b3;
	        padding: 5px 0;
	        margin-top: 30px;
	    }

	    #wl-print-id-cards .list-group-item {
	        font-size: 14px;
	    }

	    .wl-id-cards-address {
	        font-size: 12px;
	    }

	    .wl-id-cards-photo-box {
	        height: 150px;
	    }

	    .wl-id-cards-photo {
	        height: 148px;
	        width: 98px;
	    }

	    .wl-id-cards-photo-box {
	        width: 100px;
	        border: 1px solid #b3b3b3;
	    }

	    .wl-institute-pro-id-card-logo {
	        max-height: 70px;
	        max-width: 70px;
	    }

	    .wl-id-cards-title-box {
	        padding-top: 5px;
	    }

	    .wl-id-cards-title {
	        font-size: 13px;
	        font-weight: 600;
	        background-color: #6c757d !important;
	        padding-top: 2px;
	        padding-bottom: 3px;
	    }

	    .wl-id-cards-contact-phone, .wl-id-cards-contact-email {
	        font-size: 12px;
	    }

	    .wl-id-cards-name {
	        font-size: 17px;
	        margin-bottom: 0;
	    }

	    .wl-id-cards-signature-box-row {
	        margin-top: 14px;
	        font-size: 11px;
	    }

	    @media print {
	    	<?php
	    	if ( $print_mode !== 'landscape' ) { ?>
	    	@page { size: portrait; }
	    	<?php
	    	} ?>
	        body {
	            -webkit-print-color-adjust: exact;
	            overflow: visible;
	        }
			.wlmim-page {
				border: initial;
				border-radius: initial;
				width: initial;
				min-height: initial;
				box-shadow: initial;
				background: initial;
			}
			.wlmim-page {
				width: 43.5%;
				float: left;
			}
			.wlmim-page:nth-of-type(2n) {
				margin-left: 2px;
			}
			.wlmim-page:nth-of-type(2n+1) {
				margin-right: 2px;
			}
			.wlmim-page:nth-of-type(8n+1) {
				page-break-inside: avoid;
				page-break-after: always;
				display: block;
			}
		    .wlmim-page .list-group-item {
		        padding-top: 2px !important;
		        padding-bottom: 2px !important;
		    }
	    }
	</style>
	<?php
	foreach ( $data as $row ) {
	$institute_id  = WL_MIM_Helper::get_current_institute_id();

    $registration_number = WL_MIM_Helper::get_institute_registration_number( $institute_id );

    $general_institute         = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );
    $general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

    $general_enable_roll_number = WL_MIM_SettingHelper::get_general_enable_roll_number_settings( $institute_id );

	if (get_option( 'multi_institute_enable_seprate_enrollment_id', '1' )) {
		$student_id = $row->enrollment_id;
	} else {
		$student_id = $row->id;
	}
	$enrollment_id = WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix );
	$course        = $wpdb->get_row( "SELECT course_name, course_code, duration, duration_in FROM {$wpdb->prefix}wl_min_courses WHERE id = $row->course_id AND institute_id = $institute_id" );
	$batch         = $wpdb->get_row( "SELECT batch_code FROM {$wpdb->prefix}wl_min_batches WHERE is_deleted = 0 AND id = $row->batch_id AND institute_id = $institute_id" );

	if ( $batch ) {
		$batch = $batch->batch_code;
	} else {
		$batch = '';
	}

	$duration    = $course->duration;
	$duration_in = $course->duration_in;
	$duration_in = ( $duration < 2 ) ? __( substr( $duration_in, 0, - 1 ), WL_MIM_DOMAIN ) : __( $duration_in, 0, - 1, WL_MIM_DOMAIN );

	$name           = $row->first_name . " $row->last_name";
	$course         = ( ! empty ( $course ) ) ? "{$course->course_name} ({$course->course_code})" : '-';
	$duration       = "{$duration} {$duration_in}";
	$admission_date = date_format( date_create( $row->created_at ), "d M, Y" );
	$phone          = ( ! empty ( $row->phone ) ) ? $row->phone : '-';
	$email          = ( ! empty ( $row->email ) ) ? $row->email : '-';

	$institute_advanced_logo    = wp_get_attachment_url( $general_institute['institute_logo'] );
	$institute_advanced_name    = $general_institute['institute_name'];
	$institute_advanced_address = $general_institute['institute_address'];
	$institute_advanced_phone   = $general_institute['institute_phone'];
	$institute_advanced_email   = $general_institute['institute_email'];
	$photo                      = $row->photo_id;
	$signature                  = $row->signature_id;
	$show_logo                  = $general_institute['institute_logo_enable'];

    $id_card = WL_MIM_SettingHelper::get_id_card_settings( $institute_id );
	?>
	<div class="wlmim-page">
	    <div class="wl-id-cards-box">
	        <div class="row">
				<?php if ( $show_logo ) { ?>
	                <div class="col-3 mx-auto mt-2">
	                    <img src="<?php echo esc_url( $institute_advanced_logo ); ?>"
	                         class="img-responsive float-right wl-institute-pro-id-card-logo">
	                </div>
					<?php
				} ?>
	            <div class="<?php echo $show_logo ? "col-9 " : "col-12 text-center "; ?>mx-auto">
	                <?php if ( $registration_number ) { ?>
	                <span class="float-right mr-2 wl-id-card-registration-number"><?php esc_html_e( 'Registration No.', WL_MIM_DOMAIN ); ?>
	                    <strong><?php echo esc_html( $registration_number ); ?></strong>
	                </span>
	                <?php } ?>
					<?php if ( $show_logo ) { ?>
	                <span class="float-left">
					<?php
					} else { ?>
	                    <span>
					<?php
					} ?>
	                        <h4 class="mt-1 wl-id-cards-name"><?php echo esc_html( $institute_advanced_name ); ?></h4>
							<?php
							if ( ! empty( $institute_advanced_address ) ) { ?>
	                            <span class="wl-id-cards-address"><?php echo esc_html( $institute_advanced_address ); ?></span><br>
								<?php
							}
							if ( ! empty( $institute_advanced_phone ) ) { ?>
	                            <span class="wl-id-cards-contact-phone"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?>
	                                - <strong><?php echo esc_html( $institute_advanced_phone ); ?></strong>
									<?php
									if ( ! empty( $institute_advanced_email ) ) { ?> | <?php } ?>
						</span>
								<?php
							}
							if ( ! empty( $institute_advanced_email ) ) { ?>
	                            <span class="wl-id-cards-contact-email"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?>
	                                - <strong><?php echo esc_html( $institute_advanced_email ); ?></strong></span>
								<?php
							} ?>
					</span>
	            </div>
	        </div>
	        <div class="row text-center">
	            <div class="col wl-id-cards-title-box">
	                <h5 class="text-white wl-id-cards-title"><?php esc_html_e( 'STUDENT IDENTITY CARD', WL_MIM_DOMAIN ); ?></h5>
	            </div>
	        </div>
	        <div class="row">
	            <div class="col-4 pl-5">
	                <div class="mt-2 wl-id-cards-photo-box">
						<?php if ( ! empty ( $photo ) ) { ?>
	                        <img src="<?php echo wp_get_attachment_url( $photo ); ?>"
	                             class="img-responsive wl-id-cards-photo">
						<?php } ?>
	                </div>
					        <div class="wl-id-cards-signature-box-row pt-4">
					            <div class="text-center">
					                <div class="pr-4"><?php esc_html_e( 'Authorized By', WL_MIM_DOMAIN ); ?></div>
					                <div class="wl-id-cards-signature-box ml-4">
										<?php if ( $id_card['sign_enable'] && $url = wp_get_attachment_url( $id_card['sign'] ) ) { ?>
										<img class="wl-authorized-by" src="<?php echo esc_url( $url ); ?>">
										<?php } ?>
					                </div>
					            </div>
					        </div>
	            </div>
	            <div class="col-8 mx-auto">
	                <ul class="list-group list-group-flush">
	                    <li class="list-group-item">
	                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
	                        <span class="list-group-value"><?php echo esc_html( $enrollment_id ); ?></span>
	                    </li>
                    	<?php if ( $general_enable_roll_number ) { ?>
	                    <li class="list-group-item">
	                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Roll Number', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
	                        <span class="list-group-value"><?php echo esc_html( $row->roll_number ); ?></span>
	                    </li>
                    	<?php } ?>
	                    <li class="list-group-item">
	                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Name', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
	                        <span class="list-group-value"><?php echo esc_html( $name ); ?></span>
	                    </li>
	                    <li class="list-group-item">
	                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
	                        <span class="list-group-value"><?php echo esc_html( $course ); ?></span>
	                    </li>
	                    <li class="list-group-item">
	                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
	                        <span class="list-group-value"><?php echo esc_html( $batch ); ?></span>
	                    </li>
	                    <li class="list-group-item">
	                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Admission Date', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
	                        <span class="list-group-value"><?php echo esc_html( $admission_date ); ?></span>
	                    </li>
	                    <li class="list-group-item">
	                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
	                        <span class="list-group-value"><?php echo esc_html( $phone ); ?></span>
	                    </li>
	                    <li class="list-group-item">
	                        <span class="list-group-heading font-weight-bold"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
	                        <span class="list-group-value"><?php echo esc_html( $email ); ?></span>
	                    </li>
	                </ul>
	            </div>
	        </div>
	    </div>
    </div>
    <?php
	} ?>
</div>