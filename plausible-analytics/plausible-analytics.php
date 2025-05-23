<?php
/**
 * Plugin Name: Plausible Analytics
 * Plugin URI: https://plausible.io
 * Description: Simple and privacy-friendly alternative to Google Analytics.
 * Author: Plausible.io
 * Author URI: https://plausible.io
 * Version: 2.4.0
 * Text Domain: plausible-analytics
 * Domain Path: /languages
 */

namespace Plausible\Analytics\WP;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PLAUSIBLE_ANALYTICS_VERSION', '2.3.1' );
define( 'PLAUSIBLE_ANALYTICS_PLUGIN_FILE', __FILE__ );
define( 'PLAUSIBLE_ANALYTICS_PLUGIN_BASENAME', plugin_basename( PLAUSIBLE_ANALYTICS_PLUGIN_FILE ) );
define( 'PLAUSIBLE_ANALYTICS_PLUGIN_DIR', plugin_dir_path( PLAUSIBLE_ANALYTICS_PLUGIN_FILE ) );
define( 'PLAUSIBLE_ANALYTICS_PLUGIN_URL', plugin_dir_url( PLAUSIBLE_ANALYTICS_PLUGIN_FILE ) );

// Automatically loads files used throughout the plugin.
require_once PLAUSIBLE_ANALYTICS_PLUGIN_DIR . 'vendor/autoload.php';

// Initialize the plugin.
$plugin = new Plugin();
$plugin->register();
