<?php
/**
 * Theme: MidSouth AFP Child
 * Author: MidSouth AFP
 * Version: 1.0.1
 *
 * @package MidSouthAFP_Child
 */

/**
 * Enqueue parent (Divi) and child styles.
 */
function midsouthafp_child_enqueue_styles() {
	$parent_style = 'divi-style';

	wp_enqueue_style(
		$parent_style,
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( 'Divi' )->get( 'Version' )
	);

	wp_enqueue_style(
		'midsouthafp-child-style',
		get_stylesheet_uri(),
		array( $parent_style ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'midsouthafp_child_enqueue_styles' );

/**
 * Defer selected frontend scripts (not in wp-admin).
 */
function midsouthafp_child_filter_script_loader_tag( $tag, $handle, $src ) {
	if ( is_admin() || empty( $src ) ) {
		return $tag;
	}

	$defer_handles = array(
		// Divi / builder.
		'divi-custom-script',
		'smoothscroll',
		'magnific-popup',
		'et-builder-modules-script-motion',
		'et-builder-modules-script-sticky',
		'et-jquery-visible-viewport',
		'et-core',
		'et-core-common',
		'et-builder-modules-script',
		'fitvids',
		'salvattore',
		'waypoints',
		'jquery-waypoints',
		'jquery-mobile',
		'webfontloader',
		// The Events Calendar (handles may or may not enqueue per view / version).
		'tribe-events-calendar-script',
		'tribe-events-pro',
		'tribe-events-views-v2-bootstrap-datepicker',
		'tribe-events-views-v2-viewport',
		'tribe-events-views-v2-accordion',
		'tribe-events-views-v2-view-selector',
		'tribe-events-views-v2-ical-links',
		'tribe-events-views-v2-navigation-scroll',
		// Feedzy (front / block / Elementor).
		'feedzy-feed-js',
		'feedzy-rss-feeds-lazy',
		'feedzy-gutenberg-block-js',
		'feedzy-elementor',
		// Print Page (if registered on front with this handle).
		'print-page-js',
	);

	$non_defer_handles = array(
		'jquery',
		'jquery-core',
		'jquery-migrate',
		'wp-mediaelement',
	);

	if ( in_array( $handle, $non_defer_handles, true ) ) {
		return $tag;
	}

	if ( in_array( $handle, $defer_handles, true ) ) {
		return sprintf(
			"<script id='%s' src='%s' defer></script>\n",
			esc_attr( $handle . '-js' ),
			esc_url( $src )
		);
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'midsouthafp_child_filter_script_loader_tag', 10, 3 );

/**
 * Non-critical font CSS: print media + onload swap; noscript fallback.
 */
function midsouthafp_child_filter_style_loader_tag( $html, $handle, $href, $media ) {
	if ( is_admin() || empty( $href ) ) {
		return $html;
	}

	$non_critical_handles = array(
		'et-divi-open-sans',
		'et-builder-googlefonts',
		'et-builder-googlefonts-cached',
		'et-fb-fonts',
	);

	if ( ! in_array( $handle, $non_critical_handles, true ) ) {
		return $html;
	}

	$href_attr = esc_url( $href );

	return sprintf(
		"<link rel='stylesheet' id='%s' href='%s' media='print' onload=\"this.media='all'\" />\n<noscript><link rel='stylesheet' href='%s' /></noscript>\n",
		esc_attr( $handle . '-css' ),
		$href_attr,
		$href_attr
	);
}
add_filter( 'style_loader_tag', 'midsouthafp_child_filter_style_loader_tag', 10, 4 );

/**
 * One-time bulk alt text for attachments (admin + query param).
 */
function midsouthafp_child_maybe_run_alt_fix() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) || empty( $_GET['run_alt_fix'] ) ) {
		return;
	}

	$manual_map = array(
		'afp-logo'                 => 'MidSouth AFP – Association for Financial Professionals logo',
		'msafp3'                   => 'MidSouth Association for Financial Professionals banner',
		'networking'               => 'AFP members networking at a professional event',
		'professional-development' => 'Financial professionals in a development session',
		'community'                => 'MidSouth AFP community gathering',
		'linkedin'                 => 'LinkedIn icon',
		'facebook'                 => 'Facebook icon',
		'linkedin-logo'            => 'LinkedIn logo',
	);

	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'post_status'    => 'inherit',
		)
	);

	$log = array();

	foreach ( $attachments as $attachment ) {
		$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
		if ( ! empty( $alt ) ) {
			continue;
		}

		$file = get_attached_file( $attachment->ID );
		if ( ! $file ) {
			continue;
		}

		$filename = strtolower( pathinfo( $file, PATHINFO_FILENAME ) );

		if ( isset( $manual_map[ $filename ] ) ) {
			$generated = $manual_map[ $filename ];
		} else {
			$generated = ucwords( str_replace( array( '-', '_', '.' ), ' ', $filename ) );
		}

		update_post_meta( $attachment->ID, '_wp_attachment_image_alt', $generated );
		$log[] = array(
			'ID'   => $attachment->ID,
			'file' => basename( $file ),
			'alt'  => $generated,
		);
	}

	echo '<pre style="font-family:monospace;font-size:13px;padding:2rem">';
	echo '<strong>Alt text update complete — ' . count( $log ) . ' images updated</strong>' . "\n\n";
	foreach ( $log as $entry ) {
		echo 'ID ' . str_pad( (string) $entry['ID'], 5, ' ', STR_PAD_RIGHT ) . ' | ' .
			str_pad( $entry['file'], 40, ' ', STR_PAD_RIGHT ) . ' | ' . esc_html( $entry['alt'] ) . "\n";
	}
	echo '</pre>';
	exit;
}
add_action( 'admin_init', 'midsouthafp_child_maybe_run_alt_fix' );

