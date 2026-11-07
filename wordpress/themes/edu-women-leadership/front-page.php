<?php
/**
 * Homepage. Markup copied from the original static site; the text, photos and
 * buttons come from the "Homepage sections" fields, the lists from the
 * conference post types, and dates and venue from Conference Settings.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ewil_year     = ewil_setting( 'event_year' );
$ewil_tracks   = ewil_items( 'ewil_track' );
$ewil_benefits = ewil_highlights( 'why-attend' );
$ewil_audience = ewil_highlights( 'who-should-attend' );
$ewil_opps     = ewil_items( 'ewil_opportunity' );
$ewil_fact     = function ( $i, $value, $label ) {
	$f = ewil_t( 'fact_' . $i, array() );
	return array( ewil_x( ! empty( $f['value'] ) ? $f['value'] : $value ), ewil_x( ! empty( $f['label'] ) ? $f['label'] : $label ) );
};
$ewil_programme = ewil_page_url( 'programme' );
?>

<div class="page" data-route="home">

  <section class="hx gut" aria-labelledby="hero-title">
    <?php ewil_contours( 14, 34, 1 ); ?>

    <div class="hx-in">
      <div class="hx-copy">
        <span class="kicker"><svg viewBox="0 0 84 24" aria-hidden="true"><use href="#i-pulse"/></svg><?php echo ewil_x( ewil_t( 'hero_kicker', 'EduPulse Africa presents' ) ); ?></span>
        <h1 class="hn" id="hero-title"><?php echo ewil_x( ewil_t( 'hero_title', ewil_setting( 'event_name' ) ) ); ?> <span class="yr"><?php echo ewil_x( ewil_t( 'hero_title_sub', 'CONFERENCE {year}' ) ); ?></span></h1>
        <p class="hx-theme"><?php echo ewil_h( ewil_setting( 'event_theme' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
        <p class="hx-lede"><?php echo ewil_x( ewil_t( 'hero_lede', 'Join a dynamic community of women education leaders, school principals, teachers, researchers, policymakers, and innovators working together to shape the future of education in Africa.' ) ); ?></p>
        <div class="actions">
          <?php ewil_button( ewil_t( 'hero_button_1' ), 'btn btn-cta btn-lg' ); ?>

          <?php ewil_button( ewil_t( 'hero_button_2' ), 'btn btn-inverse btn-lg', false ); ?>

          <?php ewil_button( ewil_t( 'hero_button_3' ), 'btn btn-ghost' ); ?>

        </div>
        <div class="hx-meta">
          <span><?php echo ewil_icon( 'i-cal' ) . esc_html( ewil_setting( 'date_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
          <span><?php echo ewil_icon( 'i-pin' ) . esc_html( ewil_setting( 'venue' ) . ', ' . ewil_setting( 'city' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
        </div>
      </div>
      <div class="hx-visual">
        <div class="hx-photo">
          <?php ewil_img( ewil_t( 'hero_image' ), array( 'class' => 'wave-mask-l', 'fetchpriority' => 'high' ) ); ?>

          <svg class="pulse-line" viewBox="0 0 400 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0 30 H170 l12 -24 l16 48 l14 -36 l10 12 H400"/></svg>
        </div>
        <div class="hx-inset"><?php ewil_img( ewil_t( 'hero_image_inset' ) ); ?></div>
        <div class="hx-tag"><b><?php echo esc_html( ewil_setting( 'date_short' ) ); ?></b><span><?php echo esc_html( ewil_setting( 'city' ) . ', ' . $ewil_year ); ?></span></div>
      </div>
    </div>
    <?php if ( ewil_setting( 'photo_credit' ) ) : ?><span class="hx-note"><?php echo esc_html( ewil_setting( 'photo_credit' ) ); ?></span><?php endif; ?>

    <?php ewil_edge( 1, false, 'position:relative' ); ?>

  </section>

  <div class="cdx gut">
    <div class="cdx-card">
      <div class="cdx-lab"><p class="eyebrow"><?php echo ewil_x( ewil_t( 'cd_eyebrow', 'The conference opens in' ) ); ?></p><strong><?php echo ewil_x( ewil_t( 'cd_label', '{city}, 27 August {year}' ) ); ?></strong></div>
      <div data-countdown data-start="<?php echo esc_attr( ewil_iso( ewil_setting( 'event_start' ) ) ); ?>" data-end="<?php echo esc_attr( ewil_iso( ewil_setting( 'event_end' ) ) ); ?>" data-msg-live="<?php esc_attr_e( 'The conference is under way', 'ewil' ); ?>" data-msg-after="<?php /* translators: %s: city */ echo esc_attr( sprintf( __( 'Thank you for joining us in %s', 'ewil' ), ewil_setting( 'city' ) ) ); ?>" data-labels="<?php esc_attr_e( 'Days,Hours,Minutes,Seconds', 'ewil' ); ?>"></div>
      <div class="cdx-eb"><b><?php echo ewil_x( ewil_t( 'cd_eb_title', 'Early bird' ) ); ?></b><span><?php echo ewil_x( ewil_t( 'cd_eb_text', 'Register by {early_bird}' ) ); ?></span><?php $ewil_l = ewil_t( 'cd_eb_link' ); if ( ! empty( $ewil_l['url'] ) ) : ?><a <?php echo ewil_link_attrs( $ewil_l ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $ewil_l['title'] ); ?> →</a><?php endif; ?></div>
    </div>
  </div>

  <div class="gut">
    <div class="facts-rb">
      <?php
      $ewil_facts = array(
          $ewil_fact( 1, '2 days', '{dates}' ),
          $ewil_fact( 2, '{tracks} tracks', 'Themes for {year}' ),
          $ewil_fact( 3, '{city}', '{venue}' ),
          $ewil_fact( 4, 'Awards', 'Ceremony 28 August' ),
      );
      foreach ( $ewil_facts as $ewil_f ) {
          echo '<div><b>' . $ewil_f[0] . '</b><span>' . $ewil_f[1] . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
      }
      ?>

    </div>
  </div>

  <section class="band" aria-labelledby="why-title">
    <div class="wrap wa">
      <div class="wa-side rv">
        <p class="eyebrow"><?php echo ewil_x( ewil_t( 'why_eyebrow', 'Why attend' ) ); ?></p>
        <h2 class="h1 hn" id="why-title"><?php echo ewil_h( ewil_t( 'why_heading', 'Lead boldly. Connect meaningfully. *Transform education.*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
        <p class="lede"><?php echo ewil_x( ewil_t( 'why_lede', 'Two days built for women who lead in schools, systems and communities, and for those preparing to.' ) ); ?></p>
        <?php ewil_img( ewil_t( 'why_image' ), array( 'class' => 'wave-mask-b' ) ); ?>

      </div>
      <ul class="wa-list rv"><?php foreach ( $ewil_benefits as $ewil_b ) : ?><li><span class="ic"><?php echo ewil_icon( ewil_field( 'icon', 'i-school', $ewil_b->ID ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><div><h3><?php echo esc_html( get_the_title( $ewil_b ) ); ?></h3><p><?php echo esc_html( ewil_field( 'description', '', $ewil_b->ID ) ); ?></p></div></li><?php endforeach; ?></ul>
    </div>
  </section>

  <section class="who gut" aria-labelledby="who-title">
    <?php ewil_edge( 2, true ); ?>

    <?php ewil_contours( 10, 26, 4 ); ?>

    <div class="who-in" style="padding-block: var(--space-8)">
      <div class="rv">
        <p class="eyebrow"><?php echo ewil_x( ewil_t( 'who_eyebrow', 'Who should attend' ) ); ?></p>
        <h2 class="h1 hn" id="who-title"><?php echo ewil_h( ewil_t( 'who_heading', 'For everyone shaping how *Africa learns*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
        <p class="lede" style="margin-top: var(--space-4)"><?php echo ewil_x( ewil_t( 'who_lede', 'From classroom to cabinet, from start-up to university.' ) ); ?></p>
      </div>
      <ul class="pills rv"><?php foreach ( $ewil_audience as $ewil_a ) : ?><li><span class="ic"><?php echo ewil_icon( ewil_field( 'icon', 'i-school', $ewil_a->ID ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php echo esc_html( get_the_title( $ewil_a ) ); ?></li><?php endforeach; ?></ul>
    </div>
    <?php ewil_edge( 3 ); ?>

  </section>

  <section class="band" aria-labelledby="tracks-title">
    <div class="wrap">
      <div class="head rv">
        <p class="eyebrow"><?php echo ewil_x( ewil_t( 'tracks_eyebrow', 'Conference tracks' ) ); ?></p>
        <h2 class="h1 hn" id="tracks-title"><?php echo ewil_h( ewil_t( 'tracks_heading', 'Six themes for the *{year} conversation*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
        <p class="lede"><?php echo ewil_x( ewil_t( 'tracks_lede', 'These thematic areas shape the programme. Sessions under each track will be published as the programme is confirmed.' ) ); ?></p>
      </div>
      <div class="tk-grid rv"><?php foreach ( $ewil_tracks as $ewil_i => $ewil_tr ) : ?><a class="tk" href="<?php echo esc_url( $ewil_programme ); ?>"><div class="tk-band"><svg viewBox="0 0 300 96" preserveAspectRatio="none" aria-hidden="true" data-tk="<?php echo (int) $ewil_i; ?>"></svg><span class="tk-n"><?php echo (int) $ewil_i + 1; ?></span></div><div class="tk-body"><span class="lbl"><?php /* translators: %d: track number */ echo esc_html( sprintf( __( 'Track %d', 'ewil' ), $ewil_i + 1 ) ); ?></span><h3><?php echo esc_html( get_the_title( $ewil_tr ) ); ?></h3><p><?php echo esc_html( ewil_field( 'description', '', $ewil_tr->ID ) ); ?></p></div></a><?php endforeach; ?></div>
    </div>
  </section>

  <section class="cfpx gut" aria-labelledby="cfp-title">
    <?php ewil_edge( 4, true ); ?>

    <div class="cfpx-in" style="padding-block: var(--space-8)">
      <div style="display:grid; gap: var(--space-5)" class="rv">
        <p class="eyebrow"><?php echo ewil_x( ewil_t( 'cfp_eyebrow', 'Call for Papers' ) ); ?></p>
        <h2 class="h-lg hn" id="cfp-title"><?php echo ewil_h( ewil_t( 'cfp_heading', 'Your ideas could shape the *future of education.*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
        <p class="lede"><?php echo ewil_x( ewil_t( 'cfp_lede', 'We invite education leaders, researchers, practitioners, academics, and innovators to share research, practical experiences, bold ideas, and solutions that can strengthen education across Africa.' ) ); ?></p>
        <ol class="cfpx-steps">
          <li><span><?php echo ewil_x( ewil_t( 'cfp_step_1', 'Choose the conference track your work speaks to.' ) ); ?></span></li>
          <li><span><?php echo ewil_x( ewil_t( 'cfp_step_2', 'Write an abstract of {abstract} and a short biography.' ) ); ?></span></li>
          <li><span><?php echo ewil_x( ewil_t( 'cfp_step_3', 'Submit online, with an optional supporting document.' ) ); ?></span></li>
        </ol>
        <p><span class="chip"><?php echo ewil_x( ewil_t( 'cfp_deadline_label', 'Deadline' ) ); ?> <b><?php echo esc_html( ewil_setting( 'cfp_deadline' ) ?: __( 'To be announced', 'ewil' ) ); ?></b></span></p>
        <div class="actions">
          <?php ewil_button( ewil_t( 'cfp_button_1' ), 'btn btn-primary btn-lg' ); ?>

          <?php ewil_button( ewil_t( 'cfp_button_2' ), 'btn btn-secondary btn-lg', false ); ?>

          <?php ewil_button( ewil_t( 'cfp_button_3' ), 'btn btn-ghost', false ); ?>

        </div>
      </div>
      <div class="cfpx-photo rv">
        <?php ewil_img( ewil_t( 'cfp_image' ), array( 'class' => 'wave-mask-l' ) ); ?>

      </div>
    </div>
    <?php ewil_edge( 5 ); ?>

  </section>

  <section class="band" aria-labelledby="spk-title">
    <div class="wrap">
      <div class="spx-head rv">
        <div class="head">
          <p class="eyebrow"><?php echo ewil_x( ewil_t( 'spk_eyebrow', 'Featured speakers' ) ); ?></p>
          <h2 class="h1 hn" id="spk-title"><?php echo ewil_h( ewil_t( 'spk_heading', 'Voices of *bold leadership*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
          <p class="lede"><?php echo ewil_x( ewil_t( 'spk_lede', 'The {year} line-up is being finalised. Speakers will appear here as they are confirmed.' ) ); ?></p>
        </div>
        <div class="actions"><?php ewil_button( ewil_t( 'spk_button_1' ), 'btn btn-secondary' ); ewil_button( ewil_t( 'spk_button_2' ), 'btn btn-ghost' ); ?></div>
      </div>
      <?php get_template_part( 'template-parts/speakers', null, array( 'home' => true, 'class' => 'spx rv' ) ); ?>

    </div>
  </section>

  <section class="awx dark gut" aria-labelledby="aw-title">
    <?php ewil_edge( 6, true, 'position:relative; z-index:1' ); ?>

    <?php ewil_contours( 12, 30, 7 ); ?>

    <div class="awx-in" style="padding-block: var(--space-8) var(--space-9)">
      <div class="awx-copy rv">
        <div style="display:grid; gap: var(--space-4)">
          <p class="eyebrow"><?php echo ewil_x( ewil_t( 'aw_eyebrow', 'Edu Women in Leadership Awards {year}' ) ); ?></p>
          <h2 class="h-lg hn" id="aw-title"><?php echo ewil_h( ewil_t( 'aw_heading', 'Recognising the women shaping the *future of education.*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
          <p class="lede"><?php echo ewil_x( ewil_t( 'aw_lede', 'The Edu Women in Leadership Awards celebrate leadership, innovation, resilience, and meaningful contributions to education.' ) ); ?></p>
        </div>
        <ol class="tl"><svg class="tl-wave" viewBox="0 0 300 40" preserveAspectRatio="none" aria-hidden="true"><path/></svg><?php ewil_award_date_items(); ?></ol>
        <div class="actions">
          <?php ewil_button( ewil_t( 'aw_button_1' ), 'btn btn-cta btn-lg' ); ?>

          <?php ewil_button( ewil_t( 'aw_button_2' ), 'btn btn-inverse btn-lg', false ); ?>

          <?php ewil_button( ewil_t( 'aw_button_3' ), 'btn btn-ghost', false ); ?>

        </div>
      </div>
      <div class="awx-photo rv"><span class="ring" aria-hidden="true"></span><?php ewil_img( ewil_t( 'aw_image' ) ); ?></div>
    </div>
    <?php ewil_edge( 7, false, 'position:relative' ); ?>

  </section>

  <section class="band" aria-labelledby="pt-title">
    <div class="ptx">
      <div class="ptx-head rv">
        <p class="eyebrow" style="margin:0"><?php echo ewil_x( ewil_t( 'pt_eyebrow', 'Partnerships & sponsorship' ) ); ?></p>
        <h2 class="h1 hn" id="pt-title"><?php echo ewil_h( ewil_t( 'pt_heading', 'Partner with us to shape the *future of education.*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
        <p class="lede"><?php echo ewil_x( ewil_t( 'pt_lede', 'Put your organisation alongside the women leading change in South African and African schools, with partnerships designed around your goals.' ) ); ?></p>
        <div class="actions"><?php ewil_button( ewil_t( 'pt_button' ), 'btn btn-primary btn-lg' ); ewil_button( ewil_t( 'pt_button_2' ), 'btn btn-secondary btn-lg', false ); ?></div>
      </div>
      <ul class="ptx-list rv"><?php foreach ( $ewil_opps as $ewil_o ) : ?><li><b><?php echo esc_html( get_the_title( $ewil_o ) ); ?></b><span><?php echo esc_html( ewil_field( 'description', '', $ewil_o->ID ) ); ?></span></li><?php endforeach; ?></ul>
    </div>
  </section>

  <section class="prx gut" aria-labelledby="reg-title">
    <?php ewil_edge( 8, true ); ?>

    <?php ewil_contours( 8, 22, 9 ); ?>

    <div class="prx-in" style="padding-block: var(--space-8)">
      <div class="head center rv">
        <p class="eyebrow"><?php echo ewil_x( ewil_t( 'reg_eyebrow', 'Registration' ) ); ?></p>
        <h2 class="h1 hn" id="reg-title"><?php echo ewil_h( ewil_t( 'reg_heading', 'Secure your place *in {city}*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
        <p class="deadline"><b><?php esc_html_e( 'Early bird', 'ewil' ); ?></b> <?php /* translators: %s: deadline */ echo esc_html( sprintf( __( 'Register by %s', 'ewil' ), ewil_setting( 'early_bird_deadline' ) ) ); ?></p>
      </div>
      <?php get_template_part( 'template-parts/prices', null, array( 'class' => 'prices rv' ) ); ?>

    </div>
    <?php ewil_edge( 9 ); ?>

  </section>

  <section class="band" aria-labelledby="nl-title">
    <div class="cmx">
      <div class="cmx-photo rv"><?php ewil_img( ewil_t( 'nl_image' ), array( 'class' => 'wave-mask-b', 'style' => 'object-position: center' ) ); ?></div>
      <div class="panel rv">
        <div class="head" style="margin-bottom: var(--space-5)">
          <p class="eyebrow"><?php echo ewil_x( ewil_t( 'nl_eyebrow', 'Join the community' ) ); ?></p>
          <h2 class="h1 hn" id="nl-title"><?php echo ewil_h( ewil_t( 'nl_heading', 'Stay connected to the *future of education.*' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
          <p class="small"><?php echo ewil_x( ewil_t( 'nl_lede', 'Hear about speaker announcements, deadlines and EduPulse Africa programmes throughout the year.' ) ); ?></p>
        </div>
        <div data-form="newsletter"><?php ewil_form( 'newsletter' ); ?></div>
      </div>
    </div>
  </section>
</div>

<?php
get_footer();
