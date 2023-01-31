<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php' );

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
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-pencil"></i> <?php esc_html_e( 'Study Material', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can either add a new note or edit existing study material.', WL_MIM_DOMAIN ); ?>
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
                        <div class="h4"><?php esc_html_e( 'Manage Study Material', WL_MIM_DOMAIN ); ?></div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-note" data-toggle="modal" data-target="#add-note" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Study Material', WL_MIM_DOMAIN ); ?>
                        </button>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="note-table">
                        <?php $entity = 'note';
                            require( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php' ); ?>
                            <thead>
                                <tr>
                                <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new note modal -->
<div class="modal fade" id="add-note" tabindex="-1" role="dialog" aria-labelledby="add-note-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-note-label"><?php esc_html_e( 'Add New Study Material', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-note-form" enctype="multipart/form-data">
                <div class="modal-body pr-4 pl-4">
					<?php $nonce = wp_create_nonce( 'add-note' ); ?>
                    <input type="hidden" name="add-note" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="hidden" name="action" value="wl-mim-add-note">
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label for="wlim-note-title" class="col-form-label">* <?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="title" type="text" class="form-control" id="wlim-note-title" placeholder="<?php esc_html_e( "Title", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="form-group wlim-selectpicker">
                        <label for="wlim-note-batch" class="col-form-label">* <?php esc_html_e( "Batch", WL_MIM_DOMAIN ); ?>:</label>
                        <select name="batch" class="form-control selectpicker" id="wlim-note-batch">
                            <option value=""><?php esc_html_e( "-------- Select a Batch --------", WL_MIM_DOMAIN ); ?></option>
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
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label for="wlim-note-description" class="col-form-label"><?php esc_html_e( 'Description', WL_MIM_DOMAIN ); ?>:</label>
                            <textarea name="description" class="form-control" rows="4" id="wlim-note-description" placeholder="<?php esc_html_e( "Description", WL_MIM_DOMAIN ); ?>"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label for="wlim-note-date" class="col-form-label">* <?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="notes_date" type="text" class="form-control wlim-notes_date" id="wlim-note-date" placeholder="<?php esc_html_e( "Date", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label for="wlim-note-documents" class="col-form-label">* <?php esc_html_e( 'File(s)', WL_MIM_DOMAIN ); ?>
                                :</label><br>
                            <input type="file" name="documents[]" id="wlim-note-documents" multiple>
                            (<?php esc_html_e( 'Hold Ctrl to select multiple files', WL_MIM_DOMAIN ); ?>)
                        </div>
                    </div>
                    <hr>
                    <div class="form-check pl-0">
                        <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-note-is_active" checked>
                        <label class="form-check-label" for="wlim-note-is_active">
							<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary add-note-submit"><?php esc_html_e( 'Add New Study Material', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - add new note modal -->

<!-- update note modal -->
<div class="modal fade" id="update-note" tabindex="-1" role="dialog" aria-labelledby="update-note-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-note-label"><?php esc_html_e( 'Update Study Material', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-note-form" enctype="multipart/form-data">
                <div class="modal-body pr-4 pl-4" id="fetch_note"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary update-note-submit"><?php esc_html_e( 'Update Study Material', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - update note modal -->