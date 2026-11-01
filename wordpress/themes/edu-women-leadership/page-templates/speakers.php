<?php
/**
 * Template Name: Speakers
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();
?>
<div class="page" data-route="speakers">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
  <section class="band"><div class="wrap">
    <?php get_template_part( 'template-parts/speakers', null, array( 'class' => 'spx' ) ); ?>

  </div></section>
  <section class="band tint"><div class="wrap" style="max-width: 720px">
    <div class="panel">
      <div class="head" style="margin-bottom: var(--space-5)"><p class="eyebrow"><?php echo ewil_x( ewil_t( 'nl_eyebrow', 'Get announcements' ) ); ?></p><h2 class="h2"><?php echo ewil_h( ewil_t( 'nl_heading', 'Be first to hear who is speaking' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2></div>
      <div data-form="newsletter"><?php ewil_form( 'newsletter' ); ?></div>
    </div>
  </div></section>
</div>
<?php
get_footer();
