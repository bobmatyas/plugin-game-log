<?php
/**
 * File: class-game-log-patterns.php
 *
 * @package Game_Log
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Game Log Patterns class
 *
 * @package Game_Log
 */
class Game_Log_Patterns {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_patterns' ) );
		add_action( 'init', array( $this, 'register_pattern_category' ) );
	}

	/**
	 * Register pattern category
	 */
	public function register_pattern_category(): void {
		register_block_pattern_category(
			'gamelog',
			array(
				'label' => __( '🎮 Mode7 Game Log', 'mode7-game-log' ),
			)
		);
	}

	/**
	 * Register all patterns
	 */
	public function register_patterns(): void {
		// Ensure terms exist before registering patterns.
		$taxonomy = new Game_Log_Taxonomy();
		$taxonomy->create_default_terms();

		// Register patterns dynamically with correct term IDs.
		$this->register_wishlist_pattern();
		$this->register_played_pattern();
		$this->register_playing_pattern();
		$this->register_backlog_pattern();
	}

	/**
	 * Get term ID by slug
	 */
	private function get_term_id_by_slug( string $slug ): ?int {
		$term = get_term_by( 'slug', $slug, 'game_status' );
		return $term ? $term->term_id : null;
	}

	/**
	 * Register wishlist pattern
	 */
	private function register_wishlist_pattern(): void {
		$term_id = $this->get_term_id_by_slug( 'wishlist' );
		if ( ! $term_id ) {
			return;
		}

		register_block_pattern(
			'gamelog/game-log-query-wishlist',
			array(
				'title'       => __( '🎮 Mode7 Game Log: Wishlist', 'mode7-game-log' ),
				'description' => __( 'Displays a list of games in your wishlist.', 'mode7-game-log' ),
				'categories'  => array( 'gamelog' ),
				'content'     => '
					<!-- wp:query {"queryId":7,"query":{"perPage":10,"pages":0,"offset":0,"postType":"game","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"game_status":[' . $term_id . ']},"parents":[],"format":[]},"className":"game__log__container"} -->
					<div class="wp-block-query game__log__container">
						<!-- wp:post-template {"className":"game__log__container__game"} -->
							<!-- wp:post-featured-image /-->
							<!-- wp:post-title /-->
						<!-- /wp:post-template -->

						<!-- wp:query-pagination -->
							<!-- wp:query-pagination-previous /-->
							<!-- wp:query-pagination-numbers /-->
							<!-- wp:query-pagination-next /-->
						<!-- /wp:query-pagination -->

						<!-- wp:query-no-results -->
							<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
								<p>No wishlist games found.</p>
							<!-- /wp:paragraph -->
						<!-- /wp:query-no-results -->
					</div>
					<!-- /wp:query -->
				',
			)
		);
	}

	/**
	 * Register played pattern
	 */
	private function register_played_pattern(): void {
		$term_id = $this->get_term_id_by_slug( 'played' );
		if ( ! $term_id ) {
			return;
		}

		register_block_pattern(
			'gamelog/game-log-query-played',
			array(
				'title'       => __( '🎮 Mode7 Game Log: Played', 'mode7-game-log' ),
				'description' => __( 'Displays a list of played games.', 'mode7-game-log' ),
				'categories'  => array( 'gamelog' ),
				'content'     => '
					<!-- wp:query {"queryId":9,"query":{"perPage":10,"pages":0,"offset":0,"postType":"game","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"game_status":[' . $term_id . ']},"parents":[],"format":[]},"className":"game__log__container"} -->
					<div class="wp-block-query game__log__container">
						<!-- wp:post-template {"className":"game__log__container__game"} -->
							<!-- wp:post-featured-image /-->
							<!-- wp:post-title /-->
						<!-- /wp:post-template -->

						<!-- wp:query-pagination -->
							<!-- wp:query-pagination-previous /-->
							<!-- wp:query-pagination-numbers /-->
							<!-- wp:query-pagination-next /-->
						<!-- /wp:query-pagination -->

						<!-- wp:query-no-results -->
							<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
								<p>No played games found.</p>
							<!-- /wp:paragraph -->
						<!-- /wp:query-no-results -->
					</div>
					<!-- /wp:query -->
				',
			)
		);
	}

	/**
	 * Register playing pattern
	 */
	private function register_playing_pattern(): void {
		$term_id = $this->get_term_id_by_slug( 'playing' );
		if ( ! $term_id ) {
			return;
		}

		register_block_pattern(
			'gamelog/game-log-query-playing',
			array(
				'title'       => __( '🎮 Mode7 Game Log: Playing', 'mode7-game-log' ),
				'description' => __( 'Displays a list of currently playing games.', 'mode7-game-log' ),
				'categories'  => array( 'gamelog' ),
				'content'     => '
					<!-- wp:query {"queryId":6,"query":{"perPage":10,"pages":0,"offset":0,"postType":"game","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"game_status":[' . $term_id . ']},"parents":[],"format":[]},"className":"game__log__container"} -->
					<div class="wp-block-query game__log__container">
						<!-- wp:post-template {"className":"game__log__container__game"} -->
							<!-- wp:post-featured-image /-->
							<!-- wp:post-title /-->
						<!-- /wp:post-template -->

						<!-- wp:query-pagination -->
							<!-- wp:query-pagination-previous /-->
							<!-- wp:query-pagination-numbers /-->
							<!-- wp:query-pagination-next /-->
						<!-- /wp:query-pagination -->

						<!-- wp:query-no-results -->
							<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
								<p>No playing games found.</p>
							<!-- /wp:paragraph -->
						<!-- /wp:query-no-results -->
					</div>
					<!-- /wp:query -->
				',
			)
		);
	}

	/**
	 * Register backlog pattern
	 */
	private function register_backlog_pattern(): void {
		$term_id = $this->get_term_id_by_slug( 'backlog' );
		if ( ! $term_id ) {
			return;
		}

		register_block_pattern(
			'gamelog/game-log-query-backlog',
			array(
				'title'       => __( '🎮 Mode7 Game Log: Backlog', 'mode7-game-log' ),
				'description' => __( 'Displays a list of games in your backlog.', 'mode7-game-log' ),
				'categories'  => array( 'gamelog' ),
				'content'     => '
					<!-- wp:query {"queryId":8,"query":{"perPage":10,"pages":0,"offset":0,"postType":"game","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"game_status":[' . $term_id . ']},"parents":[],"format":[]},"className":"game__log__container"} -->
					<div class="wp-block-query game__log__container">
						<!-- wp:post-template {"className":"game__log__container__game"} -->
							<!-- wp:post-featured-image /-->
							<!-- wp:post-title /-->
						<!-- /wp:post-template -->

						<!-- wp:query-pagination -->
							<!-- wp:query-pagination-previous /-->
							<!-- wp:query-pagination-numbers /-->
							<!-- wp:query-pagination-next /-->
						<!-- /wp:query-pagination -->

						<!-- wp:query-no-results -->
							<!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
								<p>No backlog games found.</p>
							<!-- /wp:paragraph -->
						<!-- /wp:query-no-results -->
					</div>
					<!-- /wp:query -->
				',
			)
		);
	}
}
