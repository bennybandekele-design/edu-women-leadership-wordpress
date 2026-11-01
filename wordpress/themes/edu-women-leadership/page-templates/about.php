<?php
/**
 * Template Name: About
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();
?>
<div class="page" data-route="about">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
  <section class="band"><div class="wrap feature">
    <?php ewil_img( ewil_t( 'about_image' ), array( 'loading' => 'lazy' ) ); ?>

    <div class="prose">
      <h2 class="h1"><?php echo ewil_h( ewil_t( 'about_heading', 'Why we gather' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
      <?php the_content(); ?>
      <dl class="facts" style="margin-top: var(--space-3)">
        <div><dt><?php esc_html_e( 'Dates', 'ewil' ); ?></dt><dd><?php echo esc_html( ewil_setting( 'date_text' ) ); ?></dd></div>
        <div><dt><?php esc_html_e( 'Venue', 'ewil' ); ?></dt><dd><?php echo esc_html( ewil_setting( 'venue' ) . ', ' . ewil_setting( 'city' ) ); ?></dd></div>
        <div><dt><?php esc_html_e( 'Host', 'ewil' ); ?></dt><dd><?php echo esc_html( ewil_setting( 'host' ) ); ?></dd></div>
      </dl>
      <div class="actions" style="margin-top: var(--space-3)"><?php ewil_button( ewil_t( 'about_button_1' ), 'btn btn-cta' ); ewil_button( ewil_t( 'about_button_2' ), 'btn btn-secondary', false ); ?></div>
    </div>
  </div></section>
  <section class="band tint"><div class="wrap">
    <div class="head"><p class="eyebrow"><?php echo ewil_x( ewil_t( 'aud_eyebrow', 'Who should attend' ) ); ?></p><h2 class="h1"><?php echo ewil_h( ewil_t( 'aud_heading', 'Built for leaders at every level' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2></div>
    <ul class="aud"><?php foreach ( ewil_highlights( 'who-should-attend' ) as $ewil_a ) : ?><li><span class="ico"><?php echo ewil_icon( ewil_field( 'icon', 'i-school', $ewil_a->ID ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span><?php echo esc_html( get_the_title( $ewil_a ) ); ?></span></li><?php endforeach; ?></ul>
  </div></section>
</div>
<?php
get_footer();
