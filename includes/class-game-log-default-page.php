<?php
/**
 * Game Log Default Page class file
 *
 * This file contains the Game_Log_Default_Page class which handles the creation
 * and management of the default game-log page with stats block and patterns.
 *
 * @package Game_Log
 * @subpackage Default_Page
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Game Log Default Page class
 *
 * Handles the creation and management of the default game-log page.
 *
 * @package Game_Log
 * @since 1.0.0
 */
class Game_Log_Default_Page {

	/**
	 * The slug for the default page
	 */
	const PAGE_SLUG = 'game-log';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_generate_page_action' ) );
	}

	/**
	 * Generate the default game-log page
	 *
	 * @return int|WP_Error The page ID on success, WP_Error on failure.
	 */
	public function generate_default_page() {
		// Check if page already exists.
		$existing_page = get_page_by_path( self::PAGE_SLUG );
		if ( $existing_page ) {
			return new WP_Error( 'page_exists', __( 'Game Log page already exists.', 'game-log' ) );
		}

		// Create the page content with game stats block and patterns.
		$page_content = $this->get_default_page_content();

		// Create the page.
		$page_data = array(
			'post_title'   => __( 'Game Log', 'game-log' ),
			'post_content' => $page_content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_name'    => self::PAGE_SLUG,
			'post_author'  => get_current_user_id(),
		);

		$page_id = wp_insert_post( $page_data );

		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		// Set page template if needed.
		update_post_meta( $page_id, '_wp_page_template', 'default' );

		return $page_id;
	}

	/**
	 * Get the default page content with game stats block and patterns
	 *
	 * @return string The page content.
	 */
	private function get_default_page_content(): string {
		// Get the game stats block.
		$stats_block = '<!-- wp:game-log/game-stats /-->';

		// Get the patterns.
		$playing_pattern  = $this->get_pattern_content( 'gamelog/game-log-query-playing' );
		$backlog_pattern  = $this->get_pattern_content( 'gamelog/game-log-query-backlog' );
		$played_pattern   = $this->get_pattern_content( 'gamelog/game-log-query-played' );
		$wishlist_pattern = $this->get_pattern_content( 'gamelog/game-log-query-wishlist' );

		// Build the complete page content.
		$content = $stats_block . "\n\n";

		// Add Playing section.
		$content .= '<!-- wp:heading {"level":2} -->' . "\n";
		$content .= '<h2>' . __( 'Currently Playing', 'game-log' ) . '</h2>' . "\n";
		$content .= '<!-- /wp:heading -->' . "\n\n";
		$content .= $playing_pattern . "\n\n";

		// Add Backlog section.
		$content .= '<!-- wp:heading {"level":2} -->' . "\n";
		$content .= '<h2>' . __( 'Backlog', 'game-log' ) . '</h2>' . "\n";
		$content .= '<!-- /wp:heading -->' . "\n\n";
		$content .= $backlog_pattern . "\n\n";

		// Add Played section.
		$content .= '<!-- wp:heading {"level":2} -->' . "\n";
		$content .= '<h2>' . __( 'Played', 'game-log' ) . '</h2>' . "\n";
		$content .= '<!-- /wp:heading -->' . "\n\n";
		$content .= $played_pattern . "\n\n";

		// Add Wishlist section.
		$content .= '<!-- wp:heading {"level":2} -->' . "\n";
		$content .= '<h2>' . __( 'Wishlist', 'game-log' ) . '</h2>' . "\n";
		$content .= '<!-- /wp:heading -->' . "\n\n";
		$content .= $wishlist_pattern;

		return $content;
	}

	/**
	 * Get pattern content by pattern name
	 *
	 * @param string $pattern_name The pattern name.
	 * @return string The pattern content.
	 */
	private function get_pattern_content( string $pattern_name ): string {
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $pattern_name );
		if ( ! $pattern ) {
			// translators: %s is the pattern name.
			return '<!-- wp:paragraph --><p>' . sprintf( esc_html__( 'Pattern %s not found.', 'game-log' ), $pattern_name ) . '</p><!-- /wp:paragraph -->';
		}

		return $pattern['content'];
	}

	/**
	 * Check if the default page exists
	 *
	 * @return bool True if page exists, false otherwise.
	 */
	public function page_exists(): bool {
		// Try multiple methods to find the page.
		$page = get_page_by_path( self::PAGE_SLUG );
		if ( $page ) {
			return true;
		}

		// Also check by post name in case get_page_by_path doesn't work.
		$posts = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'name'        => self::PAGE_SLUG,
				'numberposts' => 1,
			)
		);

		return ! empty( $posts );
	}

	/**
	 * Get the default page ID
	 *
	 * @return int|null The page ID if exists, null otherwise.
	 */
	public function get_page_id(): ?int {
		// Try multiple methods to find the page.
		$page = get_page_by_path( self::PAGE_SLUG );
		if ( $page ) {
			return $page->ID;
		}

		// Also check by post name in case get_page_by_path doesn't work.
		$posts = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'name'        => self::PAGE_SLUG,
				'numberposts' => 1,
			)
		);

		return ! empty( $posts ) ? $posts[0]->ID : null;
	}

	/**
	 * Handle the generate page action from admin
	 */
	public function handle_generate_page_action(): void {
		// Check if this is our action.
		if ( ! isset( $_POST['game_log_generate_page'] ) || ! isset( $_POST['game_log_generate_page_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['game_log_generate_page_nonce'] ) ), 'game_log_generate_page' ) ) {
			return;
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'game-log' ) );
		}

		// Generate the page.
		$result = $this->generate_default_page();

		if ( is_wp_error( $result ) ) {
			add_action(
				'admin_notices',
				function () use ( $result ) {
					echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
				}
			);
		} else {
			add_action(
				'admin_notices',
				function () use ( $result ) {
					$page_url = get_permalink( $result );
					// translators: %s is the page URL.
					$message = sprintf(
						// translators: %s is the page URL.
						__( 'Game Log page created successfully! <a href="%s" target="_blank">View Page</a>', 'game-log' ),
						esc_url( $page_url )
					);
					echo '<div class="notice notice-success"><p>' . wp_kses_post( $message ) . '</p></div>';
				}
			);
		}
	}

	/**
	 * Get the page generation form HTML
	 *
	 * @return string The form HTML.
	 */
	public function get_generation_form(): string {
		$page_exists = $this->page_exists();
		$page_id     = $this->get_page_id();

		ob_start();
		?>
		<div class="game-log-page-generation">
			<h3><?php esc_html_e( 'Default Game Log Page', 'game-log' ); ?></h3>
			<p><?php esc_html_e( 'Generate a default page with game statistics and all game collection sections.', 'game-log' ); ?></p>
			
			<?php if ( $page_exists && $page_id ) : ?>
				<div class="notice notice-info inline">
					<p>
						<?php esc_html_e( 'Game Log page already exists.', 'game-log' ); ?>
						<a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank"><?php esc_html_e( 'View Page', 'game-log' ); ?></a> |
						<a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>"><?php esc_html_e( 'Edit Page', 'game-log' ); ?></a>
					</p>
				</div>
			<?php else : ?>
				<?php
				// Use WordPress's built-in form handling.
				$generate_url = wp_nonce_url(
					admin_url( 'admin.php?page=game-log-settings&action=generate_page' ),
					'game_log_generate_page',
					'game_log_generate_page_nonce'
				);
				?>
				<p>
					<a href="<?php echo esc_url( $generate_url ); ?>" class="button button-primary">
						<?php esc_html_e( 'Generate Game Log Page', 'game-log' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
