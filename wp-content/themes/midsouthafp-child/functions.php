<?php
/**
 * Theme: MidSouth AFP Child
 * Author: MidSouth AFP
 * Version: 1.0.6
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
		'@id'             => 'https://www.midsouthafp.org/#organization',
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
 * Remove Yoast graph pieces that duplicate our custom JSON-LD.
 *
 * - Drops Yoast's Organization (we output a richer Organization on the homepage).
 * - On TEC event views, drops Yoast Event pieces if Yoast SEO for Events (or similar) is active.
 */
function midsouthafp_child_wpseo_schema_graph_pieces( $pieces, $context ) {
	if ( ! is_array( $pieces ) ) {
		return $pieces;
	}

	foreach ( $pieces as $key => $piece ) {
		if ( ! is_object( $piece ) ) {
			continue;
		}

		$class = get_class( $piece );

		// Yoast's Organization generator class name ends with \Organization.
		if ( preg_match( '/\\\\Organization$/', $class ) || 'Organization' === $class ) {
			unset( $pieces[ $key ] );
			continue;
		}

		if ( function_exists( 'tribe_is_event_query' ) && tribe_is_event_query() ) {
			if ( preg_match( '/\\\\Event$/', $class ) || 'Event' === $class ) {
				unset( $pieces[ $key ] );
			}
		}
	}

	return array_values( $pieces );
}
add_filter( 'wpseo_schema_graph_pieces', 'midsouthafp_child_wpseo_schema_graph_pieces', 10, 2 );

/**
 * Ensure Yoast XML sitemaps are enabled (idempotent; runs when Yoast is active).
 */
function midsouthafp_child_ensure_yoast_xml_sitemap_enabled() {
	if ( ! defined( 'WPSEO_VERSION' ) ) {
		return;
	}
	$options = get_option( 'wpseo', array() );
	if ( empty( $options['enable_xml_sitemap'] ) ) {
		$options['enable_xml_sitemap'] = true;
		update_option( 'wpseo', $options );
	}
}
add_action( 'init', 'midsouthafp_child_ensure_yoast_xml_sitemap_enabled', 20 );

/**
 * Include The Events Calendar events in Yoast XML sitemaps when not excluded.
 *
 * @param bool   $excluded  Whether this post type is excluded.
 * @param string $post_type Post type name.
 * @return bool
 */
function midsouthafp_child_wpseo_sitemap_include_tribe_events( $excluded, $post_type ) {
	if ( 'tribe_events' === $post_type ) {
		return false;
	}
	return $excluded;
}
add_filter( 'wpseo_sitemap_exclude_post_type', 'midsouthafp_child_wpseo_sitemap_include_tribe_events', 10, 2 );

/**
 * Ensure virtual robots.txt references the Yoast sitemap index.
 *
 * @param string $output Robots.txt content.
 * @param bool   $public Whether the site is public.
 * @return string
 */
function midsouthafp_child_robots_txt_sitemap( $output, $public ) {
	$sitemap_url = home_url( '/sitemap_index.xml' );
	if ( false === strpos( $output, $sitemap_url ) ) {
		$output .= "\nSitemap: " . $sitemap_url . "\n";
	}
	return $output;
}
add_filter( 'robots_txt', 'midsouthafp_child_robots_txt_sitemap', 10, 2 );

/**
 * One-time Yoast social settings configurator.
 * Visit: /wp-admin/?msafp_yoast_social=1 (admin only).
 * Optional: &og_image_id=ATTACHMENT_ID (after ?generate_og_image=1).
 *
 * OG image: generated via ?generate_og_image=1. See inc/generate-og-image.php
 */
