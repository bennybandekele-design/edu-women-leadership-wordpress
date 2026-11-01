<?php
/**
 * Registration rate cards (homepage and Register page).
 *
 * Args: class (string).
 */

defined( 'ABSPATH' ) || exit;

$ewil_reg = ewil_page_url( 'register' );
?>
<div class="<?php echo esc_attr( $args['class'] ?? 'prices' ); ?>"><?php
foreach ( ewil_items( 'ewil_rate' ) as $ewil_r ) {
	$ewil_id       = $ewil_r->ID;
	$ewil_featured = (bool) get_field( 'featured', $ewil_id );
	$ewil_badge    = get_field( 'badge', $ewil_id );
	$ewil_desc     = get_field( 'description', $ewil_id );
	$ewil_incl     = ewil_lines( get_field( 'includes', $ewil_id ) );
	$ewil_note     = get_field( 'note', $ewil_id );
	$ewil_href     = add_query_arg( 'rate', $ewil_r->post_name, $ewil_reg ) . '#reg-form';

	echo '<article class="price' . ( $ewil_featured ? ' featured' : '' ) . '">'
		. ( $ewil_badge ? '<span class="badge">' . esc_html( $ewil_badge ) . '</span>' : '' )
		. '<h3 class="h3">' . esc_html( get_the_title( $ewil_r ) ) . '</h3>'
		. '<p class="fig"><span class="amt">' . esc_html( get_field( 'price', $ewil_id ) ) . '</span><span class="unit">' . esc_html( ewil_field( 'unit', __( 'per delegate', 'ewil' ), $ewil_id ) ) . '</span></p>'
		. ( $ewil_desc ? '<p class="desc">' . esc_html( $ewil_desc ) . '</p>' : '' )
		. ( $ewil_incl ? '<ul>' . implode( '', array_map( fn( $x ) => '<li>' . esc_html( $x ) . '</li>', $ewil_incl ) ) . '</ul>' : '' )
		. ( $ewil_note ? '<p class="note">' . esc_html( $ewil_note ) . '</p>' : '' )
		. '<a class="btn ' . ( $ewil_featured ? 'btn-cta' : 'btn-primary' ) . '" href="' . esc_url( $ewil_href ) . '" data-scroll="reg-form" data-rate="' . esc_attr( $ewil_r->post_name ) . '">' . esc_html( ewil_field( 'button_label', __( 'Register', 'ewil' ), $ewil_id ) ) . ' <span class="arrow" aria-hidden="true">→</span></a>'
		. '</article>'; // phpcs:ignore WordPress.Security.EscapeOutput
}
?></div>
