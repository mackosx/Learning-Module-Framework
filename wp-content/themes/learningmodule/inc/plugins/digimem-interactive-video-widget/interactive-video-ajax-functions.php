<?php

/**
 * AJAX Functions for quiz video creation
 */

/* Globals for Interactive Video Widget DB table names*/
global $wpdb;
$quiz_table     = $wpdb->prefix . "quiz";
$question_table = $wpdb->prefix . "question";
$answer_table   = $wpdb->prefix . "answer";
/*
 * Functions to register AJAX calls
 */
$admin_action_array = array(
	'get_edit_buttons',
	'get_quiz_form',
	'get_question_form',
	'insert_question',
	'edit_quiz_form',
	'load_quiz',
	'set_active_quiz',
	'delete_quiz',
	'update_quiz_select',
	'question_edit',
	'answer_video_question',
	'create_quiz',
	'update_quiz',
	'update_question'
);
/*
 * Unauthenticated users
 */
$no_priv_action_array = array(
	'load_quiz',
	'answer_video_question'
);


function get_quiz_form() {
	global $wpdb, $question_table, $quiz_table, $answer_table;
	if ( isset( $_POST['widgetId'] ) && ! empty( $_POST['widgetId'] ) && isset( $_POST['quizId'] ) && ! empty( $_POST['quizId'] ) ) {
		$quizId       = $_POST['quizId'];
		$widgetId     = $_POST['widgetId'];
		$question_num = 0;

		?>
        <div id="quiz-<?= $quizId ?>-title-area">
            <p>Pick a title for your quiz.</p>
            <label for="quiz-<?= $quizId ?>-title">Quiz Title: </label>
            <input type="text" id="quiz-<?= $quizId ?>-title" max="100"/>
            <button class="button" type="button" id="widget-<?= $widgetId ?>-title-next"
                    onclick="getQuestionForm(<?= $quizId ?>, <?= $widgetId ?>)">Next
            </button>
        </div>


		<?php
	}
	wp_die();
}

/**
 * Outputs the form for creating a question
 */
function get_question_form() {
	global $wpdb, $question_table, $quiz_table, $answer_table;
	if ( isset( $_POST['widgetId'] ) && ! empty( $_POST['widgetId'] ) && isset( $_POST['quizId'] ) && ! empty( $_POST['quizId'] ) ) {

		$quizId   = $_POST['quizId'];
		$widgetId = $_POST['widgetId'];
		if ( isset( $_POST['title'] ) ) {
			$wpdb->update( $quiz_table,
				array(
					'title' => $_POST['title']
				),
				array(
					'quizId' => $quizId
				)
			);
		}
		// calculate question number
		$questions     = $wpdb->get_results( $wpdb->prepare(
			"SELECT qid FROM $question_table WHERE quizId = %d",
			$quizId
		), ARRAY_A );
		$questionCount = count( $questions ) + 1;
		// echo form for question

		?>
        <form method="post" id="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-form"
              class="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-form">

			<?php get_question_table( 'Add Question', $quizId, $questionCount, array() ) ?>
<?php// Submits the form for the question ?>
            <input class="button" type="submit" value="Add and Continue"/>
            <button type="button" class="button"
                    onclick="finishQuiz(<?= $widgetId ?>, <?= $quizId ?>, <?= $questionCount ?>)">Finish
            </button>
        </form>
		<?php
	}
	wp_die();
}

function insert_question() {
	global $wpdb, $question_table, $quiz_table, $answer_table;
	$quizId   = $_POST['quizId'];
	$widgetId = $_POST['widgetId'];
	parse_str( $_POST['form'], $form );
	$questionNum = $_POST['questionNumber'];
	$baseId      = 'quiz-' . $quizId . '-question-' . $questionNum;

	$wpdb->insert( $question_table, array(
		'qid'    => $questionNum,
		'text'   => $form["$baseId-text"],
		'quizId' => $quizId,
		'time'   => $form["$baseId-time"]
	) );
	for ( $i = 1; $i <= 4; $i ++ ) {
		if ( isset( $form["$baseId-answer-$i"] ) and $form["$baseId-answer-$i"] != '' ) {
			'answer-' . $i == $form["$baseId-correct"] ? $isCorrect = 1 : $isCorrect = 0;
			$wpdb->insert( $answer_table, array(
				'qid'       => $questionNum,
				'aid'       => $i,
				'quizId'    => $quizId,
				'isCorrect' => $isCorrect,
				'text'      => $form["$baseId-answer-$i"]
			) );
		}
	}

	wp_die();
}

