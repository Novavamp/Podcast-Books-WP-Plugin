# Jidoba Podcast & Book Library

A WordPress plugin for organising podcast episodes and books into searchable, filterable library displays.

## Features

- Dedicated podcast and book custom post types.
- Podcast topics and book genres.
- Shortcode-based library output.
- AJAX filtering and search.
- Responsive card layouts and pagination.
- Custom metadata for listen and purchase links.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Installation

1. Download the repository or plugin package.
2. Upload the `jidoba-media-library` directory to `wp-content/plugins/`.
3. Activate **Jidoba Podcast & Book Library** in WordPress.
4. Add podcast episodes and books in the WordPress dashboard.
5. Add one of the shortcodes below to a page.

## Shortcodes

```text
[jidoba_podcasts]
[jidoba_podcasts per_page="8" topic="interviews" show_filters="yes"]

[jidoba_books]
[jidoba_books per_page="12" genre="fiction" columns="3" show_filters="yes"]
```

Available attributes are sanitised and bounded by the plugin. The topic and genre values are taxonomy slugs.

## Content management

- Podcast entries use the title and editor for episode information and a custom listen URL field.
- Book entries use the title, editor, featured image, author field, and purchase URL field.
- Podcast topics and book genres are managed as WordPress taxonomies.

## Security

The plugin uses WordPress nonces for its public AJAX filter request and capability checks plus nonces when saving custom metadata. Public AJAX filtering is intended to expose only published library content. Keep WordPress core and the active theme updated, and test plugin compatibility in a staging environment before production upgrades.

## Project structure

```text
jidoba-media-library/
├── assets/
│   ├── css/
│   └── js/
├── includes/
│   └── class-jidoba-media-library.php
└── jidoba-media-library.php
```

## Maintainer

[Precious Okechukwu Nwosu](https://github.com/Novavamp)
