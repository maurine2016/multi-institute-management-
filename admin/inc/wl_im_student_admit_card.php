<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

$wlim_exams = WL_MIM_Helper::get_published_exams();
?>
<div class="wl_im_container wl_im">
    <div class="row justify-content-md-center mx-auto">
        <div class="card col-xs-12 col-md-8 col-sm-10">
            <div id="wlim-get-student-admit-card"></div>
            <div class="card-header bg-primary">
                <h4 class="text-white"><?php esc_html_e( "Get your Admit Card", WL_MIM_DOMAIN ); ?></h4>
            </div>
            <div class="card-body">
                <form id="wlim-student-admit-card-form">
                    <div class="form-group wlim-selectpicker">
                        <label for="wlim-admit-card-exam" class="col-form-label">*
                            <strong><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?>:</strong>
                        </label>
                        <select name="exam" class="form-control" id="wlim-admit-card-exam">
                            <option value="">-------- <?php esc_html_e( "Select an Exam", WL_MIM_DOMAIN ); ?> --------</option>
							<?php
							if ( count( $wlim_exams ) > 0 ) {
								foreach ( $wlim_exams as $exam ) { ?>
                                    <option value="<?php echo esc_attr( $exam->id ); ?>"><?php echo esc_html( "$exam->exam_title ( $exam->exam_code )" ); ?></option>
									<?php
								}
							} ?>
                        </select>
                    </div>
                    <div class="mt-3 float-right">
                        <button type="submit" class="btn btn-primary view-student-admit-card-submit"><?php esc_html_e( 'Get Admit Card!', WL_MIM_DOMAIN ); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>