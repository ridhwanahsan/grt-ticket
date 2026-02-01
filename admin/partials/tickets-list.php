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

// Base args from filters
$filter_args = array();
if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
	$filter_args['search'] = sanitize_text_field( $_GET['s'] );
	$base_url = add_query_arg( 's', $filter_args['search'], $base_url );
}
if ( isset( $_GET['agent_id'] ) && ! empty( $_GET['agent_id'] ) ) {
	$filter_args['assigned_agent_id'] = (int) $_GET['agent_id'];
	$base_url = add_query_arg( 'agent_id', $filter_args['assigned_agent_id'], $base_url );
}
if ( isset( $_GET['filter_date'] ) && ! empty( $_GET['filter_date'] ) ) {
	$filter_args['date'] = sanitize_text_field( $_GET['filter_date'] );
	$base_url = add_query_arg( 'filter_date', $filter_args['date'], $base_url );
}

// Counts
$count_all = GRT_Ticket_Database::count_tickets( $filter_args );

// Assigned count (Date filter applies, Agent filter overridden by current user)
$assigned_args = $filter_args;
$assigned_args['assigned_agent_id'] = $current_user_id;
$count_assigned = GRT_Ticket_Database::count_tickets( $assigned_args );

// Status counts
$open_args = $filter_args;
$open_args['status'] = 'open';
$count_open = GRT_Ticket_Database::count_tickets( $open_args );

$solved_args = $filter_args;
$solved_args['status'] = 'solved';
$count_solved = GRT_Ticket_Database::count_tickets( $solved_args );

