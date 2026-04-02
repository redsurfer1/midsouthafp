<?php

/**
 * Plugin Name: Print Page - Block
 * Description: Print the entire page with single click
 * Version: 1.2.6
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: print-page
 */
// ABS PATH
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'pp_fs' ) ) {
    pp_fs()->set_basename( false, __FILE__ );
} else {
    define( 'PPB_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.2.6' ) );
    define( 'PPB_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'PPB_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'PPB_HAS_FRMS', file_exists( dirname( __FILE__ ) . '/vendor/freemius/start.php' ) );
    if ( !function_exists( 'pp_fs' ) ) {
        function pp_fs() {
            global $pp_fs;
            if ( !isset( $pp_fs ) ) {
                if ( PPB_HAS_FRMS ) {
                    require_once PPB_DIR_PATH . 'vendor/freemius/start.php';
                } else {
                    require_once PPB_DIR_PATH . 'vendor/freemius-lite/start.php';
                }
                $ssbConfig = [
                    'id'                  => '21137',
                    'slug'                => 'print-page',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_0009bfe45ac358eb763ddf7022975',
                    'is_premium'          => true,
                    'premium_suffix'      => 'Pro',
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'menu'                => array(
                        'slug'       => 'edit.php?post_type=print_page',
                        'first-path' => 'edit.php?post_type=print_page&page=ppb_demo_page#/welcome',
                        'support'    => false,
                    ),
                ];
                $pp_fs = ( PPB_HAS_FRMS ? fs_dynamic_init( $ssbConfig ) : fs_lite_dynamic_init( $ssbConfig ) );
            }
            return $pp_fs;
        }

        pp_fs();
        do_action( 'pp_fs_loaded' );
    }
    if ( PPB_HAS_FRMS ) {
        require_once PPB_DIR_PATH . 'includes/LicenseActivation.php';
    }
    require_once PPB_DIR_PATH . 'includes/utility/functions.php';
    require_once PPB_DIR_PATH . 'includes/ppbPlugin/plugin.php';
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
        $help_link = '<a href="' . admin_url( 'edit.php?post_type=print_page&page=ppb_demo_page' ) . '" style="color:#FF7A00;font-weight:bold;">Help & Demos</a>';
        $links[] = $help_link;
        return $links;
    } );
}