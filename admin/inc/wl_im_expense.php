<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php' );

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
                <span class="border-bottom"><i class="fa fa-envelope"></i> <?php esc_html_e( 'Expense', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can either add a new expense or edit existing expense.', WL_MIM_DOMAIN ); ?>
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
                        <div class="h4"><?php esc_html_e( 'Manage Expense', WL_MIM_DOMAIN ); ?></div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-expense" data-toggle="modal" data-target="#add-expense" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Expense', WL_MIM_DOMAIN ); ?>
                        </button>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="expense-table">
                        <?php
					$entity = 'expense';
                    require( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php' ); ?>
                            <thead>
                                <tr>
                                <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Consumption Date', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new expense modal -->
<div class="modal fade" id="add-expense" tabindex="-1" role="dialog" aria-labelledby="add-expense-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-expense-label"><?php esc_html_e( 'Add New Expense', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-expense-form">
                <div class="modal-body pr-4 pl-4">
					<?php $nonce = wp_create_nonce( 'add-expense' ); ?>
                    <input type="hidden" name="add-expense" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="hidden" name="action" value="wl-mim-add-expense">
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label for="wlim-expense-title" class="col-form-label">* <?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="title" type="text" class="form-control" id="wlim-expense-title" placeholder="<?php esc_html_e( "Title", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label for="wlim-expense-description" class="col-form-label"><?php esc_html_e( 'Description', WL_MIM_DOMAIN ); ?>:</label>
                            <textarea name="description" class="form-control" rows="3" id="wlim-expense-description" placeholder="<?php esc_html_e( "Description", WL_MIM_DOMAIN ); ?>"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label for="wlim-expense-amount" class="col-form-label">* <?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?>
                                :</label>
                            <input name="amount" type="number" class="form-control" id="wlim-expense-amount" placeholder="<?php esc_html_e( "Amount", WL_MIM_DOMAIN ); ?>" min="0" step="any">
                        </div>
                        <div class="col-sm-6 form-group">
                            <label for="wlim-expense-consumption_date" class="col-form-label">* <?php esc_html_e( 'Consumption Date', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="consumption_date" type="text" class="form-control wlim-consumption_date" id="wlim-expense-consumption_date" placeholder="<?php esc_html_e( "Consumption Date", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label for="wlim-expense-notes" class="col-form-label"><?php esc_html_e( 'Notes', WL_MIM_DOMAIN ); ?>:</label>
                            <textarea name="notes" class="form-control" rows="3" id="wlim-expense-notes" placeholder="<?php esc_html_e( "Note", WL_MIM_DOMAIN ); ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary add-expense-submit"><?php esc_html_e( 'Add New Expense', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - add new expense modal -->

<!-- update expense modal -->
<div class="modal fade" id="update-expense" tabindex="-1" role="dialog" aria-labelledby="update-expense-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-expense-label"><?php esc_html_e( 'Update Expense', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-expense-form">
                <div class="modal-body pr-4 pl-4" id="fetch_expense"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary update-expense-submit"><?php esc_html_e( 'Update Expense', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - update expense modal -->