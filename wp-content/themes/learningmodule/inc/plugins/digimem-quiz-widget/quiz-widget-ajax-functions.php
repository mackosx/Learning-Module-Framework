<?php
global $wpdb;
$quiz_table     = $wpdb->prefix . "quiz";
$question_table = $wpdb->prefix . "question";
$answer_table   = $wpdb->prefix . "answer";
/*
 * Functions to register AJAX calls
 */
$admin_action_array = array(
	'get_new_category',
	'check_correct_submission'
);
/*
 * Unauthenticated users
 */
$no_priv_action_array = array(
	'check_correct_submission'
);


/*
 * Functions for single-question Quiz Widget
 */
function check_correct_submission() {
	global $wpdb;
	$options      = $wpdb->prefix . 'options';
	$wid          = $_POST['wid'];
	$val          = $_POST['value'];
	$quiz_options = $wpdb->get_results( "SELECT option_value FROM $options WHERE option_name = 'widget_quiz'", ARRAY_A );
	$quiz         = $quiz_options[0]['option_value'];
	$quiz         = unserialize( $quiz );
	// Give msg based on selection
	if ( $val == $quiz['isCorrect'] ) {
		echo "<p class='correct'>Correct</p>";
	} else {
		echo "<p class='wrong'>Sorry, that was incorrect.</p>";
	}
	wp_die();
}

function get_new_category() {

	$wid       = $_POST['wid'];
	$catNum    = $_POST['cat'];
	$base_id   = 'widget-classification-game-' . $wid;
	$base_name = 'widget-classification-game[' . $wid . ']';
	?>

	<div id="<?= $base_id . '-cat' . $catNum . '-container'?>">
		<label for="<?= $base_id . '-cat' . $catNum ?>">Category:</label>
		<input type="text" id="<?= $base_id . '-cat' . $catNum ?>"
		       name="<?= $base_name . '[cat' . $catNum . ']' ?>"
		       value=""/>
		<button id="<?= $base_id . '-del-cat' . $catNum ?>" class="button" type="button"
		        onclick="deleteCategory(<?= $wid ?>,<?= $catNum ?>)">Delete Category
		</button>

		<br/>
		<div class="classification-images-group" id="<?= $base_id . '-cat' . $catNum . '-images' ?>">
			<label for="<?= $base_id . '-cat' . $catNum . '-images' ?>">Images: </label>


		</div>
		<br/>

		<button type="button" class="add-images-button button wp-media-buttons"
		        onclick="uploadMedia('<?= $base_id . '-cat' . $catNum . '-images' ?>', <?= $wid ?>, 'classification-game', <?= $catNum ?>)">
			Select Images
		</button>
		<br/><br/>
	</div>
	<div id="<?= $base_id . '-button-container' ?>">
		<button type="button" id="<?= $base_id . '-add-category-button' ?>" class="button-primary add-button"
		        onclick="addCategory(<?= $wid ?>,<?= $catNum + 1 ?>)"><span
				class="dashicons dashicons-plus"></span></button>
		<input type="hidden" id="<?= $base_id . '-numCat' ?>"
		       name="<?= $base_name . '[numCat]' ?>" value="<?= $catNum ?>"/>
	</div>
	<?php
	wp_die();
}

add_ajax_actions($admin_action_array, false);
add_ajax_actions($no_priv_action_array, true);