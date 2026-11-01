<?php
/**
 * Footer.
 */

defined( 'ABSPATH' ) || exit;

$ewil_social = array_filter(
	array(
		'LinkedIn'    => ewil_setting( 'social_linkedin' ),
		'Facebook'    => ewil_setting( 'social_facebook' ),
		'Instagram'   => ewil_setting( 'social_instagram' ),
		'X'           => ewil_setting( 'social_x' ),
		'YouTube'     => ewil_setting( 'social_youtube' ),
	)
);
$ewil_legal  = ewil_menu_links( 'footer_legal' );
$ewil_reg    = ewil_page_url( 'register' );
?>

</main>

<footer>
  <div class="band"><div class="wrap">
    <div class="foot-grid">
      <div style="display:grid; gap: var(--space-3); align-content: start">
        <h2><?php echo esc_html( trim( ewil_setting( 'event_name' ) . ' ' . sprintf( /* translators: %s: year */ __( 'Conference %s', 'ewil' ), ewil_setting( 'event_year' ) ) ) ); ?></h2>
        <p><?php echo esc_html( ewil_setting( 'date_text' ) . ' · ' . ewil_setting( 'venue' ) . ', ' . ewil_setting( 'city' ) ); ?></p>
        <p class="small"><?php /* translators: %s: host organisation */ echo esc_html( sprintf( __( 'Hosted by %s', 'ewil' ), ewil_setting( 'host' ) ) ); ?></p>
        <?php if ( $ewil_reg ) : ?><div class="actions" style="margin-top: var(--space-3)"><a class="btn btn-cta" href="<?php echo esc_url( $ewil_reg ); ?>" style="color: var(--on-cta)"><?php esc_html_e( 'Register now', 'ewil' ); ?></a></div><?php endif; ?>

      </div>
      <div><h3><?php echo esc_html( ewil_menu_name( 'footer_explore' ) ); ?></h3><?php ewil_menu( 'footer_explore', "<ul>\n        %3\$s\n      </ul>" ); ?></div>
      <div><h3><?php echo esc_html( ewil_menu_name( 'footer_part' ) ); ?></h3><?php ewil_menu( 'footer_part', "<ul>\n        %3\$s\n      </ul>" ); ?><div data-render="social" style="margin-top: var(--space-5)"><?php if ( $ewil_social ) : ?><h3><?php esc_html_e( 'Follow', 'ewil' ); ?></h3><ul><?php foreach ( $ewil_social as $ewil_label => $ewil_url ) : ?><li><a href="<?php echo esc_url( $ewil_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ewil_label ); ?></a></li><?php endforeach; ?></ul><?php endif; ?></div></div>
    </div>
    <div class="foot-base">
      <span>© <span id="year"><?php echo esc_html( wp_date( 'Y' ) ); ?></span> <?php echo esc_html( ewil_setting( 'host' ) ); ?></span>
      <span><?php echo implode( ' · ', array_map( fn( $l ) => '<a href="' . esc_url( $l[0] ) . '">' . esc_html( $l[1] ) . '</a>', $ewil_legal ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( current_user_can( 'edit_posts' ) ) : ?><span id="admin-link"> · <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ewil_submission' ) ); ?>"><?php esc_html_e( 'Submissions', 'ewil' ); ?></a></span><?php endif; ?></span>
    </div>
  </div></div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
