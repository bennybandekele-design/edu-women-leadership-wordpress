<?php
/**
 * Template Name: Contact
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

$ewil_rows = array_filter(
	array(
		__( 'Email', 'ewil' )   => ewil_setting( 'contact_email' ),
		__( 'Phone', 'ewil' )   => ewil_setting( 'contact_phone' ),
		__( 'Address', 'ewil' ) => ewil_setting( 'contact_address' ),
	)
);
?>
<div class="page" data-route="contact">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
  <section class="band"><div class="wrap formgrid">
    <aside class="factbox"><h2 class="h3"><?php echo ewil_x( ewil_t( 'box_heading', ewil_setting( 'host' ) ) ); ?></h2><?php if ( $ewil_rows ) : ?><dl class="facts"><?php foreach ( $ewil_rows as $ewil_label => $ewil_value ) : ?><div><dt><?php echo esc_html( $ewil_label ); ?></dt><dd class="copyrow"><code><?php echo esc_html( $ewil_value ); ?></code><button class="tab" type="button" data-copy="<?php echo esc_attr( $ewil_value ); ?>" data-copied="<?php esc_attr_e( 'Copied', 'ewil' ); ?>"><?php esc_html_e( 'Copy', 'ewil' ); ?></button></dd></div><?php endforeach; ?></dl><?php else : ?><p class="small"><?php esc_html_e( 'Direct contact details will be published soon. In the meantime, the form reaches the conference team.', 'ewil' ); ?></p><?php endif; ?><dl class="facts"><div><dt><?php esc_html_e( 'Conference', 'ewil' ); ?></dt><dd><?php echo esc_html( ewil_setting( 'date_text' ) ); ?></dd></div><div><dt><?php esc_html_e( 'Venue', 'ewil' ); ?></dt><dd><?php echo esc_html( ewil_setting( 'venue' ) . ', ' . ewil_setting( 'city' ) ); ?></dd></div></dl></aside>
    <div class="panel"><div data-form="contact"><?php ewil_form( 'contact' ); ?></div></div>
  </div></section>
</div>
<?php
get_footer();
