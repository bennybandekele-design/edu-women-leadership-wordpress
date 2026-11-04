// Content migration from the original static site. Run through Novamira's
// execute-php (no <?php tag). Idempotent: existing items are updated, not duplicated.
//
// ONE-TIME IMPORT, already run on 1 November 2026. Do not run it again on the
// live site: it would overwrite content edited since (for example the Privacy
// Policy, speakers' details and Conference Settings) with the original text.

$media = get_option( 'ewil_media_ids' );
$m     = function ( $file ) use ( $media ) { return (int) ( $media[ $file ] ?? 0 ); };
$log   = array();

// ---------- pages ----------
$pages = array(
	'home'            => array( 'Home', '' ),
	'about'           => array( 'About', 'about' ),
	'programme'       => array( 'Programme', 'programme' ),
	'speakers'        => array( 'Speakers', 'speakers' ),
	'call-for-papers' => array( 'Call for Papers', 'call-for-papers' ),
	'awards'          => array( 'Awards', 'awards' ),
	'partners'        => array( 'Partners', 'partners' ),
	'register'        => array( 'Register', 'register' ),
	'contact'         => array( 'Contact', 'contact' ),
	'privacy-notice'  => array( 'Privacy notice', '' ),
	'website-terms'   => array( 'Website terms', '' ),
);
$content = array(
	'about'           => '<!-- wp:paragraph --><p>The conference gives women in education a platform to strengthen their leadership capacity, exchange ideas, build professional networks, showcase innovation, address the challenges facing schools and communities, and shape the future of education.</p><!-- /wp:paragraph -->' . "\n\n" . '<!-- wp:paragraph --><p>In 2027 we meet in Durban on 27–28 August under the theme <em>The Future Belongs to Bold Leaders: Women Reimagining Education for a Changing World</em>, with participation from all nine South African provinces, across Africa and from international education partners.</p><!-- /wp:paragraph -->',
	'call-for-papers' => '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>Link your proposal clearly to one of the six conference tracks.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Show what delegates will take away: practical strategies, evidence or new ways of thinking.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Ground your work in African and South African education contexts where you can.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Write for a mixed audience of practitioners, leaders and researchers.</li><!-- /wp:list-item --></ul><!-- /wp:list -->',
	'partners'        => '<!-- wp:paragraph --><p>Tell us what your organisation would like to achieve and we will come back to you with options. Our 2027 partners will be announced here once confirmed.</p><!-- /wp:paragraph -->',
	'privacy-notice'  => '<!-- wp:paragraph {"className":"lede"} --><p class="lede">EduPulse Africa’s full privacy policy will be published here. This summary explains how the forms on this website use your information.</p><!-- /wp:paragraph -->' . "\n\n" . '<!-- wp:heading --><h2 class="wp-block-heading">What we collect</h2><!-- /wp:heading -->' . "\n\n" . '<!-- wp:paragraph --><p>Only the details each form asks for: for example your name, email address, role, organisation and country, and the content of any proposal, nomination or enquiry you send.</p><!-- /wp:paragraph -->' . "\n\n" . '<!-- wp:heading --><h2 class="wp-block-heading">How we use it</h2><!-- /wp:heading -->' . "\n\n" . '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>To process your registration, proposal, nomination or enquiry and contact you about it.</li><!-- /wp:list-item --><!-- wp:list-item --><li>To send you news and updates, only if you tick the box to agree. You can unsubscribe at any time.</li><!-- /wp:list-item --></ul><!-- /wp:list -->' . "\n\n" . '<!-- wp:heading --><h2 class="wp-block-heading">Your choices</h2><!-- /wp:heading -->' . "\n\n" . '<!-- wp:paragraph --><p>You can ask to see, correct or delete your information using the contact form. We aim to handle personal information in line with South Africa’s Protection of Personal Information Act (POPIA).</p><!-- /wp:paragraph -->',
	'website-terms'   => '<!-- wp:paragraph --><p>The full website terms will be published by EduPulse Africa before registration opens to the public.</p><!-- /wp:paragraph -->' . "\n\n" . '<!-- wp:paragraph {"className":"small"} --><p class="small">Registration, payment, cancellation and refund terms will be confirmed by the organisers and shown here and on the registration page.</p><!-- /wp:paragraph -->',
);
$ids = array();
$order = 0;
foreach ( $pages as $slug => $p ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$args     = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $p[0],
		'post_name'    => $slug,
		'menu_order'   => $order++,
		'post_content' => $content[ $slug ] ?? '',
	);
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id         = wp_update_post( wp_slash( $args ), true );
	} else {
		$id = wp_insert_post( wp_slash( $args ), true );
	}
	if ( is_wp_error( $id ) ) {
		$log[] = "page $slug: " . $id->get_error_message();
		continue;
	}
	if ( $p[1] ) {
		update_post_meta( $id, '_wp_page_template', 'page-templates/' . $p[1] . '.php' );
	} else {
		delete_post_meta( $id, '_wp_page_template' );
	}
	$ids[ $slug ] = $id;
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $ids['home'] );
update_option( 'wp_page_for_privacy_policy', $ids['privacy-notice'] );
$url = function ( $slug ) use ( $ids ) { return wp_make_link_relative( get_permalink( $ids[ $slug ] ) ); };
$link = function ( $title, $url ) { return array( 'title' => $title, 'url' => $url, 'target' => '' ); };