function midsouthafp_child_configure_yoast_social() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( empty( $_GET['msafp_yoast_social'] ) ) {
		return;
	}
	if ( ! defined( 'WPSEO_VERSION' ) ) {
		wp_die( esc_html__( 'Yoast SEO is not active.', 'midsouthafp-child' ) );
	}

	$current = get_option( 'wpseo_social', array() );

	$og_image_id = ! empty( $_GET['og_image_id'] )
		? absint( $_GET['og_image_id'] )
		: '';
	$og_image_url = $og_image_id
		? wp_get_attachment_url( $og_image_id )
		: 'https://midsouthafp.org/wp-content/uploads/og-midsouthafp-1200x630.png';

	if ( $og_image_id && ! $og_image_url ) {
		wp_die( esc_html__( 'Invalid og_image_id: attachment not found.', 'midsouthafp-child' ) );
	}

	$updates = array(
		'opengraph'           => true,
		'og_default_image'    => $og_image_url,
		'og_default_image_id' => (string) $og_image_id,
		'og_frontpage_title'  => 'MidSouth AFP – Empowering Financial Professionals',
		'og_frontpage_desc'   => 'MidSouth AFP is a Memphis-based nonprofit for treasury '
			. 'and finance professionals. Quarterly events, CTP credits, '
			. 'and peer networking since 1979.',
		'twitter'             => true,
		'twitter_card_type'   => 'summary_large_image',
		'twitter_site'        => '',
		'facebook_site'       => 'https://www.facebook.com/midsouthafp',
		'pinterest'           => false,
		'youtube'             => '',
	);

	$merged = array_merge( $current, $updates );
	update_option( 'wpseo_social', $merged );

	$front_page_id = (int) get_option( 'page_on_front' );
	if ( $front_page_id > 0 ) {
		update_post_meta(
			$front_page_id,
			'_yoast_wpseo_metadesc',
			'MidSouth AFP is a Memphis-based nonprofit for treasury '
			. 'and finance professionals. Join us for quarterly events, '
			. 'education, and CTP continuing education credits.'
		);
		update_post_meta(
			$front_page_id,
			'_yoast_wpseo_title',
			'MidSouth AFP – Empowering Financial Professionals in Memphis, TN'
		);
	}

	$page_metas = array(
		'events'             => array(
			'title' => 'Events – MidSouth AFP | Memphis Finance Professionals',
			'desc'  => 'Upcoming MidSouth AFP quarterly meetings, workshops, and '
				. 'networking events for treasury and finance professionals '
				. 'in Memphis and the Mid-South region.',
		),
		'resources'          => array(
			'title' => 'Resources – MidSouth AFP',
			'desc'  => 'Finance and treasury resources for AFP members — news, '
				. 'podcasts, fintech updates, and industry focus reports.',
		),
		'contact-us'         => array(
			'title' => 'Contact MidSouth AFP',
			'desc'  => 'Get in touch with the MidSouth Association for Financial '
				. 'Professionals. Based in Memphis, TN.',
		),
		'membership-invoice' => array(
			'title' => 'Join MidSouth AFP – Membership',
			'desc'  => 'Become a MidSouth AFP member. Access quarterly events, '
				. 'CTP education credits, and a network of finance and '
				. 'treasury professionals in the Mid-South region.',
		),
	);

	foreach ( $page_metas as $slug => $meta ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $page instanceof WP_Post ) {
			update_post_meta( $page->ID, '_yoast_wpseo_title', $meta['title'] );
			update_post_meta( $page->ID, '_yoast_wpseo_metadesc', $meta['desc'] );
		}
	}

	$fp_li = '';
	if ( $front_page_id > 0 ) {
		$fp_li = '<li>' . esc_html(
			sprintf(
				/* translators: %d: front page ID */
				__( 'Front page meta title + description set (page ID %d)', 'midsouthafp-child' ),
				$front_page_id
			)
		) . '</li>';
	} else {
		$fp_li = '<li style="color:orange">' . esc_html__( 'No static front page set — homepage meta not updated', 'midsouthafp-child' ) . '</li>';
	}

	wp_die(
		'<div style="font-family:sans-serif;padding:2rem">' .
		'<h2 style="color:green">' . esc_html__( 'Yoast social settings configured', 'midsouthafp-child' ) . '</h2>' .
		'<ul>' .
		'<li>' . esc_html__( 'Open Graph: enabled', 'midsouthafp-child' ) . '</li>' .
		'<li>' . esc_html__( 'Default OG image:', 'midsouthafp-child' ) . ' <code>' . esc_html( $og_image_url ) . '</code></li>' .
		'<li>' . esc_html__( 'Twitter card: summary_large_image', 'midsouthafp-child' ) . '</li>' .
		'<li>' . esc_html__( 'Facebook page linked', 'midsouthafp-child' ) . '</li>' .
		$fp_li .
		'</ul>' .
		'<p><a href="' . esc_url( admin_url( 'admin.php?page=wpseo_social' ) ) . '">' .
		esc_html__( 'Verify in Yoast → Social', 'midsouthafp-child' ) . '</a></p>' .
		'</div>',
		esc_html__( 'Yoast Social Configured', 'midsouthafp-child' ),
		array( 'response' => 200 )
	);
}
add_action( 'admin_init', 'midsouthafp_child_configure_yoast_social' );

