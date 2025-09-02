jQuery(document).ready(function($) {
    // Generate Price Button
    $('#generate_price').on('click', function() {
        const width = parseFloat($('#input_width').val());
        const length = parseFloat($('#input_length').val());
        const qty = parseInt($('#input_qty').val());
        const discount_rate = parseFloat($('#input_discount_rate').val());

        const street_address = $('#input_street_address').val().trim();
        const address_line2 = $('#input_address_line2').val().trim();
        const city = $('#input_city').val().trim();
        const county_state = $('#input_county_state').val().trim();
        const zip_postal = $('#input_zip_postal').val().trim();
        const country = $('#input_country').val();

        // Client-side validation for price calculation
        if (isNaN(width) || isNaN(length) || isNaN(qty) || width <= 0 || length <= 0 || qty < 1) {
            $('#custom_price_display').html('Please enter valid positive Width, Length, and Quantity.');
            return;
        }

        if (!street_address || !city || !county_state || !zip_postal || !country) {
            $('#custom_price_display').html('Please fill in all required shipping address fields.');
            return;
        }

        // Validate discount rate
        const valid_discount_rates = [0, 0.015, 0.02, 0.025, 0.03, 0.035, 0.04, 0.05];
        if (!valid_discount_rates.includes(discount_rate)) {
            $('#custom_price_display').html('Please select a valid delivery time.');
            return;
        }

        $('#price-spinner-overlay').fadeIn(200);

        $.ajax({
            url: ajax_params.ajax_url,
            type: 'POST',
            data: {
                action: 'calculate_secure_price',
                product_id: ajax_params.product_id,
                width: width,
                length: length,
                qty: qty,
                discount_rate: discount_rate,
                nonce: ajax_params.nonce,
                street_address: street_address,
                address_line2: address_line2,
                city: city,
                county_state: county_state,
                zip_postal: zip_postal,
                country: country
            },
            success: function(response) {
                $('#price-spinner-overlay').fadeOut(200);
                if (response.success) {
                    const price = response.data.price;
                    $('#custom_price_display').html('Total Price: £' + price.toFixed(2));
                    $('#custom_price').val(price);
                } else {
                    $('#custom_price_display').html('Error: ' + (response.data.message || 'Unable to calculate price.'));
                }
            },
            error: function() {
                $('#price-spinner-overlay').fadeOut(200);
                $('#custom_price_display').html('Error: Server error.');
            }
        });
    });

    // Validate shipping address on Add to Cart form submission
    $('.woocommerce-cart-form, form.cart').on('submit', function(e) {
        const street_address = $('#input_street_address').val().trim();
        const city = $('#input_city').val().trim();
        const county_state = $('#input_county_state').val().trim();
        const zip_postal = $('#input_zip_postal').val().trim();
        const country = $('#input_country').val();
        const price = $('#custom_price').val();

        if (!price) {
            e.preventDefault();
            $('#custom_price_display').html('Please generate a price before adding to cart.');
            return false;
        }

        if (!street_address || !city || !county_state || !zip_postal || !country) {
            e.preventDefault();
            $('#custom_price_display').html('Please fill in all required shipping address fields.');
            return false;
        }
    });
});