// ---------- conference post types ----------
$upsert = function ( $type, $title, $fields, $order, $slug = '', $terms = array() ) use ( &$log ) {
	$found = get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'title' => $title, 'numberposts' => 1, 'fields' => 'ids' ) );
	$args  = array( 'post_type' => $type, 'post_status' => 'publish', 'post_title' => $title, 'menu_order' => $order );
	if ( $slug ) {
		$args['post_name'] = $slug;
	}
	if ( $found ) {
		$args['ID'] = $found[0];
	}
	$id = $found ? wp_update_post( wp_slash( $args ), true ) : wp_insert_post( wp_slash( $args ), true );
	if ( is_wp_error( $id ) ) {
		$log[] = "$type $title: " . $id->get_error_message();
		return 0;
	}
	foreach ( $fields as $k => $v ) {
		update_field( $k, $v, $id );
	}
	foreach ( $terms as $tax => $term ) {
		wp_set_object_terms( $id, $term, $tax );
	}
	return $id;
};

$tracks = array(
	array( 'Bold Leadership & School Transformation', 'Leading change in schools and systems with courage, clarity and care: culture, strategy and accountability.' ),
	array( 'Teaching, Learning & Future-Ready Education', 'Curriculum, pedagogy and assessment that prepare learners for a changing world.' ),
	array( 'Technology, AI & Digital Transformation', 'How AI and digital tools are reshaping teaching, learning and school leadership, responsibly.' ),
	array( 'Wellbeing, Inclusion & Resilient Communities', 'Caring for learners, teachers and leaders, and building schools where everyone belongs.' ),
	array( 'Innovation, Entrepreneurship & New Models for Education', 'New ideas, ventures and delivery models that widen access and improve quality.' ),
	array( 'Partnerships, Policy & Sustainable Education', 'Government, NGOs, business and communities working together for lasting improvement.' ),
);
foreach ( $tracks as $i => $t ) {
	$upsert( 'ewil_track', $t[0], array( 'description' => $t[1] ), $i + 1 );
}

