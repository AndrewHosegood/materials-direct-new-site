<?php
add_action( 'woocommerce_single_product_summary', 'show_stock_sheet_size', 29 );
function show_stock_sheet_size() {
    global $product;

    if ( ! $product ) {
        return;
    }

    // Get shipping dimensions
    $width  = $product->get_width();
    $length = $product->get_length();

    // Only display if both width and length exist
    if ( $width && $length ) {
        // Format as cm (WooCommerce stores dimensions in the store unit, e.g. cm/mm/inch)
        echo '<p class="product-page__stock-sheet-size" style="margin:10px 0; font-weight:bold; font-size:16px;">';
        echo 'Stock sheet size: ' . esc_html( $length ) . 'cm x ' . esc_html( $width ) . 'cm';
        echo '</p>';
    }
}