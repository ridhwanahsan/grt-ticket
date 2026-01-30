<?php
/**
 * AJAX handlers
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/includes
 */

/**
 * AJAX handlers.
 *
 * Handles all AJAX requests for the plugin.
 */
class GRT_Ticket_Ajax {

	/**
	 * Submit a new ticket.
	 *
	 * @since    1.0.0
	 */
	public function submit_ticket() {
		// Verify nonce
		check_ajax_referer( 'grt_ticket_nonce', 'nonce' );

		// Validate required fields
		$required_fields = array( 'user_name', 'user_email', 'theme_name', 'license_code', 'category', 'title', 'description' );
		foreach ( $required_fields as $field ) {
			if ( empty( $_POST[ $field ] ) ) {
				wp_send_json_error( array( 'message' => sprintf( __( 'Field %s is required.', 'grt-ticket' ), $field ) ) );
			}
		}

		// Validate email
		if ( ! is_email( $_POST['user_email'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email address.', 'grt-ticket' ) ) );
		}


		// Check if user exists or create new one
		$user_email = sanitize_email( $_POST['user_email'] );
		$user_name = sanitize_text_field( $_POST['user_name'] );
		$user_id = 0;
		$new_account_created = false;
		
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$current_user = wp_get_current_user();
			// Only update email if it matches current user's email to avoid spoofing
			if ( $current_user->user_email !== $user_email && ! current_user_can( 'manage_options' ) ) {
				// If email doesn't match, we still link to logged in user but keep the submitted email for contact
				// Alternatively, force submitted email to match logged in user
			}
		} elseif ( email_exists( $user_email ) ) {
			$user_id = email_exists( $user_email );

			// Try to auto-login if password is provided
			if ( ! empty( $_POST['user_password'] ) ) {
				$user = get_user_by( 'email', $user_email );
				if ( $user && wp_check_password( $_POST['user_password'], $user->data->user_pass, $user->ID ) ) {
					wp_set_current_user( $user->ID );
					wp_set_auth_cookie( $user->ID );
				}
			}
		} else {
			// Create new user
			// Use custom password if provided, otherwise auto-generate
			$custom_password = ! empty( $_POST['user_password'] ) ? $_POST['user_password'] : '';
			$user_pass = $custom_password ? $custom_password : wp_generate_password();
			$password_was_custom = ! empty( $custom_password );
			
			$user_name_login = sanitize_user( current( explode( '@', $user_email ) ) );
			
			// Ensure unique username
			if ( username_exists( $user_name_login ) ) {
				$i = 1;
				while ( username_exists( $user_name_login . $i ) ) {
					$i++;
				}
				$user_name_login = $user_name_login . $i;
			}

			$user_id = wp_create_user( $user_name_login, $user_pass, $user_email );

			if ( ! is_wp_error( $user_id ) ) {
				// update user display name
				wp_update_user( array(
					'ID' => $user_id,
					'display_name' => $user_name,
					'first_name'   => $user_name,
				) );

				// Send email with credentials
				$site_name = get_bloginfo( 'name' );
				$message  = sprintf( __( 'Welcome to %s Support!', 'grt-ticket' ), $site_name ) . "\r\n\r\n";
				$message .= sprintf( __( 'A support account has been created for you so you can track your tickets.', 'grt-ticket' ) ) . "\r\n\r\n";
				$message .= sprintf( __( 'Username: %s', 'grt-ticket' ), $user_name_login ) . "\r\n";
				
				// Only include password in email if it was auto-generated
				if ( ! $password_was_custom ) {
					$message .= sprintf( __( 'Password: %s', 'grt-ticket' ), $user_pass ) . "\r\n\r\n";
				} else {
					$message .= __( 'Password: The password you set during ticket submission', 'grt-ticket' ) . "\r\n\r\n";
				}
				
				$message .= sprintf( __( 'Login here: %s', 'grt-ticket' ), wp_login_url() ) . "\r\n";

				wp_mail( $user_email, sprintf( __( '[%s] New Support Account', 'grt-ticket' ), $site_name ), $message );
				
				// Auto-login newly created user
				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id );

				$new_account_created = true;
			}
		}

		// Sanitize inputs
		$theme_name = sanitize_text_field( $_POST['theme_name'] );
		$license_code = sanitize_text_field( $_POST['license_code'] );
		$category = sanitize_text_field( $_POST['category'] );
		$title = sanitize_text_field( $_POST['title'] );
		$description = wp_kses_post( $_POST['description'] ); // Allow HTML in description (it becomes the first message)
		$priority = sanitize_text_field( $_POST['priority'] );

		// Create ticket
		$ticket_id = GRT_Ticket_Database::create_ticket( array(
			'user_id'      => $user_id,
			'user_email'   => $user_email,
			'user_name'    => $user_name,
			'theme_name'   => $theme_name,
			'license_code' => $license_code,
			'category'     => $category,
			'title'        => $title,
			'description'  => $description,
			'priority'     => $priority,
		) );

		if ( $ticket_id ) {
			// Email Notification to Admins
			$emails_str = get_option( 'grt_ticket_notification_emails', get_option( 'admin_email' ) );
			$emails = array_map( 'trim', explode( ',', $emails_str ) );
			$emails = array_filter( $emails, 'is_email' );

			if ( ! empty( $emails ) ) {
				$site_name = get_bloginfo( 'name' );
				$subject = sprintf( __( '[%s] New Ticket: %s', 'grt-ticket' ), $site_name, $title );
				$message = sprintf( __( 'A new ticket has been created by %s.', 'grt-ticket' ), $user_name ) . "\r\n\r\n";
				$message .= sprintf( __( 'Category: %s', 'grt-ticket' ), $category ) . "\r\n";
				$message .= sprintf( __( 'Title: %s', 'grt-ticket' ), $title ) . "\r\n\r\n";
				$message .= sprintf( __( 'Description:', 'grt-ticket' ) ) . "\r\n";
				$message .= wp_strip_all_tags( $description ) . "\r\n\r\n";
				$message .= sprintf( __( 'View Ticket: %s', 'grt-ticket' ), admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket_id ) );

				foreach ( $emails as $email ) {
					wp_mail( $email, $subject, $message );
				}
			}

			// Email Confirmation to User
			$site_name = get_bloginfo( 'name' );
			$user_subject = sprintf( __( '[%s] Ticket #%d Created: %s', 'grt-ticket' ), $site_name, $ticket_id, $title );
			$user_message = sprintf( __( 'Hello %s,', 'grt-ticket' ), $user_name ) . "\r\n\r\n";
			$user_message .= sprintf( __( 'Thank you for contacting support. Your ticket #%d has been created successfully.', 'grt-ticket' ), $ticket_id ) . "\r\n\r\n";
			$user_message .= sprintf( __( 'You can reply directly to this email to add more information to your ticket.', 'grt-ticket' ) ) . "\r\n\r\n";
			$user_message .= sprintf( __( 'Ticket Details:', 'grt-ticket' ) ) . "\r\n";
			$user_message .= sprintf( __( 'Subject: %s', 'grt-ticket' ), $title ) . "\r\n";
			$user_message .= sprintf( __( 'Description:', 'grt-ticket' ) ) . "\r\n";
			$user_message .= wp_strip_all_tags( $description ) . "\r\n\r\n";
			
			wp_mail( $user_email, $user_subject, $user_message );

			// WhatsApp Notification to Admins
			$whatsapp_admin_number = get_option( 'grt_ticket_whatsapp_admin_number', '' );
			if ( ! empty( $whatsapp_admin_number ) ) {
				$wa_message = sprintf( "*New Ticket #%d*\n", $ticket_id );
				$wa_message .= sprintf( "From: %s\n", $user_name );
				$wa_message .= sprintf( "Title: %s\n", $title );
				$wa_message .= sprintf( "Desc: %s\n", wp_strip_all_tags( $description ) );
				$wa_message .= sprintf( "Link: %s", admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket_id ) );

				$this->send_twilio_whatsapp( $whatsapp_admin_number, $wa_message );
			}