/**
 * Dismissible post-launch admin notice.
 * Dismiss forever via ?dismiss_msafp_notice=1&_wpnonce=...
 */
function midsouthafp_child_post_launch_notice() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! empty( $_GET['dismiss_msafp_notice'] ) ) {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'msafp_dismiss_notice' ) ) {
			wp_die( esc_html__( 'Invalid dismiss link.', 'midsouthafp-child' ) );
		}
		update_option( 'msafp_launch_notice_dismissed', '1' );
		wp_safe_redirect( admin_url() );
		exit;
	}

	if ( get_option( 'msafp_launch_notice_dismissed' ) ) {
		return;
	}

	$steps = array(
		__( 'Run alt text fix', 'midsouthafp-child' )        => admin_url( '?run_alt_fix=1' ),
		__( 'Run health check', 'midsouthafp-child' )        => admin_url( '?msafp_health=1' ),
		__( 'Configure Yoast OG', 'midsouthafp-child' )      => admin_url( '?msafp_yoast_social=1' ),
		__( 'Generate OG image', 'midsouthafp-child' )       => admin_url( '?generate_og_image=1' ),
		__( 'Purge Divi cache', 'midsouthafp-child' )        => admin_url( '?purge_divi_cache=1' ),
		__( 'Run ID + H1 audit', 'midsouthafp-child' )       => home_url( '/?audit_ids=1' ),
	);

	$links = '';
	foreach ( $steps as $label => $url ) {
		$links .= '<a href="' . esc_url( $url ) . '" style="margin-right:16px">' . esc_html( $label ) . '</a>';
	}

	$dismiss_url = esc_url(
		add_query_arg(
			array(
				'dismiss_msafp_notice' => '1',
				'_wpnonce'               => wp_create_nonce( 'msafp_dismiss_notice' ),
			),
			admin_url()
		)
	);

	echo '<div class="notice notice-warning" style="padding:12px 16px"><p><strong>' .
		esc_html__( 'MidSouth AFP — Post-launch checklist', 'midsouthafp-child' ) .
		'</strong></p><p>' . $links . '</p><p><a href="' . $dismiss_url .
		'" style="font-size:12px;color:#666">' .
		esc_html__( 'Dismiss permanently', 'midsouthafp-child' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'midsouthafp_child_post_launch_notice' );

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

/**
 * Public JSON health probe (no auth). Minimal fields for uptime monitors.
 *
 * @return WP_REST_Response
 */
function midsouthafp_child_rest_health_basic() {
	return new WP_REST_Response(
		array(
			'ok'           => true,
			'service'      => 'midsouthafp',
			'wp_version'   => get_bloginfo( 'version' ),
			'php_version'  => PHP_VERSION,
			'stylesheet'   => get_stylesheet(),
			'template'     => get_template(),
			'timestamp'    => gmdate( 'c' ),
		),
		200
	);
}

/**
 * Register REST route for basic health check.
 */
