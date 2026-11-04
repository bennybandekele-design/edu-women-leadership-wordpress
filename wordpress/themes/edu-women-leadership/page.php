<?php
/**
 * Default page: page header and the editor text (Privacy policy, Website terms
 * and any new page).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
<div class="page" data-route="<?php echo esc_attr( get_post_field( 'post_name' ) ); ?>">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
  <section class="band"><div class="wrap prose"><?php the_content(); ?></div></section>
</div>
	<?php
endwhile;

get_footer();
