<?php
// Remove default price output
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);

// Add custom price display if ACF is true
add_action('woocommerce_single_product_summary', 'show_price_if_acf_true', 9);

function show_price_if_acf_true() {
    global $product;

    if (function_exists('get_field')) {
        $is_product_single = get_field('is_product_single', $product->get_id());

        if ($is_product_single) {
            echo '<p class="price">' . $product->get_price_html() . '</p>';
        }
    }
}