foreach ( array( 'why-attend' => 'Why attend', 'who-should-attend' => 'Who should attend' ) as $slug => $name ) {
	if ( ! term_exists( $slug, 'ewil_highlight_group' ) ) {
		wp_insert_term( $name, 'ewil_highlight_group', array( 'slug' => $slug ) );
	}
}
$benefits = array(
	array( 'Practical leadership strategies', 'Approaches you can take back to your school or organisation on Monday.', 'i-landmark' ),
	array( 'Innovative teaching and learning', 'Fresh ideas for classrooms, curriculum and assessment.', 'i-book' ),
	array( 'New perspectives', 'Professional development through keynotes, panels and workshops.', 'i-search' ),
	array( 'Networking with education leaders', 'Meet peers from all nine provinces, across Africa and beyond.', 'i-users' ),
	array( 'Emerging technologies and trends', 'See how AI and digital tools are changing education.', 'i-cpu' ),
	array( 'Collaboration and partnerships', 'Find partners for projects, research and school improvement.', 'i-handshake' ),
	array( 'Recognition of women in education', 'Celebrate the women transforming learning at the Awards.', 'i-heart' ),
	array( 'A stronger professional community', 'Join a network that continues long after August.', 'i-school' ),
);
foreach ( $benefits as $i => $b ) {
	$upsert( 'ewil_highlight', $b[0], array( 'description' => $b[1], 'icon' => $b[2] ), $i + 1, '', array( 'ewil_highlight_group' => 'why-attend' ) );
}
$audience = array(
	array( 'i-school', 'School principals and deputy principals' ), array( 'i-users', 'Heads of Department and SMT members' ), array( 'i-book', 'Teachers and aspiring leaders' ), array( 'i-landmark', 'Education policymakers' ),
	array( 'i-search', 'Researchers and academics' ), array( 'i-heart', 'NGOs and education organisations' ), array( 'i-cpu', 'Education technology companies' ), array( 'i-handshake', 'Corporate partners and sponsors' ),
);
foreach ( $audience as $i => $a ) {
	$upsert( 'ewil_highlight', $a[1], array( 'description' => '', 'icon' => $a[0] ), 101 + $i, '', array( 'ewil_highlight_group' => 'who-should-attend' ) );
}

$opps = array(
	array( 'Conference sponsorship', 'Headline and supporting packages across the two-day event.' ),
	array( 'Education technology demonstrations', 'Show your tools to the leaders who choose them.' ),
	array( 'Delegate scholarships', 'Sponsor attendance for educators who could not otherwise come.' ),
	array( 'Workshop and masterclass partnerships', 'Co-design practical sessions with our programme team.' ),
	array( 'Awards sponsorship', 'Stand behind the recognition of women transforming education.' ),
	array( 'Exhibition opportunities', 'Meet delegates in the exhibition space throughout the conference.' ),
	array( 'Thought leadership and speaker contributions', 'Share your expertise from the stage or in panels.' ),
	array( 'Provincial educator development', 'Support initiatives that reach educators in every province.' ),
	array( 'Year-round leadership programmes', 'Partner with EduPulse Africa beyond the conference.' ),
);
foreach ( $opps as $i => $o ) {
	$upsert( 'ewil_opportunity', $o[0], array( 'description' => $o[1] ), $i + 1 );
}

$upsert( 'ewil_rate', 'Conference & Gala Dinner', array( 'price' => 'R3,500', 'unit' => 'per delegate', 'featured' => 1, 'badge' => 'Gala dinner included', 'description' => '', 'includes' => "Two-day conference, 27–28 August 2027\nGala dinner", 'note' => '', 'button_label' => 'Register' ), 1, 'conference-gala' );
$upsert( 'ewil_rate', 'No-Fee-Paying Schools', array( 'price' => 'R3,000', 'unit' => 'per delegate', 'featured' => 0, 'badge' => '', 'description' => 'A reduced rate for educators from no-fee-paying schools.', 'includes' => '', 'note' => 'This rate is not applied automatically. Eligibility is confirmed by the organisers after you register.', 'button_label' => 'Register at this rate' ), 2, 'no-fee-school' );
$upsert( 'ewil_rate', 'Standard fee', array( 'price' => 'R4,000', 'unit' => 'per delegate', 'featured' => 0, 'badge' => '', 'description' => 'Conference registration at the standard rate.', 'includes' => '', 'note' => '', 'button_label' => 'Register' ), 3, 'standard' );

