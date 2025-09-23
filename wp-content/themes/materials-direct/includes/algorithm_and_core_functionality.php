<?php
// Codys Exponential Decay Function 
function exponentialDecay($A, $k, $t) {
    return $A * exp(-$k * $t);
}
// End Codys Exponential Decay Function 

// 1. PRICE CALCULATION FUNCTION
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

    if ($cost_per_cm2 <= 0) {
        error_log("calculate_product_price: Invalid cost_per_cm ($cost_per_cm2) for product ID $product_id");
        return new WP_Error('invalid_acf_field', 'Invalid cost per cm² for this product');
    }
    if ($item_border < 0) {
        error_log("calculate_product_price: Invalid border_around ($item_border) for product ID $product_id");
        return new WP_Error('invalid_acf_field', 'Invalid border size for this product');
    }
    if ($globalPriceAdjust <= 0) {
        error_log("calculate_product_price: Invalid global_adjust_square_rectangle ($globalPriceAdjust)");
        $globalPriceAdjust = 1; // Fallback to avoid zero price
    }

    // Core calculation
    $borderSize = $item_border * 2;
    $setLength = $length / 10;
    $setWidth = $width / 10;
    $maxSetWidth = $setWidth + $borderSize;
    $maxSetLength = $setLength + $borderSize;
    $ppp = $maxSetLength * $maxSetWidth * $cost_per_cm2;
    $totalSqMm = $setWidth * $setLength * 100;


    $A = 0.68;      // Maximum Cost Factor possible
    $k = 0.0018;    // Decay Rate
    $t = $totalSqMm; // mm2 of part
    $costFactorResult = exponentialDecay($A, $k, $t);
    $finalPppOnAva = $ppp + $costFactorResult;

    /* remove this for now as maybe we dont need to apply a backorder discount */
    /* dicount may already be covered with our partial backorder discount */
    /*
    $product = wc_get_product($product_id);
    $stock_quantity = $product->get_stock_quantity();
    if ($stock_quantity <= 0) {
        $discount_rate = max($discount_rate, 0.05); 
    }
    */
    /* remove this for now as maybe we dont need to apply a backorder discount */
    /* dicount may already be covered with our partial backorder discount */

    $discountAmount = $finalPppOnAva * $discount_rate;
    $finalPppOnAva = $finalPppOnAva - $discountAmount;


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

    return sprintf("%.2f", $total_price);
}
// 1. END PRICE CALCULATION FUNCTION


// 2. HTML FORM WITH SPINNER
add_action('woocommerce_before_add_to_cart_button', 'custom_price_input_fields_prefill');
function custom_price_input_fields_prefill() {
    global $product;

    // Get the ACF field value
    $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product->get_id()) : false;
    $product_id = $product->get_id();
    $shipping_address = WC()->session->get('custom_shipping_address', []);

    // Prefill shipping address fields from session
    $street_address = !empty($shipping_address['street_address']) ? esc_attr($shipping_address['street_address']) : '';
    $address_line2 = !empty($shipping_address['address_line2']) ? esc_attr($shipping_address['address_line2']) : '';
    $city = !empty($shipping_address['city']) ? esc_attr($shipping_address['city']) : '';
    $county_state = !empty($shipping_address['county_state']) ? esc_attr($shipping_address['county_state']) : '';
    $zip_postal = !empty($shipping_address['zip_postal']) ? esc_attr($shipping_address['zip_postal']) : '';
    $country = !empty($shipping_address['country']) ? esc_attr($shipping_address['country']) : 'United Kingdom';

    // If is_product_single is true, skip the custom form and rely on default WooCommerce behavior
if ($is_product_single) {
        // Output shipping address form for single products
        echo '<div id="shipping-address-form">
            <h3 class="product-page__subheading">Item(s) shipping address<span class="gfield_required gfield_required_asterisk">*</span></h3>
            <label class="custom-price-calc__label"><input class="product-page__calc-input" type="text" id="input_street_address" name="custom_street_address" placeholder="Street Address" value="' . $street_address . '" required></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input" type="text" id="input_address_line2" name="custom_address_line2" placeholder="Address Line 2" value="' . $address_line2 . '"></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input product-page__calc-input-small" type="text" id="input_city" name="custom_city" placeholder="City" value="' . $city . '" required></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input product-page__calc-input-small" type="text" id="input_county_state" name="custom_county_state" placeholder="County/State" value="' . $county_state . '" required></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input product-page__calc-input-small" type="text" id="input_zip_postal" name="custom_zip_postal" placeholder="ZIP/Postal Code" value="' . $zip_postal . '" required></label>
            <label class="custom-price-calc__label">
                <select id="input_country" class="product-page__calc-input product-page__calc-input-small" name="custom_country" required>
                    <option value="United Kingdom"' . selected($country, 'United Kingdom', false) . '>United Kingdom</option>
                    <option value="France"' . selected($country, 'France', false) . '>France</option>
                    <option value="Germany"' . selected($country, 'Germany', false) . '>Germany</option>
                    <option value="Monaco"' . selected($country, 'Monaco', false) . '>Monaco</option>
                </select>
            </label>
        </div>';
        echo '<input type="hidden" id="custom_price" name="custom_price" value="">';
        
        echo '<div id="custom_price_display"></div>';
        return;
    }


    // Existing form for non-single products
    echo '<div id="custom-price-calc" class="custom-price-calc">
    
        <!-- Price Inputs -->
        <div class="product-page__grey-panel">
        <label class="product-page__input-wrap">Width (MM): <input class="product-page__input" type="number" id="input_width" name="custom_width" min="0.01" step="0.01" required></label>
        <label class="product-page__input-wrap">Length (MM): <input class="product-page__input" type="number" id="input_length" name="custom_length" min="0.01" step="0.01" required></label>
        <label class="product-page__input-wrap">Quantity: <input class="product-page__input" type="number" id="input_qty" name="custom_qty" value="1" min="1" step="1" required></label>
        </div>

        <label id="despatched_within" class="custom-price-calc__label product-page__label">Despatched Within <span class="product-page__label-small-text">Only applies to available stock</span> 
            <select class="custom-price-calc__input product-page__calc-input" id="input_discount_rate" name="custom_discount_rate">
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

        <button type="button" class="product-page__generate-price" id="generate_price">Calculate Price</button>
        <div id="price-spinner-overlay" style="display:none;">
            <div class="spinner-wrapper">
                <img src="' . esc_url(get_theme_file_uri('/images/loading_md.gif')) . '" alt="Loading...">
            </div>
        </div>
        <div id="custom_price_display"></div>
        <input type="hidden" id="custom_price" name="custom_price" value="">
        <input type="hidden" id="sheets_required" name="sheets_required" value="1">
        <input type="hidden" id="is_backorder" name="is_backorder" value="0">
        <input type="hidden" id="able_to_dispatch" name="able_to_dispatch" value="0">
        <input type="hidden" id="parts_backorder" name="parts_backorder" value="0">
        

        <!-- Shipping Address Inputs -->
        <div id="shipping-address-form">
            <h3 class="product-page__subheading">Item(s) shipping address<span class="gfield_required gfield_required_asterisk">*</span></h3>
            <label class="custom-price-calc__label"><input class="product-page__calc-input" type="text" id="input_street_address" name="custom_street_address" placeholder="Street Address" value="' . $street_address . '" required></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input" type="text" id="input_address_line2" name="custom_address_line2" placeholder="Address Line 2" value="' . $address_line2 . '"></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input product-page__calc-input-small" type="text" id="input_city" name="custom_city" placeholder="City" value="' . $city . '" required></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input product-page__calc-input-small" type="text" id="input_county_state" name="custom_county_state" placeholder="County/State" value="' . $county_state . '" required></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input product-page__calc-input-small" type="text" id="input_zip_postal" name="custom_zip_postal" placeholder="ZIP/ Postal Code" value="' . $zip_postal . '" required></label>
            <label class="custom-price-calc__label">
                <select id="input_country" class="product-page__calc-input product-page__calc-input-small" name="custom_country" required>
                    <option value="United Kingdom"' . selected($country, 'United Kingdom', false) . '>United Kingdom</option>
                    <option value="France"' . selected($country, 'France', false) . '>France</option>
                    <option value="Germany"' . selected($country, 'Germany', false) . '>Germany</option>
                    <option value="Monaco"' . selected($country, 'Monaco', false) . '>Monaco</option>
                </select>
            </label>
        </div>
    </div>';
}
// 2. HTML FORM WITH SPINNER



