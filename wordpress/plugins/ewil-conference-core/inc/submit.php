<?php
/**
 * Receives form submissions: POST /wp-json/ewil/v1/submit/{form}.
 * Re-checks everything on the server, filters spam, stores the submission and
 * emails the organisers.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'ewil/v1',
			'/submit/(?P<kind>[a-z]+)',
			array(
				'methods'             => 'POST',
				'callback'            => 'ewil_handle_submission',
				'permission_callback' => '__return_true', // Public forms; protected by the spam checks below.
				'args'                => array(
					'kind' => array(
						'validate_callback' => function ( $kind ) {
							return isset( ewil_form_kinds()[ $kind ] );
						},
					),
				),
			)
		);
	}
);

/**
 * Error response in the shape the form script expects.
 *
 * @param string $message Message shown under the form.
 * @param int    $status  HTTP status.
 * @param array  $errors  Field name => message.
 */
function ewil_submit_error( $message, $status = 400, $errors = array() ) {
	return new WP_REST_Response(
		array(
			'ok'      => false,
			'message' => $message,
			'errors'  => (object) $errors,
		),
		$status
	);
}

/**
 * Counts words the same way as the browser check.
 *
 * @param string $text Text.
 */
function ewil_word_count( $text ) {
	$text = trim( (string) $text );
	return '' === $text ? 0 : count( preg_split( '/\s+/u', $text ) );
}

/**
 * Handles one submission.
 *
 * @param WP_REST_Request $request Request.
 */
function ewil_handle_submission( WP_REST_Request $request ) {
	$kind   = $request['kind'];
	$params = $request->get_body_params();

	// Spam: hidden field must stay empty, and the form must have been open for a few seconds.
	$ts = (int) ( $params['_ewil_ts'] ?? 0 );
	if ( ! empty( $params['website'] ) || ! $ts || ( time() - (int) floor( $ts / 1000 ) ) < 3 ) {
		return ewil_submit_error( __( 'Please wait a moment and try again.', 'ewil' ), 400 );
	}

	// Rate limit: 8 submissions per 10 minutes per visitor.
	$ip_key = 'ewil_rl_' . md5( ( $_SERVER['REMOTE_ADDR'] ?? '' ) . wp_salt( 'nonce' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 8 ) {
		return ewil_submit_error( __( 'Too many submissions in a short time. Please try again in a few minutes.', 'ewil' ), 429 );
	}
	set_transient( $ip_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	if ( 'nomination' === $kind ) {
		$now   = time();
		$open  = strtotime( ewil_iso( ewil_setting( 'awards_open' ) ) );
		$close = strtotime( ewil_iso( ewil_setting( 'awards_close' ) ) );
		if ( ( $open && $now < $open ) || ( $close && $now > $close ) ) {
			return ewil_submit_error( __( 'Nominations are not open at the moment.', 'ewil' ), 403 );
		}
	}

	list( , $builder ) = ewil_build_form( $kind );
	$data              = array();
	$labels            = array();
	$errors            = array();

	foreach ( $builder->fields as $name => $f ) {
		$labels[ $name ] = $f['label'];
		if ( 'file' === $f['type'] ) {
			continue;
		}
		$value = isset( $params[ $name ] ) ? (string) wp_unslash( $params[ $name ] ) : '';
		$value = 'textarea' === $f['type'] ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
		$value = mb_substr( $value, 0, 8000 );

		if ( $f['req'] && '' === trim( $value ) ) {
			$errors[ $name ] = 'select' === $f['type'] || 'radio' === $f['type'] ? sprintf( __( 'Choose %s.', 'ewil' ), mb_strtolower( $f['label'] ) ) : sprintf( __( 'Enter %s.', 'ewil' ), mb_strtolower( $f['label'] ) );
		} elseif ( 'email' === $f['type'] && '' !== $value && ! is_email( $value ) ) {
			$errors[ $name ] = __( 'Enter an email address like name@school.co.za.', 'ewil' );
		} elseif ( in_array( $f['type'], array( 'select', 'radio' ), true ) && '' !== $value && ! in_array( $value, $f['options'], true ) ) {
			$errors[ $name ] = sprintf( __( 'Choose %s.', 'ewil' ), mb_strtolower( $f['label'] ) );
		} elseif ( ! empty( $f['min'] ) && '' !== trim( $value ) ) {
			$n = ewil_word_count( $value );
			if ( $n < $f['min'] || $n > $f['max'] ) {
				/* translators: 1: minimum words, 2: maximum words, 3: current count */
				$errors[ $name ] = sprintf( __( 'Use %1$s–%2$s words (currently %3$s).', 'ewil' ), $f['min'], $f['max'], $n );
			}
		}
		$data[ $name ] = $value;
	}

	foreach ( $builder->consents as $name => $c ) {
		$labels[ $name ] = $c['label'];
		$data[ $name ]   = ! empty( $params[ $name ] ) && 'false' !== $params[ $name ];
		if ( $c['req'] && ! $data[ $name ] ) {
			$errors[ $name ] = __( 'Tick the consent box to continue.', 'ewil' );
		}
	}

	// Optional supporting document (Call for Papers).
	$files = $request->get_file_params();
	$file  = null;
	if ( isset( $builder->fields['document'] ) && ! empty( $files['document']['name'] ) ) {
		$file    = $files['document'];
		$allowed = array_map( 'trim', explode( ',', strtolower( ewil_setting( 'cfp_file_types' ) ) ) );
		$ext     = '.' . strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		$max     = (float) ewil_setting( 'cfp_max_mb' ) * 1048576;
		$check   = wp_check_filetype( $file['name'] );
		if ( UPLOAD_ERR_OK !== (int) $file['error'] ) {
			$errors['document'] = __( 'The file could not be uploaded. Please try again.', 'ewil' );
		} elseif ( ! in_array( $ext, $allowed, true ) || ! $check['ext'] ) {
			/* translators: %s: allowed file types */
			$errors['document'] = sprintf( __( 'Upload a %s file.', 'ewil' ), ewil_setting( 'cfp_file_label' ) );
		} elseif ( $file['size'] > $max ) {
			/* translators: %s: size in MB */
			$errors['document'] = sprintf( __( 'Choose a file smaller than %s MB.', 'ewil' ), ewil_setting( 'cfp_max_mb' ) );
		}
	}

	if ( $errors ) {
		return ewil_submit_error( __( 'Some fields need your attention.', 'ewil' ), 422, $errors );
	}

	$note = '';
	if ( $file ) {
		$stored = ewil_store_private_file( $file );
		if ( is_wp_error( $stored ) ) {
			return ewil_submit_error( __( 'The file could not be saved. Please try again, or submit without it and the team will ask for it.', 'ewil' ), 500 );
		}
		$data['document'] = $stored;
	}

	$id = ewil_save_submission( $kind, $data, $labels );
	if ( ! $id ) {
		return ewil_submit_error( __( 'Something went wrong saving your details. Please try again.', 'ewil' ), 500 );
	}
	ewil_notify_submission( $id, $kind, $data, $labels );

	return new WP_REST_Response(
		array(
			'ok'      => true,
			'message' => ewil_setting( 'msg_' . $kind ),
			'note'    => $note,
		),
		200
	);
}
