<?php
defined( 'ABSPATH' ) || die();
?>

<div class="container-fluid wl_im_container">
	<!-- row 1 -->
	<div class="row">
		<div class="col">
			<!-- main header content -->
			<h1 class="display-4 text-center"><span class="border-bottom"><i class="fa fa-refresh"></i> <?php esc_html_e( 'Reset Plugin', WL_MIM_DOMAIN ); ?></span></h1>
			<div class="mt-3 alert alert-info text-center" role="alert">
				<?php esc_html_e( 'Here, you can reset the plugin to its initial state.', WL_MIM_DOMAIN ); ?>
			</div>
			<!-- end main header content -->
		</div>
	</div>
	<!-- end - row 1 -->

	<!-- row 2 -->
	<div class="row">
		<div class="card col">
			<div class="card-body">
				<!-- card body content -->
				<div class="">
					<div class="ml-4 font-weight-bold h5"><?php esc_html_e( 'This will', WL_MIM_DOMAIN ); ?>:</div>
					<ul class="list-group list-group-flush text-danger font-weight-bold">
						<li class="list-group-item">* <?php esc_html_e( 'Remove all database tables created by multi-institute plugin and recreate them.', WL_MIM_DOMAIN ); ?></li>
						<li class="list-group-item">* <?php esc_html_e( 'Remove all attachments (photo, signature, ID etc.) related to institutes.', WL_MIM_DOMAIN ); ?></li>
						<li class="list-group-item">* <?php esc_html_e( "Remove all student user accounts.", WL_MIM_DOMAIN ); ?></li>
						<li class="list-group-item">* <?php esc_html_e( 'Reset all settings of institutes.', WL_MIM_DOMAIN ); ?></li>
					</ul>
				</div>
				<div class="ml-4 mt-4">
					<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlim-reset-plugin-form" >
						<?php $nonce = wp_create_nonce( 'reset-plugin' ); ?>
		                <input type="hidden" name="reset-plugin" value="<?php echo $nonce; ?>">
		                <input type="hidden" name="action" value="wl-mim-reset-plugin">
						<button type="submit" class="btn btn-lg btn-info text-white wlim-reset-plugin-button" data-message="<?php esc_html_e( "Are you sure to reset the plugin?", WL_MIM_DOMAIN ); ?>"><?php esc_html_e( 'Reset Plugin', WL_MIM_DOMAIN ); ?></button>
					</form>
				</div>
				<!-- end - card body content -->
			</div>
		</div>
	</div>
	<!-- end - row 2 -->
</div>