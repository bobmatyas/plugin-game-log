<?php
/**
 * Registers custom taxonomies for game post type.
 *
 * @package Video_Game_Log
 */

add_action( 'init', 'ws_games_register_taxonomies' );

/**
 * Registers a "game custom post type"
 *
 * @return void
 */
function ws_games_register_taxonomies() {

	register_taxonomy(
		'list',
		'game',
		array(

			// Taxonomy arguments.
			'public'            => true,
			'show_in_rest'      => true,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'query_var'         => 'list',

			// The rewrite handles the URL structure.
			'rewrite'           => array(
				'slug'         => 'list',
				'with_front'   => false,
				'hierarchical' => false,
				'ep_mask'      => EP_NONE,
			),

			// Text labels.
			'labels'            => array(
				'name'                  => 'Lists',
				'singular_name'         => 'List',
				'menu_name'             => 'Lists',
				'name_admin_bar'        => 'List',
				'search_items'          => 'Search Lists',
				'popular_items'         => 'Popular Lists',
				'all_items'             => 'All Lists',
				'edit_item'             => 'Edit List',
				'view_item'             => 'View List',
				'update_item'           => 'Update List',
				'add_new_item'          => 'Add New List',
				'new_item_name'         => 'New List Name',
				'not_found'             => 'No Lists found.',
				'no_terms'              => 'No Lists',
				'items_list_navigation' => 'List list navigation',
				'items_list'            => 'List list',

				// Hierarchical only.
				'select_name'           => 'Select List',
				'parent_item'           => 'Parent List',
				'parent_item_colon'     => 'Parent List:',
			),
		)
	);
}
