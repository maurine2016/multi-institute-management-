<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

global $wpdb;

$institute_id = WL_MIM_Helper::get_current_institute_id();

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

if ( empty( $general_institute['institute_name'] ) ) {
	$institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
	$institute_name = $general_institute['institute_name'];
}

$wlim_institute_exams = $wpdb->get_results( "SELECT id, exam_title, exam_code FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND is_published = 1 AND institute_id = $institute_id ORDER BY id DESC" );
?>

<div class="container-fluid wl_im_container">
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-id-card"></i> <?php esc_html_e( 'Admit Cards', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can view admit cards of students.', WL_MIM_DOMAIN ); ?>
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
                        <div class="h4"><?php esc_html_e( 'Admit Cards', WL_MIM_DOMAIN ); ?></div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
       	 		<div class="row justify-content-md-center">
       	 			<div id="wlim-view-admit-card"></div>
       	 		</div>
                <div class="row justify-content-md-center">
                    <div class="col-md-6 col-xs-12">
		                <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-view-admit-card-form">
		                    <div class="form-group wlim-selectpicker">
		                        <label for="wlim-admit-card-exam" class="col-form-label">
									*<strong><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?></strong>
								</label>
		                        <select name="exam" class="form-control" id="wlim-admit-card-exam">
		                            <option value="">-------- <?php esc_html_e( "Select an Exam", WL_MIM_DOMAIN ); ?> --------</option>                           
									<?php
									if ( count( $wlim_institute_exams ) > 0 ) {
										foreach ( $wlim_institute_exams as $exam ) { ?>
		                                    <option value="<?php echo esc_attr( $exam->id ); ?>"><?php echo esc_html( "$exam->exam_title ( $exam->exam_code )" ); ?></option>
											<?php
										}
									} ?>
		                        </select>
		                    </div>

			                <div class="form-group">
			                    <label for="wlim-admit-card-enrollment_id" class="col-form-label">
									*<strong><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?>:</strong>
								</label>
			                    <input name="enrollment_id" type="text" class="form-control" id="wlim-admit-card-enrollment_id" placeholder="<?php esc_html_e( "Enrollment ID", WL_MIM_DOMAIN ); ?>">
			                </div>

			                <div class="mt-3 float-right">
			                    <button type="submit" class="btn btn-primary view-admit-card-submit"><?php esc_html_e( 'Get Admit Card!', WL_MIM_DOMAIN ); ?></button>
			                </div>
		                </form>
            		</div>
       	 		</div>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
</div>
