<?php
defined( 'ABSPATH' ) || die();
?>
<caption>
	<div class="input-group">
		<select name="bulk_action" data-entity="<?php echo esc_attr( $entity ); ?>" class="bulk-action-select bulk-action-select-<?php echo esc_attr( $entity ); ?> form-control">
			<option value=""><?php esc_html_e( 'Select Option', WL_MIM_DOMAIN ); ?></option>
			<option value="delete"><?php esc_html_e( 'Delete', WL_MIM_DOMAIN ); ?></option>
		</select>
		<button data-nonce="<?php echo esc_attr( wp_create_nonce( 'bulk-action-' . $entity ) ); ?>" data-message-title="<?php esc_attr_e( 'Confirmation!', WL_MIM_DOMAIN ); ?>" data-message-content="<?php esc_attr_e( 'Please confirm the action.', WL_MIM_DOMAIN ); ?>" data-cancel="<?php esc_attr_e( 'Cancel', WL_MIM_DOMAIN ); ?>" data-submit="<?php esc_attr_e( 'Submit', WL_MIM_DOMAIN ); ?>" class="btn btn-danger btn-sm bulk-action-btn bulk-action-btn-<?php echo esc_attr( $entity ); ?>" type="button">
			<?php esc_html_e( 'Apply', WL_MIM_DOMAIN ); ?>
		</button>
	</div>
</caption>
