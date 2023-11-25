<?php
/**
 * Video Game Log
 *
 * @package Video_Game_Log
 * Plugin Name: Video Game Log
 * Description: Creates a log for video games.
 * Author:      Bob Matyas
 * Author URI:  https://www.bobmatyas.com
 * Text Domain: video-game-log
 */

// Load custom post type functions.
require_once plugin_dir_path( __FILE__ ) . 'post-types.php';
require_once plugin_dir_path( __FILE__ ) . 'post-meta.php';
require_once plugin_dir_path( __FILE__ ) . 'meta-boxes.php';
require_once plugin_dir_path( __FILE__ ) . 'taxonomies.php';
