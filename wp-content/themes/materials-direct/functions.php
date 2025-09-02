<?php
/**
 * Materials Direct functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Materials_Direct
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function materials_direct_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on Materials Direct, use a find and replace
		* to change 'materials-direct' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'materials-direct', get_template_directory() . '/languages' );

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

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'materials-direct' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'materials_direct_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'materials_direct_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function materials_direct_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'materials_direct_content_width', 640 );
}
add_action( 'after_setup_theme', 'materials_direct_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function materials_direct_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'materials-direct' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'materials-direct' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'materials_direct_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function materials_direct_scripts() {
	wp_enqueue_style( 'materials-direct-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'materials-direct-style', 'rtl', 'replace' );

	wp_enqueue_script( 'materials-direct-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'materials_direct_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}

/* BEGIN CUSTOM FUNCTIONS */

// Generate and display PPP for testing
require_once('includes/acf_global_options.php');
// Generate and display PPP for testing

// Generate and display PPP for testing
require_once('includes/algorithm_and_core_functionality.php');
// Generate and display PPP for testing

// Display Ajax Page spinner
// require_once('includes/page_load_spinner.php');
// Display Ajax Page spinner

// Display Stock Sheet Sizes On Product Page
//require_once('includes/custom-shipping-calculation.php');
// Display Stock Sheet Sizes On Product Page

// Calculate Nnmber Of Sheets Required
//require_once('includes/number-of-sheets-required.php');
// Calculate Nnmber Of Sheets Required


// Calculate Nnmber Of Sheets Required
//require_once('includes/display_order_object_on_thankyou_page.php');
// Calculate Nnmber Of Sheets Required


/* END CUSTOM FUNCTIONS */






/*
add_action('woocommerce_before_single_product', 'display_custom_inputs_on_product_page');
function display_custom_inputs_on_product_page() {

	global $product;

	$sheet_length = $product->get_length();
    $sheet_width = $product->get_width();
	$part_length = isset($_POST['custom_length']) ? floatval($_POST['custom_length']) : 0;
    $part_width = isset($_POST['custom_width']) ? floatval($_POST['custom_width']) : 0;
	$edge_margin  = 2;     
	$gap          = 4;     
	$quantity = isset($_POST['custom_qty']) ? intval($_POST['custom_qty']) : 0;

	echo $sheet_length . "<br>";
	echo $sheet_width . "<br>";
	echo $part_length . "<br>";
	echo $part_width . "<br>";
	echo $quantity . "<br>";

}
*/