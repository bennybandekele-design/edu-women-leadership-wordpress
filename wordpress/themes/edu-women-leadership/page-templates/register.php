<?php
/**
 * Template Name: Register
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

/* translators: %s: early-bird deadline */
$ewil_deadline = '<p class="deadline"><b>' . esc_html__( 'Early bird', 'ewil' ) . '</b> ' . esc_html( sprintf( __( 'Register by %s', 'ewil' ), ewil_setting( 'early_bird_deadline' ) ) ) . '</p>';
?>
<div class="page" data-route="register">
	<?php
	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'lede'  => '{dates} · {venue}, {city}',
			'after' => $ewil_deadline,
		)
	);
	?>
  <section class="band"><div class="wrap">
    <?php get_template_part( 'template-parts/prices', null, array( 'class' => 'prices' ) ); ?>

  </div></section>
  <section class="band tint" style="padding-top: var(--space-7)"><div class="wrap" style="max-width: 860px">
    <div class="panel" id="reg-form">
      <div class="head" style="margin-bottom: var(--space-5)"><h2 class="h2"><?php echo ewil_x( ewil_t( 'form_heading', 'Your registration' ) ); ?></h2><p class="small"><?php echo esc_html( ewil_setting( 'payment_note' ) ); ?></p></div>
      <div data-form="registration"><?php ewil_form( 'registration' ); ?></div>
    </div>
  </div></section>
</div>
<?php
get_footer();
