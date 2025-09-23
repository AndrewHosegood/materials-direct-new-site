<?php
/* proposed rounding fixes */
// add_filter('woocommerce_calculated_total', function($total, $cart) {
//     return round($total, 2);
// }, 10, 2);

add_filter('woocommerce_cart_item_subtotal', function($subtotal, $cart_item, $cart_item_key) {
    if (isset($cart_item['custom_inputs']['total_price'])) {
        $total_price = floatval($cart_item['custom_inputs']['total_price']);
        return wc_price($total_price);
    }
    return $subtotal;
}, 10, 3);
/* proposed rounding fixes */