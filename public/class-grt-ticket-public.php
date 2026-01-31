<?php
/**
 * The public-facing functionality of the plugin
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for public-facing side.
 */
class GRT_Ticket_Public {

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
	 * @param    string $plugin_name    The name of the plugin.
	 * @param    string $version        The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		
		// Register query var
		add_filter( 'query_vars', function( $vars ) {
			$vars[] = 'ticket'; // matches the endpoint name
			$vars[] = 'grt_ticket_id'; // internal use if needed, but endpoint uses 'ticket'
			return $vars;
		});

		// Handle the endpoint mapping
		add_filter( 'request', function( $vars ) {
			if ( isset( $vars['ticket'] ) ) {
				$vars['grt_ticket_id'] = $vars['ticket'];
			}
			return $vars;
		});
	}

	/**
	 * Hide admin bar for non-admin users.
	 *
	 * @since    1.0.0
	 */
	public function hide_admin_bar_for_non_admins() {
		if ( ! current_user_can( 'manage_options' ) && ! is_admin() ) {
			show_admin_bar( false );
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		// Enqueue Dashicons for frontend usage
		wp_enqueue_style( 'dashicons' );

		// Register styles
		wp_register_style(
			$this->plugin_name . '-ticket-form',
			GRT_TICKET_PLUGIN_URL . 'public/css/ticket-form.css',
			array(),
			time(),
			'all'
		);
		wp_register_style(
			$this->plugin_name . '-chat-interface',
			GRT_TICKET_PLUGIN_URL . 'public/css/chat-interface.css',
			array(),
			time(),
			'all'
		);

		// Check if we are on a page with the shortcode and enqueue styles early
		global $post;
		$should_enqueue = false;

		// Check 1: Shortcode in post content
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'grt_ticket' ) ) {
			$should_enqueue = true;
		}

		// Check 2: Query Vars or GET param (stronger signal)
		if ( get_query_var( 'grt_ticket_id' ) || get_query_var( 'ticket' ) || isset( $_GET['ticket_id'] ) ) {
			$should_enqueue = true;
		}

		// Check 3: Fallback - if we are on a singular post/page, just enqueue it to be safe
		// This ensures it loads even if shortcode detection fails (e.g. inside a block or widget)
		if ( is_singular() ) {
			$should_enqueue = true;
		}

		if ( $should_enqueue ) {
			wp_enqueue_style( $this->plugin_name . '-chat-interface' );
			wp_enqueue_style( $this->plugin_name . '-ticket-form' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// Register scripts
		wp_register_script(
			$this->plugin_name . '-ticket-form',
			GRT_TICKET_PLUGIN_URL . 'public/js/ticket-form.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_register_script(
			$this->plugin_name . '-chat-interface',
			GRT_TICKET_PLUGIN_URL . 'public/js/chat-interface.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		$data = array(
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'grt_ticket_nonce' ),
			'poll_interval' => get_option( 'grt_ticket_poll_interval', 3000 ),
		);

		wp_localize_script( $this->plugin_name . '-ticket-form', 'grtTicketPublic', $data );
		wp_localize_script( $this->plugin_name . '-chat-interface', 'grtTicketPublic', $data );
	}

	/**
	 * Register shortcodes.
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'grt_ticket', array( $this, 'render_ticket_shortcode' ) );
		
		// Add rewrite endpoint for pretty URLs: .../page-name/ticket/123/
		add_rewrite_endpoint( 'ticket', EP_PAGES );
		
		// Flush rewrite rules if our rule is missing (auto-heal)
		if ( ! get_option( 'grt_ticket_flush_rewrite_rules' ) ) {
			add_action( 'shutdown', function() {
				flush_rewrite_rules();
				update_option( 'grt_ticket_flush_rewrite_rules', true );
			});
		}
	}

	/**
	 * Render the ticket shortcode.
	 *
	 * @since    1.0.0
	 * @param    array $atts    Shortcode attributes.
	 * @return   string         Shortcode output.
	 */
	public function render_ticket_shortcode( $atts ) {
		// Check for pretty permalink usage first (ticket_id via rewrite endpoint)
		$ticket_id_query_var = get_query_var( 'grt_ticket_id' );
		if ( $ticket_id_query_var ) {
			return $this->render_chat_interface( (int) $ticket_id_query_var );
		}

		// Fallback to GET parameters (for compatibility or if rewrite rules fail)
		$ticket_id = isset( $_GET['ticket_id'] ) ? (int) $_GET['ticket_id'] : 0;
		// Legacy support or specific use cases (user_email now optional in URL as we prefer checking logged-in state)
		$user_email = isset( $_GET['user_email'] ) ? sanitize_email( $_GET['user_email'] ) : '';

		if ( $ticket_id ) {
			return $this->render_chat_interface( $ticket_id, $user_email );
		}

		// Default: show ticket submission form
		return $this->render_ticket_form();
	}

	/**
	 * Render ticket submission form.
	 *
	 * @since    1.0.0
	 * @return   string    Form HTML.
	 */
	private function render_ticket_form() {
		// Enqueue assets
		wp_enqueue_style( $this->plugin_name . '-ticket-form' );
		wp_enqueue_script( $this->plugin_name . '-ticket-form' );

		// Prepare data for the form template
		$current_user = wp_get_current_user();
		$is_logged_in = is_user_logged_in();
		$user_name = $is_logged_in ? $current_user->display_name : '';
		$user_email = $is_logged_in ? $current_user->user_email : '';
		
		// Fetch recent tickets if logged in
		$recent_tickets = array();
		if ( $is_logged_in ) {
			$recent_tickets = GRT_Ticket_Database::get_tickets( array(
				'user_id' => $current_user->ID,
				'limit'   => 5,
			) );
		}
		
		ob_start();
		include GRT_TICKET_PLUGIN_DIR . 'public/partials/ticket-form.php';
		return ob_get_clean();
	}

	/**
	 * Render chat interface.
	 *
	 * @since    1.0.0
	 * @param    int    $ticket_id     Ticket ID.
	 * @param    string $user_email    User email.
	 * @return   string                Chat HTML.
	 */
	private function render_chat_interface( $ticket_id, $user_email = '' ) {
		// Enqueue assets
		wp_enqueue_style( $this->plugin_name . '-chat-interface' );
		wp_enqueue_script( $this->plugin_name . '-chat-interface' );

		$ticket = GRT_Ticket_Database::get_ticket( $ticket_id );
		
		if ( ! $ticket ) {
			return '<div class="grt-ticket-error">' . esc_html__( 'Ticket not found.', 'grt-ticket' ) . '</div>';
		}

		$has_access = false;

		// Access check
		if ( is_user_logged_in() ) {
			$current_user_id = get_current_user_id();
			$current_user_email = wp_get_current_user()->user_email;

			// Check by ID or Email
			if ( (int) $ticket->user_id === $current_user_id || $ticket->user_email === $current_user_email || current_user_can( 'manage_options' ) ) {
				$has_access = true;
			}
		} else {
			// Guest access via matching email 
			if ( $user_email && $ticket->user_email === $user_email ) {
				$has_access = true;
			} elseif ( ! $user_email && ! is_user_logged_in() ) {
				// Guest trying to access pretty URL without email parameter:
				// They must login to view it.
				return '<div class="grt-ticket-error">' . sprintf( esc_html__( 'Please %1$slogin%2$s to view this ticket.', 'grt-ticket' ), '<a href="' . wp_login_url( get_permalink() ) . '">', '</a>' ) . '</div>';
			}
		}

		// Verify ticket belongs to user
		if ( ! $has_access ) {
			return '<div class="grt-ticket-error">' . esc_html__( 'Access denied.', 'grt-ticket' ) . '</div>';
		}

		$messages = GRT_Ticket_Database::get_messages( $ticket_id );
		

		// Securely fetch "Your Tickets" list
		$args = array( 'limit' => 10 );
		
		if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
			// Logged in user: ONLY fetch by their ID
			$args['user_id'] = get_current_user_id();
			// Explicitly unset user_email to avoid OR logic ambiguity in database query
			// depending on implementation, but passing ID is safest for logged in users
		} else {
			// Guest: Use the email from the VALIDATED ticket they are currently viewing
			// This prevents them from changing 'user_email' in URL to see others' tickets
			// while viewing their own valid ticket.
			$args['user_email'] = $ticket->user_email;
			$args['user_id'] = 0; // Ensure no user_id conflict
		}
		
		$user_tickets = GRT_Ticket_Database::get_tickets( $args );

		ob_start();
		include GRT_TICKET_PLUGIN_DIR . 'public/partials/chat-interface.php';
		return ob_get_clean();
	}
}
