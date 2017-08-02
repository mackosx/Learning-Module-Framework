/**
 * Created by mackenzie s. on 2017-06-05.
 */
(function($){
    let id = data.id;
    let canvas;
    let stage;
    let bg;
    let numCat = parseInt(data['numCat']);
    let categories = [];

    let catImages = [];
    let catBoxes = [];

    let score = 0;
    let max;
    const FONT = 'Helvetica';
    let gameOverScreen;

    let images = [];
    let STAGE_HEIGHT;
    let STAGE_WIDTH;

    function reset() {
        gameOverScreen = new createjs.Container();
        catImages.length = catBoxes.length = images.length = categories.length = 0;
        if (stage)
            stage.removeAllChildren();
    }
    function init() {
        reset();

        canvas = document.getElementById('myCanvas-' + id);
        stage = new createjs.Stage(canvas);
        STAGE_HEIGHT = stage.canvas.height;
        STAGE_WIDTH = stage.canvas.width;

        parseData();
        score = 0;
        addBackground();
        // load images
        loadImages();
        max = images.length;
        // display the categories and boxes
        addCategories();

        let submit = createButton("Submit", (STAGE_WIDTH / 2),  STAGE_HEIGHT - 40, 90, 50, checkScore);
        stage.addChild(submit);
        // set up tick event to update
        createjs.Ticker.setFPS(30);
        createjs.Ticker.addEventListener("tick", tick);
    }
    function addBackground(){
        let bg = new createjs.Shape();
        bg.graphics
            .beginFill('#808080')
            .drawRect(0, 0, STAGE_WIDTH, STAGE_HEIGHT);
        stage.addChild(bg);
    }
    function checkScore() {
        for (let i = 0; i < catBoxes.length; i++) {
            for (let j = 0; j < catImages.length; j++) {
                if (intersects(catImages[j], catBoxes[i]) && catImages[j].category === i) {
                    score++;

                }
            }
        }
        gameOver(score);
        score = 0;

    }
    function gameOver(score) {
	    // Send score to db as AJAX
	    $.ajax({
		    type: 'POST',
		    url: classificationGame.ajaxUrl,
		    data: {
			    action: 'submit_score',
			    wid: id,
			    score: score,
			    type: 'classification'
		    },
		    success: function(data){
		    	showGameOverScreen(score, data);
		    }
	    });
    }
    function showGameOverScreen(score, data){
        // show game over screen and score
        stage.removeAllChildren();
        stage.addChild(gameOverScreen);
        let bg = new createjs.Shape();
        gameOverScreen.addChild(bg);
        bg.graphics.beginFill('#808080').drawRect(0, 0, STAGE_WIDTH, STAGE_HEIGHT);
        let endText = new createjs.Text('Game Over!', '100px ' + FONT);
        let scoreMsg = data === 'FALSE' ? '\nYou are not logged in, \nso your score was not saved.' : '';
        let scoreText = new createjs.Text('Score: ' + score + '/' + max +  scoreMsg, '50px ' + FONT);
        endText.textAlign = scoreText.textAlign = 'center';
        endText.textBaseline = scoreText.textBaseline = 'middle';
        gameOverScreen.addChild(endText, scoreText);
        endText.x = scoreText.x = STAGE_WIDTH / 2;
        endText.y = (STAGE_HEIGHT / 2) - 150;
        scoreText.y = (STAGE_HEIGHT / 2);
        let playAgainBtn = createButton('Play Again', (STAGE_WIDTH/2)-70, STAGE_HEIGHT - 100, 120, 50, init);
        let quitButton = createButton('Quit', (STAGE_WIDTH/2) + 70, STAGE_HEIGHT - 100, 100, 50, quit);
        gameOverScreen.addChild(playAgainBtn, quitButton);

        gameOverScreen.width = STAGE_WIDTH;
        gameOverScreen.height = STAGE_HEIGHT;

    }
    function quit(){
        jQuery(canvas).parent().hide(1000);
    }

    function createButton(buttonText, x, y, width, height, onclick) {
        let btnCorner = 3;
        // initialize container and set location
        let btn = new createjs.Container();
        btn.width = width;
        btn.height = height;
        btn.regX = width/ 2;
        btn.regY = height/ 2;
        let text = new createjs.Text(buttonText, 'bold 20px ' + FONT, 'black');
        // draw button
        let btnBg = new createjs.Shape();
        btn.addChild(btnBg);
        btn.addChild(text);
        btnBg.graphics
            .beginFill('#d3d3d3')
            .drawRoundRect(0, 0, btn.width, btn.height, btnCorner);
        // add items to button container

        btn.on('click', onclick);
        stage.enableMouseOver();
        // add color on mouseover
        btn.on('mouseover', function () {
            btn.cursor = 'pointer';
            btnBg.graphics.clear();
            btnBg.graphics
                .beginFill("white")
                .drawRoundRect(0, 0, btn.width, btn.height, btnCorner);
        });
        // remove color on mouse out
        btn.on('mouseout', function () {
            btn.cursor = 'pointer';
            btnBg.graphics.clear();
            btnBg.graphics
                .beginFill('#d3d3d3')
                .drawRoundRect(0, 0, btn.width, btn.height, btnCorner);
        });

        //move button to bottom of screen
        btn.x = x;
        btn.y = y;

// center the text within the container
        text.textAlign = 'center';
        text.textBaseline = 'middle';
        let b = text.getBounds();
        text.x = btn.width / 2;
        text.y = btn.height / 2;
        return btn;

    }
    function loadImages() {
        for (let i = 0; i < categories.length; i++) {
            for (let j = 0; j < categories[i].images.length; j++) {
                images[images.length] = new Image();
                images[images.length - 1].src = categories[i]['images'][j];


                // setup images on stage
                images[images.length - 1].onload = handleImageLoad;
                images[images.length - 1].category = i;
            }

        }
    }
    function tick() {
        stage.update();
    }

// converts images into bitmap objects and adds them to stage
    function handleImageLoad(event) {
        let boxMouseOverBorderColor = 'white';
        let boxClickBorderColor = 'red';
        let size = 100;
        // random placement of images
        let x = Math.floor((Math.random() * STAGE_WIDTH));
        let y = Math.floor((Math.random() * STAGE_HEIGHT * .2) + STAGE_HEIGHT * .60);
        let image = event.target;

        let bmp = new createjs.Bitmap(image);
        // set uniform image size and random placement
        bmp.snapToPixel = false;
        bmp.scaleX = size / image.width;
        bmp.scaleY = size / image.height;
        bmp.x = x;
        bmp.y = y;

        // set up mouse event handlers
        bmp.on("pressmove", dragHandler);
        bmp.on("pressup", releaseHandler);
        bmp.on('mousedown', function(){
            bmp.mask.graphics.clear();
            bmp.mask.graphics
                .setStrokeStyle(1)
                .beginStroke(boxClickBorderColor)
                .drawRect(bmp.x-50, bmp.y-50, 100, 100);
        });
        bmp.mask = new createjs.Shape();



        bmp.on('mouseover' , function(){
            bmp.cursor = 'pointer';
            bmp.mask.graphics.clear();
            bmp.mask.graphics
                .setStrokeStyle(1)
                .beginStroke(boxMouseOverBorderColor)
                .drawRect(bmp.x-50, bmp.y-50, size, size);
            stage.addChild(bmp.mask);
            bmp.mask.graphics.beginStroke('black');
            bmp.mask.x = 0;
            bmp.mask.y = 0;
            stage.setChildIndex(bmp.mask, stage.getChildIndex(bmp));

        });
        bmp.on('mouseout', function(){
            bmp.mask.graphics.clear();
            stage.removeChild(bmp.mask);
        });
        bmp.regX = bmp.image.width / 2;
        bmp.regY = bmp.image.height / 2;
        bmp.category = image.category;
        bmp.setBounds(bmp.x, bmp.y, size, size);
        catImages[catImages.length] = bmp;
        stage.addChild(bmp);
    }

// handles the click and drag functionality
    function dragHandler(e) {
        let boxDragBorderColor = "red";
        e.target.x = e.stageX;
        e.target.y = e.stageY;
        e.target.setBounds(e.target.x, e.target.y, e.target.width, e.target.width);
        for (let i = 0; i < catBoxes.length; i++) {

            if (intersects(e.target, catBoxes[i])) {
                if (!catBoxes[i].withinBounds)
                    catBoxes[i].highlightBounds();

            } else {
                if (catBoxes[i].withinBounds)
                    catBoxes[i].removeBox();
            }
        }
        stage.setChildIndex( e.target, stage.getNumChildren()-1);
        stage.enableMouseOver(0);
        e.target.mask.graphics.clear();
        e.target.mask.graphics
            .setStrokeStyle(1)
            .beginStroke(boxDragBorderColor)
            .drawRect(e.target.x-50, e.target.y-50, 100, 100);
        stage.setChildIndex(e.target.mask, stage.getNumChildren() - 1);

    }
    function releaseHandler(e) {
        let boxHoverBorderColor = "white";
        for (let i = 0; i < catBoxes.length; i++) {
            if (catBoxes[i].withinBounds) {
                catBoxes[i].removeBox();
            }
        }
        e.target.mask.x = 0;
        e.target.mask.y = 0;
        e.target.mask.graphics
            .clear()
            .setStrokeStyle(1)
            .beginStroke(boxHoverBorderColor)
            .drawRect(e.target.x-50, e.target.y-50, 100, 100);
        stage.enableMouseOver();

    }

// checks if the registration point on r1 is within the bounds of r2
    function intersects(r1, r2) {
        let bounds2 = r2.getBounds();
        let bounds1 = {};
        // if event
        if (r1.stageX) {
            bounds1.x = r1.stageX;
            bounds1.y = r1.stageY;
            // check if event is in rectangle
        } else {
            bounds1.x = r1.x;
            bounds1.y = r1.y;
            bounds1 = r1.getBounds();

        }
        //check intersection
        return bounds1.y < bounds2.y + bounds2.height && bounds1.y > bounds2.y && bounds1.x < bounds2.x + bounds2.width && bounds1.x > bounds2.x;


    }

    function addCategories() {
        let boxColor = '#f8f7ed';
        let incr = (STAGE_WIDTH - 50) / categories.length;
        let catX = 50.5;
        let catY = 50.5;
        let boxWidth = incr - 50;
        let boxHeight = STAGE_HEIGHT * 0.5;
        for (let i = 0; i < categories.length; i++) {

            let catContainer = new createjs.Container;
            // draw category text
            let cat = new createjs.Text(categories[i].name, "20px " + FONT, boxColor);
            cat.snapToPixel = true;
            cat.x = catX;
            cat.y = catY;
            cat.id = "cat" + i;
            // draw box around category
            catBoxes[i] = new createjs.Shape();

            catBoxes[i].x = catX - 10;
            catBoxes[i].y = catY - 10;
            catBoxes[i].width = boxWidth;
            catBoxes[i].height = boxHeight;
            let g = catBoxes[i].graphics;
            g.beginStroke(boxColor)
                .setStrokeStyle(1)
                .drawRect(0, 0, boxWidth, boxHeight);
            catBoxes[i].setBounds(catX - 10, catY - 10, boxWidth, boxHeight);
            catBoxes[i].category = i;
            // events for displaying green outline
            catBoxes[i].highlightBounds = function () {
                let bounds = this.getBounds();
                this.greenBox = new createjs.Shape();
                this.greenBox.graphics
                    .beginStroke("red")
                    .setStrokeStyle(1)
                    .drawRect(bounds.x, bounds.y, bounds.width, bounds.height);
                stage.addChild(this.greenBox);
                this.withinBounds = true;
            };
            catBoxes[i].removeBox = function () {
                stage.removeChild(this.greenBox);
                this.withinBounds = false;
            };
            stage.addChild(cat, catBoxes[i]);

            catX += incr;
        }

    }
// gets the data from the php object into a cleaner js object version
    function parseData() {
        for (let i = 0; i < numCat; i++) {
            // set each category name
            categories[i] = {'name': data['cat' + (i + 1)]};
            // set images for that category
            let images = [];
            for (let j = 0; j < parseInt(data['cat' + (i + 1) + '-images-count']); j++) {
                images[j] = data['cat' + (i + 1) + '-images-' + (j + 1)];
            }
            categories[i]['images'] = images;
        }
    }

    jQuery(document).onload = init();
}(jQuery));
function showClassificationGame(wid) {
    let cvs = jQuery(`#myCanvas-${wid}`);
    cvs.show({
        duration: 1000,
        easing: 'linear'

    });
    cvs.parent().children('button').hide();
}
