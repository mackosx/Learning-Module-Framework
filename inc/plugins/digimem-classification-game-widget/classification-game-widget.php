<?php


class Classification_Game_Widget extends WP_Widget {
	private $saving = false;

	/**
	 * Sets up a new Classification game widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */

	public function __construct() {
		global $widget_prefix;

		$widget_ops  = array(
			'classname'                   => 'widget_classification_game',
			'description'                 => __( 'A classification game with customizable categories and images.' ),
			'customize_selective_refresh' => true,
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );
		parent::__construct( 'classification-game', __( $widget_prefix . 'Classification Game' ), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {
		/*
		 * Classification Game Widget Scripts and Styles
		 */
		wp_enqueue_style( 'digimem-classification-game-style-public', get_theme_file_uri( '/inc/plugins/digimem-classification-game-widget/css/classification-game-widget-public.css' ), array() );
		wp_enqueue_script( 'digimem-classification-game-public', get_theme_file_uri( '/inc/plugins/digimem-classification-game-widget/js/classification-game-public.js' ), array(
			'jquery',
			'createjs'
		), '0.1', true );

		$title = "Classification Game";
		global $wpdb;
		// Send instance data and widget id to game script
		$data       = $instance;
		$data['id'] = $this->number;
		// Send the image locations to the script
		wp_localize_script( 'digimem-classification-game-public', 'data', $data );
		// Send the AJAX URL to the script ( for storing scores in db).
		wp_localize_script(
			"digimem-classification-game-public",
			"classificationGame",
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' )
			)
		);

		echo $args['before_widget'];
		?>
        <div class="classification-game-widget" id="<?= $this->get_field_id( 'container' ) ?>">
            <button class="button play-classification-game" type="button"
                    onclick="showClassificationGame(<?= $this->number ?>)">Play
                                                                           Game
            </button>
            <canvas style="display:none;" class="classification-game" id="myCanvas-<?= $this->number ?>" width="900"
                    height="600"></canvas>
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
			'numCat' => 2,
		) );
		if ( $instance['numCat'] <= 0 ) {
			$instance['numCat'] = 1;
		}
		?>
        <p><label><?php _e( 'Classification Game' ); ?></label></p>
        <div class="classification-widget">

			<?php
			for (
				$i = 0;
				$i < $instance['numCat'];
				$i ++
			) {

				$cat = 'cat' . ( $i + 1 );
				if ( ! isset( $instance[ $cat . '-images-count' ] ) || empty( $instance[ $cat . '-images-count' ] ) ) {
					$instance[ $cat . '-images-count' ] = 0;
				}
				if ( ! isset( $instance[ $cat ] ) ) {
					$instance[ $cat ] = '';
				}

				?>
                <div id="<?= $this->get_field_id( $cat . '-container' ) ?>">
                    <label for="<?= $this->get_field_id( $cat ) ?>">Category: </label>
                    <input type="text" id="<?= $this->get_field_id( $cat ) ?>"
                           name="<?= $this->get_field_name( $cat ) ?>"
                           value="<?= $instance[ $cat ] ?>"/>
					<?php
					if ( $instance['numCat'] > 1 ) {

						?>
                        <button id="<?= $this->get_field_id( 'del-' . $cat ) ?>" class="button" type="button"
                                onclick="deleteCategory(<?= $this->number ?>,<?= $i + 1 ?>)">Delete Category
                        </button>
					<?php } ?>
                    <br/>
                    <div class="classification-images-group"
                         id="<?= $this->get_field_id( $cat . '-images' ) ?>">
                        <label for="<?= $this->get_field_id( $cat . '-images' ) ?>">Images: </label>

						<?php
						for ( $j = 1; $j <= $instance[ $cat . '-images-count' ]; $j ++ ) {
							?>
                            <img id="<?= $this->get_field_id( $cat . '-images-' . $j ) ?>"
                                 src="<?= $instance[ $cat . '-images-' . $j ] ?>"/>
                            <input type="hidden" name="<?= $this->get_field_name( $cat . '-images-' . $j ) ?>"
                                   value="<?= $instance[ $cat . '-images-' . $j ] ?>"/>
							<?php
						}
						?>
                        <script>
							var images<?=$i + 1?> = jQuery('#<?=$this->get_field_id( $cat . '-images' )?>').children();
							for (let i = 1; i <= images<?=$i + 1?>.length / 2; i++) {
								jQuery('#widget-classification-game-cat<?=$i + 1?>-images-' + i).attr('src', jQuery('input[name="<?=$this->get_field_name( $cat . '-images-' )?>' + i + '"]').val());
							}
                        </script>
                        <input type="hidden" id="<?= $this->get_field_id( $cat . '-images-count' ) ?>"
                               name="<?= $this->get_field_name( $cat . '-images-count' ) ?>"
                               value="<?= $instance[ $cat . '-images-count' ] ?>"/>
                    </div>
                    <br/>
                    <button type="button" class="add-images-button button wp-media-buttons"
                            onclick="uploadMediaGame('<?= $this->get_field_id( $cat . '-images' ) ?>', <?= $this->number ?>, '<?= $this->id_base ?>', <?= $i + 1 ?>)">
                        Select Images
                    </button>
                    <br/>
                    <br/>
                </div>
			<?php } ?>

            <div id="<?= $this->get_field_id( 'button-container' ) ?>">
                <button type="button" id="<?= $this->get_field_id( 'add-category-button' ) ?>"
                        class="button-primary add-button"
                        onclick="addCategory(<?= $this->number ?>)"><span
                            class="dashicons dashicons-plus"></span></button>

                <input type="hidden" id="<?= $this->get_field_id( 'numCat' ) ?>"
                       name="<?= $this->get_field_name( 'numCat' ) ?>" value="<?= $instance['numCat'] ?>"/>
                <br/><br/>
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
function register_game_classification_widget() {
	register_widget( 'Classification_Game_Widget' );
}

add_action( 'widgets_init', 'register_game_classification_widget' );

require 'classification-game-ajax-functions.php';
