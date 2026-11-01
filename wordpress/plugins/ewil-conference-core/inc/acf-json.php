<?php
/**
 * Keeps the conference content model (ACF post types, taxonomies and field
 * groups) in this plugin's acf-json folder, so it is version-controlled and
 * independent of the active theme.
 */

defined( 'ABSPATH' ) || exit;

/**
 * True when an ACF item belongs to the conference content model.
 *
 * @param array $item ACF field group, post type or taxonomy settings.
 */
function ewil_is_model_item( $item ) {
	$key  = $item['key'] ?? '';
	$slug = $item['post_type'] ?? ( $item['taxonomy'] ?? '' );
	return str_starts_with( $key, 'group_ewil_' ) || str_starts_with( $key, 'post_type_ewil_' ) || str_starts_with( $key, 'taxonomy_ewil_' ) || str_starts_with( (string) $slug, 'ewil_' );
}

add_filter(
	'acf/json/save_paths',
	function ( $paths, $item ) {
		return ewil_is_model_item( $item ) ? array( EWIL_CORE_DIR . 'acf-json' ) : $paths;
	},
	10,
	2
);

add_filter(
	'acf/settings/load_json',
	function ( $paths ) {
		$paths[] = EWIL_CORE_DIR . 'acf-json';
		return $paths;
	}
);

/**
 * Names the JSON files after the item key, e.g. group_ewil_speaker.json.
 */
add_filter(
	'acf/json/save_file_name',
	function ( $filename, $item ) {
		return ewil_is_model_item( $item ) ? sanitize_file_name( $item['key'] ) . '.json' : $filename;
	},
	10,
	2
);
