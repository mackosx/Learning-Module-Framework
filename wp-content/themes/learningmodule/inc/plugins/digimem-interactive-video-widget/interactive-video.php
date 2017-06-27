<?php
/**
 * Widget API: WP_Widget_Text class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

/**
 * Core class used to implement a Text widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class Interactive_Video_Widget extends WP_Widget {

	/**
	 * Sets up a new Text widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'interactive_video',
			'description'                 => __( 'Displays videos with interactive quizzes.' ),
			'customize_selective_refresh' => true,
		);
		// create db tables to store quiz content
		create_video_table();
		$control_ops = array( 'width' => 400, 'height' => 350 );
		parent::__construct( 'interactive', __( 'Interactive Video' ), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		$widget_url = ! empty( $instance['url'] ) ? $instance['url'] : '';
		$addText  = isset( $instance['addText']) ? $instance['addText'] : false;


		//active quiz

		global $wpdb;
		$quiz_table     = $wpdb->prefix . "quiz";
		$question_table = $wpdb->prefix . "question";
		$answer_table   = $wpdb->prefix . "answer";

		$activeQuiz   = $wpdb->get_results(
			"
		        SELECT quizId
		        FROM $quiz_table
		        WHERE isActive = 1 AND widgetId = $this->number
		        ", ARRAY_A );
		$hasQuiz      = false;
		$activeQuizId = - 1;

		if ( count( $activeQuiz ) >= 1 ) {
			$hasQuiz      = true;
			$activeQuizId = $activeQuiz[0]['quizId'];
		}


		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>

        <div class="videowidget">
			<?php
			if ( $addText == 'true' ) {
				$text = $instance['text-box'];
				echo "<div class='video-widget-text-area'><p>$text</p></div>";
			}
			?>
            <div id="widget-<?= $this->number ?>-video">
                <video width='1280' height='720' controls controlsList='nodownload'>
                    <source src="<?= $widget_url ?>">
                </video>
            </div>
			<?php
			if ( $hasQuiz ) {
				?>
                <script>
                    jQuery(document).ready(function () {
                        attachVideoListener(<?=$this->number?>, <?=$activeQuizId?>);


                    })
                </script>
				<?php
			}

			?>

        </div>
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current Text widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['url'] = $new_instance['url'];
		} else {
			$instance['url'] = wp_kses_post( $new_instance['url'] );
		}
		$instance['id']       = sanitize_text_field( $new_instance['id'] );
		$instance['text-box'] = sanitize_text_field( $new_instance['text-box'] );
		$instance['addText']  = $new_instance['addText'];

		return $instance;
	}

	/**
	 * Outputs the Text widget settings form.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 *
	 * @return void
	 */
	public function form( $instance ) {
		echo "<div class='interactive-video-container'>";

		global $wpdb;

		$title    = isset( $instance['title'] ) ? $instance['title'] : '';
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'url' => '', 'id' => '' ) );
		$title    = sanitize_text_field( $instance['title'] );
        $addText  = isset( $instance['addText']) ? $instance['addText'] : '';
		$textBox  = isset( $instance['text-box']) ? $instance['text-box'] : '';

		$urlId   = $this->get_field_id( 'url' );
		$videoId = $this->get_field_id( 'video' );
		// Title ?>
        <p>
            <label
                    for="<?php echo $this->get_field_id( 'title' ); ?>"
            >
				<?php _e( 'Title:' ); ?>
            </label>
            <input
                    class="widefat"
                    id="<?php echo $this->get_field_id( 'title' ); ?>"
                    name="<?php echo $this->get_field_name( 'title' ); ?>"
                    type="text"
                    value="<?php echo esc_attr( $title ); ?>"
            />
        </p>
        <button
                id='media-button'
                type='button'
                class='button'

                onclick='media(event, "<?= $urlId ?>", "<?= $videoId ?>")'
        ><span class="dashicons dashicons-editor-video"></span>Select Video
        </button>
		<?php // Hidden field to store video url ?>
        <input type="hidden"
               id="<?php echo $this->get_field_id( 'url' ); ?>"
               name="<?php echo $this->get_field_name( 'url' ); ?>"
               value="<?php echo $instance['url']; ?>"
        />
