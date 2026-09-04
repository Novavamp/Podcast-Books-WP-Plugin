=== Jidoba Podcast & Book Library ===
Contributors: jidoba
Tags: podcast, books, ajax, shortcode
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later

Podcast and book post types with responsive, AJAX-filtered shortcode displays.

== Installation ==

1. Upload the `jidoba-media-library` folder to `/wp-content/plugins/` or upload its ZIP in Plugins > Add New.
2. Activate "Jidoba Podcast & Book Library".
3. Add Podcast Topics and Book Genres, then publish your items.
4. Add `[jidoba_podcasts]` and/or `[jidoba_books]` to a page.

== Shortcodes ==

`[jidoba_podcasts]`

Options: `per_page="4"`, `topic="topic-slug"`, `show_filters="yes"`.

`[jidoba_books]`

Options: `per_page="8"`, `genre="genre-slug"`, `show_filters="yes"`, `columns="4"`.

Examples:

`[jidoba_podcasts per_page="6" topic="business"]`

`[jidoba_books per_page="8" genre="finance" columns="4"]`

Book covers use the WordPress Featured Image. Podcast descriptions use the main content editor; excerpts are used when provided.
