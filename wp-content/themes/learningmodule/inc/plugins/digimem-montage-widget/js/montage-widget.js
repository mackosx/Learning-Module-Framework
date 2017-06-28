/**
 * Created by macke on 2017-06-27.
 */


function uploadMedia( wid, idBase) {
    // Extend the wp.media object
    let base = 'widget-' + idBase + '-' + wid + '-';
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
        let groupDiv = jQuery(`#${base}images`);
        console.log(base + 'images');

        groupDiv.children('input, img').remove();
        for (let i = 0; i < length; i++) {
            // only insert images
            if (images[i].changed.type === "image") {
                imgCount++;
                groupDiv
                    .append(`<img id="${base}image-${imgCount}" src="${images[i].changed.url}"/>`)
                    .append(`<input type="hidden" id="${base}-${imgCount}" name="widget-${idBase}[${wid}][image-${imgCount}]" value="${images[i].changed.url}"/>`)


            }
        }
        groupDiv.append(`<input type="hidden" name="widget-${idBase}[${wid}][image-count]" id="${base}image-count" value="${imgCount}"/>`);
        jQuery(`#${base}saved`).remove();

    });
    // Open the uploader dialog
    mediaUploader.open();
}