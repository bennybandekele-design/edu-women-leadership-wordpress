<?php
/**
 * Styles and scripts. Loads only the design's own files and removes WordPress
 * default styles that would otherwise leak into the design.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	function () {
		$uri = get_template_directory_uri() . '/assets';
		$dir = get_template_directory() . '/assets';
		$ver = fn( $file ) => EWIL_THEME_VERSION . '.' . filemtime( $dir . $file );

		wp_enqueue_style( 'ewil-fonts', $uri . '/css/fonts.css', array(), $ver( '/css/fonts.css' ) );
		wp_enqueue_style( 'ewil-main', $uri . '/css/main.css', array( 'ewil-fonts' ), $ver( '/css/main.css' ) );
		wp_enqueue_style( 'ewil-wp', $uri . '/css/wp.css', array( 'ewil-main' ), $ver( '/css/wp.css' ) );
		wp_enqueue_script( 'ewil-main', $uri . '/js/main.js', array(), $ver( '/js/main.js' ), true );
		wp_localize_script( 'ewil-main', 'EWIL', array( 'routes' => ewil_legacy_routes() ) );

		// WordPress defaults the design does not use.
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'wp-block-library-theme' );
		if ( ! is_singular() || ! has_blocks() ) {
			wp_dequeue_style( 'wp-block-library' );
		}
	},
	20
);

/**
 * Preloads the two most visible font files so text renders in the right face
 * from the first paint.
 */
add_action(
	'wp_head',
	function () {
		$uri = get_template_directory_uri() . '/assets/fonts/';
		foreach ( glob( get_template_directory() . '/assets/fonts/{fraunces,figtree}-latin-[0-9]*.woff2', GLOB_BRACE ) as $i => $file ) {
			if ( $i > 1 ) {
				break;
			}
			echo '<link rel="preload" href="' . esc_url( $uri . basename( $file ) ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		}
	},
	2
);

/**
 * Old one-page links (#about, #register …) mapped to the new page addresses,
 * so links shared before the move still arrive at the right page.
 */
function ewil_legacy_routes() {
	$map = array(
		'home'            => home_url( '/' ),
		'about'           => ewil_page_url( 'about' ),
		'programme'       => ewil_page_url( 'programme' ),
		'speakers'        => ewil_page_url( 'speakers' ),
		'call-for-papers' => ewil_page_url( 'call-for-papers' ),
		'awards'          => ewil_page_url( 'awards' ),
		'partners'        => ewil_page_url( 'partners' ),
		'register'        => ewil_page_url( 'register' ),
		'contact'         => ewil_page_url( 'contact' ),
		'privacy'         => get_privacy_policy_url(),
		'terms'           => ewil_page_url_by_path( 'website-terms' ),
		'submissions'     => admin_url( 'edit.php?post_type=ewil_submission' ),
	);
	return array_filter( $map );
}
