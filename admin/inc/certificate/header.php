 <!-- row 1 -->
 <div class="row">
        <div class="col">
            <!-- main header content -->
           
            <h2 class="text-center font-weight-normal">
                <span class="border-bottom"><i class="fa fa-usd"></i> <?php esc_html_e( 'Certificate', WL_MIM_DOMAIN ); ?></span>
            </h2>
			<?php
			$institute_active = WL_MIM_Helper::get_current_institute_status();
			if ( ! $institute_active ) {
				require_once( WL_MIM_PLUGIN_DIR_PATH . 'admin/inc/wl_im_institute_status.php' );
				die();
			} ?>
            <div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you manage your student certificates.', WL_MIM_DOMAIN ); ?>
            </div>
            <!-- end main header content -->
        </div>
    </div>
    <!-- end - row 1 -->