<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
?>

<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="display-4 text-center">
                <span class="border-bottom"><i class="fa fa-graduation-cap"></i> <?php esc_html_e( 'Courses', WL_MIM_DOMAIN ); ?></span>
            </h1>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can either add a new course or edit existing courses.', WL_MIM_DOMAIN ); ?>
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
                        <div class="h4"><?php esc_html_e( 'Manage Courses', WL_MIM_DOMAIN ); ?></div>
                    </div>
                    <div class="col-md-3 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-main_main_course" data-toggle="modal" data-target="#add-main-course" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Course', WL_MIM_DOMAIN ); ?>
                        </button>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="main-course-table">
                            <thead>
                                <tr>
                                    <th scope="col"><?php esc_html_e( 'Course Code', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Course Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Fees', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Fees Period', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new course modal -->
<div class="modal fade" id="add-main-course" tabindex="-1" role="dialog" aria-labelledby="add-main-course-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-main-course-label"><?php esc_html_e( 'Add New Course', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4">
                <form id="wlim-add-main-course-form">
					<?php $nonce = wp_create_nonce( 'add-main-course' ); ?>
                    <input type="hidden" name="add-main-course" value="<?php echo esc_attr( $nonce ); ?>">
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-main-course-main-course_code" class="col-form-label"><?php esc_html_e( 'Course Code', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="course_code" type="text" class="form-control" id="wlim-main-course-main-course_code" placeholder="<?php esc_html_e( "Course Code", WL_MIM_DOMAIN ); ?>">
                        </div>
                        <div class="col form-group">
                            <label for="wlim-main-course-main-course_name" class="col-form-label"><?php esc_html_e( 'Course Name', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="course_name" type="text" class="form-control" id="wlim-main-course-main-course_name" placeholder="<?php esc_html_e( "Course Name", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="wlim-main-course-main-course_detail" class="col-form-label"><?php esc_html_e( 'Course Detail', WL_MIM_DOMAIN ); ?>:</label>
                        <textarea name="course_detail" class="form-control" rows="3" id="wlim-main-course-main-course_detail" placeholder="<?php esc_html_e( "Course Detail", WL_MIM_DOMAIN ); ?>"></textarea>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-main-course-duration" class="col-form-label"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="duration" type="number" class="form-control" id="wlim-main-course-duration" placeholder="<?php esc_html_e( "Duration", WL_MIM_DOMAIN ); ?>" step="1" min="0">
                        </div>
                        <div class="col form-group wlim_select_col">
                            <label for="wlim-main-course-duration_in" class="pt-2"><?php esc_html_e( 'Duration In', WL_MIM_DOMAIN ); ?>:</label>
                            <select name="duration_in" class="form-control" id="wlim-main-course-duration_in">
								<?php
								foreach ( WL_MIM_Helper::get_duration_in() as $value ) { ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php esc_html_e( $value, WL_MIM_DOMAIN ); ?></option>
									<?php
								} ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="wlim-main-course-fees" class="col-form-label"><?php esc_html_e( 'Fees', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="fees" type="number" class="form-control" id="wlim-main-course-fees" placeholder="<?php esc_html_e( "Fees", WL_MIM_DOMAIN ); ?>" min="0" step="any">
                    </div>
                    <div class="form-group">
                        <label for="wlim-main-course-period"
                               class="pt-2"><?php esc_html_e( 'Fees Period', WL_MIM_DOMAIN ); ?>:</label>
                        <select name="period" class="form-control" id="wlim-main-course-period">
                            <?php
                            foreach ( WL_MIM_Helper::get_period_in() as $key => $value ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                <?php
                            } ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary add-main-course-submit"><?php esc_html_e( 'Add New Course', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - add new course modal -->

<!-- update course modal -->
<div class="modal fade" id="update-main-course" tabindex="-1" role="dialog" aria-labelledby="update-main-course-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-main-course-label"><?php esc_html_e( 'Update Course', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="fetch_main_course"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary update-main-course-submit"><?php esc_html_e( 'Update Course', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - update course modal -->
