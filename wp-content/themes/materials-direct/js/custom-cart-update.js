jQuery(function($){
    function updateCount(){
        $.post(MyCart.ajax_url, { action: 'get_cart_count' }, function(response){
            if(response && response.success){
                $('#header-cart-wrapper .header__cart-count').text(response.data);
            }
        });
    }

    // Update when WooCommerce fragments are refreshed or item added/removed
    $(document.body).on('wc_fragments_refreshed added_to_cart removed_from_cart', function(){
        updateCount();
    });

    // Also update after clicking remove links (mini-cart/cart page)
    $(document.body).on('click', '.woocommerce-mini-cart__remove, .cart .product-remove a, a.remove', function(){
        setTimeout(updateCount, 400);
    });
});
