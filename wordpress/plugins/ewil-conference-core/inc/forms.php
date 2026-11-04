<?php
/**
 * The six website forms. The markup is identical to the original static site
 * so the theme's styling and the in-browser validation apply unchanged. The same
 * definitions drive the server-side validation in submit.php.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Form names and their admin labels.
 */
function ewil_form_kinds() {
	return array(
		'registration' => __( 'Registrations', 'ewil' ),
		'paper'        => __( 'Paper proposals', 'ewil' ),
		'nomination'   => __( 'Nominations', 'ewil' ),
		'partnership'  => __( 'Partnership enquiries', 'ewil' ),
		'newsletter'   => __( 'Community sign-ups', 'ewil' ),
		'contact'      => __( 'Messages', 'ewil' ),
	);
}

/**
 * Builds a form's markup and collects its field definitions.
 */
class EWIL_Form_Builder {

	/** @var int Shared across all forms on a page so ids stay unique. */
	protected static $fid = 0;

	/** @var array Field definitions collected while building. */
	public $fields = array();

	/** @var array Consent checkboxes collected while building. */
	public $consents = array();

	/**
	 * One labelled field, as in the original F() helper.
	 *
	 * @param string $label Field label.
	 * @param string $name  Field name.
	 * @param string $type  text, email, tel, textarea, select or file.
	 * @param array  $o     Options: req, ac, rows, min, max, hint, options, accept, maxmb,
	 *                      default (preselected option, no "Select…"), attrs (extra HTML attributes),
	 *                      record (label used in emails and exports), delegate (delegate number).
	 */
	public function f( $label, $name, $type = 'text', $o = array() ) {
		$id  = 'f-' . $name . '-' . ( ++self::$fid );
		$req = ! isset( $o['req'] ) || false !== $o['req'];

		$this->fields[ $name ] = array_merge( $o, array( 'label' => $label, 'type' => $type, 'req' => $req ) );

		$lab = '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . ( $req ? '<span class="req" aria-hidden="true">*</span>' : ' <span class="opt">(' . esc_html__( 'optional', 'ewil' ) . ')</span>' ) . '</label>';
		$a   = 'id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" ' . ( $req ? 'required' : '' ) . ' ' . ( ! empty( $o['ac'] ) ? 'autocomplete="' . esc_attr( $o['ac'] ) . '"' : '' ) . ' aria-describedby="' . esc_attr( $id ) . '-h ' . esc_attr( $id ) . '-e"' . ( ! empty( $o['attrs'] ) ? ' ' . $o['attrs'] : '' );

		if ( 'textarea' === $type ) {
			$ctl = '<textarea class="input" ' . $a . ' rows="' . (int) ( $o['rows'] ?? 6 ) . '" ' . ( ! empty( $o['min'] ) ? 'data-min="' . (int) $o['min'] . '" data-max="' . (int) $o['max'] . '"' : '' ) . '></textarea>';
		} elseif ( 'select' === $type ) {
			$has_default = isset( $o['default'] );
			$opts        = $has_default ? '' : '<option value="" disabled selected>' . esc_html__( 'Select…', 'ewil' ) . '</option>';
			foreach ( $o['options'] as $option ) {
				$opts .= '<option' . ( $has_default && (string) $option === (string) $o['default'] ? ' selected' : '' ) . '>' . esc_html( $option ) . '</option>';
			}
			$ctl = '<select class="input" ' . $a . '>' . $opts . '</select>';
		} elseif ( 'file' === $type ) {
			$ctl = '<input class="input" type="file" ' . $a . ' accept="' . esc_attr( $o['accept'] ) . '" data-maxmb="' . esc_attr( $o['maxmb'] ) . '">';
		} else {
			$ctl = '<input class="input" type="' . esc_attr( $type ) . '" ' . $a . '>';
		}

		$range = ! empty( $o['min'] ) ? '<span class="wc" data-wc>0 / ' . (int) $o['min'] . '–' . (int) $o['max'] . ' ' . esc_html__( 'words', 'ewil' ) . '</span>' : '';

		return '<div class="field">' . $lab . $ctl . '<div class="hint" id="' . esc_attr( $id ) . '-h"><span>' . esc_html( $o['hint'] ?? '' ) . '</span>' . $range . '</div><p class="err" id="' . esc_attr( $id ) . '-e" hidden></p></div>';
	}

