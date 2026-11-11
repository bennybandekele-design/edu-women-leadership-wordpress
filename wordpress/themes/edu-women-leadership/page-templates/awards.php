<?php
/**
 * Template Name: Awards
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

$ewil_categories = ewil_items( 'ewil_award_category' );
$ewil_fallbacks  = array(
	'eligibility' => __( 'Eligibility criteria will be published when nominations open.', 'ewil' ),
	'judging'     => __( 'Information on the judging process will be published soon.', 'ewil' ),
	'guidelines'  => __( 'Tell us about a woman whose leadership is transforming education.', 'ewil' ),
);
?>
<div class="page" data-route="awards">
  <section class="band dark"><div class="wrap" style="display:grid; gap: var(--space-6)">
    <div class="head" style="margin:0">
      <?php if ( ewil_t( 'page_eyebrow' ) ) : ?><p class="eyebrow"><?php echo ewil_x( ewil_t( 'page_eyebrow' ) ); ?></p><?php endif; ?>

      <h1 class="h-lg"><?php echo ewil_h( ewil_page_heading() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
      <?php if ( ewil_t( 'page_lede' ) ) : ?><p class="lede"><?php echo ewil_x( ewil_t( 'page_lede' ) ); ?></p><?php endif; ?>

      <?php $ewil_btn = ewil_t( 'page_button' ); if ( ! empty( $ewil_btn['url'] ) ) : ?><div class="actions" style="margin-top: var(--space-5)"><?php ewil_button( $ewil_btn, 'btn btn-inverse', false ); ?></div><?php endif; ?>

    </div>
    <ul class="dates"><?php ewil_award_date_items(); ?></ul>
  </div></section>
  <section class="band"><div class="wrap formgrid">
    <aside>
      <div class="factbox"><h2 class="h3"><?php echo ewil_x( ewil_t( 'cat_heading', 'Award categories' ) ); ?></h2><div><?php if ( $ewil_categories ) : ?><?php ewil_award_category_lists( $ewil_categories ); ?><?php else : ?><p class="small"><?php esc_html_e( 'Award categories will be announced when nominations open.', 'ewil' ); ?></p><?php endif; ?></div></div>
      <div class="factbox"><h2 class="h3"><?php echo ewil_x( ewil_t( 'elig_heading', 'Eligibility' ) ); ?></h2><p class="small"><?php echo nl2br( esc_html( ewil_setting( 'awards_eligibility' ) ?: $ewil_fallbacks['eligibility'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p></div>
      <div class="factbox"><h2 class="h3"><?php echo ewil_x( ewil_t( 'judg_heading', 'Judging' ) ); ?></h2><p class="small"><?php echo nl2br( esc_html( ewil_setting( 'awards_judging' ) ?: $ewil_fallbacks['judging'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p></div>
    </aside>
    <div class="panel" id="nominate">
      <div class="head" style="margin-bottom: var(--space-5)"><h2 class="h2"><?php echo ewil_x( ewil_t( 'form_heading', 'Nominate a leader' ) ); ?></h2><p class="small"><?php echo nl2br( esc_html( ewil_setting( 'awards_guidelines' ) ?: $ewil_fallbacks['guidelines'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p></div>
      <div data-form="nomination"><?php ewil_form( 'nomination' ); ?></div>
    </div>
  </div></section>
	<?php
	$ewil_photos = array_filter( array_map( fn( $i ) => (int) ewil_t( 'gallery_photo_' . $i, 0 ), range( 1, 8 ) ) );
	if ( $ewil_photos ) :
		$ewil_g_eyebrow = ewil_t( 'gallery_eyebrow' );
		$ewil_g_lede    = ewil_t( 'gallery_lede' );
		?>
  <section class="band tint" aria-labelledby="aw-gallery-title"><div class="wrap">
    <div class="head rv">
      <?php if ( $ewil_g_eyebrow ) : ?><p class="eyebrow"><?php echo ewil_x( $ewil_g_eyebrow ); ?></p><?php endif; ?>

      <h2 class="h1 hn" id="aw-gallery-title"><?php echo ewil_h( ewil_t( 'gallery_heading', 'Celebrating *bold leaders*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
      <?php if ( $ewil_g_lede ) : ?><p class="lede"><?php echo ewil_x( $ewil_g_lede ); ?></p><?php endif; ?>

    </div>
    <ul class="aw-gallery rv"><?php
    foreach ( $ewil_photos as $ewil_pid ) {
        $ewil_meta = wp_get_attachment_metadata( $ewil_pid );
        $ewil_wide = ! empty( $ewil_meta['width'] ) && $ewil_meta['width'] > $ewil_meta['height'];
        echo '<li' . ( $ewil_wide ? ' class="wide"' : '' ) . '>' . ewil_strip_dimensions( wp_get_attachment_image( $ewil_pid, 'large', false, array( 'loading' => 'lazy', 'sizes' => $ewil_wide ? '(max-width: 560px) 100vw, 400px' : '(max-width: 560px) 50vw, 200px' ) ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput
    }
    ?></ul>
  </div></section>
	<?php endif; ?>
</div>
<?php
get_footer();
