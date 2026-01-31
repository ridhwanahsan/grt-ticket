<?php
/**
 * Plugin Name:       GRT Ticket
 * Plugin URI:        https://github.com/ridhwanahsan/grt-ticket
 * Description:       A complete support ticket system with real-time chat functionality for WordPress.
 * Version:           1.0.5
 * Author:            GRT Team
 * Author URI:        https://github.com/ridhwanahsan
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       grt-ticket
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'GRT_TICKET_VERSION', '1.0.6' );
define( 'GRT_TICKET_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GRT_TICKET_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GRT_TICKET_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function grt_ticket_activate() {
	require_once GRT_TICKET_PLUGIN_DIR . 'includes/class-grt-ticket-activator.php';
	GRT_Ticket_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function grt_ticket_deactivate() {
	require_once GRT_TICKET_PLUGIN_DIR . 'includes/class-grt-ticket-deactivator.php';
	GRT_Ticket_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'grt_ticket_activate' );
register_deactivation_hook( __FILE__, 'grt_ticket_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require GRT_TICKET_PLUGIN_DIR . 'includes/class-grt-ticket.php';

/**
 * Begins execution of the plugin.
 */
function grt_ticket_run() {
	$plugin = new GRT_Ticket();
	$plugin->run();
}

grt_ticket_run();
