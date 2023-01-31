<?php
defined( 'ABSPATH' ) || die();

require_once( WL_MIM_PLUGIN_DIR_PATH . '/admin/inc/helpers/WL_MIM_Helper.php' );

class WL_MIM_Noticeboard_Widget extends WP_Widget {
	/* Widget information set up */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'wl_min_noticeboard_widget',
			'description' => esc_html__( 'Display institute notices.', WL_MIM_DOMAIN )
		);
		parent::__construct( 'wl_min_noticeboard_widget', esc_html__( 'Institute Noticeboard', WL_MIM_DOMAIN ), $widget_options );
	}

	/* Widget output */
	public function widget( $args, $instance ) {
		global $wpdb;

		$title              = apply_filters( 'widget_title', $instance['title'] );
		$institute_id       = $instance['institute'];
		$notices_number     = $instance['notices_number'];
		$animation_interval = $instance['animation_interval'];
		$max_height         = $instance['max_height'];
		$min_height         = $instance['min_height'];

		/* Get notices */
		$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wl_min_notices WHERE is_deleted = 0 AND is_active = 1 AND institute_id = $institute_id ORDER BY priority ASC, id DESC LIMIT $notices_number" );

		$allowed_html = wp_kses_allowed_html( 'post' );
		echo wp_kses( $args['before_widget'] . $args['before_title'] . $title . $args['after_title'], $allowed_html );
		if ( count( $data ) > 0 ) {
			$css = ".wlim-noticeboard-section {
                        max-height: 380px;
                        overflow: hidden;
                    }
                    .wlim-noticeboard {
                        overflow: hidden;
                        top: 6em;
                        position: relative;
                        box-sizing: border-box;
                    }
                    .wlim-noticeboard:hover {
                        animation-play-state: paused;
                    }
                    .wlim-noticeboard li {
                        margin-bottom: 5px;
                    }
                    @keyframes marquee {
                        0% {
                            top: 8em
                        }
                        100% {
                            top: -8em
                        }
                    }
                    .wlim-noticeboard-new {
                        display: inline;
                        margin-left: 4px;
                    }
                    .wlim-noticeboard-section {
                        max-height: {$max_height}px;
                        min-height: {$min_height}px;
                    }
                    .wlim-noticeboard {
                        animation: marquee {$animation_interval}s linear infinite;
                    }";
			wp_register_style( 'wl-mim-widget-style', false );
			wp_enqueue_style( 'wl-mim-widget-style' );
			wp_add_inline_style( 'wl-mim-widget-style', $css ); ?>
            <div class="wlim-noticeboard-section">
                <ul class="wlim-noticeboard">
					<?php
					foreach ( $data as $key => $row ) {
						if ( $row->link_to == 'url' ) {
							$link_to = $row->url;
						} elseif ( $row->link_to == 'attachment' ) {
							$link_to = wp_get_attachment_url( $row->attachment );
						} else {
							$link_to = '#';
						}
						?>
                        <li>
                            <a target="_blank" href="<?php echo esc_url( $link_to ); ?>"><?php echo stripcslashes( $row->title ); ?></a>
							<?php
							if ( $key < 3 ) { ?>
                                <img class="wlim-noticeboard-new" src="<?php echo WL_MIM_PLUGIN_URL . 'assets/images/newicon.gif'; ?>">
								<?php
							} ?>
                        </li>
						<?php
					} ?>
                </ul>
            </div>
			<?php
		} else { ?>
            <p><?php esc_html_e( 'There is no notice.', WL_MIM_DOMAIN ); ?></p>
			<?php
		}	echo wp_kses( $args['after_widget'], $allowed_html );
	}

	/* Widget options form */
	public function form( $instance ) {
		$wlim_active_institutes = WL_MIM_Helper::get_institutes();
		$fields                 = array(
			'title'              => esc_html__( 'Noticeboard', WL_MIM_DOMAIN ),
			'institute'          => 0,
			'notices_number'     => 6,
			'animation_interval' => 8,
			'max_height'         => 380,
			'min_height'         => 100,
		);
		$instance               = wp_parse_args( (array) $instance, $fields );
		$title              = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$institute          = ! empty( $instance['institute'] ) ? $instance['institute'] : '';
		$notices_number     = ! empty( $instance['notices_number'] ) ? $instance['notices_number'] : '';
		$animation_interval = ! empty( $instance['animation_interval'] ) ? $instance['animation_interval'] : '';
		$max_height         = ! empty( $instance['max_height'] ) ? $instance['max_height'] : 380;
		$min_height         = ! empty( $instance['min_height'] ) ? $instance['min_height'] : 100; ?>
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'institute' ) ); ?>"><?php esc_html_e( "Select Institute", WL_MIM_DOMAIN ); ?>:</label>
            <select id="<?php echo esc_attr( $this->get_field_id( 'institute' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'institute' ) ); ?>">
                <option value="">-------- <?php esc_html_e( "Select Institute", WL_MIM_DOMAIN ); ?>--------</option>
				<?php
				if ( count( $wlim_active_institutes ) > 0 ) {
					foreach ( $wlim_active_institutes as $active_institute ) { ?>
                        <option <?php selected( esc_attr( $institute ), $active_institute->id, true ); ?> value="<?php echo esc_attr( $active_institute->id ); ?>"><?php echo esc_html( $active_institute->name ); ?></option>
						<?php
					}
				} ?>
            </select>
        </p>
		<p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', WL_MIM_DOMAIN ); ?>:</label><br>
            <input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
        </p>
		<p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'notices_number' ) ); ?>"><?php esc_html_e( 'Number of Notices', WL_MIM_DOMAIN ); ?>:</label><br>
            <input type="number" id="<?php echo esc_attr( $this->get_field_id( 'notices_number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'notices_number' ) ); ?>" value="<?php echo esc_attr( $notices_number ); ?>"/><br>
        </p>
		<p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'animation_interval' ) ); ?>"><?php esc_html_e( 'Animation Interval (in seconds)', WL_MIM_DOMAIN ); ?>:</label><br>
            <input type="number" id="<?php echo esc_attr( $this->get_field_id( 'animation_interval' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'animation_interval' ) ); ?>" value="<?php echo esc_attr( $animation_interval ); ?>"/><br>
        </p>
		<p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'max_height' ) ); ?>"><?php esc_html_e( 'Maximum Height (in pixels)', WL_MIM_DOMAIN ); ?>:</label><br>
            <input type="number" id="<?php echo esc_attr( $this->get_field_id( 'max_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'max_height' ) ); ?>" value="<?php echo esc_attr( $max_height ); ?>"/><br>
        </p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'min_height' ) ); ?>"><?php esc_html_e( 'Minimum Height (in pixels)', WL_MIM_DOMAIN ); ?>:</label><br>
			<input type="number" id="<?php echo esc_attr( $this->get_field_id( 'min_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'min_height' ) ); ?>" value="<?php echo esc_attr( $min_height ); ?>"/><br>
        </p><?php
	}

	/* Process widget options on save */
	public function update( $new_instance, $old_instance ) {
		$instance                       = $old_instance;
		$instance['title']              = strip_tags( $new_instance['title'] );
		$instance['institute']          = intval( strip_tags( $new_instance['institute'] ) );
		$instance['notices_number']     = intval( strip_tags( $new_instance['notices_number'] ) );
		$instance['animation_interval'] = intval( strip_tags( $new_instance['animation_interval'] ) );
		$instance['max_height']         = intval( strip_tags( $new_instance['max_height'] ) );
		$instance['min_height']         = intval( strip_tags( $new_instance['min_height'] ) );

		if ( empty( $instance['animation_interval'] ) ) {
			$instance['animation_interval'] = 8;
		}
		return $instance;
	}
}
?>
