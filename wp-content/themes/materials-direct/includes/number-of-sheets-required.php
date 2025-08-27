<?php
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



add_action('woocommerce_before_single_product', 'display_custom_inputs_on_product_page');
function display_custom_inputs_on_product_page() {

	global $product;

	// WooCommerce returns length/width in cm – convert to mm
	$sheet_length_mm = $product->get_length() * 10; // cm → mm
	$sheet_width_mm  = $product->get_width() * 10;  // cm → mm

	// Form values (part size and quantity) are in mm
	$part_length_mm = isset($_POST['custom_length']) ? floatval($_POST['custom_length']) : 0;
	$part_width_mm  = isset($_POST['custom_width']) ? floatval($_POST['custom_width']) : 0;
	$quantity       = isset($_POST['custom_qty']) ? intval($_POST['custom_qty']) : 0;

	// Display raw inputs
	echo "<strong>Sheet Size (mm):</strong> {$sheet_width_mm} x {$sheet_length_mm}<br>";
	echo "<strong>Part Size (mm):</strong> {$part_width_mm} x {$part_length_mm}<br>";
	echo "<strong>Quantity Needed:</strong> {$quantity}<br>";

	// Call updated calculator function
	$result = calculate_sheets_required(
		$sheet_width_mm,
		$sheet_length_mm,
		$part_width_mm,
		$part_length_mm,
		$quantity
	);

	// Output result
	echo "<strong>Sheets Required:</strong> {$result['sheets_required']}<br>";
	echo "<strong>Parts Per Sheet:</strong> {$result['parts_per_sheet']}<br>";
	echo "<strong>Max Columns:</strong> {$result['max_columns']}<br>";
	echo "<strong>Max Rows:</strong> {$result['max_rows']}<br>";
}
