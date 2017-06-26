let mediaUploader;


function addTextChanged(textId) {
    let textArea = jQuery(`#${textId}`);
    if (textArea.parent().children('input').get(0).checked) {
        textArea.show();
    } else {
        textArea.hide();
    }
}

function updateQuestion(widgetId, quizId, questionId) {
    let questionNumber = parseInt(jQuery(`#quiz-${quizId}-question-number`).val());
    let baseId = `quiz-${quizId}-question-${questionNumber}`;
    let correctAnswer = jQuery(`[name="${baseId}-correct"]:checked`).val();

    if (jQuery(`#${baseId}-text`).val() === '') {
        alert("You must enter a question.");
    } else if (jQuery(`#${baseId}-${correctAnswer}`).val() === '') {
        alert("The correct answer must not be blank.");
    } else {
        sendUpdateQuestion(widgetId, quizId, questionId, function () {
            getEditButtons(widgetId, true);
        });
    }
}

// refreshes the edit quiz buttons to reflect AJAX changes
function getEditButtons(widgetId, hasUrl) {
    let wid = !isNaN(widgetId) ? parseInt(widgetId) : widgetId;
    jQuery.ajax({
        type: "POST",
        url: videoUpload.ajaxUrl,
        data: {
            action: 'get_edit_buttons',
            widgetId: wid,
            hasUrl: hasUrl
        },
        success: function (data) {
            jQuery(`#quiz-edit-${widgetId}`).empty();
            jQuery(`#widget-${widgetId}-edit-buttons`).empty().append(data);

        }
    });
}

// stores the edited quiz question in the db
function sendUpdateQuestion(widgetId, quizId, questionId, callback) {
    let form = jQuery(`#quiz-${quizId}-question-${questionId}-form-edit`);
    let formData = JSON.stringify(form.serialize());
    form.hide(300);
    jQuery.ajax({
        type: 'post',
        url: videoUpload.ajaxUrl,
        data: {
            action: 'update_question',
            quizId: quizId,
            qid: questionId,
            form: formData
        },
        success: callback

    });
}


// appends the quiz editing form with current values inputted
function getQuestionEditForm(selectedQuestion, quizId, widgetId) {
    jQuery.ajax({
        type: "POST",
        url: videoUpload.ajaxUrl,
        data: {
            action: 'question_edit',
            widgetId: widgetId,
            quizId: quizId,
            questionNumber: selectedQuestion
        },
        success: function (data) {
            // remove "set active" msg
            jQuery(`#widget-${widgetId}-active-msg`).remove();

            jQuery('form[id*=quiz]').remove();
            let editForm = jQuery(data).hide();
            jQuery(`#quiz-edit-${widgetId}`).append(editForm);
            editForm.show(500);
            let questionNumber = parseInt(jQuery(`#quiz-${quizId}-question-number`).val());
            let baseId = `quiz-${quizId}-question-${questionNumber}`;
            let video = jQuery(`#widget-interactive-${widgetId}-video`);
            let timeBox = jQuery(`#${baseId}-time`);
            // set time input to update when the video is played
            video.on('timeupdate', function () {
                timeBox.val(video.get(0).currentTime.toFixed(2));

            });
            // set the video time to change when the time input is changed
            timeBox.on('change', function () {
                video.get(0).currentTime = timeBox.val();
            });
        }
    });
}


function finishQuiz(widgetId, quizId, qCount) {
    getEditButtons(widgetId, true);
    jQuery(`.quiz-creation-area.widget-${widgetId}`).hide(800);
    jQuery(`#quiz-${quizId}-question-${qCount}-form`).hide(700, function () {
        jQuery(this).remove();
    });
    jQuery(`#widget-${widgetId}-edit-buttons`).show({
        duration: 400,
        easing: 'linear'
    });
}
// remove quiz from db
function deleteQuiz(widgetId) {
    deleteQuizCallback(widgetId, function () {
        getEditButtons(widgetId, true);
    })
}
function deleteQuizCallback(widgetId, callback) {
    let quizId = jQuery(`#quiz-select-${widgetId}`).val();
    jQuery.ajax({
        type: "POST",
        url: videoUpload.ajaxUrl,
        data: {
            action: 'delete_quiz',
            widgetId: widgetId,
            quizId: quizId
        },
        success: function (data) {
            jQuery(`#quiz-select-${widgetId}`).empty().append(data);
            jQuery(`#quiz-edit-${widgetId}`).hide();
            callback();
        }
    });
}

// sets the selected quiz to be displayed with the video
function setActiveQuiz(widgetId) {
    let quizId = jQuery(`#quiz-select-${widgetId}`).val();
    if(quizId) {
        jQuery.ajax({
            type: "POST",
            url: videoUpload.ajaxUrl,
            data: {
                action: 'set_active_quiz',
                widgetId: widgetId,
                quizId: quizId
            },
            success: function (data) {
                getEditButtons(widgetId, true);
                let activeMsg = jQuery(data);
                jQuery(`#widget-${widgetId}-active-msg`).remove();
                jQuery(`#quiz-select-${widgetId}`).parent().parent().append(activeMsg);
                activeMsg.delay(3000).fadeOut(1500, function(){
                    jQuery(this).remove();
                })

            }
        });
    }
}

jQuery(document).ready(function () {
    jQuery("video").bind("contextmenu", function () {
        return false;
    });
    jQuery(window).keydown(function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });

});


