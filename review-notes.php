<?php
/**
 * Plugin Name:       Review Notes
 * Plugin URI:        https://github.com/carolyneby/review-notes
 * Description:       Adds an internal editorial review workflow to any post type — a status field, reviewer notes, a sortable admin column, and a shortcode to surface the status to logged-in users.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Carolyn Eby
 * Author URI:        https://github.com/carolyneby
 * License:            GPL v2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:        review-notes
 */

// Block direct access — this file should only ever be loaded by WordPress itself.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REVIEW_NOTES_VERSION', '1.0.0' );
define( 'REVIEW_NOTES_PATH', plugin_dir_path( __FILE__ ) );
define( 'REVIEW_NOTES_URL', plugin_dir_url( __FILE__ ) );

require_once REVIEW_NOTES_PATH . 'includes/class-settings.php';
require_once REVIEW_NOTES_PATH . 'includes/class-meta-box.php';
require_once REVIEW_NOTES_PATH . 'includes/class-admin-columns.php';
require_once REVIEW_NOTES_PATH . 'includes/class-shortcode.php';

/**
 * Boot the plugin. Each piece registers its own hooks in its constructor,
 * so this function just needs to instantiate them once.
 */
function review_notes_init() {
	new Review_Notes_Settings();
	new Review_Notes_Meta_Box();
	new Review_Notes_Admin_Columns();
	new Review_Notes_Shortcode();
}
add_action( 'plugins_loaded', 'review_notes_init' );

/**
 * Register the plugin's default settings on activation so the settings
 * page and meta box have sane defaults from the first load, rather than
 * relying on every call site to handle "option doesn't exist yet."
 */
function review_notes_activate() {
	if ( false === get_option( 'review_notes_post_types' ) ) {
		add_option( 'review_notes_post_types', array( 'post', 'page' ) );
	}
}
register_activation_hook( __FILE__, 'review_notes_activate' );
