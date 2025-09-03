<?php
// Remove the default product title
add_filter( 'the_title', 'remove_product_entry_title', 10, 2 );
function remove_product_entry_title( $title, $post_id ) {
    if ( is_singular( 'product' ) && in_the_loop() && is_main_query() ) {
        // Remove only the default entry-header title, not the WooCommerce one
        if ( did_action('woocommerce_before_single_product') === 0 ) {
            return '';
        }
    }
    return $title;
}


// Add the product title back at a custom position (you can change the priority as needed)
add_action( 'woocommerce_single_product_summary', 'custom_add_product_title', 5 );

function custom_add_product_title() {
    echo '<h1 class="product-page__title">' . get_the_title() . '</h1>';
}