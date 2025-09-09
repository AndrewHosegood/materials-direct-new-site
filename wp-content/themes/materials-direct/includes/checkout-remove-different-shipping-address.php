<?php
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );

add_action( 'woocommerce_before_order_notes', 'custom_additional_info_heading' );
function custom_additional_info_heading( $checkout ) {
    echo '<h3>Additional Information</h3>';
}