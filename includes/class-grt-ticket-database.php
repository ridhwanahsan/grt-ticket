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
	 * Get the tickets table name.
	 *
	 * @since    1.0.0
	 * @return   string    Table name with prefix.
	 */
	private static function get_tickets_table() {
		global $wpdb;
		return $wpdb->prefix . 'grt_tickets';
	}

	/**
	 * Get the messages table name.
	 *
	 * @since    1.0.0
	 * @return   string    Table name with prefix.
	 */
	private static function get_messages_table() {
		global $wpdb;
		return $wpdb->prefix . 'grt_ticket_messages';
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

		$result = $wpdb->insert(
			self::get_tickets_table(),
			array(
				'user_id'      => isset( $data['user_id'] ) ? (int) $data['user_id'] : 0,
				'user_email'   => sanitize_email( $data['user_email'] ),
				'user_name'    => sanitize_text_field( $data['user_name'] ),
				'theme_name'   => sanitize_text_field( $data['theme_name'] ),
				'license_code' => sanitize_text_field( $data['license_code'] ),
				'category'     => sanitize_text_field( $data['category'] ),
				'title'        => sanitize_text_field( $data['title'] ),
				'description'  => wp_kses_post( $data['description'] ),
				'status'       => 'open',
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
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

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . self::get_tickets_table() . " WHERE id = %d",
				$ticket_id
			)
		);
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

		$where = array( '1=1' );
		$prepare_args = array();

		if ( ! empty( $args['user_id'] ) ) {
			$where[] = 'user_id = %d';
			$prepare_args[] = $args['user_id'];
		}

		if ( ! empty( $args['user_email'] ) ) {
			$where[] = 'user_email = %s';
			$prepare_args[] = $args['user_email'];
		}

		if ( ! empty( $args['status'] ) ) {
			$where[] = 'status = %s';
			$prepare_args[] = $args['status'];
		}

		$where_clause = implode( ' AND ', $where );

		$orderby = in_array( $args['orderby'], array( 'id', 'created_at', 'updated_at', 'status' ), true ) ? $args['orderby'] : 'created_at';
		$order = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';

		$prepare_args[] = (int) $args['limit'];
		$prepare_args[] = (int) $args['offset'];

		$query = "SELECT * FROM " . self::get_tickets_table() . " WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";

		if ( ! empty( $prepare_args ) ) {
			$query = $wpdb->prepare( $query, $prepare_args );
		}

		return $wpdb->get_results( $query );
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

		$where = array( '1=1' );
		$prepare_args = array();

		if ( ! empty( $args['user_id'] ) ) {
			$where[] = 'user_id = %d';
			$prepare_args[] = $args['user_id'];
		}

		if ( ! empty( $args['user_email'] ) ) {
			$where[] = 'user_email = %s';
			$prepare_args[] = $args['user_email'];
		}

		if ( ! empty( $args['status'] ) ) {
			$where[] = 'status = %s';
			$prepare_args[] = $args['status'];
		}

		$where_clause = implode( ' AND ', $where );

		$query = "SELECT COUNT(*) FROM " . self::get_tickets_table() . " WHERE {$where_clause}";

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

		if ( ! in_array( $status, array( 'open', 'solved', 'closed' ), true ) ) {
			return false;
		}

		$result = $wpdb->update(
			self::get_tickets_table(),
			array( 'status' => $status ),
			array( 'id' => $ticket_id ),
			array( '%s' ),
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
			$formats[] = '%s';
		}

		$result = $wpdb->insert(
			self::get_messages_table(),
			$insert_data,
			$formats
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
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

		if ( $since_id > 0 ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM " . self::get_messages_table() . " WHERE ticket_id = %d AND id > %d ORDER BY created_at ASC",
					$ticket_id,
					$since_id
				)
			);
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM " . self::get_messages_table() . " WHERE ticket_id = %d ORDER BY created_at ASC",
				$ticket_id
			)
		);
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

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(id) FROM " . self::get_messages_table() . " WHERE ticket_id = %d",
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

		// Delete messages first
		$wpdb->delete(
			self::get_messages_table(),
			array( 'ticket_id' => $ticket_id ),
			array( '%d' )
		);

		// Delete ticket
		$result = $wpdb->delete(
			self::get_tickets_table(),
			array( 'id' => $ticket_id ),
			array( '%d' )
		);

		return false !== $result;
	}
}
