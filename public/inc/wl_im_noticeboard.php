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
	}

    $data_of_birth_required = WL_MIM_SettingHelper::get_certificate_dob_enable_settings( $institute_id );

} else {
	$institute = null;
	$wlim_active_institutes	= WL_MIM_Helper::get_active_institutes();
}

$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_notices WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY priority ASC, id DESC " );
?>
<div class="wl_im_container wl_im">
    <div class="row justify-content-md-center">
        <div class="col-xs-12 col-md-12">
            <div class="wlim-noticeboard-section">
                <ul class="wlim-noticeboard list-group">
                    <?php
                    foreach ( $data as $key => $row ) {
                        if ( $row->link_to == 'url' ) {
                            $link_to = $row->url;
                        } elseif ( $row->link_to == 'attachment' ) {
                            $link_to = wp_get_attachment_url( $row->attachment );
                        } else {
                            $link_to = '#';
                        }
                        ?>
                        <li class="list-group-item">
                            <a target="_blank" href="<?php echo esc_url( $link_to ); ?>"><?php echo stripcslashes( $row->title ); ?></a>
                            <?php
                            if ( $key < 3 ) { ?>
                                <img class="wlim-noticeboard-new" src="<?php echo WL_MIM_PLUGIN_URL . 'assets/images/newicon.gif'; ?>">
                                <?php
                            } ?>
                        </li>
                        <?php
                    } ?>
                </ul>
            </div>
        </div>
    </div>
</div>
