<?php


class Montage_Widget extends WP_Widget {
	private $saving = false;

	/**
	 * Sets up a new montage game widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */

	public function __construct() {
		$widget_ops  = array(
			'classname'                   => 'widget_montage',
			'description'                 => __( 'A montage creation widget.' ),
			'customize_selective_refresh' => true,
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );
		parent::__construct( 'montage', __( 'Montage' ), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {
		/*
		 * montage Game Widget Scripts and Styles
		 */
		wp_enqueue_script( 'digimem-montage-public', get_theme_file_uri( '/inc/plugins/digimem-montage-widget/js/montage-public.js' ), array(
			'jquery',
			'fabricjs'
		), '0.1', true );

		$title = "Montage Creation";
		global $wpdb;
		// Send instance data and widget id to game script
		$data       = $instance;
		$data['id'] = $this->number;
		wp_localize_script( 'digimem-montage-public', 'montageData', $data );


		echo $args['before_widget'];
		?>
        <div class="montage-widget" id="<?= $this->get_field_id( 'container' ) ?>">
			<?php
			if ( isset( $instance['description'] ) && $instance['description'] != '' ) {
				?>
                <div class="montage-text">
                    <p><?= $instance['description'] ?></p>
                </div>

				<?php
			}
			?>
            <div class="montage-drawing-area">
                <div class="montage-controls">
                    <button id="brush-button">Select Tool</button>
                    <input type="color" id="color-button" value="#4295f4">
                    <input type="range" min="1" value="5" max="50" step="1" id="width-button">
                    <button id="delete-button">Delete Selection</button>
                    <button id="finish-button">Done</button>
                    <div class="montage-images" id="<?= $this->get_field_id( 'image-container' ) ?>">

                    </div>
                    <div class="montage-shapes">
                        <button id="oval-button">Oval</button>
                        <button id="rectangle-button">Rectangle</button>
                    </div>
                </div>
                <canvas class="montage" id="montage-canvas-<?= $this->number ?>" width="600" height="500"></canvas>
            </div>
        </div>
		<?php


		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $new_instance;
		for ( $i = 1; $i <= $instance['numCat']; $i ++ ) {
			$instance[ 'cat' . $i ] = sanitize_text_field( $new_instance[ 'cat' . $i ] );
		}
		$this->saving = true;

		return $instance;
	}

	public function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array(
			'numCat'      => 2,
			'description' => '',
		) );
		if ( $instance['numCat'] <= 0 ) {
			$instance['numCat'] = 1;
		}
		?>
        <p><label><?php _e( 'Montage Creation' ); ?></label></p>
        <div class="montage-widget">

			<?php


			$cat = 'cat';
			if ( ! isset( $instance['image-count'] ) || empty( $instance['image-count'] ) ) {
				$instance['image-count'] = 0;
			}

			?>
            <div id="<?= $this->get_field_id( 'container' ) ?>">
                <div class="montage-images-group"
                     id="<?= $this->get_field_id( 'images' ) ?>">
                    <label for="<?= $this->get_field_id( 'images' ) ?>">Images: </label>

					<?php
					// Display images and their hidden input fields to store img path in
					for ( $j = 1; $j <= $instance['image-count']; $j ++ ) {
						?>
                        <img id="<?= $this->get_field_id( 'image-' . $j ) ?>"
                             src="<?= $instance[ 'image-' . $j ] ?>"/>
                        <input type="hidden" name="<?= $this->get_field_name( 'image-' . $j ) ?>"
                               value="<?= $instance[ 'image-' . $j ] ?>"/>
						<?php
					}
					?>
                    <script>
                        //                        var images = jQuery('#<?//=$this->get_field_id( 'images' )?>//').children();
                        //                        // Divide by 2 since there are 2 field for every image (one <img> and one hidden <input>
                        //                        for (let i = 1; i <= images.length / 2; i++) {
                        //                            jQuery('#<?//=$this->get_field_id('')?>//).attr('src', jQuery('input[name="<?//=$this->get_field_name( 'image-' )?>//' + i + '"]').val());
                        //                        }
                    </script>
                    <input type="hidden" id="<?= $this->get_field_id( 'image-count' ) ?>"
                           name="<?= $this->get_field_name( 'image-count' ) ?>"
                           value="<?= $instance['image-count'] ?>"/>
                </div>
                <br/>
                <button type="button" class="add-images-button button wp-media-buttons"
                        onclick="uploadMedia(<?= $this->number ?>, '<?= $this->id_base ?>')">
                    Select Images
                </button>
                <br/>
                <br/>
                <label for="<?= $this->get_field_id( 'description' ) ?>">Description </label>
                <textarea class="widefat" id="<?= $this->get_field_id( 'description' ) ?>"
                          name="<?= $this->get_field_name( 'description' ) ?>"><?= $instance['description'] ?></textarea>
            </div>

        </div>

		<?php
		// Display save message
		if ( $this->saving ) {
			?>
            <p class="saved" id="<?= $this->get_field_id( 'saved' ) ?>">Your settings were successfully saved.</p>
            <script>
                jQuery('#<?=$this->get_field_id( 'saved' )?>').delay(3000).fadeOut(1500);
            </script>
			<?php
			$this->saving = false;
		}
	}

}

// Register Widget
function register_game_montage_widget() {
	register_widget( 'Montage_Widget' );
}

add_action( 'widgets_init', 'register_game_montage_widget' );
