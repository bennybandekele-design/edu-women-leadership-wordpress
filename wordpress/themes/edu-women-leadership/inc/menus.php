<?php
/**
 * WordPress menus printed with exactly the original markup: <li><a href>…</a></li>,
 * with aria-current="page" on the current page.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minimal menu walker. Custom CSS classes set on a menu item in wp-admin are kept.
 */
class EWIL_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Starts one menu item.
	 *
	 * @param string   $output Output.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Arguments.
	 * @param int      $id     Item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$custom  = array_filter( (array) get_post_meta( $item->ID, '_menu_item_classes', true ) );
		// A menu item with the CSS class "reg-item" is the design's Register button.
		$button  = in_array( 'reg-item', $custom, true );
		$current = ! $button && in_array( 'current-menu-item', (array) $item->classes, true );
		$output .= '<li' . ( $custom ? ' class="' . esc_attr( implode( ' ', $custom ) ) . '"' : '' ) . '>';
		$output .= '<a' . ( $button ? ' class="btn btn-cta"' : '' ) . ' href="' . esc_url( $item->url ) . '"' . ( $current ? ' aria-current="page"' : '' ) . ( $item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '' ) . '>' . esc_html( $item->title ) . '</a>';
	}

	/**
	 * Ends one menu item.
	 *
	 * @param string   $output Output.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}

	/**
	 * The design has no sub-menus; nested items are shown flat.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * See start_lvl().
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}
}

/**
 * Prints the menu assigned to a location as a list.
 *
 * @param string $location Menu location.
 * @param string $wrap     items_wrap pattern.
 */
function ewil_menu( $location, $wrap = '<ul>%3$s</ul>' ) {
	if ( ! has_nav_menu( $location ) ) {
		return false;
	}
	wp_nav_menu(
		array(
			'theme_location' => $location,
			'container'      => false,
			'items_wrap'     => $wrap,
			'walker'         => new EWIL_Menu_Walker(),
			'depth'          => 1,
			'fallback_cb'    => false,
		)
	);
	return true;
}

/**
 * Name of the menu assigned to a location, used as the footer column heading.
 *
 * @param string $location Menu location.
 */
function ewil_menu_name( $location ) {
	$locations = get_nav_menu_locations();
	$menu      = isset( $locations[ $location ] ) ? wp_get_nav_menu_object( $locations[ $location ] ) : null;
	return $menu ? $menu->name : '';
}

/**
 * The main menu's Register button item (CSS class "reg-item"), if there is one.
 * On narrow screens the design shows it as a separate button beside the logo.
 */
function ewil_register_item() {
	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		return null;
	}
	foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
		if ( $item && in_array( 'reg-item', (array) $item->classes, true ) ) {
			return $item;
		}
	}
	return null;
}

/**
 * Links of a menu location as plain [url, title] pairs (for the legal links line).
 *
 * @param string $location Menu location.
 * @return array<int,array{0:string,1:string}>
 */
function ewil_menu_links( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	return array_map(
		function ( $i ) {
			return array( $i->url, $i->title );
		},
		$items ? $items : array()
	);
}
