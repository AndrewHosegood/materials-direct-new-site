<?php
// Codys Exponential Decay Function 
function exponentialDecay($A, $k, $t) {
    return $A * exp(-$k * $t);
}
// End Codys Exponential Decay Function 

// PRICE CALCULATION FUNCTION
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


    // Codys algorith
    $A = 0.68;      // Maximum Cost Factor possible
    $k = 0.0018;    // Decay Rate
    $t = $totalSqMm; // mm2 of part
    $costFactorResult = exponentialDecay($A, $k, $t);
    // End Codys algorith

    $finalPppOnAva = $ppp + $costFactorResult;
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

    return round($total_price, 2);
}
// END PRICE CALCULATION FUNCTION


// HTML FORM WITH SPINNER
add_action('woocommerce_before_add_to_cart_button', 'custom_price_input_fields_prefill');
function custom_price_input_fields_prefill() {
    global $product;

    // Get the ACF field value
    $is_product_single = function_exists('get_field') ? get_field('is_product_single', $product->get_id()) : false;

    // If is_product_single is true, skip the custom form and rely on default WooCommerce behavior
    if ($is_product_single) {
        return; // Exit early to display default WooCommerce form
    }

    $product_id = $product->get_id();
    $shipping_address = WC()->session->get('custom_shipping_address', []);

    $street_address = !empty($shipping_address['street_address']) ? esc_attr($shipping_address['street_address']) : '';
    $address_line2 = !empty($shipping_address['address_line2']) ? esc_attr($shipping_address['address_line2']) : '';
    $city = !empty($shipping_address['city']) ? esc_attr($shipping_address['city']) : '';
    $county_state = !empty($shipping_address['county_state']) ? esc_attr($shipping_address['county_state']) : '';
    $zip_postal = !empty($shipping_address['zip_postal']) ? esc_attr($shipping_address['zip_postal']) : '';
    $country = !empty($shipping_address['country']) ? esc_attr($shipping_address['country']) : 'United Kingdom';

    echo '<div id="custom-price-calc" class="custom-price-calc">
        <!-- Price Inputs -->
        <div class="product-page__grey-panel">
        <label class="product-page__input-wrap">Width (MM): <input class="product-page__input" type="number" id="input_width" name="custom_width" min="0.01" step="0.01" required></label>
        <label class="product-page__input-wrap">Length (MM): <input class="product-page__input" type="number" id="input_length" name="custom_length" min="0.01" step="0.01" required></label>
        <label class="product-page__input-wrap">Quantity: <input class="product-page__input" type="number" id="input_qty" name="custom_qty" value="1" min="1" step="1" required></label>
        </div>
        <label class="custom-price-calc__label product-page__label">Despatched Within <span class="product-page__label-small-text">Only applies to available stock</span> 
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

        <!-- Shipping Address Inputs -->
        <div id="shipping-address-form">
            <h3 class="product-page__subheading">Item(s) shipping address<span class="gfield_required gfield_required_asterisk">*</span></h3>
            <label class="custom-price-calc__label"><input class="product-page__calc-input" type="text" id="input_street_address" name="custom_street_address" placeholder="Street Address" value="' . $street_address . '" required></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input" type="text" id="input_address_line2" name="custom_address_line2" placeholder="Address Line 2" value="' . $address_line2 . '"></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input product-page__calc-input-small" type="text" id="input_city" name="custom_city" placeholder="City" value="' . $city . '" required></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input product-page__calc-input-small" type="text" id="input_county_state" name="custom_county_state" placeholder="County/State" value="' . $county_state . '" required></label>
            <label class="custom-price-calc__label"><input class="product-page__calc-input product-page__calc-input-small" type="text" id="input_zip_postal" name="custom_zip_postal" placeholder="ZIP/ POstal Code" value="' . $zip_postal . '" required></label>
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
// HTML FORM WITH SPINNER



// SECURE PRICE CALCULATION IN PHP
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
            'price' => round($price, 2),
            'per_part' => round($price, 2), // No per-part calculation needed
            'sheets_required' => 1 // Default to 1 sheet for single products
        ]);
        return;
    }

    $width = floatval($_POST['width']);
    $length = floatval($_POST['length']);
    $qty = intval($_POST['qty']);
    $discount_rate = floatval($_POST['discount_rate']);

    if (!is_numeric($product_id) || $width <= 0 || $length <= 0 || $qty < 1 || !is_numeric($discount_rate)) {
        wp_send_json_error(['message' => 'Invalid input values.']);
    }

    // Retrieve the product to get sheet dimensions (in cm, convert to mm)
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(['message' => 'Invalid product ID.']);
        return;
    }
    $sheet_length_mm = $product->get_length() * 10; // Convert cm to mm
    $sheet_width_mm = $product->get_width() * 10;   // Convert cm to mm

    if ($sheet_length_mm <= 0 || $sheet_width_mm <= 0) {
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

    //$price = calculate_product_price($product_id, $width, $length, $qty, $discount_rate);
    $total_price = calculate_product_price($product_id, $width, $length, $qty, $discount_rate);

    if (is_wp_error($total_price)) {
        wp_send_json_error(['message' => $total_price->get_error_message()]);
    }
    $per_part_price = $total_price / $qty;

    // Dynamically calculate sheets required
    $sheet_result = calculate_sheets_required(
        $sheet_width_mm,
        $sheet_length_mm,
        $width,
        $length,
        $qty
    );

    $sheets_required = $sheet_result['sheets_required'];

    // Optional: Log for debugging (remove in production)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Dynamic sheets calculation: Sheets required = $sheets_required for width=$width, length=$length, qty=$qty, sheet=$sheet_width_mm x $sheet_length_mm");
    }

    // SEND DATA TO algorith-core-functionality.js
    wp_send_json_success([
        'price' => round($total_price, 2),
        'per_part' => round($per_part_price, 2),
        'sheets_required' => $sheets_required 
    ]);
}
// SECURE PRICE CALCULATION IN PHP



