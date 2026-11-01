<?php
/**
 * Template Name: Programme
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

$ewil_boxes = array(
	1 => array( 'Day 1', 'Friday 27 August 2027', 'Sessions to be announced' ),
	2 => array( 'Day 2', 'Saturday 28 August 2027', 'Sessions and the Edu Women in Leadership Awards (time to be confirmed)' ),
	3 => array( 'Venue', '{venue}, {city}', 'Room details to be confirmed' ),
);
?>
<div class="page" data-route="programme">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
  <section class="band"><div class="wrap">
    <dl class="facts grid" style="margin-bottom: var(--space-7)">
      <?php
      foreach ( $ewil_boxes as $ewil_i => $ewil_d ) {
          $ewil_b = ewil_t( 'box_' . $ewil_i, array() );
          echo '<div class="factbox"><dt>' . ewil_x( $ewil_b['label'] ?? '' ?: $ewil_d[0] ) . '</dt><dd>' . ewil_x( $ewil_b['value'] ?? '' ?: $ewil_d[1] ) . '</dd><p class="small">' . ewil_x( $ewil_b['note'] ?? '' ?: $ewil_d[2] ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
          echo "\n      ";
      }
      ?>

    </dl>
    <div class="head"><p class="eyebrow"><?php echo ewil_x( ewil_t( 'tracks_eyebrow', 'Conference tracks' ) ); ?></p><h2 class="h1"><?php echo ewil_h( ewil_t( 'tracks_heading', 'Themes for {year}' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2></div>
    <div class="grid"><?php foreach ( ewil_items( 'ewil_track' ) as $ewil_i => $ewil_t ) : ?><article class="card"><svg class="pulse" aria-hidden="true"><use href="#i-pulse"/></svg><span class="num"><?php /* translators: %s: track number with leading zero */ echo esc_html( sprintf( __( 'TRACK %s', 'ewil' ), str_pad( (string) ( $ewil_i + 1 ), 2, '0', STR_PAD_LEFT ) ) ); ?></span><h3 class="h3"><?php echo esc_html( get_the_title( $ewil_t ) ); ?></h3><p><?php echo esc_html( ewil_field( 'description', '', $ewil_t->ID ) ); ?></p><p class="small foot"><?php esc_html_e( 'Sessions to be announced', 'ewil' ); ?></p></article><?php endforeach; ?></div>
    <?php $ewil_btn = ewil_t( 'prog_button' ); if ( ! empty( $ewil_btn['url'] ) ) : ?><div class="actions" style="margin-top: var(--space-7)"><?php ewil_button( $ewil_btn, 'btn btn-primary' ); ?></div><?php endif; ?>

  </div></section>
</div>
<?php
get_footer();
