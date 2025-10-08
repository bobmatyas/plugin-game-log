<?php
/**
 * File: class-game-log-post-type.php
 *
 * @package Game_Log
 * @since 1.0.0
 */

declare(strict_types=1);

/**
 * Game Log Post Type class
 *
 * @package Game_Log
 */
class Game_Log_Post_Type {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
	}

	/**
	 * Register the game post type
	 */
	public function register_post_type(): void {
		$labels = array(
			'name'                  => _x( 'Games', 'Post type general name', 'game-log' ),
			'singular_name'         => _x( 'Game', 'Post type singular name', 'game-log' ),
			'menu_name'             => _x( 'Games', 'Admin Menu text', 'game-log' ),
			'name_admin_bar'        => _x( 'Game', 'Add New on Toolbar', 'game-log' ),
			'add_new'               => __( 'Add New', 'game-log' ),
			'add_new_item'          => __( 'Add New Game', 'game-log' ),
			'new_item'              => __( 'New Game', 'game-log' ),
			'edit_item'             => __( 'Edit Game', 'game-log' ),
			'view_item'             => __( 'View Game', 'game-log' ),
			'all_items'             => __( 'All Games', 'game-log' ),
			'search_items'          => __( 'Search Games', 'game-log' ),
			'parent_item_colon'     => __( 'Parent Games:', 'game-log' ),
			'not_found'             => __( 'No games found.', 'game-log' ),
			'not_found_in_trash'    => __( 'No games found in Trash.', 'game-log' ),
			'featured_image'        => _x( 'Game Cover', 'Overrides the "Featured Image" phrase', 'game-log' ),
			'set_featured_image'    => _x( 'Set game cover', 'Overrides the "Set featured image" phrase', 'game-log' ),
			'remove_featured_image' => _x( 'Remove game cover', 'Overrides the "Remove featured image" phrase', 'game-log' ),
			'use_featured_image'    => _x( 'Use as game cover', 'Overrides the "Use as featured image" phrase', 'game-log' ),
			'archives'              => _x( 'Game archives', 'The post type archive label', 'game-log' ),
			'insert_into_item'      => _x( 'Insert into game', 'Overrides the "Insert into post"/"Insert into page" phrase', 'game-log' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this game', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'game-log' ),
			'filter_items_list'     => _x( 'Filter games list', 'Screen reader text for the filter links', 'game-log' ),
			'items_list_navigation' => _x( 'Games list navigation', 'Screen reader text for the pagination', 'game-log' ),
			'items_list'            => _x( 'Games list', 'Screen reader text for the items list', 'game-log' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false, // We'll add it to our custom menu.
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'game' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-games',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'tags' ),
			'show_in_rest'       => true,
			'rest_base'          => 'games',
		);

		register_post_type( 'game', $args );
	}

	/**
	 * Add meta boxes for game custom fields
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'game_details',
			__( 'Game Details', 'game-log' ),
			array( $this, 'game_details_meta_box' ),
			'game',
			'normal',
			'high'
		);
	}

	/**
	 * Game details meta box callback
	 *
	 * @param WP_Post $post The post object.
	 */
	public function game_details_meta_box( $post ): void {
		wp_nonce_field( 'game_details_meta_box', 'game_details_meta_box_nonce' );

		$rating       = get_post_meta( $post->ID, '_game_rating', true );
		$release_date = get_post_meta( $post->ID, '_game_release_date', true );
		$igdb_id      = get_post_meta( $post->ID, '_game_igdb_id', true );

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="game_rating"><?php esc_html_e( 'Rating', 'game-log' ); ?></label>
				</th>
				<td>
					<input type="number" id="game_rating" name="game_rating" value="<?php echo esc_attr( $rating ); ?>" min="1" max="10" step="0.1" />
					<p class="description"><?php esc_html_e( 'Rate the game from 1 to 10', 'game-log' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="game_release_date"><?php esc_html_e( 'Release Date', 'game-log' ); ?></label>
				</th>
				<td>
					<input type="date" id="game_release_date" name="game_release_date" value="<?php echo esc_attr( $release_date ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="game_igdb_id"><?php esc_html_e( 'IGDB ID', 'game-log' ); ?></label>
				</th>
				<td>
					<input type="text" id="game_igdb_id" name="game_igdb_id" value="<?php echo esc_attr( $igdb_id ); ?>" readonly />
					<p class="description"><?php esc_html_e( 'Internal IGDB identifier', 'game-log' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save meta box data
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_meta_boxes( $post_id ): void {
		// Check nonce.
		if ( ! isset( $_POST['game_details_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['game_details_meta_box_nonce'] ) ), 'game_details_meta_box' ) ) {
			return;
		}

		// Check if autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save meta fields.
		$fields = array(
			'game_rating'       => 'sanitize_text_field',
			'game_release_date' => 'sanitize_text_field',
			'game_igdb_id'      => 'sanitize_text_field',
		);

		foreach ( $fields as $field => $sanitize_callback ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = call_user_func( $sanitize_callback, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}
}
