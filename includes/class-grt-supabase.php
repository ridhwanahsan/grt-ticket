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
        // Check if enabled
        $enabled = get_option( 'grt_ticket_enable_supabase', 0 );
        if ( ! $enabled ) {
            return;
        }

        // Security: Do not sync internal messages to public Supabase channel
        if ( ! empty( $message_data['is_internal'] ) ) {
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
            'id'             => (int) $message_data['id'],
            'ticket_id'      => (int) $message_data['ticket_id'],
            'sender_type'    => $message_data['sender_type'],
            'sender_name'    => $message_data['sender_name'],
            'message'        => $message_data['message'],
            'attachment_url' => isset($message_data['attachment_url']) ? $message_data['attachment_url'] : '',
            'is_internal'    => (int) $message_data['is_internal'],
            'created_at'     => gmdate( 'Y-m-d\TH:i:s\Z' ) // Send UTC ISO 8601
        );

        $body = json_encode( $body_data );

        $response = wp_remote_post( $url, array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'apikey'        => $supabase_key,
                'Authorization' => 'Bearer ' . $supabase_key,
                'Prefer'        => 'return=representation'
            ),
            'body'    => $body,
            'blocking' => false // Set to false for production performance
        ));

        if ( is_wp_error( $response ) ) {
            error_log( 'GRT Supabase Error: ' . $response->get_error_message() );
        }
    }
}
