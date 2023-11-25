<?php
/**
 * Registers metabox.
 *
 * @package Video_Game_Log
 */

add_action( 'add_meta_boxes_game', 'ws_game_register_meta_boxes' );

/**
 * Registers a metabox for game details
 *
 * @return void
 */
function ws_game_register_meta_boxes() {

	add_meta_box(
		'ws-game-details',
		__( 'Game Details', 'video-game-log' ),
		'ws_game_details_meta_box',
		'game',
		'advanced',
		'high'
	);
}

/**
 * Adds game details meta box
 *
 * @param type $post Current post.
 */
function ws_game_details_meta_box( $post ) {

	// Get the existing purchase link.
	$game_purchase_link = get_post_meta( $post->ID, 'game_purchase_link', true );

	// Add a nonce field to check on save.
	wp_nonce_field( basename( __FILE__ ), 'ws-game-purchase-link' ); ?>

	<p>
		<label>
			<b><?php esc_html_e( 'Purchase Link', 'video-game-log' ); ?></b>
			<br />
			<input type="url" pattern="http*://.*" placeholder="https://affiliate.link" name="ws-game-purchase-link" value="<?php echo esc_attr( $game_purchase_link ); ?>" />
			<p>A link to purchase the game.</p>
		</label>
	</p>

	<?php
}

add_action( 'save_post_game', 'ws_game_save_post', 10, 2 );

/**
 * Registers a metabox for game details
 *
 * @param type $post_id ID of post.
 * @param type $post Post object.
 */
function ws_game_save_post( $post_id, $post ) {

	// Verify the nonce before proceeding.
	if (
		! isset( $_POST['ws-game-purchase-link'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['ws-game-purchase-link'] ), basename( __FILE__ ) )
	) {
		return;
	}

	// Bail if user doesn't have permission to edit the post.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Bail if this is an Ajax request, autosave, or revision.
	if (
		wp_is_doing_ajax() ||
		wp_is_post_autosave( $post_id ) ||
		wp_is_post_revision( $post_id )
	) {
		return;
	}

	// Get the existing purchase link if the value exists.
	// If no existing purchase link, value is empty string.
	$old_purchase_link = get_post_meta( $post_id, 'book_author', true );

	// Strip all tags from posted link.
	// If no value is passed from the form, set to empty string.
	$new_purchase_link = isset( $_POST['ws-game-purchase-link'] ) ? wp_strip_all_tags( wp_unslash( $_POST['ws-game-purchase-link'] ) ) : '';

	// If there's an old value but not a new value, delete old value.
	if ( ! $new_purchase_link && $old_purchase_link ) {
		delete_post_meta( $post_id, 'game_purchase_link' );

		// If the new value doesn't match the new value add/update.
	} elseif ( $new_value !== $old_value ) {
		update_post_meta( $post_id, 'game_purchase_link', $new_value );
	}
}
