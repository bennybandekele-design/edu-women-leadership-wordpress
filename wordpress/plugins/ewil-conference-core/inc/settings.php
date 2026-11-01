<?php
/**
 * Conference Settings.
 *
 * ACF Free has no Options pages, so the settings live on a single private post
 * of the ewil_settings post type, opened from the "Conference Settings" admin
 * menu. Everything reads values through ewil_setting(); switching to an ACF Pro
 * Options page later only means changing ewil_settings_id().
 */

defined( 'ABSPATH' ) || exit;

/**
 * Values used when a setting is left empty. They match the content of the
 * original static website.
 */
function ewil_setting_defaults() {
	return array(
		// General.
		'event_name'          => 'Edu Women in Leadership',
		'event_year'          => '2027',
		'event_theme'         => 'The Future Belongs to *Bold Leaders*: Women Reimagining Education for a Changing World',
		'event_start'         => '2027-08-27 00:00:00',
		'event_end'           => '2027-08-29 00:00:00',
		'date_text'           => '27–28 August 2027',
		'date_short'          => '27–28 Aug',
		'venue'               => 'Coastlands Musgrave Hotel',
		'city'                => 'Durban',
		'host'                => 'EduPulse Africa',
		'early_bird_deadline' => '28 February 2027',
		'photo_credit'        => 'Photos: previous EduPulse Africa events',
		'meta_description'    => 'Edu Women in Leadership Conference 2027, hosted by EduPulse Africa. 27–28 August 2027, Coastlands Musgrave Hotel, Durban.',
		// Announcement bar.
		'announce_show'       => true,
		'announce_text'       => 'Edu Women in Leadership Conference 2027 | 27–28 August 2027',
		'announce_link'       => array(
			'url'    => '/register/',
			'title'  => 'Early-bird closes 28 Feb 2027',
			'target' => '',
		),
		// Registration.
		'payment_note'        => 'Online payment is not available on this website yet. Submitting the form records your registration, and the EduPulse Africa team will contact you to confirm your rate and next steps.',
		// Call for Papers.
		'cfp_deadline'        => '',
		'cfp_abstract_min'    => 300,
		'cfp_abstract_max'    => 500,
		'cfp_bio_min'         => 100,
		'cfp_bio_max'         => 150,
		'cfp_file_types'      => '.pdf,.doc,.docx',
		'cfp_file_label'      => 'PDF or Word',
		'cfp_max_mb'          => 5,
		'cfp_formats'         => "Presentation\nWorkshop\nPanel discussion\nPoster",
		// Awards.
		'awards_open'         => '2026-11-09 00:00:00',
		'awards_close'        => '2027-02-27 23:59:59',
		'awards_ceremony'     => '2027-08-28',
		'awards_ceremony_note' => 'Time and venue to be confirmed',
		'awards_eligibility'  => '',
		'awards_judging'      => '',
		'awards_guidelines'   => '',
		// Contact and social.
		'contact_email'       => '',
		'contact_phone'       => '',
		'contact_address'     => '',
		'social_linkedin'     => '',
		'social_facebook'     => '',
		'social_instagram'    => '',
		'social_x'            => '',
		'social_youtube'      => '',
		// Forms.
		'notify_email'        => '',
		'msg_registration'    => 'Thank you. Your registration details have been received. Submitting the form records your registration, and the EduPulse Africa team will contact you to confirm your rate and next steps.',
		'msg_paper'           => 'Thank you. Your proposal has been received. The programme committee will be in touch once reviews are complete.',
		'msg_nomination'      => 'Thank you. Your nomination has been received.',
		'msg_partnership'     => 'Thank you. Our partnerships team will be in touch.',
		'msg_newsletter'      => 'Thank you for joining. You’ll hear from us about speakers, deadlines and events.',
		'msg_contact'         => 'Thank you. We’ll reply to your message by email.',
		'list_roles'          => "Principal or deputy principal\nHead of Department / SMT member\nTeacher\nAspiring leader\nPolicymaker or government official\nAcademic or researcher\nEducation consultant / PD provider\nNGO or non-profit\nCorporate or EdTech\nEntrepreneur or innovator\nOther",
		'list_countries'      => "South Africa\nBotswana\nEswatini\nLesotho\nNamibia\nZimbabwe\nZambia\nMozambique\nMalawi\nKenya\nUganda\nTanzania\nRwanda\nGhana\nNigeria\nEthiopia\nEgypt\nMorocco\nOther African country\nOutside Africa",
		'list_provinces'      => "Eastern Cape\nFree State\nGauteng\nKwaZulu-Natal\nLimpopo\nMpumalanga\nNorth West\nNorthern Cape\nWestern Cape\nNot in South Africa",
		'list_topics'         => "Registration\nCall for Papers\nAwards\nPartnerships and sponsorship\nPrivacy request\nSomething else",
		'retention_months'    => 0,
	);
}