// 3. SECURE PRICE CALCULATION IN PHP
add_action('wp_ajax_calculate_secure_price', 'calculate_secure_price');
add_action('wp_ajax_nopriv_calculate_secure_price', 'calculate_secure_price');

function calculate_secure_price() {
    // Verify nonce for security
    check_ajax_referer('custom_price_nonce', 'nonce');

    $product_id = intval($_POST['product_id']);
    $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product_id) : false;

    // If is_product_single is true, return the default WooCommerce price
    if ($is_product_single) {
        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(['message' => 'Invalid product ID.']);
            return;
        }
        $price = $product->get_price(); // Get default WooCommerce price
        wp_send_json_success([
            'price' => sprintf("%.2f", $price),
            'per_part' => sprintf("%.2f", $price),
            'sheets_required' => 1,
            'stock_quantity' => $product->get_stock_quantity(),
            'is_backorder' => false
        ]);
        return;
    }

    $width = floatval($_POST['width']);
    $length = floatval($_POST['length']);
    $qty = intval($_POST['qty']);
    $discount_rate = floatval($_POST['discount_rate']);

    if (!is_numeric($product_id) || $width <= 0 || $length <= 0 || $qty < 1 || !is_numeric($discount_rate)) {
        error_log("calculate_secure_price: Invalid input values - product_id=$product_id, width=$width, length=$length, qty=$qty, discount_rate=$discount_rate");
        wp_send_json_error(['message' => 'Invalid input values.']);
    }

    // Retrieve the product to get sheet dimensions (in cm, convert to mm)
    $product = wc_get_product($product_id);
    if (!$product) {
        error_log("calculate_secure_price: Invalid product ID $product_id");
        wp_send_json_error(['message' => 'Invalid product ID.']);
        return;
    }
    $sheet_length_mm = $product->get_length() * 10; // Convert cm to mm
    $sheet_width_mm = $product->get_width() * 10;   // Convert cm to mm
    $stock_quantity = $product->get_stock_quantity();

    if ($sheet_length_mm <= 0 || $sheet_width_mm <= 0) {
        error_log("calculate_secure_price: Invalid sheet dimensions - length=$sheet_length_mm, width=$sheet_width_mm");
        wp_send_json_error(['message' => 'Invalid sheet dimensions for this product.']);
        return;
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

    $total_price = calculate_product_price($product_id, $width, $length, $qty, $discount_rate);

    if (is_wp_error($total_price)) {
        error_log("calculate_secure_price: Price calculation error - " . $total_price->get_error_message());
        wp_send_json_error(['message' => $total_price->get_error_message()]);
        return;
    }
    $per_part_price = $total_price / $qty;

    $sheet_result = calculate_sheets_required($sheet_width_mm, $sheet_length_mm, $width, $length, $qty);
    $sheets_required = $sheet_result['sheets_required'];
    $parts_per_sheet = $sheet_result['parts_per_sheet']; 

    if ($sheets_required <= 0 || $parts_per_sheet <= 0) {
        wp_send_json_error(['message' => 'Part dimensions are too large for the sheet size.']);
        return;
    }

    $available_sheets = max(0, $stock_quantity);
    $available_parts = min($qty, $available_sheets * $parts_per_sheet);
    $parts_backorder = max(0, $qty - $available_parts);
    $sheets_backorder = $parts_backorder > 0 ? ceil($parts_backorder / $parts_per_sheet) : 0;

    $is_backorder = $stock_quantity > 0 ? $sheets_required > $stock_quantity : ($qty > 0);

    if ($is_backorder && $parts_backorder > 0) {
        $backorder_discount_amount = 0.05 * ($parts_backorder * $per_part_price);
        $total_price = $total_price - $backorder_discount_amount;
        $per_part_price = $total_price / $qty;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Backorder discount: parts_backorder=$parts_backorder, per_part_price=$per_part_price, discount_amount=$backorder_discount_amount, new_total_price=$total_price");
        }
    }

    // Calculate price per sheet
    $price_per_sheet = $total_price / $sheets_required;

    // Optional: Log for debugging (remove in production)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Dynamic sheets calculation: Sheets required = $sheets_required for width=$width, length=$length, qty=$qty, sheet=$sheet_width_mm x $sheet_length_mm");
        error_log("Stock check: sheets_required=$sheets_required, stock_quantity=$stock_quantity, is_backorder=" . ($is_backorder ? 'true' : 'false'));
        error_log("Response data: price=$total_price, per_part=$per_part_price");
    }

    // SEND DATA TO algorith-core-functionality.js
    wp_send_json_success([
        'price' => sprintf("%.2f", $total_price),
        'per_part' => sprintf("%.2f", $per_part_price),
        'sheets_required' => $sheets_required,
        'stock_quantity' => $stock_quantity,
        'is_backorder' => $is_backorder,
        'sheets_backorder' => $sheets_backorder,
        'parts_backorder' => $parts_backorder,
        'able_to_dispatch' => $available_parts,
        'parts_per_sheet' => $parts_per_sheet,
        'price_per_sheet' => sprintf("%.2f", $price_per_sheet)
    ]);
}
// 3. SECURE PRICE CALCULATION IN PHP