// ---------- conference settings ----------
$settings_id = ewil_settings_id();
foreach ( ewil_setting_defaults() as $name => $value ) {
	if ( 'announce_link' === $name ) {
		$value = $link( 'Early-bird closes 28 Feb 2027', $url( 'register' ) );
	}
	if ( 'awards_ceremony' === $name ) {
		$value = '20270828';
	}
	if ( is_bool( $value ) ) {
		$value = $value ? 1 : 0;
	}
	update_field( $name, $value, $settings_id );
}

// ---------- page fields ----------
$hero = function ( $id, $eyebrow, $heading, $lede, $button = null ) {
	update_field( 'page_eyebrow', $eyebrow, $id );
	update_field( 'page_heading', $heading, $id );
	update_field( 'page_lede', $lede, $id );
	update_field( 'page_button', $button, $id );
};
$hero( $ids['about'], 'About the conference', 'More than an annual event', 'Edu Women in Leadership is a growing professional community committed to women’s leadership, educational transformation, innovation, collaboration, and sustainable school improvement.' );
$hero( $ids['programme'], 'Programme', 'Two days, six tracks', 'The detailed programme is being developed. The tracks below set its direction; confirmed sessions, times and rooms will be published here.' );
$hero( $ids['speakers'], 'Speakers', 'Voices of bold leadership', 'Speakers for 2027 will be announced here as they are confirmed. Want to present? Our Call for Papers is open to leaders, researchers and practitioners.', $link( 'Submit a proposal', $url( 'call-for-papers' ) ) );
$hero( $ids['call-for-papers'], 'Call for Papers', 'Your ideas could shape the future of education.', 'We invite education leaders, researchers, practitioners, academics, and innovators to share research, practical experiences, bold ideas, and solutions that can strengthen education across Africa.' );
$hero( $ids['awards'], 'Edu Women in Leadership Awards 2027', 'Recognising the women shaping the future of education.', 'The Edu Women in Leadership Awards celebrate leadership, innovation, resilience, and meaningful contributions to education. In 2027 we are widening participation across provinces and across Africa.' );
$hero( $ids['partners'], 'Partners & sponsors', 'Partner with us to shape the future of education.', 'Support women’s leadership and educational transformation while connecting with principals, academics, policymakers and innovators from across South Africa and Africa.' );
$hero( $ids['register'], 'Registration', 'Register for 2027', '' );
$hero( $ids['contact'], 'Contact', 'Get in touch', 'Questions about registration, papers, awards or partnerships? Send us a message.' );
$hero( $ids['privacy-notice'], 'Privacy', 'Privacy notice', '' );
$hero( $ids['website-terms'], 'Terms', 'Website terms', '' );

$set = function ( $id, $values ) {
	foreach ( $values as $k => $v ) {
		update_field( $k, $v, $id );
	}
};
$set( $ids['about'], array(
	'about_image'    => $m( 'plenary-session-wide.jpg' ),
	'about_heading'  => 'Why we gather',
	'about_button_1' => $link( 'Register now', $url( 'register' ) ),
	'about_button_2' => $link( 'See the tracks', $url( 'programme' ) ),
	'aud_eyebrow'    => 'Who should attend',
	'aud_heading'    => 'Built for leaders at every level',
) );
$set( $ids['programme'], array(
	'box_1'          => array( 'label' => 'Day 1', 'value' => 'Friday 27 August 2027', 'note' => 'Sessions to be announced' ),
	'box_2'          => array( 'label' => 'Day 2', 'value' => 'Saturday 28 August 2027', 'note' => 'Sessions and the Edu Women in Leadership Awards (time to be confirmed)' ),
	'box_3'          => array( 'label' => 'Venue', 'value' => '{venue}, {city}', 'note' => 'Room details to be confirmed' ),
	'tracks_eyebrow' => 'Conference tracks',
	'tracks_heading' => 'Themes for {year}',
	'prog_button'    => $link( 'Propose a session', $url( 'call-for-papers' ) ),
) );
$set( $ids['speakers'], array( 'nl_eyebrow' => 'Get announcements', 'nl_heading' => 'Be first to hear who is speaking' ) );
$set( $ids['call-for-papers'], array( 'glance_heading' => 'At a glance', 'guide_heading' => 'What makes a strong proposal', 'form_heading' => 'Submit your proposal' ) );
$set( $ids['awards'], array( 'cat_heading' => 'Award categories', 'elig_heading' => 'Eligibility', 'judg_heading' => 'Judging', 'form_heading' => 'Nominate a leader' ) );
$set( $ids['partners'], array( 'opp_eyebrow' => 'Opportunities', 'opp_heading' => 'Ways to get involved', 'conv_heading' => 'Start a conversation' ) );
$set( $ids['register'], array( 'form_heading' => 'Your registration' ) );
$set( $ids['contact'], array( 'box_heading' => '' ) );

