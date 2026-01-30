<?php
/**
 * The core plugin class
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class GRT_Ticket {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      GRT_Ticket_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version     = GRT_TICKET_VERSION;
		$this->plugin_name = 'grt-ticket';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_ajax_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// The class responsible for orchestrating the actions and filters of the core plugin.
		require_once GRT_TICKET_PLUGIN_DIR . 'includes/class-grt-ticket-loader.php';

		// The class responsible for defining internationalization functionality of the plugin.
		require_once GRT_TICKET_PLUGIN_DIR . 'includes/class-grt-ticket-i18n.php';

		// The class responsible for database operations.
		require_once GRT_TICKET_PLUGIN_DIR . 'includes/class-grt-ticket-database.php';

		// The class responsible for AJAX handlers.
		require_once GRT_TICKET_PLUGIN_DIR . 'includes/class-grt-ticket-ajax.php';

		// The class responsible for defining all actions that occur in the admin area.
		require_once GRT_TICKET_PLUGIN_DIR . 'admin/class-grt-ticket-admin.php';

		// The class responsible for defining all actions that occur in the public-facing side of the site.
		require_once GRT_TICKET_PLUGIN_DIR . 'public/class-grt-ticket-public.php';

		// The class responsible for email piping.
		require_once GRT_TICKET_PLUGIN_DIR . 'includes/class-grt-ticket-email-piping.php';

		$this->loader = new GRT_Ticket_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new GRT_Ticket_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new GRT_Ticket_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		// Email Piping Hooks
		if ( class_exists( 'GRT_Ticket_Email_Piping' ) ) {
			$plugin_piping = new GRT_Ticket_Email_Piping();
			$this->loader->add_action( 'grt_ticket_check_emails_cron', $plugin_piping, 'check_emails' );
			$this->loader->add_filter( 'cron_schedules', $this, 'add_cron_schedules' );
		} else {
			error_log( 'GRT Ticket: GRT_Ticket_Email_Piping class not found.' );
		}
	}

	/**
	 * Add custom cron schedules.
	 *
	 * @since    1.0.0
	 * @param    array $schedules List of current schedules.
	 * @return   array Modified list of schedules.
	 */
	public function add_cron_schedules( $schedules ) {
		$schedules['grt_5_min'] = array(
			'interval' => 300,
			'display'  => __( 'Every 5 Minutes', 'grt-ticket' ),
		);
		return $schedules;
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new GRT_Ticket_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'hide_admin_bar_for_non_admins' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
	}

	/**
	 * Register all AJAX hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_ajax_hooks() {
		$plugin_ajax = new GRT_Ticket_Ajax();

		// Public AJAX (logged in and logged out users)
		$this->loader->add_action( 'wp_ajax_grt_ticket_submit', $plugin_ajax, 'submit_ticket' );
		$this->loader->add_action( 'wp_ajax_nopriv_grt_ticket_submit', $plugin_ajax, 'submit_ticket' );

		$this->loader->add_action( 'wp_ajax_grt_ticket_send_message', $plugin_ajax, 'send_message' );
		$this->loader->add_action( 'wp_ajax_nopriv_grt_ticket_send_message', $plugin_ajax, 'send_message' );

		$this->loader->add_action( 'wp_ajax_grt_ticket_get_messages', $plugin_ajax, 'get_messages' );
		$this->loader->add_action( 'wp_ajax_nopriv_grt_ticket_get_messages', $plugin_ajax, 'get_messages' );

		$this->loader->add_action( 'wp_ajax_grt_ticket_get_tickets', $plugin_ajax, 'get_tickets' );
		$this->loader->add_action( 'wp_ajax_nopriv_grt_ticket_get_tickets', $plugin_ajax, 'get_tickets' );

		$this->loader->add_action( 'wp_ajax_grt_ticket_submit_rating', $plugin_ajax, 'submit_rating' );
		$this->loader->add_action( 'wp_ajax_nopriv_grt_ticket_submit_rating', $plugin_ajax, 'submit_rating' );

		// Admin only AJAX
		$this->loader->add_action( 'wp_ajax_grt_ticket_mark_solved', $plugin_ajax, 'mark_solved' );
		$this->loader->add_action( 'wp_ajax_grt_ticket_delete', $plugin_ajax, 'delete_ticket' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    GRT_Ticket_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
