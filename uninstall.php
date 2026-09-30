<?php
/**
 * Fires only when the plugin is deleted from the Plugins screen (not on
 * simple deactivation), so it's the right place to remove the option
 * this plugin created. Post meta is intentionally left in place —
 * deleting a plugin shouldn't silently erase content-level data the
 * site owner may still want.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'review_notes_post_types' );
