<?php
add_action( 'woocommerce_before_single_product', 'display_is_product_single_field' );

function display_is_product_single_field() {
    global $post;

    if ( function_exists('get_field') ) {
        $is_product_single = get_field( 'is_product_single', $post->ID );

        if ( $is_product_single ) {
            echo '<div class="acf-is-product-single" style="padding:10px; background:#f9f9f9; margin-bottom:15px;">';
            echo '<strong>Is Product Single:</strong> Yes';
            echo '</div>';
        } else {
            echo '<div class="acf-is-product-single" style="padding:10px; background:#f9f9f9; margin-bottom:15px;">';
            echo '<strong>Is Product Single:</strong> No';
            echo '</div>';
        }
    }
}