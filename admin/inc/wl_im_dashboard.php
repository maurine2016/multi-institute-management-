<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php' );

$institute_id = WL_MIM_Helper::get_current_institute_id();

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

if ( empty( $general_institute['institute_name'] ) ) {
	$institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
	$institute_name = $general_institute['institute_name'];
}

$data                      = WL_MIM_Helper::get_data();
$count                     = $data['count'];
$course_data               = $data['course_data'];
$recent_enquiries          = $data['recent_enquiries'];
$popular_courses_enquiries = $data['popular_courses_enquiries'];
$revenue                   = $data['revenue'];
?>

<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can view statistics and reports.', WL_MIM_DOMAIN ); ?>
            </div>
            <!-- end main header content -->
        </div>
    </div>
    <!-- end - row 1 -->

    <!-- row 2 -->
    <div class="row">
        <div class="card col">
            <div class="card-header bg-primary text-white">
                <!-- card header content -->
                <div class="row">
                    <div class="col-md-9 col-xs-12">
                        <div class="h4"><?php esc_html_e( 'View Statistics and Reports', WL_MIM_DOMAIN ); ?></div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row text-center">
					<?php
					if ( current_user_can( 'wl_min_manage_courses' ) ) { ?>
                        <div class="col-md-3 col-sm-4 col-xs-2 mb-4">
                            <ul class="list-group">
                                <li class="list-group-item active h5"><i class="fa fa-graduation-cap"></i>
                                    <a href="<?php menu_page_url( 'multi-institute-management-courses' ); ?>" class="text-white">
										<?php esc_html_e( 'Courses', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Total Courses', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $count->courses ); ?></span>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Active Courses', WL_MIM_DOMAIN ); ?>:</span>&nbsp;<span><?php echo esc_html( $count->courses_active ); ?></span>
                                </li>
                            </ul>
                        </div>
					<?php
					}
					if ( current_user_can( 'wl_min_manage_batches' ) ) { ?>
                        <div class="col-md-3 col-sm-4 col-xs-2 mb-4">
                            <ul class="list-group">
                                <li class="list-group-item active h5"><i class="fa fa-object-group"></i>
                                    <a href="<?php menu_page_url( 'multi-institute-management-batches' ); ?>" class="text-white">
										<?php esc_html_e( 'Batches', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Total Batches', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $count->batches ); ?></span>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Active Batches', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $count->batches_active ); ?></span>
                                </li>
                            </ul>
                        </div>
					<?php
					}
					if ( current_user_can( 'wl_min_manage_enquiries' ) ) { ?>
                        <div class="col-md-3 col-sm-4 col-xs-2 mb-4">
                            <ul class="list-group">
                                <li class="list-group-item active h5"><i class="fa fa-envelope"></i>
                                    <a href="<?php menu_page_url( 'multi-institute-management-enquiries' ); ?>" class="text-white">
										<?php esc_html_e( 'Enquiries', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Total Enquiries', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $count->enquiries ); ?></span>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Active Enquiries', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $count->enquiries_active ); ?></span>
                                </li>
                            </ul>
                        </div>
					<?php
					}
					if ( current_user_can( 'wl_min_manage_students' ) ) { ?>
                        <div class="col-md-3 col-sm-4 col-xs-2 mb-4">
                            <ul class="list-group">
                                <li class="list-group-item active h5"><i class="fa fa-users"></i>
                                    <a href="<?php menu_page_url( 'multi-institute-management-students' ); ?>" class="text-white">
										<?php esc_html_e( 'Students', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Total Students', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $count->students ); ?></span>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Active Students', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $count->students_active ); ?></span>
                                </li>
                            </ul>
                        </div>
					<?php
					}
					if ( current_user_can( 'wl_min_manage_fees' ) ) { ?>
                        <div class="col-md-3 col-sm-4 col-xs-2 mb-4">
                            <ul class="list-group">
                                <li class="list-group-item active h5"><i class="fa fa-usd"></i>
                                    <a href="<?php menu_page_url( 'multi-institute-management-fees' ); ?>" class="text-white">
										<?php esc_html_e( 'Fees', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Active Students with Fees Pending', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $count->students_fees_pending ); ?></span>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Students with Fees Paid', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $count->students_fees_paid ); ?></span>
                                </li>
                            </ul>
                        </div>
					<?php
					}
					if ( current_user_can( 'wl_min_manage_fees' ) ) { ?>
                        <div class="col-md-3 col-sm-4 col-xs-2 mb-4">
                            <ul class="list-group">
                                <li class="list-group-item active h5"><i class="fa fa-usd"></i>
                                    <a href="<?php menu_page_url( 'multi-institute-management-fees' ); ?>" class="text-white">
										<?php esc_html_e( 'Installments', WL_MIM_DOMAIN ); ?>
                                    </a>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Total Installments', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $count->installments ); ?></span>
                                </li>
                                <li class="list-group-item h6">
                                    <span class="text-secondary"><?php esc_html_e( 'Revenue', WL_MIM_DOMAIN ); ?>:&nbsp;</span>
                                    <span><?php echo esc_html( $revenue ); ?></span>
                                </li>
                            </ul>
                        </div>
					<?php
					} ?>
                    <?php if ( current_user_can( 'wl_min_manage_enquiries' ) ) { ?>
                        <div class="col-md-6 col-sm-8 col-xs-4 mb-4">
                            <div class="row text-center">
                                <div class="col-md-12">
                                    <ul class="list-group">
                                        <li class="list-group-item active h5"><i class="fa fa-envelope"></i>
                                            <?php esc_html_e( 'Enquiry Follow up Reminders', WL_MIM_DOMAIN ); ?>
                                        </li>
                                    </ul>
                                </div>
                                <?php
                                $yesterday = new DateTime( '-1day' );
                                $today     = new DateTime();
                                $tommorow  = new DateTime( '+1day' );

                                $yesterday = $yesterday->format('Y-m-d');
                                $today     = $today->format('Y-m-d');
                                $tommorow  = $tommorow->format('Y-m-d');

                                global $wpdb;

                                $total_enquiries_yesteday = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND institute_id = $institute_id AND follow_up_date = '$yesterday'" );
                                $total_enquiries_today    = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND institute_id = $institute_id AND follow_up_date = '$today'" );
                                $total_enquiries_tommorow = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wl_min_enquiries WHERE is_deleted = 0 AND institute_id = $institute_id AND follow_up_date = '$tommorow'" );
                                ?>
                                <div class="col-md-4 col-sm-3 col-6 mb-4">
                                    <ul class="list-group">
                                        <li class="list-group-item h6"><i class="fa fa-calendar"></i>
                                            <a href="<?php menu_page_url( 'multi-institute-management-enquiries' ); ?>&follow_up=<?php echo esc_attr( $yesterday ); ?>" class="text-secondary">
                                                <?php esc_html_e( 'Yesterday', WL_MIM_DOMAIN ); ?>
                                                <span class="border-0 badge badge-pill badge-primary"><?php echo esc_attr( $total_enquiries_yesteday ); ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4 col-sm-3 col-6 mb-4">
                                    <ul class="list-group">
                                        <li class="list-group-item h6"><i class="fa fa-calendar"></i>
                                            <a href="<?php menu_page_url( 'multi-institute-management-enquiries' ); ?>&follow_up=<?php echo esc_attr( $today ); ?>" class="text-secondary">
                                                <?php esc_html_e( 'Today', WL_MIM_DOMAIN ); ?>
                                                <span class="border-0 badge badge-pill badge-primary"><?php echo esc_attr( $total_enquiries_today ); ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4 col-sm-3 col-6 mb-4">
                                    <ul class="list-group">
                                        <li class="list-group-item h6"><i class="fa fa-calendar"></i>
                                            <a href="<?php menu_page_url( 'multi-institute-management-enquiries' ); ?>&follow_up=<?php echo esc_attr( $tommorow ); ?>" class="text-secondary">
                                                <?php esc_html_e( 'Tommorow', WL_MIM_DOMAIN ); ?>
                                                <span class="border-0 badge badge-pill badge-primary"><?php echo esc_attr( $total_enquiries_tommorow ); ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                         </div>
                    <?php } ?>
                </div>
                <div class="row">
					<?php
					if ( current_user_can( 'wl_min_manage_enquiries' ) ) { ?>
                        <div class="col bg-primary m-3 border-bottom border-primary">
                            <div class="h5 mt-3 mb-3 text-white text-center"><i class="fa fa-envelope"></i>
                                <a href="<?php menu_page_url( 'multi-institute-management-enquiries' ); ?>" class="text-white">
									<?php esc_html_e( 'Recent Enquiries', WL_MIM_DOMAIN ); ?>
                                </a>
                            </div>
							<?php
							if ( count( $recent_enquiries ) > 0 ) { ?>
                                <ul class="list-group list-group-flush">
									<?php foreach ( $recent_enquiries as $enquiry ) {
										$course = '-';
										if ( $enquiry->course_id && isset( $course_data[ $enquiry->course_id ] ) ) {
											$course_name = $course_data[ $enquiry->course_id ]->course_name;
											$course_code = $course_data[ $enquiry->course_id ]->course_code;
											$course      = "$course_name ($course_code)";
										}
										?>
                                        <li class="list-group-item align-items-center">
											<?php echo "<strong class='text-secondary'>" . WL_MIM_Helper::get_enquiry_id( $enquiry->id ) . "</strong> - <strong>" . $course . "</strong>"; ?>
                                            <span class="text-secondary float-right">
									           <?php echo date_format( date_create( $enquiry->created_at ), "d-m-Y g:i A" ); ?>
								            </span>
                                        </li>
									<?php
									} ?>
                                </ul>
							<?php
							} else { ?>
                                <div class="text-white text-center pb-3"><?php esc_html_e( 'There is no enquiry.', WL_MIM_DOMAIN ); ?></div>
							<?php
							} ?>
                        </div>
					<?php
					}
					if ( current_user_can( 'wl_min_manage_courses' ) ) { ?>
                        <div class="col bg-primary m-3 border-bottom border-primary">
                            <div class="h5 mt-3 mb-3 text-white text-center"><i class="fa fa-graduation-cap"></i>
                                <a href="<?php menu_page_url( 'multi-institute-management-courses' ); ?>" class="text-white">
									<?php esc_html_e( 'Popular Courses', WL_MIM_DOMAIN ); ?>
                                </a>
                            </div>
							<?php
							$popular_courses_count = 0;
							if ( count( $popular_courses_enquiries ) > 0 ) { ?>
                                <ul class="list-group list-group-flush">
									<?php
									foreach ( $popular_courses_enquiries as $enquiry ) {
										if ( $enquiry->course_id && isset( $course_data[ $enquiry->course_id ] ) ) {
											if ( $course_data[ $enquiry->course_id ]->is_deleted == 0 ) {
												$course_name = $course_data[ $enquiry->course_id ]->course_name;
												$course_code = $course_data[ $enquiry->course_id ]->course_code;
												$course      = "$course_name ($course_code)"; ?>
                                                <li class="list-group-item align-items-center">
													<?php echo "<strong>" . $course . "</strong>"; ?>
                                                    <span class="text-secondary float-right">
									                   <?php echo esc_html( $enquiry->students ) . " "; ?><?php echo ( $enquiry->students == 1 ) ? esc_html__( 'Student', WL_MIM_DOMAIN ) : esc_html__( 'Students', WL_MIM_DOMAIN ); ?>
								                    </span>
                                                </li>
												<?php
												$popular_courses_count ++;
											}
										}
									} ?>
                                </ul>
							<?php
							}
							if ( $popular_courses_count == 0 ) { ?>
                                <div class="text-white text-center pb-3"><?php esc_html_e( 'There is no popular course.', WL_MIM_DOMAIN ); ?></div>
							<?php
							} ?>
                        </div>
					<?php
					} ?>
                </div>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
    <!-- end - row 2 -->
</div>