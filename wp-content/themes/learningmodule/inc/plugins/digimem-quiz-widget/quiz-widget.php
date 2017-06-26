<?php

/**
 * Created by PhpStorm.
 * User: macke
 * Date: 2017-05-31
 * Time: 2:32 PM
 */
class Quiz_Widget extends WP_Widget {

	/**
	 * Sets up a new Text widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */
    private $saving = false;
	public function __construct() {
		$widget_ops  = array(
			'classname'                   => 'widget_quiz',
			'description'                 => __( 'Multiple choice question.' ),
			'customize_selective_refresh' => true,
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );
		parent::__construct( 'quiz', __( 'Quiz' ), $widget_ops, $control_ops );
	}

	public $numAnswers = 4;

	public function widget( $args, $instance ) {

		$question = apply_filters( 'widget_title', empty( $instance['question'] ) ? '' : $instance['question'], $instance, $this->id_base );


		echo $args['before_widget'];
		?>
        <div class="quizwidget" id="<?= $this->get_field_id( 'container' ) ?>">

			<?php if ( $instance['addText'] == 'true' ) {
				$text = $instance['text-box']; ?>
                <div class="quiz-widget-text-area"><p><?= $text ?></p></div>

				<?php
			}
			?>
            <div class="quiz-widget-question-area">
                <form>
				<?php
				if ( ! empty( $question ) ) {
					echo $args['before_title'] . $question . $args['after_title'];
				}

				for ( $i = 0; $i <= $this->numAnswers; $i ++ ) {
					if ( ! empty( $instance[ 'answer' . $i ] ) ) {
						$answer = $instance[ 'answer' . $i ];
						?>
                        <input type="radio" id="<?= $this->get_field_id( 'answer' )?>" name="<?= $this->get_field_name( 'answer' ) ?>"
                               value="<?= $i ?>"/><label style="display: inline-block" for="<?= $this->get_field_name( 'answer' )?>"><?= $answer ?></label><br/>
						<?php
					}
				}
				?>
                <br/>
                <button class="button"  id="<?= $this->get_field_id( 'submit' ) ?>">Submit</button>
                </form>
                <script>
                    //submission of quiz
                    jQuery('#<?=$this->get_field_id( 'submit' )?>').on('click', function (e) {
                        e.preventDefault();
                        let data = jQuery(this).parent().serializeArray();
                        let par = jQuery(this).parent().parent();
                        submitQuizWidget( <?=$this->number?>, par, data );
                    });
                </script>
            </div>
        </div>
		<?php
		echo $args['after_widget'];
	}


	public function update( $new_instance, $old_instance ) {
		$instance             = $old_instance;
		$instance['question'] = sanitize_text_field( $new_instance['question'] );

		for ( $i = 1; $i <= $this->numAnswers; $i ++ ) {
			$instance[ 'answer' . $i ] = $new_instance[ 'answer' . $i ];
		}

		$instance['isCorrect'] = $new_instance['isCorrect'];
		$instance['text-box']  = sanitize_text_field( $new_instance['text-box'] );
		$instance['addText']   = $new_instance['addText'];

        $this->saving = true;

		return $instance;
	}


	public function form( $instance ) {
		$question = isset( $instance['question'] ) ? $instance['question'] : '';
		$addText  = isset( $instance['addText']) ? $instance['addText'] : '';
		$textBox  = isset( $instance['text-box']) ? $instance['text-box'] : '';
		$instance = wp_parse_args( (array) $instance, array(
			'question'  => '',
			'answer1'   => '',
			'answer2'   => '',
			'answer3'   => '',
			'answer4'   => '',
			'isCorrect' => 1
		) );
		$question = sanitize_text_field( $instance['question'] );
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'question' ); ?>"><?php _e( 'Question:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'question' ); ?>"
                   name="<?php echo $this->get_field_name( 'question' ); ?>" type="text"
                   value="<?php echo esc_attr( $question ); ?>"/>
        </p>
		<?php for ( $i = 1; $i <= $this->numAnswers; $i ++ ) { ?>
            <p>
                <label for="<?= $this->get_field_id( 'answer' . $i ); ?>"><?php _e( 'Answer ' . $i . ':' ); ?></label>
                <input type="text" id="<?= $this->get_field_id( 'answer' . $i ) ?>"
                       name="<?= $this->get_field_name( 'answer' . $i ) ?>"
                       value="<?= esc_attr( $instance[ 'answer' . $i ] ) ?>"/>
                <input type="radio"
                       name="<?= $this->get_field_name( 'isCorrect' ) ?>" <?php if ( $instance['isCorrect'] == $i && ! empty( [ 'isCorrect' ] ) ) {
					echo 'checked="checked"';
				} ?> value="<?= $i ?>"/>
            </p>

		<?php }
		?>

        <div id="widget-<?= $this->number ?>-text-area">
            <input id="<?= $this->get_field_id( 'addText' ) ?>" name="<?= $this->get_field_name( 'addText' ) ?>"
                   type="checkbox" <?php if ($addText == 'true' ) {
				echo "checked='checked'";
			} ?> title="Additional Text" value="true"
                   onchange="addTextChanged('<?= $this->get_field_id( 'text-box' ) ?>')"/> Additional Text<br/>
            <textarea id="<?= $this->get_field_id( 'text-box' ) ?>" class="widefat"
                      name="<?= $this->get_field_name( 'text-box' ) ?>" <?php if ( $addText != 'true' ) {
				echo 'style="display: none"';
			} ?>><?= esc_attr( $textBox ) ?></textarea>
        </div>
        <br/><br/>
		<?php
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

function register_quiz_widget() {
	register_widget( 'Quiz_Widget' );
}

add_action( 'widgets_init', 'register_quiz_widget' );

require 'quiz-widget-ajax-functions.php';