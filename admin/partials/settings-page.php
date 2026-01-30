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
	// Handle Categories
	$categories_data = array();
	if ( isset( $_POST['grt_categories'] ) && is_array( $_POST['grt_categories'] ) ) {
		foreach ( $_POST['grt_categories'] as $cat ) {
			if ( ! empty( $cat['name'] ) ) {
				$categories_data[] = array(
					'name'  => sanitize_text_field( $cat['name'] ),
					'image' => esc_url_raw( $cat['image'] ),
				);
			}
		}
	}
	update_option( 'grt_ticket_categories', json_encode( $categories_data ) );
	
	update_option( 'grt_ticket_admin_name', sanitize_text_field( $_POST['grt_ticket_admin_name'] ) );
	update_option( 'grt_ticket_enable_email_notifications', isset( $_POST['grt_ticket_enable_email_notifications'] ) ? 1 : 0 );
	update_option( 'grt_ticket_notification_emails', sanitize_textarea_field( $_POST['grt_ticket_notification_emails'] ) );
	update_option( 'grt_ticket_per_page', absint( $_POST['grt_ticket_per_page'] ) );
	update_option( 'grt_ticket_poll_interval', absint( $_POST['grt_ticket_poll_interval'] ) );
	
	// WhatsApp Settings
	update_option( 'grt_ticket_enable_whatsapp', isset( $_POST['grt_ticket_enable_whatsapp'] ) ? 1 : 0 );
	update_option( 'grt_ticket_twilio_sid', sanitize_text_field( $_POST['grt_ticket_twilio_sid'] ) );
	update_option( 'grt_ticket_twilio_token', sanitize_text_field( $_POST['grt_ticket_twilio_token'] ) );
	update_option( 'grt_ticket_twilio_from', sanitize_text_field( $_POST['grt_ticket_twilio_from'] ) );
	update_option( 'grt_ticket_whatsapp_admin_number', sanitize_text_field( $_POST['grt_ticket_whatsapp_admin_number'] ) );

	// Direct Contact Settings
	update_option( 'grt_ticket_enable_direct_call', isset( $_POST['grt_ticket_enable_direct_call'] ) ? 1 : 0 );
	update_option( 'grt_ticket_enable_direct_sms', isset( $_POST['grt_ticket_enable_direct_sms'] ) ? 1 : 0 );
	update_option( 'grt_ticket_support_phone', sanitize_text_field( $_POST['grt_ticket_support_phone'] ) );
	update_option( 'grt_ticket_sms_body', sanitize_textarea_field( $_POST['grt_ticket_sms_body'] ) );
	
	// Email Piping Settings
	update_option( 'grt_ticket_enable_piping', isset( $_POST['grt_ticket_enable_piping'] ) ? 1 : 0 );
	update_option( 'grt_ticket_imap_host', sanitize_text_field( $_POST['grt_ticket_imap_host'] ) );
	update_option( 'grt_ticket_imap_port', absint( $_POST['grt_ticket_imap_port'] ) );
	update_option( 'grt_ticket_imap_user', sanitize_email( $_POST['grt_ticket_imap_user'] ) );
	// Only update password if provided (to avoid clearing it on empty save)
	if ( ! empty( $_POST['grt_ticket_imap_pass'] ) ) {
		update_option( 'grt_ticket_imap_pass', sanitize_text_field( $_POST['grt_ticket_imap_pass'] ) );
	}
	update_option( 'grt_ticket_imap_ssl', isset( $_POST['grt_ticket_imap_ssl'] ) ? 1 : 0 );

	echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully!', 'grt-ticket' ) . '</p></div>';
}

$categories_option = get_option( 'grt_ticket_categories', 'Installation Issue,Customization Help,Bug Report,Feature Request,License Issue' );
$categories = array();

// Try to decode JSON
$decoded = json_decode( $categories_option, true );
if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
	$categories = $decoded;
} else {
	// Fallback to comma-separated
	$items = array_map( 'trim', explode( ',', $categories_option ) );
	foreach ( $items as $item ) {
		if ( ! empty( $item ) ) {
			$categories[] = array( 'name' => $item, 'image' => '' );
		}
	}
}

// Ensure at least one empty row if empty
if ( empty( $categories ) ) {
	$categories[] = array( 'name' => '', 'image' => '' );
}

$admin_name = get_option( 'grt_ticket_admin_name', 'Support Team' );
$enable_email_notifications = get_option( 'grt_ticket_enable_email_notifications', 1 );
$notification_emails = get_option( 'grt_ticket_notification_emails', get_option( 'admin_email' ) );
$per_page = get_option( 'grt_ticket_per_page', 20 );
$poll_interval = get_option( 'grt_ticket_poll_interval', 3000 );

