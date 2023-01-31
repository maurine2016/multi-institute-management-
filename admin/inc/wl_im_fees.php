<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_SettingHelper.php' );

$wlim_active_students = WL_MIM_Helper::get_active_students();

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
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html( $institute_name ); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-usd"></i> <?php esc_html_e( 'Fees', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can find installments or add a new installment.', WL_MIM_DOMAIN ); ?>
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
                    <div class="col-md-7 col-xs-12">
                        <div class="h4"><?php esc_html_e( 'Manage Fees', WL_MIM_DOMAIN ); ?></div>
                    </div>
                    <div class="col-md-5 col-xs-12">
                        <div class="btn-group float-right" role="group">
                            <button type="button" class="btn btn-outline-light add-installment mr-2" data-toggle="modal" data-target="#add-installment" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-plus"></i> <?php esc_html_e( 'Collect Payment', WL_MIM_DOMAIN ); ?>
                            </button>
                            <button type="button" class="btn btn-outline-light float-right add-fee-type" data-toggle="modal" data-target="#add-fee-type" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Fee Type', WL_MIM_DOMAIN ); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <div class="h5 text-primary"><?php esc_html_e( 'Installments', WL_MIM_DOMAIN ); ?></div>
                        <hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="installment-table">
                        <?php $entity = 'installment';
                            require( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php' ); ?>
                            <thead>
                                <tr>
                                <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e( 'Receipt', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Total Amount', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Student Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Payment Method', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Payment ID', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Date', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Edit', WL_MIM_DOMAIN ); ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col">
                        <div class="h5 text-primary"><?php esc_html_e( 'Fee Types', WL_MIM_DOMAIN ); ?></div>
                        <hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="fee-type-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php esc_html_e( 'Fee Type', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Period', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new fee type modal -->
<div class="modal fade" id="add-fee-type" tabindex="-1" role="dialog" aria-labelledby="add-fee-type-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-fee-type-label"><?php esc_html_e( 'Add New Fee Type', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4">
                <form id="wlim-add-fee-type-form">
					<?php $nonce = wp_create_nonce( 'add-fee-type' ); ?>
                    <input type="hidden" name="add-fee-type" value="<?php echo esc_attr( $nonce ); ?>">
                    <div class="form-group">
                        <label for="wlim-fee-type-fee_type" class="col-form-label"><?php esc_html_e( 'Fee Type', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="fee_type" type="text" class="form-control" id="wlim-fee-type-fee_type" placeholder="<?php esc_html_e( "Fee Type", WL_MIM_DOMAIN ); ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="wlim-fee-type-amount" class="col-form-label"><?php esc_html_e( 'Amount', WL_MIM_DOMAIN ); ?>
                            :</label>
                        <input name="amount" type="number" class="form-control" id="wlim-fee-type-amount" placeholder="<?php esc_html_e( "Amount", WL_MIM_DOMAIN ); ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="wlim-fee-type-period"
                               class="pt-2"><?php esc_html_e( 'Period', WL_MIM_DOMAIN ); ?>:</label>
                        <select name="period" class="form-control" id="wlim-fee-type-period">
                            <?php
                            foreach ( WL_MIM_Helper::get_period_in() as $key => $value ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                <?php
                            } ?>
                        </select>
                    </div>
                    <div class="form-check pl-0">
                        <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-fee-type-is_active" checked>
                        <label class="form-check-label" for="wlim-fee-type-is_active">
							<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary add-fee-type-submit"><?php esc_html_e( 'Add New Fee Type', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - add new fee type modal -->

<!-- update fee type modal -->
<div class="modal fade" id="update-fee-type" tabindex="-1" role="dialog" aria-labelledby="update-fee-type-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-fee-type-label"><?php esc_html_e( 'Update Fee Type', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="fetch_fee-type"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary update-fee-type-submit"><?php esc_html_e( 'Update Fee Type', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - update fee type modal -->

<!-- Collect Payment modal -->
<div class="modal fade" id="add-installment" tabindex="-1" role="dialog" aria-labelledby="add-installment-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-installment-label"><?php esc_html_e( 'Collect Payment', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4">
                <form id="wlim-add-installment-form">
					<?php $nonce = wp_create_nonce( 'add-installment' ); ?>
                    <input type="hidden" name="add-installment" value="<?php echo esc_attr( $nonce ); ?>">
                    <div class="wlim-add-installment-form-fields">
                        <div class="form-group">
                            <label for="wlim-installment-student" class="col-form-label"><?php esc_html_e( "Student", WL_MIM_DOMAIN ); ?>:</label>
                            <select name="student" class="form-control selectpicker" id="wlim-installment-student">
                                <option value="">-------- <?php esc_html_e( "Select a Student", WL_MIM_DOMAIN ); ?>--------
                                </option>
								<?php
                                
								if ( count( $wlim_active_students ) > 0 ) {
									foreach ( $wlim_active_students as $active_student ) { 
                                        if (get_option( 'multi_institute_enable_seprate_enrollment_id', 'yes' )) {
                                            $student_id = $active_student->enrollment_id;
                                        } else {
                                            $student_id = $active_student->id;
                                        }
                                        ?>
                                    
                                        <option value="<?php echo esc_attr( $active_student->id ); ?>"><?php echo esc_html( "$active_student->first_name $active_student->last_name (" ) . WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ) . ")"; ?></option>
									<?php
									}
								} ?>
                            </select>
                            <div id="wlim_add_installment_fetch_fees"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary add-installment-submit"><?php esc_html_e( 'Collect Payment', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - Collect Payment modal -->

<!-- update installment modal -->
<div class="modal fade" id="update-installment" tabindex="-1" role="dialog" aria-labelledby="update-installment-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-installment-label"><?php esc_html_e( 'Update Installment', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="fetch_installment"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary update-installment-submit"><?php esc_html_e( 'Update Installment', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - update installment modal -->

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
