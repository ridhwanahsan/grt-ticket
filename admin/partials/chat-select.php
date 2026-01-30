<?php
/**
 * Admin chat select page
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
	<div class="grt-ticket-header">
		<h1><?php esc_html_e( 'Support Chat', 'grt-ticket' ); ?></h1>
		<p><?php esc_html_e( 'Select a ticket to start chatting with the user.', 'grt-ticket' ); ?></p>
	</div>

	<?php if ( empty( $tickets ) ) : ?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'No tickets available for chat.', 'grt-ticket' ); ?></p>
		</div>
	<?php else : ?>
		<table class="grt-tickets-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Title', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'User', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Status', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'grt-ticket' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $tickets as $ticket ) : ?>
					<tr>
						<td><?php echo esc_html( $ticket->id ); ?></td>
						<td><strong><?php echo esc_html( $ticket->title ); ?></strong></td>
						<td><?php echo esc_html( $ticket->user_name ); ?></td>
						<td>
							<span class="grt-ticket-status status-<?php echo esc_attr( $ticket->status ); ?>">
								<?php echo esc_html( ucfirst( $ticket->status ) ); ?>
							</span>
						</td>
						<td class="grt-ticket-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket->id ) ); ?>" class="button button-primary">
								<?php esc_html_e( 'Open Chat', 'grt-ticket' ); ?>
							</a>
							<button type="button" class="button button-secondary grt-delete-ticket" data-ticket-id="<?php echo esc_attr( $ticket->id ); ?>">
								<?php esc_html_e( 'Delete', 'grt-ticket' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