	/**
	 * A consent checkbox. $html may contain a link to the privacy policy.
	 *
	 * @param string $name Field name.
	 * @param string $html Label HTML.
	 * @param bool   $req  Whether it must be ticked.
	 */
	public function consent( $name, $html, $req = false ) {
		$this->consents[ $name ] = array(
			'req'   => $req,
			'label' => wp_strip_all_tags( $html ),
		);
		return '<label class="choice"><input type="checkbox" name="' . esc_attr( $name ) . '" ' . ( $req ? 'required' : '' ) . '><span>' . $html . ( $req ? '<span class="req" aria-hidden="true">*</span>' : '' ) . '</span></label>';
	}

	/**
	 * The optional marketing consent shared by several forms.
	 */
	public function marketing() {
		/* translators: %s: privacy policy link */
		return $this->consent( 'marketing_consent', sprintf( __( 'Yes, send me news about Edu Women in Leadership and EduPulse Africa events. I can unsubscribe at any time. See the %s.', 'ewil' ), ewil_privacy_link() ) );
	}

	/**
	 * Submit button and the status area.
	 *
	 * @param string $label Button text.
	 * @param string $cls   Button style class.
	 */
	public function submit( $label, $cls = 'btn-primary' ) {
		return '<div class="actions"><button class="btn ' . esc_attr( $cls ) . ' btn-lg" type="submit">' . esc_html( $label ) . ' <span class="arrow" aria-hidden="true">→</span></button></div><div class="status" role="status" aria-live="polite" hidden></div>';
	}
}

/**
 * Link to the privacy policy used inside consent labels.
 */
function ewil_privacy_link() {
	$url = get_privacy_policy_url();
	if ( ! $url ) {
		$url = home_url( '/privacy-notice/' );
	}
	return '<a href="' . esc_url( $url ) . '">' . esc_html__( 'privacy policy', 'ewil' ) . '</a>';
}

/**
 * Link to the Terms of Service used in the registration consent.
 */
function ewil_terms_link() {
	$page = get_page_by_path( 'website-terms' );
	$url  = $page && 'publish' === $page->post_status ? get_permalink( $page ) : home_url( '/website-terms/' );
	return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Terms of Service', 'ewil' ) . '</a>';
}

/**
 * Titles of published posts of a conference post type, for dropdowns.
 *
 * @param string $post_type Post type slug.
 * @return string[]
 */
function ewil_titles( $post_type ) {
	// The stored title, not get_the_title(): display filters turn "&" into
	// "&#038;", which would no longer match the value the browser sends back.
	return array_map(
		function ( $p ) {
			return $p->post_title;
		},
		ewil_items( $post_type )
	);
}

/**
 * Builds one form's inner markup and returns the builder holding its fields.
 *
 * @param string $kind Form name.
 * @return array{0:string,1:EWIL_Form_Builder}
 */
