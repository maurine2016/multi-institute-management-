<?php
$page_url = 'admin.php?page=' . 'multi-institute-management-certificate';
?>

<div class="row">
    <div class="card col">
        <div class="card-header bg-primary text-white">
            <!-- card header content -->
            <div class="row">
                <div class="col-md-9 col-xs-12">
                    <div class="h4"><?php esc_html_e('Manage Certificate', WL_MIM_DOMAIN); ?></div>
                </div>
                <div class="col-md-3 col-xs-12 ">
                    <a href="<?php echo esc_url($page_url . '&action=save'); ?>" class="float-md-right btn btn-sm btn-outline-light">
                        <?php echo esc_html('Add New Certificate', WL_MIM_DOMAIN); ?>
                    </a>
                </div>
            </div>
            <!-- end - card header content -->
        </div>
        <div class="card-body">
            <!-- card body content -->
            <div class="row">
                <div class="col">
                    <div class="wlsm-table-block wlsm-form-section">
                        <table class="table table-hover table-bordered" id="wl-mim-certificates-table">
                            <thead>
                                <tr class="text-white bg-primary">
                                    <th scope="col"><?php esc_html_e('Title', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Certificates Distributed', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col"><?php esc_html_e('Distribute Certificate', WL_MIM_DOMAIN); ?></th>
                                    <th scope="col" class="text-nowrap"><?php esc_html_e('Action', WL_MIM_DOMAIN); ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <!-- end - card body content -->
        </div>
    </div>
</div>
<!-- end - row 2 -->
</div>