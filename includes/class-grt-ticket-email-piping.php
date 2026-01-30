<?php
/**
 * Email Piping functionality
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/includes
 */

class GRT_Ticket_Email_Piping {

	public function __construct() {
		// Constructor left empty as hooks are registered via GRT_Ticket loader
	}

	public function check_emails() {
		if ( ! get_option( 'grt_ticket_enable_piping' ) ) {
			return;
		}

		if ( ! function_exists( 'imap_open' ) ) {
			error_log( 'GRT Ticket: IMAP extension not enabled.' );
			return;
		}

		$host = get_option( 'grt_ticket_imap_host' );
		$port = get_option( 'grt_ticket_imap_port' );
		$user = get_option( 'grt_ticket_imap_user' );
		$pass = get_option( 'grt_ticket_imap_pass' );
		$ssl  = get_option( 'grt_ticket_imap_ssl' ) ? '/ssl' : '';

		if ( empty( $host ) || empty( $user ) || empty( $pass ) ) {
			return;
		}

		$mailbox = "{{$host}:{$port}/imap{$ssl}}INBOX";
		
		// Suppress warnings to avoid filling error logs on connection failures
		$inbox = @imap_open( $mailbox, $user, $pass );

		if ( ! $inbox ) {
			error_log( 'GRT Ticket: IMAP connection failed: ' . imap_last_error() );
			return;
		}

		// Search for UNSEEN emails
		$emails = imap_search( $inbox, 'UNSEEN' );

		if ( $emails ) {
			rsort( $emails ); // Newest first

			foreach ( $emails as $email_number ) {
				$overview = imap_fetch_overview( $inbox, $email_number, 0 );
				
				if ( isset( $overview[0]->subject ) ) {
					$subject = $overview[0]->subject;
					$from = $overview[0]->from;
					$ticket_id = $this->parse_ticket_id( $subject );

					if ( $ticket_id ) {
						// It's a reply to an existing ticket
						$message_body = $this->get_part( $inbox, $email_number, "TEXT/PLAIN" );
						if ( ! $message_body ) {
							$message_body = $this->get_part( $inbox, $email_number, "TEXT/HTML" );
							$message_body = strip_tags( $message_body );
						}
						
						$message_body = $this->clean_reply_body( $message_body );
						
						// Extract sender email
						$sender_email = '';
						if ( preg_match( '/<([^>]+)>/', $from, $matches ) ) {
							$sender_email = $matches[1];
						} else {
							$sender_email = $from;
						}

						$this->add_reply( $ticket_id, $message_body, $sender_email );
					}
				}
			}
		}

		imap_close( $inbox );
	}

	private function parse_ticket_id( $subject ) {
		if ( preg_match( '/#(\d+)/', $subject, $matches ) ) {
			return intval( $matches[1] );
		}
		return 0;
	}

	private function get_part( $imap, $uid, $mimetype, $structure = false, $partNumber = false ) {
		if ( ! $structure ) {
			$structure = imap_fetchstructure( $imap, $uid );
		}
		if ( $structure ) {
			if ( $mimetype == $this->get_mime_type( $structure ) ) {
				if ( ! $partNumber ) {
					$partNumber = 1;
				}
				$text = imap_fetchbody( $imap, $uid, $partNumber );
				if ( $structure->encoding == 3 ) {
					return imap_base64( $text );
				} else if ( $structure->encoding == 4 ) {
					return imap_qprint( $text );
				} else {
					return $text;
				}
			}

			if ( $structure->type == 1 ) { /* multipart */
				foreach ( $structure->parts as $index => $subStruct ) {
					$prefix = "";
					if ( $partNumber ) {
						$prefix = $partNumber . ".";
					}
					$data = $this->get_part( $imap, $uid, $mimetype, $subStruct, $prefix . ( $index + 1 ) );
					if ( $data ) {
						return $data;
					}
				}
			}
		}
		return false;
	}

	private function get_mime_type( $structure ) {
		$primaryMimetype = array( "TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER" );
		if ( $structure->subtype ) {
			return $primaryMimetype[ (int) $structure->type ] . "/" . $structure->subtype;
		}
		return "TEXT/PLAIN";
	}

	private function clean_reply_body( $body ) {
		// Basic cleanup to remove quoted replies
		$delimiters = array(
			'/^On.*wrote:$/m',
			'/^From:.*$/m',
			'/^-+Original Message-+$/m',
			'/^>.*$/m' // Quoted lines
		);

		$lines = explode( "\n", $body );
		$new_lines = array();
		
		foreach ( $lines as $line ) {
			$line = trim( $line );
			$is_delimiter = false;
			
			// Check for quoted lines (>)
			if ( strpos( $line, '>' ) === 0 ) {
				continue; // Skip quoted lines
			}

			foreach ( $delimiters as $delimiter ) {
				if ( preg_match( $delimiter, $line ) ) {
					$is_delimiter = true;
					break;
				}
			}
			
			if ( $is_delimiter ) {
				break; // Stop at the first delimiter
			}
			
			$new_lines[] = $line;
		}
		
		return implode( "\n", $new_lines );
	}

	private function add_reply( $ticket_id, $message, $sender_email ) {
		$ticket = GRT_Ticket_Database::get_ticket( $ticket_id );
		if ( ! $ticket ) {
			return;
		}

		// Determine sender type
		$admin_emails = get_option( 'grt_ticket_notification_emails', get_option( 'admin_email' ) );
		$admin_emails_arr = array_map( 'trim', explode( ',', $admin_emails ) );
		
		$sender_type = 'user';
		$sender_name = $ticket->user_name;

		if ( in_array( $sender_email, $admin_emails_arr ) || $sender_email === get_option( 'admin_email' ) ) {
			$sender_type = 'admin';
			$sender_name = get_option( 'grt_ticket_admin_name', 'Support Team' );
		} else {
			if ( strcasecmp( $sender_email, $ticket->user_email ) !== 0 ) {
				 // Optional: allow flexible matching or return
				 return;
			}
		}

		if ( empty( trim( $message ) ) ) {
			return;
		}

		GRT_Ticket_Database::add_message( array(
			'ticket_id'   => $ticket_id,
			'sender_type' => $sender_type,
			'sender_name' => $sender_name,
			'message'     => wp_kses_post( $message ),
		) );
	}

}
