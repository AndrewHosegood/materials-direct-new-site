<?php
add_action('wp_head', 'inject_custom_css_for_single_product');
function inject_custom_css_for_single_product() {
    // Check if we're on a product page
    if (is_product()) {
        global $post;

        // Ensure $post is available and has an ID
        if (!is_a($post, 'WP_Post') || !isset($post->ID)) {
            return; // Exit early if $post is not valid
        }

        // Get the product object
        $product = wc_get_product($post->ID);
        
        // Ensure $product is a valid WC_Product object
        if (!is_a($product, 'WC_Product')) {
            return; // Exit early if $product is not valid
        }

        // Get the is_product_single ACF field value
        $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product->get_id()) : false;

        // Inject CSS if is_product_single is true
        if ($is_product_single) {
            echo '<style type="text/css">
                .woocommerce div.product form.cart div.quantity {
                    display: block;
                }
            </style>';
        }
    }
}