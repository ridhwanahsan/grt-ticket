<?php
/**
 * Admin settings page
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/admin/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Save settings
if ( isset( $_POST['grt_ticket_save_settings'] ) && check_admin_referer( 'grt_ticket_settings_nonce' ) ) {
	update_option( 'grt_ticket_categories', sanitize_text_field( $_POST['grt_ticket_categories'] ) );
	update_option( 'grt_ticket_admin_name', sanitize_text_field( $_POST['grt_ticket_admin_name'] ) );
	update_option( 'grt_ticket_per_page', absint( $_POST['grt_ticket_per_page'] ) );
	update_option( 'grt_ticket_poll_interval', absint( $_POST['grt_ticket_poll_interval'] ) );
	
	echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully!', 'grt-ticket' ) . '</p></div>';
}

$categories = get_option( 'grt_ticket_categories', 'Installation Issue,Customization Help,Bug Report,Feature Request,License Issue' );
$admin_name = get_option( 'grt_ticket_admin_name', 'Support Team' );
$per_page = get_option( 'grt_ticket_per_page', 20 );
$poll_interval = get_option( 'grt_ticket_poll_interval', 3000 );
?>

<div class="wrap grt-ticket-wrap">
	<div class="grt-ticket-header">
		<h1><?php esc_html_e( 'GRT Ticket Settings', 'grt-ticket' ); ?></h1>
		<p><?php esc_html_e( 'Configure your support ticket system settings.', 'grt-ticket' ); ?></p>
	</div>

	<form method="post" action="" class="grt-settings-form">
		<?php wp_nonce_field( 'grt_ticket_settings_nonce' ); ?>
		
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="grt_ticket_categories"><?php esc_html_e( 'Issue Categories', 'grt-ticket' ); ?></label>
					</th>
					<td>
						<textarea name="grt_ticket_categories" id="grt_ticket_categories" rows="5" class="large-text"><?php echo esc_textarea( $categories ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Enter issue categories separated by commas. These will appear in the dropdown on the ticket submission form.', 'grt-ticket' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="grt_ticket_admin_name"><?php esc_html_e( 'Admin Display Name', 'grt-ticket' ); ?></label>
					</th>
					<td>
						<input type="text" name="grt_ticket_admin_name" id="grt_ticket_admin_name" value="<?php echo esc_attr( $admin_name ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'The name that will be displayed for admin messages in the chat.', 'grt-ticket' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="grt_ticket_per_page"><?php esc_html_e( 'Tickets Per Page', 'grt-ticket' ); ?></label>
					</th>
					<td>
						<input type="number" name="grt_ticket_per_page" id="grt_ticket_per_page" value="<?php echo esc_attr( $per_page ); ?>" class="small-text" min="1">
						<p class="description"><?php esc_html_e( 'Number of tickets to display per page in the admin area.', 'grt-ticket' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="grt_ticket_poll_interval"><?php esc_html_e( 'Chat Polling Interval', 'grt-ticket' ); ?></label>
					</th>
					<td>
						<input type="number" name="grt_ticket_poll_interval" id="grt_ticket_poll_interval" value="<?php echo esc_attr( $poll_interval ); ?>" class="small-text" min="1000" step="1000">
						<p class="description"><?php esc_html_e( 'How often to check for new messages in milliseconds (1000ms = 1 second). Recommended: 3000ms.', 'grt-ticket' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="grt_ticket_save_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'grt-ticket' ); ?>">
		</p>
	</form>
</div>
