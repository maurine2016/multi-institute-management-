<?php
defined( 'ABSPATH' ) || die();
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php' );

$institute_id = WL_MIM_Helper::get_current_institute_id();

$wlim_active_categories = WL_MIM_Helper::get_active_categories_institute( $institute_id );

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings( $institute_id );

if ( empty( $general_institute['institute_name'] ) ) {
	$institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
	$institute_name = $general_institute['institute_name'];
}

global $wpdb;

if ( current_user_can( WL_MIM_Helper::get_multi_institute_capability() ) ) {
    $can_add_course = true;
} else {
    $institute = $wpdb->get_row( "SELECT can_add_course FROM {$wpdb->prefix}wl_min_institutes WHERE id = $institute_id" );
    $can_add_course = false;
    if($institute) {
        $can_add_course = (bool) $institute->can_add_course;
    }
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
                <span class="border-bottom"><i class="fa fa-graduation-cap"></i> <?php esc_html_e( 'Courses', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
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
                        <div class="btn-group float-right" role="group">
                            <?php if ( $can_add_course ) { ?>
                            <button type="button" class="btn btn-outline-light add-course mr-2" data-toggle="modal" data-target="#add-course" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Course', WL_MIM_DOMAIN ); ?>
                            </button>
                            <?php } ?>
                            <button type="button" class="btn btn-outline-light float-right add-category" data-toggle="modal" data-target="#add-category" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Category', WL_MIM_DOMAIN ); ?>
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
                        <table class="table table-hover table-striped table-bordered" id="course-table">
                        <?php
					$entity = 'course';
                    require( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php' ); ?>
                            <thead>
                                <tr>
                                <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e( 'Course Code', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Course Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Category', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Fees', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Fees Period', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Total Students', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Active Students', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Added On', WL_MIM_DOMAIN ); ?></th>
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
                        <div class="h5 text-primary"><?php esc_html_e( 'Categories', WL_MIM_DOMAIN ); ?></div>
                        <hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <table class="table table-hover table-striped table-bordered" id="category-table">
                       
					
                            <thead>
                                <tr>
                               
                                    <th scope="col"><?php esc_html_e( 'Category Name', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Total Courses', WL_MIM_DOMAIN ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Is Active', WL_MIM_DOMAIN ); ?></th>
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

<!-- add new course modal -->
<div class="modal fade" id="add-course" tabindex="-1" role="dialog" aria-labelledby="add-course-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-course-label"><?php esc_html_e( 'Add New Course', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4">
                <form id="wlim-add-course-form">
					<?php $nonce = wp_create_nonce( 'add-course' ); ?>
                    <input type="hidden" name="add-course" value="<?php echo esc_attr( $nonce ); ?>">
                    <div class="form-group wlim-selectpicker">
                        <label for="wlim-course-category" class="col-form-label"><?php esc_html_e( "Category", WL_MIM_DOMAIN ); ?>:</label>
                        <select name="category" class="form-control selectpicker" id="wlim-course-category">
                            <option value="">-------- <?php esc_html_e( "Select a Category", WL_MIM_DOMAIN ); ?> --------</option>
							<?php
							if ( count( $wlim_active_categories ) > 0 ) {
								foreach ( $wlim_active_categories as $active_category ) { ?>
                                    <option value="<?php echo esc_attr( $active_category->id ); ?>"><?php echo esc_html( $active_category->name ); ?></option>
									<?php
								}
							} ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-course-course_code" class="col-form-label"><?php esc_html_e( 'Course Code', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="course_code" type="text" class="form-control" id="wlim-course-course_code" placeholder="<?php esc_html_e( "Course Code", WL_MIM_DOMAIN ); ?>">
                        </div>
                        <div class="col form-group">
                            <label for="wlim-course-course_name" class="col-form-label"><?php esc_html_e( 'Course Name', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="course_name" type="text" class="form-control" id="wlim-course-course_name" placeholder="<?php esc_html_e( "Course Name", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="wlim-course-course_detail" class="col-form-label"><?php esc_html_e( 'Course Detail', WL_MIM_DOMAIN ); ?>:</label>
                        <textarea name="course_detail" class="form-control" rows="3" id="wlim-course-course_detail" placeholder="<?php esc_html_e( "Course Detail", WL_MIM_DOMAIN ); ?>"></textarea>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-course-duration" class="col-form-label"><?php esc_html_e( 'Duration', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="duration" type="number" class="form-control" id="wlim-course-duration" placeholder="<?php esc_html_e( "Duration", WL_MIM_DOMAIN ); ?>" step="1" min="0">
                        </div>
                        <div class="col form-group wlim_select_col">
                            <label for="wlim-course-duration_in" class="pt-2"><?php esc_html_e( 'Duration In', WL_MIM_DOMAIN ); ?>:</label>
                            <select name="duration_in" class="form-control" id="wlim-course-duration_in">
								<?php
								foreach ( WL_MIM_Helper::get_duration_in() as $value ) { ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php esc_html_e( $value, WL_MIM_DOMAIN ); ?></option>
									<?php
								} ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="wlim-course-fees" class="col-form-label"><?php esc_html_e( 'Fees', WL_MIM_DOMAIN ); ?>:</label>
                        <input name="fees" type="number" class="form-control" id="wlim-course-fees" placeholder="<?php esc_html_e( "Fees", WL_MIM_DOMAIN ); ?>" min="0" step="any">
                    </div>
                    <div class="form-group">
                        <label for="wlim-course-period"
                               class="pt-2"><?php esc_html_e( 'Fees Period', WL_MIM_DOMAIN ); ?>:</label>
                        <select name="period" class="form-control" id="wlim-course-period">
                            <?php
                            foreach ( WL_MIM_Helper::get_period_in() as $key => $value ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                <?php
                            } ?>
                        </select>
                    </div>
                    <div class="form-check pl-0">
                        <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-course-is_active" checked>
                        <label class="form-check-label" for="wlim-course-is_active">
							<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary add-course-submit"><?php esc_html_e( 'Add New Course', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - add new course modal -->

<!-- update course modal -->
<div class="modal fade" id="update-course" tabindex="-1" role="dialog" aria-labelledby="update-course-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-course-label"><?php esc_html_e( 'Update Course', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="fetch_course"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary update-course-submit"><?php esc_html_e( 'Update Course', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - update course modal -->

<!-- add new category modal -->
<div class="modal fade" id="add-category" tabindex="-1" role="dialog" aria-labelledby="add-category-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-category-label"><?php esc_html_e( 'Add New Category', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4">
                <form id="wlim-add-category-form">
					<?php $nonce = wp_create_nonce( 'add-category' ); ?>
                    <input type="hidden" name="add-category" value="<?php echo esc_attr( $nonce ); ?>">
                    <div class="row">
                        <div class="col form-group">
                            <label for="wlim-category-name" class="col-form-label"><?php esc_html_e( 'Category Name', WL_MIM_DOMAIN ); ?>:</label>
                            <input name="name" type="text" class="form-control" id="wlim-course-name" placeholder="<?php esc_html_e( "Category Name", WL_MIM_DOMAIN ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="wlim-category-detail" class="col-form-label"><?php esc_html_e( 'Detail', WL_MIM_DOMAIN ); ?>:</label>
                        <textarea name="detail" class="form-control" rows="3" id="wlim-category-detail" placeholder="<?php esc_html_e( "Detail", WL_MIM_DOMAIN ); ?>"></textarea>
                    </div>
                    <div class="form-check pl-0">
                        <input name="is_active" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-category-is_active" checked>
                        <label class="form-check-label" for="wlim-category-is_active">
							<?php esc_html_e( 'Is Active?', WL_MIM_DOMAIN ); ?>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary add-category-submit"><?php esc_html_e( 'Add New Category', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - add new category modal -->

<!-- update category modal -->
<div class="modal fade" id="update-category" tabindex="-1" role="dialog" aria-labelledby="update-category-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-category-label"><?php esc_html_e( 'Update Category', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="fetch_category"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', WL_MIM_DOMAIN ); ?></button>
                <button type="button" class="btn btn-primary update-category-submit"><?php esc_html_e( 'Update Category', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - update category modal -->