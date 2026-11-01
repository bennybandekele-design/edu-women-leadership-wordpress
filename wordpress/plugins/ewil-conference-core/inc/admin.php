<?php
/**
 * wp-admin conveniences for the conference post types.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Conference lists sort by their drag order (the "Order" box), matching the website.
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'orderby' ) ) {
			return;
		}
		$type = $query->get( 'post_type' );
		if ( in_array( $type, array( 'ewil_speaker', 'ewil_track', 'ewil_rate', 'ewil_opportunity', 'ewil_award_category', 'ewil_highlight' ), true ) ) {
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'ASC' ) );
		}
	}
);

/**
 * Order and photo columns, so editors can see the website order at a glance.
 */
foreach ( array( 'ewil_speaker', 'ewil_track', 'ewil_rate', 'ewil_opportunity', 'ewil_award_category', 'ewil_highlight' ) as $ewil_type ) {
	add_filter(
		"manage_{$ewil_type}_posts_columns",
		function ( $columns ) use ( $ewil_type ) {
			$out = array();
			foreach ( $columns as $key => $label ) {
				if ( 'title' === $key && 'ewil_speaker' === $ewil_type ) {
					$out['ewil_photo'] = __( 'Photo', 'ewil' );
				}
				$out[ $key ] = $label;
				if ( 'title' === $key ) {
					$out['ewil_order'] = __( 'Order', 'ewil' );
				}
			}
			unset( $out['date'] );
			return $out;
		}
	);
	add_action(
		"manage_{$ewil_type}_posts_custom_column",
		function ( $column, $post_id ) {
			if ( 'ewil_order' === $column ) {
				echo (int) get_post_field( 'menu_order', $post_id );
			} elseif ( 'ewil_photo' === $column ) {
				echo get_the_post_thumbnail( $post_id, array( 48, 48 ), array( 'style' => 'border-radius:50%;object-fit:cover;width:48px;height:48px' ) ) ?: '—'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		},
		10,
		2
	);
}
