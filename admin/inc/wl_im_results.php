<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

$wlim_exams          = WL_MIM_Helper::get_exams();
$wlim_active_courses = WL_MIM_Helper::get_active_courses();
$institute_id        = WL_MIM_Helper::get_current_institute_id();

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
                <span class="border-bottom"><i class="fa fa-bar-chart-o"></i> <?php esc_html_e( 'Exam Results', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can add and publish exam results.', WL_MIM_DOMAIN ); ?>
            </div>
            <div class="text-center">
                <?php esc_html_e( 'To Display Admit Card Form on Front End, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>:
                <div class="col-12 justify-content-center align-items-center">
                    <span class="col-6">
                        <strong id="wl_im_admit_card_form_shortcode">[institute_admit_card id=<?php echo esc_html( $institute_id ); ?>]</strong>
                    </span>
                    <span class="col-6">
                        <button id="wl_im_admit_card_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                    </span>
                </div>
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
                        <span class="h4"><?php esc_html_e( 'Manage Exams and Results', WL_MIM_DOMAIN ); ?></span>
                        <span class="float-right">
							<button type="button" class="btn btn-outline-light add-exam mr-2" data-toggle="modal" data-target="#add-exam" data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Exam', WL_MIM_DOMAIN ); ?>
							</button>
						</span>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="exam-table">
                        <?php $entity = 'exam';
                            require( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php' ); ?>
                            <thead>
                            <tr>
                            <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                <th scope="col"><?php esc_html_e( 'Exam Code', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Exam Title', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Exam Date', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Is Published', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Published At', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Added By', WL_MIM_DOMAIN ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Edit', WL_MIM_DOMAIN ); ?></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-wlim-exam-results-tab" data-toggle="pill" href="#pills-wlim-exam-results" role="tab" aria-controls="pills-wlim-exam-results" aria-selected="true"><?php esc_html_e( "Exam Results", WL_MIM_DOMAIN ); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-wlim-batch-results-tab" data-toggle="pill" href="#pills-wlim-batch-results" role="tab" aria-controls="pills-wlim-batch-results" aria-selected="false"><?php esc_html_e( "Save Results", WL_MIM_DOMAIN ); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-wlim-results-shortcode-tab" data-toggle="pill" href="#pills-wlim-results-shortcode" role="tab" aria-controls="pills-wlim-results-shortcode" aria-selected="false"><?php esc_html_e( "Shortcode", WL_MIM_DOMAIN ); ?></a>
                            </li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-wlim-exam-results" role="tabpanel" aria-labelledby="pills-wlim-exam-results-tab">
                                <div class="row">
                                    <div class="col-md-8 col-xs-12">
                                        <form id="wlim-get-exam-results-form">
                                            <div class="form-group wlim-selectpicker">
                                                <label for="wlim-result-exam" class="col-form-label">* <?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?>
                                                    :</label>
                                                <select name="exam" class="form-control selectpicker">
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
                                                <button type="submit" class="btn btn-success"><?php esc_html_e( 'Get Results', WL_MIM_DOMAIN ); ?></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div id="wlim-get-exam-results"></div>
                            </div>
                            
                            <div class="tab-pane fade" id="pills-wlim-batch-results" role="tabpanel" aria-labelledby="pills-wlim-batch-results-tab">
                            <!-- Save exam results -->
                            <div class="tab-pane fade show active" id="pills-wlim-exam-results-save" role="tabpanel" aria-labelledby="pills-wlim-exam-results-save-tab">
                                <div class="row">
                                    <div class="col-md-3 col-xs-12">
                                        <form id="wlim-save-exam-results-form">
                                            <div class="form-group wlim-selectpicker">
                                                <label for="wlim-result-exam" class="col-form-label">* <?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?>
                                                    :</label>
                                                <select name="exam" class="form-control selectpicker">
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
                                                <button type="submit" class="btn btn-success"><?php esc_html_e( 'Save Results', WL_MIM_DOMAIN ); ?></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div id="wlim-save-exam-results"></div>
                            </div>

                                 <!-- <div class="row">
                                    <div class="col-md-8 col-xs-12">
                                        <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-save-result-form">
											<?php $nonce = wp_create_nonce( 'save-result' ); ?>
                                            <input type="hidden" name="save-result" value="<?php echo esc_attr( $nonce ); ?>">
                                            <input type="hidden" name="action" value="wl-mim-save-result">
                                            <div class="form-group wlim-selectpicker">
                                                <label for="wlim-result-exam" class="col-form-label">* <?php esc_html_e( "Exam", WL_MIM_DOMAIN ); ?>
                                                    :</label>
                                                <select name="exam" class="form-control selectpicker" id="wlim-result-exam">
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
                                            <div class="form-group wlim-selectpicker">
                                                <label for="wlim-result-course" class="col-form-label">* <?php esc_html_e( "Course", WL_MIM_DOMAIN ); ?>
                                                    :</label>
                                                <select name="course" class="form-control selectpicker" id="wlim-result-course">
                                                    <option value="">-------- <?php esc_html_e( "Select a Course", WL_MIM_DOMAIN ); ?> --------</option>
													<?php
													if ( count( $wlim_active_courses ) > 0 ) {
														foreach ( $wlim_active_courses as $active_course ) { ?>
                                                            <option value="<?php echo esc_attr( $active_course->id ); ?>">
																<?php echo esc_html( "$active_course->course_name ($active_course->course_code)" ); ?>
                                                            </option>
															<?php
														}
													} ?>
                                                </select>
                                            </div>
                                            <div id="wlim-add-result-course-batches"></div>
                                            <div class="mt-3 float-right">
                                                <button type="submit" class="btn btn-success save-result-submit"><?php esc_html_e( 'Save Result', WL_MIM_DOMAIN ); ?></button>
                                            </div>
                                        </form>
                                    </div>
                                </div> -->
                            </div>
                            <div class="tab-pane fade" id="pills-wlim-results-shortcode" role="tabpanel" aria-labelledby="pills-wlim-results-shortcode-tab">
                                <div class="row">
                                    <div class="col">
                                        <div class="text-center border-top border-bottom pt-2 pb-2">
											<?php esc_html_e( 'To Display Exam Results Form on Front End, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>
                                            :
                                            <div class="col-12 justify-content-center align-items-center">
												<span class="col-6">
							 						<strong id="wl_im_exam_result_form_shortcode">[institute_exam_result id=<?php echo esc_attr( $institute_id ); ?>]</strong>
												</span>
                                                <span class="col-6">
													<button id="wl_im_exam_result_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
												</span>
                                            </div>
                                        </div>
                                        <div class="text-center border-top border-bottom pt-2 pb-2">
                                            <?php esc_html_e( 'To Display Exam Results By Name Form on Front End, Copy and Paste Shortcode', WL_MIM_DOMAIN ); ?>
                                            :
                                            <div class="col-12 justify-content-center align-items-center">
                                                <span class="col-6">
                                                    <strong id="wl_im_exam_results_by_name_form_shortcode">[institute_exam_results_by_name id=<?php echo esc_attr( $institute_id ); ?>]</strong>
                                                </span>
                                                <span class="col-6">
                                                    <button id="wl_im_exam_results_by_name_form_shortcode_copy" class="btn btn-outline-success btn-sm" type="button"><?php esc_html_e( 'Copy', WL_MIM_DOMAIN ); ?></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end - card body content -->
            </div>
        </div>
    </div>
</div>

<!-- add new exam modal -->
<div class="modal fade" id="add-exam" tabindex="-1" role="dialog" aria-labelledby="add-exam-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-exam-label"><?php esc_html_e( 'Add New Exam', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-add-exam-form">
                <div class="modal-body pr-4 pl-4">
					<?php $nonce = wp_create_nonce( 'add-exam' ); ?>
                    <input type="hidden" name="add-exam" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="hidden" name="action" value="wl-mim-add-exam">
                    <div class="form-group">
                        <label for="wlim-exam-exam_code" class="col-form-label">* <?php esc_html_e( 'Exam Code', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="exam_code" type="text" class="form-control" id="wlim-exam-exam_code" placeholder="<?php esc_html_e( "Exam Code", WL_MIM_DOMAIN ); ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="wlim-exam-exam_title" class="col-form-label">* <?php esc_html_e( 'Exam Title', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="exam_title" type="text" class="form-control" id="wlim-exam-exam_title" placeholder="<?php esc_html_e( "Exam Title", WL_MIM_DOMAIN ); ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label for="wlim-exam-exam_date" class="col-form-label">* <?php esc_html_e( 'Exam Date', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="exam_date" type="text" class="form-control wlim-exam-exam_date" id="wlim-exam-exam_date" placeholder="<?php esc_html_e( "Exam Date", WL_MIM_DOMAIN ); ?>">
                    </div>
                    <label class="col-form-label">* <?php esc_html_e( 'Exam Marks', WL_MIM_DOMAIN ); ?>:</label>
                    <div class="exam_marks_box">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?></th>
                                <th><?php esc_html_e( 'Maximum Theory Marks', WL_MIM_DOMAIN ); ?></th>
                                <th><?php esc_html_e( 'Maximum Practical Marks', WL_MIM_DOMAIN ); ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="exam_marks_rows exam_marks_table">
                            <tr>
                                <td>
                                    <input type="text" name="marks[subject][]" class="form-control" placeholder="<?php esc_html_e( 'Subject', WL_MIM_DOMAIN ); ?>">
                                </td>
                                <td>
                                    <input type="number" min="0" name="marks[maximum][]" class="form-control" placeholder="<?php esc_html_e( 'Maximum Theory Marks', WL_MIM_DOMAIN ); ?>">
                                </td>
                                <td>
                                    <input type="number" min="0" name="marks[maximum_p][]" class="form-control" placeholder="<?php esc_html_e( 'Maximum Practical Marks', WL_MIM_DOMAIN ); ?>">
                                </td>
                                <td>
                                    <button class="remove_row btn btn-danger btn-sm" type="button">
                                        <i class="fa fa-remove" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="text-right">
                            <button type="button" class="add-more-exam-marks btn btn-success btn-sm"><?php esc_html_e( 'Add More', WL_MIM_DOMAIN ); ?></button>
                        </div>
                    </div>
                    <label class="col-form-label">* <?php esc_html_e( 'Exam Notes', WL_MIM_DOMAIN ); ?>:</label>
                    <div class="exam_notes_box">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Note', WL_MIM_DOMAIN ); ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="exam_notes_rows exam_notes_table">
                            <tr>
                                <td>
                                    <input type="text" name="notes[]" class="form-control" placeholder="<?php esc_html_e( 'Add Note', WL_MIM_DOMAIN ); ?>">
                                </td>
                                <td>
                                    <button class="remove_row btn btn-danger btn-sm" type="button">
                                        <i class="fa fa-remove" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="text-right">
                            <button type="button" class="add-more-exam-notes btn btn-success btn-sm"><?php esc_html_e( 'Add More', WL_MIM_DOMAIN ); ?></button>
                        </div>
                    </div>
                    <div class="form-check pl-0">
                        <input name="is_published" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-exam-is_published">
                        <label class="form-check-label" for="wlim-exam-is_published">
							<?php esc_html_e( 'Is Published?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary add-exam-submit"><?php esc_html_e( 'Add New Exam', WL_MIM_DOMAIN ); ?></button>
                </div>
        </div>
        </form>
    </div>
</div><!-- end - add new exam modal -->

<!-- update exam modal -->
<div class="modal fade" id="update-exam" tabindex="-1" role="dialog" aria-labelledby="update-exam-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-exam-label"><?php esc_html_e( 'Update Exam', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-update-exam-form">
                <div class="modal-body pr-4 pl-4" id="fetch_exam"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                    <button type="submit" class="btn btn-primary update-exam-submit"><?php esc_html_e( 'Update Exam', WL_MIM_DOMAIN ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - update exam modal -->