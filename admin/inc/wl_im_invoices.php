<?php
defined( 'ABSPATH' ) or die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php' );

$institute_id         = WL_MIM_Helper::get_current_institute_id();
$wlim_active_students = WL_MIM_Helper::get_active_students();

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
                <span class="border-bottom"><i class="fa fa-usd"></i> <?php esc_html_e( 'Invoices', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can find invoices or add a new invoice.', WL_MIM_DOMAIN ); ?>
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
						<div class="h4"><?php esc_html_e( 'Manage Invoices', WL_MIM_DOMAIN ); ?></div>
					</div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-invoice" data-toggle="modal" data-target="#add-invoice" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Invoice', WL_MIM_DOMAIN ); ?>
                        </button>
					</div>
				</div>
				<!-- end - card header content -->
			</div>
			<div class="card-body">
				<!-- card body content -->
				<div class="row">
					<div class="col">
						<table class="table table-hover table-striped table-bordered" id="invoice-table">
							<thead>
								<tr>
						        	<th scope="col"><?php esc_html_e( 'Invoice No.', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Invoice Title', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Amount Payable', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Enrollment ID', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Student Name', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Status', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
						        	<th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new invoice modal -->
<div class="modal fade" id="add-invoice" tabindex="-1" role="dialog" aria-labelledby="add-invoice-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="add-invoice-label"><?php esc_html_e( 'Add New Invoice', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4">
				<form id="wlim-add-invoice-form">
					<?php $nonce = wp_create_nonce( 'add-invoice' ); ?>
	                <input type="hidden" name="add-invoice" value="<?php echo $nonce; ?>">
	                <div class="wlim-add-invoice-form-fields">
						<div class="form-group">
	                        <label for="wlim-invoice-student" class="col-form-label"><?php esc_html_e( "Student", WL_MIM_DOMAIN ); ?>:</label>
	                        <select name="student" class="form-control selectpicker" id="wlim-invoice-student">
	                            <option value="">-------- <?php esc_html_e( "Select a Student", WL_MIM_DOMAIN ); ?> --------</option>
	                        <?php
	                        if ( count( $wlim_active_students ) > 0 ) {
	                            foreach ( $wlim_active_students as $active_student ) { 
									if (get_option( 'multi_institute_enable_seprate_enrollment_id', 'yes' )) {
										$student_id = $active_student->enrollment_id;
									} else {
										$student_id = $active_student->id;
									}
									?>
	                            <option value="<?php echo $active_student->id; ?>"><?php echo "$active_student->first_name $active_student->last_name (" . WL_MIM_Helper::get_enrollment_id_with_prefix( $student_id, $general_enrollment_prefix ) . ")"; ?></option>
	                        <?php
	                            }
	                        } ?>
	                        </select>
	                        <div id="wlim_add_invoice_fetch_fees"></div>
	                	</div>
	                </div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
				<button type="button" class="btn btn-primary add-invoice-submit"><?php esc_html_e( 'Add New Invoice', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div>
<!-- end - add new invoice modal -->

<!-- update invoice modal -->
<div class="modal fade" id="update-invoice" tabindex="-1" role="dialog" aria-labelledby="update-invoice-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="update-invoice-label"><?php esc_html_e( 'Update Invoice', WL_MIM_DOMAIN ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body pr-4 pl-4" id="fetch_invoice"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
				<button type="button" class="btn btn-primary update-invoice-submit"><?php esc_html_e( 'Update Invoice', WL_MIM_DOMAIN ); ?></button>
			</div>
		</div>
	</div>
</div>
<!-- end - update invoice modal -->

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