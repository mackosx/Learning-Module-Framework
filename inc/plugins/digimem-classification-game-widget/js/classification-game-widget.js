function addCategory(wid) {
    let btn = jQuery(`#widget-classification-game-${wid}-add-category-button`);
    let numCat = jQuery(`#widget-classification-game-${wid}-numCat`);
    let catToAdd = parseInt(numCat.val()) + 1;
    if(catToAdd > 4){
        alert('Maximum 4 Categories.');
        return false;
    } else {
	    jQuery.ajax({
		    url: game.ajaxUrl,
		    type: 'post',
		    data: {
			    action: 'get_new_category',
			    wid: wid,
			    cat: catToAdd
		    },
		    success: function (data) {
			    if (catToAdd === 2) {
				    let delBtn = jQuery(`<button id="widget-classification-game-${wid}-del-cat1" class="button" type="button" onclick="deleteCategory(${wid},1)">Delete Category
                </button>`);
				    jQuery(`#widget-classification-game-${wid}-cat1`).after(delBtn);
			    }
			    let catField = jQuery(data).hide();
			    //btn.siblings("input").remove();
			    btn.parent().replaceWith(catField);
			    catField.show(400);
			    jQuery(`#widget-classification-game-${wid}-saved`).remove();

		    }
	    });
    }
}
function deleteCategory(wid, cat) {
    let categoryContainer = jQuery(`#widget-classification-game-${wid}-del-cat${cat}`).parent();
    let parent = categoryContainer.parent();
    let numCat = jQuery(`#widget-classification-game-${wid}-numCat`);
    // Check if trying to delete only category
    if(parseInt(numCat.val()) !== 1) {
        numCat.val(parseInt(numCat.val()) - 1);

        categoryContainer.hide(200, function () {
            jQuery(this).remove();
            let newValue = 1;
            jQuery(`#widget-classification-game-${wid}-saved`).remove();
            /**
             * Reshuffles the category numbers to proper order
             */
            // Loops through each container except the last one (which hold the new category button)
            parent.children(':not(:last-child)').each(function () {
                // Replace id of container div
                jQuery(this).attr('id', jQuery(this).attr('id').replace(/cat\d/, 'cat' + newValue));
                jQuery(this).children().each(function () {
                    for (let i = 0; i < this.attributes.length; i++) {
                        // Replace category number of ids and names
                        this.attributes[i].value = this.attributes[i].value.replace(/cat\d/, 'cat' + newValue);
                        // Replace function references with category number
                        if (this.attributes[i].name === 'onclick')
                            this.attributes[i].value = this.attributes[i].value.replace(/(\d+)(?!.*\d)/, newValue);

                    }
                });
                newValue++;
            });
            // If only one category remains, remove the delete button
            if (parseInt(numCat.val()) === 1) {
                jQuery(`#widget-classification-game-${wid}-del-cat1`).remove();
            }
        });
    }


}
function uploadMediaGame(groupId, wid, idBase, catNum) {
    // Extend the wp.media object
    mediaUploader = wp.media.frames.file_frame = wp.media({
        title: 'Select Images',
        button: {
            text: 'Select Images'
        },
        multiple: true,
        library: {
            type: 'image'
        }
    });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader.on('select', function () {
        let images = mediaUploader.state().get('selection').models;
        let length = mediaUploader.state().get('selection').length;
        let imgCount = 0;
        let groupDiv = jQuery(`#${groupId}`);

        groupDiv.children('input, img').remove();
        for (let i = 0; i < length; i++) {
            // only insert images
            if (images[i].changed.type === "image") {
                imgCount++;
                groupDiv
                    .append(`<img id="${groupId}-${imgCount}" src="${images[i].changed.url}"/>`)
                    .append(`<input type="hidden" id="${groupId}-${imgCount}" name="widget-${idBase}[${wid}][cat${catNum}-images-${imgCount}]" value="${images[i].changed.url}"/>`)


            }
        }
        groupDiv.append(`<input type="hidden" name="widget-${idBase}[${wid}][cat${catNum}-images-count]" id="${groupId}-count" value="${imgCount}"/>`);
        jQuery(`#widget-classification-game-${wid}-saved`).remove();

    });
    // Open the uploader dialog
    mediaUploader.open();
}