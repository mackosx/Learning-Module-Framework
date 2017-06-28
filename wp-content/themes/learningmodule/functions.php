<?php
/**
 * Twenty Seventeen functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 */

/**
 * Twenty Seventeen only works in WordPress 4.7 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';

	return;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function learningmodule_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed at WordPress.org. See: https://translate.wordpress.org/projects/wp-themes/learningmodule
	 * If you're building a theme based on Twenty Seventeen, use a find and replace
	 * to change 'learningmodule' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'learningmodule' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	add_image_size( 'learningmodule-featured-image', 2000, 1200, true );

	add_image_size( 'learningmodule-thumbnail-avatar', 100, 100, true );

	// Set the default content width.
	$GLOBALS['content_width'] = 900;

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'top'    => __( 'Top Menu', 'learningmodule' ),
		'social' => __( 'Social Links Menu', 'learningmodule' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 *
	 * See: https://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
		'gallery',
		'audio',
	) );

	// Add theme support for Custom Logo.
	add_theme_support( 'custom-logo', array(
		'width'      => 250,
		'height'     => 250,
		'flex-width' => true,
	) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, and column width.
 	 */
	add_editor_style( array( 'assets/css/editor-style.css', learningmodule_fonts_url() ) );

	// Define and register starter content to showcase the theme on new sites.

	/**
	 * Filters Twenty Seventeen array of starter content.
	 *
	 * @since Twenty Seventeen 1.1
	 *
	 * @param array $starter_content Array of starter content.
	 */
	//$starter_content = apply_filters( 'learningmodule_starter_content', $starter_content );

	//add_theme_support( 'starter-content', $starter_content );
}

add_action( 'after_setup_theme', 'learningmodule_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function learningmodule_content_width() {

	$content_width = $GLOBALS['content_width'];

	// Get layout.
	$page_layout = get_theme_mod( 'page_layout' );

	// Check if layout is one column.
	if ( 'one-column' === $page_layout ) {
		if ( learningmodule_is_frontpage() ) {
			$content_width = 900;
		} elseif ( is_page() ) {
			$content_width = 740;
		}
	}

	// Check if is single post and there is no sidebar.
	if ( is_single() && ! is_active_sidebar( 'sidebar-1' ) ) {
		$content_width = 740;
	}

	/**
	 * Filter Twenty Seventeen content width of the theme.
	 *
	 * @since Twenty Seventeen 1.0
	 *
	 * @param $content_width integer
	 */
	$GLOBALS['content_width'] = apply_filters( 'learningmodule_content_width', $content_width );
}

add_action( 'template_redirect', 'learningmodule_content_width', 0 );

/**
 * Register custom fonts.
 */
