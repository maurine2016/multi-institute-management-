<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php' );

$wlim_active_courses = WL_MIM_Helper::get_active_courses();

$institute_id = WL_MIM_Helper::get_current_institute_id();

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

if ( empty( $general_institute['institute_name'] ) ) {
	$institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
	$institute_name = $general_institute['institute_name'];
}
?>

<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-object-group"></i> <?php esc_html_e( 'Batches', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can either add a new batch or edit existing batches.', WL_MIM_DOMAIN ); ?>
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
                        <div class="h4"><?php esc_html_e( 'Manage Batches', WL_MIM_DOMAIN ); ?></div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-batch" data-toggle="modal" data-target="#add-batch" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Batch', WL_MIM_DOMAIN ); ?>
                        </button>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="batch-table">
                        <?php
					$entity = 'batch';
                    require( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php' ); ?>
                            <thead>
                                <tr>
                                <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e( 'Batch Code', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Batch Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Batch Timing', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Starting Date', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Ending Date', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Total Students', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Active Students', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Batch Status', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Edit', WL_MIM_DOMAIN ); ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
    <!-- end - row 2 -->
</div>

<!-- add new batch modal -->
<div class="modal fade" id="add-batch" tabindex="-1" role="dialog" aria-labelledby="add-batch-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-batch-label"><?php esc_html_e( 'Add New Batch', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4">
                <form id="wlim-add-batch-form">
					<?php $nonce = wp_create_nonce( 'add-batch' ); ?>
                    <input type="hidden" name="add-batch" value="<?php echo esc_attr( $nonce ); ?>">
                    <div class="form-group wlim-selectpicker">
                        <label for="wlim-batch-course" class="col-form-label">* <?php esc_html_e( "Course", WL_MIM_DOMAIN ); ?>
                            :</label>
                        <select name="course" class="form-control selectpicker" id="wlim-batch-course">
                            <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?> --------</option>
							<?php
							if ( count( $wlim_active_courses ) > 0 ) {
								foreach ( $wlim_active_courses as $active_course ) { ?>
                                    <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?></option>
									<?php
								}
							} ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-batch-batch_code" class="col-form-label">* <?php esc_html_e( 'Batch Code', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="batch_code" type="text" class="form-control" id="wlim-course-batch_code" placeholder="<?php esc_html_e( "Batch Code", WL_MIM_DOMAIN ); ?>">
                        </div>
                        <div class="col form-group">
                            <label for="wlim-batch-batch_name" class="col-form-label"><?php esc_html_e( 'Batch Name', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="batch_name" type="text" class="form-control" id="wlim-batch-batch_name" placeholder="<?php esc_html_e( "Batch Name", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label for="wlim-batch-time_from" class="col-form-label">* <?php esc_html_e( 'Timing From', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="time_from" type="text" class="form-control wlim-batch-time" id="wlim-batch-time_from" placeholder="<?php esc_html_e( "Timing From", WL_MIM_DOMAIN ); ?>">
                        </div>
                        <div class="col-sm-6 form-group">
                            <label for="wlim-batch-time_to" class="col-form-label">* <?php esc_html_e( 'Timing To', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="time_to" type="text" class="form-control wlim-batch-time" id="wlim-batch-time_to" placeholder="<?php esc_html_e( "Timing To", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                    <div class="col-sm-6 form-group ">
                            <label for="wlim-batch-end_date" class="col-form-label"><?php esc_html_e( 'Never Ending', WL_MIM_DOMAIN ); ?>:</label>
                            <input type="checkbox" name="end_date" id="no-end-date" class="form-control ">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label for="wlim-batch-start_date" class="col-form-label">* <?php esc_html_e( 'Starting Date', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="start_date" type="text" class="form-control wlim-batch-date" id="wlim-batch-start_date" placeholder="<?php esc_html_e( "Starting Date", WL_MIM_DOMAIN ); ?>">
                        </div>
                        <div class="col-sm-6 form-group no-end-date-div" id="no-end-date-div">
                            <label for="wlim-batch-end_date" class="col-form-label"><?php esc_html_e( 'Ending Date ', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="end_date" type="text" class="form-control wlim-batch-date" id="wlim-batch-end_date" placeholder="<?php esc_html_e( "Ending Date", WL_MIM_DOMAIN ); ?>">
                        </div> 
                        
                    </div>
                    <hr>
                    <div class="form-check pl-0">
                        <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-batch-is_active" checked>
                        <label class="form-check-label" for="wlim-batch-is_active">
							<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary add-batch-submit"><?php esc_html_e( 'Add New Batch', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - add new batch modal -->

<!-- update batch modal -->
<div class="modal fade" id="update-batch" tabindex="-1" role="dialog" aria-labelledby="update-batch-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-batch-label"><?php esc_html_e( 'Update Batch', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="fetch_batch"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary update-batch-submit"><?php esc_html_e( 'Update Batch', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - update batch modal -->