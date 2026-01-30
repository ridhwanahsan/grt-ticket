<?php
/**
 * Fired during plugin activation
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class GRT_Ticket_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Creates database tables and sets default options.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Table for tickets
		$table_tickets = $wpdb->prefix . 'grt_tickets';
		$sql_tickets = "CREATE TABLE $table_tickets (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL DEFAULT 0,
			user_email varchar(100) NOT NULL,
			user_name varchar(100) NOT NULL,
			theme_name varchar(200) NOT NULL,
			license_code varchar(100) NOT NULL,
			category varchar(100) NOT NULL,
			title varchar(255) NOT NULL,
			description text NOT NULL,
			status enum('open','solved','closed') DEFAULT 'open',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY user_email (user_email),
			KEY status (status),
			KEY created_at (created_at)
		) $charset_collate;";

		// Table for messages
		$table_messages = $wpdb->prefix . 'grt_ticket_messages';
		$sql_messages = "CREATE TABLE $table_messages (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			ticket_id bigint(20) NOT NULL,
			sender_type enum('admin','user') NOT NULL,
			sender_name varchar(100) NOT NULL,
			message text NOT NULL,
			attachment_url varchar(255) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY ticket_id (ticket_id),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_tickets );
		dbDelta( $sql_messages );

		// Set default options
		$default_categories = array(
			'Installation Issue',
			'Customization Help',
			'Bug Report',
			'Feature Request',
			'License Issue',
		);

		add_option( 'grt_ticket_categories', implode( ',', $default_categories ) );
		add_option( 'grt_ticket_admin_name', 'Support Team' );
		add_option( 'grt_ticket_per_page', 20 );
		add_option( 'grt_ticket_poll_interval', 3000 ); // 3 seconds in milliseconds
		add_option( 'grt_ticket_version', GRT_TICKET_VERSION );
	}
}
