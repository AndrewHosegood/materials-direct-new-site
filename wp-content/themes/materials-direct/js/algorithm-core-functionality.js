jQuery(document).ready(function($) {
    // Check if the product is single (via data attribute or AJAX)
    const isProductSingle = $('input[name="is_product_single"]').val() === '1';

    // Common function to validate shipping address
    function validateShippingAddress() {
        const street_address = $('#input_street_address').val().trim();
        const city = $('#input_city').val().trim();
        const county_state = $('#input_county_state').val().trim();
        const zip_postal = $('#input_zip_postal').val().trim();
        const country = $('#input_country').val();
        
        if (!street_address || !city || !county_state || !zip_postal || !country) {
            $('#custom_price_display').html('Please fill in all required shipping address fields.');
            return false;
        }
        return {
            street_address,
            address_line2: $('#input_address_line2').val().trim(),
            city,
            county_state,
            zip_postal,
            country
        };
    }
    
    // Generate Price Button (only for non-single products)
    if (!isProductSingle) {
        $('#generate_price').on('click', function() {
            const width = parseFloat($('#input_width').val());
            const length = parseFloat($('#input_length').val());
            const qty = parseInt($('#input_qty').val());
            const discount_rate = parseFloat($('#input_discount_rate').val());
            const shipping_address = validateShippingAddress();

            if (!shipping_address) return;

            // Client-side validation for price calculation
            if (isNaN(width) || isNaN(length) || isNaN(qty) || width <= 0 || length <= 0 || qty < 1) {
                $('#custom_price_display').html('Please enter valid positive Width, Length, and Quantity.');
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
                    ...shipping_address
                },
                success: function(response) {
                    $('#price-spinner-overlay').fadeOut(200);
                    if (response.success) {
                        const price = parseFloat(response.data.price);
                        const adjustedPrice = parseFloat(response.data.per_part);
                        const sheetsRequired = response.data.sheets_required || 1;
                        const isBackorder = response.data.is_backorder || false;
                        const sheetsBackorder = response.data.sheets_backorder || 0;
                        const partsBackorder = response.data.parts_backorder || 0;
                        const ableToDispatch = response.data.able_to_dispatch || 0;
                        const pricePerSheet = response.data.price_per_sheet; // Use server-calculated price per sheet

                        // Calculate price per sheet for the hidden field
                        //const cart_price = price / sheetsRequired;

                        // Build price display HTML
                        let priceHtml = '<div class="product-page__display-price-outer"><div><h4 class="product-page__display-price-heading">Here is your instant quote</h4></div><div class="product-page__display-price-inner"><div class="product-page__display-price">Cost per part: <span class="product-page__display-price-text">£' + adjustedPrice.toFixed(2) + '</span></div><div class="product-page__display-price">Total part costs: <span class="product-page__display-price-text">£' + price.toFixed(2) + '</span></div></div></div>';

                        // Add backorder message if applicable
                        if (isBackorder) {
                            priceHtml += '<div id="partial-backorder-message" class="product-page__backorder-message"><p class="product-page__backorder-message-text"><strong>Notice:</strong> This order exceeds current stock. It requires an additional ' + sheetsBackorder + ' sheet(s) and ' + partsBackorder + ' part(s) to be backordered. We can dispatch ' + ableToDispatch + ' part(s) within 24 hours (working day). Please allow 35 days to complete the order. A 5% discount will apply to these parts.</p></div>';
                        }



                        //const sheetsRequired = 2;
                        // DISPLAY PRICE AND PART COST ON PRODUCT PAGE AFTER CLICKING 'CALCULATE PRICE'
                        $('#custom_price_display').html(priceHtml);
                        // ADD PRICE TO HIDDEN FIELD THAT IS THEN PASSED TO CART
                        $('#custom_price').val(price); // Store total_price
                        $('#sheets_required').val(sheetsRequired); // Store sheets_required
                        $('input[name="quantity"]').val(sheetsRequired); 
                        $('#is_backorder').val(isBackorder ? '1' : '0');
                        $('#able_to_dispatch').val(ableToDispatch);
                        $('#parts_backorder').val(partsBackorder);

                        // Log for debugging
                        console.log('Backorder data set:', {
                            is_backorder: isBackorder ? '1' : '0',
                            able_to_dispatch: ableToDispatch,
                            parts_backorder: partsBackorder,
                            total_price: price.toFixed(2),
                            sheets_required: sheetsRequired
                        });

                    } else {
                        console.log('Error message:', response.data ? response.data.message : 'No error message');
                        $('#custom_price_display').html('Error: ' + (response.data && response.data.message ? response.data.message : 'Unable to calculate price.'));
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX error:', status, error, xhr.responseText);
                    $('#price-spinner-overlay').fadeOut(200);
                    $('#custom_price_display').html('Error: Server error.');
                }
            });
        });

        // Validate shipping address on Add to Cart form submission
        $('form.cart').on('submit', function(e) {
            const price = $('#custom_price').val(); 
            if (!price) {
                e.preventDefault();
                $('#custom_price_display').html('Please generate a price before adding to cart.');
                return false;
            }

            const shipping_address = validateShippingAddress();
            if (!shipping_address) {
                e.preventDefault();
                return false;
            }

            const formData = $(this).serialize();
            console.log('Form submission data:', formData);

            // Ensure hidden fields are included
            console.log('Hidden fields:', {
                custom_price: $('#custom_price').val(),
                is_backorder: $('#is_backorder').val(),
                able_to_dispatch: $('#able_to_dispatch').val(),
                parts_backorder: $('#parts_backorder').val()
            });

        });
    } else {
        // For single products, display the default price immediately
        $.ajax({
            url: ajax_params.ajax_url,
            type: 'POST',
            data: {
                action: 'calculate_secure_price',
                product_id: ajax_params.product_id,
                nonce: ajax_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    const price = response.data.price;
                    $('#custom_price_display').html('<div class="product-page__display-price-outer"><div><h4 class="product-page__display-price-heading">Product Price</h4></div><div class="product-page__display-price-inner"><div class="product-page__display-price">Price: <span class="product-page__display-price-text">£' + price.toFixed(2) + '</span></div></div></div>');
                    $('#custom_price').val(price);
                    $('input[name="quantity"]').val(1); // Default quantity
                } else {
                    $('#custom_price_display').html('Error: ' + (response.data.message || 'Unable to fetch price.'));
                }
            },
            error: function() {
                $('#custom_price_display').html('Error: Server error.');
            }
        });

        // Validate and save shipping address on Add to Cart for single products
        $('form.cart').on('submit', function(e) {
            const price = $('#custom_price').val();
            if (!price) {
                e.preventDefault();
                $('#custom_price_display').html('Please generate a price before adding to cart.');
                return false;
            }

            const shipping_address = validateShippingAddress();
            if (!shipping_address) {
                e.preventDefault();
                return false;
            }

            // Send shipping address to server via AJAX before adding to cart
            $.ajax({
                url: ajax_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_single_product_shipping',
                    product_id: ajax_params.product_id,
                    nonce: ajax_params.nonce,
                    ...shipping_address
                },
                success: function(response) {
                    if (!response.success) {
                        e.preventDefault();
                        $('#custom_price_display').html('Error: ' + (response.data.message || 'Unable to save shipping address.'));
                    }
                    // Form submission will proceed if AJAX is successful
                },
                error: function() {
                    e.preventDefault();
                    $('#custom_price_display').html('Error: Server error while saving shipping address.');
                }
            });
        });

    }
});