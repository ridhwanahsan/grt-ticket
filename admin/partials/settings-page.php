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
	update_option( 'grt_ticket_notification_emails', sanitize_textarea_field( $_POST['grt_ticket_notification_emails'] ) );
	update_option( 'grt_ticket_per_page', absint( $_POST['grt_ticket_per_page'] ) );
	update_option( 'grt_ticket_poll_interval', absint( $_POST['grt_ticket_poll_interval'] ) );
	
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
$notification_emails = get_option( 'grt_ticket_notification_emails', get_option( 'admin_email' ) );
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
						
						<script>
						jQuery(document).ready(function($) {
							var wrapper = $('#grt-categories-wrapper');
							var addBtn = $('#grt-add-category');
							var count = <?php echo count( $categories ); ?>;
							
							// Add Category
							addBtn.on('click', function() {
								var item = `
									<div class="grt-category-item">
										<input type="text" name="grt_categories[${count}][name]" placeholder="<?php esc_attr_e( 'Category Name', 'grt-ticket' ); ?>" class="regular-text">
										
										<div class="grt-image-upload-wrapper">
											<input type="hidden" name="grt_categories[${count}][image]" class="grt-cat-image-url">
											<div class="grt-image-preview"></div>
											<button type="button" class="button grt-upload-image"><?php esc_html_e( 'Select Image', 'grt-ticket' ); ?></button>
											<button type="button" class="button grt-remove-image" style="display:none;">×</button>
										</div>

										<button type="button" class="button grt-remove-category"><?php esc_html_e( 'Remove', 'grt-ticket' ); ?></button>
									</div>
								`;
								wrapper.append(item);
								count++;
							});
							
							// Remove Category
							wrapper.on('click', '.grt-remove-category', function() {
								if (confirm('<?php esc_attr_e( 'Are you sure?', 'grt-ticket' ); ?>')) {
									$(this).closest('.grt-category-item').remove();
								}
							});

							// Media Uploader
							var frame;
							var currentUploadWrapper;

							wrapper.on('click', '.grt-upload-image', function(e) {
								e.preventDefault();
								currentUploadWrapper = $(this).closest('.grt-image-upload-wrapper');

								if (frame) {
									frame.open();
									return;
								}

								frame = wp.media({
									title: '<?php esc_attr_e( 'Select Category Image', 'grt-ticket' ); ?>',
									button: {
										text: '<?php esc_attr_e( 'Use this image', 'grt-ticket' ); ?>'
									},
									multiple: false
								});

								frame.on('select', function() {
									var attachment = frame.state().get('selection').first().toJSON();
									
									// Update fields
									currentUploadWrapper.find('.grt-cat-image-url').val(attachment.url);
									
									// Update preview
									var previewHtml = '<img src="' + attachment.url + '" alt="Preview">';
									currentUploadWrapper.find('.grt-image-preview').html(previewHtml);
									
									// Show remove button
									currentUploadWrapper.find('.grt-remove-image').show();
								});

								frame.open();
							});

							// Remove Image
							wrapper.on('click', '.grt-remove-image', function() {
								var wrapper = $(this).closest('.grt-image-upload-wrapper');
								wrapper.find('.grt-cat-image-url').val('');
								wrapper.find('.grt-image-preview').empty();
								$(this).hide();
							});
						});
						</script>
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
						<label for="grt_ticket_notification_emails"><?php esc_html_e( 'Notification Emails', 'grt-ticket' ); ?></label>
					</th>
					<td>
						<textarea name="grt_ticket_notification_emails" id="grt_ticket_notification_emails" rows="3" class="large-text code"><?php echo esc_textarea( $notification_emails ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Enter email addresses separated by commas to receive notifications about new tickets and messages.', 'grt-ticket' ); ?></p>
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
