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
			// Register styles
			wp_register_style( $this->plugin_name . '-tickets-list', GRT_TICKET_PLUGIN_URL . 'admin/css/tickets-list.css', array(), $this->version, 'all' );
			wp_register_style( $this->plugin_name . '-chat-interface', GRT_TICKET_PLUGIN_URL . 'admin/css/chat-interface.css', array(), $this->version, 'all' );
			wp_register_style( $this->plugin_name . '-settings-page', GRT_TICKET_PLUGIN_URL . 'admin/css/settings-page.css', array(), $this->version, 'all' );

			// Enqueue based on screen
			if ( $screen->id === 'toplevel_page_grt-ticket' || $screen->id === 'grt-ticket_page_grt-ticket' ) {
				wp_enqueue_style( $this->plugin_name . '-tickets-list' );
			} elseif ( strpos( $screen->id, 'grt-ticket-chat' ) !== false ) {
				if ( isset( $_GET['ticket_id'] ) && intval( $_GET['ticket_id'] ) > 0 ) {
					wp_enqueue_style( $this->plugin_name . '-chat-interface' );
				} else {
					// Chat selection page uses tickets list styles (table, status, buttons)
					wp_enqueue_style( $this->plugin_name . '-tickets-list' );
				}
			} elseif ( strpos( $screen->id, 'grt-ticket-settings' ) !== false ) {
				wp_enqueue_style( $this->plugin_name . '-settings-page' );
			}
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

			// Register scripts
			wp_register_script( $this->plugin_name . '-tickets-list', GRT_TICKET_PLUGIN_URL . 'admin/js/tickets-list.js', array( 'jquery' ), $this->version, false );
			wp_register_script( $this->plugin_name . '-chat-interface', GRT_TICKET_PLUGIN_URL . 'admin/js/chat-interface.js', array( 'jquery' ), $this->version, false );
			wp_register_script( $this->plugin_name . '-settings-page', GRT_TICKET_PLUGIN_URL . 'admin/js/settings-page.js', array( 'jquery' ), $this->version, false );

			$data = array(
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'grt_ticket_nonce' ),
				'poll_interval' => get_option( 'grt_ticket_poll_interval', 3000 ),
				'i18n'          => array(
					'category_name'         => __( 'Category Name', 'grt-ticket' ),
					'select_image'          => __( 'Select Image', 'grt-ticket' ),
					'remove'                => __( 'Remove', 'grt-ticket' ),
					'are_you_sure'          => __( 'Are you sure?', 'grt-ticket' ),
					'select_category_image' => __( 'Select Category Image', 'grt-ticket' ),
					'use_this_image'        => __( 'Use this image', 'grt-ticket' ),
				),
			);

			// Enqueue based on screen
			if ( $screen->id === 'toplevel_page_grt-ticket' || $screen->id === 'grt-ticket_page_grt-ticket' ) {
				wp_enqueue_script( $this->plugin_name . '-tickets-list' );
				wp_localize_script( $this->plugin_name . '-tickets-list', 'grtTicketAdmin', $data );
			} elseif ( strpos( $screen->id, 'grt-ticket-chat' ) !== false ) {
				if ( isset( $_GET['ticket_id'] ) && intval( $_GET['ticket_id'] ) > 0 ) {
					wp_enqueue_script( $this->plugin_name . '-chat-interface' );
					wp_localize_script( $this->plugin_name . '-chat-interface', 'grtTicketAdmin', $data );
				} else {
					// Chat selection page uses tickets list scripts (delete button)
					wp_enqueue_script( $this->plugin_name . '-tickets-list' );
					wp_localize_script( $this->plugin_name . '-tickets-list', 'grtTicketAdmin', $data );
				}
			} elseif ( strpos( $screen->id, 'grt-ticket-settings' ) !== false ) {
				wp_enqueue_script( $this->plugin_name . '-settings-page' );
				wp_localize_script( $this->plugin_name . '-settings-page', 'grtTicketAdmin', $data );
			}
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
	 * Display canned responses page.
	 *
	 * @since    1.0.0
	 */
	public function display_canned_responses_page() {
		// Handle form submission
		if ( isset( $_POST['grt_add_canned_response'] ) && check_admin_referer( 'grt_add_canned_response', 'grt_canned_response_nonce' ) ) {
			$title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
			$response = isset( $_POST['response'] ) ? wp_kses_post( $_POST['response'] ) : '';
			
			if ( ! empty( $title ) && ! empty( $response ) ) {
				GRT_Ticket_Database::add_canned_response( $title, $response );
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Canned response added.', 'grt-ticket' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Please fill in all fields.', 'grt-ticket' ) . '</p></div>';
			}
		}

		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['id'] ) && check_admin_referer( 'grt_delete_canned_response' ) ) {
			GRT_Ticket_Database::delete_canned_response( (int) $_GET['id'] );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Canned response deleted.', 'grt-ticket' ) . '</p></div>';
		}

		$canned_responses = GRT_Ticket_Database::get_canned_responses();
		include GRT_TICKET_PLUGIN_DIR . 'admin/partials/canned-responses.php';
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
