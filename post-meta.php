<?php
/**
 * Registers meta information for video games.
 *
 * @package Video_Game_Log
 */

add_action( 'init', 'ws_games_register_meta' );

/**
 * Registers meta values for games
 *
 * @return void
 */
function ws_games_register_meta() {

	register_post_meta(
		'game',
		'game_purchase_link',
		array(
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => function( $value ) {
				return wp_strip_all_tags( $value );
			},
		)
	);
}
