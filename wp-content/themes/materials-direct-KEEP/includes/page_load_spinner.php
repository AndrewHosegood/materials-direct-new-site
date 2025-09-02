<?php
add_action('woocommerce_before_add_to_cart_button', 'custom_price_loading_spinner');
function custom_price_loading_spinner() {
    echo '
    <div id="price-spinner-overlay" style="display:none;">
        <div class="spinner-wrapper">
            <img src="http://localhost:8888/wp-content/uploads/2025/08/loading_md.gif" alt="Loading...">
        </div>
    </div>';
}
