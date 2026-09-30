<?php
/**
 * Adds a small settings page under Settings → Review Notes, where an
 * admin picks which post types get the review workflow. Demonstrates
 * the Settings API: register_setting, add_settings_field, and a
 * dedicated admin menu page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Review_Notes_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_settings_page() {
		add_options_page(
			__( 'Review Notes', 'review-notes' ),
			__( 'Review Notes', 'review-notes' ),
			'manage_options',
			'review-notes',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'review_notes_settings_group',
			'review_notes_post_types',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_post_types' ),
				'default'           => array( 'post', 'page' ),
			)
		);

		add_settings_section(
			'review_notes_main_section',
			__( 'Where should the review workflow appear?', 'review-notes' ),
			'__return_false',
			'review-notes'
		);

		add_settings_field(
			'review_notes_post_types_field',
			__( 'Enabled post types', 'review-notes' ),
			array( $this, 'render_post_types_field' ),
			'review-notes',
			'review_notes_main_section'
		);
	}

	/**
	 * Only allow post type slugs that actually exist and are public —
	 * never trust the raw POST data for something that ends up in a
	 * capability-gated query later.
	 */
	public function sanitize_post_types( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$valid = get_post_types( array( 'public' => true ), 'names' );
		return array_values( array_intersect( array_map( 'sanitize_key', $input ), $valid ) );
	}

	public function render_post_types_field() {
		$selected   = (array) get_option( 'review_notes_post_types', array( 'post', 'page' ) );
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $post_types as $post_type ) {
			printf(
				'<label style="display:block;margin-bottom:6px;"><input type="checkbox" name="review_notes_post_types[]" value="%1$s" %2$s /> %3$s</label>',
				esc_attr( $post_type->name ),
				checked( in_array( $post_type->name, $selected, true ), true, false ),
				esc_html( $post_type->labels->name )
			);
		}
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Review Notes Settings', 'review-notes' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'review_notes_settings_group' );
				do_settings_sections( 'review-notes' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