$set( $ids['home'], array(
	'hero_kicker'      => 'EduPulse Africa presents',
	'hero_title'       => 'Edu Women in Leadership',
	'hero_title_sub'   => 'CONFERENCE {year}',
	'hero_lede'        => 'Join a dynamic community of women education leaders, school principals, teachers, researchers, policymakers, and innovators working together to shape the future of education in Africa.',
	'hero_button_1'    => $link( 'Register now', $url( 'register' ) ),
	'hero_button_2'    => $link( 'Explore the conference', $url( 'about' ) ),
	'hero_button_3'    => $link( 'Submit a paper', $url( 'call-for-papers' ) ),
	'hero_image'       => $m( 'speaker-on-stage.jpg' ),
	'hero_image_inset' => $m( 'delegates-group-backdrop.jpg' ),
	'cd_eyebrow'       => 'The conference opens in',
	'cd_label'         => '{city}, 27 August {year}',
	'cd_eb_title'      => 'Early bird',
	'cd_eb_text'       => 'Register by {early_bird}',
	'cd_eb_link'       => $link( 'See rates', $url( 'register' ) ),
	'fact_1'           => array( 'value' => '2 days', 'label' => '{dates}' ),
	'fact_2'           => array( 'value' => '{tracks} tracks', 'label' => 'Themes for {year}' ),
	'fact_3'           => array( 'value' => '{city}', 'label' => '{venue}' ),
	'fact_4'           => array( 'value' => 'Awards', 'label' => 'Ceremony 28 August' ),
	'why_eyebrow'      => 'Why attend',
	'why_heading'      => 'Lead boldly. Connect meaningfully. *Transform education.*',
	'why_lede'         => 'Two days built for women who lead in schools, systems and communities, and for those preparing to.',
	'why_image'        => $m( 'delegate-question-from-floor.jpg' ),
	'who_eyebrow'      => 'Who should attend',
	'who_heading'      => 'For everyone shaping how *Africa learns*',
	'who_lede'         => 'From classroom to cabinet, from start-up to university.',
	'tracks_eyebrow'   => 'Conference tracks',
	'tracks_heading'   => 'Six themes for the *{year} conversation*',
	'tracks_lede'      => 'These thematic areas shape the programme. Sessions under each track will be published as the programme is confirmed.',
	'cfp_eyebrow'      => 'Call for Papers',
	'cfp_heading'      => 'Your ideas could shape the *future of education.*',
	'cfp_lede'         => 'We invite education leaders, researchers, practitioners, academics, and innovators to share research, practical experiences, bold ideas, and solutions that can strengthen education across Africa.',
	'cfp_step_1'       => 'Choose the conference track your work speaks to.',
	'cfp_step_2'       => 'Write an abstract of {abstract} and a short biography.',
	'cfp_step_3'       => 'Submit online, with an optional supporting document.',
	'cfp_deadline_label' => 'Deadline',
	'cfp_button_1'     => $link( 'Submit your proposal', $url( 'call-for-papers' ) . '#cfp-form' ),
	'cfp_button_2'     => $link( 'View Call for Papers', $url( 'call-for-papers' ) ),
	'cfp_image'        => $m( 'roundtable-discussion.jpg' ),
	'spk_eyebrow'      => 'Featured speakers',
	'spk_heading'      => 'Voices of *bold leadership*',
	'spk_lede'         => 'The {year} line-up is being finalised. Speakers will appear here as they are confirmed.',
	'spk_button_1'     => $link( 'All speakers', $url( 'speakers' ) ),
	'spk_button_2'     => $link( 'Become a speaker', $url( 'call-for-papers' ) ),
	'aw_eyebrow'       => 'Edu Women in Leadership Awards {year}',
	'aw_heading'       => 'Recognising the women shaping the *future of education.*',
	'aw_lede'          => 'The Edu Women in Leadership Awards celebrate leadership, innovation, resilience, and meaningful contributions to education.',
	'aw_button_1'      => $link( 'Nominate a leader', $url( 'awards' ) . '#nominate' ),
	'aw_button_2'      => $link( 'Explore the awards', $url( 'awards' ) ),
	'aw_image'         => $m( 'delegates-portrait-pair.jpg' ),
	'pt_eyebrow'       => 'Partnerships & sponsorship',
	'pt_heading'       => 'Partner with us to shape the *future of education.*',
	'pt_lede'          => 'Put your organisation alongside the women leading change in South African and African schools, with partnerships designed around your goals.',
	'pt_button'        => $link( 'Explore partnership opportunities', $url( 'partners' ) ),
	'reg_eyebrow'      => 'Registration',
	'reg_heading'      => 'Secure your place *in {city}*',
	'nl_eyebrow'       => 'Join the community',
	'nl_heading'       => 'Stay connected to the *future of education.*',
	'nl_lede'          => 'Hear about speaker announcements, deadlines and EduPulse Africa programmes throughout the year.',
	'nl_image'         => $m( 'plenary-session-wide.jpg' ),
) );

