<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php' );

$institute_id                     = WL_MIM_Helper::get_current_institute_id();
$wlim_institute_active_categories = WL_MIM_Helper::get_active_categories_institute( $institute_id );

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

if ( empty( $general_institute['institute_name'] ) ) {
	$institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
	$institute_name = $general_institute['institute_name'];
}

if ( isset( $_GET['follow_up'] ) && ! empty( $_GET['follow_up'] ) ) {
    $follow_up = new DateTime( sanitize_text_field( $_GET['follow_up'] ) );
    if ( $follow_up ) {
        $follow_up = $follow_up->format('Y-m-d');
    }
} else {
    $follow_up = '';
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
                <span class="border-bottom"><i class="fa fa-envelope"></i> <?php esc_html_e( 'Enquiries', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can either add a new enquiry or edit existing enquiries.', WL_MIM_DOMAIN ); ?>
            </div>
            <div class="text-center">
				<?php esc_html_e( 'To Display Enquiry Form on Front End, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                <div class="col-12 justify-content-center align-items-center">
					<span class="col-6">
 						<strong id="wl_im_enquiry_form_shortcode">[institute_admission_enquiry_form id=<?php echo esc_html( $institute_id ); ?>]</strong>
					</span>
                    <span class="col-6">
						<button id="wl_im_enquiry_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
					</span>
                </div>
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
                        <div class="h4">
                            <?php esc_html_e( 'Manage Enquiries', WL_MIM_DOMAIN ); ?>
                            <?php if ( $follow_up ) { ?>
                            <small><?php esc_html_e( 'Follow Up:', WL_MIM_DOMAIN ); ?>
                                <span class="font-weight-bold"><?php echo esc_attr( $follow_up ); ?></span>
                                <a class="text-white" href="<?php menu_page_url( 'multi-institute-management-enquiries' ); ?>"><?php esc_html_e( 'View All', WL_MIM_DOMAIN ); ?></a>
                            </small>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-enquiry" data-toggle="modal" data-target="#add-enquiry" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Enquiry', WL_MIM_DOMAIN ); ?>
                        </button>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="enquiry-table" data-follow-up="<?php echo esc_attr( $follow_up ); ?>">
                        <?php 
                        $entity = 'inquiry';
                        require( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php' ); ?>
                            <thead>
                                <tr>
                                <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e( 'Enquiry ID', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Reference', WL_MIM_DOMAIN ); ?></th>
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
    <!-- end - row 2 -->
</div>

<!-- add new enquiry modal -->
<div class="modal fade" id="add-enquiry" tabindex="-1" role="dialog" aria-labelledby="add-enquiry-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-enquiry-label"><?php esc_html_e( 'Add New Enquiry', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-enquiry-form" enctype="multipart/form-data">
                <div class="modal-body pr-4 pl-4">
					<?php $nonce = wp_create_nonce( 'add-enquiry' ); ?>
                    <input type="hidden" name="add-enquiry" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="hidden" name="action" value="wl-mim-add-enquiry">
                    <div class="wlim-add-enquiry-form-fields">
						<?php
						if ( count( $wlim_institute_active_categories ) > 0 ) { ?>
                            <div class="form-group">
                                <label for="wlim-enquiry-category" class="col-form-label">* <?php esc_html_e( "Category", WL_MIM_DOMAIN ); ?>:</label>
                                <select name="category" class="form-control" id="wlim-enquiry-category">
                                    <option value="">-------- <?php esc_html_e( "Select a Category", WL_MIM_DOMAIN ); ?> --------</option>
									<?php
									foreach ( $wlim_institute_active_categories as $active_category ) { ?>
                                        <option value="<?php echo esc_attr( $active_category->id ); ?>"><?php echo esc_html( $active_category->name ); ?></option>
									<?php
									} ?>
                                </select>
                            </div>
                            <div id="wlim-fetch-category-courses"></div>
						<?php } else {
							$wlim_active_courses = WL_MIM_Helper::get_active_courses(); ?>
                            <div class="form-group wlim-selectpicker">
                                <label for="wlim-enquiry-course" class="col-form-label">* <?php esc_html_e( "Admission For", WL_MIM_DOMAIN ); ?>:</label>
                                <select name="course" class="form-control selectpicker" id="wlim-enquiry-course">
                                    <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?>--------
                                    </option>
									<?php
									if ( count( $wlim_active_courses ) > 0 ) {
										foreach ( $wlim_active_courses as $active_course ) { ?>
                                            <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?></option>
										<?php
										}
									} ?>
                                </select>
                            </div>
						<?php
						} ?>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-first_name" class="col-form-label">* <?php esc_html_e( 'First Name', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="first_name" type="text" class="form-control" id="wlim-enquiry-first_name" placeholder="<?php esc_html_e( "First Name", WL_MIM_DOMAIN ); ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-last_name" class="col-form-label"><?php esc_html_e( 'Last Name', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="last_name" type="text" class="form-control" id="wlim-enquiry-last_name" placeholder="<?php esc_html_e( "Last Name", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label class="col-form-label">* <?php esc_html_e( 'Gender', WL_MIM_DOMAIN ); ?>:</label><br>
                                <div class="row mt-2">
                                    <div class="col-sm-12">
                                        <label class="radio-inline mr-3"><input checked type="radio" name="gender" class="mr-2" value="male" id="wlim-enquiry-male"><?php esc_html_e( 'Male', WL_MIM_DOMAIN ); ?>
                                        </label>
                                        <label class="radio-inline"><input type="radio" name="gender" class="mr-2" value="female" id="wlim-enquiry-female"><?php esc_html_e( 'Female', WL_MIM_DOMAIN ); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-date_of_birth" class="col-form-label">* <?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="date_of_birth" type="text" class="form-control wlim-date_of_birth" id="wlim-enquiry-date_of_birth" placeholder="<?php esc_html_e( "Date of Birth", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-father_name" class="col-form-label"><?php esc_html_e( "Father's Name", WL_MIM_DOMAIN ); ?>:</label>
                                <input name="father_name" type="text" class="form-control" id="wlim-enquiry-father_name" placeholder="<?php esc_html_e( "Father's Name", WL_MIM_DOMAIN ); ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-mother_name" class="col-form-label"><?php esc_html_e( "Mother's Name", WL_MIM_DOMAIN ); ?>:</label>
                                <input name="mother_name" type="text" class="form-control" id="wlim-enquiry-mother_name" placeholder="<?php esc_html_e( "Mother's Name", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-address" class="col-form-label"><?php esc_html_e( 'Address', WL_MIM_DOMAIN ); ?>:</label>
                                <textarea name="address" class="form-control" rows="4" id="wlim-enquiry-address" placeholder="<?php esc_html_e( "Address", WL_MIM_DOMAIN ); ?>"></textarea>
                            </div>
                            <div class="col-sm-6 form-group">
                                <div>
                                    <label for="wlim-enquiry-city" class="col-form-label"><?php esc_html_e( 'City', WL_MIM_DOMAIN ); ?>:</label>
                                    <input name="city" type="text" class="form-control" id="wlim-enquiry-city" placeholder="<?php esc_html_e( "City", WL_MIM_DOMAIN ); ?>">
                                </div>
                                <div>
                                    <label for="wlim-enquiry-zip" class="col-form-label"><?php esc_html_e( 'Zip Code', WL_MIM_DOMAIN ); ?>:</label>
                                    <input name="zip" type="text" class="form-control" id="wlim-enquiry-zip" placeholder="<?php esc_html_e( "Zip Code", WL_MIM_DOMAIN ); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-state" class="col-form-label"><?php esc_html_e( 'State', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="state" type="text" class="form-control" id="wlim-enquiry-state" placeholder="<?php esc_html_e( "State", WL_MIM_DOMAIN ); ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-nationality" class="col-form-label"><?php esc_html_e( 'Nationality', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="nationality" type="text" class="form-control" id="wlim-enquiry-nationality" placeholder="<?php esc_html_e( "Nationality", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-phone" class="col-form-label">* <?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="phone" type="text" class="form-control" id="wlim-enquiry-phone" placeholder="<?php esc_html_e( "Phone", WL_MIM_DOMAIN ); ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-email" class="col-form-label"><?php esc_html_e( 'Email', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="email" type="text" class="form-control" id="wlim-enquiry-email" placeholder="<?php esc_html_e( "Email", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-qualification" class="col-form-label"><?php esc_html_e( 'Qualification', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="qualification" type="text" class="form-control" id="wlim-enquiry-qualification" placeholder="<?php esc_html_e( "Qualification", WL_MIM_DOMAIN ); ?>">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-id_proof" class="col-form-label"><?php esc_html_e( 'ID Proof', WL_MIM_DOMAIN ); ?>:</label><br>
                                <input name="id_proof" type="file" id="wlim-enquiry-id_proof">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-photo" class="col-form-label"><?php esc_html_e( 'Choose Photo', WL_MIM_DOMAIN ); ?>:</label><br>
                                <input name="photo" type="file" id="wlim-enquiry-photo">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="wlim-enquiry-signature" class="col-form-label"><?php esc_html_e( 'Choose Signature', WL_MIM_DOMAIN ); ?>:</label><br>
                                <input name="signature" type="file" id="wlim-enquiry-signature">
                            </div>
                        </div>
						<?php
						$custom_fields = WL_MIM_Helper::get_active_custom_fields_institute( $institute_id );
						if ( count( $custom_fields ) ) { ?>
                            <div class="row">
								<?php
								foreach ( $custom_fields as $key => $custom_field ) { ?>
                                    <div class="col-sm-6 form-group">
                                        <label for="wlim-enquiry-custom_fields_<?php echo esc_attr( $key ); ?>" class="col-form-label"><?php echo esc_attr( $custom_field->field_name ); ?>:</label>
                                        <input type="hidden" name="custom_fields[name][]" value="<?php echo esc_attr( $custom_field->field_name ); ?>">
                                        <input name="custom_fields[value][]" type="text" class="form-control" id="wlim-enquiry-custom_fields_<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $custom_field->field_name ); ?>">
                                    </div>
								<?php
								} ?>
                            </div>
						<?php
						} ?>
                        <div class="form-group">
                            <label for="wlim-enquiry-message" class="col-form-label"><?php esc_html_e( 'Message', WL_MIM_DOMAIN ); ?>:</label>
                            <textarea name="message" class="form-control" rows="3" id="wlim-enquiry-message" placeholder="<?php esc_html_e( "Message", WL_MIM_DOMAIN ); ?>"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                 <div class="form-group">
                                   <label for="wlim-enquiry-follow_up_date" class="col-form-label"><?php esc_html_e( 'Follow Up Date', WL_MIM_DOMAIN ); ?>:</label>
                                        <input name="follow_up_date" type="text" class="form-control wlim-follow_up_date" id="wlim-enquiry-follow_up_date" placeholder="<?php esc_html_e( "Follow Up Date", WL_MIM_DOMAIN ); ?>">
                                
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="wlim-enquiry-reference" class="col-form-label"><?php esc_html_e( "Reference", WL_MIM_DOMAIN ); ?>:</label>
                                    <input name="reference" type="text" class="form-control" id="wlim-enquiry-reference" placeholder="<?php esc_html_e( "Reference", WL_MIM_DOMAIN ); ?>">
                                </div>
                            </div>
                        </div>

                         <div class="form-group">
                            <label for="wlim-enquiry-note" class="col-form-label"><?php esc_html_e( 'Note', WL_MIM_DOMAIN ); ?>:</label>
                            <textarea name="note" class="form-control" rows="3" id="wlim-enquiry-note" placeholder="<?php esc_html_e( "Note", WL_MIM_DOMAIN ); ?>"></textarea>
                        </div>

                        <div class="form-check pl-0">
                            <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-enquiry-is_active" checked>
                            <label class="form-check-label" for="wlim-enquiry-is_active">
								<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary add-enquiry-submit"><?php esc_html_e( 'Add New Enquiry', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - add new enquiry modal -->

<!-- update enquiry modal -->
<div class="modal fade" id="update-enquiry" tabindex="-1" role="dialog" aria-labelledby="update-enquiry-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-enquiry-label"><?php esc_html_e( 'Update Enquiry', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-enquiry-form" enctype="multipart/form-data">
                <div class="modal-body pr-4 pl-4" id="fetch_enquiry"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary update-enquiry-submit"><?php esc_html_e( 'Update Enquiry', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - update enquiry modal -->