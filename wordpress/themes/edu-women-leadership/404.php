<?php
/**
 * Page not found, in the site's page style.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="page">
  <section class="band pagehero"><div class="wrap">
    <p class="eyebrow"><?php esc_html_e( 'Page not found', 'ewil' ); ?></p>
    <h1 class="h-lg"><?php esc_html_e( 'This page has moved or no longer exists', 'ewil' ); ?></h1>
    <p class="lede"><?php esc_html_e( 'Try the homepage, or use the menu above to find what you need.', 'ewil' ); ?></p>
    <div class="actions"><a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the homepage', 'ewil' ); ?> <span class="arrow" aria-hidden="true">→</span></a></div>
  </div></section>
</div>
<?php
get_footer();
