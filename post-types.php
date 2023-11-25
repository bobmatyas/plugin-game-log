<?php
/**
 * Registers custom post type for tracking games.
 *
 * @package Video_Game_Log
 */

add_action( 'init', 'ws_game_log_post_types' );

/**
 * Registers a game custom post type.
 *
 * @return void
 */
function ws_game_log_post_types() {

	register_post_type(
		'game',
		array(

			// Post type arguments.
			'public'              => true,
			'publicly_queryable'  => true,
			'show_in_rest'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-games',
			'hierarchical'        => false,
			'has_archive'         => true,
			'query_var'           => 'game',
			'map_meta_cap'        => true,

			// The rewrite handles the URL structure.
			'rewrite'             => array(
				'slug'       => 'gaming-log',
				'with_front' => false,
				'pages'      => true,
				'feeds'      => true,
				'ep_mask'    => EP_PERMALINK,
			),

			// Features the post type supports.
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
			),

			// Text labels.
			'labels'              => array(
				'name'                  => __( 'Video Games', 'video-game-log' ),
				'singular_name'         => __( 'Video Game', 'video-game-log' ),
				'add_new'               => __( 'Add New', 'video-game-log' ),
				'add_new_item'          => __( 'Add New Game', 'video-game-log' ),
				'edit_item'             => __( 'Edit Game', 'video-game-log' ),
				'new_item'              => __( 'New Game', 'video-game-log' ),
				'view_item'             => __( 'View Game', 'video-game-log' ),
				'view_items'            => __( 'View Games', 'video-game-log' ),
				'search_items'          => __( 'Search Games', 'video-game-log' ),
				'not_found'             => __( 'No Games found.', 'video-game-log' ),
				'not_found_in_trash'    => __( 'No Games found in Trash.', 'video-game-log' ),
				'all_items'             => __( 'All Games', 'video-game-log' ),
				'archives'              => __( 'Video Game Archives', 'video-game-log' ),
				'attributes'            => __( 'Video Game Attributes', 'video-game-log' ),
				'insert_into_item'      => __( 'Insert into game', 'video-game-log' ),
				'uploaded_to_this_item' => __( 'Uploaded to this game', 'video-game-log' ),
				'set_featured_image'    => __( 'Set game cover image', 'video-game-log' ),
				'set_featured_image'    => __( 'Set game cover image', 'video-game-log' ),
				'use_featured_image'    => __( 'Use as game cover', 'video-game-log' ),
				'filter_items_list'     => __( 'Filter Video Games list', 'video-game-log' ),
			),
		),
	);
}
