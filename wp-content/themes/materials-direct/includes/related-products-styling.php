<?php
// change related products columns to 3
add_filter( 'woocommerce_output_related_products_args', 'change_related_products_columns', 9999 );
function change_related_products_columns( $args ) {
    $args['columns'] = 3; // Change number of columns to 3
    $args['posts_per_page'] = 3; // Optional: limit number of products shown (change or remove as needed)
    return $args;
}
// change related products columns to 3

// Remove price from related products
add_action( 'woocommerce_after_shop_loop_item_title', 'remove_price_from_related_products', 1 );
function remove_price_from_related_products() {
    if ( is_product() ) { // only run on single product pages
        remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
    }
}
// Remove price from related products

// Remove Add to Cart button from Related Products and replace with "Select Options"
add_action( 'woocommerce_after_shop_loop_item', 'replace_related_products_add_to_cart', 1 );
function replace_related_products_add_to_cart() {
    if ( is_product() ) {
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
        add_action( 'woocommerce_after_shop_loop_item', 'custom_related_product_button', 20 );
    }
}

function custom_related_product_button() {
    global $product;

    echo '<a class="related__button" href="' . esc_url( get_permalink( $product->get_id() ) ) . '" class="button select-options-button">';
    echo __( 'Select Options', 'woocommerce' );
    echo '</a>';
}
// Remove Add to Cart button from Related Products and replace with "Select Options"

// Open wrapper div before product title in related products
add_action( 'woocommerce_before_shop_loop_item_title', 'open_related_grey_panel_wrapper', 20 );
function open_related_grey_panel_wrapper() {
    if ( is_product() ) { // only on single product pages
        echo '<div class="related__grey-panel">';
    }
}
// Open wrapper div before product title in related products

// Display SKU inside the wrapper (under the title)
add_action( 'woocommerce_after_shop_loop_item_title', 'display_related_product_sku', 5 );
function display_related_product_sku() {
    global $product;

    if ( is_product() ) {
        $sku = $product->get_sku();
        if ( $sku ) {
            echo '<div class="related__product-sku">SKU: ' . esc_html( $sku ) . '</div>';
        }
        // CLOSE the wrapper immediately after SKU
        echo '</div>'; // closes .related__grey-panel
    }
}
// Display SKU inside the wrapper (under the title)

// Display short description OUTSIDE the wrapper
add_action( 'woocommerce_after_shop_loop_item_title', 'display_related_product_excerpt', 6 );
function display_related_product_excerpt() {
    global $product;

    if ( is_product() ) {
        $excerpt = $product->get_short_description();
        if ( $excerpt ) {
            echo '<div class="related__product-excerpt" style="font-size: 14px; color: #444; margin: 6px 0 10px;">' . wp_kses_post( $excerpt ) . '</div>';
        }
    }
}
// Display short description OUTSIDE the wrapper