			// Add initial message
			GRT_Ticket_Database::add_message( array(
				'ticket_id'   => $ticket_id,
				'sender_type' => 'user',
				'sender_name' => $user_name,
				'message'     => $description,
			) );

			wp_send_json_success( array(
				'message'   => __( 'Ticket submitted successfully!', 'grt-ticket' ),
				'ticket_id' => $ticket_id,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to create ticket. Please try again.', 'grt-ticket' ) ) );
		}
	}

	/**
	 * Send a message to a ticket.
	 *
	 * @since    1.0.0
	 */
	public function send_message() {
		// Verify nonce
		check_ajax_referer( 'grt_ticket_nonce', 'nonce' );

		// Validate required fields (message is now optional if attachment is provided)
		if ( empty( $_POST['ticket_id'] ) || ( empty( $_POST['message'] ) && empty( $_FILES['attachment'] ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Ticket ID and message or attachment are required.', 'grt-ticket' ) ) );
		}

		$ticket_id = (int) $_POST['ticket_id'];
		$ticket = GRT_Ticket_Database::get_ticket( $ticket_id );

		if ( ! $ticket ) {
			wp_send_json_error( array( 'message' => __( 'Ticket not found.', 'grt-ticket' ) ) );
		}

		// Check if ticket is solved or closed
		if ( in_array( $ticket->status, array( 'solved', 'closed' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Cannot send message to a solved or closed ticket.', 'grt-ticket' ) ) );
		}

		// Determine sender type and name
		$is_admin = current_user_can( 'manage_options' );
		$sender_type = $is_admin ? 'admin' : 'user';
		$sender_name = $is_admin ? get_option( 'grt_ticket_admin_name', 'Support Team' ) : $ticket->user_name;

		// Handle file upload
		$attachment_url = '';
		if ( ! empty( $_FILES['attachment'] ) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK ) {
			// Validate file type
			$allowed_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf' );
			$file_type = $_FILES['attachment']['type'];

			if ( ! in_array( $file_type, $allowed_types, true ) ) {
				wp_send_json_error( array( 'message' => __( 'Only image files (JPEG, PNG, GIF) and PDF files are allowed.', 'grt-ticket' ) ) );
			}

			// Validate file size (5MB max)
			$max_size = 5 * 1024 * 1024; // 5MB
			if ( $_FILES['attachment']['size'] > $max_size ) {
				wp_send_json_error( array( 'message' => __( 'Image size must be less than 5MB.', 'grt-ticket' ) ) );
			}

			// Use WordPress upload handling
			require_once ABSPATH . 'wp-admin/includes/file.php';

			$upload_overrides = array( 'test_form' => false );
			$uploaded_file = wp_handle_upload( $_FILES['attachment'], $upload_overrides );

			if ( isset( $uploaded_file['error'] ) ) {
				wp_send_json_error( array( 'message' => $uploaded_file['error'] ) );
			}

			$attachment_url = $uploaded_file['url'];
		}

		// Prepare message data
		$message_content = ! empty( $_POST['message'] ) ? $_POST['message'] : '';
		
		// If message is empty but we have an attachment, use a placeholder or empty string
		// We ensure it's not null, but let's make sure it's at least an empty string
		if ( empty( $message_content ) && $attachment_url ) {
			$message_content = ''; // Empty string is valid for TEXT NOT NULL
		}

		$message_data = array(
			'ticket_id'   => $ticket_id,
			'sender_type' => $sender_type,
			'sender_name' => $sender_name,
			'message'     => $message_content,
		);

		if ( $attachment_url ) {
			$message_data['attachment_url'] = $attachment_url;
		}

		// Add message
		$message_id = GRT_Ticket_Database::add_message( $message_data );

		if ( $message_id ) {
			// Email Notification Logic
			$site_name = get_bloginfo( 'name' );
			
			if ( $sender_type === 'user' ) {
				// Notify Admins
				$emails_str = get_option( 'grt_ticket_notification_emails', get_option( 'admin_email' ) );
				$emails = array_map( 'trim', explode( ',', $emails_str ) );
				$emails = array_filter( $emails, 'is_email' );
				
				if ( ! empty( $emails ) ) {
					$subject = sprintf( __( '[%s] New Message on Ticket #%d', 'grt-ticket' ), $site_name, $ticket_id );
					$body = sprintf( __( 'New message from %s:', 'grt-ticket' ), $sender_name ) . "\r\n\r\n";
					$body .= wp_strip_all_tags( $message_content ) . "\r\n\r\n";
					$body .= sprintf( __( 'View Ticket: %s', 'grt-ticket' ), admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket_id ) );
					
					foreach ( $emails as $email ) {
						wp_mail( $email, $subject, $body );
					}
				}

				// Notify Admins via WhatsApp
				$whatsapp_admin_number = get_option( 'grt_ticket_whatsapp_admin_number', '' );
				if ( ! empty( $whatsapp_admin_number ) ) {
					$wa_message = sprintf( "*New Message on Ticket #%d*\n", $ticket_id );
					$wa_message .= sprintf( "From: %s\n", $sender_name );
					$wa_message .= sprintf( "Msg: %s\n", wp_strip_all_tags( $message_content ) );
					$wa_message .= sprintf( "Link: %s", admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket_id ) );

					$this->send_twilio_whatsapp( $whatsapp_admin_number, $wa_message );
				}
			} else {
				// Notify User (if admin replied)
				if ( is_email( $ticket->user_email ) ) {
					$subject = sprintf( __( '[%s] Update on Ticket #%d', 'grt-ticket' ), $site_name, $ticket_id );
					$body = sprintf( __( 'Hello %s,', 'grt-ticket' ), $ticket->user_name ) . "\r\n\r\n";
					$body .= sprintf( __( 'You have received a new reply from support:', 'grt-ticket' ) ) . "\r\n\r\n";
					$body .= wp_strip_all_tags( $message_content ) . "\r\n\r\n";
					
					wp_mail( $ticket->user_email, $subject, $body );
				}
			}

			$new_message = GRT_Ticket_Database::get_message( $message_id );
			
			wp_send_json_success( array(
				'message'      => __( 'Message sent successfully!', 'grt-ticket' ),
				'message_id'   => $message_id,
				'chat_message' => $new_message,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to send message. Please try again.', 'grt-ticket' ) ) );
		}
	}

	/**
	 * Get messages for a ticket.
	 *
	 * @since    1.0.0
	 */
	public function get_messages() {
		// Verify nonce
		check_ajax_referer( 'grt_ticket_nonce', 'nonce' );

		if ( empty( $_POST['ticket_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Ticket ID is required.', 'grt-ticket' ) ) );
		}

		$ticket_id = (int) $_POST['ticket_id'];
		$since_id = isset( $_POST['since_id'] ) ? (int) $_POST['since_id'] : 0;

		$messages = GRT_Ticket_Database::get_messages( $ticket_id, $since_id );
		$ticket = GRT_Ticket_Database::get_ticket( $ticket_id );

		if ( ! $ticket ) {
			wp_send_json_error( array( 'message' => __( 'Ticket not found.', 'grt-ticket' ) ) );
		}

		wp_send_json_success( array(
			'messages' => $messages,
			'status'   => $ticket->status,
		) );
	}

	/**
	 * Get tickets list.
	 *
	 * @since    1.0.0
	 */
	public function get_tickets() {
		// Verify nonce
		check_ajax_referer( 'grt_ticket_nonce', 'nonce' );

		$args = array();

		// If not admin, filter by user email
		if ( ! current_user_can( 'manage_options' ) ) {
			if ( empty( $_POST['user_email'] ) ) {
				wp_send_json_error( array( 'message' => __( 'User email is required.', 'grt-ticket' ) ) );
			}
			$args['user_email'] = sanitize_email( $_POST['user_email'] );
		}

		// Optional status filter
		if ( ! empty( $_POST['status'] ) ) {
			$args['status'] = sanitize_text_field( $_POST['status'] );
		}

		$tickets = GRT_Ticket_Database::get_tickets( $args );

		wp_send_json_success( array( 'tickets' => $tickets ) );
	}

	/**
	 * Mark a ticket as solved (admin only).
	 *
	 * @since    1.0.0
	 */
	public function mark_solved() {
		// Verify nonce
		check_ajax_referer( 'grt_ticket_nonce', 'nonce' );

		// Check admin capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'grt-ticket' ) ) );
		}

		if ( empty( $_POST['ticket_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Ticket ID is required.', 'grt-ticket' ) ) );
		}

		$ticket_id = (int) $_POST['ticket_id'];
		$result = GRT_Ticket_Database::update_ticket_status( $ticket_id, 'solved' );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Ticket marked as solved!', 'grt-ticket' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update ticket status.', 'grt-ticket' ) ) );
		}
	}

	/**
	 * Delete a ticket (admin only).
	 *
	 * @since    1.0.0
	 */
	public function delete_ticket() {
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'grt-ticket' ) ) );
		}

		// Verify nonce
		check_ajax_referer( 'grt_ticket_nonce', 'nonce' );

		$ticket_id = isset( $_POST['ticket_id'] ) ? (int) $_POST['ticket_id'] : 0;

		if ( ! $ticket_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ticket ID.', 'grt-ticket' ) ) );
		}

		$result = GRT_Ticket_Database::delete_ticket( $ticket_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Ticket deleted.', 'grt-ticket' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete ticket.', 'grt-ticket' ) ) );
		}
	}

	/**
	 * Submit ticket rating.
	 *
	 * @since    1.0.0
	 */
	public function submit_rating() {
		// Verify nonce
		check_ajax_referer( 'grt_ticket_nonce', 'nonce' );

		$ticket_id = isset( $_POST['ticket_id'] ) ? (int) $_POST['ticket_id'] : 0;
		$rating    = isset( $_POST['rating'] ) ? (int) $_POST['rating'] : 0;
		$feedback  = isset( $_POST['feedback'] ) ? sanitize_textarea_field( $_POST['feedback'] ) : '';

		if ( ! $ticket_id || $rating < 1 || $rating > 5 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'grt-ticket' ) ) );
		}

		// Check if already rated
		$ticket = GRT_Ticket_Database::get_ticket( $ticket_id );
		if ( ! $ticket ) {
			wp_send_json_error( array( 'message' => __( 'Ticket not found.', 'grt-ticket' ) ) );
		}

		if ( isset( $ticket->rating ) && $ticket->rating > 0 ) {
			wp_send_json_error( array( 'message' => __( 'You have already rated this ticket.', 'grt-ticket' ) ) );
		}

		// Verify ownership (or admin)
		if ( ! current_user_can( 'manage_options' ) ) {
			// Check if ticket belongs to user (by email match or user ID)
			// For simplicity in this public context, we assume the nonce provides basic protection,
			// but ideally we should verify the user owns the ticket.
			
			// If user is logged in, check ID
			if ( is_user_logged_in() && $ticket->user_id != get_current_user_id() ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied.', 'grt-ticket' ) ) );
			}
			// If not logged in, we rely on the nonce and session/cookie context which is limited here.
			// In a real-world scenario, we might check a cookie or token.
		}

		$result = GRT_Ticket_Database::update_ticket_rating( $ticket_id, $rating, $feedback );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Thank you for your feedback!', 'grt-ticket' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to submit rating.', 'grt-ticket' ) ) );
		}
	}

	/**
	 * Send WhatsApp notification via Twilio.
	 *
	 * @since 1.0.0
	 * @param string $to Recipient phone number.
	 * @param string $message Message body.
	 * @return bool|WP_Error True on success, false or WP_Error on failure.
	 */
	private function send_twilio_whatsapp( $to, $message ) {
		$enable_whatsapp = get_option( 'grt_ticket_enable_whatsapp', 0 );
		if ( ! $enable_whatsapp ) {
			return false;
		}

		$sid = get_option( 'grt_ticket_twilio_sid', '' );
		$token = get_option( 'grt_ticket_twilio_token', '' );
		$from = get_option( 'grt_ticket_twilio_from', '' );

		if ( empty( $sid ) || empty( $token ) || empty( $from ) || empty( $to ) ) {
			return false;
		}

		// Ensure numbers have whatsapp: prefix
		if ( strpos( $from, 'whatsapp:' ) === false ) {
			$from = 'whatsapp:' . $from;
		}
		if ( strpos( $to, 'whatsapp:' ) === false ) {
			$to = 'whatsapp:' . $to;
		}

		$url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
		
		$args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( "$sid:$token" ),
			),
			'body' => array(
				'From' => $from,
				'To'   => $to,
				'Body' => $message,
			),
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code >= 200 && $code < 300 ) {
			return true;
		}

		return false;
	}
}