// 3b NEW AJAX HANDLER FOR SAVING SHIPPING ADDRESS FOR SINGLE PRODUCTS
add_action('wp_ajax_save_single_product_shipping', 'save_single_product_shipping');
add_action('wp_ajax_nopriv_save_single_product_shipping', 'save_single_product_shipping');

function save_single_product_shipping() {
    // Verify nonce for security
    check_ajax_referer('custom_price_nonce', 'nonce');

    $product_id = intval($_POST['product_id']);
    $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product_id) : false;

    if (!$is_product_single) {
        wp_send_json_error(['message' => 'This action is only for single products.']);
        return;
    }

    // Validate and save shipping address to session
    if (!empty($_POST['street_address']) && !empty($_POST['city']) && !empty($_POST['county_state']) && !empty($_POST['zip_postal']) && !empty($_POST['country'])) {
        $shipping_address = [
            'street_address' => sanitize_text_field($_POST['street_address']),
            'address_line2' => sanitize_text_field($_POST['address_line2']),
            'city'          => sanitize_text_field($_POST['city']),
            'county_state'  => sanitize_text_field($_POST['county_state']),
            'zip_postal'    => sanitize_text_field($_POST['zip_postal']),
            'country'       => sanitize_text_field($_POST['country']),
        ];
        WC()->session->set('custom_shipping_address', $shipping_address);
        wp_send_json_success(['message' => 'Shipping address saved successfully.']);
    } else {
        wp_send_json_error(['message' => 'Please fill in all required shipping address fields.']);
    }
}
// 3b NEW AJAX HANDLER FOR SAVING SHIPPING ADDRESS FOR SINGLE PRODUCTS


// 4. ENQUEUE JS WITH NONCE
add_action('wp_enqueue_scripts', function() {
    if (is_product()) {
        wp_enqueue_script('custom-price-calc', get_stylesheet_directory_uri() . '/js/algorithm-core-functionality.js', ['jquery'], null, true);
        wp_localize_script('custom-price-calc', 'ajax_params', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'product_id' => get_the_ID(),
            'nonce' => wp_create_nonce('custom_price_nonce'),
        ]);
    }
});
// 4. ENQUEUE JS WITH NONCE






// HELPER FUNCTION TO GROUP SHIPPING DATA BY DISPATCH DATE
function group_shipping_by_date($cart) {
    $shipping_by_date = [];

    foreach ($cart->get_cart() as $cart_item) {
        if (
            isset($cart_item['custom_inputs']['shipments']) &&
            isset($cart_item['custom_inputs']['total_del_weight']) &&
            isset($cart_item['custom_inputs']['shipping_address']['country'])
        ) {
            $shipments = $cart_item['custom_inputs']['shipments'];
            $total_del_weight = floatval($cart_item['custom_inputs']['total_del_weight']);
            $country = $cart_item['custom_inputs']['shipping_address']['country'];

            // Log shipments for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("group_shipping_by_date: Processing cart item");
                error_log("  shipments: " . print_r($shipments, true));
            }

            // Handle array or string shipments
            $date = is_array($shipments) ? ($shipments['in_stock'] ?? $shipments['backorder'] ?? '') : $shipments;

            // Ensure $date is a valid string
            if (!is_string($date) || empty($date)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("group_shipping_by_date: Invalid date for shipments - " . print_r($shipments, true));
                }
                continue;
            }

            if (isset($shipping_by_date[$date])) {
                $shipping_by_date[$date]['quantity'] += 1;
                $shipping_by_date[$date]['total_del_weight'] += $total_del_weight;
            } else {
                $shipping_by_date[$date] = [
                    'quantity' => 1,
                    'total_del_weight' => $total_del_weight,
                    'country' => $country,
                ];
            }

            $shipping_by_date[$date]['final_shipping'] = calculate_shipping_cost($shipping_by_date[$date]['total_del_weight'], $country);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("group_shipping_by_date: Updated shipping_by_date for date {$date}");
                error_log("  shipping_by_date: " . print_r($shipping_by_date[$date], true));
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("group_shipping_by_date: Missing required cart item data");
                error_log("  cart_item: " . print_r($cart_item, true));
            }
        }
    }

    return $shipping_by_date;
}
// HELPER FUNCTION TO GROUP SHIPPING DATA BY DISPATCH DATE





// HELPER FUNCTION TO CALCULATE SHIPPING COST BASED ON TOTAL DELIVERY WEIGHT
function calculate_shipping_cost($total_del_weight, $country) {
    // Define cost tiers for each country or group of countries
    $cost_tiers = [
        'United Kingdom' => [
            [0, 30, 16.50],
            [30, 50, 36.50],
            [50, PHP_INT_MAX, 82.50],
        ],
        'Europe' => [ // Shared tiers for France, Germany, Monaco
            [0, 1, 54.18],
            [1, 1.5, 61.86],
            [1.5, 2, 65.86],
            [2, 2.5, 69.54],
            [2.5, 3, 73.34],
            [3, 3.5, 77.46],
            [3.5, 4, 81.22],
            [4, 4.5, 85.20],
            [4.5, 5, 88.98],
            [5, 10, 92.94],
            [10, 26, 126.48],
            [26, 30, 202.10],
            [30, 50, 221.50],
            [50, 70, 322.42],
            [70, 100, 423.40],
            [100, PHP_INT_MAX, 603.40],
        ],
    ];

    // Map countries to cost tier groups
    $country_groups = [
        'United Kingdom' => 'United Kingdom',
        'France' => 'Europe',
        'Germany' => 'Europe',
        'Monaco' => 'Europe',
    ];

    // Get the appropriate cost tier based on country
    switch ($country) {
        case 'United Kingdom':
        case 'France':
        case 'Germany':
        case 'Monaco':
            $tiers = $cost_tiers[$country_groups[$country]];
            break;
        default:
            return 0;
    }

    // Find the cost based on weight
    foreach ($tiers as $tier) {
        list($min_weight, $max_weight, $cost) = $tier;
        if ($total_del_weight >= $min_weight && $total_del_weight < $max_weight) {
            return $cost;
        }
    }

    return 0;
}
// HELPER FUNCTION TO CALCULATE SHIPPING COST BASED ON TOTAL DELIVERY WEIGHT



