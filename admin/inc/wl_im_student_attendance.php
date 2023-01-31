<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
?>
<div class="wl_im_container wl_im">
    <div class="row justify-content-md-center mx-auto">
        <div class="card col-xs-12 col-md-8 col-sm-10">
            <div id="wlim-get-student-attendance"></div>
            <div class="card-header bg-primary">
                <h4 class="text-white"><?php esc_html_e( "Get your Attendance", WL_MIM_DOMAIN ); ?></h4>
            </div>
            <div class="card-body">
                <form id="wlim-student-attendance-form">
                    <div id="wlim-duration-period">
                        <hr>
                        <div class="form-check pl-0">
                            <input name="custom_duration" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-custom-duration-checkbox">
                            <label class="form-check-label" for="wlim-custom-duration-checkbox">
                                <strong class="text-dark"><?php esc_html_e( 'Custom Duration?', WL_MIM_DOMAIN ); ?></strong>
                            </label>
                        </div>
                        <hr>
                        <div class="form-group wlim-predefined-period">
                            <label for="wlim-predefined-period" class="col-form-label"><?php esc_html_e( "Select Period", WL_MIM_DOMAIN ); ?>
                                :</label>
                            <select name="predefined_period" class="form-control selectpicker" id="wlim-predefined-period">
                                <option value="">-------- <?php esc_html_e( "Select Period", WL_MIM_DOMAIN ); ?> --------</option>
                                <?php
                                foreach ( WL_MIM_Helper::get_report_period() as $key => $value ) { ?>
                                    <option value="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( $value, WL_MIM_DOMAIN ); ?></option>
                                <?php
                                } ?>
                            </select>
                        </div>
                        <div class="row wlim-custom-duration">
                            <div class="form-group col-6">
                                <label for="wlim-custom-duration-start" class="col-form-label"><?php esc_html_e( 'From', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="duration_from" type="text" class="form-control wlim-custom-duration-field" id="wlim-custom-duration-start" placeholder="<?php esc_html_e( "From", WL_MIM_DOMAIN ); ?>">
                            </div>
                            <div class="form-group col-6">
                                <label for="wlim-custom-duration-to" class="col-form-label"><?php esc_html_e( 'To', WL_MIM_DOMAIN ); ?>:</label>
                                <input name="duration_to" type="text" class="form-control wlim-custom-duration-field" id="wlim-custom-duration-to" placeholder="<?php esc_html_e( "To", WL_MIM_DOMAIN ); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 float-right">
                        <button type="submit" class="btn btn-primary view-student-attendance-submit"><?php esc_html_e( 'Get Attendance!', WL_MIM_DOMAIN ); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
