<?php

if (!defined('ABSPATH')) exit;

if( !class_exists( 'PPBPlugin' ) ){
    class PPBPlugin{
        function __construct(){
            $this -> loaded_classes();
        }
 
        function loaded_classes(){
			require_once PPB_DIR_PATH . 'includes/ppbPlugin/inc/Init.php';
			require_once PPB_DIR_PATH . 'includes/ppbPlugin/inc/AdminMenu.php';
			require_once PPB_DIR_PATH . 'includes/ppbPlugin/inc/Enqueue.php';
			require_once PPB_DIR_PATH . 'includes/ppbPlugin/inc/ShortCode.php';
			require_once PPB_DIR_PATH . 'includes/ppbPlugin/inc/CustomColumn.php';
			require_once PPB_DIR_PATH . 'includes/ppbPlugin/inc/RestAPI.php';

			new PPB\Init();
			new PPB\AdminMenu();
			new PPB\Enqueue();
			new PPB\ShortCode();
			new PPB\CustomColumn();
			new PPB\RestAPI();
		}
        
    }
    new PPBPlugin();
}