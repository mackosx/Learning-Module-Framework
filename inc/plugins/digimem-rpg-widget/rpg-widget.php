<?php

/*
 * RPG Widget that allows the user to create interactive settings
 * for students to navigate and learn through experience and real
 * world examples.
 */

class RPG_Widget extends WP_Widget {

	/**
	 * Sets up a new RPG widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	private $saving = false;

	public function __construct() {
		$widget_ops  = array(
			'classname'                   => 'rpg_widget',
			'description'                 => __( 'Interactive Role-playing Storylines.' ),
			'customize_selective_refresh' => true,
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );
		parent::__construct( 'rpg', __( 'RPG' ), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		wp_enqueue_script( 'digimem-rpg-widget-display', get_theme_file_uri( '/inc/plugins/digimem-rpg-widget/js/rpg-display.js' ), array( 'vue', 'adjacency-list' ), '1.0', true );
		wp_enqueue_style( 'digimem-rpg-widget-style', get_theme_file_uri( '/inc/plugins/digimem-rpg-widget/css/display-style.css' ) );
        wp_localize_script(
		"digimem-rpg-widget-display",
		"rpg",
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' )
		)
	);
		$options = get_option('rpg_options');
		$data = json_decode($options['data'], true);
		$passages = $data['data'];
		$scoreTotal = 0;
		$startPassage = null;
		$foundStart = false;
		foreach($passages['vertices'] as $passage){
		    if($passage['data']['isStart'] == 'true'){
		        $startPassage = $passage;
		        $foundStart = true;
		        break;
            }
        }
		echo $args['before_widget'];
		?>
        <div class="rpgwidget" id="<?= $this->get_field_id( 'container' ) ?>">
            <button id="<?=$this->get_field_id('show-button')?>">Start: <?=$data['title']?></button>
            <div class="story-area" id="<?=$this->get_field_id('story-area')?>" style="display: none">

            <?php if(!$foundStart) {
	            echo '<p>No starting point was found for this story.</p>';
            } else { ?>
            <p><?=$data['desc']?></p>
             <passage :current="currentPassage" :change="changeCurrentPassage" :passages="passages" :score="score" :again="playAgain"></passage>
            <?php } ?>
        </div>

		<?php
		wp_localize_script('digimem-rpg-widget-display', 'rpgData', array($this->get_field_id('story-area'), $startPassage, $passages, $this->get_field_id('show-button'), $this->get_field_id('story-area')));

		echo $args['after_widget'];
	}


	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		$this->saving = true;

		return $instance;
	}


	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';

		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',

		) );
		$title    = sanitize_text_field( $instance['title'] );
		$options = get_option('rpg_options');
		$data = json_decode($options['data'], true);
        $title = $data['title'];
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                   value="<?php echo esc_attr( $title ); ?>"/>
        </p>
        <p>To edit the story, go the the <a href="<?=admin_url('?page=rpg')?>">RPG Creator page</a>.</p>
        <p><?php echo $title?></p>
        <p><?=$data['desc']?></p>
        <br/><br/>
		<?php
		/* Display saving message is widget was just saved. */
		if ( $this->saving ) {
			?>
            <p class="saved-quiz" id="<?= $this->get_field_id( 'saved' ) ?>">Your settings were successfully saved.</p>
            <script>
				jQuery('#<?=$this->get_field_id( 'saved' )?>').delay(3000).fadeOut(1500);
            </script>
			<?php
			$this->saving = false;
		}

	}
}

function register_rpg_widget() {
	register_widget( 'RPG_Widget' );
}

add_action( 'widgets_init', 'register_rpg_widget' );

