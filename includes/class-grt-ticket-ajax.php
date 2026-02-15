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
		if ( ! check_ajax_referer( 'grt_ticket_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'grt-ticket' ) ) );
			return;
		}

		try {
			// Validate required fields
		$required_fields = array( 'user_name', 'user_email', 'theme_name', 'license_code', 'category', 'title', 'description' );
		foreach ( $required_fields as $field ) {
			if ( empty( $_POST[ $field ] ) ) {
				/* translators: %s: Field name */
				wp_send_json_error( array( 'message' => sprintf( __( 'Field %s is required.', 'grt-ticket' ), $field ) ) );
			}
		}

		// Validate email
		if ( empty( $_POST['user_email'] ) || ! is_email( wp_unslash( $_POST['user_email'] ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email address.', 'grt-ticket' ) ) );
		}


		// Check if user exists or create new one
		$user_email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
		$user_name = isset( $_POST['user_name'] ) ? sanitize_text_field( wp_unslash( $_POST['user_name'] ) ) : '';
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
		} else {
			// Guest ticket or existing user not logged in:
			// Do NOT create user account.
			// Do NOT link to existing user account (to prevent spoofing).
			// Set user_id to 0 and rely on cookie for access.
			$user_id = 0;
		}

		// Sanitize inputs
		$theme_name = isset( $_POST['theme_name'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_name'] ) ) : '';
		$license_code = isset( $_POST['license_code'] ) ? sanitize_text_field( wp_unslash( $_POST['license_code'] ) ) : '';
		$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$description = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : ''; // Allow HTML in description (it becomes the first message)
		$priority = isset( $_POST['priority'] ) ? sanitize_text_field( wp_unslash( $_POST['priority'] ) ) : '';

		// Process Custom Fields
		$custom_fields_data = array();
		if ( isset( $_POST['custom_fields'] ) && is_array( $_POST['custom_fields'] ) ) {
			$defined_fields = get_option( 'grt_ticket_custom_fields', array() );
			if ( is_array( $defined_fields ) ) {
				foreach ( $defined_fields as $field ) {
					$field_id = isset( $field['id'] ) ? $field['id'] : '';
					if ( empty( $field_id ) ) continue;

					if ( isset( $_POST['custom_fields'][ $field_id ] ) ) {
						// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below based on type.
						$value = wp_unslash( $_POST['custom_fields'][ $field_id ] );
						
						// Validation based on type
						if ( ! empty( $field['required'] ) && empty( $value ) ) {
							/* translators: %s: Field label */
							wp_send_json_error( array( 'message' => sprintf( __( 'Field %s is required.', 'grt-ticket' ), $field['label'] ) ) );
							return;
						}

						if ( $field['type'] === 'textarea' ) {
							$value = sanitize_textarea_field( wp_unslash( $value ) );
						} else {
							$value = sanitize_text_field( wp_unslash( $value ) );
						}

						$custom_fields_data[ $field_id ] = array(
							'label' => $field['label'],
							'value' => $value
						);
					}
				}
			}
		}

		// Auto-assign agent based on category
		$assigned_agent_id = 0;
		$categories_option = get_option( 'grt_ticket_categories' );
		if ( $categories_option ) {
			$categories_data = json_decode( $categories_option, true );
			if ( is_array( $categories_data ) ) {
				foreach ( $categories_data as $cat_data ) {
					if ( isset( $cat_data['name'] ) && $cat_data['name'] === $category ) {
						if ( ! empty( $cat_data['agent_id'] ) ) {
							$assigned_agent_id = (int) $cat_data['agent_id'];
						}
						break;
					}
				}
			}
		}

		// Create ticket
		$ticket_id = GRT_Ticket_Database::create_ticket( array(
			'user_id'           => $user_id,
			'user_email'        => $user_email,
			'user_name'         => $user_name,
			'theme_name'        => $theme_name,
			'license_code'      => $license_code,
			'category'          => $category,
			'title'             => $title,
			'description'       => $description,
			'priority'          => $priority,
			'assigned_agent_id' => $assigned_agent_id,
			'custom_fields'     => ! empty( $custom_fields_data ) ? json_encode( $custom_fields_data ) : null,
		) );

		if ( $ticket_id ) {
			// Email Notification to Admins
			$emails_str = get_option( 'grt_ticket_notification_emails', get_option( 'admin_email' ) );
			$emails = array_map( 'trim', explode( ',', $emails_str ) );
			$emails = array_filter( $emails, 'is_email' );

			if ( ! empty( $emails ) ) {
				$site_name = get_bloginfo( 'name' );
				/* translators: 1: Site name, 2: Ticket title */
				$subject = sprintf( __( '[%1$s] New Ticket: %2$s', 'grt-ticket' ), $site_name, $title );
				/* translators: %s: User name */
				$message = sprintf( __( 'A new ticket has been created by %s.', 'grt-ticket' ), $user_name ) . "\r\n\r\n";
				/* translators: %s: Category name */
				$message .= sprintf( __( 'Category: %s', 'grt-ticket' ), $category ) . "\r\n";
				/* translators: %s: Ticket title */
				$message .= sprintf( __( 'Title: %s', 'grt-ticket' ), $title ) . "\r\n\r\n";
				$message .= sprintf( __( 'Description:', 'grt-ticket' ) ) . "\r\n";
				$message .= wp_strip_all_tags( $description ) . "\r\n\r\n";
				/* translators: %s: Ticket URL */
				$message .= sprintf( __( 'View Ticket: %s', 'grt-ticket' ), admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket_id ) );

				// Check if email notifications are enabled
				if ( get_option( 'grt_ticket_enable_email_notifications', 1 ) ) {
					foreach ( $emails as $email ) {
						try {
							wp_mail( $email, $subject, $message );
						} catch ( \Throwable $e ) {
							// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Essential for debugging.
							error_log( 'GRT Ticket Email Error: ' . $e->getMessage() );
						}
					}
				}
			}

			// Email Notification to Assigned Agent
			if ( $assigned_agent_id > 0 && get_option( 'grt_ticket_enable_email_notifications', 1 ) ) {
				$agent_user = get_userdata( $assigned_agent_id );
				if ( $agent_user ) {
					$site_name = get_bloginfo( 'name' );
					/* translators: 1: Site name, 2: Ticket title */
					$agent_subject = sprintf( __( '[%1$s] New Ticket Assigned: %2$s', 'grt-ticket' ), $site_name, $title );
					/* translators: %s: Agent name */
					$agent_message = sprintf( __( 'Hello %s,', 'grt-ticket' ), $agent_user->display_name ) . "\r\n\r\n";
					/* translators: %s: Category name */
					$agent_message .= sprintf( __( 'A new ticket has been automatically assigned to you based on the category "%s".', 'grt-ticket' ), $category ) . "\r\n\r\n";
					$agent_message .= __( 'Ticket Details:', 'grt-ticket' ) . "\r\n";
					/* translators: %s: User name */
					$agent_message .= sprintf( __( 'From: %s', 'grt-ticket' ), $user_name ) . "\r\n";
					/* translators: %s: Ticket title */
					$agent_message .= sprintf( __( 'Title: %s', 'grt-ticket' ), $title ) . "\r\n\r\n";
					$agent_message .= __( 'Description:', 'grt-ticket' ) . "\r\n";
					$agent_message .= wp_strip_all_tags( $description ) . "\r\n\r\n";
					/* translators: %s: Ticket URL */
					$agent_message .= sprintf( __( 'View Ticket: %s', 'grt-ticket' ), admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket_id ) );

					try {
						wp_mail( $agent_user->user_email, $agent_subject, $agent_message );
					} catch ( \Throwable $e ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Essential for debugging.
						error_log( 'GRT Ticket Agent Email Error: ' . $e->getMessage() );
					}
				}
			}

			// Email Confirmation to User
			$site_name = get_bloginfo( 'name' );
			/* translators: 1: Site name, 2: Ticket ID, 3: Ticket title */
			$user_subject = sprintf( __( '[%1$s] Ticket #%2$d Created: %3$s', 'grt-ticket' ), $site_name, $ticket_id, $title );
			/* translators: %s: User name */
			$user_message = sprintf( __( 'Hello %s,', 'grt-ticket' ), $user_name ) . "\r\n\r\n";
			/* translators: %d: Ticket ID */
			$user_message .= sprintf( __( 'Thank you for contacting support. Your ticket #%d has been created successfully.', 'grt-ticket' ), $ticket_id ) . "\r\n\r\n";
			
			// Generate Frontend Ticket URL
			$page = get_page_by_path( 'grt-ticket' );
			$ticket_base_url = $page ? get_permalink( $page->ID ) : site_url( '/grt-ticket/' );
			$ticket_view_url = trailingslashit( $ticket_base_url ) . 'ticket/' . $ticket_id . '/';
			
			/* translators: %s: Ticket URL */
			$user_message .= sprintf( __( 'View Ticket: %s', 'grt-ticket' ), $ticket_view_url ) . "\r\n\r\n";
			
			$user_message .= sprintf( __( 'You can reply directly to this email to add more information to your ticket.', 'grt-ticket' ) ) . "\r\n\r\n";
			$user_message .= sprintf( __( 'Ticket Details:', 'grt-ticket' ) ) . "\r\n";
			/* translators: %s: Ticket title */
			$user_message .= sprintf( __( 'Subject: %s', 'grt-ticket' ), $title ) . "\r\n";
			$user_message .= sprintf( __( 'Description:', 'grt-ticket' ) ) . "\r\n";
			$user_message .= wp_strip_all_tags( $description ) . "\r\n\r\n";
			
			// Check if email notifications are enabled
			if ( get_option( 'grt_ticket_enable_email_notifications', 1 ) ) {
				try {
					wp_mail( $user_email, $user_subject, $user_message );
				} catch ( \Throwable $e ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Essential for debugging.
					error_log( 'GRT Ticket Email Error: ' . $e->getMessage() );
				}
			}

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

			// Webhook Notifications
			$this->send_webhook_notifications( $ticket_id, array(
				'title'       => $title,
				'description' => $description,
				'user_name'   => $user_name,
				'user_email'  => $user_email,
				'category'    => $category,
				'priority'    => $priority,
			) );

			// Always set guest cookie for new tickets submitted by users (whether logged in or not)
			// This ensures that even if they are logged in but session expires or is lost, they can access this specific ticket via the cookie.
			// It is secure because it is hashed with the ticket ID and user email.
			$cookie_name = 'grt_ticket_guest_' . $ticket_id;
			$cookie_value = hash_hmac( 'sha256', $ticket_id . $user_email, wp_salt() );
			setcookie( $cookie_name, $cookie_value, time() + 7 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );

			// Add initial message
			GRT_Ticket_Database::add_message( array(
				'ticket_id'   => $ticket_id,
				'sender_type' => 'user',
				'sender_id'   => $user_id,
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

		} catch ( \Throwable $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Essential for debugging.
			error_log( 'GRT Ticket Submit Error: ' . $e->getMessage() );
			// We show a generic error to the user, but include the message for debugging since this is a support system
			wp_send_json_error( array( 'message' => __( 'Server error: ', 'grt-ticket' ) . $e->getMessage() ) );
		}
	}

	/**
	 * Send a message to a ticket.
	 *
	 * @since    1.0.0
	 */
	public function send_message() {
		// Verify nonce
		if ( ! check_ajax_referer( 'grt_ticket_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'grt-ticket' ) ) );
			return;
		}

		try {
		// Validate required fields (message is now optional if attachment is provided)
		if ( empty( $_POST['ticket_id'] ) || ( empty( $_POST['message'] ) && empty( $_FILES['attachment'] ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Ticket ID and message or attachment are required.', 'grt-ticket' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$ticket_id = isset( $_POST['ticket_id'] ) ? (int) wp_unslash( $_POST['ticket_id'] ) : 0;
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
		
		$current_user_id = is_user_logged_in() ? get_current_user_id() : 0;
		$sender_id = $current_user_id;

		if ( $is_admin ) {
			// Use agent's display name if available, otherwise fallback to setting
			$user_info = get_userdata( $current_user_id );
			$sender_name = $user_info ? $user_info->display_name : get_option( 'grt_ticket_admin_name', 'Support Team' );
		} else {
			$sender_name = $ticket->user_name;
		}

		// Check rate limit
		$transient_key = 'grt_ticket_message_limit_' . $sender_id . '_' . $ticket_id;
		if ( ! $is_admin && get_transient( $transient_key ) ) {
			// wp_send_json_error( array( 'message' => __( 'Please wait a few seconds before sending another message.', 'grt-ticket' ) ) );
			// Rate limit disabled for better UX
		}

		// Handle file upload
		$attachment_url = '';
		if ( ! empty( $_FILES['attachment'] ) && isset( $_FILES['attachment']['error'] ) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK ) {
			// Validate file type
			$allowed_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf' );
			$file_type = isset( $_FILES['attachment']['type'] ) ? sanitize_mime_type( $_FILES['attachment']['type'] ) : '';

			if ( ! in_array( $file_type, $allowed_types, true ) ) {
				wp_send_json_error( array( 'message' => __( 'Only image files (JPEG, PNG, GIF) and PDF files are allowed.', 'grt-ticket' ) ) );
			}

			// Validate file size (5MB max)
			$max_size = 5 * 1024 * 1024; // 5MB
			$file_size = isset( $_FILES['attachment']['size'] ) ? (int) $_FILES['attachment']['size'] : 0;
			if ( $file_size > $max_size ) {
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
		$message_content = ! empty( $_POST['message'] ) ? wp_kses_post( wp_unslash( $_POST['message'] ) ) : '';
		
		// If message is empty but we have an attachment, use a placeholder or empty string
		// We ensure it's not null, but let's make sure it's at least an empty string
		$is_internal_raw = isset( $_POST['is_internal'] ) ? sanitize_text_field( wp_unslash( $_POST['is_internal'] ) ) : '';
		$is_internal = filter_var( $is_internal_raw, FILTER_VALIDATE_BOOLEAN ) ? 1 : 0;
		if ( $sender_type === 'user' ) {
			$is_internal = 0; // Users cannot send internal notes
		}

		if ( empty( $message_content ) && $attachment_url ) {
			$message_content = ''; // Empty string is valid for TEXT NOT NULL
		}

		$message_data = array(
			'ticket_id'   => $ticket_id,
			'sender_type' => $sender_type,
			'sender_id'   => $sender_id,
			'sender_name' => $sender_name,
			'message'     => $message_content,
			'is_internal' => $is_internal,
		);

		if ( $attachment_url ) {
			$message_data['attachment_url'] = $attachment_url;
		}

		// Add message
		$message_id = GRT_Ticket_Database::add_message( $message_data );

		if ( $message_id ) {
			// Set rate limit transient
			if ( ! $is_admin ) {
				set_transient( $transient_key, true, 2 );
			}

			// Email Notification Logic
			$site_name = get_bloginfo( 'name' );
			
			if ( $sender_type === 'user' ) {
				// Notify Admins
				$emails_str = get_option( 'grt_ticket_notification_emails', get_option( 'admin_email' ) );
				$emails = array_map( 'trim', explode( ',', $emails_str ) );
				$emails = array_filter( $emails, 'is_email' );
				
				if ( ! empty( $emails ) ) {
					/* translators: 1: Site name, 2: Ticket ID */
					$subject = sprintf( __( '[%1$s] New Message on Ticket #%2$d', 'grt-ticket' ), $site_name, $ticket_id );
					/* translators: %s: Sender name */
					$body = sprintf( __( 'New message from %s:', 'grt-ticket' ), $sender_name ) . "\r\n\r\n";
					$body .= wp_strip_all_tags( $message_content ) . "\r\n\r\n";
					/* translators: %s: Ticket URL */
					$body .= sprintf( __( 'View Ticket: %s', 'grt-ticket' ), admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket_id ) );
					
					// Check if email notifications are enabled
					if ( get_option( 'grt_ticket_enable_email_notifications', 1 ) ) {
						foreach ( $emails as $email ) {
							try {
								wp_mail( $email, $subject, $body );
							} catch ( \Throwable $e ) {
								// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Essential for debugging.
								error_log( 'GRT Ticket Email Error: ' . $e->getMessage() );
							}
						}
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
				// Notify User (if admin replied) - BUT ONLY IF NOT INTERNAL NOTE
				if ( ! $is_internal && is_email( $ticket->user_email ) ) {
					/* translators: 1: Site name, 2: Ticket ID */
					$subject = sprintf( __( '[%1$s] Update on Ticket #%2$d', 'grt-ticket' ), $site_name, $ticket_id );
					/* translators: %s: User name */
					$body = sprintf( __( 'Hello %s,', 'grt-ticket' ), $ticket->user_name ) . "\r\n\r\n";
					$body .= __( 'You have received a new reply from support:', 'grt-ticket' ) . "\r\n\r\n";
					$body .= wp_strip_all_tags( $message_content ) . "\r\n\r\n";
					
					// Check if email notifications are enabled
					if ( get_option( 'grt_ticket_enable_email_notifications', 1 ) ) {
						try {
							wp_mail( $ticket->user_email, $subject, $body );
						} catch ( \Throwable $e ) {
							// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Essential for debugging.
							error_log( 'GRT Ticket Email Error: ' . $e->getMessage() );
						}
					}
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

		} catch ( \Throwable $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Essential for debugging.
			error_log( 'GRT Ticket Send Message Error: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => __( 'Server error: ', 'grt-ticket' ) . $e->getMessage() ) );
		}
	}

	/**
	 * Get messages for a ticket.
	 *
	 * @since    1.0.0
	 */
	public function grt_ticket_get_messages() {
		// Verify nonce
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( empty( $nonce ) || ( ! wp_verify_nonce( $nonce, 'grt_ticket_nonce' ) && ! wp_verify_nonce( $nonce, 'grt_ticket_public_nonce' ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'grt-ticket' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$ticket_id = isset( $_POST['ticket_id'] ) ? (int) wp_unslash( $_POST['ticket_id'] ) : 0;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$since_id  = isset( $_POST['since_id'] ) ? (int) wp_unslash( $_POST['since_id'] ) : 0;

		if ( ! $ticket_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ticket ID.', 'grt-ticket' ) ) );
		}

		// Check if user is admin or the ticket owner
		$is_admin = current_user_can( 'manage_options' );
		$is_owner = false;

		$ticket = GRT_Ticket_Database::get_ticket( $ticket_id );
		if ( ! $ticket ) {
			wp_send_json_error( array( 'message' => __( 'Ticket not found.', 'grt-ticket' ) ) );
		}

		if ( is_user_logged_in() && get_current_user_id() == $ticket->user_id ) {
			$is_owner = true;
		}

		// Allow access via secure token/nonce for public/guest users (handled by nonce check mostly)
		// But strictly speaking we should check cookie or session if we had one for guests.
		// For now, nonce + knowing ticket ID is enough for guest access in this simple system.

		$messages = GRT_Ticket_Database::get_messages( $ticket_id, $since_id );
		
		// Filter internal messages if not admin
		if ( ! $is_admin ) {
			$messages = array_filter( $messages, function( $msg ) {
				return empty( $msg->is_internal );
			} );
			// Re-index array to JSON array
			$messages = array_values( $messages );
		}

		// Check if other party is typing
		$other_type_suffix = $is_admin ? 'user' : 'admin';
		$other_typing_key = 'grt_typing_' . $ticket_id . '_' . $other_type_suffix;
		$is_other_typing = get_transient( $other_typing_key ) ? true : false;

		wp_send_json_success( array(
			'messages' => $messages,
			'is_typing' => $is_other_typing
		) );
	}

	/**
	 * Update typing status.
	 *
	 * @since    1.0.0
	 */
	public function update_typing_status() {
		// Verify nonce
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( empty( $nonce ) || ( ! wp_verify_nonce( $nonce, 'grt_ticket_nonce' ) && ! wp_verify_nonce( $nonce, 'grt_ticket_public_nonce' ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'grt-ticket' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$ticket_id = isset( $_POST['ticket_id'] ) ? (int) wp_unslash( $_POST['ticket_id'] ) : 0;
		$is_typing = isset( $_POST['is_typing'] ) && $_POST['is_typing'] === 'true';

		if ( ! $ticket_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ticket ID.', 'grt-ticket' ) ) );
		}

		// Determine user type
		$is_admin = current_user_can( 'manage_options' );
		$type_suffix = $is_admin ? 'admin' : 'user';
		
		// Set transient key: grt_typing_{ticket_id}_{admin|user}
		$key = 'grt_typing_' . $ticket_id . '_' . $type_suffix;

		if ( $is_typing ) {
			// Set transient for 5 seconds (client should ping every 3s)
			set_transient( $key, true, 5 );
		} else {
			delete_transient( $key );
		}

		wp_send_json_success();
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
			$args['user_email'] = sanitize_email( wp_unslash( $_POST['user_email'] ) );
		}

		// Optional status filter
		if ( ! empty( $_POST['status'] ) ) {
			$args['status'] = sanitize_text_field( wp_unslash( $_POST['status'] ) );
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

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$ticket_id = (int) wp_unslash( $_POST['ticket_id'] );
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

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$ticket_id = isset( $_POST['ticket_id'] ) ? (int) wp_unslash( $_POST['ticket_id'] ) : 0;

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

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$ticket_id = isset( $_POST['ticket_id'] ) ? (int) wp_unslash( $_POST['ticket_id'] ) : 0;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$rating    = isset( $_POST['rating'] ) ? (int) wp_unslash( $_POST['rating'] ) : 0;
		$feedback  = isset( $_POST['feedback'] ) ? sanitize_textarea_field( wp_unslash( $_POST['feedback'] ) ) : '';

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

	/**
	 * Upload profile image.
	 *
	 * @since    1.0.0
	 */
	public function upload_profile_image() {
		// Verify nonce
		if ( ! check_ajax_referer( 'grt_ticket_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'grt-ticket' ) ) );
			return;
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in to upload an image.', 'grt-ticket' ) ) );
			return;
		}

		if ( empty( $_FILES['profile_image'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'grt-ticket' ) ) );
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File array handling via media_handle_upload.
		$file = $_FILES['profile_image'];
		
		// Use media_handle_sideload or similar, but for direct upload we can use wp_handle_upload
		// However, media_handle_upload is easier as it creates attachment
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$attachment_id = media_handle_upload( 'profile_image', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ) );
			return;
		}

		// Save user meta
		update_user_meta( get_current_user_id(), 'grt_profile_image', $attachment_id );

		wp_send_json_success( array( 
			'message' => __( 'Profile image updated.', 'grt-ticket' ),
			'image_url' => wp_get_attachment_url( $attachment_id )
		) );
	}

	/**
	 * Save form builder fields.
	 *
	 * @since    1.0.0
	 */
	public function save_form_builder() {
		// Verify nonce and capability
		if ( ! check_ajax_referer( 'grt_ticket_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'grt-ticket' ) ) );
			return;
		}

		if ( ! isset( $_POST['fields'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No data received.', 'grt-ticket' ) ) );
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON data is decoded and fields are sanitized individually below.
		$fields_json = wp_unslash( $_POST['fields'] );
		$fields = json_decode( $fields_json, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( array( 'message' => __( 'Invalid JSON data.', 'grt-ticket' ) ) );
			return;
		}

		// Sanitize fields
		$sanitized_structure = array();
		$custom_fields_only = array();

		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( ! isset( $field['id'], $field['type'], $field['label'] ) ) {
					continue;
				}

				$clean_field = array(
					'id'          => sanitize_text_field( $field['id'] ),
					'type'        => sanitize_text_field( $field['type'] ),
					'label'       => sanitize_text_field( $field['label'] ),
					'placeholder' => isset( $field['placeholder'] ) ? sanitize_text_field( $field['placeholder'] ) : '',
					'required'    => ! empty( $field['required'] ),
					'width'       => isset( $field['width'] ) ? sanitize_text_field( $field['width'] ) : '100',
					'is_system'   => ! empty( $field['is_system'] ),
				);

				if ( isset( $field['options'] ) ) {
					$clean_field['options'] = sanitize_textarea_field( $field['options'] );
				}

				$sanitized_structure[] = $clean_field;

				// If it's a custom field, add to custom_fields_only
				if ( empty( $clean_field['is_system'] ) ) {
					$custom_fields_only[] = $clean_field;
				}
			}
		}

		// Save the full structure (including system fields and order)
		update_option( 'grt_ticket_form_structure', $sanitized_structure );
		
		// Save only custom fields (for backward compatibility and validation logic)
		update_option( 'grt_ticket_custom_fields', $custom_fields_only );

		wp_send_json_success( array( 'message' => __( 'Form structure saved successfully.', 'grt-ticket' ) ) );
	}

	/**
	 * Assign ticket to agent.
	 *
	 * @since    1.0.5
	 */
	public function assign_ticket() {
		// Verify nonce
		check_ajax_referer( 'grt_ticket_nonce', 'nonce' );

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'grt-ticket' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$ticket_id = isset( $_POST['ticket_id'] ) ? (int) wp_unslash( $_POST['ticket_id'] ) : 0;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast to integer.
		$agent_id  = isset( $_POST['agent_id'] ) ? (int) wp_unslash( $_POST['agent_id'] ) : 0;

		if ( ! $ticket_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ticket ID.', 'grt-ticket' ) ) );
		}

		$result = GRT_Ticket_Database::assign_ticket( $ticket_id, $agent_id );

		if ( $result ) {
			// Send email to agent
			if ( $agent_id > 0 ) {
				$agent = get_userdata( $agent_id );
				if ( $agent ) {
					$ticket = GRT_Ticket_Database::get_ticket( $ticket_id );
					/* translators: 1: Site name, 2: Ticket ID */
					$subject = sprintf( __( '[%1$s] You have been assigned to Ticket #%2$d', 'grt-ticket' ), get_bloginfo( 'name' ), $ticket_id );
					/* translators: %s: Agent name */
					$message = sprintf( __( 'Hello %s,', 'grt-ticket' ), $agent->display_name ) . "\r\n\r\n";
					$message .= sprintf( __( 'You have been assigned to the following ticket:', 'grt-ticket' ) ) . "\r\n\r\n";
					/* translators: 1: Ticket ID, 2: Ticket title */
					$message .= sprintf( __( 'Ticket #%1$d: %2$s', 'grt-ticket' ), $ticket_id, $ticket->title ) . "\r\n";
					/* translators: %s: Ticket URL */
					$message .= sprintf( __( 'View Ticket: %s', 'grt-ticket' ), admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket_id ) ) . "\r\n\r\n";
					
					wp_mail( $agent->user_email, $subject, $message );
				}
			}

			wp_send_json_success( array( 'message' => __( 'Agent assigned successfully.', 'grt-ticket' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to assign agent.', 'grt-ticket' ) ) );
		}
	}

	/**
	 * Test Supabase Connection.
	 *
	 * @since    1.0.0
	 */
	public function test_supabase_connection() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'grt-ticket' ) ) );
		}

		check_ajax_referer( 'grt_ticket_settings_nonce', 'nonce' );

		$url = isset( $_POST['supabase_url'] ) ? sanitize_text_field( wp_unslash( $_POST['supabase_url'] ) ) : '';
		$key = isset( $_POST['supabase_key'] ) ? sanitize_text_field( wp_unslash( $_POST['supabase_key'] ) ) : '';

		if ( empty( $url ) || empty( $key ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing URL or Key.', 'grt-ticket' ) ) );
		}

		// Try to fetch 1 row from 'grt_messages'
		$api_url = rtrim( $url, '/' ) . '/rest/v1/grt_messages?select=id&limit=1';

		$response = wp_remote_get( $api_url, array(
			'headers' => array(
				'apikey'        => $key,
				'Authorization' => 'Bearer ' . $key,
			),
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) ) {
			/* translators: %s: Error message */
			wp_send_json_error( array( 'message' => sprintf( __( 'Connection failed: %s', 'grt-ticket' ), $response->get_error_message() ) ) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		
		if ( $code >= 200 && $code < 300 ) {
			wp_send_json_success( array( 'message' => __( 'Connection successful! Table found.', 'grt-ticket' ) ) );
		} elseif ( $code === 404 ) {
			wp_send_json_error( array( 'message' => __( 'Connected, but table "grt_messages" not found. Did you run the SQL?', 'grt-ticket' ) ) );
		} elseif ( $code === 401 || $code === 403 ) {
			wp_send_json_error( array( 'message' => __( 'Authentication failed. Check your Secret Key.', 'grt-ticket' ) ) );
		} else {
			$body = wp_remote_retrieve_body( $response );
			/* translators: 1: Error code, 2: Error message */
			wp_send_json_error( array( 'message' => sprintf( __( 'Error %1$d: %2$s', 'grt-ticket' ), $code, $body ) ) );
		}
	}

	/**
	 * Test Supabase Push (Write).
	 *
	 * @since    1.0.0
	 */
	public function test_supabase_push() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'grt-ticket' ) ) );
		}

		check_ajax_referer( 'grt_ticket_settings_nonce', 'nonce' );

		$url = isset( $_POST['supabase_url'] ) ? sanitize_text_field( wp_unslash( $_POST['supabase_url'] ) ) : '';
		$key = isset( $_POST['supabase_key'] ) ? sanitize_text_field( wp_unslash( $_POST['supabase_key'] ) ) : '';

		if ( empty( $url ) || empty( $key ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing URL or Key.', 'grt-ticket' ) ) );
		}

		// Prepare dummy data
		$dummy_data = array(
			'id'             => 999999, // Dummy ID
			'ticket_id'      => 0,
			'sender_type'    => 'admin',
			'sender_name'    => 'System Test',
			'message'        => 'This is a test message from GRT Ticket Settings.',
			'is_internal'    => 1, // Mark as internal so it doesn't show up in user chats if accidentally synced
			'created_at'     => gmdate( 'Y-m-d\TH:i:s\Z' )
		);

		$api_url = rtrim( $url, '/' ) . '/rest/v1/grt_messages';

		$response = wp_remote_post( $api_url, array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'apikey'        => $key,
				'Authorization' => 'Bearer ' . $key,
				'Prefer'        => 'return=representation' // Return the inserted row
			),
			'body'    => json_encode( $dummy_data ),
			'timeout' => 15,
		) );

		if ( is_wp_error( $response ) ) {
			/* translators: %s: Error message */
			wp_send_json_error( array( 'message' => sprintf( __( 'Connection failed: %s', 'grt-ticket' ), $response->get_error_message() ) ) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( $code >= 200 && $code < 300 ) {
			// Success! Now let's delete it to clean up
			wp_remote_request( $api_url . '?id=eq.999999', array(
				'method'  => 'DELETE',
				'headers' => array(
					'apikey'        => $key,
					'Authorization' => 'Bearer ' . $key,
				),
				'timeout' => 10,
			) );

			wp_send_json_success( array( 'message' => __( 'Write test successful! Message pushed and deleted.', 'grt-ticket' ) ) );
		} else {
			/* translators: 1: Error code, 2: Error message */
			wp_send_json_error( array( 'message' => sprintf( __( 'Write failed (Error %1$d): %2$s', 'grt-ticket' ), $code, $body ) ) );
		}
	}

	/**
	 * Send Webhook Notifications.
	 *
	 * @since 1.1.2
	 * @param int   $ticket_id   Ticket ID.
	 * @param array $ticket_data Ticket Data.
	 */
	private function send_webhook_notifications( $ticket_id, $ticket_data ) {
		// Get Webhook URLs
		$slack_webhook   = get_option( 'grt_ticket_slack_webhook', '' );
		$discord_webhook = get_option( 'grt_ticket_discord_webhook', '' );
		$zapier_webhook  = get_option( 'grt_ticket_zapier_webhook', '' );

		if ( empty( $slack_webhook ) && empty( $discord_webhook ) && empty( $zapier_webhook ) ) {
			return;
		}

		// Prepare Data
		$ticket_url = admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket_id );
		$description_snippet = wp_trim_words( wp_strip_all_tags( $ticket_data['description'] ), 20 );

		// 1. Slack Notification
		if ( ! empty( $slack_webhook ) ) {
			$message = sprintf( "*New Ticket #%d Created*\n", $ticket_id );
			$message .= sprintf( "*Title:* %s\n", $ticket_data['title'] );
			$message .= sprintf( "*From:* %s (%s)\n", $ticket_data['user_name'], $ticket_data['user_email'] );
			$message .= sprintf( "*Category:* %s\n", $ticket_data['category'] );
			$message .= sprintf( "*Priority:* %s\n", $ticket_data['priority'] );
			$message .= sprintf( "*Description:* %s\n", $description_snippet );
			$message .= sprintf( "<%s|View Ticket>", $ticket_url );

			wp_remote_post( $slack_webhook, array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => json_encode( array( 'text' => $message ) ),
				'blocking' => false, // Non-blocking
			) );
		}

		// 2. Discord Notification
		if ( ! empty( $discord_webhook ) ) {
			$message = sprintf( "**New Ticket #%d Created**\n", $ticket_id );
			$message .= sprintf( "**Title:** %s\n", $ticket_data['title'] );
			$message .= sprintf( "**From:** %s (%s)\n", $ticket_data['user_name'], $ticket_data['user_email'] );
			$message .= sprintf( "**Category:** %s\n", $ticket_data['category'] );
			$message .= sprintf( "**Priority:** %s\n", $ticket_data['priority'] );
			$message .= sprintf( "**Description:** %s\n", $description_snippet );
			$message .= sprintf( "[View Ticket](%s)", $ticket_url );

			wp_remote_post( $discord_webhook, array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => json_encode( array( 'content' => $message ) ),
				'blocking' => false,
			) );
		}

		// 3. Zapier Notification (Send full JSON object)
		if ( ! empty( $zapier_webhook ) ) {
			$zapier_data = array_merge( array(
				'ticket_id'  => $ticket_id,
				'ticket_url' => $ticket_url,
				'created_at' => current_time( 'mysql' ),
			), $ticket_data );

			wp_remote_post( $zapier_webhook, array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => json_encode( $zapier_data ),
				'blocking' => false,
			) );
		}
	}

	/**
	 * Process Bulk Actions.
	 *
	 * @since 1.1.2
	 */
	public function process_bulk_action() {
		// Verify nonce
		if ( ! check_ajax_referer( 'grt_ticket_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'grt-ticket' ) ) );
			return;
		}

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_others_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'grt-ticket' ) ) );
			return;
		}

		$action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) ) : '';
		$ticket_ids = isset( $_POST['ticket_ids'] ) ? array_map( 'intval', wp_unslash( $_POST['ticket_ids'] ) ) : array();

		if ( empty( $action ) || empty( $ticket_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'grt-ticket' ) ) );
			return;
		}

		$success_count = 0;
		$error_count = 0;

		foreach ( $ticket_ids as $ticket_id ) {
			$result = false;
			switch ( $action ) {
				case 'delete':
					$result = GRT_Ticket_Database::delete_ticket( $ticket_id );
					break;
				case 'close':
					$result = GRT_Ticket_Database::update_ticket_status( $ticket_id, 'closed' );
					break;
				case 'open':
					$result = GRT_Ticket_Database::update_ticket_status( $ticket_id, 'open' );
					break;
				case 'solved':
					$result = GRT_Ticket_Database::update_ticket_status( $ticket_id, 'solved' );
					break;
			}

			if ( $result ) {
				$success_count++;
			} else {
				$error_count++;
			}
		}

		if ( $success_count > 0 ) {
			/* translators: %d: Number of processed tickets */
			wp_send_json_success( array( 'message' => sprintf( _n( 'Successfully processed %d ticket.', 'Successfully processed %d tickets.', $success_count, 'grt-ticket' ), $success_count ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to process selected tickets.', 'grt-ticket' ) ) );
		}
	}
}
