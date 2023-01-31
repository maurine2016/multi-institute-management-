<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );
if ( isset( $attr['id'] ) ) {
	global $wpdb;
	$institute_id = intval( $attr['id'] );
	$institute    = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id AND is_active = 1" );
	if ( ! $institute ) {
        if ( function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
        } else {
            $screen = '';
        }
        if ( ! $screen || ! in_array( $screen->post_type, array( 'page', 'post' ) ) ) {
            die( esc_html__( "Institute is either invalid or not active. If you are owner of this institute, then please contact the administrator.", WL_MIM_DOMAIN ) );
        }
	} else {
		$wlim_institute_active_categories = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}wl_min_course_categories WHERE is_active = 1 AND institute_id = $institute_id ORDER BY name" );

		$general_enquiry           = WL_MIM_SettingHelper::get_general_enquiry_settings( $institute->id );
		$enquiry_form_title_enable = $general_enquiry['enquiry_form_title_enable'];
		$enquiry_form_title        = $general_enquiry['enquiry_form_title'];
		$custom_fields             = WL_MIM_Helper::get_active_custom_fields_institute( $institute->id );
	}
} else {
	$institute                 = null;
	$wlim_active_institutes    = WL_MIM_Helper::get_active_institutes();
	$enquiry_form_title_enable = get_option( 'multi_institute_enable_enquiry_form_title' );
	$enquiry_form_title        = get_option( 'multi_institute_enquiry_form_title' );
}
?>
<div class="wl_im_container wl_im">
    <div class="row justify-content-md-center">
        <div class="col-xs-12 col-md-12">
            <div class="card">
				<?php if ( $enquiry_form_title_enable ) { ?>
                    <div class="card-header">
                        <div class="text-center wl_im_heading_title">
							<h2><span><?php echo esc_html( $enquiry_form_title ); ?></span></h2>
						</div>
                    </div>
				<?php
				} ?>
                <div class="card-body">
                    <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-enquiry-form" enctype="multipart/form-data">
						<?php $nonce = wp_create_nonce( 'add-enquiry' ); ?>
                        <input type="hidden" name="add-enquiry" value="<?php echo esc_attr( $nonce ); ?>">
                        <input type="hidden" name="action" value="wl-mim-add-enquiry">
						<?php if ( ! $institute ) { ?>
                            <div class="form-group">
                                <label for="wlim-enquiry-institute" class="col-form-label">* <?php esc_html_e( "Select Institute", WL_MIM_DOMAIN ); ?>:</label>
                                <select name="institute" class="form-control" id="wlim-enquiry-institute">
                                    <option value="">-------- <?php esc_html_e( "Select Institute", WL_MIM_DOMAIN ); ?>--------</option>
									<?php
									if ( count( $wlim_active_institutes ) > 0 ) {
										foreach ( $wlim_active_institutes as $active_institute ) { ?>
                                            <option value="<?php echo esc_attr( $active_institute->id ); ?>"><?php echo esc_html( $active_institute->name ); ?></option>
											<?php
										}
									} ?>
                                </select>
                            </div>
                            <div id="wlim-fetch-institute-categories"></div>
						<?php } else { ?>
                            <input type="hidden" name="institute" value="<?php echo esc_attr( $institute->id ); ?>">
							<?php
							if ( count( $wlim_institute_active_categories ) > 0 ) { ?>
                                <div class="form-group">
                                    <label for="wlim-enquiry-category" class="col-form-label">* <?php esc_html_e( "Category", WL_MIM_DOMAIN ); ?>:</label>
                                    <select name="category" class="form-control" id="wlim-enquiry-category">
                                        <option value="">-------- <?php esc_html_e( "Select a Category", WL_MIM_DOMAIN ); ?>--------
                                        </option>
										<?php
										foreach ( $wlim_institute_active_categories as $active_category ) { ?>
                                            <option value="<?php echo esc_attr( $active_category->id ); ?>"><?php echo esc_html( $active_category->name ); ?></option>
											<?php
										} ?>
                                    </select>
                                </div>
                                <div id="wlim-fetch-category-courses"></div>
								<?php
								} else {
								$wlim_institute_active_courses = $wpdb->get_results( "SELECT id, course_name, fees, course_code FROM {$wpdb->prefix}wl_min_courses WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY course_name" );
								?>
                                <div class="form-group">
                                    <label for="wlim-enquiry-course" class="col-form-label">* <?php esc_html_e( "Admission For", WL_MIM_DOMAIN ); ?>:</label>
                                    <select name="course" class="form-control" id="wlim-enquiry-course">
                                        <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?>--------
                                        </option>
										<?php
										if ( count( $wlim_institute_active_courses ) > 0 ) {
											foreach ( $wlim_institute_active_courses as $active_course ) { ?>
                                                <option value="<?php echo esc_attr( $active_course->id ); ?>"><?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?></option>
												<?php
											}
										} ?>
                                    </select>
                                </div>
								<?php
								}
							}	?>
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
                                        <label class="radio-inline mr-3"><input checked type="radio" name="gender" class="mr-2" value="male" id="wlim-enquiry-male"><?php esc_html_e( 'Male', WL_MIM_DOMAIN ); ?></label>
                                        <label class="radio-inline"><input type="radio" name="gender" class="mr-2" value="female" id="wlim-enquiry-female"><?php esc_html_e( 'Female', WL_MIM_DOMAIN ); ?></label>
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
						<?php if ( ! $institute ) { ?>
                            <div id="wlim-fetch-institute-custom-fields"></div>
						<?php } else { if ( count( $custom_fields ) ) { ?>
                            <div class="row">
								<?php foreach ( $custom_fields as $key => $custom_field ) { ?>
                                    <div class="col-sm-6 form-group">
                                        <label for="wlim-enquiry-custom_fields_<?php echo esc_attr( $key ); ?>" class="col-form-label"><?php echo esc_html( $custom_field->field_name ); ?>:</label>
                                        <input type="hidden" name="custom_fields[name][]" value="<?php echo esc_attr( $custom_field->field_name ); ?>">
                                        <input name="custom_fields[value][]" type="text" class="form-control" id="wlim-enquiry-custom_fields_<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $custom_field->field_name ); ?>">
                                    </div>
								<?php } ?>
                            </div>
						<?php }
						} ?>
                        <div class="form-group">
                            <label for="wlim-enquiry-message" class="col-form-label"><?php esc_html_e( 'Message', WL_MIM_DOMAIN ); ?>:</label>
                            <textarea name="message" class="form-control" rows="3" id="wlim-enquiry-message" placeholder="<?php esc_html_e( "Message", WL_MIM_DOMAIN ); ?>"></textarea>
                        </div>
                            
                        

                        <div class="mt-3">
                            <button type="submit" class="btn btn-block add-enquiry-submit"><?php esc_html_e( 'Submit!', WL_MIM_DOMAIN ); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>