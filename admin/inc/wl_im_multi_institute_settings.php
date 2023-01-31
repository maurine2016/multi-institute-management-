<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_PaymentHelper.php' );
?>
<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="display-4 text-center">
                <span class="border-bottom"><i class="fa fa-cog"></i> <?php esc_html_e( 'Settings', WL_MIM_DOMAIN ); ?></span>
            </h1>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can view and modify multi-institute settings.', WL_MIM_DOMAIN ); ?>
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
                        <div class="h4"><?php esc_html_e( 'Settings', WL_MIM_DOMAIN ); ?></div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <form action="options.php" method="post" enctype="multipart/form-data">
                    <div class="row">
						<?php
						settings_fields( 'wl_min_settings_group' );
						?>
                        <div class="col-xs-12 col-md-6">
                            <div class="card-header bg-primary">
                                <h5 class="card-title text-white"><?php esc_html_e( 'General Settings', WL_MIM_DOMAIN ); ?></h5>
                            </div>
                            <div class="card-body border">
                                <div class="form-check pl-0 mb-3">
                                    <input name="multi_institute_enable_enquiry_form_title" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-multi_institute_enable_enquiry_form_title" value="yes" <?php checked( get_option( 'multi_institute_enable_enquiry_form_title', 'yes' ), 'yes' ); ?>>
                                    <label for="wlim-setting-multi_institute_enable_enquiry_form_title" class="form-check-label">
										<?php esc_html_e( 'Enable Enquiry Form Title', WL_MIM_DOMAIN ); ?>
                                    </label>
                                </div>
                                <div class="form-check pl-0 mb-3">
                                    <input name="multi_institute_enable_seprate_enrollment_id" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-multi_institute_enable_seprate_enrollment_id" value="1" <?php checked( get_option( 'multi_institute_enable_seprate_enrollment_id', '1' ), '1' ); ?>>
                                    <label for="wlim-setting-multi_institute_enable_seprate_enrollment_id" class="form-check-label">
										<?php esc_html_e( 'Seperate Enrollment ID (do not this change setting if you already have students. )', WL_MIM_DOMAIN ); ?>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-multi_institute_enquiry_form_title" class="col-form-label">
										<?php esc_html_e( 'Enquiry Form Title', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="multi_institute_enquiry_form_title" type="text" class="form-control" id="wlim-setting-multi_institute_enquiry_form_title" placeholder="<?php esc_html_e( "Enquiry Form Title", WL_MIM_DOMAIN ); ?>" value="<?php echo get_option( 'multi_institute_enquiry_form_title', 'Admission Enquiry' ); ?>">
                                </div>
                                <div class="mt-2 pt-2 mb-2 pb-2 border-top">
									<?php esc_html_e( 'To Display Enquiry Form for all Institutes, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                    <div class="justify-content-center align-items-center">
                                        <span class="col-6">
                                            <strong id="wl_im_enquiry_form_shortcode">[institute_admission_enquiry_form]</strong>
                                        </span>
                                        <span class="col-6">
                                            <button id="wl_im_enquiry_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 mb-2 pb-2 border-top border-bottom">
									<?php esc_html_e( 'To Display Exam Results Form for all Institutes, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                    <span class="col-6">
                                        <br>
                                        <strong id="wl_im_exam_result_form_shortcode">[institute_exam_result]</strong>
                                    </span>
                                    <span class="col-6">
                                        <button id="wl_im_exam_result_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                    </span>
                                </div>
                                <div class="mt-2 pt-2 mb-2 pb-2 border-top border-bottom">
                                    <?php esc_html_e( 'To Display Exam Results By Name Form for all Institutes, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                    <span class="col-6">
                                        <br>
                                        <strong id="wl_im_exam_results_by_name_form_shortcode">[institute_exam_results_by_name]</strong>
                                    </span>
                                    <span class="col-6">
                                        <button id="wl_im_exam_results_by_name_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                    </span>
                                </div>
                                <div class="mt-2 pt-2 mb-2 pb-2 border-top border-bottom">
                                    <?php esc_html_e( 'To Display Admit Card Form for all Institutes, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                    <span class="col-6">
                                        <br>
                                        <strong id="wl_im_admit_card_form_shortcode">[institute_admit_card]</strong>
                                    </span>
                                    <span class="col-6">
                                        <button id="wl_im_admit_card_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                    </span>
                                </div>
                                <div class="mt-2 pt-2 mb-2 pb-2 border-top border-bottom">
                                    <?php esc_html_e( 'To Display ID Card Form for all Institutes, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                    <span class="col-6">
                                        <br>
                                        <strong id="wl_im_id_card_form_shortcode">[institute_id_card]</strong>
                                    </span>
                                    <span class="col-6">
                                        <button id="wl_im_id_card_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                    </span>
                                </div>
                                <div class="mt-2 pt-2 mb-2 pb-2 border-top border-bottom">
                                    <?php esc_html_e( 'To Display Certificate Form for all Institutes, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                                    <span class="col-6">
                                        <br>
                                        <strong id="wl_im_certificate_form_shortcode">[institute_certificate]</strong>
                                    </span>
                                    <span class="col-6">
                                        <button id="wl_im_certificate_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <div class="card-header bg-primary">
                                <h5 class="card-title text-white"><?php esc_html_e( 'University Settings', WL_MIM_DOMAIN ); ?></h5>
                            </div>
                            <div class="card-body border">
                                <div class="form-check pl-0 mb-3">
                                    <input name="multi_institute_enable_university_header" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-setting-multi_institute_enable_university_header" value="yes" <?php checked( get_option( 'multi_institute_enable_university_header' ), 'yes' ); ?>>
                                    <label for="wlim-setting-multi_institute_enable_university_header" class="form-check-label">
                                        <?php esc_html_e( 'Enable University Header in Marksheet & Admit Card', WL_MIM_DOMAIN ); ?>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-multi_institute_university_logo" class="col-form-label">
                                        <?php esc_html_e( 'University Logo', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <?php
                                    if( ! empty ( $logo_url = get_option( 'multi_institute_university_logo' ) ) ) { ?>
                                        <img src="<?php echo esc_url( $logo_url ); ?>" id="wlim-multi_institute_university_logo" class="img-responsive">
                                    <?php
                                    } ?>
                                    <input name="multi_institute_university_logo" type="file" class="form-control" id="wlim-setting-multi_institute_university_logo" placeholder="<?php esc_html_e( "Institute Logo", WL_MIM_DOMAIN ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-multi_institute_university_name" class="col-form-label">
                                        <?php esc_html_e( 'University Name', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="multi_institute_university_name" type="text" class="form-control" id="wlim-setting-multi_institute_university_name" placeholder="<?php esc_html_e( "University Name", WL_MIM_DOMAIN ); ?>" value="<?php echo get_option( 'multi_institute_university_name' ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-multi_institute_university_address" class="col-form-label">
                                        <?php esc_html_e( 'University Address', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <textarea name="multi_institute_university_address" class="form-control" id="wlim-setting-multi_institute_university_address" cols="30" rows="3" placeholder="<?php esc_html_e( "University Address", WL_MIM_DOMAIN ); ?>"><?php echo esc_html( get_option( 'multi_institute_university_address' ) ); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-multi_institute_university_phone" class="col-form-label">
                                        <?php esc_html_e( 'University Phone', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="multi_institute_university_phone" type="text" class="form-control" id="wlim-setting-multi_institute_university_phone" placeholder="<?php esc_html_e( "University Phone", WL_MIM_DOMAIN ); ?>" value="<?php echo get_option( 'multi_institute_university_phone' ); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="wlim-setting-multi_institute_university_email" class="col-form-label">
                                        <?php esc_html_e( 'University Name', WL_MIM_DOMAIN ); ?>:
                                    </label>
                                    <input name="multi_institute_university_email" type="email" class="form-control" id="wlim-setting-multi_institute_university_email" placeholder="<?php esc_html_e( "University Email", WL_MIM_DOMAIN ); ?>" value="<?php echo get_option( 'multi_institute_university_email' ); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <div class="text-right">
                                <button type="submit" name="submit" class="btn btn-primary"><?php esc_html_e( 'Save', WL_MIM_DOMAIN ); ?></button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
    <!-- end - row 2 -->
</div>