function learningmodule_fonts_url() {
	$fonts_url = '';

	/**
	 * Translators: If there are characters in your language that are not
	 * supported by Libre Franklin, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$libre_franklin = _x( 'on', 'Libre Franklin font: on or off', 'learningmodule' );

	if ( 'off' !== $libre_franklin ) {
		$font_families = array();

		$font_families[] = 'Libre Franklin:300,300i,400,400i,600,600i,800,800i';

		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext' ),
		);

		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}

	return esc_url_raw( $fonts_url );
}

/**
 * Add preconnect for Google Fonts.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param array $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed.
 *
 * @return array $urls           URLs to print for resource hints.
 */
function learningmodule_resource_hints( $urls, $relation_type ) {
	if ( wp_style_is( 'learningmodule-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}

	return $urls;
}

add_filter( 'wp_resource_hints', 'learningmodule_resource_hints', 10, 2 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function learningmodule_widgets_init() {
	// loops through all active front page widget sections
	for ( $i = 1; $i <= wpc_custom_front_sections(); $i ++ ) {
		register_sidebar( array(
			'name'          => __( 'Content Area ' . $i, 'learningmodule' ),
			'id'            => 'content-area-' . $i,
			'description'   => __( 'Add widgets here to create content.', 'learningmodule' ),
			'before_widget' => '<div>',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}


}

add_action( 'widgets_init', 'learningmodule_widgets_init' );

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and
 * a 'Continue reading' link.
 *
 * @since Twenty Seventeen 1.0
 *
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
function learningmodule_excerpt_more( $link ) {
	if ( is_admin() ) {
		return $link;
	}

	$link = sprintf( '<p class="link-more"><a href="%1$s" class="more-link">%2$s</a></p>',
		esc_url( get_permalink( get_the_ID() ) ),
		/* translators: %s: Name of current post */
		sprintf( __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'learningmodule' ), get_the_title( get_the_ID() ) )
	);

	return ' &hellip; ' . $link;
}

add_filter( 'excerpt_more', 'learningmodule_excerpt_more' );

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Twenty Seventeen 1.0
 */
function learningmodule_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}

add_action( 'wp_head', 'learningmodule_javascript_detection', 0 );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function learningmodule_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", get_bloginfo( 'pingback_url' ) );
	}
}

add_action( 'wp_head', 'learningmodule_pingback_header' );

/**
 * Display custom color CSS.
 */
function learningmodule_colors_css_wrap() {
	if ( 'custom' !== get_theme_mod( 'colorscheme' ) && ! is_customize_preview() ) {
		return;
	}

	require_once( get_parent_theme_file_path( '/inc/color-patterns.php' ) );
	$hue = absint( get_theme_mod( 'colorscheme_hue', 250 ) );
	?>
    <style type="text/css" id="custom-theme-colors" <?php if ( is_customize_preview() ) {
		echo 'data-hue="' . $hue . '"';
	} ?>>
        <?php echo learningmodule_custom_colors_css(); ?>
    </style>
<?php }

add_action( 'wp_head', 'learningmodule_colors_css_wrap' );

/**
 * Enqueue scripts and styles.
 */
function learningmodule_scripts() {

	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'learningmodule-fonts', learningmodule_fonts_url(), array(), null );

	// Theme stylesheet.
	wp_enqueue_style( 'learningmodule-style', get_stylesheet_uri() );

	// Load the dark colorscheme.
	if ( 'dark' === get_theme_mod( 'colorscheme', 'light' ) || is_customize_preview() ) {
		wp_enqueue_style( 'learningmodule-colors-dark', get_theme_file_uri( '/assets/css/colors-dark.css' ), array( 'learningmodule-style' ), '1.0' );
	}

	// Load the Internet Explorer 9 specific stylesheet, to fix display issues in the Customizer.
	if ( is_customize_preview() ) {
		wp_enqueue_style( 'learningmodule-ie9', get_theme_file_uri( '/assets/css/ie9.css' ), array( 'learningmodule-style' ), '1.0' );
		wp_style_add_data( 'learningmodule-ie9', 'conditional', 'IE 9' );
	}

	// Load the Internet Explorer 8 specific stylesheet.
	wp_enqueue_style( 'learningmodule-ie8', get_theme_file_uri( '/assets/css/ie8.css' ), array( 'learningmodule-style' ), '1.0' );
	wp_style_add_data( 'learningmodule-ie8', 'conditional', 'lt IE 9' );

	// Load the html5 shiv.
	wp_enqueue_script( 'html5', get_theme_file_uri( '/assets/js/html5.js' ), array(), '3.7.3' );
	wp_script_add_data( 'html5', 'conditional', 'lt IE 9' );

	wp_enqueue_script( 'learningmodule-skip-link-focus-fix', get_theme_file_uri( '/assets/js/skip-link-focus-fix.js' ), array(), '1.0', true );

	$learningmodule_l10n = array(
		'quote' => learningmodule_get_svg( array( 'icon' => 'quote-right' ) ),
	);

	if ( has_nav_menu( 'top' ) ) {
		wp_enqueue_script( 'learningmodule-navigation', get_theme_file_uri( '/assets/js/navigation.js' ), array( 'jquery' ), '1.0', true );
		$learningmodule_l10n['expand']   = __( 'Expand child menu', 'learningmodule' );
		$learningmodule_l10n['collapse'] = __( 'Collapse child menu', 'learningmodule' );
		$learningmodule_l10n['icon']     = learningmodule_get_svg( array(
			'icon'     => 'angle-down',
			'fallback' => true
		) );
	}

	wp_enqueue_script( 'learningmodule-global', get_theme_file_uri( '/assets/js/global.js' ), array( 'jquery' ), '1.0', true );

	wp_enqueue_script( 'jquery-scrollto', get_theme_file_uri( '/assets/js/jquery.scrollTo.js' ), array( 'jquery' ), '2.1.2', true );

	wp_localize_script( 'learningmodule-skip-link-focus-fix', 'learningmoduleScreenReaderText', $learningmodule_l10n );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	/*
	 * ASSETS FOR WIDGETS
	 */
	/* Priority queue for interactive video*/
	wp_enqueue_script( 'priority-queue', get_theme_file_uri( '/assets/js/priority-queue.js' ) );

	/* Createjs suite for game creation and canvas element interaction*/
	wp_enqueue_script( 'createjs', get_theme_file_uri( '/inc/plugins/digimem-classification-game-widget/js/createjs-2015.11.26.min.js' ), array( 'jquery' ) );

	/* Fabricjs for montage creation widget */
	wp_enqueue_script( 'fabricjs', get_theme_file_uri( '/inc/plugins/digimem-montage-widget/js/fabric.min.js' ), array( 'jquery' ), false, false );

	/*
	 * Quiz Widget Scripts and Styles
	 */
	wp_enqueue_script( 'digimem-quiz-widget', get_theme_file_uri( '/inc/plugins/digimem-quiz-widget/js/quiz-widget.js' ), array( 'jquery' ), '1.0' );
	wp_enqueue_style( 'digimem-quiz-widget-style', get_theme_file_uri( '/inc/plugins/digimem-quiz-widget/css/quiz-widget-style-public.css' ), array() );
	wp_localize_script( "digimem-quiz-widget", "quizAjax", array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );



	/*
	 * Interactive Video Widget Scripts and Styles
	 */
	wp_enqueue_script( 'digimem-interactive-video-public', get_theme_file_uri( '/inc/plugins/digimem-interactive-video-widget/js/interactive-video-public.js' ), array( 'jquery' ), false, false );
	wp_localize_script( "digimem-interactive-video-public", "videoUpload", array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_style(
		'digimem-interactive-video-style',
		get_theme_file_uri( '/inc/plugins/digimem-interactive-video-widget/css/interactive-video-style-public.css' ),
		array(),
		false,
		false
	);

	/*
     * Montage Widget Scripts and Styles
     */
	wp_enqueue_style( 'digimem-montage-style', get_theme_file_uri( '/inc/plugins/digimem-montage-widget/css/montage-widget-public.css' ), array() );


}

add_action( 'wp_enqueue_scripts', 'learningmodule_scripts' );


function learning_module_admin_scripts() {
	/*
	 * For integration with the widgets and the Wordpress media handler
	 */
	wp_enqueue_media();

	/* Scripts and style for classification game widget */
	wp_enqueue_script( 'digimem-classification-game-widget', get_theme_file_uri( '/inc/plugins/digimem-classification-game-widget/js/classification-game-widget.js' ), array( 'jquery' ) );
	wp_localize_script( 'digimem-classification-game-widget', "game", array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_style( 'digimem-classification-game-style', get_theme_file_uri( '/inc/plugins/digimem-classification-game-widget/css/classification-game-widget.css' ), array() );

	/*
	 * Interactive Video Widget Admin Page Scripts and Styles for video creation and editing
	 */
	wp_enqueue_style(
		'video-style',
		get_theme_file_uri( '/inc/plugins/digimem-interactive-video-widget/css/interactive-video-style.css' ),
		array(),
		false,
		false
	);
	wp_enqueue_script(
		'digimem-interactive-video-admin',
		get_theme_file_uri( '/inc/plugins/digimem-interactive-video-widget/js/interactive-video.js' ),
		array( 'jquery' ),
		false,
		true
	);
	wp_localize_script(
		"digimem-interactive-video-admin",
		"videoUpload",
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' )
		)
	);
	/*
	 * Quiz Widget Scripts
	 */
	wp_enqueue_style( 'digimem-quiz-widget-admin', get_theme_file_uri( '/inc/plugins/digimem-quiz-widget/css/quiz-widget-style-admin.css' ), array() );

	/*
	 * Montage Scripts
	 */
	wp_enqueue_script( 'digimem-montage-widget', get_theme_file_uri( '/inc/plugins/digimem-montage-widget/js/montage-widget.js' ), array( 'jquery' ) );

	wp_enqueue_style( 'digimem-montage-style-admin', get_theme_file_uri( '/inc/plugins/digimem-montage-widget/css/montage-widget.css' ), array() );

}

add_action( 'admin_enqueue_scripts', 'learning_module_admin_scripts' );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array $size Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 *
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function learningmodule_content_image_sizes_attr( $sizes, $size ) {
	$width = $size[0];

	if ( 740 <= $width ) {
		$sizes = '(max-width: 706px) 89vw, (max-width: 767px) 82vw, 740px';
	}

	if ( is_active_sidebar( 'sidebar-1' ) || is_archive() || is_search() || is_home() || is_page() ) {
		if ( ! ( is_page() && 'one-column' === get_theme_mod( 'page_options' ) ) && 767 <= $width ) {
			$sizes = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
		}
	}

	return $sizes;
}

add_filter( 'wp_calculate_image_sizes', 'learningmodule_content_image_sizes_attr', 10, 2 );

/**
 * Filter the `sizes` value in the header image markup.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $html The HTML image tag markup being filtered.
 * @param object $header The custom header object returned by 'get_custom_header()'.
 * @param array $attr Array of the attributes for the image tag.
 *
 * @return string The filtered header image HTML.
 */
function learningmodule_header_image_tag( $html, $header, $attr ) {
	if ( isset( $attr['sizes'] ) ) {
		$html = str_replace( $attr['sizes'], '100vw', $html );
	}

	return $html;
}

add_filter( 'get_header_image_tag', 'learningmodule_header_image_tag', 10, 3 );

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param array $attr Attributes for the image markup.
 * @param int $attachment Image attachment ID.
 * @param array $size Registered image size or flat array of height and width dimensions.
 *
 * @return string A source size value for use in a post thumbnail 'sizes' attribute.
 */
function learningmodule_post_thumbnail_sizes_attr( $attr, $attachment, $size ) {
	if ( is_archive() || is_search() || is_home() ) {
		$attr['sizes'] = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
	} else {
		$attr['sizes'] = '100vw';
	}

	return $attr;
}

add_filter( 'wp_get_attachment_image_attributes', 'learningmodule_post_thumbnail_sizes_attr', 10, 3 );

/**
 * Use front-page.php when Front page displays is set to a static page.
 *
 * @since Twenty Seventeen 1.0
 *
 * @param string $template front-page.php.
 *
 * @return string The template to be used: blank if is_home() is true (defaults to index.php), else $template.
 */
function learningmodule_front_page_template( $template ) {
	return is_home() ? '' : $template;
}

add_filter( 'frontpage_template', 'learningmodule_front_page_template' );

/**
 * Implement the Custom Header feature.
 */
require get_parent_theme_file_path( '/inc/custom-header.php' );

/**
 * Custom template tags for this theme.
 */
require get_parent_theme_file_path( '/inc/template-tags.php' );

/**
 * Additional features to allow styling of the templates.
 */
require get_parent_theme_file_path( '/inc/template-functions.php' );

/**
 * Customizer additions.
 */
require get_parent_theme_file_path( '/inc/customizer.php' );

/**
 * SVG icons functions and filters.
 */
require get_parent_theme_file_path( '/inc/icon-functions.php' );

//Change this number to change the number of the sections.
function wpc_custom_front_sections() {

	return 1;
}

add_filter( 'learningmodule_front_page_sections', 'wpc_custom_front_sections' );

/*
 * Helper function to add ajax actions to either admin or public hook
 */
function add_ajax_actions($actions = array(), $nopriv = false){
	$prefix = $nopriv ? 'wp_ajax_nopriv_': 'wp_ajax_';
	foreach($actions as $action){
		add_action( $prefix . $action, $action );
	}
}

/*
 * Widget files to be included in learning module theme
 */
require_once( get_theme_file_path( '/inc/plugins/digimem-interactive-video-widget/interactive-video.php' ) );
require_once( get_theme_file_path( '/inc/plugins/digimem-quiz-widget/quiz-widget.php' ) );
require_once( get_theme_file_path( '/inc/plugins/digimem-classification-game-widget/classification-game-widget.php' ) );
require_once( get_theme_file_path( '/inc/plugins/digimem-montage-widget/montage-widget.php' ) );

function badge_collection(){
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>Badge Collection</h1>';
	echo '<p>Display any badges that are offered</p>';
	echo '</div>';
}
function issuer_options(){
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	?>

    <form method="post" action="options.php">
        <?php
        settings_fields('issuer_profile_options');
        do_settings_sections('badge_issuer_profile');
        // Name, Type, Url, Email
        ?>
        <input name="Submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
    </form>


    <?php

	echo '</div>';
}
add_action('admin_init', 'issuer_profile_admin_init' );
function issuer_profile_admin_init(){
	register_setting('issuer_profile_options', 'issuer_profile_options');
	add_settings_section('issuer_profile','Issuer Profile Settings', 'issuer_profile_section_text', 'badge_issuer_profile');
	add_settings_field('issuer-profile-name','Organization Name', 'name_input_setting', 'badge_issuer_profile', 'issuer_profile');
	add_settings_field('issuer-profile-url','Organization URL', 'url_input_setting', 'badge_issuer_profile', 'issuer_profile');
	add_settings_field('issuer-profile-email','Email', 'email_input_setting', 'badge_issuer_profile', 'issuer_profile');

}

function issuer_profile_section_text(){
    echo '<p>Main desc of this section here.</p>';
}

function name_input_setting(){
	$options = get_option('issuer_profile_options');
	echo "<input placeholder='e.g. UBC' title='Name of the organization' id=\"issuer-profile-id\" name='issuer_profile_options[name]' size='40' type='text' value='{$options['name']}'/>";
}
function url_input_setting(){
	$options = get_option('issuer_profile_options');
	echo "<input placeholder='e.g. http://www.example.org' title='Main website url of the organization' id=\"issuer-profile-id\" name='issuer_profile_options[url]' size='40' type='text' value='{$options['url']}'/>";
}
function email_input_setting(){
	$options = get_option('issuer_profile_options');
	echo "<input placeholder='e.g. account@example.org' title='Issuing email' id=\"issuer-profile-id\" name='issuer_profile_options[email]' size='40' type='email' value='{$options['email']}'/>";
}


function add_new_badge(){

}