<?php
/**
 * Admin chat interface
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/admin/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $ticket ) {
	echo '<div class="wrap"><p>' . esc_html__( 'Ticket not found.', 'grt-ticket' ) . '</p></div>';
	return;
}

$is_solved = 'solved' === $ticket->status || 'closed' === $ticket->status;
?>

<div class="grt-chat-container">
	<div class="grt-chat-sidebar">
		<div class="grt-chat-sidebar-header">
			<h2><?php esc_html_e( 'Recent Tickets', 'grt-ticket' ); ?></h2>
		</div>
		<div class="grt-chat-tickets-list">
			<?php
			$recent_tickets = GRT_Ticket_Database::get_tickets( array( 'limit' => 20 ) );
			foreach ( $recent_tickets as $recent_ticket ) :
				$active_class = $recent_ticket->id === $ticket->id ? 'active' : '';
				?>
				<div class="grt-chat-ticket-item <?php echo esc_attr( $active_class ); ?>" onclick="location.href='<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-chat&ticket_id=' . $recent_ticket->id ) ); ?>'">
					<h4><?php echo esc_html( $recent_ticket->title ); ?></h4>
					<p><?php echo esc_html( $recent_ticket->user_name ); ?> - <span class="grt-ticket-status status-<?php echo esc_attr( $recent_ticket->status ); ?>"><?php echo esc_html( ucfirst( $recent_ticket->status ) ); ?></span></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="grt-chat-main">
		<div class="grt-chat-header">
			<div>
				<h2><?php echo esc_html( $ticket->title ); ?></h2>
			</div>
			<div class="grt-chat-info">
				<span><strong><?php esc_html_e( 'User:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $ticket->user_name ); ?></span>
				<span><strong><?php esc_html_e( 'Email:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $ticket->user_email ); ?></span>
				<span><strong><?php esc_html_e( 'Theme:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $ticket->theme_name ); ?></span>
				<?php if ( isset( $ticket->priority ) ) : ?>
					<span><strong><?php esc_html_e( 'Priority:', 'grt-ticket' ); ?></strong> <span class="grt-ticket-priority priority-<?php echo esc_attr( $ticket->priority ); ?>"><?php echo esc_html( ucfirst( $ticket->priority ) ); ?></span></span>
				<?php endif; ?>
				<span><strong><?php esc_html_e( 'Status:', 'grt-ticket' ); ?></strong> <span class="grt-ticket-status status-<?php echo esc_attr( $ticket->status ); ?>"><?php echo esc_html( ucfirst( $ticket->status ) ); ?></span></span>
			</div>
		</div>

		<div class="grt-chat-messages">
			<?php foreach ( $messages as $message ) : ?>
				<div class="grt-chat-message <?php echo esc_attr( $message->sender_type ); ?>" data-message-id="<?php echo esc_attr( $message->id ); ?>">
					<div class="grt-message-sender"><?php echo esc_html( $message->sender_name ); ?></div>
					<?php if ( ! empty( $message->message ) ) : ?>
						<div class="grt-message-bubble"><?php echo wp_kses_post( nl2br( $message->message ) ); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $message->attachment_url ) ) : ?>
						<div class="grt-message-attachment">
							<a href="<?php echo esc_url( $message->attachment_url ); ?>" target="_blank">
								<img src="<?php echo esc_url( $message->attachment_url ); ?>" alt="<?php esc_attr_e( 'Attachment', 'grt-ticket' ); ?>" style="max-width: 300px; border-radius: 8px;">
							</a>
						</div>
					<?php endif; ?>
					<div class="grt-message-time"><?php echo esc_html( human_time_diff( strtotime( $message->created_at ), current_time( 'timestamp' ) ) . ' ago' ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $is_solved ) : ?>
			<div class="grt-chat-solved-notice">
				<?php esc_html_e( '✓ This ticket has been marked as solved. No further messages can be sent.', 'grt-ticket' ); ?>
			</div>
		<?php else : ?>
			<div class="grt-chat-input-container">
				<div class="grt-chat-input-wrapper">
					<input type="file" id="grt-chat-attachment" accept="image/*" style="display: none;">
					<button type="button" id="grt-chat-attach-btn" class="grt-chat-attach-btn" title="<?php esc_attr_e( 'Attach Image', 'grt-ticket' ); ?>">📎</button>
					<div id="grt-attachment-preview" class="grt-attachment-preview" style="display: none;">
						<img id="grt-preview-image" src="" alt="Preview">
						<button type="button" id="grt-remove-attachment" class="grt-remove-attachment">×</button>
					</div>
					<textarea id="grt-chat-input" class="grt-chat-input" placeholder="<?php esc_attr_e( 'Type your message...', 'grt-ticket' ); ?>"></textarea>
					<button type="button" id="grt-chat-send-btn" class="grt-chat-send-btn"><?php esc_html_e( 'Send', 'grt-ticket' ); ?></button>
					<button type="button" id="grt-chat-solve-btn" class="grt-chat-solve-btn"><?php esc_html_e( 'Mark as Solved', 'grt-ticket' ); ?></button>
				</div>
			</div>
		<?php endif; ?>

		<input type="hidden" id="grt-ticket-id" value="<?php echo esc_attr( $ticket->id ); ?>">
	</div>
</div>