function midsouthafp_child_register_health_rest_route() {
	register_rest_route(
		'midsouthafp/v1',
		'/health',
		array(
			'methods'             => 'GET',
			'callback'            => 'midsouthafp_child_rest_health_basic',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'midsouthafp_child_register_health_rest_route' );

/**
 * Full health check (admin + ?msafp_health=1). HTML table output.
 */
function midsouthafp_child_health_check() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( empty( $_GET['msafp_health'] ) ) {
		return;
	}

	$checks = array();

	$checks['active_theme'] = array(
		'value' => get_stylesheet(),
		'pass'  => get_stylesheet() === 'midsouthafp-child',
		'note'  => 'Should be midsouthafp-child',
	);

	$checks['parent_theme'] = array(
		'value' => get_template(),
		'pass'  => get_template() === 'Divi',
		'note'  => 'Should be Divi',
	);

	$show_on_front = get_option( 'show_on_front' );
	$front_page_id = (int) get_option( 'page_on_front' );

	$checks['front_page_type'] = array(
		'value' => $show_on_front,
		'pass'  => 'page' === $show_on_front,
		'note'  => 'Must be "page" for hero + is_front_page() to fire',
	);

	$checks['front_page_set'] = array(
		'value' => (string) $front_page_id,
		'pass'  => $front_page_id > 0,
		'note'  => 'A static page must be set as homepage',
	);

	$mem_page = get_page_by_path( 'membership-invoice', OBJECT, 'page' );
	$checks['membership_page'] = array(
		'value' => $mem_page ? get_permalink( $mem_page->ID ) : 'NOT FOUND',
		'pass'  => (bool) $mem_page,
		'note'  => 'membership-invoice page; fallback to /contact-us/ if missing',
	);

	$contact_page = get_page_by_path( 'contact-us', OBJECT, 'page' );
	$checks['contact_fallback'] = array(
		'value' => $contact_page ? get_permalink( $contact_page->ID ) : 'NOT FOUND',
		'pass'  => (bool) $contact_page,
		'note'  => 'Fallback CTA target if membership-invoice missing',
	);

	$checks['tec_active'] = array(
		'value' => function_exists( 'tribe_get_events' ) ? 'active' : 'not found',
		'pass'  => function_exists( 'tribe_get_events' ),
		'note'  => 'Required for next-event card and event schema',
	);

	if ( function_exists( 'tribe_get_events' ) ) {
		$upcoming = tribe_get_events(
			array(
				'posts_per_page' => 5,
				'start_date'     => current_time( 'Y-m-d' ),
			)
		);
		$count    = is_array( $upcoming ) ? count( $upcoming ) : 0;
		$checks['upcoming_events'] = array(
			'value' => $count . ' upcoming events found',
			'pass'  => $count > 0,
			'note'  => 'At least 1 needed for next-event card + Event schema',
		);
	}

	if ( defined( 'WPSEO_VERSION' ) ) {
		$seo_plugin = 'Yoast SEO ' . WPSEO_VERSION;
	} elseif ( defined( 'RANK_MATH_VERSION' ) ) {
		$seo_plugin = 'Rank Math ' . RANK_MATH_VERSION;
	} elseif ( defined( 'AIOSEO_VERSION' ) ) {
		$seo_plugin = 'AIOSEO ' . AIOSEO_VERSION;
	} else {
		$seo_plugin = 'NONE';
	}

	$checks['seo_plugin'] = array(
		'value' => $seo_plugin,
		'pass'  => 'NONE' !== $seo_plugin,
		'note'  => 'Install Yoast SEO to replace fallback meta description',
	);

	$checks['yoast_active'] = array(
		'value' => defined( 'WPSEO_VERSION' ) ? 'active v' . WPSEO_VERSION : 'not active',
		'pass'  => defined( 'WPSEO_VERSION' ),
		'note'  => 'Required for SEO meta, OG, and sitemap',
	);

	$wpseo_social = get_option( 'wpseo_social', array() );
	$checks['yoast_og_enabled'] = array(
		'value' => ! empty( $wpseo_social['opengraph'] ) ? 'enabled' : 'disabled',
		'pass'  => ! empty( $wpseo_social['opengraph'] ),
		'note'  => 'Open Graph must be on for LinkedIn/Facebook sharing',
	);

	$fp_id_for_yoast = (int) get_option( 'page_on_front' );
	$fp_desc         = $fp_id_for_yoast ? get_post_meta( $fp_id_for_yoast, '_yoast_wpseo_metadesc', true ) : '';
	$fp_desc_preview = $fp_desc
		? ( strlen( $fp_desc ) > 60 ? substr( $fp_desc, 0, 60 ) . '...' : $fp_desc )
		: 'NOT SET';
	$checks['yoast_homepage_metadesc'] = array(
		'value' => $fp_desc_preview,
		'pass'  => ! empty( $fp_desc ),
		'note'  => 'Homepage meta description (run ?msafp_yoast_social=1 to set)',
	);

	$wpseo_opts = get_option( 'wpseo', array() );
	$checks['yoast_sitemap_enabled'] = array(
		'value' => ! empty( $wpseo_opts['enable_xml_sitemap'] ) ? 'enabled' : 'disabled',
		'pass'  => ! empty( $wpseo_opts['enable_xml_sitemap'] ),
		'note'  => 'XML sitemap at /sitemap_index.xml',
	);

	$checks['fallback_meta_silent'] = array(
		'value' => defined( 'WPSEO_VERSION' ) ? 'silent (Yoast active)' : 'ACTIVE (no SEO plugin)',
		'pass'  => defined( 'WPSEO_VERSION' ),
		'note'  => 'Fallback fires only when no SEO plugin detected',
	);

	$cache_dir = WP_CONTENT_DIR . '/et-cache';
	$checks['et_cache_writable'] = array(
		'value' => is_dir( $cache_dir )
			? ( is_writable( $cache_dir ) ? 'writable' : 'NOT WRITABLE' )
			: 'dir missing',
		'pass'  => is_dir( $cache_dir ) && is_writable( $cache_dir ),
		'note'  => 'Divi needs this to regenerate static CSS',
	);

	$checks['php_version'] = array(
		'value' => PHP_VERSION,
		'pass'  => version_compare( PHP_VERSION, '7.4', '>=' ),
		'note'  => 'Minimum 7.4 required',
	);

	$checks['wp_debug'] = array(
		'value' => WP_DEBUG ? 'ON' : 'OFF',
		'pass'  => ! WP_DEBUG,
		'note'  => 'Should be OFF on production',
	);

	$git_head = '';
	$git_candidates = array(
		ABSPATH . '.git/refs/heads/main',
		ABSPATH . '.git/refs/heads/master',
		dirname( ABSPATH ) . '/.git/refs/heads/main',
		dirname( ABSPATH ) . '/.git/refs/heads/master',
	);
	foreach ( $git_candidates as $git_file ) {
		if ( is_readable( $git_file ) ) {
			$raw = trim( (string) file_get_contents( $git_file ) );
			if ( preg_match( '/^[a-f0-9]{40}$/i', $raw ) ) {
				$git_head = $raw;
				break;
			}
		}
	}

	$checks['git_head'] = array(
		'value' => $git_head ? $git_head : 'could not read',
		'pass'  => 40 === strlen( $git_head ),
		'note'  => 'Compare to: git log --oneline -1 on server',
	);

	$total  = count( $checks );
	$passed = count(
		array_filter(
			$checks,
			static function ( $c ) {
				return ! empty( $c['pass'] );
			}
		)
	);
	$failed = $total - $passed;

	header( 'Content-Type: text/html; charset=utf-8' );
	echo '<html><body style="font-family:monospace;padding:2rem">';
	echo '<h2>MidSouth AFP — Health Check</h2>';
	echo '<p>' . (int) $passed . '/' . (int) $total . ' checks passed';
	if ( $failed ) {
		echo ' | <strong style="color:red">' . (int) $failed . ' FAILED</strong>';
	}
	echo '</p><table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse">';
	echo '<tr><th>Check</th><th>Value</th><th>Pass</th><th>Note</th></tr>';
	foreach ( $checks as $key => $c ) {
		$color = ! empty( $c['pass'] ) ? '#d4edda' : '#f8d7da';
		$icon  = ! empty( $c['pass'] ) ? '&#10003;' : '&#10005;';
		echo '<tr style="background:' . esc_attr( $color ) . '">';
		echo '<td>' . esc_html( $key ) . '</td>';
		echo '<td>' . esc_html( (string) $c['value'] ) . '</td>';
		echo '<td style="text-align:center">' . $icon . '</td>';
		echo '<td>' . esc_html( $c['note'] ) . '</td>';
		echo '</tr>';
	}
	echo '</table></body></html>';
	exit;
}
add_action( 'admin_init', 'midsouthafp_child_health_check' );

require_once get_stylesheet_directory() . '/inc/id-audit.php';
require_once get_stylesheet_directory() . '/inc/homepage-hero.php';
require_once get_stylesheet_directory() . '/inc/rollback.php';
require_once get_stylesheet_directory() . '/inc/generate-og-image.php';
