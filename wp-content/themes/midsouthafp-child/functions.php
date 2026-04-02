<?php
/**
 * Theme: MidSouth AFP Child
 * Author: MidSouth AFP
 * Version: 1.0.3
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
 * Meta description fallback when no major SEO plugin is active.
 */
function midsouthafp_child_fallback_meta_description() {
	if ( defined( 'WPSEO_VERSION' ) ||
		defined( 'RANK_MATH_VERSION' ) ||
		defined( 'AIOSEO_VERSION' ) ) {
		return;
	}

	$description = '';

	if ( is_front_page() ) {
		$description = 'MidSouth AFP is a Memphis-based nonprofit for treasury '
			. 'and finance professionals. Join us for quarterly events, '
			. 'education, and CTP continuing education credits.';
	} elseif ( is_singular() ) {
		$description = wp_strip_all_tags(
			has_excerpt() ? get_the_excerpt() : wp_trim_words( get_the_content(), 30 )
		);
	} elseif ( is_post_type_archive() || is_tax() ) {
		$description = wp_strip_all_tags( get_the_archive_description() );
	}

	if ( ! empty( $description ) ) {
		echo '<meta name="description" content="'
			. esc_attr( $description )
			. "\" />\n";
	}
}
add_action( 'wp_head', 'midsouthafp_child_fallback_meta_description', 1 );

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
 * Event JSON-LD from The Events Calendar (single or list/archive).
 */
function midsouthafp_child_event_schema() {
	if ( ! function_exists( 'tribe_is_event_query' ) ) {
		return;
	}
	if ( ! tribe_is_event_query() ) {
		return;
	}

	$schemas = array();

	if ( function_exists( 'tribe_is_event' ) && tribe_is_event() ) {
		$event_id = get_the_ID();
		$schema   = midsouthafp_child_build_event_schema( $event_id );
		if ( $schema ) {
			$schemas[] = $schema;
		}
	} elseif ( function_exists( 'tribe_get_events' ) ) {
		$events = tribe_get_events(
			array(
				'posts_per_page' => 5,
				'start_date'     => current_time( 'Y-m-d' ),
				'orderby'        => 'event_date',
				'order'          => 'ASC',
			)
		);
		foreach ( $events as $event ) {
			$schema = midsouthafp_child_build_event_schema( $event->ID );
			if ( $schema ) {
				$schemas[] = $schema;
			}
		}
	}

	foreach ( $schemas as $schema ) {
		echo '<script type="application/ld+json">' .
			wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) .
			"</script>\n";
	}
}

/**
 * Build a single Event schema array from a TEC event post ID.
 *
 * @param int $event_id Event post ID.
 * @return array<string,mixed>|null
 */
function midsouthafp_child_build_event_schema( $event_id ) {
	if ( ! function_exists( 'tribe_get_start_date' ) ) {
		return null;
	}

	$event_id = absint( $event_id );
	if ( ! $event_id ) {
		return null;
	}

	$start = tribe_get_start_date( $event_id, false, 'Y-m-d\TH:i' );
	if ( empty( $start ) ) {
		return null;
	}

	$end = function_exists( 'tribe_get_end_date' )
		? tribe_get_end_date( $event_id, false, 'Y-m-d\TH:i' )
		: '';
	if ( empty( $end ) ) {
		$end = $start;
	}

	$title = get_the_title( $event_id );
	$url   = get_permalink( $event_id );

	$post_event = get_post( $event_id );
	$desc       = '';
	if ( $post_event instanceof WP_Post ) {
		$desc = wp_strip_all_tags( get_the_excerpt( $post_event ) );
		if ( '' === $desc && ! empty( $post_event->post_content ) ) {
			$desc = wp_strip_all_tags( wp_trim_words( $post_event->post_content, 30 ) );
		}
	}

	$venue_id = function_exists( 'tribe_get_venue_id' ) ? tribe_get_venue_id( $event_id ) : 0;
	$venue_id = absint( $venue_id );
	$venue_name = ( $venue_id && function_exists( 'tribe_get_venue' ) ) ? tribe_get_venue( $event_id ) : '';
	$address    = ( $venue_id && function_exists( 'tribe_get_address' ) ) ? tribe_get_address( $event_id ) : '';
	$city       = ( $venue_id && function_exists( 'tribe_get_city' ) ) ? tribe_get_city( $event_id ) : '';
	$state      = ( $venue_id && function_exists( 'tribe_get_stateprovince' ) ) ? tribe_get_stateprovince( $event_id ) : '';
	$zip        = ( $venue_id && function_exists( 'tribe_get_zip' ) ) ? tribe_get_zip( $event_id ) : '';

	$schema = array(
		'@context'            => 'https://schema.org',
		'@type'               => 'Event',
		'name'                => $title,
		'url'                 => $url,
		'startDate'           => $start,
		'endDate'             => $end,
		'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
		'eventStatus'         => 'https://schema.org/EventScheduled',
		'organizer'           => array(
			'@type' => 'Organization',
			'name'  => 'MidSouth AFP',
			'url'   => 'https://www.midsouthafp.org',
		),
	);

	if ( ! empty( $desc ) ) {
		$schema['description'] = $desc;
	}

	if ( ! empty( $venue_name ) ) {
		$schema['location'] = array(
			'@type'   => 'Place',
			'name'    => $venue_name,
			'address' => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $address,
				'addressLocality' => $city,
				'addressRegion'   => $state,
				'postalCode'      => $zip,
				'addressCountry'  => 'US',
			),
		);
	}

	return $schema;
}
add_action( 'wp_head', 'midsouthafp_child_event_schema' );

/**
 * Purge Divi static CSS and common caches (shared implementation).
 */
function midsouthafp_child_purge_divi_cache_run() {
	if ( function_exists( 'et_core_clear_cache' ) ) {
		et_core_clear_cache();
	}
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
	}
	if ( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all();
	}
	do_action( 'litespeed_purge_all' );
}

/**
 * Manual purge: /wp-admin/?purge_divi_cache=1 (admin only).
 */
function midsouthafp_child_purge_divi_cache_admin() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) || empty( $_GET['purge_divi_cache'] ) ) {
		return;
	}

	midsouthafp_child_purge_divi_cache_run();

	wp_die(
		'<p style="font-family:sans-serif;padding:2rem">' .
		'Cache purge completed (Divi static CSS + compatible object/page caches).<br><br>' .
		'<a href="' . esc_url( home_url() ) . '">&larr; Return to site</a></p>',
		'Cache Purged',
		array( 'response' => 200 )
	);
}
add_action( 'admin_init', 'midsouthafp_child_purge_divi_cache_admin' );

/**
 * After switching to this child theme, clear caches once (admin activation).
 */
function midsouthafp_child_purge_divi_cache_after_switch() {
	if ( get_option( 'stylesheet' ) !== 'midsouthafp-child' ) {
		return;
	}
	if ( ! is_admin() || ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	midsouthafp_child_purge_divi_cache_run();
}
add_action( 'after_switch_theme', 'midsouthafp_child_purge_divi_cache_after_switch' );

require_once get_stylesheet_directory() . '/inc/id-audit.php';
require_once get_stylesheet_directory() . '/inc/homepage-hero.php';
