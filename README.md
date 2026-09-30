# Review Notes — a WordPress Plugin

A small, real WordPress plugin built from scratch (no boilerplate generator)
to add an internal editorial review workflow: a status field, reviewer
notes, a sortable admin column, and a shortcode to surface the status
on the front end for logged-in users.

I built this specifically to round out my WordPress portfolio with
genuine plugin-development experience — most of my professional
WordPress work has been long-term site ownership and PHP/MySQL
application development rather than building themes/plugins from
scratch, so this project deliberately covers that ground.

## What it does

- Adds a **meta box** to the post/page edit screen with a review status
  dropdown (Needs review / In progress / Approved) and a notes field
- Adds a **sortable admin column** so editors can see every post's
  review status at a glance from the post list, without opening each one
- Adds a `[review_status]` **shortcode** that shows the current status
  to logged-in users on the front end (e.g. for an internal preview page)
- Adds a **settings page** (Settings → Review Notes) to choose which
  post types get the workflow

## WordPress concepts this demonstrates

This was built to deliberately touch the areas a plugin-development
interview question is most likely to probe:

| Area | Where |
|---|---|
| Hooks (actions & filters) | Throughout — `add_meta_box`, `save_post`, `manage_posts_columns`, `pre_get_posts` |
| Custom post meta | `class-meta-box.php` — read/write via `get_post_meta` / `update_post_meta` |
| Secure form handling | `class-meta-box.php::save()` — nonce verification, capability check, autosave guard, sanitization on every field |
| Settings API | `class-settings.php` — `register_setting`, `add_settings_field` |
| Admin list table customization | `class-admin-columns.php` — custom sortable column |
| Shortcode API | `class-shortcode.php` |
| Output escaping | `esc_html`, `esc_attr`, `esc_textarea` used at every output point, not just on save |
| Clean uninstall | `uninstall.php` — removes plugin options without touching post content |

## Architecture notes

- Each feature (settings, meta box, admin columns, shortcode) is its
  own class with its own constructor wiring up its own hooks — nothing
  is registered from a giant central function. This mirrors how a
  larger plugin would actually be organized, and made each piece easy
  to test/verify independently while building it.
- All post type registration reads from a single option
  (`review_notes_post_types`) set on the settings page, so the meta
  box and the admin columns both stay in sync with one source of truth
  instead of duplicating a hardcoded list.

## Installing locally

1. Copy the `review-notes` folder into `wp-content/plugins/` on a local
   WordPress install (e.g. via [Local](https://localwp.com/) or
   [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)).
2. Activate it from the Plugins screen.
3. Edit any post — the "Editorial Review" box appears in the sidebar.
4. Check Settings → Review Notes to change which post types are enabled.

## Possible next steps

- Add a REST API endpoint so the status could be read/written from a
  headless front end (ties back into the headless WP + React project)
- Add email notifications when a post's status changes to "Approved"
- Add a Gutenberg sidebar panel as a modern alternative to the classic
  meta box