/**
 * ID of the Conference Settings post, creating it when needed.
 */
function ewil_settings_id() {
	static $id = null;
	if ( null !== $id ) {
		return $id;
	}
	$stored = (int) get_option( 'ewil_settings_post_id' );
	if ( $stored && 'ewil_settings' === get_post_type( $stored ) ) {
		$id = $stored;
		return $id;
	}
	$found = get_posts(
		array(
			'post_type'   => 'ewil_settings',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( $found ) {
		$id = (int) $found[0];
	} elseif ( post_type_exists( 'ewil_settings' ) && current_user_can( 'manage_options' ) ) {
		$id = (int) wp_insert_post(
			array(
				'post_type'   => 'ewil_settings',
				'post_status' => 'private',
				'post_title'  => __( 'Conference Settings', 'ewil' ),
			)
		);
	} else {
		return 0;
	}
	update_option( 'ewil_settings_post_id', $id, true );
	return $id;
}

/**
 * Reads one conference setting, falling back to the default when empty.
 *
 * @param string $name Setting (ACF field) name.
 * @return mixed
 */
function ewil_setting( $name ) {
	$defaults = ewil_setting_defaults();
	$default  = $defaults[ $name ] ?? '';
	$id       = ewil_settings_id();
	if ( ! $id || ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = get_field( $name, $id, false );
	if ( is_bool( $default ) ) {
		// A true/false field that was never saved returns null.
		return null === $value || '' === $value ? $default : (bool) $value;
	}
	if ( 'announce_link' === $name ) {
		$value = get_field( $name, $id );
	}
	return ( null === $value || '' === $value || false === $value || array() === $value ) ? $default : $value;
}

/**
 * The admin menu opens the single settings post directly.
 */
add_action(
	'admin_menu',
	function () {
		add_menu_page(
			__( 'Conference Settings', 'ewil' ),
			__( 'Conference Settings', 'ewil' ),
			'manage_options',
			'ewil-settings',
			'__return_null',
			'dashicons-admin-generic',
			58
		);
	}
);

add_action(
	'load-toplevel_page_ewil-settings',
	function () {
		$id = ewil_settings_id();
		wp_safe_redirect( $id ? admin_url( 'post.php?post=' . $id . '&action=edit' ) : admin_url() );
		exit;
	}
);

/**
 * Keeps the settings screen tidy: no "Add new", and it stays private.
 */
add_action(
	'admin_head',
	function () {
		$screen = get_current_screen();
		if ( $screen && 'ewil_settings' === $screen->post_type ) {
			echo '<style>.page-title-action,#minor-publishing,#delete-action{display:none!important}</style>';
		}
	}
);

add_filter(
	'wp_insert_post_data',
	function ( $data ) {
		if ( 'ewil_settings' === $data['post_type'] && ! in_array( $data['post_status'], array( 'auto-draft', 'trash' ), true ) ) {
			$data['post_status'] = 'private';
			$data['post_title']  = __( 'Conference Settings', 'ewil' );
		}
		return $data;
	}
);

add_filter(
	'parent_file',
	function ( $parent ) {
		$screen = get_current_screen();
		return ( $screen && 'ewil_settings' === $screen->post_type ) ? 'ewil-settings' : $parent;
	}
);

/**
 * Clears the page cache after settings or conference content change, so the
 * website shows the update straight away.
 */
add_action(
	'save_post',
	function ( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) || ! str_starts_with( $post->post_type, 'ewil_' ) || 'ewil_submission' === $post->post_type ) {
			return;
		}
		do_action( 'litespeed_purge_all' );
	},
	20,
	2
);
