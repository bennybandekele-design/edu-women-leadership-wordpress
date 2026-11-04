<?php
/**
 * Document head, icon sprite, announcement bar and main navigation.
 */

defined( 'ABSPATH' ) || exit;

$ewil_logo_id  = (int) get_theme_mod( 'custom_logo' );
$ewil_logo_src = $ewil_logo_id ? wp_get_attachment_image_url( $ewil_logo_id, 'full' ) : get_template_directory_uri() . '/assets/img/edupulse-africa-logo.png';
$ewil_name     = ewil_setting( 'event_name' );
$ewil_year     = ewil_setting( 'event_year' );
$ewil_link     = ewil_setting( 'announce_link' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'template-parts/icons' ); ?>

<a class="skip" href="#main"><?php esc_html_e( 'Skip to content', 'ewil' ); ?></a>

<?php if ( ewil_setting( 'announce_show' ) ) : ?>
<div class="announce" role="region" aria-label="<?php esc_attr_e( 'Announcement', 'ewil' ); ?>">
  <span class="announce-msg"><span class="dot" aria-hidden="true"></span><span id="announce-text"><?php echo esc_html( ewil_setting( 'announce_text' ) ); ?></span></span>
  <?php if ( ! empty( $ewil_link['url'] ) ) : ?><a id="announce-link" <?php echo ewil_link_attrs( $ewil_link ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $ewil_link['title'] ); ?> →</a><?php else : ?><a id="announce-link" hidden></a><?php endif; ?>

</div>
<?php endif; ?>

<header class="nav">
  <nav class="nav-in" aria-label="<?php esc_attr_e( 'Main', 'ewil' ); ?>">
    <?php /* translators: 1: conference name, 2: year */ ?>
    <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%1$s Conference %2$s, home', 'ewil' ), $ewil_name, $ewil_year ) ); ?>">
      <img src="<?php echo esc_url( $ewil_logo_src ); ?>" alt="" width="52" height="52">
      <span class="brand-txt"><span class="brand-name"><?php echo esc_html( $ewil_name ); ?></span><span class="brand-sub"><?php /* translators: %s: year */ echo esc_html( sprintf( __( 'Conference %s', 'ewil' ), $ewil_year ) ); ?></span></span>
    </a>
    <?php $ewil_reg_item = ewil_register_item(); if ( $ewil_reg_item ) : ?><a class="btn btn-cta nav-reg" href="<?php echo esc_url( $ewil_reg_item->url ); ?>"><?php echo esc_html( $ewil_reg_item->title ); ?></a><?php endif; ?>

    <button class="burger" id="burger" hidden aria-expanded="false" aria-controls="menu" aria-label="<?php esc_attr_e( 'Open menu', 'ewil' ); ?>" data-label-open="<?php esc_attr_e( 'Open menu', 'ewil' ); ?>" data-label-close="<?php esc_attr_e( 'Close menu', 'ewil' ); ?>"><svg><use href="#i-menu"/></svg></button>
    <?php
    if ( ! ewil_menu( 'primary', '<ul class="menu" id="menu">%3$s</ul>' ) ) {
        echo '<ul class="menu" id="menu"></ul>';
    }
    ?>

  </nav>
</header>

<main id="main" tabindex="-1">
