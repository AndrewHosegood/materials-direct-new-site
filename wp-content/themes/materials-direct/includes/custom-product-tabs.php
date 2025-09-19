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
    <div class="woocommerce-tabs__mkd-grid">
        <div class="woocommerce-tabs__mkd-grid-row">
            <div class="woocommerce-tabs__mkd-grid-col-6">
                <?php if( have_rows('specifications') ): ?>
                <h3 class="woocommerce-tabs__mkd-grid-heading">Features</h3>
                <?php while ( have_rows('specifications') ) : the_row(); ?>
                <div class="woocommerce-tabs__feat-blck">

                    <?php if(get_sub_field('group_heading')): ?>
                    <h3 class="woocommerce-tabs__mkd-grid-subheading"><?php the_sub_field('group_heading'); ?></h3>
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
            <div class="woocommerce-tabs__mkd-grid-col-6">
                <?php if( have_rows('spec_highlight_list') ): ?>
                <div class="woocommerce-tabs__specs">
                <h3 class="woocommerce-tabs__mkd-grid-heading">Recommended Uses</h3>
                <ul class="woocommerce-tabs__fullFeats">
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


        // check if the repeater field has rows of data
        if( have_rows('download_items') ):

            // loop through the rows of data
            while ( have_rows('download_items') ) : the_row(); 
            
            $image = get_sub_field('download_image');
            ?>
            



        <div class="col-md-3 download-blk">
            <div class="inner">
                <div class="d-img">
                    <?php if( !empty($image) ): ?>
                        <a href="<?php the_sub_field('download_file'); ?>" target="_blank"><div class="img-inner" style="background-image: url(<?php echo $image['url']; ?>);"></div></a>
                    <?php endif; ?>
                </div>
                <div class="d-title"><a href="<?php the_sub_field('download_file'); ?>" target="_blank"><h4><?php the_sub_field('download_title'); ?></h4></a></div>
                <div class="d-desc"><?php the_sub_field('download_description'); ?></div>
            </div>
        </div>


        <?php
            endwhile;

        else :

            // no rows found

        endif;


}

function custom_enquiry_tab_content() {
    ?>
        <div class="woocommerce-tabs__mkd-grid">
        <div class="woocommerce-tabs__mkd-grid-row">
            <div class="woocommerce-tabs__mkd-grid-col-6 enq left">
                <div class="woocommerce-tabs__form">    
                    <?php
                        if( get_field('contact_shortcode_enquire_tab', 'option') ):
                        $cshort = get_field('contact_shortcode_enquire_tab', 'option');
                        echo do_shortcode($cshort);
                        endif;
                    ?>
                </div>
            </div>
            <div class="woocommerce-tabs__mkd-grid-col-6 enq right">
                <div class="woocommerce-tabs__contact-address">
                <?php
                if( get_field('contact_information', 'option') ):
                    the_field('contact_information', 'option');
                endif;
                ?>
                </div>
            </div>
        </div>
        </div>
    <?php            
}