function ewil_build_form( $kind ) {
	$b         = new EWIL_Form_Builder();
	$roles     = ewil_lines( ewil_setting( 'list_roles' ) );
	$countries = ewil_lines( ewil_setting( 'list_countries' ) );
	$provinces = ewil_lines( ewil_setting( 'list_provinces' ) );
	$privacy   = ewil_privacy_link();
	$html      = '';

	switch ( $kind ) {
		case 'newsletter':
			$html = '<div class="row2">' . $b->f( __( 'Name', 'ewil' ), 'name', 'text', array( 'ac' => 'name' ) ) . $b->f( __( 'Email address', 'ewil' ), 'email', 'email', array( 'ac' => 'email' ) ) . '</div>'
				. '<div class="row2">' . $b->f( __( 'Professional role', 'ewil' ), 'role', 'select', array( 'options' => $roles ) ) . $b->f( __( 'Country', 'ewil' ), 'country', 'select', array( 'options' => $countries ) ) . '</div>'
				/* translators: %s: privacy policy link */
				. $b->consent( 'marketing_consent', sprintf( __( 'I agree to receive news and updates from Edu Women in Leadership and EduPulse Africa. I can unsubscribe at any time. See the %s.', 'ewil' ), $privacy ), true )
				. $b->submit( __( 'Join the community', 'ewil' ) );
			break;

		case 'paper':
			$amin  = (int) ewil_setting( 'cfp_abstract_min' );
			$amax  = (int) ewil_setting( 'cfp_abstract_max' );
			$bmin  = (int) ewil_setting( 'cfp_bio_min' );
			$bmax  = (int) ewil_setting( 'cfp_bio_max' );
			$flab  = ewil_setting( 'cfp_file_label' );
			$maxmb = ewil_setting( 'cfp_max_mb' );
			$html  = '<fieldset><legend>' . esc_html__( 'Your proposal', 'ewil' ) . '</legend>
    ' . $b->f( __( 'Presentation title', 'ewil' ), 'title' ) . '
    <div class="row2">' . $b->f( __( 'Relevant conference track', 'ewil' ), 'track', 'select', array( 'options' => ewil_titles( 'ewil_track' ) ) ) . $b->f( __( 'Preferred presentation format', 'ewil' ), 'format', 'select', array( 'options' => ewil_lines( ewil_setting( 'cfp_formats' ) ) ) ) . '</div>
    ' . $b->f( __( 'Abstract', 'ewil' ), 'abstract', 'textarea', array( 'rows' => 10, 'min' => $amin, 'max' => $amax, 'hint' => __( 'Your aim, approach and key insight for delegates.', 'ewil' ) ) ) . '
    ' . $b->f( __( 'Supporting document', 'ewil' ), 'document', 'file', array( 'req' => false, 'accept' => ewil_setting( 'cfp_file_types' ), 'maxmb' => $maxmb, 'hint' => sprintf( '%s, up to %s MB.', $flab, $maxmb ) ) ) . '
  </fieldset><fieldset><legend>' . esc_html__( 'About you', 'ewil' ) . '</legend>
    <div class="row2">' . $b->f( __( 'Name and surname', 'ewil' ), 'name', 'text', array( 'ac' => 'name' ) ) . $b->f( __( 'Professional title or role', 'ewil' ), 'role', 'text', array( 'ac' => 'organization-title' ) ) . '</div>
    <div class="row2">' . $b->f( __( 'Organisation', 'ewil' ), 'organisation', 'text', array( 'ac' => 'organization' ) ) . $b->f( __( 'Email address', 'ewil' ), 'email', 'email', array( 'ac' => 'email' ) ) . '</div>
    ' . $b->f( __( 'Country', 'ewil' ), 'country', 'select', array( 'options' => $countries ) ) . '
    ' . $b->f( __( 'Short professional biography', 'ewil' ), 'bio', 'textarea', array( 'rows' => 5, 'min' => $bmin, 'max' => $bmax, 'hint' => __( 'Written in the third person, as it would appear in the programme.', 'ewil' ) ) ) . '
  </fieldset>' . $b->consent( 'processing_consent', esc_html__( 'I agree that EduPulse Africa may use these details to review my proposal and contact me about it.', 'ewil' ), true ) . $b->marketing() . $b->submit( __( 'Submit proposal', 'ewil' ) );
			break;

		case 'nomination':
			$categories = ewil_titles( 'ewil_award_category' );
			$html       = '<fieldset><legend>' . esc_html__( 'The nominee', 'ewil' ) . '</legend>
    <div class="row2">' . $b->f( __( 'Nominee’s name and surname', 'ewil' ), 'nominee_name' ) . $b->f( __( 'Nominee’s role', 'ewil' ), 'nominee_role' ) . '</div>
    <div class="row2">' . $b->f( __( 'School or organisation', 'ewil' ), 'nominee_organisation' ) . $b->f( __( 'Province', 'ewil' ), 'nominee_province', 'select', array( 'options' => $provinces ) ) . '</div>
    ' . $b->f( __( 'Country', 'ewil' ), 'nominee_country', 'select', array( 'options' => $countries ) ) . '
    ' . ( $categories ? $b->f( __( 'Award category', 'ewil' ), 'category', 'select', array( 'options' => $categories ) ) : '' ) . '
    ' . $b->f( __( 'Why does she deserve recognition?', 'ewil' ), 'motivation', 'textarea', array( 'rows' => 8, 'hint' => __( 'Describe her leadership, innovation, resilience and impact, with examples.', 'ewil' ) ) ) . '
  </fieldset><fieldset><legend>' . esc_html__( 'About you', 'ewil' ) . '</legend>
    <div class="row2">' . $b->f( __( 'Your name', 'ewil' ), 'nominator_name', 'text', array( 'ac' => 'name' ) ) . $b->f( __( 'Your email address', 'ewil' ), 'nominator_email', 'email', array( 'ac' => 'email' ) ) . '</div>
    ' . $b->f( __( 'Your relationship to the nominee', 'ewil' ), 'relationship', 'text', array( 'req' => false, 'hint' => __( 'For example colleague, learner’s parent, district official.', 'ewil' ) ) ) . '
  </fieldset>' . $b->consent( 'processing_consent', esc_html__( 'I agree that EduPulse Africa may use these details to process this nomination and contact me about it.', 'ewil' ), true ) . $b->marketing() . $b->submit( __( 'Submit nomination', 'ewil' ), 'btn-cta' );
			break;

		case 'partnership':
			$interests   = ewil_titles( 'ewil_opportunity' );
			$interests[] = __( 'Something else', 'ewil' );
			$html        = '<div class="row2">' . $b->f( __( 'Organisation', 'ewil' ), 'organisation', 'text', array( 'ac' => 'organization' ) ) . $b->f( __( 'Contact person', 'ewil' ), 'name', 'text', array( 'ac' => 'name' ) ) . '</div>
    <div class="row2">' . $b->f( __( 'Email address', 'ewil' ), 'email', 'email', array( 'ac' => 'email' ) ) . $b->f( __( 'Partnership interest', 'ewil' ), 'interest', 'select', array( 'options' => $interests ) ) . '</div>
    ' . $b->f( __( 'Message', 'ewil' ), 'message', 'textarea', array( 'rows' => 6, 'hint' => __( 'What would your organisation like to achieve?', 'ewil' ) ) ) . '
    ' . $b->consent( 'processing_consent', esc_html__( 'I agree that EduPulse Africa may use these details to respond to this enquiry.', 'ewil' ), true ) . $b->marketing() . $b->submit( __( 'Send enquiry', 'ewil' ) );
			break;

		case 'registration':
			$rates  = ewil_items( 'ewil_rate' );
			$radios = '';
			$notes  = '';
			foreach ( $rates as $i => $rate ) {
				$small   = ewil_field( 'badge', '', $rate->ID ) ?: ewil_field( 'description', '', $rate->ID );
				$radios .= '<label class="radio"><input type="radio" name="rate" value="' . esc_attr( $rate->post_name ) . '" required ' . ( 0 === $i ? 'checked' : '' ) . '><span>' . esc_html( get_the_title( $rate ) ) . '<small>' . esc_html( $small ) . '</small></span><b>' . esc_html( ewil_field( 'price', '', $rate->ID ) ) . '</b></label>';
				$note    = ewil_field( 'note', '', $rate->ID );
				if ( $note ) {
					$notes .= '<p class="small" data-nofee-note data-rate-note="' . esc_attr( $rate->post_name ) . '" hidden>' . esc_html( $note ) . '</p>';
				}
			}
			$b->fields['rate'] = array(
				'label'   => __( 'Rate', 'ewil' ),
				'type'    => 'radio',
				'req'     => true,
				'options' => wp_list_pluck( $rates, 'post_name' ),
			);
			$html = '<fieldset><legend>' . esc_html__( 'Choose your rate', 'ewil' ) . '</legend><div class="radios" role="radiogroup" aria-label="' . esc_attr__( 'Registration rate', 'ewil' ) . '">' . $radios . '</div>
    ' . $notes . '</fieldset>
    <fieldset><legend>' . esc_html__( 'Your details', 'ewil' ) . '</legend>
    <div class="row2">' . $b->f( __( 'Name and surname', 'ewil' ), 'name', 'text', array( 'ac' => 'name' ) ) . $b->f( __( 'Email address', 'ewil' ), 'email', 'email', array( 'ac' => 'email' ) ) . '</div>
    <div class="row2">' . $b->f( __( 'Mobile number', 'ewil' ), 'phone', 'tel', array( 'ac' => 'tel' ) ) . $b->f( __( 'Professional role', 'ewil' ), 'role', 'select', array( 'options' => $roles ) ) . '</div>
    <div class="row2">' . $b->f( __( 'School or organisation', 'ewil' ), 'organisation', 'text', array( 'ac' => 'organization' ) ) . $b->f( __( 'Province', 'ewil' ), 'province', 'select', array( 'options' => $provinces ) ) . '</div>
    ' . $b->f( __( 'Country', 'ewil' ), 'country', 'select', array( 'options' => $countries ) ) . '
    ' . $b->f( __( 'Number of delegates', 'ewil' ), 'delegates', 'select', array( 'options' => array_map( 'strval', range( 1, ewil_max_delegates() ) ), 'default' => '1', 'attrs' => 'data-delegates', 'hint' => sprintf( /* translators: %d: maximum delegates */ __( 'Including you. Register up to %d delegates from your school or organisation, all at the rate chosen above.', 'ewil' ), ewil_max_delegates() ) ) ) . '
    </fieldset>' . ewil_delegate_fieldsets( $b, $roles ) . '
    ' . $b->f( __( 'Dietary or accessibility requirements', 'ewil' ), 'requirements', 'textarea', array( 'req' => false, 'rows' => 3, 'hint' => __( 'For any delegate. Please include the delegate’s name.', 'ewil' ) ) ) . $b->consent( 'processing_consent', sprintf( /* translators: %s: Terms of Service link */ esc_html__( 'I agree that EduPulse Africa may use these details to manage my registration and contact me about it, and I accept the %s.', 'ewil' ), ewil_terms_link() ), true ) . $b->marketing() . $b->submit( __( 'Submit registration', 'ewil' ), 'btn-cta' );
			break;

		case 'contact':
			$html = '<div class="row2">' . $b->f( __( 'Name', 'ewil' ), 'name', 'text', array( 'ac' => 'name' ) ) . $b->f( __( 'Email address', 'ewil' ), 'email', 'email', array( 'ac' => 'email' ) ) . '</div>
    ' . $b->f( __( 'Topic', 'ewil' ), 'topic', 'select', array( 'options' => ewil_lines( ewil_setting( 'list_topics' ) ) ) ) . '
    ' . $b->f( __( 'Message', 'ewil' ), 'message', 'textarea', array( 'rows' => 6 ) ) . '
    ' . $b->consent( 'processing_consent', esc_html__( 'I agree that EduPulse Africa may use these details to reply to my message.', 'ewil' ), true ) . $b->submit( __( 'Send message', 'ewil' ) );
			break;
	}

	return array( $html, $b );
}