function edit_quiz_form() {
	global $wpdb, $question_table, $quiz_table, $answer_table;
	$quizId    = $_POST['quizId'];
	$widgetId  = $_POST['widgetId'];
	$questions = $wpdb->get_results( $wpdb->prepare(
		"SELECT text, qid FROM $question_table WHERE quizId = %d",
		$quizId
	), ARRAY_A
	);
	if ( count( $questions ) > 0 ) {
		echo "<label for='widget-$widgetId-edit-question-select'>Select Question to Edit: </label>";
		echo "<select id='widget-$widgetId-edit-question-select'>";// output select list of quiz questions
		echo "<option value=''>Select A Question</option>";
		foreach ( $questions as $q ) {
			echo "<option value='" . $q['qid'] . "'>" . $q['qid'] . ". " . $q['text'] . "</option>";
		}
		echo '</select> or ';
		?>
        <script>
            jQuery('#widget-<?=$widgetId?>-edit-question-select').on('change', function () {
                if (jQuery(this).val() != '')
                    getQuestionEditForm(jQuery(this).val(), <?=$quizId?>, <?=$widgetId?>);
            })
        </script>
		<?php
	} else {
		echo "<p>This quiz has no questions yet.</p>";
	}
	echo '<button type="button" class="button" onclick="getQuestionForm(' . $quizId . ',' . $widgetId . ')">Add a Question</button>';


	wp_die();

}

function load_quiz() {
	global $wpdb, $question_table, $quiz_table, $answer_table;

	$quizId   = $_POST['quizId'];
	$widgetId = $_POST['widgetId'];

	$quiz = $wpdb->get_results(
		"
            SELECT q.qid, q.text as question, q.time, a.text as answer, isCorrect
            FROM $question_table as q, $quiz_table as qz, $answer_table as a
            WHERE q.quizId = qz.quizId
            AND qz.quizId = $quizId
            AND a.quizId = qz.quizId
            AND a.qid = q.qid
            ORDER BY q.time
            ", ARRAY_A
	);

	$questions = $wpdb->get_results( "SELECT qid, text as question, time FROM $question_table WHERE quizId = $quizId", ARRAY_A );
	for ( $i = 0; $i < count( $questions ); $i ++ ) {
		$answers = $wpdb->get_results( "SELECT text, isCorrect FROM $answer_table WHERE quizId = $quizId AND qid = " . $questions[ $i ]['qid'], ARRAY_A );

		$answer_arr = array();
		for ( $j = 0; $j < count( $answers ); $j ++ ) {
			$answer_arr[ 'answer' . ( $j + 1 ) ] = $answers[ $j ];
		}
		$questions[ $i ]['answers'] = $answer_arr;

	}

	echo json_encode( $questions );
	wp_die();

}

function set_active_quiz() {
	global $wpdb, $question_table, $quiz_table, $answer_table;

	$quizId   = $_POST['quizId'];
	$widgetId = $_POST['widgetId'];


	//make sure all other widgets quizzes are inactive

	$wpdb->update( $quiz_table, array(
		'isActive' => 0
	), array(
		'widgetId' => $widgetId
	) );
	//then set quiz to be active
	$wpdb->update( $quiz_table, array(
		'isActive' => 1
	), array(
		'quizId' => $quizId
	) );

	echo '<p id="widget-' . $widgetId . '-active-msg" class="set-active">Quiz was set as active</p>';
	wp_die();
}

function delete_quiz() {
	global $wpdb, $question_table, $quiz_table, $answer_table;

	$quizId   = $_POST['quizId'];
	$widgetId = $_POST['widgetId'];

	// delete quiz
	$wpdb->delete( $quiz_table, array(
		'quizId'   => $quizId,
		'widgetId' => $widgetId
	) );
	// return updated select list
	$rows = $wpdb->get_results(
		"
                SELECT quizId, title
                FROM $quiz_table
                WHERE widgetId = $widgetId
                ",
		ARRAY_A );
	echo '<option value="">Select A Quiz</option>';
	foreach ( $rows as $quiz ) {
		echo '<option value="' . $quiz['quizId'] . '">' . $quiz['title'] . '</option>';
	}

	wp_die();
}

function update_quiz_select() {
	global $wpdb, $question_table, $quiz_table, $answer_table;

	$quizId   = $_POST['quizId'];
	$widgetId = $_POST['widgetId'];

	$rows = $wpdb->get_results(
		"
                SELECT quizId, title
                FROM $quiz_table
                WHERE widgetId = $widgetId
                ",
		ARRAY_A );
	echo '<option>Select A Quiz</option>';
	foreach ( $rows as $quiz ) {
		echo '<option value="' . $quiz['quizId'] . '">' . $quiz['title'] . '</option>';
	}

	wp_die();
}

