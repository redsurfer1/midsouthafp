<?php

namespace PPB;

class Init {
    function __construct() {
        add_action( 'init', [ $this, 'onInit' ] );    
    }

    function onInit(){

        register_block_type( PPB_DIR_PATH  . '/build' );

        register_post_type('print_page', [
            'label' => 'Print Page Button',
            'labels' => [
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Print Page Button',
                'edit_item' => 'Edit Print Page Button',
                'not_found' => 'There was no Print Page Button please add one'
            ],
            'show_in_rest' => true,
            'public' => true,
            'menu_icon' => 'dashicons-printer',
            'publicly_queryable' => false,
            'item_published' => 'Print Page Button Published',
            'item_updated' => 'Print Page Button Updated',
            'template' => [['ppb/print-page']],
            'template_lock' => 'all',
        ]);
    }

}