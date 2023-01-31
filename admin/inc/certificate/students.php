<?php
defined( 'ABSPATH' ) || die();

$page_url = WL_MIM_Helper::get_certificates_page_url();

$institute_id = WL_MIM_Helper::get_current_institute_id();

$certificate = NULL;

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id          = absint( $_GET['id'] );
	$certificate = WL_MIM_Helper::fetch_certificate($institute_id, $id );
}

if ( ! $certificate ) {
	die;
}

$label = $certificate->label;
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					printf(
						wp_kses(
							/* translators: %s: certificate title */
							__( 'Certificates Distributed: %s', WL_MIM_DOMAIN ),
							array(
								'span' => array( 'class' => array() )
							)
						),
						esc_html( $certificate->label )
					);
					?>
				</span>
			</span>
			<span class="float-md-right">
				<a href="<?php echo esc_url( $page_url . "&action=distribute&id=" . $certificate->ID ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-certificate"></i>&nbsp;
					<?php esc_html_e( 'Distribute Certificate', WL_MIM_DOMAIN ); ?>
				</a>
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-certificate"></i>&nbsp;
					<?php esc_html_e( 'View All', WL_MIM_DOMAIN ); ?>
				</a>
			</span>
		</div>
		<div class="wlsm-table-block">
			<table class="table table-hover table-bordered" id="wl-mim-certificates-distributed-table" data-certificate="<?php echo esc_attr( $certificate->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'certificate-' . $certificate->ID ) ); ?>">
				<thead>
					<tr class="text-white bg-primary">
						<th scope="col"><?php esc_html_e( 'Certificate No.', WL_MIM_DOMAIN ); ?></th>
						<th scope="col"><?php esc_html_e( 'Student Name', WL_MIM_DOMAIN ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Course', WL_MIM_DOMAIN ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Batch', WL_MIM_DOMAIN ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Date Issued', WL_MIM_DOMAIN ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Phone', WL_MIM_DOMAIN ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', WL_MIM_DOMAIN ); ?></th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
