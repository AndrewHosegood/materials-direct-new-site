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

	// Enqueue main.css after WooCommerce styles
	wp_enqueue_style(
		'materials-direct-main',
		get_template_directory_uri() . '/css/main.css',
		array( 'woocommerce-general' ), 
		filemtime( get_template_directory() . '/css/main.css' ), 
		'all' 
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


add_action( 'wp_enqueue_scripts', function() {
    if ( function_exists( 'is_woocommerce' ) ) {
        wp_enqueue_script( 'wc-cart-fragments' );
    }
}, 20 );



/* BEGIN CUSTOM FUNCTIONS */

// Generate and display PPP for testing
require_once('includes/acf_global_options.php');
// Generate and display PPP for testing

// THEME ALGORITHM AND CORE FUNCTIONALITY ****
require_once('includes/algorithm_and_core_functionality.php');
// THEME ALGORITHM AND CORE FUNCTIONALITY ****

// Lets make sure that a product cannot be added to cart if width length and qty is empty
require_once('includes/custom-price-add-to-cart-validation.php');
// Lets make sure that a product cannot be added to cart if width length and qty is empty

// Display Ajax Page spinner
require_once('includes/allow-svg-upload.php');
// Display Ajax Page spinner

// Reposition product title on product page
require_once('includes/reposition-product-title-on-product-page.php');
// Reposition product title on product page

// remove different shipping address option on checkout page
require_once('includes/checkout-remove-different-shipping-address.php');
// remove different shipping address option on checkout page

// remove different shipping address option on checkout page
require_once('includes/add-discount-chart-to-product-page.php');
// remove different shipping address option on checkout page

// Inject custom css in header for is_single_product
require_once('includes/inject-custom-css-in-head-for-issingleproduct.php');
// Inject custom css in header for is_single_product

// Add dab terms to checkout 
require_once('includes/add-dap-terms-to-checkout.php');
// Add dab terms to checkout

// Prevent duplicate orders merging in the cart
require_once('includes/prevent-duplicate-order-merge.php');
// Prevent duplicate orders merging in the cart

// Related products styling
require_once('includes/related-products-styling.php');
// Related products styling

// Shop page styling
require_once('includes/shop-page-styling.php');
// Shop page styling

// Custom product page tabs
require_once('includes/custom-product-tabs.php');
// Custom product page tabs

// Display stock availability on product page based on stock status
require_once('includes/display-stock-availability.php');
// Display stock availability on product page based on stock status

// Display backorder message on product page
require_once('includes/display-backorder-message.php');
// Display backorder message on product page

// conditionally show/hide product price based on single product
require_once('includes/conditionally-hide-price.php');
// conditionally show/hide product price based on single product

// Rename listed items on thankyou page
require_once('includes/rename-listed-items-on-thankyou-page.php');
// Rename listed items on thankyou page

// Cart fragments functionality for cart quantity icon in header
require_once('includes/cart-fragments-functionality-for-cart-icon.php');
// Cart fragments functionality for cart quantity icon in header

// Show stock sheet size on product page
require_once('includes/show-stock-sheet-size-on-product-page.php');
// Show stock sheet size on product page

// change the shop grid to 3 columns
require_once('includes/change-shop-grid-to-3-columns.php');
// change the shop grid to 3 columns

/* END CUSTOM FUNCTIONS */




/*
function display_part_backorder_stock_message() {
    global $product;

    $product_id = $product->get_id();
    $stock_quantity = $product->get_stock_quantity();

	// we need to get the sheets required

    $is_backorder = $stock_quantity <= 0;

    if ($is_backorder) {
        echo '<div class="product-page__backorder-message">';
        echo '<p class="product-page__backorder-message-text"><strong>Notice:</strong> This order exceeds current stock, it requires additional sheets to be back ordered. Please allow 35 Days to complete the back ordered items. A 5% discount will apply to these parts. </p>';
        echo '</div>';
        echo '<script type="text/javascript">
        jQuery(function($){
            $("#despatched_within").hide();
        });
        </script>';
    }

}
add_action('woocommerce_before_add_to_cart_form', 'display_part_backorder_stock_message', 100);
*/




// Temporary - display acf is_single_product on product page
//require_once('includes/display-is-single-product-on-product-page.php');
// Temporary - display acf is_single_product on product page



