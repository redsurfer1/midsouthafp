<?php

namespace PPB;

class Enqueue {
    function __construct() {
        add_action( 'admin_enqueue_scripts', [$this, 'ppb_admin_enqueue_script']);
    }
   

    function ppb_admin_enqueue_script($screen){
        global $typenow;
        
        if ('print_page' === $typenow) {

            wp_enqueue_script( 'admin-post-js', PPB_DIR_URL . 'build/admin-post.js', [], PPB_VERSION, true );
            wp_enqueue_style( 'admin-post-css', PPB_DIR_URL . 'build/admin-post.css', [], PPB_VERSION );

            if ($screen === "print_page_page_ppb_demo_page") {
                wp_enqueue_script( 'bpl-admin-dashboard-js', PPB_DIR_URL . 'build/admin-dashboard.js', [ 'react', 'react-dom', 'wp-util' ], PPB_VERSION, true );
                wp_enqueue_style( 'bpl-admin-dashboard-css', PPB_DIR_URL . 'build/admin-dashboard.css', [], PPB_VERSION );
            }

        }
    }
}