// WhatsApp Options
$enable_whatsapp = get_option( 'grt_ticket_enable_whatsapp', 0 );
$twilio_sid = get_option( 'grt_ticket_twilio_sid', '' );
$twilio_token = get_option( 'grt_ticket_twilio_token', '' );
$twilio_from = get_option( 'grt_ticket_twilio_from', '' );
$whatsapp_admin_number = get_option( 'grt_ticket_whatsapp_admin_number', '' );

// Direct Contact Options
$enable_direct_call = get_option( 'grt_ticket_enable_direct_call', 0 );
$enable_direct_sms = get_option( 'grt_ticket_enable_direct_sms', 0 );
$support_phone = get_option( 'grt_ticket_support_phone', '' );
$sms_body = get_option( 'grt_ticket_sms_body', 'Hello, I need help with my ticket.' );

// Email Piping Options
$enable_piping = get_option( 'grt_ticket_enable_piping', 0 );
$imap_host = get_option( 'grt_ticket_imap_host', '' );
$imap_port = get_option( 'grt_ticket_imap_port', 993 );
$imap_user = get_option( 'grt_ticket_imap_user', '' );
$imap_pass = get_option( 'grt_ticket_imap_pass', '' );
$imap_ssl = get_option( 'grt_ticket_imap_ssl', 1 );
?>

