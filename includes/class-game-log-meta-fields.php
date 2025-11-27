<?php
/**
 * File: class-game-log-meta-fields.php
 *
 * @package Game_Log
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Game Log Meta Fields class
 *
 * @package Game_Log
 */
class Game_Log_Meta_Fields {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_meta_fields' ) );
	}

	/**
	 * Register meta fields for the game post type
	 */
	public function register_meta_fields(): void {
		$meta_fields = array(
			'game_rating'       => array(
				'type'              => 'number',
				'description'       => __( 'Game rating from 1 to 10', 'mode7-game-log' ),
				'single'            => true,
				'sanitize_callback' => array( $this, 'sanitize_rating' ),
				'auth_callback'     => array( $this, 'meta_auth_callback' ),
				'show_in_rest'      => true,
			),
			'game_release_date' => array(
				'type'              => 'string',
				'description'       => __( 'Game release date', 'mode7-game-log' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => array( $this, 'meta_auth_callback' ),
				'show_in_rest'      => true,
			),
			'game_igdb_id'      => array(
				'type'              => 'string',
				'description'       => __( 'IGDB game ID', 'mode7-game-log' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => array( $this, 'meta_auth_callback' ),
				'show_in_rest'      => false,
			),
		);

		foreach ( $meta_fields as $meta_key => $args ) {
			register_meta( 'post', $meta_key, $args );
		}
	}
	/**
	 * Sanitize rating field
	 *
	 * @param mixed $value Rating value to sanitize.
	 * @return float|string Sanitized rating or empty string if invalid.
	 */
	public function sanitize_rating( $value ) {
		$value = floatval( $value );
		return ( $value >= 1 && $value <= 10 ) ? $value : '';
	}

	/**
	 * Meta field authorization callback
	 *
	 * @param bool   $allowed   Whether the user is allowed to edit the meta.
	 * @param string $meta_key  Meta key.
	 * @param int    $object_id Object ID.
	 * @param int    $user_id   User ID.
	 * @param string $cap       Capability.
	 * @param array  $caps      Capabilities.
	 * @return bool Whether the user can edit the meta.
	 */
	public function meta_auth_callback( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
		// Unused parameters are required by WordPress hook signature.
		unset( $allowed, $meta_key, $user_id, $cap, $caps );
		return current_user_can( 'edit_post', $object_id );
	}
}
