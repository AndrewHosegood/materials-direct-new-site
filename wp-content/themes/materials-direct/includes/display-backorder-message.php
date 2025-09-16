<?php
function display_backorder_stock_message() {
    global $product;

    $product_id = $product->get_id();
    $stock_quantity = $product->get_stock_quantity();
    $is_backorder = $stock_quantity <= 0;

    if ($is_backorder) {
        echo '<div class="product-page__backorder-message">';
        echo '<p class="product-page__backorder-message-text"><strong>Notice:</strong> This order is currently on backorder only. Please allow 35 Days for complete order fulfillment with a 5% discount applied to the total order.</p>';
        echo '</div>';
        echo '<script type="text/javascript">
        jQuery(function($){
            $("#despatched_within").hide();
        });
        </script>';
    }




}
add_action('woocommerce_before_add_to_cart_form', 'display_backorder_stock_message', 100);