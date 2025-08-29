<?php
add_action('woocommerce_thankyou', 'display_order_object_on_thankyou_page', 10, 1);
function display_order_object_on_thankyou_page($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    // Display full order object
    // echo '<h2>Debug: Order Object</h2>';
    // echo '<pre>';
    // print_r($order);
    // echo '</pre>';

    // Display sheets_required for each item
    echo '<h2>Debug: Sheets Required per Item</h2>';
    //echo '<pre>';
    foreach ($order->get_items() as $item_id => $item) {
        $product_name = $item->get_name();
        $sheets_required = $item->get_meta('sheets_required');
		echo '<pre>';
		print_r($item);
		echo '</pre>';

        //echo "Item: $product_name\n";
        //echo "Sheets Required: " . ($sheets_required ? $sheets_required : 'Not set') . "\n\n";
    }
    //echo '</pre>';
}