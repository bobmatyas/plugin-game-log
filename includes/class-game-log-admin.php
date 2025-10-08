<?php
/**
 * Game Log Admin class file
 *
 * This file contains the Game_Log_Admin class which handles the admin interface
 * for the Game Log plugin, including menu pages, settings, and game management.
 *
 * @package Game_Log
 * @subpackage Admin
 * @since 1.0.0
 */

/**
 * Game Log Admin class
 *
 * Handles the admin interface for the Game Log plugin.
 *
 * @package Game_Log
 * @since 1.0.0
 */
class Game_Log_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		add_filter( 'admin_url', array( $this, 'modify_add_new_game_url' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'handle_page_generation' ) );
	}
	/**
	 * Add admin menu
	 */
	public function add_admin_menu(): void {
		// Main menu page.
		add_menu_page(
			__( 'Game Log', 'game-log' ),
			__( 'Game Log', 'game-log' ),
			'manage_options',
			'game-log',
			array( $this, 'games_list_page' ),
			'dashicons-games',
			30
		);

		// Games list submenu (same as main page).
		add_submenu_page(
			'game-log',
			__( 'All Games', 'game-log' ),
			__( 'All Games', 'game-log' ),
			'manage_options',
			'game-log',
			array( $this, 'games_list_page' )
		);

		// Add Game submenu.
		add_submenu_page(
			'game-log',
			__( 'Add Game', 'game-log' ),
			__( 'Add Game', 'game-log' ),
			'manage_options',
			'game-log-add',
			array( $this, 'add_game_page' )
		);

		// Settings submenu.
		add_submenu_page(
			'game-log',
			__( 'Settings', 'game-log' ),
			__( 'Settings', 'game-log' ),
			'manage_options',
			'game-log-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Add Game page
	 */
	public function add_game_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Add Game', 'game-log' ); ?></h1>
			
			<div class="game-search-container">
				<form id="game-search-form" class="game-search-form">
					<div class="search-field-group">
						<p><?php esc_html_e( 'Search the Internet Gaming Database for a game to add to your log.', 'game-log' ); ?></p>
						<label for="game-search-input"><?php esc_html_e( 'Search for a game:', 'game-log' ); ?></label>
						<div class="search-input-wrapper">
							<input 
								type="text" 
								id="game-search-input" 
								name="game_search" 
								placeholder="<?php esc_attr_e( 'Enter game name...', 'game-log' ); ?>"
								class="regular-text"
							/>
							<button type="submit" class="button button-primary">
								<?php esc_html_e( 'Search', 'game-log' ); ?>
							</button>
						</div>
					</div>
				</form>
				
				<div id="game-search-results" class="game-search-results"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Register settings
	 */
	public function register_settings(): void {
		register_setting( 'game_log_settings', 'game_log_igdb_client_id', array( $this, 'sanitize_igdb_client_id' ) );
		register_setting( 'game_log_settings', 'game_log_igdb_client_secret', array( $this, 'sanitize_igdb_client_secret' ) );

		add_settings_section(
			'game_log_igdb_section',
			__( 'IGDB API Settings', 'game-log' ),
			array( $this, 'igdb_section_callback' ),
			'game_log_settings'
		);

		add_settings_field(
			'game_log_igdb_client_id',
			__( 'Client ID', 'game-log' ),
			array( $this, 'igdb_client_id_callback' ),
			'game_log_settings',
			'game_log_igdb_section'
		);

		add_settings_field(
			'game_log_igdb_client_secret',
			__( 'Client Secret', 'game-log' ),
			array( $this, 'igdb_client_secret_callback' ),
			'game_log_settings',
			'game_log_igdb_section'
		);
	}

	/**
	 * IGDB section callback
	 */
	public function igdb_section_callback(): void {
		echo '<p>' . esc_html__( 'Enter your IGDB API credentials to enable game search functionality.', 'game-log' ) . '</p>';
		echo '<p><a href="https://api.igdb.com/" target="_blank">' . esc_html__( 'Get your API credentials from IGDB', 'game-log' ) . '</a></p>';
	}

	/**
	 * IGDB Client ID callback
	 */
	public function igdb_client_id_callback(): void {
		$value = get_option( 'game_log_igdb_client_id', '' );
		echo '<input type="text" name="game_log_igdb_client_id" value="' . esc_attr( $value ) . '" class="regular-text" />';
	}

	/**
	 * IGDB Client Secret callback
	 */
	public function igdb_client_secret_callback(): void {
		$value = get_option( 'game_log_igdb_client_secret', '' );
		echo '<input type="password" name="game_log_igdb_client_secret" value="' . esc_attr( $value ) . '" class="regular-text" />';
	}

	/**
	 * Sanitize IGDB Client ID
	 *
	 * @param string $value The input value to sanitize.
	 * @return string Sanitized value.
	 */
	public function sanitize_igdb_client_id( string $value ): string {
		// Use WordPress built-in sanitization first.
		$sanitized = sanitize_text_field( $value );

		// Additional validation - IGDB client IDs are typically alphanumeric.
		if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $sanitized ) ) {
			add_settings_error(
				'game_log_igdb_client_id',
				'invalid_client_id',
				__( 'Invalid Client ID format. Only alphanumeric characters, hyphens, and underscores are allowed.', 'game-log' )
			);
			return get_option( 'game_log_igdb_client_id', '' );
		}

		return $sanitized;
	}

	/**
	 * Sanitize IGDB Client Secret
	 *
	 * @param string $value The input value to sanitize.
	 * @return string Sanitized value.
	 */
	public function sanitize_igdb_client_secret( string $value ): string {
		// Use WordPress built-in sanitization first.
		$sanitized = sanitize_text_field( $value );

		// Additional validation - IGDB client secrets are typically alphanumeric.
		if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $sanitized ) ) {
			add_settings_error(
				'game_log_igdb_client_secret',
				'invalid_client_secret',
				__( 'Invalid Client Secret format. Only alphanumeric characters, hyphens, and underscores are allowed.', 'game-log' )
			);
			return get_option( 'game_log_igdb_client_secret', '' );
		}

		return $sanitized;
	}

	/**
	 * Settings page
	 */
	public function settings_page(): void {
		$default_page = new Game_Log_Default_Page();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Game Log Settings', 'game-log' ); ?></h1>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'game_log_settings' );
				do_settings_sections( 'game_log_settings' );
				submit_button();
				?>
			</form>
			
			<hr>

			<?php
			$form_html = $default_page->get_generation_form();
			if ( empty( $form_html ) ) {
				echo '<div class="notice notice-error"><p>Error: Could not generate form HTML</p></div>';
			} else {
				echo wp_kses_post( $form_html );
			}
			?>

		</div>
		<?php
	}

	/**
	 * Games list page
	 */
	public function games_list_page(): void {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Game Log', 'game-log' ); ?></h1>
			<div class="game-log-add-holder">
				<button type="button" class="page-title-action button button-primary" id="search-games-btn"><?php esc_html_e( 'Add A Game', 'game-log' ); ?></button>
			</div>
			<hr class="wp-header-end">
			
			<!-- Game Search Modal -->
			<dialog id="game-search-modal" class="game-search-modal" style="display: none;">

					<div class="game-search-modal-header">
						<h2><?php esc_html_e( 'Search Games', 'game-log' ); ?></h2>
						<span class="close">&times;</span>
					</div>
					<div class="game-search-modal-body">
						<p><?php esc_html_e( 'Search the Internet Gaming Database for to add a game to your log.', 'game-log' ); ?></p>	
						<div class="game-search-form">
							
							<input type="text" id="game-search-input" placeholder="<?php esc_attr_e( 'Enter game name...', 'game-log' ); ?>" class="regular-text" />
							<button type="button" id="search-games-submit" class="button button-primary"><?php esc_html_e( 'Search', 'game-log' ); ?></button>
						</div>
						<div id="game-search-results" class="game-search-results"></div>
					</div>

			</dialog>
			
			<!-- Games List -->
			<div class="game-log-stats">
				<?php $this->display_game_stats(); ?>
			</div>
			
			<div class="game-log-filters">
				<?php $this->display_game_filters(); ?>
			</div>
			
			<div class="game-log-list">
				<?php $this->display_games_list(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display game statistics
	 */
	private function display_game_stats(): void {
		$total_games = wp_count_posts( 'game' )->publish;
		$played      = $this->get_games_by_status( 'played' );
		$playing     = $this->get_games_by_status( 'playing' );
		$backlog     = $this->get_games_by_status( 'backlog' );
		$wishlist    = $this->get_games_by_status( 'wishlist' );

		?>
		<div class="game-stats">
			<div class="stat-box">
				<h3><?php echo esc_html( $total_games ); ?></h3>
				<p><?php esc_html_e( 'Total Games', 'game-log' ); ?></p>
			</div>
			<div class="stat-box">
				<h3><?php echo esc_html( count( $played ) ); ?></h3>
				<p><?php esc_html_e( 'Played', 'game-log' ); ?></p>
			</div>
			<div class="stat-box">
				<h3><?php echo esc_html( count( $playing ) ); ?></h3>
				<p><?php esc_html_e( 'Playing', 'game-log' ); ?></p>
			</div>
			<div class="stat-box">
				<h3><?php echo esc_html( count( $backlog ) ); ?></h3>
				<p><?php esc_html_e( 'Backlog', 'game-log' ); ?></p>
			</div>
			<div class="stat-box">
				<h3><?php echo esc_html( count( $wishlist ) ); ?></h3>
				<p><?php esc_html_e( 'Wishlist', 'game-log' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Display game filters
	 */
	private function display_game_filters(): void {
		$statuses = get_terms(
			array(
				'taxonomy'   => 'game_status',
				'hide_empty' => false,
			)
		);
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET form for filtering, no data modification
		$current_status = isset( $_GET['game_status'] ) ? sanitize_text_field( wp_unslash( $_GET['game_status'] ) ) : '';

		?>
		<form method="get" class="game-filters">
			<input type="hidden" name="page" value="game-log" />
			<select name="game_status" id="game_status_filter">
				<option value=""><?php esc_html_e( 'All Statuses', 'game-log' ); ?></option>
				<?php foreach ( $statuses as $status ) : ?>
					<option value="<?php echo esc_attr( $status->slug ); ?>" <?php selected( $current_status, $status->slug ); ?>>
						<?php echo esc_html( $status->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'game-log' ); ?>" />
		</form>
		<?php
	}

	/**
	 * Display games list
	 */
	private function display_games_list(): void {
		$args = array(
			'post_type'      => 'game',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
		);

		// Add status filter if selected.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET form for filtering, no data modification
		if ( isset( $_GET['game_status'] ) && ! empty( $_GET['game_status'] ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required for filtering games by status
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'game_status',
					'field'    => 'slug',
					'terms'    => sanitize_text_field( wp_unslash( $_GET['game_status'] ) ),
				),
			);
		}

		$games = new WP_Query( $args );

		if ( $games->have_posts() ) {
			?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Cover', 'game-log' ); ?></th>
						<th><?php esc_html_e( 'Title', 'game-log' ); ?></th>
						<th><?php esc_html_e( 'Status', 'game-log' ); ?></th>
						<th><?php esc_html_e( 'Rating', 'game-log' ); ?></th>
						<th><?php esc_html_e( 'Release Date', 'game-log' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'game-log' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					while ( $games->have_posts() ) :
						$games->the_post();
						?>
						<?php
						$game_id      = get_the_ID();
						$rating       = get_post_meta( $game_id, '_game_rating', true );
						$release_date = get_post_meta( $game_id, '_game_release_date', true );
						$platforms    = get_post_meta( $game_id, '_game_platforms', true );
						$status_terms = get_the_terms( $game_id, 'game_status' );
						$status       = ! empty( $status_terms ) ? $status_terms[0]->name : '';
						?>
						<tr>
							<td>
								<?php if ( has_post_thumbnail() ) : ?>
									<?php echo get_the_post_thumbnail( get_the_ID(), 'full' ); ?>
								<?php else : ?>
									<div class="no-thumbnail"><?php esc_html_e( 'No Image', 'game-log' ); ?></div>
								<?php endif; ?>
							</td>
							<td>
								<strong><a href="<?php echo esc_url( get_edit_post_link( $game_id ) ); ?>"><?php the_title(); ?></a></strong>
							</td>
							<td><?php echo esc_html( $status ); ?></td>
							<td><?php echo $rating ? esc_html( $rating ) . '/10' : '-'; ?></td>
							<td><?php echo $release_date ? esc_html( gmdate( 'M j, Y', strtotime( $release_date ) ) ) : '-'; ?></td>
							<td>
								<a href="<?php echo esc_url( get_edit_post_link( $game_id ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'game-log' ); ?></a>
								<a href="<?php echo esc_url( get_delete_post_link( $game_id ) ); ?>" class="button button-small" onclick="return confirm('Are you sure you want to delete this game?' ); ?>')"><?php esc_html_e( 'Delete', 'game-log' ); ?></a>
							</td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
			
			<?php
			// Pagination.
			$pagination_args = array(
				'total'        => $games->max_num_pages,
				'current'      => max( 1, get_query_var( 'paged' ) ),
				'format'       => '?paged=%#%',
				'show_all'     => false,
				'type'         => 'list',
				'end_size'     => 1,
				'mid_size'     => 2,
				'prev_next'    => true,
				'prev_text'    => __( '« Previous', 'game-log' ),
				'next_text'    => __( 'Next »', 'game-log' ),
				'add_args'     => false,
				'add_fragment' => '',
			);

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo paginate_links( $pagination_args );
			?>

			<?php
		} else {
			?>
			<p><?php esc_html_e( 'No games found.', 'game-log' ); ?></p>
			<?php
		}

		wp_reset_postdata();
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
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required for filtering games by status
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

	/**
	 * Show admin notices
	 */
	public function show_admin_notices(): void {
		$screen = get_current_screen();

		// Only show on our admin pages.
		if ( ! $screen || ! in_array( $screen->id, array( 'game', 'edit-game', 'toplevel_page_game-log', 'game-log_page_game-log-settings' ), true ) ) {
			return;
		}

		$client_id     = get_option( 'game_log_igdb_client_id', '' );
		$client_secret = get_option( 'game_log_igdb_client_secret', '' );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Game Log:', 'game-log' ); ?></strong>
					<?php esc_html_e( 'IGDB API credentials are not configured. Please go to', 'game-log' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=game-log-settings' ) ); ?>"><?php esc_html_e( 'Settings', 'game-log' ); ?></a>
					<?php esc_html_e( 'to enter your API credentials to enable game search functionality.', 'game-log' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Modify the Add New Game URL to point to our custom page
	 *
	 * @param string $url The admin URL.
	 * @param string $path The admin path.
	 * @return string Modified URL.
	 */
	public function modify_add_new_game_url( string $url, string $path ): string {
		// Check if this is the "Add New" URL for the game post type.
		if ( 'post-new.php?post_type=game' === $path ) {
			return admin_url( 'admin.php?page=game-log-add' );
		}

		return $url;
	}

	/**
	 * Handle page generation form submission
	 */
	public function handle_page_generation(): void {
		// Check if this is our action (GET request with nonce).
		if ( ! isset( $_GET['action'] ) || 'generate_page' !== $_GET['action'] ) {
			return;
		}

		if ( ! isset( $_GET['game_log_generate_page_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['game_log_generate_page_nonce'] ) ), 'game_log_generate_page' ) ) {
			return;
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'game-log' ) );
		}

		// Generate the page using the default page class.
		$default_page = new Game_Log_Default_Page();
		$result       = $default_page->generate_default_page();

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
					$message  = sprintf(
						// translators: %s is the page URL.
						__( 'Game Log page created successfully! <a href="%s" target="_blank">View Page</a>', 'game-log' ),
						esc_url( $page_url )
					);
					echo '<div class="notice notice-success"><p>' . wp_kses_post( $message ) . '</p></div>';
				}
			);
		}
	}
}
