<?php
/**
 * Fallback for any view without a more specific template (for example news
 * posts, if they are used later): a page header and a simple list.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="page">
  <section class="band pagehero"><div class="wrap">
    <h1 class="h-lg"><?php echo esc_html( is_singular() ? get_the_title() : wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
  </div></section>
  <section class="band"><div class="wrap prose">
	<?php
	if ( is_singular() ) {
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	} elseif ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			echo '<h2 class="h3"><a class="link" href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
			echo '<p class="small">' . esc_html( get_the_excerpt() ) . '</p>';
		}
		the_posts_pagination();
	} else {
		echo '<p>' . esc_html__( 'Nothing here yet.', 'ewil' ) . '</p>';
	}
	?>
  </div></section>
</div>
<?php
get_footer();
