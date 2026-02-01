<?php
/**
 * Supabase Integration Handler
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/includes
 */

class GRT_Supabase {

    /**
     * Push message to Supabase (Server-side)
     * 
     * @param array $message_data The message data to sync
     * @return void
     */
    public static function sync_message( $message_data ) {
        // Log entry
        error_log( 'GRT Supabase: sync_message called for message ID: ' . ( isset($message_data['id']) ? $message_data['id'] : 'unknown' ) );

        // Check if enabled
        $enabled = get_option( 'grt_ticket_enable_supabase', 0 );
        if ( ! $enabled ) {
            error_log( 'GRT Supabase: Sync aborted. "grt_ticket_enable_supabase" is false/0.' );
            return;
        }

        // Security: Do not sync internal messages to public Supabase channel
        if ( ! empty( $message_data['is_internal'] ) ) {
            error_log( 'GRT Supabase: Sync aborted. Message is marked as internal.' );
            return;
        }

        $supabase_url = get_option( 'grt_ticket_supabase_url', '' );
        $supabase_key = get_option( 'grt_ticket_supabase_service_role', '' ); // Use Service Role for backend writes

        if ( empty( $supabase_url ) || empty( $supabase_key ) ) {
            error_log( 'GRT Supabase: Missing URL or Key' );
            return;
        }

        // Remove trailing slash if present
        $supabase_url = rtrim( $supabase_url, '/' );
        $url = $supabase_url . '/rest/v1/grt_messages';
        
        $body_data = array(
            'id'             => $message_data['id'],
            'ticket_id'      => $message_data['ticket_id'],
            'sender_type'    => $message_data['sender_type'],
            'sender_name'    => $message_data['sender_name'],
            'message'        => $message_data['message'],
            'attachment_url' => isset($message_data['attachment_url']) ? $message_data['attachment_url'] : '',
            'is_internal'    => $message_data['is_internal'],
            'created_at'     => gmdate( 'Y-m-d\TH:i:s\Z' ) // Send UTC ISO 8601
        );

        error_log( 'GRT Supabase: Sending data: ' . print_r( $body_data, true ) );

        $body = json_encode( $body_data );

        $response = wp_remote_post( $url, array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'apikey'        => $supabase_key,
                'Authorization' => 'Bearer ' . $supabase_key,
                'Prefer'        => 'return=minimal'
            ),
            'body'    => $body,
            'blocking' => true // Changed to true for debugging
        ));

        if ( is_wp_error( $response ) ) {
            error_log( 'GRT Supabase Error: ' . $response->get_error_message() );
        } else {
            $code = wp_remote_retrieve_response_code( $response );
            $msg = wp_remote_retrieve_body( $response );
            error_log( 'GRT Supabase Response Code: ' . $code );
            if ( $code >= 400 ) {
                error_log( 'GRT Supabase Response Body: ' . $msg );
            }
        }
    }
}
