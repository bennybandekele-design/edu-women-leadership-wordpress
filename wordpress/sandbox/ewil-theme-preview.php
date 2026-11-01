<?php
/**
 * Plugin Name: EWIL theme preview (temporary)
 * Description: Shows the new Edu Women in Leadership theme only to requests that carry the secret preview key (?ewil_preview=KEY, remembered in a cookie). Everyone else keeps the active theme. Delete this file after the new theme is activated.
 */

defined( 'ABSPATH' ) || exit;

/**
 * True when this request carries the valid preview key.
 */
function ewil_preview_active() {
	static $active = null;
	if ( null !== $active ) {
		return $active;
	}
	$token  = (string) get_option( 'ewil_preview_token' );
	$given  = isset( $_GET['ewil_preview'] ) ? sanitize_text_field( wp_unslash( $_GET['ewil_preview'] ) ) : ( isset( $_COOKIE['ewil_preview'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['ewil_preview'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification
	$active = strlen( $token ) >= 20 && hash_equals( $token, $given ) && is_dir( get_theme_root() . '/edu-women-leadership' );
	if ( $active && isset( $_GET['ewil_preview'] ) && ! headers_sent() ) { // phpcs:ignore WordPress.Security.NonceVerification
		setcookie( 'ewil_preview', $token, time() + DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	}
	return $active;
}

if ( ewil_preview_active() ) {
	$ewil_switch = function () {
		return 'edu-women-leadership';
	};
	add_filter( 'stylesheet', $ewil_switch, 99 );
	add_filter( 'template', $ewil_switch, 99 );
	// Theme settings (menus, logo) are read through these options.
	add_filter( 'pre_option_stylesheet', $ewil_switch, 99 );
	add_filter( 'pre_option_template', $ewil_switch, 99 );

	// Never cache previews, and never serve them from the cache.
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	add_action(
		'init',
		function () {
			do_action( 'litespeed_control_set_nocache', 'ewil theme preview' );
		}
	);

	// Skip the coming-soon screen for previews only (its settings are untouched).
	add_action(
		'wp',
		function () {
			global $wp_filter;
			if ( empty( $wp_filter['template_redirect'] ) ) {
				return;
			}
			foreach ( $wp_filter['template_redirect']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $cb ) {
					if ( is_array( $cb['function'] ) && isset( $cb['function'][1] ) && 'cmp_displayPage' === $cb['function'][1] ) {
						remove_action( 'template_redirect', $cb['function'], $priority );
					}
				}
			}
		},
		99
	);

	// Previews must never be indexed by search engines.
	add_filter(
		'wp_robots',
		function ( $robots ) {
			$robots['noindex'] = true;
			return $robots;
		}
	);
}
