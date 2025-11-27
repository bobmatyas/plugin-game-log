<?php
/**
 * File: class-game-log.php
 *
 * @package Game_Log
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Main Game Log class
 *
 * @package Game_Log
 */
class Game_Log {

	/**
	 * Initialize the plugin
	 */
	public function init(): void {
		// Initialize components.
		new Game_Log_Post_Type();
		new Game_Log_Taxonomy();
		new Game_Log_Meta_Fields();
		new Game_Log_Admin();
		new Game_Log_IGDB_API();
		new Game_Log_Ajax_Handler();
		new Game_Log_Patterns();
		new Game_Log_Stats_Block();
		new Game_Log_Default_Page();

		// Enqueue scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
		add_action( 'after_setup_theme', array( $this, 'add_editor_styles_support' ) );
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_admin_scripts(): void {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, array( 'game', 'edit-game', 'toplevel_page_mode7-game-log', 'mode7-game-log_page_mode7-game-log-add', 'mode7-game-log_page_mode7-game-log-settings' ), true ) ) {
			return;
		}

		wp_enqueue_script(
			'game-log-admin',
			GAME_LOG_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			GAME_LOG_VERSION,
			true
		);

		wp_enqueue_style(
			'game-log-admin',
			GAME_LOG_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			GAME_LOG_VERSION
		);

		// Localize script for AJAX.
		wp_localize_script(
			'game-log-admin',
			'gameLogAjax',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'game_log_nonce' ),
				'strings' => array(
					'searching'  => __( 'Searching...', 'mode7-game-log' ),
					'noResults'  => __( 'No games found', 'mode7-game-log' ),
					'error'      => __( 'An error occurred', 'mode7-game-log' ),
					'gameAdded'  => __( 'Game added successfully', 'mode7-game-log' ),
				),
			)
		);
	}
    
	/**
	 * Enqueue public scripts and styles
	 */
	public function enqueue_public_scripts(): void {
		wp_enqueue_style(
			'game-log-public',
			GAME_LOG_PLUGIN_URL . 'assets/css/public.css',
			array(),
			GAME_LOG_VERSION
		);
	}

	/**
	 * Add editor styles support
	 */
	public function add_editor_styles_support(): void {
		add_theme_support( 'editor-styles' );
		add_editor_style( GAME_LOG_PLUGIN_URL . 'assets/css/public.css' );
	}
}
