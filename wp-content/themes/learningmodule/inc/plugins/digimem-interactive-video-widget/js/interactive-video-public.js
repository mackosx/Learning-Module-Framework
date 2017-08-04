let maxScore = -1;
let currentScore = 0;
let quizOver = false;
// adds to listener to displays questions at their appropriate times
function attachVideoListener(widgetId, activeQuiz) {
    jQuery.ajax({
        type: "POST",
        url: videoUpload.ajaxUrl,
        data: {
            action: 'load_quiz',
            widgetId: widgetId,
            quizId: activeQuiz
        },
        success: function (data) {
            // on return of the quiz, attach listeners to execute questions at specific times
            let video = jQuery('#widget-' + widgetId + '-video > video');
            let quiz = JSON.parse(data);
            let queue = new PriorityQueue({
                comparator: function (a, b) {
                    return a["time"] - b["time"];
                }
            });
	        //enqueue all items
	        for (let i = 0; i < Object.keys(quiz).length; i++) {
                queue.queue(quiz[i]);

            }
            maxScore = queue.length;
            video.on("timeupdate", function () {
                let current = video.get(0).currentTime;
                for (let i = 0; i < queue.length; i++) {
                    if (current >= queue.peek()["time"] && video.get(0).paused == false) {
                        // pause video
                        video.get(0).pause();
                        //pop question off and display
                        let question = queue.dequeue();
                        if(queue.length === 0)
                        	quizOver = true;
                        //display question
                        let qid = question["qid"];
                        let area = jQuery(`<div id='quiz-${activeQuiz}-question-${qid}-area'><h4>${question["question"]}</h4></div>`).hide();
                        video.parent().append(area);

                        // displays answers as input
                        let correctAnswer = -1;
                        for (let j = 1; j <= Object.keys(question['answers']).length; j++) {
                            if (question['answers']['answer' + j]['isCorrect'] == 1) {
                                correctAnswer = j;
                            }
                            area.append(`<input type="radio" name="quiz-${activeQuiz}-question-${qid}-answer" value="${j}" id="quiz-${activeQuiz}-answer-${j}"/><label for="quiz-${activeQuiz}-answer-${j}" style="display: inline-block"> ${question['answers']['answer' + j]['text']}</label><br/>`);
                        }
                        // add submit button for question
                        area.append(`<br/><button type="button" id="question-${qid}-submit" class="button" >Submit</button>`);
                        jQuery(`#question-${qid}-submit`).on('click', function () {
                            submitQuestion(qid, video, correctAnswer, activeQuiz, widgetId);
                        });
                        area.children().css('margin-bottom', 0);

                        // disable video controls
                        video.get(0).removeAttribute('controls');
                        area.show(800);
                        break;
                    }
                }
            });
            video.on('ended', function(){
            	if(quizOver){
		            let score = (( currentScore / maxScore ) * 100) + '%';

		            video.parent().append(`<p>Your score for this video was ${score}.</p>`)
	            }
            })
        }
    });

}

// handles submission of each question while watching the video
function submitQuestion(qid, video, correctAnswer, quizId, wid) {
    let radioBtnGroup = jQuery(`input[name='quiz-${quizId}-question-${qid}-answer']:checked`);
    if (!radioBtnGroup.val()) {
        alert("Please select an answer.");
    } else {
        let btn = jQuery('<button type="button" class="button">Continue Video</button>');
        let msg = '';
        // set message to display
        let answeredCorrect;
        if (radioBtnGroup.val() == correctAnswer) {
            msg = '<p class="video-question-correct">Correct!</p>';
            answeredCorrect = true;
            currentScore++;
        } else {
            msg = '<p class="video-question-wrong">Sorry, that was incorrect.</p>';
            answeredCorrect = false;
        }
        //check for correct selection
        let displayContainer = jQuery(`#quiz-${quizId}-question-${qid}-area`);
        displayContainer.css('position', 'relative');
        msg = jQuery(msg).hide().css('position', 'absolute').css('top', 0);
        btn.css('margin-top', '4em');

        let children = displayContainer.children();
        children.animate(
            {
                height: 0,
                opacity: 0,
                display: 'none'
            }, {
                duration: 1200,
                complete: function () {
                    jQuery(this).remove();
                }
            });
        displayContainer.append(msg);
        msg.fadeIn(1200, function () {
            displayContainer.append(btn);
        }).delay(3000).fadeOut(1000);
        btn.on('click', function () {
            continueAfterAnswer(video, qid, quizId, displayContainer, answeredCorrect, wid);
        })

    }
}
function continueAfterAnswer(video, qid, quizId, container, answeredCorrect, wid) {
	if(quizOver){
		submit_score(wid);
	}
    container.hide(500, function () {

        jQuery(this).remove();
        video.prop('controls', true);
        video.get(0).play();
        jQuery.ajax({
            type: 'post',
            url: videoUpload.ajaxUrl,
            data: {
                action: 'answer_video_question',
                questionId: qid,
                quizId: quizId,
                correct: answeredCorrect
            },
            success: function (data) {
                //do something if answer is correct

            }

        });
    });
}
function submit_score(wid){
	let score = currentScore / maxScore;
	jQuery.ajax({
		type: 'POST',
		url: videoUpload.ajaxUrl,
		data: {
			action: 'submit_score',
			wid: wid,
			score: score,
			type: 'video_quiz'
		}
	})
}
