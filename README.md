<div align="center">

# Jidoba Podcast & Book Library

**A WordPress plugin for organising podcast episodes and books into searchable, filterable library displays.**

![WordPress](https://img.shields.io/badge/WordPress-plugin-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![Version](https://img.shields.io/badge/version-1.0.3-334155)

</div>

## Overview

Jidoba Podcast & Book Library registers podcast and book content types and provides shortcode-driven library displays. Its front end supports AJAX-based filtering so visitors can browse media without navigating through a long list of posts.

## Features

- Dedicated podcast and book content types.
- Shortcode-based library output.
- AJAX-filtered display.
- Search and category/filter controls in the library interface.
- Responsive podcast and book card layouts.
- Pagination for library results.

## Requirements

- WordPress 6.0 or later.
- PHP 7.4 or later.

## Installation

1. Download the plugin ZIP or clone this repository.
2. Upload the plugin folder to `wp-content/plugins/`.
3. Activate **Jidoba Podcast & Book Library** from the WordPress Plugins screen.
4. Add podcast and book entries using the content types registered by the plugin.
5. Place the relevant library shortcode on a page.

## Shortcodes

The plugin is designed around shortcode-based displays. Check the plugin's implementation and any bundled usage notes for the exact shortcode attributes supported by the installed version.

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

## Front-end presentation

The library styles are scoped to the `.jidoba-library` component. Podcast and book cards use responsive grids, with reduced-motion support and visible keyboard focus styles.

## Development

The main plugin bootstrap loads the library class, initialises the plugin singleton, and registers activation and deactivation hooks.

## Compatibility note

Test the plugin with your active WordPress theme and any caching, security, or optimisation plugins before deploying it to a live site.

---

Maintained by [Precious Okechukwu Nwosu](https://github.com/Novavamp).