<?php
/**
 * The core feature: a meta box on the post edit screen with a review
 * status dropdown and a free-text notes field, saved as post meta.
 * Covers the standard secure-save pattern: nonce, capability check,
 * autosave check, and sanitization on every field before it touches
 * the database.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Review_Notes_Meta_Box {

	const NONCE_ACTION = 'review_notes_save';
	const NONCE_NAME   = 'review_notes_nonce';

	public static function get_statuses() {
		return array(
			'needs_review' => __( 'Needs review', 'review-notes' ),
			'in_progress'  => __( 'In progress', 'review-notes' ),
			'approved'     => __( 'Approved', 'review-notes' ),
		);
	}

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_meta_box() {
		$post_types = (array) get_option( 'review_notes_post_types', array( 'post', 'page' ) );

		add_meta_box(
			'review_notes_meta_box',
			__( 'Editorial Review', 'review-notes' ),
			array( $this, 'render' ),
			$post_types,
			'side',
			'high'
		);
	}

	public function enqueue_assets( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'review-notes-admin',
			REVIEW_NOTES_URL . 'assets/admin.css',
			array(),
			REVIEW_NOTES_VERSION
		);
	}

	public function render( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$status = get_post_meta( $post->ID, '_review_notes_status', true );
		$notes  = get_post_meta( $post->ID, '_review_notes_text', true );

		if ( '' === $status ) {
			$status = 'needs_review';
		}
		?>
		<p>
			<label for="review_notes_status"><strong><?php esc_html_e( 'Status', 'review-notes' ); ?></strong></label>
			<select name="review_notes_status" id="review_notes_status" class="widefat">
				<?php foreach ( self::get_statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="review_notes_text"><strong><?php esc_html_e( 'Notes', 'review-notes' ); ?></strong></label>
			<textarea name="review_notes_text" id="review_notes_text" class="widefat" rows="4"><?php
				echo esc_textarea( $notes );
			?></textarea>
		</p>
		<?php
	}

	public function save( $post_id ) {
		// Verify the nonce first — if it's missing or invalid, bail before
		// touching anything else. This alone blocks CSRF-style forged saves.
		if (
			! isset( $_POST[ self::NONCE_NAME ] ) ||
			! wp_verify_nonce( $_POST[ self::NONCE_NAME ], self::NONCE_ACTION )
		) {
			return;
		}

		// Skip autosaves — they don't carry the fields we care about and
		// would otherwise silently overwrite real data with empty values.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Capability check: never trust that showing the field means the
		// current request is allowed to save it.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['review_notes_status'] ) ) {
			$status = sanitize_key( wp_unslash( $_POST['review_notes_status'] ) );
			if ( array_key_exists( $status, self::get_statuses() ) ) {
				update_post_meta( $post_id, '_review_notes_status', $status );
			}
		}

		if ( isset( $_POST['review_notes_text'] ) ) {
			$notes = sanitize_textarea_field( wp_unslash( $_POST['review_notes_text'] ) );
			update_post_meta( $post_id, '_review_notes_text', $notes );
		}
	}
}