// CREATE CART ITEM DATA AND STORE AS SESSION
add_filter('woocommerce_add_cart_item_data', 'add_custom_price_cart_item_data_secure', 10, 2);
function add_custom_price_cart_item_data_secure($cart_item_data, $product_id) {
    $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product_id) : false;

    // Initialize custom_inputs
    $cart_item_data['custom_inputs'] = [];

    // Handle shipping address for both single and non-single products
    if (
        isset($_POST['custom_street_address']) &&
        isset($_POST['custom_city']) &&
        isset($_POST['custom_county_state']) &&
        isset($_POST['custom_zip_postal']) &&
        isset($_POST['custom_country'])
    ) {
        $cart_item_data['custom_inputs']['shipping_address'] = [
            'street_address' => sanitize_text_field($_POST['custom_street_address']),
            'address_line2' => sanitize_text_field($_POST['custom_address_line2'] ?? ''),
            'city' => sanitize_text_field($_POST['custom_city']),
            'county_state' => sanitize_text_field($_POST['custom_county_state']),
            'zip_postal' => sanitize_text_field($_POST['custom_zip_postal']),
            'country' => sanitize_text_field($_POST['custom_country']),
        ];
        // Store shipping address in session
        WC()->session->set('custom_shipping_address', $cart_item_data['custom_inputs']['shipping_address']);
    }


    // Get product object
    $product = wc_get_product($product_id);
    if (!$product) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("add_custom_price_cart_item_data_secure: Invalid product ID {$product_id}");
        }
        return $cart_item_data;
    }

    // Get country from shipping address (default to 'United Kingdom' if not set)
    $country = isset($cart_item_data['custom_inputs']['shipping_address']['country']) ? $cart_item_data['custom_inputs']['shipping_address']['country'] : 'United Kingdom';




if ($is_product_single) {
        // For single products, use the WooCommerce product weight and default price
        $product_weight = $product->get_weight();
        if (!is_numeric($product_weight) || $product_weight <= 0) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("add_custom_price_cart_item_data_secure: Invalid weight for single product ID {$product_id}");
            }
            $total_del_weight = 0; // Fallback to avoid breaking
        } else {
            $total_del_weight = floatval($product_weight); // Use product weight directly
        }

        // Calculate shipping cost
        $final_shipping = calculate_shipping_cost($total_del_weight, $country);

        // Set despatch notes and shipment date (default to 24 hours for single products)
        $despatch_notes = 'Single product to be despatched in 24Hrs (working day)';
        $shipments = date('d/m/Y', strtotime('+1 days'));

        // Store data in cart item
        $cart_item_data['custom_inputs'] = array_merge($cart_item_data['custom_inputs'], [
            'price' => floatval($product->get_price()),
            'qty' => 1,
            'sheets_required' => 1,
            'despatch_notes' => $despatch_notes,
            'shipments' => $shipments,
            'total_del_weight' => $total_del_weight,
            'final_shipping' => $final_shipping,
        ]);

        // Log for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("add_custom_price_cart_item_data_secure (Single Product ID: {$product_id}):");
            error_log("  total_del_weight: {$total_del_weight}");
            error_log("  final_shipping: {$final_shipping}");
            error_log("  shipments: {$shipments}");
        }

        return $cart_item_data;
    }

    // Check if all required POST fields are set
    if (
        !isset($_POST['custom_width']) ||
        !isset($_POST['custom_length']) ||
        !isset($_POST['custom_qty']) ||
        !isset($_POST['custom_price']) ||
        !isset($_POST['custom_discount_rate'])

    ) {
        // Log missing POST data for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('add_custom_price_cart_item_data_secure: Missing required POST fields');
            error_log('POST data: ' . print_r($_POST, true));
        }
        return $cart_item_data; // Return unchanged if required fields are missing
    }

    // Get inputs
    $sheet_length_mm = $product->get_length() * 10; // Convert cm to mm
    $sheet_width_mm = $product->get_width() * 10;   // Convert cm to mm
    $part_width_mm = floatval($_POST['custom_width']);
    $part_length_mm = floatval($_POST['custom_length']);
    $quantity = intval($_POST['custom_qty']);
    $product_weight = $product->get_weight();
    $discount_rate = floatval($_POST['custom_discount_rate']);
    $is_backorder = isset($_POST['is_backorder']) && $_POST['is_backorder'] === '1';
    $able_to_dispatch = isset($_POST['able_to_dispatch']) ? intval($_POST['able_to_dispatch']) : 0;
    $parts_backorder = isset($_POST['parts_backorder']) ? intval($_POST['parts_backorder']) : 0;
    //$custom_price = floatval($_POST['custom_price']); // Price per sheet from form
    $custom_price = isset($_POST['custom_price']) ? sanitize_text_field($_POST['custom_price']) : '0.00';
    $sheets_required_post = isset($_POST['sheets_required']) ? intval($_POST['sheets_required']) : 1;

    // Log POST data for debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("add_custom_price_cart_item_data_secure (Non-Single Product ID: {$product_id}):");
        error_log("  total_price: {$custom_price}");
        error_log("  sheets_required: {$sheets_required_post}");
        error_log("  total_del_weight: {$total_del_weight}");
        error_log("  final_shipping: {$final_shipping}");
        error_log("  shipments: " . print_r($shipments, true));
        error_log("  despatch_notes: {$despatch_notes}");
        error_log("  is_backorder: {$is_backorder}");
        error_log("  able_to_dispatch: {$able_to_dispatch}");
        error_log("  parts_backorder: {$parts_backorder}");
    }

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

    $delivery_time = isset($discount_labels[(string)$discount_rate]) ? $discount_labels[(string)$discount_rate] : 'Unknown';

    // Construct despatch notes
    if ($is_backorder && $parts_backorder > 0) {
        $despatch_notes = sprintf(
            '%d parts to be despatched in 24Hrs (working day) %d parts to be despatched in 35 days (5%% discount)',
            $able_to_dispatch,
            $parts_backorder
        );
        // For shipments, use the selected delivery time for in-stock parts, and 35 days for backordered parts
        $shipments = [
            'in_stock' => $delivery_time === '24Hrs (working day)' ? date('d/m/Y', strtotime('+1 days')) : $delivery_time,
            'backorder' => date('d/m/Y', strtotime('+35 days'))
        ];
    } else {
        $despatch_notes = sprintf(
            '%d parts to be despatched in %s',
            $quantity,
            $delivery_time
        );
        $shipments = $delivery_time === '24Hrs (working day)' ? date('d/m/Y', strtotime('+1 days')) : $delivery_time;
    }
        
    // Calculate sheets required
    $sheet_result = calculate_sheets_required(
        $sheet_width_mm,
        $sheet_length_mm,
        $part_width_mm,
        $part_length_mm,
        $quantity
    );

    // Calculate final shipping cost
    $totalSqMm = $part_length_mm * $part_width_mm;
    $totalSqCm = $totalSqMm / 100;
    $total_del_weight = $totalSqCm * floatval($product_weight) * $quantity * 1.03;
    $final_shipping = calculate_shipping_cost($total_del_weight, $country);

    // Store data in cart item
    $cart_item_data['custom_inputs'] = array_merge($cart_item_data['custom_inputs'], [
        'width' => $part_width_mm,
        'length' => $part_length_mm,
        'qty' => $quantity,
        'total_price' => $custom_price, // Store total_price
        'discount_rate' => $discount_rate,
        'sheets_required' => $sheet_result['sheets_required'],
        'final_shipping' => $final_shipping,
        'shipments' => $shipments,
        'total_del_weight' => $total_del_weight,
        'despatch_notes' => $despatch_notes,
        'is_backorder' => $is_backorder,
        'able_to_dispatch' => $able_to_dispatch,
        'parts_backorder' => $parts_backorder
    ]);


    // Log for debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("add_custom_price_cart_item_data_secure (Non-Single Product ID: {$product_id}):");
        error_log("  total_del_weight: {$total_del_weight}");
        error_log("  final_shipping: {$final_shipping}");
        error_log("  shipments: " . print_r($shipments, true));
        error_log("  despatch_notes: {$despatch_notes}");
        error_log("  is_backorder: {$is_backorder}");
        error_log("  able_to_dispatch: {$able_to_dispatch}");
        error_log("  parts_backorder: {$parts_backorder}");
        error_log("  price_per_sheet: {$custom_price}");
    }

    return $cart_item_data;
}
// CREATE CART ITEM DATA AND STORE AS SESSION



