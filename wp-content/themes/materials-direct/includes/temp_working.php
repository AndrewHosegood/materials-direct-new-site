<?php
add_action('woocommerce_checkout_create_order', 'add_custom_shipping_to_order', 20, 2);
function add_custom_shipping_to_order( $order, $calculated_shipping ) {
    if ( ! $order instanceof WC_Order ) {
        return;
    }

    // Log for debugging
    error_log( 'CUSTOM SHIPPING DEBUG - BEFORE ADD: ' . json_encode( [
        'cart_total' => WC()->cart->get_cart_contents_total(),
        'cart_shipping_total' => WC()->cart->get_cart_shipping_total(),
        'calculated_total_shipping' => $calculated_shipping,
    ] ) );

    // Create a shipping rate (WooCommerce-native)
    $shipping_rate = new WC_Shipping_Rate(
        'custom_shipping_method', // method ID
        'Shipping Total',         // method title
        $calculated_shipping,     // cost
        [],                       // taxes (empty array will let WooCommerce calculate)
        'custom_shipping_instance'// instance ID
    );

    // Add shipping to order
    $order->add_shipping( $shipping_rate );

    // Recalculate totals, including taxes
    $order->calculate_totals( true );

    // Log shipping items and taxes
    $shipping_items = $order->get_items( 'shipping' );
    foreach ( $shipping_items as $key => $shipping_item ) {
        error_log( 'CUSTOM SHIPPING DEBUG - Shipping Item #' . $key . ' after add: ' . print_r( $shipping_item->get_data(), true ) );
        error_log( 'CUSTOM SHIPPING DEBUG - Shipping Item #' . $key . ' get_taxes(): ' . print_r( $shipping_item->get_taxes(), true ) );
    }

    error_log( 'CUSTOM SHIPPING DEBUG - Order shipping tax total: ' . $order->get_shipping_tax() );
    error_log( 'CUSTOM SHIPPING DEBUG - Order total after shipping added: ' . $order->get_total() );
}