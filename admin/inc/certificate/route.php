<?php
defined( 'ABSPATH' ) || die();
// require_once WL_MIM_PLUGIN_DIR_PATH  .'';
 $action = '';
if ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) {
	$action = sanitize_text_field( $_GET['action'] );
}
?>

<div class="wlsm container-fluid">
	<?php
	if ( in_array( $action, array( 'save' ) ) ) {
		$disallow_session_change = true;
	}

	require_once WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/certificate/header.php';

	if ( 'save' === $action ) {
		require_once WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/certificate/save.php';
	} elseif ( 'students' === $action ) {
		if ( isset( $_GET['certificate_student_id'] ) && ! empty( $_GET['certificate_student_id'] ) ) {
			require WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/certificate/student.php';
			
		} else {
			require WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/certificate/students.php';
		}
	} elseif ( 'distribute' === $action ) {
		require_once WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/certificate/distribute.php';
	} else {
		require_once WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/certificate/index.php';
	}
	?>
</div>
