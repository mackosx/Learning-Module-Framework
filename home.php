<?php
/**
 * The front page template file
 *
 * If the user has selected a static page for their homepage, this is what will
 * appear.
 * Learn more: https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			// Get each of our panels and show the post data.
			if ( 0 !== learningmodule_panel_count() || is_customize_preview() ) : // If we have pages to show.

				/**
				 * Filter number of front page sections in Twenty Seventeen.
				 *
				 * @since Twenty Seventeen 1.0
				 *
				 * @param $num_sections integer
				 */
				$num_sections = apply_filters( 'learningmodule_front_page_sections', 4 );
				global $learningmodulecounter;

				// Create a setting and control for each of the sections available in the theme.
				for ( $i = 1; $i < ( 1 + $num_sections ); $i ++ ) {
					$learningmodulecounter = $i;
					learningmodule_front_page_section( null, $i );
				}

			else:
				echo "<div class='wrap no-widget-message'><p>Add widgets to create content!</p></div>";
			endif;

			?>


		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer();