<?php
        if(isset($instance['url']) && $instance['url'] != ''){
            ?>
            <video
                    id="<?php echo $this->get_field_id( 'video' ) ?>"
                    width='640'
                    height='240'
                    controls
                    controlsList='nodownload'
                    src="<?=$instance['url']?>"
            >
            </video>
            <?php
        }
        ?>



        <br/>
        <div id="widget-<?= $this->number ?>-edit-buttons">

            <script>
                if(typeof getEditButtons == 'function'){
                    getEditButtons("<?=$this->number?>", '<?php echo isset($instance['url']) & $instance['url']!== '' ? 'true':  'false'?>')
                }
                jQuery(document).ready(function () {
                    getEditButtons("<?=$this->number?>", '<?php echo isset($instance['url']) & $instance['url']!=='' ? 'true':  'false'?>');
                });
            </script>
        </div>
        <div id="quiz-edit-<?= $this->number ?>">


        </div><br/>
        <div id="widget-<?= $this->number ?>-text-area">
            <input id="<?= $this->get_field_id( 'addText' ) ?>" name="<?= $this->get_field_name( 'addText' ) ?>"
                   type="checkbox" <?php if ( $addText == 'true' ) {
				echo "checked='checked'";
			} ?> title="Additional Text" value="true"
                   onchange="addTextChanged('<?= $this->get_field_id( 'text-box' ) ?>')"/> Additional Text<br/>
            <textarea id="<?= $this->get_field_id( 'text-box' ) ?>" class="widefat"
                      name="<?= $this->get_field_name( 'text-box' ) ?>" <?php if ( $addText != 'true' ) {
				echo 'style="display: none"';
			} ?>><?= esc_attr( $textBox  ) ?></textarea>
        </div>

        <?php
        echo "</div>";
	}

}


function register_interactive_video_widget() {
	register_widget( 'Interactive_Video_Widget' );
}

add_action( 'widgets_init', 'register_interactive_video_widget' );

/*
 * Check if db table exists, if not create it
 *
 * Creates the tables to store the quiz information
 */
function create_video_table() {
	require_once( ABSPATH . 'wp-config.php' );
	$connection = mysqli_connect( DB_HOST, DB_USER );
	mysqli_select_db( $connection, DB_NAME );
	if ( mysqli_connect_errno() ) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$quiz_table      = $wpdb->prefix . "quiz";
	$sql1            = "CREATE TABLE IF NOT EXISTS $quiz_table (
				quizId int NOT NULL AUTO_INCREMENT,
				title varchar(100),
				widgetId int,
				isActive boolean default false,
				PRIMARY KEY  (quizId)
			) $charset_collate;";
	$question_table  = $wpdb->prefix . "question";
	$sql2            = "CREATE TABLE IF NOT EXISTS $question_table(
				qid int NOT NULL,
				text varchar(1000) default null,
				quizId int,
				time decimal(6, 2) default null,
				PRIMARY KEY  (qid, quizId),
				FOREIGN KEY (quizId) REFERENCES $quiz_table (quizId) ON DELETE CASCADE ON UPDATE CASCADE
			) $charset_collate;";
	$answer_table    = $wpdb->prefix . "answer";
	$sql3            = "CREATE TABLE IF NOT EXISTS $answer_table(
				aid int NOT NULL,
				text varchar(1000) default null,
				isCorrect boolean,
				qid int,
				quizId int,
				PRIMARY KEY  (aid, qid, quizId),
				FOREIGN KEY (qid) REFERENCES $question_table (qid) ON DELETE CASCADE ON UPDATE CASCADE,
				FOREIGN KEY (quizId) REFERENCES $quiz_table (quizId) ON DELETE CASCADE ON UPDATE CASCADE
			) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( array( $sql1 ) );
	dbDelta( array( $sql2 ) );
	dbDelta( array( $sql3 ) );



}
require 'interactive-video-ajax-functions.php';
