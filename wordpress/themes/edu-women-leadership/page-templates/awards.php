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

    </div>
    <ul class="dates"><?php ewil_award_date_items(); ?></ul>
  </div></section>
  <section class="band"><div class="wrap formgrid">
    <aside>
      <div class="factbox"><h2 class="h3"><?php echo ewil_x( ewil_t( 'cat_heading', 'Award categories' ) ); ?></h2><div><?php if ( $ewil_categories ) : ?><ul class="small" style="margin:0;padding-left:1.2em"><?php foreach ( $ewil_categories as $ewil_c ) : ?><li><?php echo esc_html( get_the_title( $ewil_c ) ); ?></li><?php endforeach; ?></ul><?php else : ?><p class="small"><?php esc_html_e( 'Award categories will be announced when nominations open.', 'ewil' ); ?></p><?php endif; ?></div></div>
      <div class="factbox"><h2 class="h3"><?php echo ewil_x( ewil_t( 'elig_heading', 'Eligibility' ) ); ?></h2><p class="small"><?php echo esc_html( ewil_setting( 'awards_eligibility' ) ?: $ewil_fallbacks['eligibility'] ); ?></p></div>
      <div class="factbox"><h2 class="h3"><?php echo ewil_x( ewil_t( 'judg_heading', 'Judging' ) ); ?></h2><p class="small"><?php echo esc_html( ewil_setting( 'awards_judging' ) ?: $ewil_fallbacks['judging'] ); ?></p></div>
    </aside>
    <div class="panel" id="nominate">
      <div class="head" style="margin-bottom: var(--space-5)"><h2 class="h2"><?php echo ewil_x( ewil_t( 'form_heading', 'Nominate a leader' ) ); ?></h2><p class="small"><?php echo esc_html( ewil_setting( 'awards_guidelines' ) ?: $ewil_fallbacks['guidelines'] ); ?></p></div>
      <div data-form="nomination"><?php ewil_form( 'nomination' ); ?></div>
    </div>
  </div></section>
</div>
<?php
get_footer();
