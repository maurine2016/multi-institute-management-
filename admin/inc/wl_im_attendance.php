<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

$institute_id = WL_MIM_Helper::get_current_institute_id();

$course_data        = WL_MIM_Helper::get_active_courses_institute( $institute_id );
$get_active_batches = WL_MIM_Helper::get_active_batches_institute( $institute_id );

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

if ( empty( $general_institute['institute_name'] ) ) {
	$institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
	$institute_name = $general_institute['institute_name'];
}
?>

<div class="container-fluid wl_im_container">
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-bar-chart-o"></i> <?php esc_html_e( 'Attendance', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can take attendance of students.', WL_MIM_DOMAIN ); ?>
            </div>
            <!-- end main header content -->
        </div>
    </div>
    <div class="row">
        <div class="col card">
            <div class="card-header bg-primary text-white">
                <!-- card header content -->
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <div class="h4"><?php esc_html_e( 'Take Attendance', WL_MIM_DOMAIN ); ?></div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-attendance-form" enctype="multipart/form-data">
                    <div class="row justify-content-md-center">
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="wlim-attendance-attendance_date" class="col-form-label"><?php esc_html_e( 'Date of Attendance', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="attendance_date" type="text" class="form-control wlim-attendance_date" id="wlim-attendance-attendance_date" placeholder="<?php esc_html_e( "Date of Attendance", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-md-center">
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group wlim-selectpicker">
                                <label for="wlim-attendance-batch" class="col-form-label"><?php esc_html_e( "Batch", WL_MIM_DOMAIN ); ?>:</label>
                                <select multiple name="batch[]" class="form-control selectpicker" id="wlim-attendance-batch">
									<?php
									if ( count( $get_active_batches ) > 0 ) {
										foreach ( $get_active_batches as $active_batch ) {
											$batch  = $active_batch->batch_code . ' ( ' . $active_batch->batch_name . ' )';
											$course = '-';
											if ( $active_batch->course_id && isset( $course_data[ $active_batch->course_id ] ) ) {
												$course_name = $course_data[ $active_batch->course_id ]->course_name;
												$course_code = $course_data[ $active_batch->course_id ]->course_code;
												$course      = "$course_name ($course_code)";
											} ?>
                                            <option value="<?php echo esc_attr( $active_batch->id ); ?>"><?php echo esc_html( "$batch ( $course )" ); ?></option>
											<?php
										}
									} ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-md-center mb-4 text-right">
                        <div class="col-sm-12 col-md-6">
                            <button type="button" class="btn btn-primary btn-sm" id="wlmim-get-students-attendance"><?php esc_html_e( "Get Students", WL_MIM_DOMAIN ); ?></button>
                        </div>
                    </div>
                    <div class="row justify-content-md-center">
                        <div class="col-sm-12 col-md-10">
                            <div id="wlim-attendance-batch-students"></div>
                        </div>
                    </div>
                </form>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
</div>