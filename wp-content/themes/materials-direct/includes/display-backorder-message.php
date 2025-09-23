<?php
function display_backorder_stock_message_and_css() {
    if ( ! is_product() ) {
        return;
    }

    $product_id = get_queried_object_id();
    $product    = wc_get_product( $product_id );

    if ( ! $product ) {
        return;
    }

    if ( $product->is_on_backorder( 1 ) ) {
        // 1. Inject CSS into head
        add_action( 'wp_head', function() {
            echo '<style>
                #partial-backorder-message {
                    display: none !important;
                }
            </style>';
        });

        // 2. Display backorder message & JS before add-to-cart form
        add_action( 'woocommerce_before_add_to_cart_form', function() {
            echo '<div class="product-page__backorder-message">';
            echo '<p class="product-page__backorder-message-text"><strong>Notice:</strong> This order is currently on backorder only. Please allow 35 Days for complete order fulfillment with a 5% discount applied to the total order.</p>';
            echo '</div>';
            echo '<script type="text/javascript">
            jQuery(function($){
                $("#despatched_within").hide();
            });
            </script>';
        }, 100 );
    }
}
add_action( 'template_redirect', 'display_backorder_stock_message_and_css' );
