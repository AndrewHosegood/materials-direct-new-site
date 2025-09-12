<?php
add_filter( 'woocommerce_product_tabs', 'custom_modify_product_tabs' );
function custom_modify_product_tabs( $tabs ) {
    // 1. Remove Additional Information tab
    unset( $tabs['additional_information'] );

    // 2. Add Features tab
    $tabs['features'] = array(
        'title'    => __( 'Features', 'woocommerce' ),
        'priority' => 20,
        'callback' => 'custom_features_tab_content'
    );

    // 3. Add Technical Data tab
    $tabs['technical_data'] = array(
        'title'    => __( 'Technical Data', 'woocommerce' ),
        'priority' => 30,
        'callback' => 'custom_technical_data_tab_content'
    );

    // 4. Add Downloads tab
    $tabs['downloads'] = array(
        'title'    => __( 'Downloads', 'woocommerce' ),
        'priority' => 40,
        'callback' => 'custom_downloads_tab_content'
    );

    // 5. Add Enquiry tab
    $tabs['enquiry'] = array(
        'title'    => __( 'Enquiry', 'woocommerce' ),
        'priority' => 50,
        'callback' => 'custom_enquiry_tab_content'
    );

    return $tabs;
}

// Callback Functions for Each Tab Content
function custom_features_tab_content() {
    echo '<h2>Features</h2>';
    echo '<p>List of features goes here...</p>';
}

function custom_technical_data_tab_content() {
    echo '<h2>Technical Data</h2>';
    echo '<p>Technical data details go here...</p>';
}

function custom_downloads_tab_content() {
    echo '<h2>Downloads</h2>';
    echo '<p>Provide download links or documents here...</p>';
}

function custom_enquiry_tab_content() {
    echo '<h2>Enquiry</h2>';
    echo '<p>You could embed a contact form here.</p>';
    // Example: echo do_shortcode('[contact-form-7 id="123" title="Product Enquiry"]');
}