<?php
/**
 * Template Name: Learning Module
 * Description: Displays a page that contains a widget area for Learning Module content
 *
 */
global $post;
?>
<style>
    .site-header{
        display: none;
    }
    #intro h1, #intro p, #intro button{
        text-align: center;
        vertical-align: middle;
        line-height: 1.5em;
    }
    #intro button{
        margin: 0 auto;
    }
    #intro h1{
        font-size: 5em;
    }
    #intro p{
        font-size: 1.5em;
    }
    #intro{
        height: calc(100% - 5.5em);
        border: 1px dashed red;
        text-align: center;
    }
    @media screen and (max-width: 400px){
        #intro h1{
            font-size: 3em;
        }
    }
    .widget-area{
        display: none;
    }

</style>
<?php
get_header();
wp_enqueue_script('module-page', get_theme_file_uri('/assets/js/learning-module-page.js'), array('jquery'),'', true);

?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <article <?php post_class( 'learningmodule-panel ' ); ?> >
                <div class="panel-content">
                    <div class="wrap">
                        <div id="intro">
                            <h1><?=$post->post_title?></h1>
                            <p><?=$post->post_content?></p>
                            <button type="button" id="begin-module">Begin</button>
                        </div>
                        <div class="widget-area">
							<?php
							if ( dynamic_sidebar( 'learning-module-' . $post->ID ) ) :
							else :
								echo '<p>Add widgets to display content here!</p>';
							endif; ?>
                        </div><!-- .widget-area -->
                    </div><!-- .wrap -->
                </div><!-- .panel-content -->

            </article><!-- #post-## -->

        </main><!-- #main -->
    </div><!-- #primary -->
<?php get_footer();

