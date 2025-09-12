<?php
add_action( 'woocommerce_after_add_to_cart_quantity', 'add_discount_charts_to_product_page' );
function add_discount_charts_to_product_page() {
echo '<table class="product-page__discount-table">
<thead>
<tr><th class="product-page__discount-table-heading">Order Total</th>
<th class="product-page__discount-table-heading">Discount</th>
</tr></thead>
<tbody>
<tr>
<td class="product-page__discount-table-content">£1 - £500</td>
<td class="product-page__discount-table-content"><b>N/A</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£501 - £1000</td>
<td class="product-page__discount-table-content"><b>1% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£1001 - £2500</td>
<td class="product-page__discount-table-content"><b>2% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£2501 - £5000</td>
<td class="product-page__discount-table-content"><b>3% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£5001 - £10000</td>
<td class="product-page__discount-table-content"><b>4% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£10001 - £25000</td>
<td class="product-page__discount-table-content"><b>5% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£25001 - £50000</td>
<td class="product-page__discount-table-content"><b>7.5% discount</b></td>
</tr>
<tr>
<td class="product-page__discount-table-content">£50001+</td>
<td class="product-page__discount-table-content"><b>10% discount</b></td>
</tr>
</tbody>
</table>';
}