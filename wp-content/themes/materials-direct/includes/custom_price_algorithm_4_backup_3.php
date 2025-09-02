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
    $totalSqMm = $setWidth * $setLength * 100;
    //$totalSqMm = 4640;

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
    //$finalPppOnAva = 1.0735292284545517;

    // Debug logging
    error_log("Debug [Product ID: $product_id, Width: $width mm, Length: $length mm, Qty: $qty, Discount: $discount_rate]:");
    error_log("  totalSqMm: $totalSqMm");
    error_log("  borderSize: $borderSize");
    error_log("  cost_per_cm2: $cost_per_cm2");
    error_log("  maxSetWidth: $maxSetWidth");
    error_log("  maxSetLength: $maxSetLength");
    error_log("  SetWidth: $setWidth");
    error_log("  SetLength: $setLength");
    error_log("  ppp: $ppp");
    error_log("  costFactorResult: $costFactorResult");
    error_log("  finalPppOnAva: $finalPppOnAva");

    $adjustedPrice = $finalPppOnAva * $globalPriceAdjust;
    $total_price = $adjustedPrice * $qty;

    return round($total_price, 2);
}
// End Price Calculation Function


// HTML Form with Spinner
add_action('woocommerce_before_add_to_cart_button', 'custom_price_input_fields_prefill');
function custom_price_input_fields_prefill() {
    global $product;
    $product_id = $product->get_id();

    // Get shipping address from session
    $shipping_address = WC()->session->get('custom_shipping_address', []);

    $street_address = !empty($shipping_address['street_address']) ? esc_attr($shipping_address['street_address']) : '';
    $address_line2 = !empty($shipping_address['address_line2']) ? esc_attr($shipping_address['address_line2']) : '';
    $city = !empty($shipping_address['city']) ? esc_attr($shipping_address['city']) : '';
    $county_state = !empty($shipping_address['county_state']) ? esc_attr($shipping_address['county_state']) : '';
    $zip_postal = !empty($shipping_address['zip_postal']) ? esc_attr($shipping_address['zip_postal']) : '';
    $country = !empty($shipping_address['country']) ? esc_attr($shipping_address['country']) : 'United Kingdom';

    echo '<div id="custom-price-calc" class="custom-price-calc">

        <!-- Price Inputs -->
        <label class="custom-price-calc__label">Width (MM): <input class="custom-price-calc__input" type="number" id="input_width" name="custom_width" min="0.01" step="0.01" required></label>
        <label class="custom-price-calc__label">Length (MM): <input class="custom-price-calc__input" type="number" id="input_length" name="custom_length" min="0.01" step="0.01" required></label>
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

        <!-- Shipping Address Inputs -->
        <div id="shipping-address-form">
            <h3>Shipping Address</h3>
            <label class="custom-price-calc__label">Street Address: <input type="text" id="input_street_address" name="custom_street_address" value="' . $street_address . '" required></label>
            <label class="custom-price-calc__label">Address Line 2: <input type="text" id="input_address_line2" name="custom_address_line2" value="' . $address_line2 . '"></label>
            <label class="custom-price-calc__label">City: <input type="text" id="input_city" name="custom_city" value="' . $city . '" required></label>
            <label class="custom-price-calc__label">County/State: <input type="text" id="input_county_state" name="custom_county_state" value="' . $county_state . '" required></label>
            <label class="custom-price-calc__label">ZIP/Postal Code: <input type="text" id="input_zip_postal" name="custom_zip_postal" value="' . $zip_postal . '" required></label>
            <label class="custom-price-calc__label">Country: 
                <select id="input_country" name="custom_country" required>
                    <option value="United Kingdom"' . selected($country, 'United Kingdom', false) . '>United Kingdom</option>
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

    if (!is_numeric($product_id) || $width <= 0 || $length <= 0 || $qty < 1 || !is_numeric($discount_rate)) {
        wp_send_json_error(['message' => 'Invalid input values.']);
    }

    if (!empty($_POST['street_address'])) {
        $shipping_address = [
            'street_address' => sanitize_text_field($_POST['street_address']),
            'address_line2' => sanitize_text_field($_POST['address_line2']),
            'city'          => sanitize_text_field($_POST['city']),
            'county_state'  => sanitize_text_field($_POST['county_state']),
            'zip_postal'    => sanitize_text_field($_POST['zip_postal']),
            'country'       => sanitize_text_field($_POST['country']),
        ];
        WC()->session->set('custom_shipping_address', $shipping_address);
    }

    $price = calculate_product_price($product_id, $width, $length, $qty, $discount_rate);

    if (is_wp_error($price)) {
        wp_send_json_error(['message' => $price->get_error_message()]);
    }


    wp_send_json_success(['price' => round($price, 2)]);
}
// End Secure Price Calculation in PHP


