<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

$institute_id = WL_MIM_Helper::get_current_institute_id();

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
                <span class="border-bottom"><i class="fa fa-user-secret"></i> <?php esc_html_e( 'Users and Administrators', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
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
                        <button type="button" class="btn btn-outline-light float-right add-administrator" data-toggle="modal" data-target="#add-administrator" data-backdrop="static" data-keyboard="false">
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
                        <table class="table table-hover table-striped table-bordered" id="administrator-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Username', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Permissions', WL_MIM_DOMAIN ); ?></th>
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

    <!-- row 3 -->
    <div class="row">
        <div class="card col">
            <div class="card-header bg-primary">
                <!-- card header content -->
                <div class="row text-white">
                    <div class="col-md-12 col-xs-12">
                        <div class="h4"><?php esc_html_e( 'Staff Records', WL_MIM_DOMAIN ); ?></div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="staff-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php esc_html_e( 'Username', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Salary', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Job Title', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Job Description', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?></th>
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
    <!-- end - row 3 -->
</div>

<!-- add new administrator modal -->
<div class="modal fade" id="add-administrator" tabindex="-1" role="dialog" aria-labelledby="add-administrator-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-administrator-label"><?php esc_html_e( 'Add New Administrator', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-administrator-form" enctype="multipart/form-data">
                <div class="modal-body pr-4 pl-4">
					<?php $nonce = wp_create_nonce( 'add-administrator' ); ?>
                    <input type="hidden" name="add-administrator" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="hidden" name="action" value="wl-mim-add-administrator">
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
                    <label class="col-form-label"><?php esc_html_e( 'Permissions', WL_MIM_DOMAIN ); ?>:</label>
					<?php
					foreach ( WL_MIM_Helper::get_capabilities() as $capability_key => $capability_value ) {
						if ( ! current_user_can( $capability_key ) ) {
							continue;
						} ?>
                        <div class="form-check pl-0">
                            <input name="permissions[]" class="position-static mt-0 form-check-input" type="checkbox" id="<?php echo esc_attr( $capability_key ); ?>" value="<?php echo esc_attr( $capability_key ); ?>">
                            <label class="form-check-label" for="<?php echo esc_attr( $capability_key ); ?>"><?php esc_html_e( $capability_value, WL_MIM_DOMAIN ); ?></label>
                        </div>
					<?php
					} ?>
                    <hr>
                    <div class="form-check pl-0">
                        <input name="add_staff_record" class="position-static mt-0 form-check-input wlim-administrator-add_staff_record" type="checkbox" id="wlim-administrator-add_staff_record">
                        <label class="form-check-label" for="wlim-administrator-add_staff_record">
                            <strong class="text-primary"><?php esc_html_e( 'Add Staff Record?', WL_MIM_DOMAIN ); ?></strong>
                        </label>
                    </div>
                    <div class="wlim-staff-record-fields">
                        <hr>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-salary" class="col-form-label">* <?php esc_html_e( 'Salary', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="salary" type="number" class="form-control" id="wlim-administrator-salary" placeholder="<?php esc_html_e( "Salary", WL_MIM_DOMAIN ); ?>" min="0" step="any">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-job_title" class="col-form-label">* <?php esc_html_e( 'Job Title', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="job_title" type="text" class="form-control" id="wlim-administrator-job_title" placeholder="<?php esc_html_e( "Job Title", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="wlim-administrator-job_description" class="col-form-label"><?php esc_html_e( 'Job Description', WL_MIM_DOMAIN ); ?>:</label>
                            <textarea name="job_description" class="form-control" rows="4" id="wlim-administrator-job_description" placeholder="<?php esc_html_e( "Job Description", WL_MIM_DOMAIN ); ?>"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label class="col-form-label"><?php esc_html_e( 'Gender', WL_MIM_DOMAIN ); ?>:</label><br>
                                <div class="row mt-2">
                                    <div class="col-sm-12">
                                        <label class="radio-inline mr-3">
                                            <input checked type="radio" name="gender" class="mr-2" value="male" id="wlim-administrator-male"><?php esc_html_e( 'Male', WL_MIM_DOMAIN ); ?>
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="gender" class="mr-2" value="female" id="wlim-administrator-female"><?php esc_html_e( 'Female', WL_MIM_DOMAIN ); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-date_of_birth" class="col-form-label"><?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="date_of_birth" type="text" class="form-control wlim-date_of_birth" id="wlim-administrator-date_of_birth" placeholder="<?php esc_html_e( "Date of Birth", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-address" class="col-form-label"><?php esc_html_e( 'Address', WL_MIM_DOMAIN ); ?>:</label>
                                <textarea name="address" class="form-control" rows="4" id="wlim-administrator-address" placeholder="<?php esc_html_e( "Address", WL_MIM_DOMAIN ); ?>"></textarea>
                            </div>
                            <div class="col-sm-6 form-group">
                                <div>
                                    <label for="wlim-administrator-city" class="col-form-label"><?php esc_html_e( 'City', WL_MIM_DOMAIN ); ?>:</label>
                                    <input name="city" type="text" class="form-control" id="wlim-administrator-city" placeholder="<?php esc_html_e( "City", WL_MIM_DOMAIN ); ?>">
                                </div>
                                <div>
                                    <label for="wlim-administrator-zip" class="col-form-label"><?php esc_html_e( 'Zip Code', WL_MIM_DOMAIN ); ?>:</label>
                                    <input name="zip" type="text" class="form-control" id="wlim-administrator-zip" placeholder="<?php esc_html_e( "Zip Code", WL_MIM_DOMAIN ); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-state" class="col-form-label"><?php esc_html_e( 'State', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="state" type="text" class="form-control" id="wlim-administrator-state" placeholder="<?php esc_html_e( "State", WL_MIM_DOMAIN ); ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-nationality" class="col-form-label"><?php esc_html_e( 'Nationality', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="nationality" type="text" class="form-control" id="wlim-administrator-nationality" placeholder="<?php esc_html_e( "Nationality", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-phone" class="col-form-label"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="phone" type="text" class="form-control" id="wlim-administrator-phone" placeholder="<?php esc_html_e( "Phone", WL_MIM_DOMAIN ); ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-email" class="col-form-label"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="email" type="text" class="form-control" id="wlim-administrator-email" placeholder="<?php esc_html_e( "Email", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-qualification" class="col-form-label"><?php esc_html_e( 'Qualification', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="qualification" type="text" class="form-control" id="wlim-administrator-qualification" placeholder="<?php esc_html_e( "Qualification", WL_MIM_DOMAIN ); ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-id_proof" class="col-form-label"><?php esc_html_e( 'ID Proof', WL_MIM_DOMAIN ); ?>:</label><br>
                                <input name="id_proof" type="file" id="wlim-administrator-id_proof">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-photo" class="col-form-label"><?php esc_html_e( 'Choose Photo', WL_MIM_DOMAIN ); ?>:</label><br>
                                <input name="photo" type="file" id="wlim-administrator-photo">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-administrator-signature" class="col-form-label"><?php esc_html_e( 'Choose Signature', WL_MIM_DOMAIN ); ?>:</label><br>
                                <input name="signature" type="file" id="wlim-administrator-signature">
                            </div>
                        </div>
                        <div class="form-check pl-0">
                            <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-administrator-is_active" checked>
                            <label class="form-check-label" for="wlim-administrator-is_active">
								<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary add-administrator-submit"><?php esc_html_e( 'Add New Administrator', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - add new administrator modal -->

<!-- update administrator modal -->
<div class="modal fade" id="update-administrator" tabindex="-1" role="dialog" aria-labelledby="update-administrator-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-administrator-label"><?php esc_html_e( 'Update Administrator', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-administrator-form" enctype="multipart/form-data">
                <div class="modal-body pr-4 pl-4" id="fetch_administrator"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary update-administrator-submit"><?php esc_html_e( 'Update Administrator', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - update administrator modal -->