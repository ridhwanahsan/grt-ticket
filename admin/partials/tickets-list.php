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

$current_user_id = get_current_user_id();
$base_url = admin_url( 'admin.php?page=grt-ticket-list' );

// Counts
$count_all = GRT_Ticket_Database::count_tickets();
$count_assigned = GRT_Ticket_Database::count_tickets( array( 'assigned_agent_id' => $current_user_id ) );
$count_open = GRT_Ticket_Database::count_tickets( array( 'status' => 'open' ) );
$count_solved = GRT_Ticket_Database::count_tickets( array( 'status' => 'solved' ) );
$count_closed = GRT_Ticket_Database::count_tickets( array( 'status' => 'closed' ) );

// Current filter
$current_filter = 'all';
if ( isset( $_GET['assigned_to_me'] ) ) {
	$current_filter = 'assigned';
} elseif ( isset( $_GET['status'] ) ) {
	$current_filter = $_GET['status'];
}

$agents = get_users( array( 'role__in' => array( 'administrator', 'editor' ) ) );
?>

<div class="wrap grt-ticket-wrap">
	<div class="grt-ticket-header">
		<h1><?php esc_html_e( 'Support Tickets', 'grt-ticket' ); ?></h1>
		<p><?php esc_html_e( 'Manage all support tickets submitted by users.', 'grt-ticket' ); ?></p>
	</div>

	<ul class="subsubsub" style="margin-bottom: 15px;">
		<li class="all">
			<a href="<?php echo esc_url( $base_url ); ?>" class="<?php echo ( 'all' === $current_filter ) ? 'current' : ''; ?>">
				<?php esc_html_e( 'All', 'grt-ticket' ); ?> <span class="count">(<?php echo (int) $count_all; ?>)</span>
			</a> |
		</li>
		<li class="assigned">
			<a href="<?php echo esc_url( add_query_arg( 'assigned_to_me', '1', $base_url ) ); ?>" class="<?php echo ( 'assigned' === $current_filter ) ? 'current' : ''; ?>">
				<?php esc_html_e( 'Assigned to Me', 'grt-ticket' ); ?> <span class="count">(<?php echo (int) $count_assigned; ?>)</span>
			</a> |
		</li>
		<li class="open">
			<a href="<?php echo esc_url( add_query_arg( 'status', 'open', $base_url ) ); ?>" class="<?php echo ( 'open' === $current_filter ) ? 'current' : ''; ?>">
				<?php esc_html_e( 'Open', 'grt-ticket' ); ?> <span class="count">(<?php echo (int) $count_open; ?>)</span>
			</a> |
		</li>
		<li class="solved">
			<a href="<?php echo esc_url( add_query_arg( 'status', 'solved', $base_url ) ); ?>" class="<?php echo ( 'solved' === $current_filter ) ? 'current' : ''; ?>">
				<?php esc_html_e( 'Solved', 'grt-ticket' ); ?> <span class="count">(<?php echo (int) $count_solved; ?>)</span>
			</a> |
		</li>
		<li class="closed">
			<a href="<?php echo esc_url( add_query_arg( 'status', 'closed', $base_url ) ); ?>" class="<?php echo ( 'closed' === $current_filter ) ? 'current' : ''; ?>">
				<?php esc_html_e( 'Closed', 'grt-ticket' ); ?> <span class="count">(<?php echo (int) $count_closed; ?>)</span>
			</a>
		</li>
	</ul>

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
					<th><?php esc_html_e( 'Assigned', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Status', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Rating', 'grt-ticket' ); ?></th>
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
							<select class="grt-assign-agent-list" data-ticket-id="<?php echo esc_attr( $ticket->id ); ?>" style="max-width: 150px;">
								<option value="0"><?php esc_html_e( 'Unassigned', 'grt-ticket' ); ?></option>
								<?php 
								$current_assigned = isset( $ticket->assigned_agent_id ) ? $ticket->assigned_agent_id : 0;
								foreach ( $agents as $agent ) {
									$selected = ( $current_assigned == $agent->ID ) ? 'selected' : '';
									echo '<option value="' . esc_attr( $agent->ID ) . '" ' . $selected . '>' . esc_html( $agent->display_name ) . '</option>';
								}
								?>
							</select>
						</td>
						<td>
							<span class="grt-ticket-status status-<?php echo esc_attr( $ticket->status ); ?>">
								<?php echo esc_html( ucfirst( $ticket->status ) ); ?>
							</span>
						</td>
						<td>
							<?php if ( isset( $ticket->rating ) && $ticket->rating > 0 ) : ?>
								<span title="<?php echo esc_attr( $ticket->rating_feedback ); ?>"><?php echo str_repeat( '★', $ticket->rating ); ?></span>
							<?php else : ?>
								<span style="color: #ddd;">-</span>
							<?php endif; ?>
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