/**
 * Most delegates one registration can include (the contact person counts as one).
 */
function ewil_max_delegates() {
	return (int) apply_filters( 'ewil_max_delegates', 5 );
}

/**
 * Sections for delegates 2–5 of a group registration. Hidden and disabled until
 * the "Number of delegates" choice reveals them, so they are neither checked nor sent.
 *
 * @param EWIL_Form_Builder $b     Builder.
 * @param string[]          $roles Role choices.
 */
function ewil_delegate_fieldsets( $b, $roles ) {
	$html = '';
	for ( $n = 2; $n <= ewil_max_delegates(); $n++ ) {
		/* translators: %d: delegate number */
		$who  = sprintf( __( 'Delegate %d', 'ewil' ), $n );
		$p    = 'delegate_' . $n . '_';
		$rec  = fn( $label ) => $who . ': ' . $label;
		$html .= '<fieldset data-delegate="' . $n . '" hidden disabled><legend>' . esc_html( $who ) . '</legend>
    <div class="row2">' . $b->f( __( 'Name and surname', 'ewil' ), $p . 'name', 'text', array( 'delegate' => $n, 'record' => $rec( __( 'Name and surname', 'ewil' ) ) ) ) . $b->f( __( 'Email address', 'ewil' ), $p . 'email', 'email', array( 'delegate' => $n, 'record' => $rec( __( 'Email address', 'ewil' ) ) ) ) . '</div>
    <div class="row2">' . $b->f( __( 'Mobile number', 'ewil' ), $p . 'phone', 'tel', array( 'delegate' => $n, 'record' => $rec( __( 'Mobile number', 'ewil' ) ) ) ) . $b->f( __( 'Professional role', 'ewil' ), $p . 'role', 'select', array( 'options' => $roles, 'delegate' => $n, 'record' => $rec( __( 'Professional role', 'ewil' ) ) ) ) . '</div>
    </fieldset>';
	}
	return $html;
}

