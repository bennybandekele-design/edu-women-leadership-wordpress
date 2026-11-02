<?php
/**
 * Stored form submissions: a private post type with a Form taxonomy, an admin
 * list filterable by form, a detail view, CSV export, protected file downloads,
 * email notifications and optional automatic clean-up.
 *
 * These are registered in code rather than in ACF on purpose: submissions are
 * records produced by this plugin, not content that editors model or write.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'ewil_submission',
			array(
				'labels'          => array(
					'name'          => __( 'Submissions', 'ewil' ),
					'singular_name' => __( 'Submission', 'ewil' ),
					'menu_name'     => __( 'Submissions', 'ewil' ),
					'all_items'     => __( 'All submissions', 'ewil' ),
					'edit_item'     => __( 'Submission', 'ewil' ),
					'search_items'  => __( 'Search submissions', 'ewil' ),
					'not_found'     => __( 'No submissions yet.', 'ewil' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'show_in_rest'    => false,
				'menu_position'   => 57,
				'menu_icon'       => 'dashicons-email-alt',
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
				'rewrite'         => false,
				'query_var'       => false,
			)
		);
		register_taxonomy(
			'ewil_form',
			'ewil_submission',
			array(
				'labels'            => array(
					'name'          => __( 'Forms', 'ewil' ),
					'singular_name' => __( 'Form', 'ewil' ),
				),
				'public'            => false,
				'show_ui'           => false,
				'show_admin_column' => true,
				'rewrite'           => false,
				'query_var'         => false,
			)
		);
	}
);

/**
 * Stores one submission.
 *
 * @param string $kind   Form name.
 * @param array  $data   Field name => value.
 * @param array  $labels Field name => label.
 * @return int Post ID, or 0 on failure.
 */
