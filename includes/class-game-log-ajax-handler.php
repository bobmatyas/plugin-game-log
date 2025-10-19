<?php
/**
 * File: class-game-log-ajax-handler.php
 *
 * @package Game_Log
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Game Log AJAX Handler class
 *
 * @package Game_Log
 */
class Game_Log_Ajax_Handler {

	/**
	 * IGDB API instance
	 *
	 * @var Game_Log_IGDB_API
	 */
	private $igdb_api;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->igdb_api = new Game_Log_IGDB_API();

		// AJAX actions for logged-in users.
		add_action( 'wp_ajax_game_log_search_games', array( $this, 'search_games' ) );
		add_action( 'wp_ajax_game_log_add_game', array( $this, 'add_game' ) );

		// AJAX actions for non-logged-in users (if needed).
		add_action( 'wp_ajax_nopriv_game_log_search_games', array( $this, 'search_games' ) );
		add_action( 'wp_ajax_nopriv_game_log_add_game', array( $this, 'add_game' ) );
	}

	/**
	 * Search games via AJAX
	 */
	public function search_games(): void {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'game_log_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'game-log' ) );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'game-log' ) );
		}

		$query = sanitize_text_field( wp_unslash( $_POST['query'] ?? '' ) );
		$limit = intval( $_POST['limit'] ?? 20 );

		if ( empty( $query ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Search query is required', 'game-log' ) ) );
		}

		try {
			$games = $this->igdb_api->search_games( $query, $limit );
			wp_send_json_success( array( 'games' => $games ) );
		} catch ( Exception $e ) {
			$message = $e->getMessage();

			// Provide more helpful error messages.
			if ( strpos( $message, 'IGDB API credentials not configured' ) !== false ) {
				$message = esc_html__( 'IGDB API credentials are not configured. Please go to Settings to enter your API credentials.', 'game-log' );
			} elseif ( strpos( $message, 'Failed to get IGDB access token' ) !== false ) {
				$message = esc_html__( 'Failed to authenticate with IGDB API. Please check your credentials.', 'game-log' );
			}

			wp_send_json_error( array( 'message' => $message ) );
		}
	}

	/**
	 * Add game via AJAX
	 */
	public function add_game(): void {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'game_log_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'game-log' ) );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'game-log' ) );
		}

		$game_data_raw = sanitize_text_field( wp_unslash( $_POST['game_data'] ?? '' ) );

		if ( empty( $game_data_raw ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Game data is required', 'game-log' ) ) );
		}

		// Parse JSON string to array.
		// Handle escaped quotes that WordPress might add.
		$decoded_data = $game_data_raw;
		if ( strpos( $decoded_data, '\\"' ) !== false ) {
			$decoded_data = str_replace( '\\"', '"', $decoded_data );
		}

		$game_data  = json_decode( $decoded_data, true );
		$json_error = json_last_error();

		if ( JSON_ERROR_NONE !== $json_error ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid game data format', 'game-log' ) ) );
		}

		// Get game status from POST data.
		$game_status = sanitize_text_field( wp_unslash( $_POST['game_status'] ?? 'wishlist' ) );

		// Sanitize game data.
		$sanitized_data = $this->sanitize_game_data( $game_data );

		// Check if game already exists.
		$existing_game = $this->get_game_by_igdb_id( $sanitized_data['igdb_id'] );
		if ( $existing_game ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Game already exists in your collection', 'game-log' ) ) );
		}

		try {
			$post_id = $this->create_game_post( $sanitized_data, $game_status );

			if ( $post_id ) {
				// Download and set cover image.
				if ( ! empty( $sanitized_data['cover_url'] ) ) {
					$attachment_id = $this->igdb_api->download_game_cover( $sanitized_data['cover_url'], $post_id, $sanitized_data['name'] );
					if ( $attachment_id ) {
						set_post_thumbnail( $post_id, $attachment_id );
					}
				}

				wp_send_json_success(
					array(
						'message'  => esc_html__( 'Game added successfully', 'game-log' ),
						'post_id'  => $post_id,
						'edit_url' => esc_url( get_edit_post_link( $post_id, 'raw' ) ),
					)
				);
			} else {
				wp_send_json_error( array( 'message' => esc_html__( 'Failed to create game post', 'game-log' ) ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => esc_html( $e->getMessage() ) ) );
		}
	}

	/**
	 * Sanitize game data
	 *
	 * @param array $data Game data to sanitize.
	 * @return array Sanitized game data.
	 */
	private function sanitize_game_data( array $data ): array {
		return array(
			'igdb_id'      => intval( $data['id'] ?? 0 ),
			'name'         => sanitize_text_field( $data['name'] ?? '' ),
			'release_date' => sanitize_text_field( $data['release_date'] ?? '' ),
			'cover_url'    => esc_url_raw( $data['cover_url'] ?? '' ),
		);
	}

	/**
	 * Get game by IGDB ID
	 *
	 * @param int $igdb_id IGDB game ID.
	 * @return WP_Post|null Game post or null if not found.
	 */
	private function get_game_by_igdb_id( int $igdb_id ): ?WP_Post {
		$args = array(
			'post_type'      => 'game',
			'post_status'    => 'publish',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- This is a targeted query for a specific game by IGDB ID.
			'meta_query'     => array(
				array(
					'key'     => '_game_igdb_id',
					'value'   => $igdb_id,
					'compare' => '=',
				),
			),
			'posts_per_page' => 1,
		);

		$games = get_posts( $args );
		return ! empty( $games ) ? $games[0] : null;
	}

	/**
	 * Create game post
	 *
	 * @param array  $data Game data.
	 * @param string $status Game status slug.
	 * @return int Post ID.
	 * @throws Exception If post creation fails.
	 */
	private function create_game_post( array $data, string $status = 'wishlist' ): int {
		$post_data = array(
			'post_title'   => sanitize_text_field( $data['name'] ),
			'post_content' => '', // No summary content.
			'post_status'  => 'publish',
			'post_type'    => 'game',
			'post_author'  => get_current_user_id(),
		);

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			throw new Exception( esc_html( $post_id->get_error_message() ) );
		}

		// Save meta fields.
		$meta_fields = array(
			'_game_igdb_id'      => $data['igdb_id'],
			'_game_release_date' => $data['release_date'],
		);

		foreach ( $meta_fields as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		// Set game status.
		$status_term = get_term_by( 'slug', $status, 'game_status' );

		if ( $status_term ) {
			wp_set_object_terms( $post_id, array( $status_term->term_id ), 'game_status' );
		} elseif ( ! term_exists( $status, 'game_status' ) ) {
			// Create the status term if it doesn't exist.
			$status_name = ucfirst( $status );
			wp_insert_term( $status_name, 'game_status', array( 'slug' => $status ) );
			// Try to set it again after creation.
			$status_term = get_term_by( 'slug', $status, 'game_status' );
			if ( $status_term ) {
				wp_set_object_terms( $post_id, array( $status_term->term_id ), 'game_status' );
			}
		}

		return $post_id;
	}
}
