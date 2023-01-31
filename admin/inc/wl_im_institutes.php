<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

$wlim_institutes = WL_MIM_Helper::get_institutes();
$institute_id    = WL_MIM_Helper::get_current_institute_id();

$courses = WL_MIM_Helper::get_main_courses();
?>

<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="display-4 text-center">
                <span class="border-bottom"><i class="fa fa-graduation-cap"></i> <?php esc_html_e( 'Institutes', WL_MIM_DOMAIN ); ?></span>
            </h1>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can either add a new institute or edit existing institutes.', WL_MIM_DOMAIN ); ?>
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
                            <option <?php selected( $institute_id, $institute->id, true ); ?> value="<?php echo esc_attr( $institute->id ); ?>"><?php echo esc_html( $institute->name ); ?></option>
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
                        <div class="h4"><?php esc_html_e( 'Manage Institutes', WL_MIM_DOMAIN ); ?></div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-institute" data-toggle="modal" data-target="#add-institute" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Institute', WL_MIM_DOMAIN ); ?>
                        </button>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="institute-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php esc_html_e( 'Institute Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Registration Number', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Address', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Contact Person', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Admission Enquiry Shortcode', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Exam Results Shortcode', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Extra Details', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new institute modal -->
<div class="modal fade" id="add-institute" tabindex="-1" role="dialog" aria-labelledby="add-institute-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-institute-label"><?php esc_html_e( 'Add New Institute', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-institute-form">
                <div class="modal-body pr-4 pl-4">
					<?php $nonce = wp_create_nonce( 'add-institute' ); ?>
                    <input type="hidden" name="add-institute" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="hidden" name="action" value="wl-mim-add-institute">
                    <div class="row">
                        <div class="col-12 col-md-8 form-group">
                            <label for="wlim-institute-name" class="col-form-label"><?php esc_html_e( 'Institute Name', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="name" type="text" class="form-control" id="wlim-institute-name" placeholder="<?php esc_html_e( "Institute Name", WL_MIM_DOMAIN ); ?>">
                        </div>
                        <div class="col-12 col-md-4 form-group">
                            <label for="wlim-institute-registration_number" class="col-form-label"><?php esc_html_e( 'Registration Number', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="registration_number" type="text" class="form-control" id="wlim-institute-registration_number" placeholder="<?php esc_html_e( "Registration Number", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="wlim-institute-address" class="col-form-label"><?php esc_html_e( 'Address', WL_MIM_DOMAIN ); ?>:</label>
                        <textarea name="address" class="form-control" rows="3" id="wlim-institute-address" placeholder="<?php esc_html_e( "Address", WL_MIM_DOMAIN ); ?>"></textarea>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-institute-phone" class="col-form-label"><?php esc_html_e( 'Phone Number', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="phone" type="text" class="form-control" id="wlim-institute-phone" placeholder="<?php esc_html_e( "Phone Number", WL_MIM_DOMAIN ); ?>">
                        </div>
                        <div class="col form-group">
                            <label for="wlim-institute-email" class="col-form-label"><?php esc_html_e( 'Email Address', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="email" type="email" class="form-control" id="wlim-institute-email" placeholder="<?php esc_html_e( "Email Address", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="wlim-institute-contact_person" class="col-form-label"><?php esc_html_e( 'Contact Person', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="contact_person" type="text" class="form-control" id="wlim-institute-contact_person" placeholder="<?php esc_html_e( "Contact Person", WL_MIM_DOMAIN ); ?>">
                    </div>
                    <div class="form-group">
                        <label for="wlim-institute-extra_details" class="col-form-label"><?php esc_html_e( 'Extra Details', WL_MIM_DOMAIN ); ?>:</label>
                        <textarea name="extra_details" class="form-control" rows="3" id="wlim-institute-extra_details" placeholder="<?php esc_html_e( "Extra Details", WL_MIM_DOMAIN ); ?>"></textarea>
                    </div>
                    <div class="form-check pl-0">
                        <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-institute-is_active" checked>
                        <label class="form-check-label" for="wlim-institute-is_active">
							<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                    <hr>
                    <h5><?php esc_html_e( 'Assign Courses', WL_MIM_DOMAIN ); ?></h4>
                    <div class="form-group">
                        <select multiple name="course[]" class="selectpicker form-control" id="wlim-institute-course" data-none-selected-text="-------- <?php esc_html_e( "Select Courses", WL_MIM_DOMAIN ); ?> --------" data-live-search="true" data-actions-box="true">
                            <?php
                            if ( count( $courses ) > 0 ) {
                                foreach ( $courses as $course ) { ?>
                                    <option value="<?php echo esc_attr( $course->id ); ?>"><?php echo esc_html( "$course->course_name ($course->course_code)" ); ?></option>
                                <?php
                                }
                            } ?>
                        </select>
                    </div>
                    <hr>
                    <h5><?php esc_html_e( 'Course Restriction', WL_MIM_DOMAIN ); ?></h4>
                    <div class="form-check pl-0">
                        <label class="form-check-label" for="wlim-institute-can_add_course">
                            <input name="can_add_course" type="checkbox" id="wlim-institute-can_add_course" checked>
                            <?php esc_html_e( 'Institute can add course?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                    <div class="form-check pl-0">
                        <label class="form-check-label" for="wlim-institute-can_update_course">
                            <input name="can_update_course" type="checkbox" id="wlim-institute-can_update_course" checked>
                            <?php esc_html_e( 'Institute can update course?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                    <div class="form-check pl-0">
                        <label class="form-check-label" for="wlim-institute-can_delete_course">
                            <input name="can_delete_course" type="checkbox" id="wlim-institute-can_delete_course" checked>
                            <?php esc_html_e( 'Institute can delete course?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary add-institute-submit"><?php esc_html_e( 'Add New Institute', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - add new institute modal -->

<!-- update institute modal -->
<div class="modal fade" id="update-institute" tabindex="-1" role="dialog" aria-labelledby="update-institute-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-institute-label"><?php esc_html_e( 'Update Institute', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-institute-form">
                <div class="modal-body pr-4 pl-4" id="fetch_institute"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary update-institute-submit"><?php esc_html_e( 'Update Institute', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - update institute modal -->