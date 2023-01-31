<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php' );

$data_of_birth_required = false;

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
		$wlim_institute_exams = $wpdb->get_results( "SELECT id, exam_title, exam_code FROM {$wpdb->prefix}wl_min_exams WHERE is_deleted = 0 AND is_published = 1 AND institute_id = $institute_id ORDER BY id DESC" );
	}

    $data_of_birth_required = WL_MIM_SettingHelper::get_admit_card_dob_enable_settings( $institute_id );

} else {
	$institute	=	null;
	$wlim_active_institutes	= WL_MIM_Helper::get_active_institutes();
}
?>
<div class="wl_im_container wl_im">
    <div class="row justify-content-md-center">
        <div class="col-xs-12 col-md-12">
            <div id="wlim-get-admit-card"></div>
            <form id="wlim-admit-card-form">
				<?php if ( ! $institute ) { ?>
                    <div class="form-group">
                        <label for="wlim-result-institute" class="col-form-label">
							*<strong><?php esc_html_e( "Select Institute", WL_MIM_DOMAIN ); ?>:</strong>
						</label>
                        <select name="institute" class="form-control" id="wlim-result-institute" data-dob="1">
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
                    <div id="wlim-fetch-institute-exams"></div>
				<?php } else { ?>
                    <input type="hidden" name="institute" value="<?php echo esc_attr( $institute->id ); ?>">
                    <div class="form-group wlim-selectpicker">
                        <label for="wlim-admit-card-exam" class="col-form-label">
							*<strong><?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?></strong>
						</label>
                        <select name="exam" class="form-control" id="wlim-admit-card-exam">
                            <option value="">-------- <?php esc_html_e( "Select an Exam", WL_MIM_DOMAIN ); ?> --------</option>                           
							<?php
							if ( count( $wlim_institute_exams ) > 0 ) {
								foreach ( $wlim_institute_exams as $exam ) { ?>
                                    <option value="<?php echo esc_attr( $exam->id ); ?>"><?php echo esc_html( "$exam->exam_title ( $exam->exam_code )" ); ?></option>
									<?php
								}
							} ?>
                        </select>
                    </div>
				<?php } ?>
                <div class="form-group">
                    <label for="wlim-admit-card-enrollment_id" class="col-form-label">
						*<strong><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?>:</strong>
					</label>
                    <input name="enrollment_id" type="text" class="form-control" id="wlim-admit-card-enrollment_id" placeholder="<?php esc_html_e( "Enrollment ID", WL_MIM_DOMAIN ); ?>">
                </div>
                <?php if ( $data_of_birth_required ) { ?>
                <div class="form-group">
                    <label for="wlim-admit-card-date_of_birth" class="col-form-label">
                        *<strong><?php esc_html_e( 'Date of Birth', WL_MIM_DOMAIN ); ?>:</strong>
                    </label>
                    <input name="date_of_birth" type="text" class="form-control wlim-date_of_birth" id="wlim-admit-card-date_of_birth" placeholder="<?php esc_html_e( "Date of Birth", WL_MIM_DOMAIN ); ?>">
                </div>
                <?php } ?>
                <div class="mt-3 float-right">
                    <button type="submit" class="btn btn-primary view-admit-card-submit"><?php esc_html_e( 'Get Admit Card!', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
