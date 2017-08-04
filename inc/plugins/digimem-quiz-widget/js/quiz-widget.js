/**
 * Created by macke on 2017-06-01.
 */


function submitQuizWidget(wid, par, formData) {
    if(formData == 'undefined' || formData == ''){
        alert('Please select an answer.');
    }else {
        let val = formData[0]['value'];
        jQuery.ajax({
            type: 'post',
            url: quizAjax.ajaxUrl,
            data: {
                action: 'check_correct_submission',
                value: val,
                wid: wid
            },
            success: function (data) {
	            let textarea = jQuery(par[0].parentElement).children('.quiz-widget-text-area');
	            let msg = jQuery(data).hide();
	            if(textarea.length === 0)
		            msg.css('position', 'relative');
	            else
                    msg.css('position', 'absolute').css('top', 0);
                let children = par.children();
                children.hide(800);
                par.append(msg);
                msg.fadeIn(1000).delay(2000).fadeOut(1000, function(){

                	textarea.animate(
		                {
			                width: '100%',
			                'padding-right': 0
		                },
		                {
			                duration: 500,
			                queue: true
		                }
	                );

                });

            }

        });
    }
}
function testDeletion(id){
}