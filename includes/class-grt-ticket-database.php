<?php
/**
 * Database operations handler
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/includes
 */

/**
 * Database operations handler.
 *
 * Handles all database operations for tickets and messages.
 */
class GRT_Ticket_Database {

	/**
	 * Cache for table existence checks.
	 *
	 * @var array
	 */
	private static $table_checks = array();

	/**
	 * Check if a table exists.
	 *
	 * @since    1.0.4
	 * @param    string $table_name    Table name.
	 * @return   bool                  True if exists, false otherwise.
	 */
	private static function check_table_exists( $table_name ) {
		global $wpdb;

		if ( isset( self::$table_checks[ $table_name ] ) ) {
			return self::$table_checks[ $table_name ];
		}

		$exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name;
		self::$table_checks[ $table_name ] = $exists;

		return $exists;
	}

	/**
	 * Get the tickets table name.
	 *
	 * @since    1.0.0
	 * @return   string|false    Table name with prefix or false if not exists.
	 */
	private static function get_tickets_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'grt_tickets';

		if ( ! self::check_table_exists( $table_name ) ) {
			return false;
		}

		return $table_name;
	}

	/**
	 * Get the canned responses table name.
	 *
	 * @since    1.0.0
	 * @return   string|false    Table name with prefix or false if not exists.
	 */
	private static function get_canned_responses_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'grt_canned_responses';

		if ( ! self::check_table_exists( $table_name ) ) {
			return false;
		}

		return $table_name;
	}

	/**
	 * Get all canned responses.
	 *
	 * @since    1.0.0
	 * @return   array    Array of canned response objects.
	 */
	public static function get_canned_responses() {
		global $wpdb;

		$table = self::get_canned_responses_table();
		if ( ! $table ) {
			return array();
		}

		$results = $wpdb->get_results( "SELECT * FROM " . $table . " ORDER BY title ASC" );

		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $response ) {
				$results[ $key ]->title    = esc_html( $response->title );
				$results[ $key ]->response = wp_kses_post( $response->response );
			}
		}

		return $results;
	}

	/**
	 * Add a canned response.
	 *
	 * @since    1.0.0
	 * @param    string $title       Title/Shortcut.
	 * @param    string $response    Response content.
	 * @return   int|false           ID on success, false on failure.
	 */
	public static function add_canned_response( $title, $response ) {
		global $wpdb;

		$table = self::get_canned_responses_table();
		if ( ! $table ) {
			return false;
		}

		$result = $wpdb->insert(
			$table,
			array(
				'title'    => sanitize_text_field( $title ),
				'response' => wp_kses_post( $response ),
			),
			array( '%s', '%s' )
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Delete a canned response.
	 *
	 * @since    1.0.0
	 * @param    int $id    Canned response ID.
	 * @return   bool       True on success, false on failure.
	 */
	public static function delete_canned_response( $id ) {
		global $wpdb;

		$table = self::get_canned_responses_table();
		if ( ! $table ) {
			return false;
		}

		$result = $wpdb->delete(
			$table,
			array( 'id' => $id ),
			array( '%d' )
		);

		return (bool) $result;
	}

	/**
	 * Get ticket statistics for dashboard.
	 *
	 * @since    1.0.0
	 * @return   array    Statistics data.
	 */
	public static function get_dashboard_stats() {
		global $wpdb;
		$tickets_table = self::get_tickets_table();

		if ( ! $tickets_table ) {
			return array(
				'total_tickets'       => 0,
				'open_tickets'        => 0,
				'solved_tickets'      => 0,
				'closed_tickets'      => 0,
				'tickets_today'       => 0,
				'avg_resolution_time' => 0,
				'avg_rating'          => 0,
				'rating_distribution' => array(
					5 => 0,
					4 => 0,
					3 => 0,
					2 => 0,
					1 => 0,
				),
			);
		}

		// Total Tickets
		$total_tickets = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $tickets_table" );

		// Tickets by Status
		$status_counts   = $wpdb->get_results( "SELECT status, COUNT(*) as count FROM $tickets_table GROUP BY status", ARRAY_A );
		$stats_by_status = array(
			'open'   => 0,
			'solved' => 0,
			'closed' => 0,
		);
		foreach ( $status_counts as $row ) {
			$stats_by_status[ $row['status'] ] = (int) $row['count'];
		}

		// Tickets Today
		$tickets_today = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $tickets_table WHERE DATE(created_at) = CURDATE()" );

		// Average Resolution Time (in hours) for solved/closed tickets
		// Calculation: Difference between created_at and updated_at for solved/closed tickets
		// Note: This is an approximation as updated_at changes on every update, but it's a good proxy for 'last touch' which is usually closing.
		$avg_resolution_seconds = $wpdb->get_var(
			"SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) 
			 FROM $tickets_table 
			 WHERE status IN ('solved', 'closed')"
		);
		$avg_resolution_hours   = $avg_resolution_seconds ? round( $avg_resolution_seconds / 3600, 1 ) : 0;

		// Average Rating
		$avg_rating = $wpdb->get_var( "SELECT AVG(rating) FROM $tickets_table WHERE rating > 0" );
		$avg_rating = $avg_rating ? round( $avg_rating, 1 ) : 0;

		// Rating Distribution
		$rating_counts = $wpdb->get_results( "SELECT rating, COUNT(*) as count FROM $tickets_table WHERE rating > 0 GROUP BY rating ORDER BY rating DESC", ARRAY_A );
		$ratings_dist  = array(
			5 => 0,
			4 => 0,
			3 => 0,
			2 => 0,
			1 => 0,
		);
		foreach ( $rating_counts as $row ) {
			$ratings_dist[ (int) $row['rating'] ] = (int) $row['count'];
		}

		return array(
			'total_tickets'       => $total_tickets,
			'open_tickets'        => $stats_by_status['open'],
			'solved_tickets'      => $stats_by_status['solved'],
			'closed_tickets'      => $stats_by_status['closed'],
			'tickets_today'       => $tickets_today,
			'avg_resolution_time' => $avg_resolution_hours,
			'avg_rating'          => $avg_rating,
			'rating_distribution' => $ratings_dist,
		);
	}

	/**
	 * Get the messages table name.
	 *
	 * @since    1.0.0
	 * @return   string|false    Table name with prefix or false if not exists.
	 */
	private static function get_messages_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'grt_ticket_messages';

		if ( ! self::check_table_exists( $table_name ) ) {
			return false;
		}

		return $table_name;
	}

	/**
	 * Create a new ticket.
	 *
	 * @since    1.0.0
	 * @param    array $data    Ticket data.
	 * @return   int|false      Ticket ID on success, false on failure.
	 */
	public static function create_ticket( $data ) {
		global $wpdb;

		$table = self::get_tickets_table();
		if ( ! $table ) {
			return false;
		}

		$result = $wpdb->insert(
			$table,
			array(
				'user_id'     => isset( $data['user_id'] ) ? (int) $data['user_id'] : 0,
				'user_email'  => sanitize_email( $data['user_email'] ),
				'user_name'   => sanitize_text_field( $data['user_name'] ),
				'theme_name'  => sanitize_text_field( $data['theme_name'] ),
				'category'    => sanitize_text_field( $data['category'] ),
				'title'       => sanitize_text_field( $data['title'] ),
				'description' => wp_kses_post( $data['description'] ),
				'priority'    => isset( $data['priority'] ) ? sanitize_text_field( $data['priority'] ) : 'medium',
				'status'      => 'open',
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get a ticket by ID.
	 *
	 * @since    1.0.0
	 * @param    int $ticket_id    Ticket ID.
	 * @return   object|null       Ticket object or null.
	 */
	public static function get_ticket( $ticket_id ) {
		global $wpdb;

		$table = self::get_tickets_table();
		if ( ! $table ) {
			return null;
		}

		$ticket = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . $table . " WHERE id = %d",
				$ticket_id
			)
		);

		if ( $ticket ) {
			$ticket->user_email      = esc_html( $ticket->user_email );
			$ticket->user_name       = esc_html( $ticket->user_name );
			$ticket->theme_name      = esc_html( $ticket->theme_name );
			$ticket->category        = esc_html( $ticket->category );
			$ticket->title           = esc_html( $ticket->title );
			$ticket->description     = wp_kses_post( $ticket->description );
			$ticket->priority        = esc_html( $ticket->priority );
			$ticket->status          = esc_html( $ticket->status );
			$ticket->rating_feedback = isset( $ticket->rating_feedback ) ? esc_html( $ticket->rating_feedback ) : '';
		}

		return $ticket;
	}

	/**
	 * Get tickets with optional filters.
	 *
	 * @since    1.0.0
	 * @param    array $args    Query arguments.
	 * @return   array          Array of ticket objects.
	 */
	public static function get_tickets( $args = array() ) {
		global $wpdb;

		$table = self::get_tickets_table();
		if ( ! $table ) {
			return array();
		}

		$defaults = array(
			'user_id'    => 0,
			'user_email' => '',
			'status'     => '',
			'limit'      => 20,
			'offset'     => 0,
			'orderby'    => 'created_at',
			'order'      => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$where        = array( '1=1' );
		$prepare_args = array();

		if ( ! empty( $args['user_id'] ) ) {
			$where[]        = 'user_id = %d';
			$prepare_args[] = $args['user_id'];
		}

		if ( ! empty( $args['user_email'] ) ) {
			$where[]        = 'user_email = %s';
			$prepare_args[] = $args['user_email'];
		}

		if ( ! empty( $args['status'] ) ) {
			$where[]        = 'status = %s';
			$prepare_args[] = $args['status'];
		}

		$where_clause = implode( ' AND ', $where );

		$orderby = in_array( $args['orderby'], array( 'id', 'created_at', 'updated_at', 'status' ), true ) ? $args['orderby'] : 'created_at';
		$order   = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';

		$prepare_args[] = (int) $args['limit'];
		$prepare_args[] = (int) $args['offset'];

		$query = "SELECT * FROM " . $table . " WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";

		if ( ! empty( $prepare_args ) ) {
			$query = $wpdb->prepare( $query, $prepare_args );
		}

		$results = $wpdb->get_results( $query );

		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $ticket ) {
				$results[ $key ]->user_email      = esc_html( $ticket->user_email );
				$results[ $key ]->user_name       = esc_html( $ticket->user_name );
				$results[ $key ]->theme_name      = esc_html( $ticket->theme_name );
				$results[ $key ]->category        = esc_html( $ticket->category );
				$results[ $key ]->title           = esc_html( $ticket->title );
				$results[ $key ]->description     = wp_kses_post( $ticket->description );
				$results[ $key ]->priority        = esc_html( $ticket->priority );
				$results[ $key ]->status          = esc_html( $ticket->status );
				$results[ $key ]->rating_feedback = isset( $ticket->rating_feedback ) ? esc_html( $ticket->rating_feedback ) : '';
			}
		}

		return $results;
	}

	/**
	 * Count tickets with optional filters.
	 *
	 * @since    1.0.0
	 * @param    array $args    Query arguments.
	 * @return   int            Number of tickets.
	 */
	public static function count_tickets( $args = array() ) {
		global $wpdb;

		$table = self::get_tickets_table();
		if ( ! $table ) {
			return 0;
		}

		$where        = array( '1=1' );
		$prepare_args = array();

		if ( ! empty( $args['user_id'] ) ) {
			$where[]        = 'user_id = %d';
			$prepare_args[] = $args['user_id'];
		}

		if ( ! empty( $args['user_email'] ) ) {
			$where[]        = 'user_email = %s';
			$prepare_args[] = $args['user_email'];
		}

		if ( ! empty( $args['status'] ) ) {
			$where[]        = 'status = %s';
			$prepare_args[] = $args['status'];
		}

		$where_clause = implode( ' AND ', $where );

		$query = "SELECT COUNT(*) FROM " . $table . " WHERE {$where_clause}";

		if ( ! empty( $prepare_args ) ) {
			$query = $wpdb->prepare( $query, $prepare_args );
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Update ticket status.
	 *
	 * @since    1.0.0
	 * @param    int    $ticket_id    Ticket ID.
	 * @param    string $status       New status.
	 * @return   bool                 True on success, false on failure.
	 */
	public static function update_ticket_status( $ticket_id, $status ) {
		global $wpdb;

		$table = self::get_tickets_table();
		if ( ! $table ) {
			return false;
		}

		if ( ! in_array( $status, array( 'open', 'solved', 'closed' ), true ) ) {
			return false;
		}

		$result = $wpdb->update(
			$table,
			array( 'status' => $status ),
			array( 'id' => $ticket_id ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Update ticket rating.
	 *
	 * @since    1.0.0
	 * @param    int    $ticket_id    Ticket ID.
	 * @param    int    $rating       Rating (1-5).
	 * @param    string $feedback     Optional feedback.
	 * @return   bool                 True on success, false on failure.
	 */
	public static function update_ticket_rating( $ticket_id, $rating, $feedback = '' ) {
		global $wpdb;

		$table = self::get_tickets_table();
		if ( ! $table ) {
			return false;
		}

		$rating = (int) $rating;
		if ( $rating < 1 || $rating > 5 ) {
			return false;
		}

		$result = $wpdb->update(
			$table,
			array(
				'rating'          => $rating,
				'rating_feedback' => sanitize_textarea_field( $feedback ),
			),
			array( 'id' => $ticket_id ),
			array( '%d', '%s' ),
			array( '%d' )
		);

		return false !== $result;
	}



	/**
	 * Add a message to a ticket.
	 *
	 * @since    1.0.0
	 * @param    array $data    Message data.
	 * @return   int|false      Message ID on success, false on failure.
	 */
	public static function add_message( $data ) {
		global $wpdb;

		$table = self::get_messages_table();
		if ( ! $table ) {
			return false;
		}

		$insert_data = array(
			'ticket_id'   => (int) $data['ticket_id'],
			'sender_type' => in_array( $data['sender_type'], array( 'admin', 'user' ), true ) ? $data['sender_type'] : 'user',
			'sender_name' => sanitize_text_field( $data['sender_name'] ),
			'message'     => wp_kses_post( $data['message'] ),
		);

		$formats = array( '%d', '%s', '%s', '%s' );

		// Add attachment URL if provided
		if ( ! empty( $data['attachment_url'] ) ) {
			$insert_data['attachment_url'] = esc_url_raw( $data['attachment_url'] );
			$formats[]                     = '%s';
		}

		$result = $wpdb->insert(
			$table,
			$insert_data,
			$formats
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get a message by ID.
	 *
	 * @since    1.0.0
	 * @param    int $message_id    Message ID.
	 * @return   object|null        Message object or null.
	 */
	public static function get_message( $message_id ) {
		global $wpdb;

		$table = self::get_messages_table();
		if ( ! $table ) {
			return null;
		}

		$message = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . $table . " WHERE id = %d",
				$message_id
			)
		);

		if ( $message ) {
			$message->sender_name    = esc_html( $message->sender_name );
			$message->message        = wp_kses_post( $message->message );
			$message->attachment_url = ! empty( $message->attachment_url ) ? esc_url( $message->attachment_url ) : '';
		}

		return $message;
	}

	/**
	 * Get messages for a ticket.
	 *
	 * @since    1.0.0
	 * @param    int $ticket_id       Ticket ID.
	 * @param    int $since_id        Optional. Get messages after this ID.
	 * @return   array                Array of message objects.
	 */
	public static function get_messages( $ticket_id, $since_id = 0 ) {
		global $wpdb;

		$table = self::get_messages_table();
		if ( ! $table ) {
			return array();
		}

		if ( $since_id > 0 ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM " . $table . " WHERE ticket_id = %d AND id > %d ORDER BY created_at ASC",
					$ticket_id,
					$since_id
				)
			);
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM " . $table . " WHERE ticket_id = %d ORDER BY created_at ASC",
					$ticket_id
				)
			);
		}

		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $message ) {
				$results[ $key ]->sender_name    = esc_html( $message->sender_name );
				$results[ $key ]->message        = wp_kses_post( $message->message );
				$results[ $key ]->attachment_url = ! empty( $message->attachment_url ) ? esc_url( $message->attachment_url ) : '';
			}
		}

		return $results;
	}

	/**
	 * Get the last message ID for a ticket.
	 *
	 * @since    1.0.0
	 * @param    int $ticket_id    Ticket ID.
	 * @return   int               Last message ID or 0.
	 */
	public static function get_last_message_id( $ticket_id ) {
		global $wpdb;

		$table = self::get_messages_table();
		if ( ! $table ) {
			return 0;
		}

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(id) FROM " . $table . " WHERE ticket_id = %d",
				$ticket_id
			)
		);

		return $result ? (int) $result : 0;
	}

	/**
	 * Delete a ticket and all its messages.
	 *
	 * @since    1.0.0
	 * @param    int $ticket_id    Ticket ID.
	 * @return   bool              True on success, false on failure.
	 */
	public static function delete_ticket( $ticket_id ) {
		global $wpdb;

		$tickets_table  = self::get_tickets_table();
		$messages_table = self::get_messages_table();

		if ( ! $tickets_table || ! $messages_table ) {
			return false;
		}

		$wpdb->query( 'START TRANSACTION' );

		// Delete messages first
		$messages_result = $wpdb->delete(
			$messages_table,
			array( 'ticket_id' => $ticket_id ),
			array( '%d' )
		);

		// Delete ticket
		$ticket_result = $wpdb->delete(
			$tickets_table,
			array( 'id' => $ticket_id ),
			array( '%d' )
		);

		if ( false !== $messages_result && false !== $ticket_result ) {
			$wpdb->query( 'COMMIT' );
			return true;
		} else {
			$wpdb->query( 'ROLLBACK' );
			return false;
		}
	}
}
