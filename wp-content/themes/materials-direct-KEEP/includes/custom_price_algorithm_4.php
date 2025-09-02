<?php
// Codys Exponential Decay Function 
function exponentialDecay($A, $k, $t) {
    return $A * exp(-$k * $t);
}
// End Codys Exponential Decay Function 

// Price Calculation Function
function calculate_product_price($product_id, $width, $length, $qty, $discount_rate = 0) {

    if (!is_numeric($product_id) || !is_numeric($width) || !is_numeric($length) || !is_numeric($qty) || !is_numeric($discount_rate)) {
        return new WP_Error('invalid_input', 'Invalid input data');
    }

    $width = floatval($width);
    $length = floatval($length);
    $qty = intval($qty);
    $discount_rate = floatval($discount_rate);

    if ($width <= 0 || $length <= 0 || $qty < 1) {
        return new WP_Error('invalid_input', 'Width, Length, and Quantity must be positive');
    }

    // Validate discount rate (ensure it's one of the allowed values)
    $valid_discount_rates = [0, 0.015, 0.02, 0.025, 0.03, 0.035, 0.04, 0.05];
    if (!in_array($discount_rate, $valid_discount_rates)) {
        return new WP_Error('invalid_discount', 'Invalid discount rate');
    }

    $cost_per_cm2 = floatval(get_field('cost_per_cm', $product_id));
    $item_border = floatval(get_field('border_around', $product_id));
    $globalPriceAdjust = floatval(get_field('global_adjust_square_rectangle', 'options'));

    // Core calculation
    $borderSize = $item_border * 2;
    $setLength = $length / 10;
    $setWidth = $width / 10;
    $maxSetWidth = $setWidth + $borderSize;
    $maxSetLength = $setLength + $borderSize;
    $ppp = $maxSetLength * $maxSetWidth * $cost_per_cm2;
    $totalSqMm = $setWidth * $setLength;

    // Codys algorith
    $A = 0.68;      // Maximum Cost Factor possible
    $k = 0.0018;    // Decay Rate
    $t = $totalSqMm; // mm2 of part
    $costFactorResult = exponentialDecay($A, $k, $t);
    // End Codys algorith

    //$discountRate = 0; // Hardcoded for now
    $finalPppOnAva = $ppp + $costFactorResult;
    $discountAmount = $finalPppOnAva * $discount_rate;
    $finalPppOnAva = $finalPppOnAva - $discountAmount;

    $adjustedPrice = $finalPppOnAva * $globalPriceAdjust;
    $total_price = $adjustedPrice * $qty;

    return round($total_price, 2);
}
// End Price Calculation Function


// HTML Form with Spinner
add_action('woocommerce_before_add_to_cart_button', 'custom_price_input_fields');
function custom_price_input_fields() {
    global $product;
    $product_id = $product->get_id();

    echo '<div id="custom-price-calc" class="custom-price-calc">
        <label class="custom-price-calc__label">Width (cm): <input class="custom-price-calc__input" type="number" id="input_width" name="custom_width" min="0.01" step="0.01" required></label>
        <label class="custom-price-calc__label">Length (cm): <input class="custom-price-calc__input" type="number" id="input_length" name="custom_length" min="0.01" step="0.01" required></label>
        <label class="custom-price-calc__label">Quantity: <input class="custom-price-calc__input" type="number" id="input_qty" name="custom_qty" value="1" min="1" step="1" required></label>
        <label class="custom-price-calc__label">Delivery Time: 
            <select class="custom-price-calc__input" id="input_discount_rate" name="custom_discount_rate">
                <option value="0" selected="selected">24Hrs (working day)</option>
                <option value="0.015">48Hrs (working days) (1.5% Discount)</option>
                <option value="0.02">5 Days (working days) (2% Discount)</option>
                <option value="0.025">7 Days (working days) (2.5% Discount)</option>
                <option value="0.03">12 Days (working days) (3% Discount)</option>
                <option value="0.035">14 Days (working days) (3.5% Discount)</option>
                <option value="0.04">30 Days (working days) (4% Discount)</option>
                <option value="0.05">35 Days (working days) (5% Discount)</option>
            </select>
        </label>
        <button type="button" id="generate_price">Generate Price</button>
        <div id="price-spinner-overlay" style="display:none;">
            <div class="spinner-wrapper">
                <img src="' . esc_url(get_theme_file_uri('/images/loading_md.gif')) . '" alt="Loading...">
            </div>
        </div>
        <div id="custom_price_display"></div>
        <input type="hidden" id="custom_price" name="custom_price" value="">
        <div id="shipping-address-form">
            <h3>Shipping Address?</h3>
            <label class="custom-price-calc__label">Street Address: <input type="text" id="input_street_address" name="custom_street_address" required></label>
            <label class="custom-price-calc__label">Address Line 2: <input type="text" id="input_address_line2" name="custom_address_line2"></label>
            <label class="custom-price-calc__label">City: <input type="text" id="input_city" name="custom_city" required></label>
            <label class="custom-price-calc__label">County/State: <input type="text" id="input_county_state" name="custom_county_state" required></label>
            <label class="custom-price-calc__label">ZIP/Postal Code: <input type="text" id="input_zip_postal" name="custom_zip_postal" required></label>
            <label class="custom-price-calc__label">Country: 
                <select id="input_country" name="custom_country" required>
                    <option value="United Kingdom" selected="selected">United Kingdom</option>
                </select>
            </label>
        </div>
    </div>';
}
// HTML Form with Spinner