function startQuizCreation(selector, widgetId) {
    createQuiz(widgetId, function (data) {
        jQuery(`#widget-${widgetId}-active-msg`).remove();

        let quizId = data;
        let btn = jQuery('#' + selector);
        // hide any
        jQuery(`form[id*=quiz], #quiz-edit-${widgetId}`).hide();
        let creationArea = jQuery(`<div class="quiz-creation-area widget-${widgetId}"></div>`).hide();
        btn.parent().parent().append(creationArea);
        creationArea.show();
        jQuery.ajax({
            type: 'post',
            url: videoUpload.ajaxUrl,
            data: {
                action: 'get_quiz_form',
                quizId: quizId,
                widgetId: widgetId
            },
            success: function (data) {
                let form = jQuery(data).hide({});
                creationArea.append(form);
                form.show({
                    duration: 400,
                    easing: 'linear'
                });
            }
        });
        jQuery(`#widget-${widgetId}-edit-buttons`).hide('fast');

    });


}
function createQuiz(widgetId, callback) {

    jQuery.ajax({
        type: "POST",
        url: videoUpload.ajaxUrl,
        data: {
            action: 'create_quiz',
            widgetId: widgetId
        },
        success: callback
    });


}

// stores a new question into the db
function insertQuestion(formData, widgetId, quizId, questionNumber) {
    jQuery.ajax({
        type: 'post',
        url: videoUpload.ajaxUrl,
        data: {
            action: 'insert_question',
            form: formData,
            widgetId: widgetId,
            quizId: quizId,
            questionNumber: questionNumber

        },
        success: function (data) {
            // jQuery(`.widget-${widgetId}`).append(data);
        }
    })

}

// diplays the form for adding question to a quiz
function getQuestionForm(quizId, widgetId) {
    let title = jQuery(`#quiz-${quizId}-title`).val();
    if (title == '') {
        alert('Please enter a title for the quiz.');
    } else {
        jQuery.ajax({
            type: "POST",
            url: videoUpload.ajaxUrl,
            data: {
                action: 'get_question_form',
                quizId: quizId,
                widgetId: widgetId,
                title: title
            },
            success: function (data) {
                // hide the buttons
                let titleArea = jQuery(`#quiz-${quizId}-title-area`).hide('fast');
                titleArea.remove();
                /* add the question form */
                jQuery('form[id*=quiz]').remove();
                jQuery(`#widget-${widgetId}-active-msg`).remove();

                // animate the question form display
                let questionForm = jQuery(data).hide();
                // insert the question adding form before the text area checkbox
                jQuery(`#widget-${widgetId}-text-area`).before(questionForm);

                questionForm.show(400);

                let questionNumber = parseInt(jQuery(`#quiz-${quizId}-question-number`).val());
                let baseId = 'quiz-' + quizId + '-question-' + questionNumber;
                let video = jQuery(`#widget-interactive-${widgetId}-video`);
                let timeBox = jQuery(`.quiz-${quizId}-question-${questionNumber}-form #${baseId}-time`);

                // set time input to update when the video is played
                video.on('timeupdate', function () {
                    timeBox.val(video.get(0).currentTime.toFixed(2));

                });
                // set the video time to change when the time input is changed
                timeBox.on('change', function () {
                    video.get(0).currentTime = timeBox.val();
                });
                //add submit listener to insert the question to the db, after doing some validation
                jQuery(`#${baseId}-form`).submit(function (event) {
                    event.preventDefault();
                    // make sure correct answer is not blank
                    let correctAnswer = jQuery(`[name="${baseId}-correct"]:checked`).val();
                    if (jQuery(`#${baseId}-text`).val() == '') {
                        alert("You must enter a question.");
                    } else if (jQuery(`#${baseId}-${correctAnswer}`).val() == '') {
                        alert("The correct answer must not be blank.");
                    } else {
                        insertQuestion(jQuery(this).serialize(), widgetId, quizId, questionNumber);
                        getQuestionForm(quizId, widgetId);
                    }
                    //trigger the next question
                })


            }
        });
    }
}

// set the quiz to be edited and displays the question select box
function selectQuizToEdit(widgetId) {
    let quizId = jQuery(`#quiz-select-${widgetId}`).val();
    jQuery.ajax({
        type: 'post',
        url: videoUpload.ajaxUrl,
        data: {
            action: 'edit_quiz_form',
            widgetId: widgetId,
            quizId: quizId

        },
        success: function (data) {
            jQuery('form[id*=quiz]').remove();
            jQuery(`#widget-${widgetId}-active-msg`).remove();

            jQuery(`#quiz-edit-${widgetId}`).empty().append(data).show(400);
        }
    })
}

// sets up using the wordpress's built-in media library
function media(e, urlId, videoId) {
    e.preventDefault();

    // Extend the wp.media object
    mediaUploader = wp.media.frames.file_frame = wp.media({
        title: 'Choose Video',
        button: {
            text: 'Choose Video'
        },
        multiple: false,
        library: {
            type: 'video'
        }
    });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader.on('select', function () {
        let attachment = mediaUploader.state().get('selection').first().toJSON();
        jQuery(`#${urlId}`).val(attachment.url).trigger("change");
        let video = jQuery(`<video id='${videoId}' width='640' height='240' controls controlsList='nodownload' src="${attachment.url}"></video>`);
        jQuery(e.target).after(video);
    });

    // Open the uploader dialog
    mediaUploader.open();
}

