<?php
function bbloomer_split_product_individual_cart_items( $cart_item_data, $product_id ){
    $unique_cart_item_key = uniqid();
    $cart_item_data['unique_key'] = $unique_cart_item_key;
    return $cart_item_data;
}
   
add_filter( 'woocommerce_add_cart_item_data', 'bbloomer_split_product_individual_cart_items', 10, 2 );