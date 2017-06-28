(function ($) {
    let data = montageData;
    let id = data.id;
    let canvas;
    let stage;
    let images = [];
    let currentSelection;
    let baseId = 'widget-montage-' + id + '-';

    // Initial function
    function montageInit() {
        canvas = new fabric.Canvas('montage-canvas-' + id);
        addDrawingToggle();
        addColorChanger();
        activateDrawing();
        addWidthSelect();
        addDeleteButton();
        addImages();
        addShapeButtonListeners();
        addFinish();

    }

    function addFinish() {
        $('#finish-button').on('click', function(options){
                let dataUrl = canvas.toDataURL({
                    format: 'png'
                });
                window.open(dataUrl);
        });


    }

    function addShapeButtonListeners() {
        $('#oval-button').on('click', function (options) {
            let oval = new fabric.Ellipse({
                left: 100,
                top: 100,
                rx: 100,
                ry: 50,
                fill: undefined,
                stroke: canvas.freeDrawingBrush.color,
                strokeWidth: canvas.freeDrawingBrush.width

            });
            canvas.add(oval);
            deactivateDrawing();
        });
        $('#rectangle-button').on('click', function (options) {
            let rect = new fabric.Rect({
                left: 100,
                top: 100,
                height: 100,
                width: 200,
                fill: undefined,
                stroke: canvas.freeDrawingBrush.color,
                strokeWidth: canvas.freeDrawingBrush.width

            });
            canvas.add(rect);
            deactivateDrawing();
        });
    }

    function addImages() {
        let container = $(`#${baseId}image-container`);
        for (let i = 0; i < data['image-count']; i++) {
            let src = data['image-' + (i + 1)];
            let img = jQuery(`<img src="${src}"/>`);
            img.on('click', function () {
                fabric.Image.fromURL(src, function (oImg) {
                    oImg.scaleToHeight(100);
                    canvas.add(oImg);
                    deactivateDrawing();
                }, {
                    top: 50,
                    left: 50
                });
            });
            container.append(img);
        }
    }

    function addDeleteButton() {
        canvas.on('object:selected', function (options) {
            currentSelection = options.target;
        });
        // Clear selection on deselect
        canvas.on('selection:cleared', function (options) {
            currentSelection = null;
        });
        $('#delete-button').on('click', function () {
            let grp = canvas.getActiveGroup();
            if (currentSelection) {
                // if there is an selection of multiple objects, delete each one
                if (grp && grp.active == true) {
                    grp._objects.forEach(function (object, key) {
                        canvas.remove(object);
                        grp.removeWithUpdate(object);
                    });
                    canvas.discardActiveGroup();
                    canvas.renderAll();
                } else {
                    // delete one object selected
                    canvas.remove(currentSelection);
                }
            }
        });
    }

    function addColorChanger() {
        let btn = $('#color-button');
        canvas.freeDrawingBrush.color = btn.val();

        btn.on('change', function () {
            canvas.freeDrawingBrush.color = btn.val();
        });
    }

    function addWidthSelect() {
        let btn = $('#width-button');
        canvas.freeDrawingBrush.width = parseInt(btn.val());
        btn.on('change', function () {
            canvas.freeDrawingBrush.width = parseInt(btn.val());
        });
    }

    function addDrawingToggle() {
        // Adds listener to brush button to toggle drawing
        $('#brush-button').on('click', function () {
            if (canvas.isDrawingMode) {
                deactivateDrawing();
                $(this).text('Brush Tool');
            } else {
                activateDrawing();
                $(this).text('Select Tool');

            }

        });
    }

    function deactivateDrawing() {
        canvas.isDrawingMode = false;
    }

    function activateDrawing() {
        canvas.isDrawingMode = true;


    }


    $(document).onload = montageInit();


}(jQuery));
function showGame(wid) {
    let cvs = jQuery(`#montage-canvas-${wid}`);
    cvs.show({
        duration: 1000,
        easing: 'linear'

    });
    cvs.parent().parent().children('button').hide();
}
