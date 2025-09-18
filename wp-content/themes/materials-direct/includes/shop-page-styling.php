<?php
// Replace all "Add to Basket" buttons with a "Select Options" button linking to the product page
add_filter('woocommerce_loop_add_to_cart_link', 'replace_add_to_cart_with_select_options', 10, 2);

function replace_add_to_cart_with_select_options($button, $product) {
    // Get product URL
    $url = get_permalink($product->get_id());
    $label = 'Select Options';

    // Return a button linking to the product page
    return '<a href="' . esc_url($url) . '" class="button woocommerce-shop__btn">' . esc_html($label) . '</a>';
}

// Remove the price
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);