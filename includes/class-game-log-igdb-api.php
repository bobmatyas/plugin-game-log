<?php
/**
 * File: class-game-log-igdb-api.php
 *
 * @package Game_Log
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Game Log IGDB API class
 *
 * @package Game_Log
 */
class Game_Log_IGDB_API {

	/**
	 * IGDB API Client ID
	 *
	 * @var string
	 */
	private $client_id;

	/**
	 * IGDB API Client Secret
	 *
	 * @var string
	 */
	private $client_secret;

	/**
	 * IGDB API Access Token
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * IGDB API Base URL
	 *
	 * @var string
	 */
	private $base_url = 'https://api.igdb.com/v4';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->client_id     = get_option( 'game_log_igdb_client_id', '' );
		$this->client_secret = get_option( 'game_log_igdb_client_secret', '' );
	}
	/**
	 * Get access token
	 *
	 * @throws Exception If credentials are not configured or token request fails.
	 * @return string Access token.
	 */
	private function get_access_token(): string {
		if ( $this->access_token ) {
			return $this->access_token;
		}

		if ( empty( $this->client_id ) || empty( $this->client_secret ) ) {
			throw new Exception( esc_html__( 'IGDB API credentials not configured', 'game-log' ) );
		}

		$response = wp_remote_post(
			'https://id.twitch.tv/oauth2/token',
			array(
				'body'    => array(
					'client_id'     => $this->client_id,
					'client_secret' => $this->client_secret,
					'grant_type'    => 'client_credentials',
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( esc_html( $response->get_error_message() ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$body          = wp_remote_retrieve_body( $response );

		$data = json_decode( $body, true );

		if ( isset( $data['access_token'] ) ) {
			$this->access_token = $data['access_token'];
			return $this->access_token;
		}

		throw new Exception( esc_html__( 'Failed to get IGDB access token', 'game-log' ) );
	}
	/**
	 * Make API request to IGDB
	 *
	 * @param string $endpoint API endpoint.
	 * @param string $query    API query.
	 * @throws Exception If request fails or returns invalid JSON.
	 * @return array API response data.
	 */
	private function make_request( string $endpoint, string $query = '' ): array {
		$access_token = $this->get_access_token();

		$response = wp_remote_post(
			$this->base_url . '/' . $endpoint,
			array(
				'headers' => array(
					'Client-ID'     => $this->client_id,
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'text/plain',
				),
				'body'    => $query,
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( esc_html( $response->get_error_message() ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$body          = wp_remote_retrieve_body( $response );

		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new Exception( esc_html__( 'Invalid JSON response from IGDB API', 'game-log' ) );
		}

		return $data;
	}

	/**
	 * Search games
	 *
	 * @param string $query Search query.
	 * @param int    $limit Number of results to return.
	 * @return array Array of formatted game data.
	 */
	public function search_games( string $query, int $limit = 20 ): array {
		if ( empty( $query ) ) {
			return array();
		}

		$search_query = 'search "' . $query . '"; fields id,name,summary,first_release_date,platforms.name,genres.name,cover.url; limit ' . $limit . ';';

		try {
			$games = $this->make_request( 'games', $search_query );
			return $this->format_games_data( $games );
		} catch ( Exception $e ) {
			return array();
		}
	}
	/**
	 * Get game details by ID
	 *
	 * @param int $game_id IGDB game ID.
	 * @return array Game data or empty array if not found.
	 */
	public function get_game_details( int $game_id ): array {
		$query = 'fields id,name,summary,first_release_date,platforms.name,genres.name,cover.url; where id = ' . $game_id . ';';

		try {
			$games = $this->make_request( 'games', $query );
			if ( ! empty( $games ) ) {
				return $this->format_games_data( $games )[0];
			}
			return array();
		} catch ( Exception $e ) {
			return array();
		}
	}
	/**
	 * Format games data for consistent output
	 *
	 * @param array $games Raw games data from IGDB API.
	 * @return array Formatted games data.
	 */
	private function format_games_data( array $games ): array {
		$formatted_games = array();

		foreach ( $games as $game ) {
			$formatted_game = array(
				'id'           => $game['id'] ?? 0,
				'name'         => $game['name'] ?? '',
				'summary'      => $game['summary'] ?? '',
				'release_date' => $game['first_release_date'] ?? '',
				'cover_url'    => '',
				'platforms'    => array(),
				'genres'       => array(),
			);

			// Format cover URL.
			if ( isset( $game['cover']['url'] ) ) {
				$formatted_game['cover_url'] = 'https:' . str_replace( 't_thumb', 't_cover_big', $game['cover']['url'] );
			}

			// Format release date.
			if ( $formatted_game['release_date'] ) {
				$formatted_game['release_date'] = gmdate( 'Y-m-d', $formatted_game['release_date'] );
			}

			// Format platforms.
			if ( isset( $game['platforms'] ) && is_array( $game['platforms'] ) ) {
				foreach ( $game['platforms'] as $platform ) {
					if ( isset( $platform['name'] ) ) {
						$formatted_game['platforms'][] = $platform['name'];
					}
				}
			}

			// Format genres.
			if ( isset( $game['genres'] ) && is_array( $game['genres'] ) ) {
				foreach ( $game['genres'] as $genre ) {
					if ( isset( $genre['name'] ) ) {
						$formatted_game['genres'][] = $genre['name'];
					}
				}
			}

			$formatted_games[] = $formatted_game;
		}

		return $formatted_games;
	}
	/**
	 * Download and save game cover image
	 *
	 * @param string $cover_url Cover image URL.
	 * @param int    $post_id   Post ID to attach image to.
	 * @param string $game_title Game title for alt text.
	 * @return int Attachment ID or 0 on failure.
	 */
	public function download_game_cover( string $cover_url, int $post_id, string $game_title = '' ): int {
		if ( empty( $cover_url ) ) {
			return 0;
		}

		// Download image.
		$response = wp_remote_get(
			$cover_url,
			array(
				'timeout'    => 30,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
			)
		);

		if ( is_wp_error( $response ) ) {
			return 0;
		}

		$image_data = wp_remote_retrieve_body( $response );
		if ( empty( $image_data ) ) {
			return 0;
		}

		// Get file extension.
		$file_extension = pathinfo( wp_parse_url( $cover_url, PHP_URL_PATH ), PATHINFO_EXTENSION );
		if ( empty( $file_extension ) ) {
			$file_extension = 'jpg';
		}

		// Create filename.
		$filename = 'game-cover-' . $post_id . '.' . $file_extension;

		// Upload file.
		$upload = wp_upload_bits( $filename, null, $image_data );

		if ( $upload['error'] ) {
			return 0;
		}

		// Create attachment.
		$attachment = array(
			'post_mime_type' => wp_check_filetype( $filename )['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			return 0;
		}

		// Set alt text if game title is provided.
		if ( ! empty( $game_title ) ) {
			$alt_text = sprintf( 'Cover art for %s', sanitize_text_field( $game_title ) );
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
		}

		// Generate attachment metadata.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		return $attachment_id;
	}
}
