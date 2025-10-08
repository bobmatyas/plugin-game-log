<?php
/**
 * Plugin Name: Game Log
 * Plugin URI: https://www.bobmatyas.com
 * Description: A WordPress plugin to track video games you've played, are playing, or want to play using IGDB.com database.
 * Version: 1.0.0
 * Author: Bob Matyas
 * License: GPL v2 or later
 * Text Domain: game-log
 *
 * @package Game_Log
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'GAME_LOG_VERSION', '1.0.0' );
define( 'GAME_LOG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GAME_LOG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GAME_LOG_PLUGIN_FILE', __FILE__ );

// Include required files.
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log.php';
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-post-type.php';
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-taxonomy.php';
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-meta-fields.php';
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-patterns.php';
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-admin.php';
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-igdb-api.php';
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-ajax-handler.php';
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-stats-block.php';
require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-default-page.php';

/**
 * Initialize the plugin.
 */
function game_log_init() {
	$game_log = new Game_Log();
	$game_log->init();
}
add_action( 'plugins_loaded', 'game_log_init' );

/**
 * Plugin activation hook.
 */
function game_log_activate() {
	// Include required files first.
	require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-post-type.php';
	require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-taxonomy.php';
	require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-patterns.php';
	require_once GAME_LOG_PLUGIN_DIR . 'includes/class-game-log-default-page.php';

	// Create instances and register post type and taxonomy.
	$post_type = new Game_Log_Post_Type();
	$taxonomy  = new Game_Log_Taxonomy();

	// Register post type and taxonomy.
	$post_type->register_post_type();
	$taxonomy->register_taxonomy();

	// Flush rewrite rules.
	flush_rewrite_rules();

	// Create default terms.
	$taxonomy->create_default_terms();

	// Register patterns so they're available for the default page.
	$patterns = new Game_Log_Patterns();
	$patterns->register_patterns();

	// Generate the default game-log page.
	$default_page = new Game_Log_Default_Page();
	$default_page->generate_default_page();
}

/**
 * Register block patterns.
 */
function gamelog_register_block_patterns() {
	// Ensure terms exist before registering patterns.
	$taxonomy = new Game_Log_Taxonomy();
	$taxonomy->create_default_terms();

	// Create a category for your plugin's patterns.
	register_block_pattern_category(
		'gamelog',
		array( 'label' => __( 'Game Log Patterns', 'game-log' ) )
	);

	// Load all PHP files inside /patterns.
	$pattern_files = glob( plugin_dir_path( __FILE__ ) . 'patterns/*.php' );
	foreach ( $pattern_files as $file ) {
		require $file;
	}
}
add_action( 'init', 'gamelog_register_block_patterns' );

register_activation_hook( __FILE__, 'game_log_activate' );

/**
 * Plugin deactivation hook.
 */
function game_log_deactivate() {
	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'game_log_deactivate' );
