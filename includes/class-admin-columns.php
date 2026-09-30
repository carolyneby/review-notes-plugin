<?php
/**
 * Adds a "Review Status" column to the admin post list for every
 * enabled post type, and makes it sortable. This is the piece that
 * makes the feature actually useful day-to-day — an editor can see
 * every post's review state at a glance instead of opening each one.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Review_Notes_Admin_Columns {

	public function __construct() {
		foreach ( $this->get_enabled_post_types() as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'render_column' ), 10, 2 );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'make_sortable' ) );
		}

		add_action( 'pre_get_posts', array( $this, 'sort_by_status' ) );
	}

	private function get_enabled_post_types() {
		return (array) get_option( 'review_notes_post_types', array( 'post', 'page' ) );
	}

	public function add_column( $columns ) {
		// Insert the new column right before the date column rather than
		// tacking it on the end, so it reads naturally next to the rest
		// of the workflow metadata.
		$new_columns = array();
		foreach ( $columns as $key => $label ) {
			if ( 'date' === $key ) {
				$new_columns['review_notes_status'] = __( 'Review Status', 'review-notes' );
			}
			$new_columns[ $key ] = $label;
		}
		return $new_columns;
	}

	public function render_column( $column, $post_id ) {
		if ( 'review_notes_status' !== $column ) {
			return;
		}

		$status   = get_post_meta( $post_id, '_review_notes_status', true );
		$statuses = Review_Notes_Meta_Box::get_statuses();
		$label    = $statuses[ $status ] ?? __( 'Needs review', 'review-notes' );

		printf( '<span class="review-notes-badge review-notes-badge--%1$s">%2$s</span>', esc_attr( $status ?: 'needs_review' ), esc_html( $label ) );
	}

	public function make_sortable( $columns ) {
		$columns['review_notes_status'] = 'review_notes_status';
		return $columns;
	}

	public function sort_by_status( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'review_notes_status' !== $query->get( 'orderby' ) ) {
			return;
		}

		$query->set( 'meta_key', '_review_notes_status' );
		$query->set( 'orderby', 'meta_value' );
	}
}
