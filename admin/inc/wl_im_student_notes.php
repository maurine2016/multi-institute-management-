<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );
require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_StudentHelper.php' );

global $wpdb;

$student      = WL_MIM_StudentHelper::get_student();
$institute_id = $student->institute_id;
$notes        = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_notes WHERE institute_id = $institute_id AND batch_id = {$student->batch_id} AND is_active = 1 ORDER BY id DESC" );
?>
<div class="wl_im_container wl_im">
    <div class="row justify-content-md-center mx-auto">
        <div class="card col-xs-12 col-md-10 col-sm-12">
            <div class="card-header bg-primary">
                <h4 class="text-white"><?php esc_html_e( "Study Material", WL_MIM_DOMAIN ); ?></h4>
            </div>
            <div class="card-body">
				<?php if ( count( $notes ) ) { ?>
                    <table class="table table-hover table-striped table-bordered" id="student-note-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( "Title", WL_MIM_DOMAIN ); ?></th>
                                <th><?php esc_html_e( "Date", WL_MIM_DOMAIN ); ?></th>
                                <th><?php esc_html_e( "Added On", WL_MIM_DOMAIN ); ?></th>
                                <th><?php esc_html_e( "Action", WL_MIM_DOMAIN ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
						<?php
                        foreach ( $notes as $note ) {
							$id = $note->id; ?>
                            <tr>
                                <td><?php echo esc_html( $note->title ); ?></td>
                                <td><?php echo date_format( date_create( $note->notes_date ), "d-m-Y" ); ?></td>
                                <td><?php echo date_format( date_create( $note->created_at ), "d-m-Y g:i A" ); ?></td>
                                <td>
                                    <a class="mr-3" href="#view-student-note" data-keyboard="false" data-backdrop="static" data-toggle="modal" data-id="<?php echo esc_attr( $id ); ?>"><i class="fa fa-search"></i></a>
                                </td>
                            </tr>
						<?php } ?>
                        </tbody>
                    </table>
				<?php } else { ?>
                    <div class="alert alert-warning"><?php esc_html_e( "There is no note.", WL_MIM_DOMAIN ); ?></div>
				<?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- view student note modal -->
<div class="modal fade" id="view-student-note" tabindex="-1" role="dialog" aria-labelledby="view-student-note-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="view-student-note-label"><?php esc_html_e( 'View Note', WL_MIM_DOMAIN ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pr-4 pl-4" id="view_student_note"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Close', WL_MIM_DOMAIN ); ?></button>
            </div>
        </div>
    </div>
</div><!-- end - view student note modal -->