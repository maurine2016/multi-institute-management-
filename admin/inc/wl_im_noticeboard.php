<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

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
                <span class="border-bottom"><i class="fa fa-clipboard"></i> <?php esc_html_e( 'Noticeboard', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can either add a new notice or edit existing notices.', WL_MIM_DOMAIN ); ?>
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
                        <div class="h4"><?php esc_html_e( 'Manage Notices', WL_MIM_DOMAIN ); ?></div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-notice" data-toggle="modal" data-target="#add-notice" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Notice', WL_MIM_DOMAIN ); ?>
                        </button>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="notice-table">
                        <?php $entity = 'notice';
                            require( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php' ); ?>
                            <thead>
                                <tr>
                                <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e( 'Notice Title', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Notice URL', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Priority', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new notice modal -->
<div class="modal fade" id="add-notice" tabindex="-1" role="dialog" aria-labelledby="add-notice-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-notice-label"><?php esc_html_e( 'Add New Notice', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-notice-form" enctype="multipart/form-data">
                <div class="modal-body pr-4 pl-4">
					<?php $nonce = wp_create_nonce( 'add-notice' ); ?>
                    <input type="hidden" name="add-notice" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="hidden" name="action" value="wl-mim-add-notice">
                    <div class="form-group">
                        <label for="wlim-notice-title" class="col-form-label">* <?php esc_html_e( 'Notice Title', WL_MIM_DOMAIN ); ?>:</label>
                        <textarea name="title" class="form-control" rows="3" id="wlim-notice-title" placeholder="<?php esc_html_e( "Notice Title", WL_MIM_DOMAIN ); ?>"></textarea>
                    </div>
                    <div class="form-group mt-3 pl-0 pt-3 border-top">
                        <label><?php esc_html_e( 'Link to', WL_MIM_DOMAIN ); ?>:</label><br>
                        <div class="row">
                            <div class="col">
                                <label class="radio-inline"><input type="radio" name="notice_link_to" value="attachment" id="wlim-notice-attachment"><?php esc_html_e( 'Attachment', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>
                            <div class="col">
                                <label class="radio-inline"><input type="radio" name="notice_link_to" value="url" id="wlim-notice-url"><?php esc_html_e( 'URL', WL_MIM_DOMAIN ); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group wlim-notice-attachment">
                        <label for="wlim-notice-attachment" class="col-form-label"><?php esc_html_e( 'Attachment', WL_MIM_DOMAIN ); ?>:</label><br>
                        <input name="attachment" type="file" id="wlim-notice-attachment">
                    </div>
                    <div class="form-group wlim-notice-url">
                        <label for="wlim-notice-url" class="col-form-label"><?php esc_html_e( 'URL', WL_MIM_DOMAIN ); ?>
                            :</label>
                        <input name="url" type="text" class="form-control" id="wlim-notice-url" placeholder="<?php esc_html_e( "Notice URL", WL_MIM_DOMAIN ); ?>">
                    </div>
                    <div class="form-group">
                        <label for="wlim-notice-priority" class="col-form-label"><?php esc_html_e( 'Priority', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="priority" type="number" class="form-control" id="wlim-notice-priority" placeholder="<?php esc_html_e( "Notice Priority", WL_MIM_DOMAIN ); ?>" step="1" value="10">
                    </div>
                    <div class="form-check pl-0">
                        <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-notice-is_active" checked>
                        <label class="form-check-label" for="wlim-notice-is_active">
							<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary add-notice-submit"><?php esc_html_e( 'Add New Notice', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - add new notice modal -->

<!-- update notice modal -->
<div class="modal fade" id="update-notice" tabindex="-1" role="dialog" aria-labelledby="update-notice-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-notice-label"><?php esc_html_e( 'Update Notice', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-notice-form" enctype="multipart/form-data">
                <div class="modal-body pr-4 pl-4" id="fetch_notice"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary update-notice-submit"><?php esc_html_e( 'Update Notice', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - update notice modal -->