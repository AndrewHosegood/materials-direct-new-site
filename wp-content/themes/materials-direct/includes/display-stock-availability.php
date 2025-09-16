<?php
add_filter( 'woocommerce_get_availability', 'custom_get_availability', 1, 2);

function custom_get_availability( $availability, $_product ) {
    $stock = 0;

    if ( $_product->is_type( 'variable' ) ) {
        // Calculate total stock for variable products
        foreach ( $_product->get_children() as $variation_id ) {
            $variation = wc_get_product( $variation_id );
            $stock += $variation->get_stock_quantity();
        }
    } else {
        // Get stock for simple products
        $stock = $_product->get_stock_quantity();
    }

    $post = get_post();
    $singleprod = get_field( 'show_price', $post->ID );

    if ( $_product->is_in_stock() ) {
        $availability['availability'] = sprintf( __( '%s Sheets In Stock', 'woocommerce' ), $stock );
    }

    if ( $_product->is_in_stock() && $singleprod == 1 ) {
        $availability['availability'] = sprintf( __( '%s In Stock', 'woocommerce' ), $stock );
    }

    if ( !$_product->is_in_stock() ) {
        $availability['availability'] = __( 'Product is on backorder, Please allow 35 working days for delivery of this item', 'woocommerce' );
    }

    if ( $_product->is_on_backorder( 1 ) ) {
        //$availability['availability'] = __( 'Product is on backorder', 'woocommerce' );
		$availability['availability'] = __( 'Available with lead time | <a href="/contact/" id="leadtime" class="backorder-leadtime" data-tooltip="Contact us for information on lead times">Contact Us</a>', 'woocommerce' );
    }

    return $availability;
}