$closed_args = $filter_args;
$closed_args['status'] = 'closed';
$count_closed = GRT_Ticket_Database::count_tickets( $closed_args );

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
			<a href="<?php echo esc_url( add_query_arg( 'assigned_to_me', '1', remove_query_arg( 'agent_id', $base_url ) ) ); ?>" class="<?php echo ( 'assigned' === $current_filter ) ? 'current' : ''; ?>">
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

	<form method="get" action="" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
		<input type="hidden" name="page" value="grt-ticket-list">
		<input type="search" name="s" value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Search by Name or ID', 'grt-ticket' ); ?>" style="width: 200px;">
		<?php if ( isset( $_GET['status'] ) ) : ?>
			<input type="hidden" name="status" value="<?php echo esc_attr( $_GET['status'] ); ?>">
		<?php endif; ?>
		<?php if ( isset( $_GET['assigned_to_me'] ) ) : ?>
			<input type="hidden" name="assigned_to_me" value="<?php echo esc_attr( $_GET['assigned_to_me'] ); ?>">
		<?php endif; ?>
		
		<select name="agent_id" style="width: 200px;">
			<option value=""><?php esc_html_e( 'All Agents', 'grt-ticket' ); ?></option>
			<?php foreach ( $agents as $agent ) : ?>
				<option value="<?php echo esc_attr( $agent->ID ); ?>" <?php selected( isset( $_GET['agent_id'] ) ? $_GET['agent_id'] : '', $agent->ID ); ?>>
					<?php echo esc_html( $agent->display_name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		
		<input type="date" name="filter_date" value="<?php echo isset( $_GET['filter_date'] ) ? esc_attr( $_GET['filter_date'] ) : ''; ?>">
		
		<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'grt-ticket' ); ?>">
		
		<?php if ( ( isset( $_GET['agent_id'] ) && '' !== $_GET['agent_id'] ) || ( isset( $_GET['filter_date'] ) && '' !== $_GET['filter_date'] ) || ( isset( $_GET['s'] ) && '' !== $_GET['s'] ) ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-list' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'grt-ticket' ); ?></a>
		<?php endif; ?>
	</form>

	<?php if ( empty( $tickets ) ) : ?>
		<div class="notice notice-info">
			<p><?php esc_html_e( 'No tickets found.', 'grt-ticket' ); ?></p>
		</div>
	<?php else : ?>
		<table class="grt-tickets-table">
			<thead>
				<tr>
					<th class="check-column"><input type="checkbox" id="cb-select-all-1"></th>
					<th><?php esc_html_e( 'ID', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Requester', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Priority', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Subject', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Agent', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Status', 'grt-ticket' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'grt-ticket' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $tickets as $ticket ) : 
					// Get User Avatar
					$user_avatar = get_avatar_url( $ticket->user_email, array( 'size' => 40 ) );
					
					// Check for custom profile image
					if ( ! empty( $ticket->user_id ) ) {
						$custom_avatar_id = get_user_meta( $ticket->user_id, 'grt_profile_image', true );
						if ( $custom_avatar_id ) {
							$custom_avatar_src = wp_get_attachment_image_src( $custom_avatar_id, 'thumbnail' );
							if ( $custom_avatar_src ) {
								$user_avatar = $custom_avatar_src[0];
							}
						}
					}

					// Determine display names: if theme_name exists, use it as main, user_name as sub. Else user_name as main.
					$main_name = ! empty( $ticket->theme_name ) ? $ticket->theme_name : $ticket->user_name;
					$sub_name = ! empty( $ticket->theme_name ) ? 'User: ' . $ticket->user_name : $ticket->user_email;
					
					// Priority Styles
					$priority_label = ucfirst( $ticket->priority );
					$priority_icon = 'dashicons-minus'; 
					$priority_class = 'priority-medium';
					
					if ( $ticket->priority === 'high' ) {
						$priority_icon = 'dashicons-arrow-up-alt';
						$priority_class = 'priority-high';
					} elseif ( $ticket->priority === 'low' ) {
						$priority_icon = 'dashicons-arrow-down-alt';
						$priority_class = 'priority-low';
					}
				?>
					<tr>
						<th class="check-column"><input type="checkbox" name="ticket[]" value="<?php echo esc_attr( $ticket->id ); ?>"></th>
						<td class="id-column"><strong>#<?php echo esc_html( $ticket->id ); ?></strong></td>
						<td>
							<div class="grt-requester-info">
								<img src="<?php echo esc_url( $user_avatar ); ?>" alt="" class="grt-user-avatar">
								<div class="grt-user-details">
									<span class="grt-user-name"><?php echo esc_html( $main_name ); ?></span>
									<span class="grt-user-sub"><?php echo esc_html( $sub_name ); ?></span>
								</div>
							</div>
						</td>
						<td>
							<div class="grt-priority-info <?php echo esc_attr( $priority_class ); ?>">
								<span class="dashicons <?php echo $priority_icon; ?>"></span>
								<span><?php echo esc_html( $priority_label ); ?></span>
							</div>
						</td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket->id ) ); ?>" class="grt-ticket-subject">
								<?php echo esc_html( $ticket->title ); ?>
							</a>
						</td>
						<td>
							<select class="grt-agent-select" data-ticket-id="<?php echo esc_attr( $ticket->id ); ?>">
								<option value="0"><?php esc_html_e( 'Unassigned', 'grt-ticket' ); ?></option>
								<?php foreach ( $agents as $agent ) : ?>
									<option value="<?php echo esc_attr( $agent->ID ); ?>" <?php selected( $ticket->assigned_agent_id, $agent->ID ); ?>>
										<?php echo esc_html( $agent->display_name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<span class="grt-ticket-status status-<?php echo esc_attr( $ticket->status ); ?>">
								<?php echo esc_html( ucfirst( $ticket->status ) ); ?>
							</span>
						</td>
						<td class="grt-ticket-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $ticket->id ) ); ?>" class="button button-small" title="<?php esc_attr_e( 'View', 'grt-ticket' ); ?>">
								<span class="dashicons dashicons-visibility"></span>
							</a>
							<button type="button" class="button button-small grt-delete-ticket" data-ticket-id="<?php echo esc_attr( $ticket->id ); ?>" title="<?php esc_attr_e( 'Delete', 'grt-ticket' ); ?>">
								<span class="dashicons dashicons-trash"></span>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Assign Agent via Select
    $('.grt-agent-select').on('change', function() {
        var $select = $(this);
        var ticketId = $select.data('ticket-id');
        var agentId = $select.val();
        
        $select.prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'grt_ticket_assign_agent',
                ticket_id: ticketId,
                agent_id: agentId,
                nonce: '<?php echo wp_create_nonce( "grt_ticket_nonce" ); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Optional: Show a small success indicator or notification
                } else {
                    alert(response.data.message || 'Failed to assign agent');
                    // Revert selection if needed, but for now we keep it simple
                }
            },
            error: function() {
                alert('Network error occurred');
            },
            complete: function() {
                $select.prop('disabled', false);
            }
        });
    });

    // Delete Ticket
    $('.grt-delete-ticket').on('click', function() {
        if (!confirm('<?php esc_html_e( "Are you sure you want to delete this ticket?", "grt-ticket" ); ?>')) {
            return;
        }

        var $button = $(this);
        var ticketId = $button.data('ticket-id');
        var $row = $button.closest('tr');

        $button.prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'grt_ticket_delete',
                ticket_id: ticketId,
                nonce: '<?php echo wp_create_nonce( "grt_ticket_nonce" ); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data.message || 'Failed to delete ticket');
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                alert('Network error occurred');
                $button.prop('disabled', false);
            }
        });
    });

    // Bulk Actions (Select All)
    $('#cb-select-all-1').on('change', function() {
        $('input[name="ticket[]"]').prop('checked', $(this).is(':checked'));
    });
});
</script>