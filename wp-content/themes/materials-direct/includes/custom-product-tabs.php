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
    ?>
    <div class="mkd-grid">
        <div class="mkd-grid-row ">
            <div class="mkd-grid-col-6 enq left">
                <?php if( have_rows('specifications') ): ?>
                <h3>Features</h3>
                <?php while ( have_rows('specifications') ) : the_row(); ?>
                <div class="feat-blck">

                    <?php if(get_sub_field('group_heading')): ?>
                    <h3><?php the_sub_field('group_heading'); ?></h3>
                    <?php endif; ?>
                        
                    <?php if( have_rows('list_items') ): ?>
        
                    <ul class="features">
                    <?php while ( have_rows('list_items') ) : the_row(); ?>
                        <li><?php the_sub_field('secification_item'); ?></li>
                    <?php endwhile; ?>
                    </ul>
                    <?php endif; ?>
            </div>
            <?php endwhile; ?>
            <?php endif; ?>
            </div>
            <div class="mkd-grid-col-6 enq right">
                <?php if( have_rows('spec_highlight_list') ): ?>
                <div class="specs">
                <h3>Recommended Uses</h3>
                <ul class="fullFeats">
                    <?php while ( have_rows('spec_highlight_list') ) : the_row(); ?>

                        <li><?php the_sub_field('sh_list_item'); ?></li>

                    <?php endwhile; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
    <?php
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