// Secure Price Calculation in PHP
add_action('wp_ajax_calculate_secure_price', 'calculate_secure_price');
add_action('wp_ajax_nopriv_calculate_secure_price', 'calculate_secure_price');

function calculate_secure_price() {
    // Verify nonce for security
    check_ajax_referer('custom_price_nonce', 'nonce');

    $product_id = intval($_POST['product_id']);
    $width = floatval($_POST['width']);
    $length = floatval($_POST['length']);
    $qty = intval($_POST['qty']);
    $discount_rate = floatval($_POST['discount_rate']);

    $result = calculate_product_price($product_id, $width, $length, $qty, $discount_rate);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }

    wp_send_json_success(['price' => $result]);
}
// End Secure Price Calculation in PHP


// Enqueue JS with Nonce
add_action('wp_enqueue_scripts', function() {
    if (is_product()) {
        wp_enqueue_script('custom-price-calc', get_stylesheet_directory_uri() . '/js/custom-price-calc-4.js', ['jquery'], null, true);
        wp_localize_script('custom-price-calc', 'ajax_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'product_id' => get_the_ID(),
            'nonce' => wp_create_nonce('custom_price_nonce'),
        ]);
    }
});
// End Enqueue JS with Nonce

// Store Shipping Address in Session and Cart
add_filter('woocommerce_add_cart_item_data', 'add_custom_price_cart_item_data_secure', 10, 2);
function add_custom_price_cart_item_data_secure($cart_item_data, $product_id) {
    if (
        isset($_POST['custom_width']) &&
        isset($_POST['custom_length']) &&
        isset($_POST['custom_qty']) &&
        isset($_POST['custom_price']) &&
        isset($_POST['custom_discount_rate']) &&
        isset($_POST['custom_street_address']) &&
        isset($_POST['custom_city']) &&
        isset($_POST['custom_county_state']) &&
        isset($_POST['custom_zip_postal']) &&
        isset($_POST['custom_country'])
    ) {
        $cart_item_data['custom_inputs'] = [
            'width' => floatval($_POST['custom_width']),
            'length' => floatval($_POST['custom_length']),
            'qty' => intval($_POST['custom_qty']),
            'price' => floatval($_POST['custom_price']),
            'discount_rate' => floatval($_POST['custom_discount_rate']),
            'shipping_address' => [
                'street_address' => sanitize_text_field($_POST['custom_street_address']),
                'address_line2' => sanitize_text_field($_POST['custom_address_line2']),
                'city' => sanitize_text_field($_POST['custom_city']),
                'county_state' => sanitize_text_field($_POST['custom_county_state']),
                'zip_postal' => sanitize_text_field($_POST['custom_zip_postal']),
                'country' => sanitize_text_field($_POST['custom_country']),
            ],
        ];

        // Store shipping address in WooCommerce session
        WC()->session->set('custom_shipping_address', $cart_item_data['custom_inputs']['shipping_address']);
    }
    return $cart_item_data;
}

add_filter('woocommerce_get_cart_item_from_session', function($item, $values) {
    if (isset($values['custom_inputs'])) {
        $item['custom_inputs'] = $values['custom_inputs'];
    }
    return $item;
}, 10, 2);

add_action('woocommerce_before_calculate_totals', 'apply_secure_custom_price');
function apply_secure_custom_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['custom_inputs'])) {
            $product_id = $cart_item['product_id'];
            $width = $cart_item['custom_inputs']['width'];
            $length = $cart_item['custom_inputs']['length'];
            $qty = $cart_item['custom_inputs']['qty'];
            $discount_rate = isset($cart_item['custom_inputs']['discount_rate']) ? $cart_item['custom_inputs']['discount_rate'] : 0;

            $total_price = calculate_product_price($product_id, $width, $length, $qty, $discount_rate);

            if (!is_wp_error($total_price)) {
                $cart_item['data']->set_price($total_price);
            }
        }
    }
}


