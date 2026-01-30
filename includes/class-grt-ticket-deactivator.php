<?php
/**
 * Fired during plugin deactivation
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class GRT_Ticket_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Cleanup tasks if needed
		// Note: We don't drop tables on deactivation, only on uninstall
		
		wp_clear_scheduled_hook( 'grt_ticket_check_emails_cron' );
	}
}