/**
 * Prints a complete form.
 *
 * @param string $kind Form name: registration, paper, nomination, partnership, newsletter or contact.
 */
function ewil_form( $kind ) {
	if ( ! isset( ewil_form_kinds()[ $kind ] ) ) {
		return;
	}
	list( $html ) = ewil_build_form( $kind );

	$attrs = array(
		'novalidate'   => true,
		'data-kind'    => $kind,
		'method'       => 'post',
		'action'       => rest_url( 'ewil/v1/submit/' . $kind ),
		'enctype'      => 'multipart/form-data',
	);
	if ( 'nomination' === $kind ) {
		$ceremony                   = ewil_setting( 'awards_ceremony' );
		$attrs['data-open']         = ewil_iso( ewil_setting( 'awards_open' ) );
		$attrs['data-close']        = ewil_iso( ewil_setting( 'awards_close' ) );
		/* translators: %s: date nominations open */
		$attrs['data-msg-before']   = sprintf( __( 'Nominations open on %s.', 'ewil' ), ewil_date( ewil_setting( 'awards_open' ), 'j F Y' ) );
		/* translators: %s: date nominations open */
		$attrs['data-msg-before2']  = sprintf( __( 'You can prepare your nomination now; the form accepts submissions from %s.', 'ewil' ), ewil_date( ewil_setting( 'awards_open' ), 'j F Y' ) );
		/* translators: %s: year */
		$attrs['data-msg-after']    = sprintf( __( 'Nominations for %s have closed.', 'ewil' ), ewil_setting( 'event_year' ) );
		/* translators: %s: ceremony date */
		$attrs['data-msg-after2']   = sprintf( __( 'Thank you to everyone who nominated. Winners will be celebrated on %s.', 'ewil' ), ewil_date( $ceremony, 'j F Y' ) );
	}

	$attr_html = '';
	foreach ( $attrs as $name => $value ) {
		$attr_html .= true === $value ? ' ' . $name : ' ' . $name . '="' . esc_attr( $value ) . '"';
	}

	$extra = '<input type="hidden" name="_ewil_ts" value=""><div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden"><label>' . esc_html__( 'Leave this field empty', 'ewil' ) . '<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';

	echo '<form' . $attr_html . '>' . $html . $extra . '</form>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts above.

	ewil_enqueue_form_script();
}

