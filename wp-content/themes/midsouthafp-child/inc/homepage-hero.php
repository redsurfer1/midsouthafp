<?php
/**
 * Homepage hero banner — injected above Divi builder content.
 * Hooked to: et_before_main_content (Divi-specific action).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output hero markup on the front page only.
 */
function midsouthafp_child_homepage_hero() {
	if ( ! is_front_page() ) {
		return;
	}

	$next_event      = '';
	$events_page_url = function_exists( 'tribe_get_events_link' )
		? tribe_get_events_link()
		: home_url( '/events/' );

	if ( function_exists( 'tribe_get_events' ) ) {
		$upcoming = tribe_get_events(
			array(
				'posts_per_page' => 1,
				'start_date'     => current_time( 'Y-m-d' ),
				'orderby'        => 'event_date',
				'order'          => 'ASC',
			)
		);
		if ( ! empty( $upcoming ) ) {
			$ev       = $upcoming[0];
			$ev_title = get_the_title( $ev->ID );
			$ev_date  = function_exists( 'tribe_get_start_date' )
				? tribe_get_start_date( $ev->ID, false, 'l, F j, Y' )
				: '';
			$ev_time = function_exists( 'tribe_get_start_date' )
				? tribe_get_start_date( $ev->ID, false, 'g:i a' )
				: '';
			$ev_url = get_permalink( $ev->ID );
			$next_event = sprintf(
				'<a href="%s" class="msafp-hero__event-link">
					<span class="msafp-hero__event-label">Next Event</span>
					<span class="msafp-hero__event-title">%s</span>
					<span class="msafp-hero__event-date">%s &bull; %s</span>
				</a>',
				esc_url( $ev_url ),
				esc_html( $ev_title ),
				esc_html( $ev_date ),
				esc_html( $ev_time )
			);
		}
	}
	?>
	<section class="msafp-hero" aria-label="<?php echo esc_attr( 'MidSouth AFP – Site hero' ); ?>">
		<div class="msafp-hero__inner">
			<p class="msafp-hero__eyebrow">Memphis &bull; West Tennessee &bull; Mid-South Region</p>
			<h1 class="msafp-hero__headline">
				Empowering<br>Financial Professionals
			</h1>
			<p class="msafp-hero__sub">
				A non-profit community for treasury, finance, and payments professionals
				— quarterly meetings, CTP education credits, and peer networking since 1979.
			</p>
			<div class="msafp-hero__ctas">
				<a href="<?php echo esc_url( home_url( '/membership-invoice' ) ); ?>"
					class="msafp-btn msafp-btn--primary">
					Join MidSouth AFP
				</a>
				<a href="<?php echo esc_url( $events_page_url ); ?>"
					class="msafp-btn msafp-btn--secondary">
					View Events
				</a>
			</div>
			<?php if ( $next_event ) : ?>
			<div class="msafp-hero__next-event">
				<?php echo $next_event; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_* above. ?>
			</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
add_action( 'et_before_main_content', 'midsouthafp_child_homepage_hero', 1 );
