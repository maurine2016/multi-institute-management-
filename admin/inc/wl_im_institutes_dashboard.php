<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

$wlim_institutes = WL_MIM_Helper::get_institutes();
$institute_id    = WL_MIM_Helper::get_current_institute_id();
global $wpdb;
?>

<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="display-4 text-center">
                <span class="border-bottom"><i class="fa fa-graduation-cap"></i> <?php esc_html_e( 'Multi Institute Dashboard', WL_MIM_DOMAIN ); ?></span>
            </h1>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can view overview of institutes.', WL_MIM_DOMAIN ); ?>
            </div>
            <!-- end main header content -->
        </div>
    </div>
    <!-- end - row 1 -->

    <!-- row 2 -->
    <div class="row justify-content-md-center">
        <div class="col-6">
            <div class="h6"><?php esc_html_e( 'Set current institute', WL_MIM_DOMAIN ); ?></div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-set-institute-form">
				<?php $nonce = wp_create_nonce( 'set-institute' ); ?>
                <input type="hidden" name="set-institute" value="<?php echo esc_attr( $nonce ); ?>">
                <input type="hidden" name="action" value="wl-mim-set-institute">
                <select name="institute" class="selectpicker form-control" id="wlim-institute-current">
                    <option value="">-------- <?php esc_html_e( "Select Institute", WL_MIM_DOMAIN ); ?> --------</option>
					<?php
					if ( count( $wlim_institutes ) > 0 ) {
						foreach ( $wlim_institutes as $institute ) { ?>
                            <option <?php selected( $institute_id, $institute->id, true ); ?>
                                    value="<?php echo esc_attr( $institute->id ); ?>"><?php echo esc_html( $institute->name ); ?></option>
						<?php
						}
					} ?>
                </select>
                <div class="text-right mt-2">
                    <button type="submit" class="btn btn-primary btn-sm set-institute-submit"><?php esc_html_e( 'Set Institute', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
    <!-- end - row 2 -->

    <!-- row 3 -->
    <div class="row">
        <div class="card col">
            <div class="card-header bg-primary text-white">
                <!-- card header content -->
                <div class="row">
                    <div class="col-md-9 col-xs-12">
                        <div class="h4"><?php esc_html_e( 'View Statistics and Overview', WL_MIM_DOMAIN ); ?></div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row text-center">
					<?php
					if ( count( $wlim_institutes ) ) {
						$course_data  = $wpdb->get_results( "SELECT id, institute_id FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0", OBJECT_K );
						$student_data = $wpdb->get_results( "SELECT id, institute_id FROM {$wpdb->prefix}wl_min_students WHERE is_deleted = 0", OBJECT_K );

						foreach ( $wlim_institutes as $institute ) {
							/* Filter courses */
							$institute_courses = array();
							$institute_courses = array_filter( $course_data, function ( $course ) use ( $institute ) {
								return $course->institute_id == $institute->id;
							} );
							if ( $institute_courses && is_array( $institute_courses ) ) {
								$institute_courses_count = count( $institute_courses );
							} else {
								$institute_courses_count = 0;
							}

							/* Filter students */
							$institute_students = array();
							$institute_students = array_filter( $student_data, function ( $student ) use ( $institute ) {
								return $student->institute_id == $institute->id;
							} );
							if ( $institute_students && is_array( $institute_students ) ) {
								$institute_students_count = count( $institute_students );
							} else {
								$institute_students_count = 0;
							}
							?>
                            <div class="col-md-4 col-sm-6 col-xs-12 mb-4">
                                <ul class="list-group border border-primary">
                                    <li class="list-group-item active h5">
                                        <strong>
                                            <i class="fa fa-graduation-cap"></i>
                                            <a data-security="<?php echo esc_attr( $nonce ); ?>" data-id="<?php echo esc_attr( $institute->id ); ?>" class="text-white wlmim-institute-switch" href="javascript:void(0)" class="text-white">
												<?php echo esc_html( $institute->name ); ?>
                                            </a>
                                        </strong>
                                    </li>
                                    <li class="list-group-item h6">
                                        <span class="text-secondary"><?php esc_html_e( 'Total Courses', WL_MIM_DOMAIN ); ?>:</span>
                                        <span><?php echo esc_html( $institute_courses_count ); ?></span>
                                    </li>
                                    <li class="list-group-item h6">
                                        <span class="text-secondary"><?php esc_html_e( 'Total Students', WL_MIM_DOMAIN ); ?>:</span>
                                        <span><?php echo esc_html( $institute_students_count ); ?></span>
                                    </li>
                                </ul>
                            </div>
						<?php
						}
					} else { ?>
                        <div class="col-md-12">
                            <div class="alert alert-info">
								<?php esc_html_e( 'There is currently no institute.', WL_MIM_DOMAIN ); ?>&nbsp;
                                <a class="btn btn-sm btn-primary" href="<?php menu_page_url( 'multi-institute-new' ); ?>"><?php esc_html_e( 'Add New Institute', WL_MIM_DOMAIN ); ?></a>
                            </div>
                        </div>
					<?php
					} ?>
                </div>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
    <!-- end - row 3 -->
</div>