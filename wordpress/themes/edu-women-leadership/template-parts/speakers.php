<?php
/**
 * Speaker grid (homepage and Speakers page). Confirmed speakers in order;
 * empty places up to six show the "Announcing soon" placeholder, as in the
 * original design.
 *
 * Args: home (bool) only speakers marked for the homepage, max six; class (string).
 */

defined( 'ABSPATH' ) || exit;

$ewil_home     = ! empty( $args['home'] );
$ewil_speakers = ewil_items( 'ewil_speaker' );
if ( $ewil_home ) {
	$ewil_speakers = array_slice(
		array_values(
			array_filter(
				$ewil_speakers,
				function ( $s ) {
					$v = get_field( 'show_on_home', $s->ID );
					return null === $v || (bool) $v;
				}
			)
		),
		0,
		6
	);
}
$ewil_list = $ewil_speakers;
while ( count( $ewil_list ) < 6 ) {
	$ewil_list[] = null;
}
?>
<div class="<?php echo esc_attr( $args['class'] ?? 'spx' ); ?>"><?php
foreach ( $ewil_list as $ewil_i => $ewil_s ) {
	$ewil_edge = '<svg class="spk-edge" viewBox="0 0 400 34" preserveAspectRatio="none" aria-hidden="true" data-spk-edge="' . (int) $ewil_i . '"></svg>';
	$ewil_bg   = '<svg class="contours" data-contours="9" data-amp="18" data-seed="' . ( 20 + $ewil_i ) . '" aria-hidden="true"></svg><svg class="spk-sil" aria-hidden="true"><use href="#i-person"/></svg>';
	if ( $ewil_s ) {
		$ewil_name = get_the_title( $ewil_s );
		$ewil_role = implode( ', ', array_filter( array( get_field( 'role', $ewil_s->ID ), get_field( 'organisation', $ewil_s->ID ) ) ) );
		$ewil_sess = get_field( 'session', $ewil_s->ID );
		$ewil_bio  = get_field( 'bio', $ewil_s->ID );
		$ewil_li   = get_field( 'linkedin', $ewil_s->ID );
		$ewil_img  = has_post_thumbnail( $ewil_s ) ? ewil_strip_dimensions( get_the_post_thumbnail(
			$ewil_s,
			'large',
			array(
				/* translators: %s: speaker name */
				'alt'     => sprintf( __( 'Portrait of %s', 'ewil' ), $ewil_name ),
				'loading' => false,
			)
		) ) : $ewil_bg;
		echo '<article class="spk"><div class="spk-photo">' . $ewil_img // phpcs:ignore WordPress.Security.EscapeOutput
			. ( $ewil_bio ? '<div class="spk-bio">' . esc_html( $ewil_bio ) . '</div>' : '' )
			/* translators: %s: speaker name */
			. ( $ewil_li ? '<a class="spk-li" href="' . esc_url( $ewil_li ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( sprintf( __( '%s on LinkedIn', 'ewil' ), $ewil_name ) ) . '">in</a>' : '' )
			. $ewil_edge . '</div><div class="spk-body"><h3>' . esc_html( $ewil_name ) . '</h3><p class="spk-role">' . esc_html( $ewil_role ) . '</p>' // phpcs:ignore WordPress.Security.EscapeOutput
			. ( $ewil_sess ? '<p class="spk-sess"><span>' . esc_html__( 'Session', 'ewil' ) . '</span>' . esc_html( $ewil_sess ) . '</p>' : '' ) . '</div></article>';
	} else {
		echo '<article class="spk"><div class="spk-photo">' . $ewil_bg . '<span class="spk-soon"><span class="dot" aria-hidden="true"></span>' . esc_html__( 'Announcing soon', 'ewil' ) . '</span>' . $ewil_edge . '</div><div class="spk-body"><h3>' . esc_html__( 'Speaker to be announced', 'ewil' ) . '</h3><p class="spk-role">' . esc_html__( 'Name, role and session will appear here once confirmed.', 'ewil' ) . '</p></div></article>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
?></div>
