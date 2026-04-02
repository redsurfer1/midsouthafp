<?php
/**
 * One-time OG image generator (1200×630, navy/gold brand colors).
 * Visit: /wp-admin/?generate_og_image=1 (admin only)
 * Output: wp-content/uploads/og-midsouthafp-1200x630.png
 *
 * @package MidSouthAFP_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate branded Open Graph image and register as media attachment.
 */
function midsouthafp_child_generate_og_image() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( empty( $_GET['generate_og_image'] ) ) {
		return;
	}
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		wp_die(
			esc_html__( 'GD library not available on this server. Create the OG image manually at 1200×630px.', 'midsouthafp-child' ),
			esc_html__( 'OG Image', 'midsouthafp-child' ),
			array( 'response' => 503 )
		);
	}

	$width  = 1200;
	$height = 630;
	$img    = imagecreatetruecolor( $width, $height );
	if ( ! $img ) {
		wp_die( esc_html__( 'Could not create image.', 'midsouthafp-child' ) );
	}

	// Colors: navy #0a1f44, gold #c9a84c, light #b8cde4.
	$navy  = imagecolorallocate( $img, 10, 31, 68 );
	$white = imagecolorallocate( $img, 255, 255, 255 );
	$gold  = imagecolorallocate( $img, 201, 168, 76 );
	$light = imagecolorallocate( $img, 184, 205, 228 );

	imagefilledrectangle( $img, 0, 0, $width, $height, $navy );

	// Gold accent bars.
	imagefilledrectangle( $img, 0, 0, $width, 4, $gold );
	imagefilledrectangle( $img, 0, $height - 8, $width, $height, $gold );

	$font_path = get_stylesheet_directory() . '/assets/fonts/Inter-Bold.ttf';
	$use_ttf   = file_exists( $font_path ) && function_exists( 'imagettftext' );

	if ( $use_ttf ) {
		imagettftext( $img, 64, 0, 80, 260, $white, $font_path, 'MidSouth AFP' );
		$font_reg = str_replace( 'Bold', 'Regular', $font_path );
		if ( ! file_exists( $font_reg ) ) {
			$font_reg = $font_path;
		}
		imagettftext( $img, 32, 0, 84, 340, $light, $font_reg, 'Empowering Financial Professionals' );
		imagettftext( $img, 20, 0, 84, 400, $gold, $font_reg, 'Memphis, TN  •  Est. 1979  •  midsouthafp.org' );
	} else {
		imagestring( $img, 5, 80, 220, 'MidSouth AFP', $white );
		imagestring( $img, 4, 80, 260, 'Empowering Financial Professionals', $light );
		imagestring( $img, 3, 80, 300, 'Memphis, TN  Est. 1979  midsouthafp.org', $gold );
	}

	$logo_path = WP_CONTENT_DIR . '/uploads/2024/08/afp-logo.jpg';
	if ( file_exists( $logo_path ) ) {
		$logo = imagecreatefromjpeg( $logo_path );
		if ( $logo ) {
			$lw = imagesx( $logo );
			$lh = imagesy( $logo );
			$scale  = min( 200 / $lw, 200 / $lh );
			$new_lw = (int) round( $lw * $scale );
			$new_lh = (int) round( $lh * $scale );
			$dest_x = $width - $new_lw - 60;
			$dest_y = (int) round( ( $height - $new_lh ) / 2 );
			imagecopyresampled( $img, $logo, $dest_x, $dest_y, 0, 0, $new_lw, $new_lh, $lw, $lh );
			imagedestroy( $logo );
		}
	}

	$upload_dir = wp_upload_dir();
	if ( ! empty( $upload_dir['error'] ) ) {
		imagedestroy( $img );
		wp_die( esc_html( $upload_dir['error'] ) );
	}

	$output = $upload_dir['basedir'] . '/og-midsouthafp-1200x630.png';
	imagepng( $img, $output, 6 );
	imagedestroy( $img );

	$filetype = wp_check_filetype( basename( $output ), null );

	$attachment = array(
		'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/png',
		'post_title'     => 'MidSouth AFP OG Image 1200x630',
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	$attach_id = wp_insert_attachment( $attachment, $output, 0, true );
	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		$msg = is_wp_error( $attach_id ) ? $attach_id->get_error_message() : __( 'Could not create attachment.', 'midsouthafp-child' );
		wp_die( esc_html( $msg ) );
	}

	$attach_data = wp_generate_attachment_metadata( $attach_id, $output );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	$img_url = $upload_dir['baseurl'] . '/og-midsouthafp-1200x630.png';

	wp_die(
		'<div style="font-family:sans-serif;padding:2rem">' .
		'<h2 style="color:green">' . esc_html__( 'OG image generated', 'midsouthafp-child' ) . '</h2>' .
		'<p>' . esc_html__( 'Saved to:', 'midsouthafp-child' ) . ' <code>' . esc_html( $output ) . '</code></p>' .
		'<p>' . esc_html__( 'URL:', 'midsouthafp-child' ) . ' <a href="' . esc_url( $img_url ) . '" target="_blank" rel="noopener noreferrer">' .
		esc_html( $img_url ) . '</a></p>' .
		'<p>' . esc_html__( 'Attachment ID:', 'midsouthafp-child' ) . ' ' . (int) $attach_id . '</p>' .
		'<p><strong>' . esc_html__( 'Next step:', 'midsouthafp-child' ) . '</strong> ' .
		esc_html__( 'Visit', 'midsouthafp-child' ) .
		' <a href="' . esc_url( admin_url( '?msafp_yoast_social=1&og_image_id=' . (int) $attach_id ) ) . '">' .
		esc_html( admin_url( '?msafp_yoast_social=1&og_image_id=' . (int) $attach_id ) ) . '</a> ' .
		esc_html__( 'to set Yoast default OG image and ID.', 'midsouthafp-child' ) . '</p>' .
		'<p><img src="' . esc_url( $img_url ) . '" alt="" style="max-width:100%;border:1px solid #ccc;margin-top:1rem" /></p>' .
		'</div>',
		esc_html__( 'OG Image Generated', 'midsouthafp-child' ),
		array( 'response' => 200 )
	);
}
add_action( 'admin_init', 'midsouthafp_child_generate_og_image' );
