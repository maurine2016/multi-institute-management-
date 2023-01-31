<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

$wlim_students = WL_MIM_Helper::get_students();

$institute_id = WL_MIM_Helper::get_current_institute_id();

$general_enrollment_prefix = WL_MIM_SettingHelper::get_general_enrollment_prefix_settings( $institute_id );

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

if ( empty( $general_institute['institute_name'] ) ) {
	$institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
	$institute_name = $general_institute['institute_name'];
}
?>

<div class="container-fluid wl_im_container">
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-bar-chart-o"></i> <?php esc_html_e( 'Report', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can view and print report.', WL_MIM_DOMAIN ); ?>
            </div>
            <!-- end main header content -->
        </div>
    </div>
    <div class="row">
        <div class="col card">
            <div class="card-header bg-primary text-white">
                <!-- card header content -->
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <div class="h4"><?php esc_html_e( 'Individual Student Report', WL_MIM_DOMAIN ); ?></div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <div id="wlim-view-report"></div>
                        <form id="wlim-view-report-form">
                            <div class="form-group">
                                <label for="wlim-report-student" class="col-form-label"><?php esc_html_e( "View Report", WL_MIM_DOMAIN ); ?>:</label>
                                <select name="student" class="form-control selectpicker" id="wlim-report-student">
                                    <option value="">-------- <?php esc_html_e( "Select a Student", WL_MIM_DOMAIN ); ?>
                                        --------
                                    </option>
									<?php
									if ( count( $wlim_students ) > 0 ) {
										foreach ( $wlim_students as $student ) {
                                            if (get_option( 'multi_institute_enable_seprate_enrollment_id', 'yes' )) {
                                                $student_id = $student->enrollment_id;
                                            } else {
                                                $student_id = $student->id;
                                            }
											$name = $student->first_name;
											$name .= $student->middle_name ? " $student->middle_name" : "";
											$name .= $student->last_name ? " $student->last_name" : "";
											?>
                                            <option value="<?php echo esc_attr( $student->id ); ?>"><?php echo "$name (" . WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ) . ")"; ?></option>
											<?php
										}
									} ?>
                                </select>
                            </div>
                            <div class="mt-3 float-right">
                                <button type="submit" class="btn btn-primary view-report-submit"><?php esc_html_e( 'Get Report!', WL_MIM_DOMAIN ); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col card">
            <div class="card-header bg-primary text-white">
                <!-- card header content -->
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <div class="h4"><?php esc_html_e( 'Overall Students / Fees Report', WL_MIM_DOMAIN ); ?></div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <form id="wlim-view-overall-report-form">
                            <div class="form-group">
                                <label for="wlim-overall-report" class="col-form-label"><?php esc_html_e( "View Report", WL_MIM_DOMAIN ); ?>:</label>
                                <select name="report_by" class="form-control selectpicker" id="wlim-overall-report">
                                    <option value="">-------- <?php esc_html_e( "Select Report Type", WL_MIM_DOMAIN ); ?> --------</option>
									<?php
									foreach ( WL_MIM_Helper::get_report_by_list() as $key => $value ) { ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( $value, WL_MIM_DOMAIN ); ?></option>
									<?php
									} ?>
                                </select>
                            </div>
                            <div id="wlim-overall-report-selection"></div>
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
                                <button type="submit" class="btn btn-primary view-overall-report-submit"><?php esc_html_e( 'Get Report!', WL_MIM_DOMAIN ); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <br>
                        <div id="wlim-view-overall-report"></div>
                    </div>
                </div>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
</div>

<!-- print student modal -->
<div class="modal fade" id="print-student" tabindex="-1" role="dialog" aria-labelledby="print-student-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="print-student-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="print-student-label"><?php esc_html_e( 'View and Print Student', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="print_student"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - print student modal -->

<!-- print student admission detail modal -->
<div class="modal fade" id="print-student-admission-detail" tabindex="-1" role="dialog" aria-labelledby="print-student-admission-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="print-student-admission-detail-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="print-student-admission-detail-label"><?php esc_html_e( 'View and Print Admission Detail', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="print_student_admission_detail"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - print student admission detail modal -->

<!-- print student fees report -->
<div class="modal fade" id="print-student-fees-report" tabindex="-1" role="dialog" aria-labelledby="print-student-fees-report-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="print-student-fees-report-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="print-student-fees-report-label"><?php esc_html_e( 'View and Print Fees Report', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="print_student_fees_report"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - print student fees report -->

<!-- print student pending fees -->
<div class="modal fade" id="print-student-pending-fees" tabindex="-1" role="dialog" aria-labelledby="print-student-pending-fees-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="print-student-pending-fees-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="print-student-pending-fees-label"><?php esc_html_e( 'View and Print Pending Fees', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="print_student_pending_fees"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - print student pending fees -->

<!-- print student certificate modal -->
<div class="modal fade" id="print-student-certificate" tabindex="-1" role="dialog" aria-labelledby="print-student-certificate-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="print-student-certificate-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="print-student-certificate-label"><?php esc_html_e( 'View and Print Completion Certificate', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="print_student_certificate"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - print student certificate modal -->

<!-- print installment fee receipt -->
<div class="modal fade" id="print-installment-fee-receipt" tabindex="-1" role="dialog" aria-labelledby="print-installment-fee-receipt-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="print-installment-fee-receipt-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="print-installment-fee-receipt-label"><?php esc_html_e( 'View and Print Installment Fee Receipt', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="print_installment_fee_receipt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - print installment fee receipt -->

<!-- print invoice fee invoice -->
<div class="modal fade" id="print-invoice-fee-invoice" tabindex="-1" role="dialog" aria-labelledby="print-invoice-fee-invoice-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="print-invoice-fee-invoice-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="print-invoice-fee-invoice-label"><?php esc_html_e( 'View and Print Fee Invoice Fee', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="print_invoice_fee_invoice"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div>
<!-- end - print invoice fee invoice -->


