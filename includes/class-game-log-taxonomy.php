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
			'name'                       => _x( 'Game Status', 'Taxonomy general name', 'mode7-game-log' ),
			'singular_name'              => _x( 'Game Status', 'Taxonomy singular name', 'mode7-game-log' ),
			'menu_name'                  => _x( 'Game Status', 'Admin menu name', 'mode7-game-log' ),
			'all_items'                  => _x( 'All Game Statuses', 'All items', 'mode7-game-log' ),
			'parent_item'                => _x( 'Parent Game Status', 'Parent item', 'mode7-game-log' ),
			'parent_item_colon'          => _x( 'Parent Game Status:', 'Parent item colon', 'mode7-game-log' ),
			'new_item_name'              => _x( 'New Game Status Name', 'New item name', 'mode7-game-log' ),
			'add_new_item'               => _x( 'Add New Game Status', 'Add new item', 'mode7-game-log' ),
			'edit_item'                  => _x( 'Edit Game Status', 'Edit item', 'mode7-game-log' ),
			'update_item'                => _x( 'Update Game Status', 'Update item', 'mode7-game-log' ),
			'view_item'                  => _x( 'View Game Status', 'View item', 'mode7-game-log' ),
			'separate_items_with_commas' => _x( 'Separate game statuses with commas', 'Separate items with commas', 'mode7-game-log' ),
			'add_or_remove_items'        => _x( 'Add or remove game statuses', 'Add or remove items', 'mode7-game-log' ),
			'choose_from_most_used'      => _x( 'Choose from the most used', 'Choose from most used', 'mode7-game-log' ),
			'popular_items'              => _x( 'Popular Game Statuses', 'Popular items', 'mode7-game-log' ),
			'search_items'               => _x( 'Search Game Statuses', 'Search items', 'mode7-game-log' ),
			'not_found'                  => _x( 'Not Found', 'Not found', 'mode7-game-log' ),
			'no_terms'                   => _x( 'No game statuses', 'No terms', 'mode7-game-log' ),
			'items_list'                 => _x( 'Game statuses list', 'Items list', 'mode7-game-log' ),
			'items_list_navigation'      => _x( 'Game statuses list navigation', 'Items list navigation', 'mode7-game-log' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Game status taxonomy for organizing games', 'mode7-game-log' ),
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
				'name'        => __( 'Played', 'mode7-game-log' ),
				'description' => __( 'Games you have completed', 'mode7-game-log' ),
			),
			'playing'  => array(
				'name'        => __( 'Playing', 'mode7-game-log' ),
				'description' => __( 'Games you are currently playing', 'mode7-game-log' ),
			),
			'backlog'  => array(
				'name'        => __( 'Backlog', 'mode7-game-log' ),
				'description' => __( 'Games you own but haven\'t started', 'mode7-game-log' ),
			),
			'wishlist' => array(
				'name'        => __( 'Wishlist', 'mode7-game-log' ),
				'description' => __( 'Games you want to play', 'mode7-game-log' ),
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
