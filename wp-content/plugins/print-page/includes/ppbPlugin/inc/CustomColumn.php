<?php

namespace PPB;

class CustomColumn {
    function __construct() {
        add_filter('manage_print_page_posts_columns', [$this, 'ppb_ManageColumns'], 10);
        add_action('manage_print_page_posts_custom_column', [$this, 'ppb_ManageCustomColumns'], 10, 2);  
    }

    function ppb_ManageColumns($defaults){
        unset($defaults['date']);
        $defaults['shortcode'] = 'ShortCode';
        $defaults['date'] = 'Date';
        return $defaults;
    }

    function ppb_ManageCustomColumns($column_name, $post_ID){
        if ($column_name == 'shortcode') {
            echo '<div class="bPlAdminShortcode" id="bPlAdminShortcode-' . esc_attr($post_ID) . '">
                    <input value="[print_page id=' . esc_attr($post_ID) . ']" onclick="copyBPlAdminShortcode(\'' . esc_attr($post_ID) . '\')" readonly>
                    <span class="tooltip">Copy To Clipboard</span>
                  </div>';
        }
    }

}