// Enqueue JS with Nonce
add_action('wp_enqueue_scripts', function() {
    if (is_product()) {
        wp_enqueue_script('custom-price-calc', get_stylesheet_directory_uri() . '/js/custom-price-calc-5.js', ['jquery'], null, true);
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

        // Get product object to retrieve sheet dimensions
        $product = wc_get_product($product_id);
        $sheet_length_mm = $product->get_length() * 10; // Convert cm to mm
        $sheet_width_mm = $product->get_width() * 10;   // Convert cm to mm
        $part_width_mm = floatval($_POST['custom_width']);
        $part_length_mm = floatval($_POST['custom_length']);
        $quantity = intval($_POST['custom_qty']);

        // Calculate sheets required
        $sheet_result = calculate_sheets_required(
            $sheet_width_mm,
            $sheet_length_mm,
            $part_width_mm,
            $part_length_mm,
            $quantity
        );

        $cart_item_data['custom_inputs'] = [
            'width' => floatval($_POST['custom_width']),
            'length' => floatval($_POST['custom_length']),
            'qty' => intval($_POST['custom_qty']),
            'price' => floatval($_POST['custom_price']),
            'discount_rate' => floatval($_POST['custom_discount_rate']),
            'sheets_required' => $sheet_result['sheets_required'], // Store sheets_required
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

// Save sheets_required to order item meta
add_action('woocommerce_checkout_create_order_line_item', 'save_sheets_required_to_order_item', 10, 4);
function save_sheets_required_to_order_item($item, $cart_item_key, $values, $order) {
    if (isset($values['custom_inputs']['sheets_required'])) {
        $item->add_meta_data('sheets_required', $values['custom_inputs']['sheets_required'], true);
    }
    if (isset($values['custom_inputs']['shipping_address'])) {
        $item->add_meta_data('custom_shipping_address', $values['custom_inputs']['shipping_address'], true);
    }
}

// Update display function to show sheets_required in cart and checkout
add_filter('woocommerce_get_item_data', 'show_custom_input_details_in_cart', 10, 2);
function show_custom_input_details_in_cart($item_data, $cart_item) {
    if (!empty($cart_item['custom_inputs'])) {
        // Width
        if (isset($cart_item['custom_inputs']['width'])) {
            $item_data[] = [
                'name' => 'Width',
                'value' => $cart_item['custom_inputs']['width'] . ' mm'
            ];
        }

        // Length
        if (isset($cart_item['custom_inputs']['length'])) {
            $item_data[] = [
                'name' => 'Length',
                'value' => $cart_item['custom_inputs']['length'] . ' mm'
            ];
        }

        // Quantity
        if (isset($cart_item['custom_inputs']['qty'])) {
            $item_data[] = [
                'name' => 'Quantity',
                'value' => $cart_item['custom_inputs']['qty']
            ];
        }

        // Custom Price
        if (isset($cart_item['custom_inputs']['price'])) {
            $item_data[] = [
                'name' => 'Custom Price',
                'value' => wc_price($cart_item['custom_inputs']['price'])
            ];
        }

        // Delivery Time / Discount Rate
        if (isset($cart_item['custom_inputs']['discount_rate'])) {
            $discount_labels = [
                '0' => '24Hrs (working day)',
                '0.015' => '48Hrs (working days) (1.5% Discount)',
                '0.02' => '5 Days (working days) (2% Discount)',
                '0.025' => '7 Days (working days) (2.5% Discount)',
                '0.03' => '12 Days (working days) (3% Discount)',
                '0.035' => '14 Days (working days) (3.5% Discount)',
                '0.04' => '30 Days (working days) (4% Discount)',
                '0.05' => '35 Days (working days) (5% Discount)',
            ];
            $rate_key = (string)$cart_item['custom_inputs']['discount_rate'];
            $item_data[] = [
                'name' => 'Delivery Time',
                'value' => isset($discount_labels[$rate_key]) ? $discount_labels[$rate_key] : 'Unknown'
            ];
        }

        // Sheets Required
        if (isset($cart_item['custom_inputs']['sheets_required'])) {
            $item_data[] = [
                'name' => 'Sheets Required',
                'value' => $cart_item['custom_inputs']['sheets_required']
            ];
        }
    }
    return $item_data;
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



// Display Shipping Address in Order Emails

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

// Display Shipping Address in Order Emails



// Display global shipping address below cart tables
add_action('woocommerce_after_cart_table', 'display_global_shipping_address_cart');
function display_global_shipping_address_cart() {
    $shipping_address = WC()->session->get('custom_shipping_address');
    if (!$shipping_address) return;

    echo '<div class="global-shipping-address" style="margin-top:20px;">';
    echo '<h3>Shipping Details</h3>';
    echo esc_html($shipping_address['street_address']) . '<br>';
    if (!empty($shipping_address['address_line2'])) {
        echo esc_html($shipping_address['address_line2']) . '<br>';
    }
    echo esc_html($shipping_address['city']) . ', ' . esc_html($shipping_address['county_state']) . ', ' . esc_html($shipping_address['zip_postal']) . '<br>';
    echo esc_html($shipping_address['country']) . '<br>';
    echo '</div>';
}
// Display global shipping address below cart tables



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



// Clear session after order is placed

add_action('woocommerce_checkout_order_processed', 'clear_custom_shipping_session', 10, 1);
function clear_custom_shipping_session($order_id) {
    WC()->session->set('custom_shipping_address', null);
}

// Clear session after order is placed


// Add function to display shipping dimensions on product page
add_action('woocommerce_single_product_summary', 'display_product_shipping_dimensions', 20);

function display_product_shipping_dimensions() {
    global $product;
    
    $shipping_length = $product->get_length();
    $shipping_width = $product->get_width();
    $dimension_unit  = get_option('woocommerce_dimension_unit');
    
    if (!empty($shipping_length) && !empty($shipping_width)) {
        $dimensions =  esc_html($shipping_length) . ' ' . esc_html(get_option('woocommerce_dimension_unit')) . ' x ' . esc_html($shipping_width) . ' ' . esc_html(get_option('woocommerce_dimension_unit'));
        echo '<p class="product-dimensions">Stock sheet size: ' . $dimensions .  '</p>';
    }

}
// Add function to display shipping dimensions on product page











// Calculate Sheets Required

function calculate_sheets_required($sheet_width, $sheet_length, $part_width, $part_length, $quantity, $edge_margin = 2, $gap = 4) {
    // Calculate max parts per row (width-wise)
    $max_parts_per_row = 1;
    while (true) {
        $total_width = (2 * $edge_margin) + ($max_parts_per_row * $part_width) + (($max_parts_per_row - 1) * $gap);
        if ($total_width > $sheet_width) break;
        $max_parts_per_row++;
    }
    $max_parts_per_row--; // Last valid count

    // Calculate max parts per column (length-wise)
    $max_parts_per_column = 1;
    while (true) {
        $total_length = (2 * $edge_margin) + ($max_parts_per_column * $part_length) + (($max_parts_per_column - 1) * $gap);
        if ($total_length > $sheet_length) break;
        $max_parts_per_column++;
    }
    $max_parts_per_column--; // Last valid count

    // Calculate parts per sheet
    $parts_per_sheet = $max_parts_per_row * $max_parts_per_column;

    if ($parts_per_sheet <= 0) {
        return [
            'sheets_required' => 0,
            'parts_per_sheet' => 0,
            'max_columns' => 0,
            'max_rows' => 0
        ];
    }

    // Calculate required sheets
    $sheets_required = ceil($quantity / $parts_per_sheet);

    return [
        'sheets_required' => $sheets_required,
        'parts_per_sheet' => $parts_per_sheet,
        'max_columns' => $max_parts_per_row,
        'max_rows' => $max_parts_per_column
    ];
}




// Modified Display Custom Inputs and Weight on Product Page
add_action('woocommerce_before_single_product_summary', 'display_custom_inputs_on_product_page', 10);
function display_custom_inputs_on_product_page() {
    global $product;

    $product_id = $product->get_id();

    // WooCommerce returns length/width in cm – convert to mm
    $sheet_length_mm = $product->get_length() * 10; // cm → mm
    $sheet_width_mm = $product->get_width() * 10;   // cm → mm

    // Form values (part size and quantity) are in mm
    $part_length_mm = isset($_POST['custom_length']) ? floatval($_POST['custom_length']) : 0;
    $part_width_mm = isset($_POST['custom_width']) ? floatval($_POST['custom_width']) : 0;
    $quantity = isset($_POST['custom_qty']) ? intval($_POST['custom_qty']) : 0;

    // Get product weight (in kg)
    $product_weight = $product->get_weight();
    $weight_unit = get_option('woocommerce_weight_unit');

    // Only calculate and display if valid inputs are provided
    if ($part_width_mm > 0 && $part_length_mm > 0 && $quantity > 0) {
        // Call calculate_sheets_required function
        $result = calculate_sheets_required(
            $sheet_width_mm,
            $sheet_length_mm,
            $part_width_mm,
            $part_length_mm,
            $quantity
        );

        // Calculate total delivery weight
        $sheets = $result['sheets_required'];
        if (!is_numeric($product_weight) || $product_weight <= 0) {
            $total_del_weight = new WP_Error('invalid_weight', 'Invalid or missing product weight');
        } else {
            //$total_del_weight = $sheets * floatval($product_weight); //old

            $totalSqMm = $part_length_mm * $part_width_mm;
            $totalSqCm = $totalSqMm / 100;
            $total_del_weight = $totalSqCm * floatval($product_weight) * $quantity * 1.03;



            //$total_del_weight = round($total_del_weight, 4);
            error_log("product_weight?: $product_weight");
            error_log("sheets?: $sheets");
            error_log("total_del_weight??: $total_del_weight");
            error_log("totalSqCm??: $totalSqCm");
            error_log("totalSqMm??: $totalSqMm");
            error_log("setWidthRaw??: $sheet_width_mm");
            error_log("setLengthRaw??: $sheet_length_mm");
        }

        // Debug logging (only if WP_DEBUG is enabled)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Debug [Product ID: $product_id, Weight Calculation]:");
            error_log("  sheets: $sheets");
            error_log("  product_weight: $product_weight $weight_unit");
            error_log("  total_del_weight: " . (is_wp_error($total_del_weight) ? $total_del_weight->get_error_message() : "$total_del_weight $weight_unit"));
        }

        // Output styled results
        echo '<div class="custom-product-info" style="margin-bottom: 20px;">';
        echo '<h3>Product Details</h3>';
        echo '<p><strong>Sheet Size (mm):</strong> ' . esc_html($sheet_width_mm) . ' x ' . esc_html($sheet_length_mm) . '</p>';
        echo '<p><strong>Part Size (mm):</strong> ' . esc_html($part_width_mm) . ' x ' . esc_html($part_length_mm) . '</p>';
        echo '<p><strong>Quantity Needed:</strong> ' . esc_html($quantity) . '</p>';
        echo '<p><strong>Sheets Required:</strong> ' . esc_html($result['sheets_required']) . '</p>';
        echo '<p><strong>Parts Per Sheet:</strong> ' . esc_html($result['parts_per_sheet']) . '</p>';
        echo '<p><strong>Max Columns:</strong> ' . esc_html($result['max_columns']) . '</p>';
        echo '<p><strong>Max Rows:</strong> ' . esc_html($result['max_rows']) . '</p>';
        if (!is_wp_error($total_del_weight)) {
            echo '<p><strong>Total Delivery Weight:</strong> ' . esc_html($total_del_weight) . ' ' . esc_html($weight_unit) . '</p>';
        } else {
            // Display error only in debug mode
            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo '<p style="color: red;"><strong>Error:</strong> ' . esc_html($total_del_weight->get_error_message()) . '</p>';
            }
        }
        echo '</div>';
    }
}



