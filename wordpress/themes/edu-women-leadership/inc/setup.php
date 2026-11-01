<?php
/**
 * Theme supports, menus, document titles and head clean-up.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'ewil', get_template_directory() . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 300,
				'width'       => 300,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);
		add_theme_support( 'editor-styles' );
		add_editor_style( array( 'assets/css/fonts.css', 'assets/css/editor.css' ) );
		remove_theme_support( 'core-block-patterns' );

		register_nav_menus(
			array(
				'primary'        => __( 'Main menu', 'ewil' ),
				'footer_explore' => __( 'Footer: first column', 'ewil' ),
				'footer_part'    => __( 'Footer: second column', 'ewil' ),
				'footer_legal'   => __( 'Footer: legal links', 'ewil' ),
			)
		);
	}
);

/**
 * Page titles as in the original: "Edu Women in Leadership 2027" on the
 * homepage, "About · Edu Women in Leadership 2027" elsewhere.
 */
add_filter(
	'document_title_separator',
	function () {
		return '·';
	}
);

add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( ! function_exists( 'ewil_setting' ) ) {
			return $parts;
		}
		$site = trim( ewil_setting( 'event_name' ) . ' ' . ewil_setting( 'event_year' ) );
		if ( is_front_page() ) {
			return array( 'title' => $site );
		}
		unset( $parts['tagline'] );
		$parts['site'] = $site;
		return $parts;
	}
);

/**
 * Search engine description, unless an SEO plugin handles it.
 */
add_action(
	'wp_head',
	function () {
		if ( ! function_exists( 'ewil_setting' ) || defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'AIOSEO\Plugin\AIOSEO' ) ) {
			return;
		}
		$description = is_singular() && has_excerpt() ? get_the_excerpt() : ewil_setting( 'meta_description' );
		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";
		}
	},
	1
);

add_post_type_support( 'page', 'excerpt' );

/**
 * Head clean-up: nothing the design does not use.
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
