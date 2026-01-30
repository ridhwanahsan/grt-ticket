<?php
/**
 * Admin tickets list page
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
		<h1><?php esc_html_e( 'Support Tickets', 'grt-ticket' ); ?></h1>
		<p><?php esc_html_e( 'Manage all support tickets submitted by users.', 'grt-ticket' ); ?></p>
	</div>

	<?php if ( empty( $tickets ) ) : ?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'No tickets found.', 'grt-ticket' ); ?></p>
		</div>
	<?php else : ?>
		<table class="grt-tickets-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Title', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'User', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Category', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Priority', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Status', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Created', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'grt-ticket' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $tickets as $ticket ) : ?>
					<tr>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket->id ) ); ?>">
								<strong><?php echo esc_html( $ticket->title ); ?></strong>
							</a>
						</td>
						<td>
							<?php echo esc_html( $ticket->user_name ); ?><br>
							<small><?php echo esc_html( $ticket->user_email ); ?></small>
						</td>
						<td><?php echo esc_html( $ticket->category ); ?></td>
						<td>
							<?php 
							$priority_class = 'priority-medium';
							if ( isset( $ticket->priority ) ) {
								$priority_class = 'priority-' . $ticket->priority;
								echo '<span class="grt-ticket-priority ' . esc_attr( $priority_class ) . '">' . esc_html( ucfirst( $ticket->priority ) ) . '</span>';
							} else {
								echo '<span class="grt-ticket-priority priority-medium">' . esc_html__( 'Medium', 'grt-ticket' ) . '</span>';
							}
							?>
						</td>
						<td>
							<span class="grt-ticket-status status-<?php echo esc_attr( $ticket->status ); ?>">
								<?php echo esc_html( ucfirst( $ticket->status ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $ticket->created_at ) ) ); ?></td>
						<td class="grt-ticket-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket->id ) ); ?>" class="button button-primary">
								<?php esc_html_e( 'View Chat', 'grt-ticket' ); ?>
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