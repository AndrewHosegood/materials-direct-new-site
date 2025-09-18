<?php
// Change WooCommerce shop page columns to 3
add_filter('loop_shop_columns', 'custom_woocommerce_shop_columns', 999);

function custom_woocommerce_shop_columns($columns) {
    return 3; // Set the number of columns you want
}