// REGISTER CUSTOM SHIPPING METHOD
add_action('woocommerce_shipping_init', 'init_custom_shipping_method');
function init_custom_shipping_method() {
    if (!class_exists('WC_Custom_Shipping_Method')) {
        class WC_Custom_Shipping_Method extends WC_Shipping_Method {
            public function __construct($instance_id = 0) {
                $this->id = 'custom_shipping_method';
                $this->instance_id = absint($instance_id);
                $this->method_title = __('Custom Shipping', 'woocommerce');
                $this->method_description = __('Custom shipping method for calculated shipping costs', 'woocommerce');
                $this->supports = ['shipping-zones', 'instance-settings'];
                $this->init();
            }

            public function init() {
                $this->enabled = 'yes';
                $this->title = __('Shipping Total', 'woocommerce');
            }

            public function calculate_shipping($package = []) {
                $cart = WC()->cart;
                $shipping_by_date = group_shipping_by_date($cart);

                // Sum the shipping costs
                $total_shipping = 0;
                foreach ($shipping_by_date as $date => $data) {
                    $total_shipping += floatval($data['final_shipping']);
                }

                if ($total_shipping > 0) {
                    $this->add_rate([
                        'id' => $this->id . ':' . $this->instance_id,
                        'label' => $this->title,
                        'cost' => $total_shipping,
                        'taxes'     => '',
                        'calc_tax' => 'per_order',
                        'package' => $package,
                    ]);
                }
            }
        }
    }
}
// END REGISTER CUSTOM SHIPPING METHOD


// ADD CUSTOM SHIPPING METHOD TO WOOCOMMERCE
add_filter('woocommerce_shipping_methods', 'add_custom_shipping_method');
function add_custom_shipping_method($methods) {
    $methods['custom_shipping_method'] = 'WC_Custom_Shipping_Method';
    return $methods;
}
// END ADD CUSTOM SHIPPING METHOD TO WOOCOMMERCE


// DISPLAY SHIPMENTS SECTION ABOVE CART TOTALS
add_action('woocommerce_before_cart_totals', 'display_shipments_section_cart');
function display_shipments_section_cart() {
    $cart = WC()->cart;
    $shipping_by_date = group_shipping_by_date($cart);

    if (!empty($shipping_by_date)) {
        echo '<div class="shipments-section" style="margin-bottom: 20px;">';
        echo '<p class="cart_totals__shipment"><strong>Shipments:</strong></p>';
        foreach ($shipping_by_date as $date => $data) {
            $shipping_cost = floatval($data['final_shipping']);
            if ($shipping_cost > 0) {
                $formatted_cost = wc_price($shipping_cost);
                echo '<p class="cart_totals__shipment-details">Dispatch ' . esc_html($date) . ' (' . $formatted_cost . ')</p>';
            }
        }
        echo '</div>';
    }
}
// DISPLAY SHIPMENTS SECTION ABOVE CART TOTALS


// DISPLAY SHIPPING ADDRESS ON CHECKOUT PAGE
add_action('woocommerce_review_order_before_payment', 'display_shipping_address_on_checkout');
function display_shipping_address_on_checkout() {

    $shipping_address = WC()->session->get('custom_shipping_address');
    if ($shipping_address) {
        echo '<div class="custom-shipping-address">';
        echo '<h3>Shipping Details</h3>';
        echo '<p><strong>Shipping Address: </strong>';
        echo '' . esc_html($shipping_address['street_address']) . ', ';
        if (!empty($shipping_address['address_line2'])) {
            echo '' . esc_html($shipping_address['address_line2']) . ', ';
        }
        echo '' . esc_html($shipping_address['city']) . ', ' . esc_html($shipping_address['county_state']) . ', ' . esc_html($shipping_address['zip_postal']) . ', ';
        echo '' . esc_html($shipping_address['country']) . '';
        echo '</p>';
        echo '</div>';
    }
}
// DISPLAY SHIPPING ADDRESS ON CHECKOUT PAGE


// DISPLAY SHIPMENTS SECTION ABOVE CHECKOUT TOTALS

add_action('woocommerce_review_order_before_payment', 'display_shipments_section_checkout');
function display_shipments_section_checkout() {
    $cart = WC()->cart;
    $shipping_by_date = group_shipping_by_date($cart);

    if (!empty($shipping_by_date)) {
        echo '<div class="shipments-section" style="margin-bottom: 20px;">';
        echo '<p class="cart_totals__shipment"><strong>Shipments:</strong></p>';
        foreach ($shipping_by_date as $date => $data) {
            $shipping_cost = floatval($data['final_shipping']);
            if ($shipping_cost > 0) {
                $formatted_cost = wc_price($shipping_cost);
                echo '<p class="cart_totals__shipment-details">Dispatch ' . esc_html($date) . ' (' . $formatted_cost . ')</p>';
            }
        }
        echo '</div>';
    }
}

// DISPLAY SHIPMENTS SECTION ABOVE CHECKOUT TOTALS





// ENSURE SHIPPING RATE IS ADDED TO ORDER

