<?php
/**
 * Admin canned responses page
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/admin/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap grt-ticket-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Canned Responses', 'grt-ticket' ); ?></h1>
	<p><?php esc_html_e( 'Manage your saved replies to quickly respond to tickets.', 'grt-ticket' ); ?></p>

	<div class="grt-canned-response-form card" style="max-width: 600px; padding: 20px; margin-bottom: 20px;">
		<h2><?php esc_html_e( 'Add New Canned Response', 'grt-ticket' ); ?></h2>
		<form method="post" action="">
			<?php wp_nonce_field( 'grt_add_canned_response', 'grt_canned_response_nonce' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row"><label for="title"><?php esc_html_e( 'Title / Shortcut', 'grt-ticket' ); ?></label></th>
					<td>
						<input name="title" type="text" id="title" class="regular-text" required placeholder="<?php esc_attr_e( 'e.g., Hello, Thanks, Closing', 'grt-ticket' ); ?>">
						<p class="description"><?php esc_html_e( 'A short title or keyword to identify this response.', 'grt-ticket' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="response"><?php esc_html_e( 'Response Content', 'grt-ticket' ); ?></label></th>
					<td>
						<textarea name="response" id="response" rows="5" class="large-text" required placeholder="<?php esc_attr_e( 'Enter the full response text here...', 'grt-ticket' ); ?>"></textarea>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<input type="submit" name="grt_add_canned_response" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Add Response', 'grt-ticket' ); ?>">
			</p>
		</form>
	</div>

	<?php if ( empty( $canned_responses ) ) : ?>
		<div class="notice notice-info inline">
			<p><?php esc_html_e( 'No canned responses found.', 'grt-ticket' ); ?></p>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th class="manage-column column-title"><?php esc_html_e( 'Title', 'grt-ticket' ); ?></th>
					<th class="manage-column column-response"><?php esc_html_e( 'Response', 'grt-ticket' ); ?></th>
					<th class="manage-column column-actions" style="width: 100px;"><?php esc_html_e( 'Actions', 'grt-ticket' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $canned_responses as $response ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $response->title ); ?></strong></td>
						<td><?php echo nl2br( esc_html( $response->response ) ); ?></td>
						<td>
							<?php 
							$delete_url = wp_nonce_url( 
								add_query_arg( array( 'action' => 'delete', 'id' => $response->id ) ), 
								'grt_delete_canned_response' 
							); 
							?>
							<a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small delete" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this response?', 'grt-ticket' ); ?>');">
								<?php esc_html_e( 'Delete', 'grt-ticket' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
