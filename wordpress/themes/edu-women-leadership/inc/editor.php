<?php
/**
 * Editing experience: the block editor only where a page has running text, on-brand
 * colours only, and editor headings styled like the design.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Templates whose pages have no editor text: they open as a clean field form.
 */
function ewil_templates_without_editor() {
	return array( 'page-templates/programme.php', 'page-templates/speakers.php', 'page-templates/register.php', 'page-templates/contact.php', 'page-templates/awards.php' );
}

/**
 * True when a page shows no editor content.
 *
 * @param int $post_id Page ID.
 */
function ewil_page_has_no_editor( $post_id ) {
	if ( 'page' !== get_post_type( $post_id ) ) {
		return false;
	}
	return (int) get_option( 'page_on_front' ) === (int) $post_id || in_array( get_page_template_slug( $post_id ), ewil_templates_without_editor(), true );
}

add_filter(
	'use_block_editor_for_post',
	function ( $use, $post ) {
		return ewil_page_has_no_editor( $post->ID ) ? false : $use;
	},
	10,
	2
);

add_action(
	'load-post.php',
	function () {
		$id = absint( $_GET['post'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( $id && ewil_page_has_no_editor( $id ) ) {
			remove_post_type_support( 'page', 'editor' );
		}
	}
);

/**
 * Brand colours and sizes only, so editor text stays on-brand.
 */
add_action(
	'after_setup_theme',
	function () {
		add_theme_support(
			'editor-color-palette',
			array(
				array( 'name' => __( 'Purple', 'ewil' ), 'slug' => 'brand-purple', 'color' => '#604078' ),
				array( 'name' => __( 'Orange', 'ewil' ), 'slug' => 'brand-orange', 'color' => '#F28C28' ),
				array( 'name' => __( 'Charcoal', 'ewil' ), 'slug' => 'brand-charcoal', 'color' => '#33313A' ),
			)
		);
		add_theme_support( 'disable-custom-colors' );
		add_theme_support( 'disable-custom-gradients' );
		add_theme_support( 'disable-custom-font-sizes' );
		add_theme_support( 'editor-font-sizes', array() );
		add_theme_support( 'editor-gradient-presets', array() );
	}
);

/**
 * Editor headings get the design's heading classes (h2 → "h2", h3 → "h3"),
 * matching the hand-written headings of the original pages.
 */
add_filter(
	'render_block_core/heading',
	function ( $html, $block ) {
		$level = (int) ( $block['attrs']['level'] ?? 2 );
		if ( 2 === $level || 3 === $level ) {
			$html = preg_replace( '/<h' . $level . '([^>]*)class="/', '<h' . $level . '$1class="h' . $level . ' ', $html, 1, $count );
			if ( ! $count ) {
				$html = preg_replace( '/<h' . $level . '(\s|>)/', '<h' . $level . ' class="h' . $level . '"$1', $html, 1 );
			}
		}
		return $html;
	},
	10,
	2
);

/**
 * Only simple text blocks on the editor pages; the design handles layout.
 */
add_filter(
	'allowed_block_types_all',
	function ( $allowed, $context ) {
		if ( empty( $context->post ) || 'page' !== $context->post->post_type ) {
			return $allowed;
		}
		return array( 'core/paragraph', 'core/heading', 'core/list', 'core/list-item', 'core/quote', 'core/table', 'core/separator', 'core/shortcode' );
	},
	10,
	2
);
