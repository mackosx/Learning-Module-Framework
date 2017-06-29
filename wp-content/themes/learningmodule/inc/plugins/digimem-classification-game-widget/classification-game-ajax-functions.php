<?php

/*
 * Functions to register AJAX calls
 */
$admin_action_array = array(
	'get_new_category'
);
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
		        onclick="uploadMediaGame('<?= $base_id . '-cat' . $catNum . '-images' ?>', <?= $wid ?>, 'classification-game', <?= $catNum ?>)">
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