function question_edit() {
	global $wpdb, $question_table, $quiz_table, $answer_table;

	$quizId        = $_POST['quizId'];
	$widgetId      = $_POST['widgetId'];
	$questionCount = $_POST['questionNumber'];

	$question = $wpdb->get_results(
		"
	        SELECT q.text as question, a.text as answer, a.isCorrect, q.time
	        FROM $question_table as q, $answer_table as a
	        WHERE q.quizId = $quizId AND q.qid = $questionCount AND a.qid = q.qid AND q.quizId = a.quizId
	        ", ARRAY_A );
	?>
    <form id="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-form-edit" method="post">
		<?php get_question_table( 'Edit Question', $quizId, $questionCount, $question, $question[0]['time'] ) ?>
        <input id="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-save-edit" type="submit" class="button green"
               value="Save Edit"/>

    </form>
    <script>
        jQuery("#quiz-<?= $quizId ?>-question-<?= $questionCount ?>-form-edit").on('submit', function (e) {
            e.preventDefault();
            updateQuestion(<?=$widgetId?>,<?=$quizId?>,<?=$questionCount?>);
        });
    </script>
	<?php
	wp_die();

}

// Help function to output table to for editing and creating questions
function get_question_table( $title, $quizId, $questionCount, $question, $time = "0.00" ) {
    $correctSet = false;
	if ( ! empty( $question[0] ) ) {
		for ( $i = 1; $i <= 4; $i ++ ) {
			if ( $question[ $i - 1 ]['isCorrect'] == 1 ) {
				$correctSet = true;
			}
		}
	}
	if(!$correctSet)
        $question[0]['isCorrect'] = 1;
	?>
    <table>
        <tr>
            <th><?= $title ?></th>
        </tr>
        <tr>
            <td><label for="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-time">Time: </label></td>
            <td><input type="number" step="any" min="0" id="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-time"
                       name="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-time" value="<?= $time ?>"/></td>
            <td></td>
        </tr>
        <tr>
            <td><label for="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-text">*Enter Question Text: </label>
            </td>
            <td><input id="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-text"
                       name="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-text"
                       value="<?= $question[0]['question'] ?>" required/></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>Correct</td>
        </tr>
		<?php
		if ( ! empty( $question[0] ) ) {
			for ( $i = 1; $i <= 4; $i ++ ) {
				?>
                <tr>
                    <td>
                        <label for="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-answer-<?= $i ?>"><?php if( $question[ $i - 1 ]['isCorrect'] == 1 ){ ?>*<?php } ?>Answer <?= $i ?>:</label></td>
                    <td><input type="text"
                               id="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-answer-<?= $i ?>"
                               name="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-answer-<?= $i ?>"
                               value="<?= $question[ $i - 1 ]['answer'] ?>"/></td>
                    <td><input type="radio" name="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-correct"
					           <?php if ( $question[ $i - 1 ]['isCorrect'] == 1 ){ ?>checked="checked" <?php } ?>
                               value="answer-<?= $i ?>"/></td>
                </tr>
				<?php
			}
		} else {
			for ( $i = 1; $i <= 4; $i ++ ) {

				?>
                <tr>
                    <td>
                        <label for="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-answer-<?= $i ?>">Answer <?= $i ?>
                            : </label></td>
                    <td><input id="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-answer-<?= $i ?>"
                               name="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-answer-<?= $i ?>"
                               value=""/></td>
                    <td><input type="radio" name="quiz-<?= $quizId ?>-question-<?= $questionCount ?>-correct"
					           <?php if ( $i == 1 ){ ?>checked="checked" <?php } ?>
                               value="answer-<?= $i ?>"/></td>
                </tr>

				<?php
			}
		}


		?>

        <tr>
            <input type="hidden" id="quiz-<?= $quizId ?>-question-number" name="quiz-<?= $quizId ?>-question-number"
                   value="<?= $questionCount ?>"/>
        </tr>
    </table>
    <p><i>*required</i></p>

    <script>
        jQuery("input[type='radio'][name='quiz-<?= $quizId ?>-question-<?= $questionCount ?>-correct']").on('change', function () {
            let qNum = jQuery(this).val();
            // remove the required attribute and asterisk from the other inputs
            jQuery("input[type='text'][name*='quiz-<?= $quizId ?>-question-<?= $questionCount ?>-answer']").attr('required', false);
            jQuery("label[for*='quiz-<?= $quizId ?>-question-<?= $questionCount ?>-answer']").each(function () {
                let label = jQuery(this).text().replace('*', '');
                console.log(label);
                jQuery(this).text(label);
            });
            // add required attribute and asterisk to "correct" answer
            jQuery(`#quiz-<?= $quizId ?>-question-<?= $questionCount ?>-${qNum}`).attr('required', true);
            jQuery(`label[for*='quiz-<?= $quizId ?>-question-<?= $questionCount ?>-${qNum}']`).text('*Answer ' + qNum[7] + ': ');
        });
    </script>
	<?php

}

function answer_video_question() {
	// do something with submission information

	wp_die();
}

/**
 * Outputs the form for creating a new quiz
 */
function create_quiz() {
	global $wpdb, $question_table, $quiz_table, $answer_table;
	if ( isset( $_POST['widgetId'] ) && ! empty( $_POST['widgetId'] ) ) {
		$wpdb->insert(
			$quiz_table,
			array(
				'widgetId' => $_POST['widgetId']

			)
		);
		echo $wpdb->insert_id;
	}
	wp_die();
}

function update_question() {
	global $wpdb, $question_table, $quiz_table, $answer_table;
	$quizId = $_POST['quizId'];
	//$widgetId = $_POST['widgetId'];
	parse_str( json_decode( stripslashes( $_POST['form'] ), true ), $form );
	//parse_str( , $form );
	$questionNum = $_POST['qid'];
	$baseId      = 'quiz-' . $quizId . '-question-' . $questionNum;

	// to keep things simple, delete question first then reinsert with update information
	$wpdb->delete( $question_table, array(
		'quizId' => $quizId,
		'qid'    => $questionNum
	) );


	$wpdb->insert( $question_table, array(
		'qid'    => $questionNum,
		'text'   => $form["$baseId-text"],
		'quizId' => $quizId,
		'time'   => $form["$baseId-time"]
	) );
	for ( $i = 1; $i <= 4; $i ++ ) {
		if ( isset( $form["$baseId-answer-$i"] ) and $form["$baseId-answer-$i"] != '' ) {
			'answer-' . $i == $form["$baseId-correct"] ? $isCorrect = 1 : $isCorrect = 0;
			$wpdb->insert( $answer_table, array(
				'qid'       => $questionNum,
				'aid'       => $i,
				'quizId'    => $quizId,
				'isCorrect' => $isCorrect,
				'text'      => $form["$baseId-answer-$i"]
			) );
		}
	}
	wp_die();

}

/*
 * For Refreshing the menu buttons on any AJAX request
 */
function get_edit_buttons() {
	global $wpdb, $question_table, $quiz_table, $answer_table;
	$widgetId = $_POST['widgetId'];
	$hasUrl   = $_POST['hasUrl'];
	if ( is_numeric( $widgetId ) && $hasUrl == 'true' ) {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT quizId, title, isActive FROM $quiz_table WHERE widgetId = %d",
				$widgetId
			),
			ARRAY_A );

		// remove any quizzes with no title, i.e. if create quiz page is navigated away from, there will be an empty title
		$modified = false;
		foreach ( $rows as $row ) {
			if ( $row['title'] == '' || $row['title'] == null ) {
				$wpdb->delete( $quiz_table, array( 'quizId' => $row['quizId'] ) );
				$modified = true;
			}
		}
		// if deleted rows, redo the query to reflect the update db
		if ( $modified ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT quizId, title, isActive FROM $quiz_table WHERE widgetId = %d",
					$widgetId
				),
				ARRAY_A );
		}
		if ( count( $rows ) > 0 ) {
			?>
            <p>This widget has quizzes. Select a quiz to edit, or create a new quiz.</p>
            <button type="button"
                    id="create-quiz-btn-<?= $widgetId ?>"
                    onclick="startQuizCreation('create-quiz-btn-<?= $widgetId ?>','<?php echo $widgetId; ?>')"
                    class="button ">Create New Quiz
            </button>
            <br/><br/>
            <label for="quiz-select-<?= $widgetId ?>">Edit: </label>
            <select id="quiz-select-<?= $widgetId ?>">
                <option value="">Select A Quiz</option>
				<?php
				foreach ( $rows as $quiz ) {
					$active = '';
					if ( $quiz['isActive'] == 1 ) {
						$active = ' (Active Quiz)';
					}
					echo '<option value="' . $quiz['quizId'] . '">' . $quiz['title'] . $active . '</option>';
				}
				?>
            </select>
            <script>
                jQuery('#quiz-select-<?= $widgetId ?>').on('change', function () {
                    if (jQuery(this).val() != '')
                        selectQuizToEdit(<?=$widgetId?>);
                });
            </script>
            <button type="button" class="button red" onclick="deleteQuiz(<?= $widgetId ?>)">Delete</button>
            <button type="button" class="button green" onclick="setActiveQuiz(<?= $widgetId ?>)">Set As Active</button>
            <br/>
			<?php
		} else {
			?>
            <p>Click create a quiz to get started.</p>
            <button
                    type="button"
                    class="button green"
                    id="create-quiz-btn-<?= $widgetId ?>"
                    onclick="startQuizCreation('create-quiz-btn-<?= $widgetId ?>', <?= $widgetId ?>)">
                Create A Quiz
            </button>
			<?php
		}
	} else {
		echo '<p>Add a video and click save to enter quiz creation.</p>';

	}
	wp_die();
}

add_ajax_actions( $admin_action_array, false );
add_ajax_actions( $no_priv_action_array, true );
