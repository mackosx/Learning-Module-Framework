<?php
/**
 * Template part for displaying pages on front page
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

global $learningmodulecounter;

?>

<article id="panel<?php echo $learningmodulecounter; ?>" <?php post_class( 'learningmodule-panel ' ); ?> >
    <div class="panel-content">
        <div class="wrap">
            <div class="widget-area">
				<?php
				if (dynamic_sidebar( 'content-area-' . $learningmodulecounter ) ) :
				else :
					echo '<p>Add widgets to display content here!</p>';
				endif; ?>
            </div><!-- .widget-area -->
        </div><!-- .wrap -->
    </div><!-- .panel-content -->

</article><!-- #post-## -->
