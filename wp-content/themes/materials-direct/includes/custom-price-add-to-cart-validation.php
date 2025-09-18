<?php
add_filter('woocommerce_add_to_cart_validation', 'validate_custom_inputs_before_add_to_cart', 10, 5);
function validate_custom_inputs_before_add_to_cart($passed, $product_id, $quantity, $variation_id = null, $variations = null) {
    $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product_id) : false;

    // Skip validation for single products, as they don't require custom inputs
    if ($is_product_single) {
        return $passed;
    }

    // Check if required fields are set and valid
    if (
        !isset($_POST['custom_width']) || 
        !isset($_POST['custom_length']) || 
        !isset($_POST['custom_qty']) ||
        !is_numeric($_POST['custom_width']) || 
        !is_numeric($_POST['custom_length']) || 
        !is_numeric($_POST['custom_qty']) ||
        floatval($_POST['custom_width']) <= 0 ||
        floatval($_POST['custom_length']) <= 0 ||
        intval($_POST['custom_qty']) < 1
    ) {
        // Add an error notice if validation fails
        wc_add_notice(__('Please specify valid Width, Length, and Quantity before adding to cart.', 'woocommerce'), 'error');
        return false; // Prevent adding to cart
    }

    return $passed; // Allow adding to cart if validation passes
}