// ---------- menus (stored for the new theme, not the active one) ----------
$menu = function ( $name, $items ) use ( &$log ) {
	$obj = wp_get_nav_menu_object( $name );
	$id  = $obj ? $obj->term_id : wp_create_nav_menu( $name );
	foreach ( (array) wp_get_nav_menu_items( $id ) as $old ) {
		if ( $old ) {
			wp_delete_post( $old->ID, true );
		}
	}
	foreach ( $items as $i => $it ) {
		wp_update_nav_menu_item( $id, 0, array(
			'menu-item-title'     => $it[0],
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $it[1],
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $i + 1,
		) );
	}
	return $id;
};
$locations = array(
	'primary'        => $menu( 'Main menu', array( array( 'Home', $ids['home'] ), array( 'About', $ids['about'] ), array( 'Programme', $ids['programme'] ), array( 'Speakers', $ids['speakers'] ), array( 'Call for Papers', $ids['call-for-papers'] ), array( 'Awards', $ids['awards'] ), array( 'Partners', $ids['partners'] ), array( 'Contact', $ids['contact'] ) ) ),
	'footer_explore' => $menu( 'Explore', array( array( 'About', $ids['about'] ), array( 'Programme', $ids['programme'] ), array( 'Speakers', $ids['speakers'] ), array( 'Contact', $ids['contact'] ) ) ),
	'footer_part'    => $menu( 'Take part', array( array( 'Register', $ids['register'] ), array( 'Call for Papers', $ids['call-for-papers'] ), array( 'Nominate for the Awards', $ids['awards'] ), array( 'Partnership enquiries', $ids['partners'] ) ) ),
	'footer_legal'   => $menu( 'Legal', array( array( 'Privacy policy', $ids['privacy-notice'] ), array( 'Website terms', $ids['website-terms'] ) ) ),
);
$mods                       = (array) get_option( 'theme_mods_edu-women-leadership', array() );
$mods['nav_menu_locations'] = $locations;
$mods['custom_logo']        = $m( 'edupulse-africa-logo.png' );
update_option( 'theme_mods_edu-women-leadership', $mods );

do_action( 'litespeed_purge_all' );
return array( 'pages' => $ids, 'settings' => $settings_id, 'menus' => $locations, 'log' => $log );
