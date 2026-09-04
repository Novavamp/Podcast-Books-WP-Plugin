<?php
/**
 * Plugin Name: Jidoba Podcast & Book Library
 * Description: Podcast and book post types with AJAX-filtered shortcode displays.
 * Version: 1.0.3
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Jidoba
 * License: GPL-2.0-or-later
 * Text Domain: jidoba-media-library
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JIDOBA_MEDIA_VERSION', '1.0.3' );
define( 'JIDOBA_MEDIA_FILE', __FILE__ );
define( 'JIDOBA_MEDIA_DIR', plugin_dir_path( __FILE__ ) );
define( 'JIDOBA_MEDIA_URL', plugin_dir_url( __FILE__ ) );

require_once JIDOBA_MEDIA_DIR . 'includes/class-jidoba-media-library.php';

Jidoba_Media_Library::instance();

register_activation_hook( __FILE__, array( 'Jidoba_Media_Library', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Jidoba_Media_Library', 'deactivate' ) );
