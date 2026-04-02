<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'ppbIsPremium' ) ) {
	function ppbIsPremium() {
		return PPB_HAS_FRMS ? pp_fs()->can_use_premium_code() : false;
	}
}


if ( ! function_exists( 'ppb_restrict_free_user_access' ) ) {
	add_action( 'load-plugin-editor.php', function() {
		if ( ! ppbIsPremium() && isset( $_GET['file'] ) ) {
			$file = sanitize_text_field( wp_unslash( $_GET['file'] ) );

			$restricted_files = [
				'print-page/includes/utility/functions.php',
				'print-page/includes/ppbPlugin/plugin.php'
			];

			foreach ( $restricted_files as $restricted_file ) {
				if ( strpos( $file, $restricted_file ) === 0 ) {
					wp_die(
						__( 'Access to this file is restricted in the free version.', 'print-page' ),
						__( 'Permission Denied', 'print-page' ),
						array( 'response' => 403 )
					);
				}
			}
		}
	});
}


