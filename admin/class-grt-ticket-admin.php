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
	 * Add settings link to plugin list table.
	 *
	 * @since    1.0.0
	 * @param    array $links    Existing links.
	 * @return   array           Modified links.
	 */
	public function add_plugin_action_links( $links ) {
		$settings_link = '<a href="admin.php?page=grt-ticket-settings">' . __( 'Settings', 'grt-ticket' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		
		// Check if we are on a plugin page (either via screen ID or GET parameter)
		$is_plugin_page = ( strpos( $screen_id, 'grt-ticket' ) !== false ) || 
						  ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'grt-ticket' ) !== false );

		if ( $is_plugin_page ) {
			// Register styles
			wp_register_style( $this->plugin_name . '-tickets-list', GRT_TICKET_PLUGIN_URL . 'admin/css/tickets-list.css', array(), time(), 'all' );
			wp_register_style( $this->plugin_name . '-chat-interface', GRT_TICKET_PLUGIN_URL . 'admin/css/chat-interface.css', array(), time(), 'all' );
			wp_register_style( $this->plugin_name . '-settings-page', GRT_TICKET_PLUGIN_URL . 'admin/css/settings-page.css', array(), time(), 'all' );
			wp_register_style( $this->plugin_name . '-dashboard', GRT_TICKET_PLUGIN_URL . 'admin/css/dashboard.css', array(), time(), 'all' );

			// Determine which style to enqueue
			if ( $screen_id === 'toplevel_page_grt-ticket' || ( isset( $_GET['page'] ) && $_GET['page'] === 'grt-ticket' ) ) {
				wp_enqueue_style( $this->plugin_name . '-dashboard' );
			} elseif ( $screen_id === 'grt-ticket_page_grt-ticket-list' || ( isset( $_GET['page'] ) && $_GET['page'] === 'grt-ticket-list' ) ) {
				wp_enqueue_style( $this->plugin_name . '-tickets-list' );
			} elseif ( strpos( $screen_id, 'grt-ticket-chat' ) !== false || ( isset( $_GET['page'] ) && $_GET['page'] === 'grt-ticket-chat' ) ) {
				// Always enqueue chat styles on the chat page
				wp_enqueue_style( $this->plugin_name . '-chat-interface' );
				// Also enqueue list styles for the select screen
				wp_enqueue_style( $this->plugin_name . '-tickets-list' );
			} elseif ( strpos( $screen_id, 'grt-ticket-settings' ) !== false || ( isset( $_GET['page'] ) && $_GET['page'] === 'grt-ticket-settings' ) ) {
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

			// Fetch agents (admins and editors)
			$agents = get_users( array( 'role__in' => array( 'administrator', 'editor' ), 'fields' => array( 'ID', 'display_name' ) ) );
			$agents_data = array();
			foreach ( $agents as $agent ) {
				$agents_data[] = array(
					'id'   => $agent->ID,
					'name' => $agent->display_name,
				);
			}

			$data = array(
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'grt_ticket_nonce' ),
				'poll_interval' => get_option( 'grt_ticket_poll_interval', 3000 ),
				'agents'        => $agents_data,
				'i18n'          => array(
					'category_name'         => __( 'Category Name', 'grt-ticket' ),
					'select_image'          => __( 'Select Image', 'grt-ticket' ),
					'select_agent'          => __( 'Select Agent', 'grt-ticket' ),
					'remove'                => __( 'Remove', 'grt-ticket' ),
					'are_you_sure'          => __( 'Are you sure?', 'grt-ticket' ),
					'select_category_image' => __( 'Select Category Image', 'grt-ticket' ),
					'use_this_image'        => __( 'Use this image', 'grt-ticket' ),
				),
			);

			// Enqueue based on screen
			if ( $screen->id === 'toplevel_page_grt-ticket' ) {
				// Dashboard scripts if needed
			} elseif ( strpos( $screen->id, 'grt-ticket-list' ) !== false ) {
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
			array( $this, 'display_dashboard_page' ), // Changed default to dashboard
			'dashicons-tickets-alt',
			30
		);

		// Dashboard submenu
		add_submenu_page(
			'grt-ticket',
			__( 'Dashboard', 'grt-ticket' ),
			__( 'Dashboard', 'grt-ticket' ),
			'manage_options',
			'grt-ticket',
			array( $this, 'display_dashboard_page' )
		);

		// Tickets submenu
		add_submenu_page(
			'grt-ticket',
			__( 'Tickets', 'grt-ticket' ),
			__( 'Tickets', 'grt-ticket' ),
			'manage_options',
			'grt-ticket-list',
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
	 * Display dashboard page.
	 *
	 * @since    1.0.0
	 */
	public function display_dashboard_page() {
		$stats = GRT_Ticket_Database::get_dashboard_stats();
		include GRT_TICKET_PLUGIN_DIR . 'admin/partials/dashboard.php';
	}

	/**
	 * Display tickets list page.
	 *
	 * @since    1.0.0
	 */
	public function display_tickets_page() {
		$args = array();
		$current_user_id = get_current_user_id();

		// Handle filters
		if ( isset( $_GET['assigned_to_me'] ) && '1' === $_GET['assigned_to_me'] ) {
			$args['assigned_agent_id'] = $current_user_id;
		}

		if ( isset( $_GET['status'] ) && ! empty( $_GET['status'] ) ) {
			$args['status'] = sanitize_text_field( $_GET['status'] );
		}

		$tickets = GRT_Ticket_Database::get_tickets( $args );
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
		// Fetch agents for the settings page dropdown
		$agents = get_users( array( 'role__in' => array( 'administrator', 'editor' ), 'fields' => array( 'ID', 'display_name' ) ) );
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
