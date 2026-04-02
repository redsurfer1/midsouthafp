<?php
/**
 * Emergency rollback to Divi parent theme.
 * URL: /wp-admin/?msafp_rollback=1&msafp_nonce=NONCE
 *
 * Generate the nonce URL by visiting:
 * /wp-admin/?generate_rollback_url=1 (admin only)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate a one-time rollback URL and display it to admin.
 * Visit /wp-admin/?generate_rollback_url=1 to get the URL.
 */
function midsouthafp_child_generate_rollback_url() {
	if ( ! is_admin() || ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	if ( empty( $_GET['generate_rollback_url'] ) ) {
		return;
	}

	$nonce = wp_create_nonce( 'msafp_rollback_nonce' );
	$url   = admin_url( '?msafp_rollback=1&msafp_nonce=' . $nonce );

	wp_die(
		'<div style="font-family:sans-serif;padding:2rem;max-width:600px">' .
		'<h2>Emergency Rollback URL</h2>' .
		'<p>Bookmark this URL. If the site breaks after activation, ' .
		'visit this link to instantly revert to Divi:</p>' .
		'<p><a href="' . esc_url( $url ) . '" style="word-break:break-all">' .
		esc_html( $url ) . '</a></p>' .
		'<p><strong>This nonce expires in 12 hours.</strong> ' .
		'Regenerate at: <code>' . esc_url( admin_url( '?generate_rollback_url=1' ) ) .
		'</code></p>' .
		'</div>',
		'Rollback URL Ready',
		array( 'response' => 200 )
	);
}
add_action( 'admin_init', 'midsouthafp_child_generate_rollback_url' );

/**
 * Execute rollback to Divi parent theme.
 */
function midsouthafp_child_execute_rollback() {
	if ( ! is_admin() || ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	if ( empty( $_GET['msafp_rollback'] ) ) {
		return;
	}

	if ( ! isset( $_GET['msafp_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['msafp_nonce'] ) ), 'msafp_rollback_nonce' ) ) {
		wp_die(
			'Invalid or expired rollback link. Regenerate at: ' .
			esc_url( admin_url( '?generate_rollback_url=1' ) )
		);
	}

	switch_theme( 'Divi' );

	if ( function_exists( 'et_core_clear_cache' ) ) {
		et_core_clear_cache();
	}
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}

	wp_die(
		'<div style="font-family:sans-serif;padding:2rem">' .
		'<h2 style="color:green">Rollback complete</h2>' .
		'<p>Active theme restored to <strong>Divi</strong>.</p>' .
		'<p><a href="' . esc_url( home_url() ) . '">&larr; View site</a> | ' .
		'<a href="' . esc_url( admin_url( 'themes.php' ) ) . '">Themes dashboard</a></p>' .
		'</div>',
		'Rollback Complete',
		array( 'response' => 200 )
	);
}
add_action( 'admin_init', 'midsouthafp_child_execute_rollback' );
