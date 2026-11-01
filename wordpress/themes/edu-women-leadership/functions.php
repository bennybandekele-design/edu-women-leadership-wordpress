<?php
/**
 * Edu Women in Leadership theme.
 *
 * Presentation only: content, settings and forms come from the EWIL Conference
 * Core plugin and Advanced Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

define( 'EWIL_THEME_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/assets.php';
require_once get_template_directory() . '/inc/menus.php';
require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/editor.php';

/**
 * Without the companion plugin the templates have no content to show.
 */
if ( ! function_exists( 'ewil_setting' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'The Edu Women in Leadership theme needs the EWIL Conference Core plugin to be active.', 'ewil' ) . '</p></div>';
		}
	);
	add_action(
		'template_redirect',
		function () {
			wp_die( esc_html__( 'This website is being updated. Please check back shortly.', 'ewil' ), '', array( 'response' => 503 ) );
		}
	);
}
