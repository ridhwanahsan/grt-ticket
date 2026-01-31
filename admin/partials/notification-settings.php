<?php
/**
 * Admin notification settings page
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/admin/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Save settings
if ( isset( $_POST['grt_ticket_save_notification_settings'] ) && check_admin_referer( 'grt_ticket_notification_settings_nonce' ) ) {
	update_option( 'grt_ticket_enable_browser_notification', isset( $_POST['grt_ticket_enable_browser_notification'] ) ? 1 : 0 );
	update_option( 'grt_ticket_notification_sound', isset( $_POST['grt_ticket_notification_sound'] ) ? 1 : 0 );
	
	echo '<div class="notice notice-success"><p>' . esc_html__( 'Notification settings saved successfully!', 'grt-ticket' ) . '</p></div>';
}

$enable_browser_notification = get_option( 'grt_ticket_enable_browser_notification', 0 );
$enable_notification_sound = get_option( 'grt_ticket_notification_sound', 0 );
?>

<div class="wrap grt-ticket-wrap">
	<div class="grt-ticket-header">
		<h1><?php esc_html_e( 'Notification Settings', 'grt-ticket' ); ?></h1>
		<p><?php esc_html_e( 'Configure browser and sound notifications for the chat.', 'grt-ticket' ); ?></p>
	</div>

	<form method="post" action="" class="grt-settings-form">
		<?php wp_nonce_field( 'grt_ticket_notification_settings_nonce' ); ?>
		
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="grt_ticket_enable_browser_notification"><?php esc_html_e( 'Browser Notifications', 'grt-ticket' ); ?></label>
					</th>
					<td>
						<label class="grt-switch">
							<input type="checkbox" name="grt_ticket_enable_browser_notification" id="grt_ticket_enable_browser_notification" value="1" <?php checked( $enable_browser_notification, 1 ); ?>>
							<span class="slider round"></span>
						</label>
						<p class="description"><?php esc_html_e( 'Receive browser push notifications when a new message arrives (works even if the tab is in background).', 'grt-ticket' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="grt_ticket_notification_sound"><?php esc_html_e( 'Notification Sound', 'grt-ticket' ); ?></label>
					</th>
					<td>
						<label class="grt-switch">
							<input type="checkbox" name="grt_ticket_notification_sound" id="grt_ticket_notification_sound" value="1" <?php checked( $enable_notification_sound, 1 ); ?>>
							<span class="slider round"></span>
						</label>
						<p class="description"><?php esc_html_e( 'Play a sound when a new message arrives.', 'grt-ticket' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="grt_ticket_save_notification_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'grt-ticket' ); ?>">
		</p>
	</form>
</div>
