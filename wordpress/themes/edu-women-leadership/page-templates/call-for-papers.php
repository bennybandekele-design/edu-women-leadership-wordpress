<?php
/**
 * Template Name: Call for Papers
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();
?>
<div class="page" data-route="call-for-papers">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
  <section class="band"><div class="wrap formgrid">
    <aside>
      <div class="factbox">
        <h2 class="h3"><?php echo ewil_x( ewil_t( 'glance_heading', 'At a glance' ) ); ?></h2>
        <dl class="facts">
          <div><dt><?php esc_html_e( 'Deadline', 'ewil' ); ?></dt><dd class="tba"><?php echo esc_html( ewil_setting( 'cfp_deadline' ) ?: __( 'To be announced', 'ewil' ) ); ?></dd></div>
          <div><dt><?php esc_html_e( 'Abstract', 'ewil' ); ?></dt><dd><?php echo ewil_x( '{abstract}' ); ?></dd></div>
          <div><dt><?php esc_html_e( 'Biography', 'ewil' ); ?></dt><dd><?php echo ewil_x( '{bio}' ); ?></dd></div>
          <div><dt><?php esc_html_e( 'Supporting document', 'ewil' ); ?></dt><dd><?php /* translators: 1: file types, 2: size in MB */ echo esc_html( sprintf( __( 'Optional · %1$s, up to %2$s MB', 'ewil' ), ewil_setting( 'cfp_file_label' ), ewil_setting( 'cfp_max_mb' ) ) ); ?></dd></div>
        </dl>
      </div>
      <div class="prose">
        <h2 class="h3"><?php echo ewil_x( ewil_t( 'guide_heading', 'What makes a strong proposal' ) ); ?></h2>
        <?php the_content(); ?>
      </div>
    </aside>
    <div class="panel" id="cfp-form">
      <div class="head" style="margin-bottom: var(--space-5)"><h2 class="h2"><?php echo ewil_x( ewil_t( 'form_heading', 'Submit your proposal' ) ); ?></h2><p class="small"><?php echo wp_kses( __( 'Fields marked <span class="req">*</span> are required.', 'ewil' ), array( 'span' => array( 'class' => true ) ) ); ?></p></div>
      <div data-form="paper"><?php ewil_form( 'paper' ); ?></div>
    </div>
  </div></section>
</div>
<?php
get_footer();
