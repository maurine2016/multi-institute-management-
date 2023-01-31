<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

$wlim_active_institutes = WL_MIM_Helper::get_institutes();
?>

<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="display-4 text-center">
                <span class="border-bottom">
                    <i class="fa fa-user-secret"></i> <?php esc_html_e( 'Users and Administrators', WL_MIM_DOMAIN ); ?>
                </span>
            </h1>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can either add a new administrator or assign administrative permissions to existing users.', WL_MIM_DOMAIN ); ?>
            </div>
            <!-- end main header content -->
        </div>
    </div>
    <!-- end - row 1 -->

    <!-- row 2 -->
    <div class="row">
        <div class="card col">
            <div class="card-header bg-primary">
                <!-- card header content -->
                <div class="row text-white">
                    <div class="col-md-9 col-xs-12">
                        <div class="h4"><?php esc_html_e( 'Manage Users and Administrators', WL_MIM_DOMAIN ); ?></div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-administrator" data-toggle="modal" data-target="#add-user-administrator" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Administrator', WL_MIM_DOMAIN ); ?>
                        </button>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="user-administrator-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Username', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Permissions', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Institute Name', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new user administrator modal -->
<div class="modal fade" id="add-user-administrator" tabindex="-1" role="dialog" aria-labelledby="add-user-administrator-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-administrator-label"><?php esc_html_e( 'Add New Administrator', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-user-administrator-form">
                <div class="modal-body pr-4 pl-4">
					<?php $nonce = wp_create_nonce( 'add-user-administrator' ); ?>
                    <input type="hidden" name="add-user-administrator" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="hidden" name="action" value="wl-mim-add-user-administrator">
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-administrator-first_name" class="col-form-label"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="first_name" type="text" class="form-control" id="wlim-administrator-first_name" placeholder="<?php esc_html_e( "First Name", WL_MIM_DOMAIN ); ?>">
                        </div>
                        <div class="col form-group">
                            <label for="wlim-administrator-last_name" class="col-form-label"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="last_name" type="text" class="form-control" id="wlim-administrator-last_name" placeholder="<?php esc_html_e( "Last Name", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="wlim-administrator-username" class="col-form-label"><?php esc_html_e( 'Username', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="username" type="text" class="form-control" id="wlim-administrator-username" placeholder="<?php esc_html_e( "Username", WL_MIM_DOMAIN ); ?>">
                    </div>
                    <div class="form-group">
                        <label for="wlim-administrator-password" class="col-form-label"><?php esc_html_e( 'Password', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="password" type="password" class="form-control" id="wlim-administrator-password" placeholder="<?php esc_html_e( "Password", WL_MIM_DOMAIN ); ?>">
                    </div>
                    <div class="form-group">
                        <label for="wlim-administrator-password_confirm" class="col-form-label"><?php esc_html_e( 'Confirm Password', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="password_confirm" type="password" class="form-control" id="wlim-administrator-password_confirm" placeholder="<?php esc_html_e( "Confirm Password", WL_MIM_DOMAIN ); ?>">
                    </div>

                    <hr>
                    <div class="form-check pl-0">
                        <input name="manage_multi_institute" class="position-static mt-0 form-check-input wlim-manage_multi_institute" type="checkbox" id="wlim-manage_multi_institute">
                        <label class="form-check-label" for="wlim-manage_multi_institute"><?php esc_html_e( 'Manage Multi Institute', WL_MIM_DOMAIN ); ?></label>
                    </div>

                    <div class="wlim-manage-single-institute">
                        <hr>
                        <div class="form-group">
                            <label for="wlim-administrator-institute" class="col-form-label"><?php esc_html_e( "Manage Single Institute", WL_MIM_DOMAIN ); ?>:</label>
                            <select name="institute" class="form-control selectpicker" id="wlim-administrator-institute">
                                <option value="">-------- <?php esc_html_e( "Select Institute", WL_MIM_DOMAIN ); ?> --------</option>
								<?php
								if ( count( $wlim_active_institutes ) > 0 ) {
									foreach ( $wlim_active_institutes as $active_institute ) { ?>
                                        <option value="<?php echo esc_attr( $active_institute->id ); ?>"><?php echo esc_html( $active_institute->name ); ?></option>
										<?php
									}
								} ?>
                            </select>
                        </div>

                        <label class="col-form-label"><?php esc_html_e( 'Permissions', WL_MIM_DOMAIN ); ?>:</label>
						<?php
						foreach ( WL_MIM_Helper::get_capabilities() as $capability_key => $capability_value ) { ?>
                            <div class="form-check pl-0">
                                <input name="permissions[]" class="position-static mt-0 form-check-input" type="checkbox" id="<?php echo esc_attr( $capability_key ); ?>" value="<?php echo esc_attr( $capability_key ); ?>">
                                <label class="form-check-label" for="<?php echo esc_attr( $capability_key ); ?>"><?php esc_html_e( $capability_value, WL_MIM_DOMAIN ); ?></label>
                            </div>
						<?php
						} ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary add-user-administrator-submit"><?php esc_html_e( 'Add New Administrator', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - add new user administrator modal -->

<!-- update user administrator modal -->
<div class="modal fade" id="update-user-administrator" tabindex="-1" role="dialog" aria-labelledby="update-user-administrator-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-user-administrator-label"><?php esc_html_e( 'Update Administrator', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-user-administrator-form">
                <div class="modal-body pr-4 pl-4" id="fetch_user_administrator"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary update-user-administrator-submit"><?php esc_html_e( 'Update Administrator', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - update user administrator modal -->