add_action('woocommerce_checkout_create_order', 'add_custom_shipping_to_order', 20, 2);
function add_custom_shipping_to_order($order, $data) {
    $cart = WC()->cart;
    $shipping_by_date = group_shipping_by_date($cart);

    // Sum the shipping costs
    $total_shipping = 0;
    foreach ($shipping_by_date as $date => $data) {
        $total_shipping += floatval($data['final_shipping']);
    }

    if ($total_shipping > 0) {
        // Create a new shipping item
        $shipping_item = new WC_Order_Item_Shipping();
        $shipping_item->set_method_id('custom_shipping_method');
        $shipping_item->set_method_title('Shipping Total');
        $shipping_item->set_total($total_shipping);

        // Add the shipping item to the order
        $order->add_item($shipping_item);
    }
    // Update shipping address from session
    $shipping_address = WC()->session->get('custom_shipping_address');
    if ($shipping_address && is_array($shipping_address)) {
        $order->set_shipping_first_name(isset($data['billing_first_name']) ? $data['billing_first_name'] : '');
        $order->set_shipping_last_name(isset($data['billing_last_name']) ? $data['billing_last_name'] : '');
        $order->set_shipping_company(isset($data['billing_company']) ? $data['billing_company'] : '');
        $order->set_shipping_address_1($shipping_address['street_address']);
        $order->set_shipping_address_2(!empty($shipping_address['address_line2']) ? $shipping_address['address_line2'] : '');
        $order->set_shipping_city($shipping_address['city']);
        $order->set_shipping_state($shipping_address['county_state']);
        $order->set_shipping_postcode($shipping_address['zip_postal']);
        
        // Map full country names to ISO country codes for WooCommerce
        $country_codes = [
            'United Kingdom' => 'GB',
            'France' => 'FR',
            'Germany' => 'DE',
            'Monaco' => 'MC',
        ];
        $country_code = isset($country_codes[$shipping_address['country']]) ? $country_codes[$shipping_address['country']] : '';
        if ($country_code) {
            $order->set_shipping_country($country_code);
        }
    }

    // Debugging log to confirm shipping item addition
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("add_custom_shipping_to_order: Added shipping item with total {$total_shipping} for order ID {$order->get_id()}");
        if ($shipping_address && is_array($shipping_address)) {
            error_log("add_custom_shipping_to_order: Shipping address set to " . print_r($shipping_address, true));
        }
    }
}

// ENSURE SHIPPING RATE IS ADDED TO ORDER



// SAVE ALL RELEVANT DATA TO ORDER ITEM META
add_action('woocommerce_checkout_create_order_line_item', 'save_sheets_required_to_order_item', 10, 4);
function save_sheets_required_to_order_item($item, $cart_item_key, $values, $order) {
    if (isset($values['custom_inputs']['sheets_required'])) {
        $item->add_meta_data('sheets_required', $values['custom_inputs']['sheets_required'], true);
    }
    if (isset($values['custom_inputs']['shipping_address'])) {
        $item->add_meta_data('custom_shipping_address', $values['custom_inputs']['shipping_address'], true);
    }

    if (isset($values['custom_inputs']['width'])) {
        $item->add_meta_data('width', $values['custom_inputs']['width'], true);
    }
    if (isset($values['custom_inputs']['length'])) {
        $item->add_meta_data('length', $values['custom_inputs']['length'], true);
    }
    if (isset($values['custom_inputs']['qty'])) {
        $item->add_meta_data('qty', $values['custom_inputs']['qty'], true);
    }
    if (isset($values['custom_inputs']['despatch_notes'])) {
        $item->add_meta_data('despatch_notes', $values['custom_inputs']['despatch_notes'], true);
    }
    if (isset($values['custom_inputs']['total_del_weight'])) {
        $item->add_meta_data('total_del_weight', $values['custom_inputs']['total_del_weight'], true);
    }
}
// SAVE ALL RELEVANT DATA TO ORDER ITEM META


// SHOW DATA IN CART AND CHECKOUT
add_filter('woocommerce_get_item_data', 'show_custom_input_details_in_cart', 10, 2);
function show_custom_input_details_in_cart($item_data, $cart_item) {
    $product_id = $cart_item['product_id'];
    $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product_id) : false;

    if ($is_product_single) {
        return $item_data; // Skip custom inputs for single products
    }

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
                'name' => 'Total number of parts',
                'value' => $cart_item['custom_inputs']['qty']
            ];
        }

        // Despatch Notes
        if (isset($cart_item['custom_inputs']['despatch_notes'])) {
            $item_data[] = [
                'name' => 'Despatch Notes',
                'value' => $cart_item['custom_inputs']['despatch_notes']
            ];
        }

        // Total Del Weights
        if (isset($cart_item['custom_inputs']['total_del_weight'])) {
            $item_data[] = [
                'name' => 'Customer Shipping Weight(s)',
                'value' => round((float)$cart_item['custom_inputs']['total_del_weight'], 3) . "kg"
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


    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];
        $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product_id) : false;

        // Skip custom pricing for products where is_product_single is true
        if ($is_product_single) {
            continue; // Use default WooCommerce price
        }

        if (isset($cart_item['custom_inputs'])) {
            $width = $cart_item['custom_inputs']['width'];
            $length = $cart_item['custom_inputs']['length'];
            $qty = $cart_item['custom_inputs']['qty'];
            $discount_rate = isset($cart_item['custom_inputs']['discount_rate']) ? $cart_item['custom_inputs']['discount_rate'] : 0;

            $price_per_sheet = isset($cart_item['custom_inputs']['total_price']) ? $cart_item['custom_inputs']['total_price'] : '0.00';
            $sheets_required = isset($cart_item['custom_inputs']['sheets_required']) ? intval($cart_item['custom_inputs']['sheets_required']) : 1;
            $price_per_sheet_float = floatval($price_per_sheet) / $sheets_required;

            $is_backorder = isset($cart_item['custom_inputs']['is_backorder']) ? $cart_item['custom_inputs']['is_backorder'] : false;
            $parts_backorder = isset($cart_item['custom_inputs']['parts_backorder']) ? intval($cart_item['custom_inputs']['parts_backorder']) : 0;

            // Validate inputs
            if ($price_per_sheet_float <= 0 || $sheets_required <= 0) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("apply_secure_custom_price: Invalid price_per_sheet ($price_per_sheet) or sheets_required ($sheets_required) for cart item key $cart_item_key. Product ID: $product_id");
                }
                continue;
            }

            // Set the price per sheet directly from custom_inputs
            $cart_item['data']->set_price(sprintf("%.2f", $price_per_sheet_float));

            // Log for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("apply_secure_custom_price: Setting price for product ID $product_id");
                error_log("  total_price: $price_per_sheet");
                error_log("  price_per_sheet_float: $price_per_sheet_float");
                error_log("  sheets_required: $sheets_required");
                error_log("  total_price (calculated as price_per_sheet_float * sheets_required): " . ($price_per_sheet_float * $sheets_required));
                error_log("  is_backorder: " . ($is_backorder ? 'true' : 'false'));
                error_log("  parts_backorder: $parts_backorder");
            }

        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("apply_secure_custom_price: Missing custom_inputs for cart item key $cart_item_key. Product ID: $product_id");
            }
        }
    }
}
// SHOW DATA IN CART AND CHECKOUT








