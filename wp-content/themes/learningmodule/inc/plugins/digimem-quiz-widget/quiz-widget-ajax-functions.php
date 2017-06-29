<?php
global $wpdb;
$quiz_table     = $wpdb->prefix . "quiz";
$question_table = $wpdb->prefix . "question";
$answer_table   = $wpdb->prefix . "answer";
/*
 * Functions to register AJAX calls
 */
$admin_action_array = array(
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



add_ajax_actions($admin_action_array, false);
add_ajax_actions($no_priv_action_array, true);