// Display Inputs in Cart/Checkout
add_filter('woocommerce_get_item_data', 'show_custom_input_details_in_cart', 10, 2);
function show_custom_input_details_in_cart($item_data, $cart_item) {
    if (!empty($cart_item['custom_inputs'])) {
        $item_data[] = ['name' => 'Width', 'value' => $cart_item['custom_inputs']['width'] . ' cm'];
        $item_data[] = ['name' => 'Length', 'value' => $cart_item['custom_inputs']['length'] . ' cm'];
        $item_data[] = ['name' => 'Quantity', 'value' => $cart_item['custom_inputs']['qty']];
        if (isset($cart_item['custom_inputs']['price'])) {
            $item_data[] = ['name' => 'Custom Price', 'value' => wc_price($cart_item['custom_inputs']['price'])];
        }
        if (isset($cart_item['custom_inputs']['discount_rate'])) {
            $discount_labels = [
                0 => '24Hrs (working day)',
                0.015 => '48Hrs (working days) (1.5% Discount)',
                0.02 => '5 Days (working days) (2% Discount)',
                0.025 => '7 Days (working days) (2.5% Discount)',
                0.03 => '12 Days (working days) (3% Discount)',
                0.035 => '14 Days (working days) (3.5% Discount)',
                0.04 => '30 Days (working days) (4% Discount)',
                0.05 => '35 Days (working days) (5% Discount)',
            ];
            $discount_rate = $cart_item['custom_inputs']['discount_rate'];
            $item_data[] = [
                'name' => 'Delivery Time',
                'value' => isset($discount_labels[$discount_rate]) ? $discount_labels[$discount_rate] : 'Unknown',
            ];
    }
    /*
    if (isset($cart_item['custom_inputs']['shipping_address'])) {
            $address = $cart_item['custom_inputs']['shipping_address'];
            $address_formatted = $address['street_address'];
            if (!empty($address['address_line2'])) {
                $address_formatted .= ', ' . $address['address_line2'];
            }
            $address_formatted .= ', ' . $address['city'] . ', ' . $address['county_state'] . ', ' . $address['zip_postal'] . ', ' . $address['country'];
            $item_data[] = [
                'name' => 'Shipping Address',
                'value' => esc_html($address_formatted),
            ];
    }
    */
}
    return $item_data;
}
// End Display Inputs in Cart/Checkout


// Display Shipping Address on Checkout Page
add_action('woocommerce_checkout_after_customer_details', 'display_shipping_address_on_checkout');
function display_shipping_address_on_checkout() {

    $shipping_address = WC()->session->get('custom_shipping_address');
    if ($shipping_address) {
        echo '<div class="custom-shipping-address">';
        echo '<h3>Shipping Details</h3>';
        echo '<p>' . esc_html($shipping_address['street_address']) . '</p>';
        if (!empty($shipping_address['address_line2'])) {
            echo '<p>' . esc_html($shipping_address['address_line2']) . '</p>';
        }
        echo '<p>' . esc_html($shipping_address['city']) . ', ' . esc_html($shipping_address['county_state']) . ', ' . esc_html($shipping_address['zip_postal']) . '</p>';
        echo '<p>' . esc_html($shipping_address['country']) . '</p>';
        echo '</div>';
    }
}
// Display Shipping Address on Checkout Page


// Display Shipping Address on Thank You Page
add_action('woocommerce_thankyou', 'display_shipping_address_on_thankyou', 10, 1);
function display_shipping_address_on_thankyou($order_id) {

    $order = wc_get_order($order_id);
    $shipping_address = false;

    foreach ($order->get_items() as $item_id => $item) {
        $custom_shipping_address = $item->get_meta('custom_shipping_address');
        if ($custom_shipping_address && is_array($custom_shipping_address)) {
            $shipping_address = $custom_shipping_address;
            break;
        }
    }

    if ($shipping_address) {
        echo '<div class="custom-shipping-address">';
        echo '<h2 class="woocommerce-column__title">Shipping Details</h2>';
        echo '<address>';
        echo esc_html($shipping_address['street_address']) . '<br>';
        if (!empty($shipping_address['address_line2'])) {
            echo esc_html($shipping_address['address_line2']) . '<br>';
        }
        //echo esc_html($shipping_address['city']) . ', ' . esc_html($shipping_address['county_state']) . ', ' . esc_html($shipping_address['zip_postal']) . '<br>';

        echo esc_html($shipping_address['city']) . '<br>';
        echo esc_html($shipping_address['county_state']) . '<br>';
        echo esc_html($shipping_address['zip_postal']) . '<br>';
        echo esc_html($shipping_address['country']) . '<br>';
        echo '</address>';
        echo '</div>';
    }
}
// Display Shipping Address on Thank You Page