// DISPLAY SHIPPING ADDRESS ON THANKYOU PAGE
add_action('woocommerce_thankyou', 'display_shipping_address_on_thankyou', 10, 1);
function display_shipping_address_on_thankyou($order_id) {

    $order = wc_get_order($order_id);
    $shipping_address = false;
    $despatch_notes = false;

    foreach ($order->get_items() as $item_id => $item) {
        $custom_shipping_address = $item->get_meta('custom_shipping_address');
        $item_despatch_notes = $item->get_meta('despatch_notes');
        if ($custom_shipping_address && is_array($custom_shipping_address)) {
            $shipping_address = $custom_shipping_address;
        }
        if ($item_despatch_notes) {
            $despatch_notes = $item_despatch_notes;
        }
        // Break after the first item with data to avoid duplicates
        if ($shipping_address || $despatch_notes) {
            break;
        }
    }
}
// DISPLAY SHIPPING ADDRESS ON THANKYOU PAGE




// SAVE SHIPPING ADDRESS TO ORDER META AND DISPLAY IN EMAILS
/*
add_action('woocommerce_checkout_create_order_line_item', 'save_shipping_address_to_order_item', 10, 4);
function save_shipping_address_to_order_item($item, $cart_item_key, $values, $order) {
    if (isset($values['custom_inputs']['shipping_address'])) {
        $shipping_address = $values['custom_inputs']['shipping_address'];
        $item->add_meta_data('custom_shipping_address', $shipping_address, true);
    }
}
    */
// SAVE SHIPPING ADDRESS TO ORDER META AND DISPLAY IN EMAILS




// DISPLAY SHIPPING ADDRESS/NOTES IN ORDER EMAILS

add_action('woocommerce_email_customer_details', 'add_custom_shipping_address_below_billing', 25, 4);
function add_custom_shipping_address_below_billing($order, $sent_to_admin, $plain_text, $email) {
    // Loop through order items to find the first shipping address
    $shipping_address = null;
    $despatch_notes = null;

    foreach ($order->get_items() as $item_id => $item) {
        $meta_address = $item->get_meta('custom_shipping_address');
        $meta_despatch_notes = $item->get_meta('despatch_notes');
        if (!empty($meta_address['street_address'])) {
            $shipping_address = $meta_address;
        }
        if ($meta_despatch_notes) {
            $despatch_notes = $meta_despatch_notes;
        }
        if ($shipping_address || $despatch_notes) {
            break; // Only show first item with data
        }
    }

    if (!$shipping_address && !$despatch_notes) return;

    if ($plain_text) {
        echo "\nShipping Address:\n";
        echo $shipping_address['street_address'] . "\n";
        if (!empty($shipping_address['address_line2'])) {
            echo $shipping_address['address_line2'] . "\n";
        }
        echo $shipping_address['city'] . ', ' . $shipping_address['county_state'] . ', ' . $shipping_address['zip_postal'] . "\n";
        echo $shipping_address['country'] . "\n";
    } else {

echo '<div class="custom-order-details" style="margin-top:10px;">';
        if ($despatch_notes) {
            echo '<h3>Despatch Notes</h3>';
            echo '<p>' . esc_html($despatch_notes) . '</p>';
        }
        if ($shipping_address) {
            echo '<h3>Shipping Address</h3>';
            echo esc_html($shipping_address['street_address']) . '<br>';
            if (!empty($shipping_address['address_line2'])) {
                echo esc_html($shipping_address['address_line2']) . '<br>';
            }
            echo esc_html($shipping_address['city']) . ', ' . esc_html($shipping_address['county_state']) . ', ' . esc_html($shipping_address['zip_postal']) . '<br>';
            echo esc_html($shipping_address['country']) . '<br>';
        }
        echo '</div>';

    }
}

// DISPLAY SHIPPING ADDRESS/NOTES IN ORDER EMAILS




// DISPLAY GLOBAL SHIPPING ADDRESS BELOW CART TABLES
add_action('woocommerce_before_cart_totals', 'display_global_shipping_address_cart', 1, 2);
function display_global_shipping_address_cart() {
    $shipping_address = WC()->session->get('custom_shipping_address');
    if (!$shipping_address) return;

    echo '<div class="global-shipping-address" style="margin-top:20px;">';
    echo '<h3>Shipping Details</h3>';
    echo '<p><strong>Shipping Address: </strong><p class="global-shipping-address-list">';
    echo esc_html($shipping_address['street_address']) . '<br>';
    if (!empty($shipping_address['address_line2'])) {
        echo esc_html($shipping_address['address_line2']) . '<br>';
    }
    echo esc_html($shipping_address['city']) . '<br>' . esc_html($shipping_address['county_state']) . '<br>' . esc_html($shipping_address['zip_postal']) . '<br>';
    echo esc_html($shipping_address['country']) . '';
    echo '</p></p>';
    echo '</div>';
}
// DISPLAY GLOBAL SHIPPING ADDRESS BELOW CART TABLES




// SAVE THE WP SESSION SHIPPING ADDRESS TO THE ORDERS PAGE
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
// SAVE THE WP SESSION SHIPPING ADDRESS TO THE ORDERS PAGE




// CLEAR SESSION AFTER ORDER IS PLACED

add_action('woocommerce_checkout_order_processed', 'clear_custom_shipping_session', 10, 1);
function clear_custom_shipping_session($order_id) {
    WC()->session->set('custom_shipping_address', null);
}

// CLEAR SESSION AFTER ORDER IS PLACED




// CALCULATE SHEETS REQUIRED

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

// CALCULATE SHEETS REQUIRED