<div class="wrap grt-ticket-wrap">
	<div class="grt-ticket-header">
		<h1><?php esc_html_e( 'GRT Ticket Settings', 'grt-ticket' ); ?></h1>
		<p><?php esc_html_e( 'Configure your support ticket system settings.', 'grt-ticket' ); ?></p>
	</div>

	<h2 class="nav-tab-wrapper grt-settings-tabs">
		<a href="#grt-tab-general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General Settings', 'grt-ticket' ); ?></a>
		<a href="#grt-tab-email" class="nav-tab"><?php esc_html_e( 'Email Notifications', 'grt-ticket' ); ?></a>
		<a href="#grt-tab-piping" class="nav-tab"><?php esc_html_e( 'Email Piping (Reply via Email)', 'grt-ticket' ); ?></a>
		<a href="#grt-tab-whatsapp" class="nav-tab"><?php esc_html_e( 'WhatsApp Integrations', 'grt-ticket' ); ?></a>
		<a href="#grt-tab-contact" class="nav-tab"><?php esc_html_e( 'Direct Contact', 'grt-ticket' ); ?></a>
	</h2>

	<form method="post" action="" class="grt-settings-form">
		<?php wp_nonce_field( 'grt_ticket_settings_nonce' ); ?>
		
		<!-- General Settings Tab -->
		<div id="grt-tab-general" class="grt-tab-content active">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label><?php esc_html_e( 'Issue Categories', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<div id="grt-categories-wrapper">
								<?php foreach ( $categories as $index => $cat ) : ?>
									<div class="grt-category-item">
										<input type="text" name="grt_categories[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $cat['name'] ); ?>" placeholder="<?php esc_attr_e( 'Category Name', 'grt-ticket' ); ?>" class="regular-text">
										
										<div class="grt-image-upload-wrapper">
											<input type="hidden" name="grt_categories[<?php echo $index; ?>][image]" value="<?php echo esc_attr( $cat['image'] ); ?>" class="grt-cat-image-url">
											<div class="grt-image-preview">
												<?php if ( ! empty( $cat['image'] ) ) : ?>
													<img src="<?php echo esc_url( $cat['image'] ); ?>" alt="Preview">
												<?php endif; ?>
											</div>
											<button type="button" class="button grt-upload-image"><?php esc_html_e( 'Select Image', 'grt-ticket' ); ?></button>
											<?php if ( ! empty( $cat['image'] ) ) : ?>
												<button type="button" class="button grt-remove-image">×</button>
											<?php else: ?>
												<button type="button" class="button grt-remove-image" style="display:none;">×</button>
											<?php endif; ?>
										</div>

										<button type="button" class="button grt-remove-category"><?php esc_html_e( 'Remove', 'grt-ticket' ); ?></button>
									</div>
								<?php endforeach; ?>
							</div>
							<button type="button" class="button" id="grt-add-category"><?php esc_html_e( 'Add Category', 'grt-ticket' ); ?></button>
							<p class="description"><?php esc_html_e( 'Enter category name and select an image (icon/thumbnail).', 'grt-ticket' ); ?></p>
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
		</div>

		<!-- Email Notifications Tab -->
		<div id="grt-tab-email" class="grt-tab-content">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="grt_ticket_enable_email_notifications"><?php esc_html_e( 'Enable Email Notifications', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<label class="grt-switch">
								<input type="checkbox" name="grt_ticket_enable_email_notifications" id="grt_ticket_enable_email_notifications" value="1" <?php checked( $enable_email_notifications, 1 ); ?>>
								<span class="slider round"></span>
							</label>
							<p class="description"><?php esc_html_e( 'Toggle all email notifications on/off.', 'grt-ticket' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="grt_ticket_notification_emails"><?php esc_html_e( 'Notification Emails', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<textarea name="grt_ticket_notification_emails" id="grt_ticket_notification_emails" rows="3" class="large-text code"><?php echo esc_textarea( $notification_emails ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Enter email addresses separated by commas to receive notifications about new tickets and messages.', 'grt-ticket' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

	<!-- Email Piping Tab -->
	<div id="grt-tab-piping" class="grt-tab-content">
		<div class="grt-settings-section">
			<h3><?php esc_html_e( 'Email Piping Configuration', 'grt-ticket' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Allow users to reply to tickets directly from their email client. This feature uses IMAP to check for new emails.', 'grt-ticket' ); ?>
				<br>
				<strong><?php esc_html_e( 'Note for Gmail Users:', 'grt-ticket' ); ?></strong> 
				<?php esc_html_e( 'You must use an "App Password" instead of your regular password. Go to Google Account > Security > 2-Step Verification > App Passwords.', 'grt-ticket' ); ?>
			</p>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Email Piping', 'grt-ticket' ); ?></th>
					<td>
						<label class="grt-switch">
							<input type="checkbox" name="grt_ticket_enable_piping" id="grt_ticket_enable_piping" value="1" <?php checked( $enable_piping, 1 ); ?>>
							<span class="slider round"></span>
						</label>
						<p class="description"><?php esc_html_e( 'Check this to enable checking email for replies.', 'grt-ticket' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="grt_ticket_imap_host"><?php esc_html_e( 'IMAP Host', 'grt-ticket' ); ?></label></th>
					<td>
						<input type="text" name="grt_ticket_imap_host" id="grt_ticket_imap_host" class="regular-text" value="<?php echo esc_attr( $imap_host ); ?>">
						<p class="description"><?php esc_html_e( 'e.g., imap.gmail.com', 'grt-ticket' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="grt_ticket_imap_port"><?php esc_html_e( 'IMAP Port', 'grt-ticket' ); ?></label></th>
					<td>
						<input type="number" name="grt_ticket_imap_port" id="grt_ticket_imap_port" class="small-text" value="<?php echo esc_attr( $imap_port ); ?>">
						<p class="description"><?php esc_html_e( 'Usually 993 for SSL.', 'grt-ticket' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="grt_ticket_imap_ssl"><?php esc_html_e( 'Use SSL', 'grt-ticket' ); ?></label></th>
					<td>
						<label>
							<input type="checkbox" name="grt_ticket_imap_ssl" value="1" <?php checked( $imap_ssl, 1 ); ?>>
							<?php esc_html_e( 'Enable SSL encryption', 'grt-ticket' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="grt_ticket_imap_user"><?php esc_html_e( 'Email Address', 'grt-ticket' ); ?></label></th>
					<td>
						<input type="email" name="grt_ticket_imap_user" id="grt_ticket_imap_user" class="regular-text" value="<?php echo esc_attr( $imap_user ); ?>">
						<p class="description"><?php esc_html_e( 'The email address to check (e.g., yourname@gmail.com).', 'grt-ticket' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="grt_ticket_imap_pass"><?php esc_html_e( 'Email Password / App Password', 'grt-ticket' ); ?></label></th>
					<td>
						<input type="password" name="grt_ticket_imap_pass" id="grt_ticket_imap_pass" class="regular-text" value="" placeholder="<?php echo ! empty( $imap_pass ) ? '********' : ''; ?>">
						<p class="description"><?php esc_html_e( 'Leave empty to keep existing password.', 'grt-ticket' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
	</div>

		<!-- WhatsApp Integrations Tab -->
		<div id="grt-tab-whatsapp" class="grt-tab-content">
			<div class="grt-settings-section-header">
				<h2><?php esc_html_e( 'WhatsApp Notifications (Twilio)', 'grt-ticket' ); ?></h2>
				<p><?php esc_html_e( 'Configure Twilio API settings to send WhatsApp notifications.', 'grt-ticket' ); ?></p>
			</div>
			
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="grt_ticket_enable_whatsapp"><?php esc_html_e( 'Enable WhatsApp', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<label class="grt-switch">
								<input type="checkbox" name="grt_ticket_enable_whatsapp" id="grt_ticket_enable_whatsapp" value="1" <?php checked( $enable_whatsapp, 1 ); ?>>
								<span class="slider round"></span>
							</label>
							<p class="description"><?php esc_html_e( 'Enable WhatsApp notifications for new tickets and messages.', 'grt-ticket' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="grt_ticket_twilio_sid"><?php esc_html_e( 'Twilio Account SID', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<input type="text" name="grt_ticket_twilio_sid" id="grt_ticket_twilio_sid" value="<?php echo esc_attr( $twilio_sid ); ?>" class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="grt_ticket_twilio_token"><?php esc_html_e( 'Twilio Auth Token', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<input type="password" name="grt_ticket_twilio_token" id="grt_ticket_twilio_token" value="<?php echo esc_attr( $twilio_token ); ?>" class="regular-text">
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="grt_ticket_twilio_from"><?php esc_html_e( 'Twilio WhatsApp Number', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<input type="text" name="grt_ticket_twilio_from" id="grt_ticket_twilio_from" value="<?php echo esc_attr( $twilio_from ); ?>" class="regular-text" placeholder="+14155238886">
							<p class="description"><?php esc_html_e( 'The Twilio WhatsApp number sending the messages (e.g., +14155238886).', 'grt-ticket' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="grt_ticket_whatsapp_admin_number"><?php esc_html_e( 'Admin WhatsApp Number', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<input type="text" name="grt_ticket_whatsapp_admin_number" id="grt_ticket_whatsapp_admin_number" value="<?php echo esc_attr( $whatsapp_admin_number ); ?>" class="regular-text" placeholder="+1234567890">
							<p class="description"><?php esc_html_e( 'The WhatsApp number where admin notifications should be sent.', 'grt-ticket' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<!-- Direct Contact Tab -->
		<div id="grt-tab-contact" class="grt-tab-content">
			<div class="grt-settings-section-header">
				<h2><?php esc_html_e( 'Direct Contact (Call & SMS)', 'grt-ticket' ); ?></h2>
				<p><?php esc_html_e( 'Enable direct "Call Us" and "SMS Us" buttons for users to contact you directly via phone.', 'grt-ticket' ); ?></p>
			</div>
			
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="grt_ticket_support_phone"><?php esc_html_e( 'Support Phone Number', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<input type="text" name="grt_ticket_support_phone" id="grt_ticket_support_phone" value="<?php echo esc_attr( $support_phone ); ?>" class="regular-text" placeholder="+1234567890">
							<p class="description"><?php esc_html_e( 'Enter your support phone number in international format (e.g., +1234567890).', 'grt-ticket' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="grt_ticket_enable_direct_call"><?php esc_html_e( 'Enable Direct Call', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<label class="grt-switch">
								<input type="checkbox" name="grt_ticket_enable_direct_call" id="grt_ticket_enable_direct_call" value="1" <?php checked( $enable_direct_call, 1 ); ?>>
								<span class="slider round"></span>
							</label>
							<p class="description"><?php esc_html_e( 'Show a "Call Us" button.', 'grt-ticket' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="grt_ticket_enable_direct_sms"><?php esc_html_e( 'Enable Direct SMS', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<label class="grt-switch">
								<input type="checkbox" name="grt_ticket_enable_direct_sms" id="grt_ticket_enable_direct_sms" value="1" <?php checked( $enable_direct_sms, 1 ); ?>>
								<span class="slider round"></span>
							</label>
							<p class="description"><?php esc_html_e( 'Show an "SMS Us" button.', 'grt-ticket' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="grt_ticket_sms_body"><?php esc_html_e( 'SMS Template', 'grt-ticket' ); ?></label>
						</th>
						<td>
							<textarea name="grt_ticket_sms_body" id="grt_ticket_sms_body" rows="3" class="large-text"><?php echo esc_textarea( $sms_body ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Pre-filled message for the SMS.', 'grt-ticket' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Hidden fields for categories JS to work -->
		<!-- JS Logic moved to admin/js/settings-page.js -->

		<p class="submit">
			<input type="submit" name="grt_ticket_save_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'grt-ticket' ); ?>">
		</p>
	</form>
</div>
