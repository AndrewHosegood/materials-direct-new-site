<?php

function my_custom_cart_count_fragment( $fragments ) {
    ob_start();
    $cart_count = 0;
    if ( WC()->cart && is_object( WC()->cart ) && method_exists( WC()->cart, 'get_cart' ) ) {
        $cart_count = count( WC()->cart->get_cart() );
    }
    ?>
    <a href="/basket/" id="header-cart-wrapper" class="header__cart-wrapper">
        <img class="header__cart-icon" src="<?php echo esc_url( get_template_directory_uri() . '/images/cart-icon.png' ); ?>" alt="Cart">
        <span class="header__cart-count"><?php echo esc_html( $cart_count ); ?></span>
    </a>
    <?php
    $fragments['#header-cart-wrapper'] = ob_get_clean();
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'my_custom_cart_count_fragment' );





add_action( 'wp_ajax_get_cart_count', 'my_get_cart_count_ajax' );
add_action( 'wp_ajax_nopriv_get_cart_count', 'my_get_cart_count_ajax' );
function my_get_cart_count_ajax() {
    if ( function_exists( 'WC' ) && WC()->cart ) {
        $count = count( WC()->cart->get_cart() ); // unique products count
        wp_send_json_success( $count );
    }
    wp_send_json_error();
}




function enqueue_custom_cart_scripts() {
    wp_enqueue_script(
        'custom-cart-update',
        get_template_directory_uri() . '/js/custom-cart-update.js',
        array('jquery'),
        '1.0',
        true
    );
    wp_localize_script( 'custom-cart-update', 'MyCart', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
    ));
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_cart_scripts' );