// DISPLAY CUSTOM INPUTS ON PRODUCT PAGE
add_action('woocommerce_before_single_product_summary', 'display_custom_inputs_on_product_page', 10);
function display_custom_inputs_on_product_page() {
    global $product;

    $product_id = $product->get_id();
    $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product_id) : false;

    // Get product weight (in kg)
    $product_weight = $product->get_weight();
    $weight_unit = get_option('woocommerce_weight_unit');

    if ($is_product_single) {
        return;
    }

    $sheet_length_mm = $product->get_length() * 10; // cm → mm
    $sheet_width_mm = $product->get_width() * 10;   // cm → mm
    $part_length_mm = isset($_POST['custom_length']) ? floatval($_POST['custom_length']) : 0;
    $part_width_mm = isset($_POST['custom_width']) ? floatval($_POST['custom_width']) : 0;
    $quantity = isset($_POST['custom_qty']) ? intval($_POST['custom_qty']) : 0;
    $stock_quantity = $product->get_stock_quantity();
    $discount_rate = isset($_POST['custom_discount_rate']) ? floatval($_POST['custom_discount_rate']) : 0;

    $country = isset($_POST['custom_country']) ? sanitize_text_field($_POST['custom_country']) : 'United Kingdom';

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

    $delivery_time = isset($discount_labels[(string)$discount_rate]) ? $discount_labels[(string)$discount_rate] : 'Unknown';

    if($delivery_time === "24Hrs (working day)"){
        $shipments = date('d/m/Y', strtotime(' + 1 days'));
    } elseif($delivery_time === "48Hrs (working days) (1.5% Discount)"){
        $shipments = date('d/m/Y', strtotime(' + 2 days'));
    } elseif($delivery_time === "5 Days (working days) (2% Discount)"){
        $shipments = date('d/m/Y', strtotime(' + 5 days'));
    } elseif($delivery_time === "7 Days (working days) (2.5% Discount)"){
        $shipments = date('d/m/Y', strtotime(' + 7 days'));
    } elseif($delivery_time === "12 Days (working days) (3% Discount)"){
        $shipments = date('d/m/Y', strtotime(' + 12 days'));
    } elseif($delivery_time === "14 Days (working days) (3.5% Discount)"){
        $shipments = date('d/m/Y', strtotime(' + 14 days'));
    } elseif($delivery_time === "30 Days (working days) (4% Discount)"){
        $shipments = date('d/m/Y', strtotime(' + 30 days'));
    } else {
        $shipments = date('d/m/Y', strtotime(' + 35 days'));
    }


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
            $totalSqMm = $part_length_mm * $part_width_mm;
            $totalSqCm = $totalSqMm / 100;
            $total_del_weight = $totalSqCm * floatval($product_weight) * $quantity * 1.03;
            $final_shipping = calculate_shipping_cost($total_del_weight, $country);


            error_log("product_weight?: $product_weight");
            error_log("sheets?: $sheets");
            error_log("total_del_weight??: $total_del_weight");
            error_log("final shipping??: $final_shipping");
            error_log("totalSqCm??: $totalSqCm");
            error_log("totalSqMm??: $totalSqMm");
            error_log("setWidthRaw??: $sheet_width_mm");
            error_log("setLengthRaw??: $sheet_length_mm");
            error_log("discount_rate: $discount_rate");
            error_log("delivery time: $delivery_time");
            error_log("shipments: $shipments");
            //error_log("countries: $countries");
            //error_log("weight: $weight");
            //error_log("cost: $cost");
            //error_log("description: $description");
        }


        // Debug logging (only if WP_DEBUG is enabled)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Debug [Product ID: $product_id, Weight Calculation]:");
            error_log("  sheets: $sheets");
            error_log("  stock_quantity: $stock_quantity");
            error_log("  product_weight: $product_weight $weight_unit");
            error_log("  total_del_weight: " . (is_wp_error($total_del_weight) ? $total_del_weight->get_error_message() : "$total_del_weight $weight_unit"));
            error_log("  final_shipping: $final_shipping");
        }

        // Output styled results
        echo '<div class="custom-product-info" style="margin-bottom: 20px;">';
        echo '<h3>Product Details</h3>';
        echo '<p><strong>Sheet Size (mm):</strong> ' . esc_html($sheet_width_mm) . ' x ' . esc_html($sheet_length_mm) . '</p>';
        echo '<p><strong>Part Size (mm):</strong> ' . esc_html($part_width_mm) . ' x ' . esc_html($part_length_mm) . '</p>';
        echo '<p><strong>Quantity Needed:</strong> ' . esc_html($quantity) . '</p>';
        echo '<p><strong>Delivery Time:</strong> ' . esc_html($delivery_time) . '</p>';
        echo '<p><strong>Shipments:</strong> ' . esc_html($shipments) . '</p>';
        echo '<p><strong>Final Shipping:</strong> ' . esc_html($final_shipping) . '</p>';
        echo '<p><strong>Sheets Required:</strong> ' . esc_html($result['sheets_required']) . '</p>';
        echo '<p><strong>Parts Per Sheet:</strong> ' . esc_html($result['parts_per_sheet']) . '</p>';
        echo '<p><strong>Max Columns:</strong> ' . esc_html($result['max_columns']) . '</p>';
        echo '<p><strong>Max Rows:</strong> ' . esc_html($result['max_rows']) . '</p>';
        echo '<p><strong>Total Delivery Weight:</strong> ' . esc_html($total_del_weight) . ' ' . esc_html($weight_unit) . '</p>';
        echo '<p><strong>Discount Rate:</strong> ' . esc_html($discount_rate) . '</p>';
        echo '<p><strong>Stock Quantity:</strong> ' . esc_html($stock_quantity) . '</p>';
        echo '</div>';
    }
}

// DISPLAY CUSTOM INPUTS ON PRODUCT PAGE


// ADD HIDDEN FIELD FOR IS_PRODUCT_SINGLE
add_action('woocommerce_before_single_product', 'add_is_product_single_hidden_field');
function add_is_product_single_hidden_field() {
    global $product;
    $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product->get_id()) : false;
    echo '<input type="hidden" name="is_product_single" value="' . ($is_product_single ? '1' : '0') . '">';
}
// ADD HIDDEN FIELD FOR IS_PRODUCT_SINGLE



// DYNAMICALLY SET THE CUSTOMERS PRODUCT PAGE SHIPPING ADDRESS FOR TAX CALCULATIONS BASED ON SESSON

// Dynamically set customer's shipping country for tax calculation based on session
add_action('template_redirect', 'set_custom_shipping_country_for_tax_calculation');
function set_custom_shipping_country_for_tax_calculation() {
    if (is_cart() || is_checkout()) {
        $shipping_address = WC()->session->get('custom_shipping_address');
        if ($shipping_address && isset($shipping_address['country'])) {
            $custom_country = $shipping_address['country'];
            // Map full country names to ISO country codes for WooCommerce
            $country_codes = [
                'United Kingdom' => 'GB',
                'France' => 'FR',
                'Germany' => 'DE',
                'Monaco' => 'MC',
            ];
            $country_code = isset($country_codes[$custom_country]) ? $country_codes[$custom_country] : '';
            if ($country_code) {
                WC()->customer->set_shipping_country($country_code);
                // Optionally set other address fields for more precise tax rules if needed
                WC()->customer->set_shipping_address($shipping_address['street_address']);
                WC()->customer->set_shipping_address_2($shipping_address['address_line2']);
                WC()->customer->set_shipping_city($shipping_address['city']);
                WC()->customer->set_shipping_state($shipping_address['county_state']);
                WC()->customer->set_shipping_postcode($shipping_address['zip_postal']);
            }
        }
    }
}
