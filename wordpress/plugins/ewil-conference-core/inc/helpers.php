<?php
/**
 * Small helpers shared by the plugin and the theme.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Splits a textarea value into trimmed, non-empty lines.
 *
 * @param string|array|null $text Text with one item per line.
 * @return string[]
 */
function ewil_lines( $text ) {
	if ( is_array( $text ) ) {
		return array_values( array_filter( array_map( 'trim', $text ) ) );
	}
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ) ) );
}

/**
 * Escapes text and turns *accent words* into <em>accent words</em>.
 * Used for headings whose design sets part of the line in italics.
 *
 * @param string $text Plain text with optional *asterisk* accents.
 */
function ewil_accent( $text ) {
	$html = esc_html( (string) $text );
	return preg_replace( '/\*([^*]+)\*/', '<em>$1</em>', $html );
}

/**
 * Removes *accent* markers, for places where the plain text is needed.
 *
 * @param string $text Text with optional *asterisk* accents.
 */
function ewil_plain( $text ) {
	return str_replace( '*', '', (string) $text );
}

/**
 * Replaces {tokens} in editable text with values from the conference settings,
 * so dates and limits are typed once.
 *
 * @param string $text Text containing tokens.
 */
function ewil_tokens( $text ) {
	$map = array(
		'{abstract}'   => ewil_setting( 'cfp_abstract_min' ) . '–' . ewil_setting( 'cfp_abstract_max' ) . ' words',
		'{bio}'        => ewil_setting( 'cfp_bio_min' ) . '–' . ewil_setting( 'cfp_bio_max' ) . ' words',
		'{dates}'      => ewil_setting( 'date_text' ),
		'{venue}'      => ewil_setting( 'venue' ),
		'{city}'       => ewil_setting( 'city' ),
		'{early_bird}' => ewil_setting( 'early_bird_deadline' ),
		'{year}'       => ewil_setting( 'event_year' ),
		'{host}'       => ewil_setting( 'host' ),
	);
	return strtr( (string) $text, $map );
}

/**
 * Published items of one conference post type, in the order set in wp-admin.
 *
 * @param string $post_type Post type slug.
 * @param array  $args      Extra get_posts() arguments.
 * @return WP_Post[]
 */
function ewil_items( $post_type, $args = array() ) {
	return get_posts(
		array_merge(
			array(
				'post_type'        => $post_type,
				'post_status'      => 'publish',
				'numberposts'      => -1,
				'orderby'          => array(
					'menu_order' => 'ASC',
					'date'       => 'ASC',
				),
				'suppress_filters' => false,
			),
			$args
		)
	);
}

/**
 * Reads an ACF field from a post, falling back to a default when it is empty.
 *
 * @param string   $name    Field name.
 * @param mixed    $default Value used when the field is empty.
 * @param int|null $post_id Post ID; the current post when null.
 */
function ewil_field( $name, $default = '', $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = get_field( $name, $post_id ?? get_the_ID() );
	return ( null === $value || '' === $value || false === $value || array() === $value ) ? $default : $value;
}

/**
 * Converts an ACF date-time value (site timezone) to ISO 8601, e.g.
 * 2027-08-27T00:00:00+02:00, for the countdown and the nomination window.
 *
 * @param string $value Date in Y-m-d H:i:s or Y-m-d format.
 */
function ewil_iso( $value ) {
	if ( ! $value ) {
		return '';
	}
	$date = date_create( ewil_normalise_date( $value ), wp_timezone() );
	return $date ? $date->format( 'c' ) : '';
}

/**
 * Formats an ACF date or date-time value for display, e.g. "9 Nov 2026".
 *
 * @param string $value  Date in Y-m-d H:i:s or Y-m-d format.
 * @param string $format PHP date format.
 */
function ewil_date( $value, $format = 'j M Y' ) {
	if ( ! $value ) {
		return '';
	}
	$date = date_create( ewil_normalise_date( $value ), wp_timezone() );
	return $date ? wp_date( $format, $date->getTimestamp() ) : '';
}

/**
 * ACF date pickers store Ymd (20270828); date-time pickers store Y-m-d H:i:s.
 *
 * @param string $value Stored date.
 */
function ewil_normalise_date( $value ) {
	$value = (string) $value;
	return preg_match( '/^\d{8}$/', $value ) ? substr( $value, 0, 4 ) . '-' . substr( $value, 4, 2 ) . '-' . substr( $value, 6, 2 ) : $value;
}

/**
 * Prints the attributes of an ACF link field as an <a> opening tag's href and target.
 *
 * @param array|null $link ACF link array (url, title, target).
 */
function ewil_link_attrs( $link ) {
	if ( empty( $link['url'] ) ) {
		return '';
	}
	$attrs = 'href="' . esc_url( $link['url'] ) . '"';
	if ( ! empty( $link['target'] ) ) {
		$attrs .= ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"';
	}
	return $attrs;
}
