<?php
// Add function to display shipping dimensions on product page
add_action('woocommerce_single_product_summary', 'display_product_shipping_dimensions', 20);

function display_product_shipping_dimensions() {
    global $product;
    
    $shipping_length = $product->get_length();
    $shipping_width = $product->get_width();
    $dimension_unit  = get_option('woocommerce_dimension_unit');
    
    if (!empty($shipping_length) && !empty($shipping_width)) {
        $dimensions =  esc_html($shipping_length) . ' ' . esc_html(get_option('woocommerce_dimension_unit')) . ' x ' . esc_html($shipping_width) . ' ' . esc_html(get_option('woocommerce_dimension_unit'));
        echo '<p class="product-dimensions">Stock sheet size: ' . $dimensions .  '</p>';
    }

}
// Add function to display shipping dimensions on product page


// create an additional fee on the cart page

// Step 1: Add custom fee when product is added to cart
add_action('woocommerce_cart_calculate_fees', 'add_dimension_based_fee', 20, 1);

function add_dimension_based_fee($cart) {
    // Don't run in admin or if cart is empty
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // Loop through each cart item
    $total_dimension_fee = 0;

    foreach ($cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        $length = floatval($product->get_length());
        $width  = floatval($product->get_width());

        // If either length or width is missing, skip
        if ($length <= 0 && $width <= 0) continue;

        $quantity = $cart_item['quantity'];

        // Total for this item = (length + width) * quantity
        $fee = ($length + $width) * $quantity;
        $total_dimension_fee += $fee;
    }

    if ($total_dimension_fee > 0) {
        $cart->add_fee(__('Shipping Total', 'woocommerce'), $total_dimension_fee, true); // true = taxable
    }
}

// create an additional fee on the cart page



