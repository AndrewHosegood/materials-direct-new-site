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
			'action-bar-top-right' => esc_html__( 'action-bar-top-right', 'materials-direct' ),
			'manufacturing-services' => esc_html__( 'manufacturing-services', 'materials-direct' ),
			'service-sectors' => esc_html__( 'service-sectors', 'materials-direct' ),
			'useful-links' => esc_html__( 'useful-links', 'materials-direct' ),
			'page-navigation' => esc_html__( 'page-navigation', 'materials-direct' ),
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

	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css', array(), '6.6.0' );

	// ✅ Enqueue main.css after WooCommerce styles
	wp_enqueue_style(
		'materials-direct-main', // Handle
		get_template_directory_uri() . '/css/main.css', // Path to main.css
		array( 'woocommerce-general' ), // Dependency
		'1.0', // Version
		'all' // Media
	);

}
add_action( 'wp_enqueue_scripts', 'materials_direct_scripts', 20 );

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


/* BEGIN CUSTOM FUNCTIONS */

// Generate and display PPP for testing
require_once('includes/acf_global_options.php');
// Generate and display PPP for testing

// Generate and display PPP for testing
require_once('includes/algorithm_and_core_functionality.php');
// Generate and display PPP for testing

// Display Ajax Page spinner
require_once('includes/allow-svg-upload.php');
// Display Ajax Page spinner

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

// Reposition product title on product page
require_once('includes/reposition-product-title-on-product-page.php');
// Reposition product title on product page

// remove different shipping address option on checkout page
require_once('includes/checkout-remove-different-shipping-address.php');
// remove different shipping address option on checkout page

/* END CUSTOM FUNCTIONS */

add_action( 'woocommerce_after_add_to_cart_quantity', 'add_discount_charts_to_product_page' );
function add_discount_charts_to_product_page() {
echo '<table class="product-page__discount-table">
<thead>
<tr><th class="product-page__discount-table-heading">Order Total</th>
<th class="product-page__discount-table-heading">Discount</th>
</tr></thead>
<tbody>
<tr>
<td class="product-page__discount-table-content">£1 - £500</td>
<td class="product-page__discount-table-content"><b>N/A</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£501 - £1000</td>
<td class="product-page__discount-table-content"><b>1% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£1001 - £2500</td>
<td class="product-page__discount-table-content"><b>2% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£2501 - £5000</td>
<td class="product-page__discount-table-content"><b>3% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£5001 - £10000</td>
<td class="product-page__discount-table-content"><b>4% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£10001 - £25000</td>
<td class="product-page__discount-table-content"><b>5% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£25001 - £50000</td>
<td class="product-page__discount-table-content"><b>7.5% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£50001+</td>
<td class="product-page__discount-table-content"><b>10% discount</b></td>
</tr>
</tbody>
</table>';
}



// Display the ACF "is_product_single" field at the top of the product page
add_action( 'woocommerce_before_single_product', 'display_is_product_single_field' );

function display_is_product_single_field() {
    global $post;

    if ( function_exists('get_field') ) {
        $is_product_single = get_field( 'is_product_single', $post->ID );

        if ( $is_product_single ) {
            echo '<div class="acf-is-product-single" style="padding:10px; background:#f9f9f9; margin-bottom:15px;">';
            echo '<strong>Is Product Single:</strong> Yes';
            echo '</div>';
        } else {
            echo '<div class="acf-is-product-single" style="padding:10px; background:#f9f9f9; margin-bottom:15px;">';
            echo '<strong>Is Product Single:</strong> No';
            echo '</div>';
        }
    }
}



