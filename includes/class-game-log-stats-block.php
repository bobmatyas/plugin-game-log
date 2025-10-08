<?php
/**
 * Game Log Stats Block class file
 *
 * This file contains the Game_Log_Stats_Block class which handles the Gutenberg block
 * for displaying game statistics on the frontend.
 *
 * @package Game_Log
 * @subpackage Blocks
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Game Log Stats Block class
 *
 * Handles the Gutenberg block for displaying game statistics.
 *
 * @package Game_Log
 * @since 1.0.0
 */
class Game_Log_Stats_Block {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register block category
	 *
	 * @param array $categories Array of block categories.
	 * @param WP_Block_Editor_Context $block_editor_context The current block editor context.
	 * @return array Modified array of block categories.
	 */
	public function register_block_category( array $categories, $block_editor_context ): array {
		// Check if category already exists
		$category_exists = false;
		foreach ( $categories as $category ) {
			if ( isset( $category['slug'] ) && $category['slug'] === 'gamelog' ) {
				$category_exists = true;
				break;
			}
		}

		// Only add if it doesn't exist
		if ( ! $category_exists ) {
			$categories[] = array(
				'slug'  => 'gamelog',
				'title' => __( '🎮 Game Log', 'game-log' ),
			);
		}

		return $categories;
	}

	/**
	 * Register the block
	 */
	public function register_block(): void {
		// Enqueue block assets
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_assets' ) );

		register_block_type(
			GAME_LOG_PLUGIN_DIR . 'blocks/game-stats/block.json',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Enqueue block assets
	 */
	public function enqueue_block_assets(): void {
		wp_enqueue_script(
			'game-log-game-stats-block',
			GAME_LOG_PLUGIN_URL . 'blocks/game-stats/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			GAME_LOG_VERSION,
			true
		);

		wp_enqueue_style(
			'game-log-game-stats-editor',
			GAME_LOG_PLUGIN_URL . 'blocks/game-stats/editor.css',
			array(),
			GAME_LOG_VERSION
		);

		wp_enqueue_style(
			'game-log-game-stats-style',
			GAME_LOG_PLUGIN_URL . 'blocks/game-stats/style.css',
			array(),
			GAME_LOG_VERSION
		);
	}


	/**
	 * Render the block
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	public function render_block( array $attributes ): string {
		$total_games = wp_count_posts( 'game' )->publish;
		$played      = $this->get_games_by_status( 'played' );
		$playing     = $this->get_games_by_status( 'playing' );
		$backlog     = $this->get_games_by_status( 'backlog' );
		$wishlist    = $this->get_games_by_status( 'wishlist' );

		$show_total    = $attributes['showTotal'] ?? true;
		$show_played   = $attributes['showPlayed'] ?? true;
		$show_playing  = $attributes['showPlaying'] ?? true;
		$show_backlog  = $attributes['showBacklog'] ?? true;
		$show_wishlist = $attributes['showWishlist'] ?? true;

		ob_start();
		?>
		<div class="game-log-stats-block">
			<?php if ( $show_total ) : ?>
				<div class="stat-box">
					<h3><?php echo esc_html( $total_games ); ?></h3>
					<p><?php esc_html_e( 'Total Games', 'game-log' ); ?></p>
				</div>
			<?php endif; ?>
			
			<?php if ( $show_played ) : ?>
				<div class="stat-box">
					<h3><?php echo esc_html( count( $played ) ); ?></h3>
					<p><?php esc_html_e( 'Played', 'game-log' ); ?></p>
				</div>
			<?php endif; ?>
			
			<?php if ( $show_playing ) : ?>
				<div class="stat-box">
					<h3><?php echo esc_html( count( $playing ) ); ?></h3>
					<p><?php esc_html_e( 'Playing', 'game-log' ); ?></p>
				</div>
			<?php endif; ?>
			
			<?php if ( $show_backlog ) : ?>
				<div class="stat-box">
					<h3><?php echo esc_html( count( $backlog ) ); ?></h3>
					<p><?php esc_html_e( 'Backlog', 'game-log' ); ?></p>
				</div>
			<?php endif; ?>
			
			<?php if ( $show_wishlist ) : ?>
				<div class="stat-box">
					<h3><?php echo esc_html( count( $wishlist ) ); ?></h3>
					<p><?php esc_html_e( 'Wishlist', 'game-log' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get games by status
	 *
	 * @param string $status_slug The status slug to filter by.
	 * @return array Array of game posts.
	 */
	private function get_games_by_status( string $status_slug ): array {
		$args = array(
			'post_type'      => 'game',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- This is necessary for filtering games by status
			'tax_query'      => array(
				array(
					'taxonomy' => 'game_status',
					'field'    => 'slug',
					'terms'    => $status_slug,
				),
			),
		);

		$games = new WP_Query( $args );
		return $games->posts;
	}
}
