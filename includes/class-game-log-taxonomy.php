<?php
/**
 * File: class-game-log-taxonomy.php
 *
 * @package Game_Log
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Game Log Taxonomy class
 *
 * @package Game_Log
 */
class Game_Log_Taxonomy {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the game status taxonomy
	 */
	public function register_taxonomy(): void {
		$labels = array(
			'name'                       => _x( 'Game Status', 'Taxonomy general name', 'game-log' ),
			'singular_name'              => _x( 'Game Status', 'Taxonomy singular name', 'game-log' ),
			'menu_name'                  => _x( 'Game Status', 'Admin menu name', 'game-log' ),
			'all_items'                  => _x( 'All Game Statuses', 'All items', 'game-log' ),
			'parent_item'                => _x( 'Parent Game Status', 'Parent item', 'game-log' ),
			'parent_item_colon'          => _x( 'Parent Game Status:', 'Parent item colon', 'game-log' ),
			'new_item_name'              => _x( 'New Game Status Name', 'New item name', 'game-log' ),
			'add_new_item'               => _x( 'Add New Game Status', 'Add new item', 'game-log' ),
			'edit_item'                  => _x( 'Edit Game Status', 'Edit item', 'game-log' ),
			'update_item'                => _x( 'Update Game Status', 'Update item', 'game-log' ),
			'view_item'                  => _x( 'View Game Status', 'View item', 'game-log' ),
			'separate_items_with_commas' => _x( 'Separate game statuses with commas', 'Separate items with commas', 'game-log' ),
			'add_or_remove_items'        => _x( 'Add or remove game statuses', 'Add or remove items', 'game-log' ),
			'choose_from_most_used'      => _x( 'Choose from the most used', 'Choose from most used', 'game-log' ),
			'popular_items'              => _x( 'Popular Game Statuses', 'Popular items', 'game-log' ),
			'search_items'               => _x( 'Search Game Statuses', 'Search items', 'game-log' ),
			'not_found'                  => _x( 'Not Found', 'Not found', 'game-log' ),
			'no_terms'                   => _x( 'No game statuses', 'No terms', 'game-log' ),
			'items_list'                 => _x( 'Game statuses list', 'Items list', 'game-log' ),
			'items_list_navigation'      => _x( 'Game statuses list navigation', 'Items list navigation', 'game-log' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Game status taxonomy for organizing games', 'game-log' ),
			'hierarchical'       => true,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'rest_base'          => 'game-status',
			'show_tagcloud'      => false,
			'show_in_quick_edit' => true,
			'show_admin_column'  => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'game-status' ),
		);

		register_taxonomy( 'game_status', array( 'game' ), $args );
	}
	/**
	 * Create default terms for game status
	 */
	public function create_default_terms(): void {
		$default_terms = array(
			'played'   => array(
				'name'        => __( 'Played', 'game-log' ),
				'description' => __( 'Games you have completed', 'game-log' ),
			),
			'playing'  => array(
				'name'        => __( 'Playing', 'game-log' ),
				'description' => __( 'Games you are currently playing', 'game-log' ),
			),
			'backlog'  => array(
				'name'        => __( 'Backlog', 'game-log' ),
				'description' => __( 'Games you own but haven\'t started', 'game-log' ),
			),
			'wishlist' => array(
				'name'        => __( 'Wishlist', 'game-log' ),
				'description' => __( 'Games you want to play', 'game-log' ),
			),
		);

		foreach ( $default_terms as $slug => $term_data ) {
			if ( ! term_exists( $slug, 'game_status' ) ) {
				wp_insert_term(
					$term_data['name'],
					'game_status',
					array(
						'slug'        => $slug,
						'description' => $term_data['description'],
					)
				);
			}
		}
	}
}
