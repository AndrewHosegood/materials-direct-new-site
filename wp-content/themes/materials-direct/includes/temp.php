<?php
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
        'Shipping Total', $total_shipping, 
        [], 
        'custom_shipping_method', 
        '' 
    ); $order->add_shipping($shipping_rate); } 
    
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