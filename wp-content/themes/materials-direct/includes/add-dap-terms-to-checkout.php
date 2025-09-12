<?php
add_action('woocommerce_review_order_before_payment', 'add_dap_notice_below_order_table');

function add_dap_notice_below_order_table() {
    echo '<div class="dap-delivery-notice" style="margin-top:-10px; margin-bottom: 35px; font-size:14px; color:#333;">
        <p>Your order will be delivered to your specified location under DAP terms.
        (<a href="' . esc_url(get_permalink(wc_get_page_id('terms'))) . '" target="_blank">See our terms and conditions</a>). 
        You are responsible for unloading and any import duties.</p>
    </div>';
}