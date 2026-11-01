<?php
/**
 * Template Name: Partners
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();
?>
<div class="page" data-route="partners">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
  <section class="band"><div class="wrap">
    <div class="head"><p class="eyebrow"><?php echo ewil_x( ewil_t( 'opp_eyebrow', 'Opportunities' ) ); ?></p><h2 class="h1"><?php echo ewil_h( ewil_t( 'opp_heading', 'Ways to get involved' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2></div>
    <div class="grid"><?php foreach ( ewil_items( 'ewil_opportunity' ) as $ewil_o ) : $ewil_title = $ewil_o->post_title; ?><article class="card"><h3 class="h3"><?php echo esc_html( $ewil_title ); ?></h3><p><?php echo esc_html( ewil_field( 'description', '', $ewil_o->ID ) ); ?></p><div class="foot"><a class="link" href="#partner-form" data-scroll="partner-form" data-interest="<?php echo esc_attr( $ewil_title ); ?>"><?php esc_html_e( 'Enquire', 'ewil' ); ?> <span class="arrow" aria-hidden="true">→</span></a></div></article><?php endforeach; ?></div>
  </div></section>
  <section class="band tint"><div class="wrap formgrid">
    <aside class="prose">
      <h2 class="h1"><?php echo ewil_h( ewil_t( 'conv_heading', 'Start a conversation' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
      <?php the_content(); ?>
    </aside>
    <div class="panel" id="partner-form"><div data-form="partnership"><?php ewil_form( 'partnership' ); ?></div></div>
  </div></section>
</div>
<?php
get_footer();
