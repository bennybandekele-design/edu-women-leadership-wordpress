<?php
/**
 * Small template helpers. Each prints markup identical to the original site.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Permalink of the first published page using a page template.
 *
 * @param string $template Template name, e.g. "register" for page-templates/register.php.
 */
function ewil_page_url( $template ) {
	static $cache = array();
	if ( ! isset( $cache[ $template ] ) ) {
		$pages              = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'  => 'page-templates/' . $template . '.php', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		$cache[ $template ] = $pages ? get_permalink( $pages[0] ) : '';
	}
	return $cache[ $template ];
}

/**
 * Permalink of a page by its path.
 *
 * @param string $path Page path (slug).
 */
function ewil_page_url_by_path( $path ) {
	$page = get_page_by_path( $path );
	return $page && 'publish' === $page->post_status ? get_permalink( $page ) : '';
}

/**
 * A field from the current page, with the original text as fallback.
 *
 * @param string $name    Field name.
 * @param mixed  $default Fallback.
 */
function ewil_t( $name, $default = '' ) {
	return function_exists( 'ewil_field' ) ? ewil_field( $name, $default ) : $default;
}

/**
 * Heading text: settings tokens filled in, *accent* words in <em>.
 *
 * @param string $text Heading text.
 */
function ewil_h( $text ) {
	return ewil_accent( ewil_tokens( $text ) );
}

/**
 * Plain text with settings tokens filled in, escaped.
 *
 * @param string $text Text.
 */
function ewil_x( $text ) {
	return esc_html( ewil_tokens( $text ) );
}

/**
 * Prints a button from an ACF link field.
 *
 * @param array|null $link   ACF link (url, title, target).
 * @param string     $class  Button classes.
 * @param bool       $arrow  Whether the design shows an arrow on this button.
 * @param string     $extra  Extra attributes.
 */
function ewil_button( $link, $class, $arrow = true, $extra = '' ) {
	if ( empty( $link['url'] ) ) {
		return;
	}
	echo '<a class="' . esc_attr( $class ) . '" ' . ewil_link_attrs( $link ) . ( $extra ? ' ' . $extra : '' ) . '>' . esc_html( $link['title'] ) . ( $arrow ? ' <span class="arrow" aria-hidden="true">→</span>' : '' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Prints a Media Library image with the design's classes.
 *
 * @param int    $id    Attachment ID.
 * @param array  $attrs Extra attributes (class, style, fetchpriority, loading, alt).
 */
function ewil_img( $id, $attrs = array() ) {
	if ( ! $id ) {
		return;
	}
	if ( ! isset( $attrs['loading'] ) ) {
		$attrs['loading'] = false;
	}
	echo ewil_strip_dimensions( wp_get_attachment_image( (int) $id, 'full', false, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Removes the width/height attributes WordPress adds to images. The original
 * design's images have none, and the CSS sizes them; the attributes could
 * otherwise change how some images are sized.
 *
 * @param string $html Image HTML.
 */
function ewil_strip_dimensions( $html ) {
	return preg_replace( '/\s(width|height)="\d+"/', '', (string) $html );
}

/**
 * Wave edge between colour bands. The path is drawn by main.js, as in the original.
 *
 * @param int    $n     Seed.
 * @param bool   $top   Top edge (flipped).
 * @param string $style Inline style of the original.
 */
function ewil_edge( $n, $top = false, $style = '' ) {
	echo '<svg class="edge' . ( $top ? ' edge-top' : '' ) . '" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '><path class="f-surface" data-edge="' . (int) $n . '"' . ( $top ? ' data-flip="1"' : '' ) . '/></svg>';
}

/**
 * Background contour lines, drawn by main.js.
 *
 * @param int $count Number of lines.
 * @param int $amp   Amplitude.
 * @param int $seed  Seed.
 */
function ewil_contours( $count, $amp, $seed ) {
	echo '<svg class="contours" data-contours="' . (int) $count . '" data-amp="' . (int) $amp . '" data-seed="' . (int) $seed . '" aria-hidden="true"></svg>';
}

/**
 * Icon from the inline sprite.
 *
 * @param string $id Symbol id, e.g. i-cal.
 */
function ewil_icon( $id ) {
	return '<svg aria-hidden="true"><use href="#' . esc_attr( $id ) . '"/></svg>';
}

/**
 * Highlights of one group (why-attend or who-should-attend).
 *
 * @param string $group Term slug.
 * @return WP_Post[]
 */
function ewil_highlights( $group ) {
	return ewil_items(
		'ewil_highlight',
		array(
			'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => 'ewil_highlight_group',
					'field'    => 'slug',
					'terms'    => $group,
				),
			),
		)
	);
}

/**
 * Award key dates from Conference Settings.
 *
 * @return array<int,array{label:string,date:string,iso:string,note:string}>
 */
function ewil_award_dates() {
	$dates = array(
		array(
			'label' => __( 'Nominations open', 'ewil' ),
			'value' => ewil_setting( 'awards_open' ),
			'note'  => '',
		),
		array(
			'label' => __( 'Nominations close', 'ewil' ),
			'value' => ewil_setting( 'awards_close' ),
			'note'  => '',
		),
		array(
			'label' => __( 'Awards ceremony', 'ewil' ),
			'value' => ewil_setting( 'awards_ceremony' ),
			'note'  => ewil_setting( 'awards_ceremony_note' ),
		),
	);
	$out = array();
	foreach ( $dates as $d ) {
		if ( ! $d['value'] ) {
			continue;
		}
		$out[] = array(
			'label' => $d['label'],
			'date'  => ewil_date( $d['value'], 'j M Y' ),
			'iso'   => ewil_date( $d['value'], 'Y-m-d' ),
			'note'  => $d['note'],
		);
	}
	return $out;
}

/**
 * Prints award date list items.
 */
function ewil_award_date_items() {
	foreach ( ewil_award_dates() as $d ) {
		echo '<li><span class="dl">' . esc_html( $d['label'] ) . '</span><time class="dv" datetime="' . esc_attr( $d['iso'] ) . '">' . esc_html( $d['date'] ) . '</time>' . ( $d['note'] ? '<span class="dn">' . esc_html( $d['note'] ) . '</span>' : '' ) . '</li>';
	}
}

/**
 * The page heading for page heroes: the Page header field or the title.
 */
function ewil_page_heading() {
	return ewil_t( 'page_heading', get_the_title() );
}

/**
 * Award categories listed under their group headings (Individual Awards,
 * Education Business and Partnership Awards), numbered across both groups.
 *
 * @param WP_Post[] $categories Categories in display order.
 */
function ewil_award_category_lists( $categories ) {
	$groups = array(
		'individual' => __( 'Individual Awards', 'ewil' ),
		'business'   => __( 'Education Business and Partnership Awards', 'ewil' ),
	);
	$by = array();
	foreach ( $categories as $c ) {
		$g          = get_field( 'award_group', $c->ID ) ?: 'individual';
		$by[ $g ][] = $c;
	}
	$n = 0;
	foreach ( $groups as $key => $label ) {
		if ( empty( $by[ $key ] ) ) {
			continue;
		}
		echo '<p class="small" style="margin:var(--space-3) 0 var(--space-1);font-weight:600;color:var(--ink)">' . esc_html( $label ) . '</p>';
		echo '<ol class="small" start="' . ( $n + 1 ) . '" style="margin:0;padding-left:1.6em">';
		foreach ( $by[ $key ] as $c ) {
			$n++;
			echo '<li>' . esc_html( get_the_title( $c ) ) . '</li>';
		}
		echo '</ol>';
	}
}