/**
 * Loads the form behaviour script once per page.
 */
function ewil_enqueue_form_script() {
	// Version from the file's modification time, so caches pick up every update.
	wp_enqueue_script( 'ewil-forms', EWIL_CORE_URL . 'assets/forms.js', array(), EWIL_CORE_VERSION . '.' . filemtime( EWIL_CORE_DIR . 'assets/forms.js' ), true );
	wp_localize_script(
		'ewil-forms',
		'EWIL_FORMS',
		array(
			'fileLabel' => ewil_setting( 'cfp_file_label' ),
			'i18n'      => array(
				'checkForm'   => __( 'Please check the form.', 'ewil' ),
				'needFix'     => __( 'Some fields need your attention.', 'ewil' ),
				'needConsent' => __( 'Tick the consent box to continue.', 'ewil' ),
				'received'    => __( 'Received.', 'ewil' ),
				'failed'      => __( 'Your submission didn’t go through.', 'ewil' ),
				'offline'     => __( 'Check your connection and try again in a moment.', 'ewil' ),
				'choose'      => __( 'Choose %s.', 'ewil' ),
				'enter'       => __( 'Enter %s.', 'ewil' ),
				'email'       => __( 'Enter an email address like name@school.co.za.', 'ewil' ),
				'words'       => __( 'Use %1$s–%2$s words (currently %3$s).', 'ewil' ),
				'fileType'    => __( 'Upload a %s file.', 'ewil' ),
				'fileSize'    => __( 'Choose a file smaller than %s MB.', 'ewil' ),
				'wordsUnit'   => __( 'words', 'ewil' ),
			),
		)
	);
}

add_shortcode(
	'ewil_form',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'type' => 'contact' ), $atts, 'ewil_form' );
		ob_start();
		echo '<div data-form="' . esc_attr( $atts['type'] ) . '">';
		ewil_form( $atts['type'] );
		echo '</div>';
		return ob_get_clean();
	}
);
