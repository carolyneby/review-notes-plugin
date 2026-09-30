<?php
/**
 * Registers [review_status] — a small front-end shortcode that shows
 * the current review status of a post to logged-in users only (an
 * editorial team previewing drafts, for example). Demonstrates the
 * Shortcode API and output escaping on the front end.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Review_Notes_Shortcode {

	public function __construct() {
		add_shortcode( 'review_status', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		// Never show internal review state to anonymous visitors.
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'post_id' => get_the_ID(),
			),
			$atts,
			'review_status'
		);

		$post_id = absint( $atts['post_id'] );
		if ( ! $post_id ) {
			return '';
		}

		$status   = get_post_meta( $post_id, '_review_notes_status', true );
		$statuses = Review_Notes_Meta_Box::get_statuses();
		$label    = $statuses[ $status ] ?? __( 'Needs review', 'review-notes' );

		return sprintf(
			'<span class="review-notes-badge review-notes-badge--%1$s">%2$s</span>',
			esc_attr( $status ?: 'needs_review' ),
			esc_html( $label )
		);
	}
}