// ENQUEUE JS WITH NONCE
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
// ENQUEUE JS WITH NONCE






// HELPER FUNCTION TO GROUP SHIPPING DATA BY DISPATCH DATE
function group_shipping_by_date($cart) {
    $shipping_by_date = [];

    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['custom_inputs']['shipments'], $cart_item['custom_inputs']['total_del_weight'], $cart_item['custom_inputs']['shipping_address']['country'])) {
            $date = $cart_item['custom_inputs']['shipments'];
            $total_del_weight = floatval($cart_item['custom_inputs']['total_del_weight']);
            $country = $cart_item['custom_inputs']['shipping_address']['country'];

            // If the date already exists, increment quantity and add to total_del_weight
            if (isset($shipping_by_date[$date])) {
                $shipping_by_date[$date]['quantity'] += 1;
                $shipping_by_date[$date]['total_del_weight'] += $total_del_weight;
            } else {
                // Initialize new date entry with quantity 1
                $shipping_by_date[$date] = [
                    'quantity' => 1,
                    'total_del_weight' => $total_del_weight,
                    'country' => $country,
                ];
            }

            // Calculate final shipping cost based on total weight for the date
            $shipping_by_date[$date]['final_shipping'] = calculate_shipping_cost($shipping_by_date[$date]['total_del_weight'], $country);
        }
    }

    // echo "<pre>";
    // print_r($shipping_by_date);
    // echo "</pre>";

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

    // If is_product_single is true, skip custom inputs
    if ($is_product_single) {
        return $cart_item_data; // Use default WooCommerce behavior
    }

    // Check if all required POST fields are set
    if (
        !isset($_POST['custom_width']) ||
        !isset($_POST['custom_length']) ||
        !isset($_POST['custom_qty']) ||
        !isset($_POST['custom_price']) ||
        !isset($_POST['custom_discount_rate']) ||
        !isset($_POST['custom_street_address']) ||
        !isset($_POST['custom_city']) ||
        !isset($_POST['custom_county_state']) ||
        !isset($_POST['custom_zip_postal']) ||
        !isset($_POST['custom_country'])
    ) {
        // Log missing POST data for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('add_custom_price_cart_item_data_secure: Missing required POST fields');
            error_log('POST data: ' . print_r($_POST, true));
        }
        return $cart_item_data; // Return unchanged if required fields are missing
    }

    // Get product object to retrieve sheet dimensions
    $product = wc_get_product($product_id);
    $sheet_length_mm = $product->get_length() * 10; // Convert cm to mm
    $sheet_width_mm = $product->get_width() * 10;   // Convert cm to mm
    $part_width_mm = floatval($_POST['custom_width']);
    $part_length_mm = floatval($_POST['custom_length']);
    $quantity = intval($_POST['custom_qty']);
    $product_weight = $product->get_weight();
    $discount_rate = isset($_POST['custom_discount_rate']) ? floatval($_POST['custom_discount_rate']) : 0;
    $country = sanitize_text_field($_POST['custom_country']);

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
    $despatch_notes = sprintf(
        '%d parts to be despatched in %s',
        $quantity,
        $delivery_time
    );



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

    // Validate inputs
    if ($part_width_mm <= 0 || $part_length_mm <= 0 || $quantity < 1 || !is_numeric($product_weight)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("add_custom_price_cart_item_data_secure: Invalid inputs - Width: $part_width_mm, Length: $part_length_mm, Qty: $quantity, Weight: $product_weight");
        }
        return $cart_item_data;
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
    $cart_item_data['custom_inputs'] = [
        'width' => floatval($_POST['custom_width']),
        'length' => floatval($_POST['custom_length']),
        'qty' => intval($_POST['custom_qty']),
        'price' => floatval($_POST['custom_price']),
        'discount_rate' => floatval($_POST['custom_discount_rate']),
        'sheets_required' => $sheet_result['sheets_required'],
        'final_shipping' => $final_shipping,
        'shipments' => $shipments,
        'total_del_weight' => $total_del_weight,
        'despatch_notes' => $despatch_notes, // Add despatch notes
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
                        'taxes' => [], // Taxes can be calculated if needed
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
        $shipping_rate = new WC_Shipping_Rate(
            'custom_shipping_rate',
            'Shipping Total',
            $total_shipping,
            [],
            'custom_shipping_method',
            ''
        );
        $order->add_shipping($shipping_rate);
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
        $order->set_shipping_country($shipping_address['country']);
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
    if (isset($values['custom_inputs']['despatch_notes'])) {
        $item->add_meta_data('despatch_notes', $values['custom_inputs']['despatch_notes'], true);
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

        //'total_del_weight' => $total_del_weight,

        // Custom Price
        // if (isset($cart_item['custom_inputs']['price'])) {
        //     $item_data[] = [
        //         'name' => 'Custom Price',
        //         'value' => wc_price($cart_item['custom_inputs']['price'])
        //     ];
        // }

        // Delivery Time / Discount Rate
        /*
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
        */
        // Sheets Required
        /*
        if (isset($cart_item['custom_inputs']['sheets_required'])) {
            $item_data[] = [
                'name' => 'Sheets Required',
                'value' => $cart_item['custom_inputs']['sheets_required']
            ];
        }
        */
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
            $sheets_required = isset($cart_item['custom_inputs']['sheets_required']) ? intval($cart_item['custom_inputs']['sheets_required']) : 1;

            $total_price = calculate_product_price($product_id, $width, $length, $qty, $discount_rate);


            // SEND PRICE TO CART
            if (!is_wp_error($total_price)) {
                $total_price_2 = $total_price / $sheets_required;
                $cart_item['data']->set_price($total_price_2);
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
/*
if ($shipping_address || $despatch_notes) {
        echo '<div class="custom-order-details">';
        echo '<h2 class="woocommerce-column__title">Order Details</h2>';

        // Display Despatch Notes
        if ($despatch_notes) {
            echo '<p><strong>Despatch Notes:</strong> ' . esc_html($despatch_notes) . '</p>';
        }

        // Display Shipping Address
        if ($shipping_address) {
            echo '<h3>Shipping Details</h3>';
            echo '<address>';
            echo esc_html($shipping_address['street_address']) . '<br>';
            if (!empty($shipping_address['address_line2'])) {
                echo esc_html($shipping_address['address_line2']) . '<br>';
            }
            echo esc_html($shipping_address['city']) . '<br>';
            echo esc_html($shipping_address['county_state']) . '<br>';
            echo esc_html($shipping_address['zip_postal']) . '<br>';
            echo esc_html($shipping_address['country']) . '<br>';
            echo '</address>';
        }

        echo '</div>';
    }
        */
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
    echo '<p><strong>Shipping Address: </strong>';
    echo esc_html($shipping_address['street_address']) . ', ';
    if (!empty($shipping_address['address_line2'])) {
        echo esc_html($shipping_address['address_line2']) . ', ';
    }
    echo esc_html($shipping_address['city']) . ', ' . esc_html($shipping_address['county_state']) . ', ' . esc_html($shipping_address['zip_postal']) . ', ';
    echo esc_html($shipping_address['country']) . '';
    echo '</p>';
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

    if ($is_product_single) {
        // Display minimal info for single products
        echo '<div class="custom-product-info" style="margin-bottom: 20px;">';
        echo '<h3>Product Details</h3>';
        echo '<p><strong>Price:</strong> ' . wc_price($product->get_price()) . '</p>';
        echo '</div>';
        return;
    }

    $sheet_length_mm = $product->get_length() * 10; // cm → mm
    $sheet_width_mm = $product->get_width() * 10;   // cm → mm
    $part_length_mm = isset($_POST['custom_length']) ? floatval($_POST['custom_length']) : 0;
    $part_width_mm = isset($_POST['custom_width']) ? floatval($_POST['custom_width']) : 0;
    $quantity = isset($_POST['custom_qty']) ? intval($_POST['custom_qty']) : 0;
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