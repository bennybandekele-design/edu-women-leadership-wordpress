<?php
/**
 * Plugin Name:       EWIL Conference Core
 * Description:       Content model, conference settings, website forms and submissions for the Edu Women in Leadership Conference website. Works with any theme; the Edu Women in Leadership theme provides the presentation.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            EduPulse Africa
 * Text Domain:       ewil
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'EWIL_CORE_VERSION', '1.0.0' );
define( 'EWIL_CORE_FILE', __FILE__ );
define( 'EWIL_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'EWIL_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once EWIL_CORE_DIR . 'inc/acf-json.php';
require_once EWIL_CORE_DIR . 'inc/helpers.php';
require_once EWIL_CORE_DIR . 'inc/settings.php';
require_once EWIL_CORE_DIR . 'inc/admin.php';
require_once EWIL_CORE_DIR . 'inc/forms.php';
require_once EWIL_CORE_DIR . 'inc/submit.php';
require_once EWIL_CORE_DIR . 'inc/submissions.php';

register_activation_hook( __FILE__, 'ewil_core_activate' );

/**
 * Prepares the private upload folder and flushes rewrite rules.
 */
function ewil_core_activate() {
	ewil_private_upload_dir();
	flush_rewrite_rules();
}

/**
 * Warns in wp-admin when ACF is missing, because the content model depends on it.
 */
add_action( 'admin_notices', function () {
	if ( function_exists( 'acf_get_setting' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>' . esc_html__( 'EWIL Conference Core needs Advanced Custom Fields to be active. Speakers, tracks, rates and the conference settings are defined in ACF.', 'ewil' ) . '</p></div>';
} );
