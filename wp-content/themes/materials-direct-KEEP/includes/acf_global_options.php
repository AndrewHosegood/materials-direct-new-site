<?php
add_action('acf/init', 'my_acf_add_options_page');
function my_acf_add_options_page() {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page(array(
            'page_title'    => 'Site Settings',
            'menu_title'    => 'Site Settings',
            'menu_slug'     => 'site-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    }
}