// Save Shipping Address to Order Meta and Display in Emails
add_action('woocommerce_checkout_create_order_line_item', 'save_shipping_address_to_order_item', 10, 4);
function save_shipping_address_to_order_item($item, $cart_item_key, $values, $order) {
    if (isset($values['custom_inputs']['shipping_address'])) {
        $shipping_address = $values['custom_inputs']['shipping_address'];
        $item->add_meta_data('custom_shipping_address', $shipping_address, true);
    }
}
// Save Shipping Address to Order Meta and Display in Emails


// Display Shipping Address in Order Emails OK

add_action('woocommerce_email_customer_details', 'add_custom_shipping_address_below_billing', 25, 4);
function add_custom_shipping_address_below_billing($order, $sent_to_admin, $plain_text, $email) {
    // Loop through order items to find the first shipping address
    $shipping_address = null;

    foreach ($order->get_items() as $item_id => $item) {
        $meta_address = $item->get_meta('custom_shipping_address');
        if (!empty($meta_address['street_address'])) {
            $shipping_address = $meta_address;
            break; // only show first one
        }
    }

    if (!$shipping_address) return;

    if ($plain_text) {
        echo "\nShipping Address:\n";
        echo $shipping_address['street_address'] . "\n";
        if (!empty($shipping_address['address_line2'])) {
            echo $shipping_address['address_line2'] . "\n";
        }
        echo $shipping_address['city'] . ', ' . $shipping_address['county_state'] . ', ' . $shipping_address['zip_postal'] . "\n";
        echo $shipping_address['country'] . "\n";
    } else {
        echo '<div class="custom-shipping-address" style="margin-top:10px;">';
        echo '<h3>Shipping Address</h3>';
        echo esc_html($shipping_address['street_address']) . '<br>';
        if (!empty($shipping_address['address_line2'])) {
            echo esc_html($shipping_address['address_line2']) . '<br>';
        }
        echo esc_html($shipping_address['city']) . ', ' . esc_html($shipping_address['county_state']) . ', ' . esc_html($shipping_address['zip_postal']) . '<br>';
        echo esc_html($shipping_address['country']) . '<br>';
        echo '</div>';
    }
}

// Display Shipping Address in Order Emails OK


// Save the WP Session shipping address to the orders page
add_action('woocommerce_checkout_create_order', 'update_order_shipping_fields', 20, 2);
function update_order_shipping_fields($order, $data) {
    // Try to get shipping address from session
    $shipping_address = WC()->session->get('custom_shipping_address');

    if ($shipping_address && is_array($shipping_address)) {
        // Map your custom fields to WooCommerce shipping fields
        $order->set_shipping_first_name(isset($data['billing_first_name']) ? $data['billing_first_name'] : '');
        $order->set_shipping_last_name(isset($data['billing_last_name']) ? $data['billing_last_name'] : '');
        $order->set_shipping_company(isset($data['billing_company']) ? $data['billing_company'] : '');
        $order->set_shipping_address_1($shipping_address['street_address']);
        $order->set_shipping_address_2(!empty($shipping_address['address_line2']) ? $shipping_address['address_line2'] : '');
        $order->set_shipping_city($shipping_address['city']);
        $order->set_shipping_state($shipping_address['county_state']);
        $order->set_shipping_postcode($shipping_address['zip_postal']);
        $order->set_shipping_country($shipping_address['country']);
    }
}
// Save the WP Session shipping address to the orders page


// Display Shipping Address in Admin Order Details OK
/*
add_action('woocommerce_admin_order_item_values', 'display_shipping_address_in_admin_order', 10, 3);
function display_shipping_address_in_admin_order($product, $item, $item_id) {
    $shipping_address = $item->get_meta('custom_shipping_address');
    if ($shipping_address) {
        echo '<div class="custom-shipping-address">';
        echo '<strong>Shipping Address?:</strong><br>';
        echo esc_html($shipping_address['street_address']) . '<br>';
        if (!empty($shipping_address['address_line2'])) {
            echo esc_html($shipping_address['address_line2']) . '<br>';
        }
        echo esc_html($shipping_address['city']) . ', ' . esc_html($shipping_address['county_state']) . ', ' . esc_html($shipping_address['zip_postal']) . '<br>';
        echo esc_html($shipping_address['country']) . '<br>';
        echo '</div>';
    }
}
    */
// Display Shipping Address in Admin Order Details OK