function ewil_save_submission( $kind, $data, $labels ) {
	$who = $data['name'] ?? ( $data['nominator_name'] ?? ( $data['email'] ?? '' ) );
	if ( 'registration' === $kind && (int) ( $data['delegates'] ?? 1 ) > 1 ) {
		/* translators: 1: contact name, 2: number of delegates */
		$who = sprintf( __( '%1$s (%2$d delegates)', 'ewil' ), $who, (int) $data['delegates'] );
	}
	if ( 'nomination' === $kind && ! empty( $data['nominee_name'] ) ) {
		/* translators: %s: nominee name */
		$who = sprintf( __( 'Nomination: %s', 'ewil' ), $data['nominee_name'] );
	}
	$id = wp_insert_post(
		array(
			'post_type'   => 'ewil_submission',
			'post_status' => 'publish',
			'post_title'  => $who ? $who : ewil_form_kinds()[ $kind ],
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	update_post_meta( $id, '_ewil_data', $data );
	update_post_meta( $id, '_ewil_labels', $labels );
	wp_set_object_terms( $id, $kind, 'ewil_form' );
	return (int) $id;
}

/**
 * Emails the organisers about a new submission.
 *
 * @param int    $id     Submission ID.
 * @param string $kind   Form name.
 * @param array  $data   Field values.
 * @param array  $labels Field labels.
 */
function ewil_notify_submission( $id, $kind, $data, $labels ) {
	$to = ewil_setting( 'notify_email' );
	$to = $to ? array_filter( array_map( 'trim', explode( ',', $to ) ), 'is_email' ) : array( get_option( 'admin_email' ) );
	if ( ! $to ) {
		return;
	}
	$lines = array();
	foreach ( $data as $name => $value ) {
		if ( 'document' === $name ) {
			$value = is_array( $value ) ? $value['name'] . ' (' . __( 'download it from the submission in wp-admin', 'ewil' ) . ')' : '';
		} elseif ( is_bool( $value ) ) {
			$value = $value ? __( 'Yes', 'ewil' ) : __( 'No', 'ewil' );
		}
		$label   = $labels[ $name ] ?? $name;
		$label   = mb_strlen( $label ) > 60 ? ucfirst( str_replace( '_', ' ', $name ) ) : $label;
		$lines[] = $label . ': ' . $value;
	}
	$lines[] = '';
	$lines[] = __( 'View in wp-admin:', 'ewil' ) . ' ' . admin_url( 'post.php?post=' . $id . '&action=edit' );

	$reply = $data['email'] ?? ( $data['nominator_email'] ?? '' );
	$hdrs  = $reply && is_email( $reply ) ? array( 'Reply-To: ' . $reply ) : array();

	/* translators: 1: form name, 2: site name */
	wp_mail( $to, sprintf( __( 'New website submission: %1$s (%2$s)', 'ewil' ), ewil_form_kinds()[ $kind ], wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ), implode( "\n", $lines ), $hdrs );
}

/**
 * Private folder for uploaded documents, blocked from direct web access.
 */
function ewil_private_upload_dir() {
	$uploads = wp_upload_dir( null, false );
	$dir     = trailingslashit( $uploads['basedir'] ) . 'ewil-private';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	if ( ! file_exists( $dir . '/index.php' ) ) {
		file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $dir;
}

/**
 * Moves an uploaded file into the private folder under a random name.
 *
 * @param array $file Entry from $_FILES.
 * @return array|WP_Error Stored file details.
 */
function ewil_store_private_file( $file ) {
	$dir  = ewil_private_upload_dir();
	$ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
	$name = gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 12, false ) . '.' . $ext;
	if ( ! is_uploaded_file( $file['tmp_name'] ) || ! move_uploaded_file( $file['tmp_name'], $dir . '/' . $name ) ) {
		return new WP_Error( 'ewil_upload', 'Upload failed' );
	}
	return array(
		'name' => sanitize_file_name( $file['name'] ),
		'file' => $name,
		'size' => (int) $file['size'],
	);
}

/**
 * Lets organisers download an uploaded document.
 */
add_action(
	'admin_post_ewil_download',
	function () {
		$id = absint( $_GET['submission'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		check_admin_referer( 'ewil_download_' . $id );
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_die( esc_html__( 'You are not allowed to download this file.', 'ewil' ) );
		}
		$data = get_post_meta( $id, '_ewil_data', true );
		$doc  = $data['document'] ?? null;
		$path = $doc ? ewil_private_upload_dir() . '/' . basename( $doc['file'] ) : '';
		if ( ! $path || ! is_file( $path ) ) {
			wp_die( esc_html__( 'The file is no longer available.', 'ewil' ) );
		}
		nocache_headers();
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . rawurlencode( $doc['name'] ) . '"' );
		header( 'Content-Length: ' . filesize( $path ) );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

/**
 * Formats a stored value for display or export.
 *
 * @param string $name  Field name.
 * @param mixed  $value Stored value.
 * @param int    $id    Submission ID (for download links).
 * @param bool   $html  Whether to return HTML.
 */
function ewil_format_value( $name, $value, $id = 0, $html = false ) {
	if ( is_bool( $value ) ) {
		return $value ? __( 'Yes', 'ewil' ) : __( 'No', 'ewil' );
	}
	if ( 'document' === $name && is_array( $value ) ) {
		if ( ! $html ) {
			return $value['name'];
		}
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=ewil_download&submission=' . $id ), 'ewil_download_' . $id );
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $value['name'] ) . '</a> (' . esc_html( size_format( $value['size'] ) ) . ')';
	}
	if ( 'rate' === $name ) {
		$rate = get_page_by_path( (string) $value, OBJECT, 'ewil_rate' );
		return $rate ? get_the_title( $rate ) . ' (' . get_field( 'price', $rate->ID ) . ')' : (string) $value;
	}
	return $html ? nl2br( esc_html( (string) $value ) ) : (string) $value;
}

/**
 * Detail view of a submission.
 */
add_action(
	'add_meta_boxes_ewil_submission',
	function () {
		remove_meta_box( 'submitdiv', 'ewil_submission', 'side' );
		add_meta_box(
			'ewil_submission_data',
			__( 'Submitted details', 'ewil' ),
			function ( $post ) {
				$data   = (array) get_post_meta( $post->ID, '_ewil_data', true );
				$labels = (array) get_post_meta( $post->ID, '_ewil_labels', true );
				$terms  = wp_get_object_terms( $post->ID, 'ewil_form', array( 'fields' => 'slugs' ) );
				$kind   = $terms[0] ?? '';
				echo '<p><strong>' . esc_html( ewil_form_kinds()[ $kind ] ?? $kind ) . '</strong> · ' . esc_html( get_the_date( 'j F Y, H:i', $post ) ) . '</p>';
				echo '<table class="widefat striped"><tbody>';
				foreach ( $data as $name => $value ) {
					echo '<tr><th style="width:30%">' . esc_html( $labels[ $name ] ?? $name ) . '</th><td>' . wp_kses_post( ewil_format_value( $name, $value, $post->ID, true ) ) . '</td></tr>';
				}
				echo '</tbody></table>';
				echo '<p><a class="button button-link-delete" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">' . esc_html__( 'Move to bin', 'ewil' ) . '</a></p>';
			},
			'ewil_submission',
			'normal',
			'high'
		);
	}
);

add_action(
	'admin_head',
	function () {
		$screen = get_current_screen();
		if ( $screen && 'ewil_submission' === $screen->post_type && 'post' === $screen->base ) {
			echo '<style>#titlediv{pointer-events:none}#titlediv #title{background:transparent;border:0;box-shadow:none}#post-body.columns-2{margin-right:0}#postbox-container-1{display:none}</style>';
		}
	}
);

/**
 * List screen: form filter, email column, CSV export button.
 */
add_filter(
	'manage_ewil_submission_posts_columns',
	function ( $columns ) {
		return array(
			'cb'             => $columns['cb'],
			'title'          => __( 'Name', 'ewil' ),
			'taxonomy-ewil_form' => __( 'Form', 'ewil' ),
			'ewil_email'     => __( 'Email', 'ewil' ),
			'date'           => __( 'Received', 'ewil' ),
		);
	}
);

add_action(
	'manage_ewil_submission_posts_custom_column',
	function ( $column, $post_id ) {
		if ( 'ewil_email' === $column ) {
			$data  = (array) get_post_meta( $post_id, '_ewil_data', true );
			$email = $data['email'] ?? ( $data['nominator_email'] ?? '' );
			echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '—';
		}
	},
	10,
	2
);

add_filter(
	'post_row_actions',
	function ( $actions, $post ) {
		if ( 'ewil_submission' === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'] );
			if ( isset( $actions['edit'] ) ) {
				$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html__( 'View', 'ewil' ) . '</a>';
			}
		}
		return $actions;
	},
	10,
	2
);

add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( 'ewil_submission' !== $post_type ) {
			return;
		}
		$current = sanitize_key( $_GET['ewil_form'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
		echo '<select name="ewil_form"><option value="">' . esc_html__( 'All forms', 'ewil' ) . '</option>';
		foreach ( ewil_form_kinds() as $kind => $label ) {
			$term  = get_term_by( 'slug', $kind, 'ewil_form' );
			$count = $term ? $term->count : 0;
			echo '<option value="' . esc_attr( $kind ) . '"' . selected( $current, $kind, false ) . '>' . esc_html( $label . ' (' . $count . ')' ) . '</option>';
		}
		echo '</select>';
	}
);

add_action(
	'manage_posts_extra_tablenav',
	function ( $which ) {
		$screen = get_current_screen();
		if ( 'top' !== $which || ! $screen || 'ewil_submission' !== $screen->post_type ) {
			return;
		}
		$kind = sanitize_key( $_GET['ewil_form'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
		$url  = wp_nonce_url( admin_url( 'admin-post.php?action=ewil_export&form=' . $kind ), 'ewil_export' );
		echo '<div class="alignleft actions"><a class="button" href="' . esc_url( $url ) . '">' . esc_html( $kind ? sprintf( /* translators: %s: form name */ __( 'Export %s (CSV)', 'ewil' ), ewil_form_kinds()[ $kind ] ?? $kind ) : __( 'Export all (CSV)', 'ewil' ) ) . '</a></div>';
	}
);

/**
 * CSV export of one form, or all forms.
 */
add_action(
	'admin_post_ewil_export',
	function () {
		check_admin_referer( 'ewil_export' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You are not allowed to export submissions.', 'ewil' ) );
		}
		$kind = sanitize_key( $_GET['form'] ?? '' );
		$args = array(
			'post_type'   => 'ewil_submission',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		);
		if ( $kind ) {
			$args['tax_query'] = array( array( 'taxonomy' => 'ewil_form', 'field' => 'slug', 'terms' => $kind ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		$posts  = get_posts( $args );
		$cols   = array();
		$labels = array();
		foreach ( $posts as $p ) {
			foreach ( (array) get_post_meta( $p->ID, '_ewil_labels', true ) as $name => $label ) {
				if ( ! isset( $cols[ $name ] ) ) {
					$cols[ $name ]   = true;
					$labels[ $name ] = mb_strlen( $label ) > 60 ? ucfirst( str_replace( '_', ' ', $name ) ) : $label;
				}
			}
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="submissions-' . ( $kind ? $kind : 'all' ) . '-' . gmdate( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // Excel-friendly UTF-8.
		fputcsv( $out, array_merge( array( __( 'Received', 'ewil' ), __( 'Form', 'ewil' ) ), array_values( $labels ) ) );
		foreach ( $posts as $p ) {
			$data  = (array) get_post_meta( $p->ID, '_ewil_data', true );
			$terms = wp_get_object_terms( $p->ID, 'ewil_form', array( 'fields' => 'slugs' ) );
			$row   = array( get_the_date( 'Y-m-d H:i', $p ), ewil_form_kinds()[ $terms[0] ?? '' ] ?? '' );
			foreach ( array_keys( $labels ) as $name ) {
				$value = array_key_exists( $name, $data ) ? ewil_format_value( $name, $data[ $name ] ) : '';
				$row[] = preg_match( '/^[=+\-@]/', $value ) ? "'" . $value : $value; // Prevent spreadsheet formula injection.
			}
			fputcsv( $out, $row );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

/**
 * Deleting a submission also deletes its uploaded document.
 */
add_action(
	'before_delete_post',
	function ( $post_id ) {
		if ( 'ewil_submission' !== get_post_type( $post_id ) ) {
			return;
		}
		$data = get_post_meta( $post_id, '_ewil_data', true );
		if ( ! empty( $data['document']['file'] ) ) {
			wp_delete_file( ewil_private_upload_dir() . '/' . basename( $data['document']['file'] ) );
		}
	}
);

/**
 * Optional clean-up: delete submissions older than the retention period set in
 * Conference Settings (0 keeps them).
 */
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'ewil_retention' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'ewil_retention' );
		}
	}
);

add_action(
	'ewil_retention',
	function () {
		$months = (int) ewil_setting( 'retention_months' );
		if ( $months <= 0 ) {
			return;
		}
		$old = get_posts(
			array(
				'post_type'   => 'ewil_submission',
				'post_status' => 'any',
				'numberposts' => 200,
				'fields'      => 'ids',
				'date_query'  => array( array( 'before' => $months . ' months ago' ) ),
			)
		);
		foreach ( $old as $id ) {
			wp_delete_post( $id, true );
		}
	}
);
