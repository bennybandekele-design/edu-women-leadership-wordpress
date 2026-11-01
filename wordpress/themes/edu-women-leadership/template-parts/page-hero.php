<?php
/**
 * Standard page header band: eyebrow, heading, intro, optional button.
 * Args: after (string) extra HTML printed after the intro (e.g. the Register deadline line).
 */

defined( 'ABSPATH' ) || exit;

$ewil_eyebrow = ewil_t( 'page_eyebrow' );
$ewil_lede    = ewil_t( 'page_lede', $args['lede'] ?? '' );
$ewil_button  = ewil_t( 'page_button' );
?>
  <section class="band pagehero"><div class="wrap">
    <?php if ( $ewil_eyebrow ) : ?><p class="eyebrow"><?php echo ewil_x( $ewil_eyebrow ); ?></p><?php endif; ?>

    <h1 class="h-lg"><?php echo ewil_h( ewil_page_heading() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
    <?php if ( $ewil_lede ) : ?><p class="lede"><?php echo ewil_x( $ewil_lede ); ?></p><?php endif; ?>

    <?php echo $args['after'] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php if ( ! empty( $ewil_button['url'] ) ) : ?><div class="actions"><?php ewil_button( $ewil_button, 'btn btn-primary', false ); ?></div><?php endif; ?>

  </div></section>
