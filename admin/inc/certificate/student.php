<?php
defined('ABSPATH') || die();

$certificate_student_id = absint($_GET['certificate_student_id']);

$page_url = WL_MIM_Helper::get_certificates_page_url();

$institute_id = WL_MIM_Helper::get_current_institute_id();

$certificate_student = WL_MIM_Helper::fetch_certificate_view($institute_id, $certificate_student_id);
// var_dump($certificate_student);
// die;

if (!$certificate_student) {
	die;
}
$user_id = $certificate_student->student_record_id;

require_once WL_MIM_PLUGIN_DIR_PATH . "admin/inc/helpers/certificate_student.php";
// $student = WLSM_M_Staff_General::fetch_student( $school_id, $session_id, $student_id );
$student = WL_MIM_StudentHelper::fetch_student($institute_id, $user_id);

?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					printf(
						wp_kses(
							/* translators: %s: certificate title */
							__('Student Certificate: %s', WL_MIM_DOMAIN),
							array(
								'span' => array('class' => array())
							)
						),
						esc_html($certificate_student->label)
					);
					?>
				</span>
			</span>
			<span class="float-md-right">
				<a href="<?php echo esc_url($page_url . "&action=distribute&id=" . $certificate_student->id); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-certificate"></i>&nbsp;
					<?php esc_html_e('Distribute Certificate', WL_MIM_DOMAIN); ?>
				</a>
				<a href="<?php echo esc_url($page_url . "&action=students&id=" . $certificate_student->id); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-certificate"></i>&nbsp;
					<?php esc_html_e('Total Certificates Distributed', WL_MIM_DOMAIN); ?>
				</a>
			</span>
		</div>
		<?php require_once WL_MIM_PLUGIN_DIR_PATH . "admin/inc/helpers/certificate.php"; ?>
	</div>
</div>