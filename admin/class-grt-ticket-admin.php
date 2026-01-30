<?php
/**
 * The admin-specific functionality of the plugin
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for admin area.
 */
class GRT_Ticket_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name    The name of this plugin.
	 * @param    string $version        The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$screen = get_current_screen();
		
		if ( strpos( $screen->id, 'grt-ticket' ) !== false ) {
			wp_enqueue_style(
				$this->plugin_name,
				GRT_TICKET_PLUGIN_URL . 'admin/css/grt-ticket-admin.css',
				array(),
				$this->version,
				'all'
			);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		
		if ( strpos( $screen->id, 'grt-ticket' ) !== false ) {
			wp_enqueue_media();
			wp_enqueue_script(
				$this->plugin_name,
				GRT_TICKET_PLUGIN_URL . 'admin/js/grt-ticket-admin.js',
				array( 'jquery' ),
				$this->version,
				false
			);

			wp_localize_script(
				$this->plugin_name,
				'grtTicketAdmin',
				array(
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'nonce'         => wp_create_nonce( 'grt_ticket_nonce' ),
					'poll_interval' => get_option( 'grt_ticket_poll_interval', 3000 ),
				)
			);
		}
	}

	/**
	 * Register admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		// Main menu
		add_menu_page(
			__( 'GRT Ticket', 'grt-ticket' ),
			__( 'GRT Ticket', 'grt-ticket' ),
			'manage_options',
			'grt-ticket',
			array( $this, 'display_tickets_page' ),
			'dashicons-tickets-alt',
			30
		);

		// Tickets submenu (default)
		add_submenu_page(
			'grt-ticket',
			__( 'Tickets', 'grt-ticket' ),
			__( 'Tickets', 'grt-ticket' ),
			'manage_options',
			'grt-ticket',
			array( $this, 'display_tickets_page' )
		);

		// Support Chat submenu
		add_submenu_page(
			'grt-ticket',
			__( 'Support Chat', 'grt-ticket' ),
			__( 'Support Chat', 'grt-ticket' ),
			'manage_options',
			'grt-ticket-chat',
			array( $this, 'display_chat_page' )
		);

		// Settings submenu
		add_submenu_page(
			'grt-ticket',
			__( 'Settings', 'grt-ticket' ),
			__( 'Settings', 'grt-ticket' ),
			'manage_options',
			'grt-ticket-settings',
			array( $this, 'display_settings_page' )
		);
	}

	/**
	 * Display tickets list page.
	 *
	 * @since    1.0.0
	 */
	public function display_tickets_page() {
		$tickets = GRT_Ticket_Database::get_tickets();
		include GRT_TICKET_PLUGIN_DIR . 'admin/partials/tickets-list.php';
	}

	/**
	 * Display chat page.
	 *
	 * @since    1.0.0
	 */
	public function display_chat_page() {
		$ticket_id = isset( $_GET['ticket_id'] ) ? (int) $_GET['ticket_id'] : 0;
		
		if ( $ticket_id ) {
			$ticket = GRT_Ticket_Database::get_ticket( $ticket_id );
			$messages = GRT_Ticket_Database::get_messages( $ticket_id );
			include GRT_TICKET_PLUGIN_DIR . 'admin/partials/chat-interface.php';
		} else {
			$tickets = GRT_Ticket_Database::get_tickets( array( 'limit' => 10 ) );
			include GRT_TICKET_PLUGIN_DIR . 'admin/partials/chat-select.php';
		}
	}

	/**
	 * Display settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		include GRT_TICKET_PLUGIN_DIR . 'admin/partials/settings-page.php';
	}

	/**
	 * Register settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting( 'grt_ticket_settings', 'grt_ticket_categories', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'grt_ticket_settings', 'grt_ticket_admin_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'grt_ticket_settings', 'grt_ticket_per_page', array( 'sanitize_callback' => 'absint' ) );
		register_setting( 'grt_ticket_settings', 'grt_ticket_poll_interval', array( 'sanitize_callback' => 'absint' ) );
	}
}
