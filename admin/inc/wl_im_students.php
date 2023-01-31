<?php
defined('ABSPATH') || die();
require_once(WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php');
require_once(WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/helpers/WL_MIM_SettingHelper.php');

$institute_id = WL_MIM_Helper::get_current_institute_id();

$general_institute = WL_MIM_SettingHelper::get_general_institute_settings($institute_id);

if (empty($general_institute['institute_name'])) {
    $institute_name = WL_MIM_Helper::get_current_institute_name();
} else {
    $institute_name = $general_institute['institute_name'];
}

$filters_applied = false;
if (isset($_GET['status'])) {
    $status = esc_attr($_GET['status']);
    if ($status == 'current') {
        $status_output   = esc_html__('Current', WL_MIM_DOMAIN);
        $filters_applied = true;
    } elseif ($status == 'former') {
        $status_output   = esc_html__('Former', WL_MIM_DOMAIN);
        $filters_applied = true;
    }
}
if (isset($_GET['course_id'])) {
    $course = WL_MIM_Helper::get_course($_GET['course_id']);
    if ($course) {
        $course_output   = "$course->course_name ($course->course_code)";
        $filters_applied = true;
    }
}
if (isset($_GET['batch_id'])) {
    $batch = WL_MIM_Helper::get_batch($_GET['batch_id']);
    if ($batch) {
        $batch_output    = $batch->batch_code;
        $filters_applied = true;
    }
}
?>
<div class="container-fluid wl_im_container">
    <!-- row 1 -->
    <div class="row">
        <div class="col">
            <!-- main header content -->
            <h1 class="text-center wlim-institute-dashboard-title">
                <span class="border-bottom"><i class="fa fa-tachometer"></i> <?php echo esc_html($institute_name); ?></span>
            </h1>
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-users"></i> <?php esc_html_e('Students', WL_MIM_DOMAIN); ?></span>
            </h2>
            <?php
            $institute_active = WL_MIM_Helper::get_current_institute_status();
            if (!$institute_active) {
                require_once(WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php');
                die();
            } ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
                <?php esc_html_e('Here, you can either add a new student or edit existing students.', WL_MIM_DOMAIN); ?>
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
                    <div class="col-md-6 col-xs-12">
                        <div class="h4"><?php esc_html_e('Manage Students', WL_MIM_DOMAIN); ?></div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <button type="button" class="btn btn-outline-light float-right add-student ml-2" data-toggle="modal" data-target="#add-student" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i> <?php esc_html_e('Add New Student', WL_MIM_DOMAIN); ?>
                        </button>
                        <button type="button" class="btn btn-outline-light float-right import-student" data-toggle="modal" data-target="#import-student" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-cloud-upload"></i> <?php esc_html_e('Bulk Import Student', WL_MIM_DOMAIN); ?>
                        </button>
                    </div>
                </div>
                <!-- end - card header content -->
            </div>
            <div class="card-body">
                <!-- card body content -->
                <div class="row">
                    <div class="col">
                        <div class="float-left">
                            <?php
                            if (isset($_GET['year']) && !empty($_GET['year'])) {
                                $year        = esc_attr($_GET['year']);
                                $date_format = esc_html__("Year", WL_MIM_DOMAIN) . ': ' . $year;
                                if (isset($_GET['month']) && !empty($_GET['month'])) {
                                    $date        = DateTime::createFromFormat('!m', esc_attr($_GET['month']));
                                    $month       = $date->format('F');
                                    $date_format = "$month, $year";
                                }
                            ?>
                                <span class="text-secondary"><?php esc_html_e('Showing Records For', WL_MIM_DOMAIN); ?>&nbsp;
                                    <strong><?php echo "$date_format"; ?></strong>
                                </span>
                                <a class="ml-1 text-primary" href="<?php echo admin_url('admin.php?page=multi-institute-management-students'); ?>"><?php esc_html_e('Show All', WL_MIM_DOMAIN); ?></a>
                            <?php
                            } else { ?>
                                <span class="text-secondary"><?php esc_html_e('Showing All Records', WL_MIM_DOMAIN); ?></span>
                                <?php
                                if ($filters_applied) { ?>
                                    <a class="ml-1 text-primary" href="<?php echo admin_url('admin.php?page=multi-institute-management-students'); ?>"><?php esc_html_e('Clear Filters', WL_MIM_DOMAIN); ?></a>
                            <?php
                                }
                            } ?>
                            <div class="row">
                                <div class="col">
                                    <ul>
                                        <?php
                                        if (isset($status_output)) { ?>
                                            <li class="font-weight-bold mt-1"><?php echo esc_html($status_output) . ' ' . esc_html__('Students', WL_MIM_DOMAIN); ?></li>
                                        <?php
                                        }
                                        if (isset($course_output)) { ?>
                                            <li>
                                                <span class="font-weight-bold"><?php esc_html_e('Course', WL_MIM_DOMAIN); ?>:&nbsp;</span>
                                                <span><?php echo esc_html($course_output); ?></span>
                                                <?php
                                                if (isset($batch_output)) { ?>
                                                    <span class="ml-3">
                                                        <span class="font-weight-bold"><?php esc_html_e('Batch', WL_MIM_DOMAIN); ?>:&nbsp;</span>
                                                        <span><?php echo esc_html($batch_output); ?></span>
                                                    </span>
                                                <?php
                                                } ?>
                                            </li>
                                        <?php
                                        } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="wlim-filter float-right mb-3">
                            <form method="get" class="form-inline">
                                <?php
                                foreach ($_GET as $name => $value) {
                                    $name  = esc_attr($name);
                                    $value = esc_attr($value);
                                    echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
                                } ?>
                                <input type="hidden" name="page" value="multi-institute-management-students">
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold" for="wlim-student-filter_by_year">
                                        <?php esc_html_e('Year', WL_MIM_DOMAIN); ?>:&nbsp;
                                    </label>
                                    <input type="text" name="year" class="form-control wlim-year" id="wlim-student-filter_by_year" placeholder="<?php esc_html_e('Year', WL_MIM_DOMAIN); ?>">
                                </div>&nbsp;
                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold" for="wlim-student-filter_by_month">
                                        <?php esc_html_e('Month', WL_MIM_DOMAIN); ?>:&nbsp;
                                    </label>
                                    <input type="text" name="month" class="form-control wlim-month" id="wlim-student-filter_by_month" placeholder="<?php esc_html_e('Month', WL_MIM_DOMAIN); ?>">
                                </div>&nbsp;
                                <button type="submit" class="btn btn-success"><?php esc_html_e('Apply Filter', WL_MIM_DOMAIN); ?></button>
                            </form>
                        </div>



                        <table class="table table-hover table-striped table-bordered" id="student-table">
                            <?php
                            $entity = 'students';
                            // require(WL_MIM_PLUGIN_DIR_PATH. 'inc/controllers/bulk_action.php' );
                            require(WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/controllers/bulk_action.php'); ?>
                            <thead>
                                <tr>
                                    <th><input type="checkbox" name="select_all" id="wl-mim-select-all" value="1"></th>
                                    <th scope="col"><?php esc_html_e('Enrollment ID', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('First Name', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Last Name', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Course', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Batch', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Duration', WL_MIM_DOMAIN); ?></th>
                                    <!-- <th scope="col"><?php esc_html_e('Status', WL_MIM_DOMAIN); ?></th> -->
                                    <th scope="col"><?php esc_html_e('Total Fees Payable', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Total Fees Paid', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Fees Status', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Phone', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Email', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Date of Birth', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Is Active', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Added By', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Registration Date', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Edit', WL_MIM_DOMAIN); ?></th>
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

<!-- add new student modal -->
<div class="modal fade" id="add-student" tabindex="-1" role="dialog" aria-labelledby="add-student-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-student-label"><?php esc_html_e('Add New Student', WL_MIM_DOMAIN); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="wlim-add-student-form">
                <div class="modal-body pr-4 pl-4">
                    <?php $nonce = wp_create_nonce('add-student'); ?>
                    <input type="hidden" name="add-student" value="<?php echo esc_attr($nonce); ?>">
                    <input type="hidden" name="action" value="wl-mim-add-student">
                    <div class="wlim-add-student-form-fields">
                        <div class="form-check pl-0 pb-3 mb-2 border-bottom">
                            <input name="from_enquiry" class="position-static mt-0 form-check-input" type="checkbox" id="wlim-student-from_enquiry">
                            <label class="form-check-label" for="wlim-student-from_enquiry">
                                <?php esc_html_e('From Enquiry?', WL_MIM_DOMAIN); ?>
                            </label>
                        </div>
                        <div id="wlim-add-student-from-enquiries"></div>
                        <div id="wlim-add-student-form-fields"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e('Cancel', WL_MIM_DOMAIN); ?></button>
                    <button type="submit" class="btn btn-primary add-student-submit"><?php esc_html_e('Add New Student', WL_MIM_DOMAIN); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - add new student modal -->

<!-- update student modal -->
<div class="modal fade" id="update-student" tabindex="-1" role="dialog" aria-labelledby="update-student-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="update-student-label"><?php esc_html_e('Update Student', WL_MIM_DOMAIN); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="wlim-update-student-form" enctype="multipart/form-data">
                <div class="modal-body pr-4 pl-4" id="fetch_student"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e('Cancel', WL_MIM_DOMAIN); ?></button>
                    <button type="submit" class="btn btn-primary update-student-submit"><?php esc_html_e('Update Student', WL_MIM_DOMAIN); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - update student modal -->


<!-- import new student modal -->
<div class="modal fade" id="import-student" tabindex="-1" role="dialog" aria-labelledby="import-student-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="import-student-label"><?php esc_html_e('Bulk Import Students', WL_MIM_DOMAIN); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="wlim-import-student-form">
                <div class="modal-body pr-4 pl-4">
                    <?php $nonce = wp_create_nonce('import-student'); ?>
                    <h5><?php esc_html_e('Import Students From CSV File', WL_MIM_DOMAIN); ?></h5>
                    <input type="hidden" name="import-student" value="<?php echo esc_attr($nonce); ?>">
                    <input type="hidden" name="action" value="wl-mim-import-student">
                    <em><?php esc_html_e('Fill Students details in the file, choose the CSV file with Students details and click "Bulk Import".', WL_MIM_DOMAIN); ?></em>

                    <div class="wlim-import-student-form-fields">
                        <div id="wlim-import-student-form-fields">
                            <div class="form-group">
                                <label for="bulk-import-file"><?php esc_html_e('Select Students CSV File', WL_MIM_DOMAIN); ?></label>
                                <input type="file" name="import_file" class="form-control-file" id="bulk-import-file" accept=".csv">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" href="<?php echo esc_url( WL_MIM_PLUGIN_URL . 'assets/sample-data/students.csv' ); ?>" download="<?php esc_html_e( 'students.csv', WL_MIM_DOMAIN ); ?>" class="btn btn-info"><?php esc_html_e('Download sample Data File', WL_MIM_DOMAIN); ?></a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e('Cancel', WL_MIM_DOMAIN); ?></button>
                    <button type="submit" class="btn btn-primary import-student-submit"><?php esc_html_e('Bulk Import', WL_MIM_DOMAIN); ?></button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end - add new student modal -->