/**
 * Organization JSON-LD on the front page.
 */
function midsouthafp_child_organization_schema() {
	if ( ! is_front_page() ) {
		return;
	}

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'Organization',
		'name'            => 'MidSouth Association for Financial Professionals',
		'alternateName'   => 'MidSouth AFP',
		'url'             => 'https://www.midsouthafp.org',
		'logo'            => 'https://midsouthafp.org/wp-content/uploads/2024/08/afp-logo.jpg',
		'foundingDate'    => '1979',
		'description'     => 'A non-profit regional affiliate of the national AFP, promoting treasury and finance management professionals in Memphis, TN and the Mid-South region.',
		'address'         => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'Memphis',
			'addressRegion'   => 'TN',
			'addressCountry'  => 'US',
		),
		'sameAs'          => array(
			'https://www.linkedin.com/company/midsouthafp',
			'https://www.facebook.com/midsouthafp',
		),
	);

	echo '<script type="application/ld+json">' .
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) .
		"</script>\n";
}
add_action( 'wp_head', 'midsouthafp_child_organization_schema' );

/**
 * Event JSON-LD on The Events Calendar views (any slug / list / single when query is event).
 */
function midsouthafp_child_event_schema() {
	if ( ! function_exists( 'tribe_is_event_query' ) || ! tribe_is_event_query() ) {
		return;
	}

	$schema = array(
		'@context'              => 'https://schema.org',
		'@type'                 => 'Event',
		'name'                  => 'MidSouth AFP Quarterly Meeting',
		'startDate'             => '2026-04-23T11:30',
		'endDate'               => '2026-04-23T13:00',
		'eventAttendanceMode'   => 'https://schema.org/OfflineEventAttendanceMode',
		'eventStatus'           => 'https://schema.org/EventScheduled',
		'location'              => array(
			'@type'   => 'Place',
			'name'    => 'Seasons 52',
			'address' => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => '6085 Poplar Ave',
				'addressLocality' => 'Memphis',
				'addressRegion'   => 'TN',
				'postalCode'      => '38119',
				'addressCountry'  => 'US',
			),
		),
		'organizer'             => array(
			'@type' => 'Organization',
			'name'  => 'MidSouth AFP',
			'url'   => 'https://www.midsouthafp.org',
		),
	);

	echo '<script type="application/ld+json">' .
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) .
		"</script>\n";
}
add_action( 'wp_head', 'midsouthafp_child_event_schema' );

require_once get_stylesheet_directory() . '/inc/id-audit.php';
