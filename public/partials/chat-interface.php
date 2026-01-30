<?php
/**
 * Public chat interface
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/public/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_solved = 'solved' === $ticket->status || 'closed' === $ticket->status;
$admin_name = get_option( 'grt_ticket_admin_name', 'Support Team' );

// Direct Contact Options
$support_phone = get_option( 'grt_ticket_support_phone', '' );
$enable_direct_call = get_option( 'grt_ticket_enable_direct_call', 0 );
$enable_direct_sms = get_option( 'grt_ticket_enable_direct_sms', 0 );
$sms_body = get_option( 'grt_ticket_sms_body', 'Hello, I need help with my ticket.' );
?>

<div class="grt-chat-container">
	<div class="grt-chat-sidebar">
		<div class="grt-chat-sidebar-header">
			<h3><?php esc_html_e( 'Your Tickets', 'grt-ticket' ); ?></h3>
			<a href="<?php echo esc_url( get_permalink() ); ?>" class="grt-new-ticket-btn" title="<?php esc_attr_e( 'Create New Ticket', 'grt-ticket' ); ?>">
				<span class="grt-plus-icon">+</span> <?php esc_html_e( 'New Ticket', 'grt-ticket' ); ?>
			</a>
		</div>

		<?php if ( ( $enable_direct_call || $enable_direct_sms ) && ! empty( $support_phone ) ) : ?>
		<div class="grt-direct-contact-actions">
			<?php if ( $enable_direct_call ) : ?>
				<a href="tel:<?php echo esc_attr( $support_phone ); ?>" class="grt-action-btn grt-call-btn">
					<span class="grt-icon">📞</span> <?php esc_html_e( 'Call Us', 'grt-ticket' ); ?>
				</a>
			<?php endif; ?>
			
			<?php if ( $enable_direct_sms ) : ?>
				<a href="sms:<?php echo esc_attr( $support_phone ); ?>?body=<?php echo rawurlencode( $sms_body ); ?>" class="grt-action-btn grt-sms-btn">
					<span class="grt-icon">💬</span> <?php esc_html_e( 'SMS Us', 'grt-ticket' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="grt-chat-tickets-list">
			<?php foreach ( $user_tickets as $user_ticket ) : ?>
				<?php
				$active_class = $user_ticket->id === $ticket->id ? 'active' : '';
				// Professional URL structure
				// Check if permalinks are enabled (implied by requirement, but good to be safe using get_permalink)
				// We append /ticket/ID/ to the base page URL
				$base_url = get_permalink();
				$base_url = rtrim( $base_url, '/' );
				$ticket_url = $base_url . '/ticket/' . $user_ticket->id . '/';
				?>
				<a href="<?php echo esc_url( $ticket_url ); ?>" class="grt-chat-ticket-item <?php echo esc_attr( $active_class ); ?>">
					<h4><?php echo esc_html( $user_ticket->title ); ?></h4>
					<p>
						<span class="grt-ticket-status status-<?php echo esc_attr( $user_ticket->status ); ?>">
							<?php echo esc_html( ucfirst( $user_ticket->status ) ); ?>
						</span>
					</p>
				</a>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="grt-chat-main">
		<div class="grt-chat-header">
			<div class="grt-chat-header-content">
				<h3><?php echo esc_html( $ticket->title ); ?></h3>
				<p>
					<strong><?php esc_html_e( 'Category:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $ticket->category ); ?> | 
					<strong><?php esc_html_e( 'Status:', 'grt-ticket' ); ?></strong> 
					<span class="grt-ticket-status status-<?php echo esc_attr( $ticket->status ); ?>">
						<?php echo esc_html( ucfirst( $ticket->status ) ); ?>
					</span>
				</p>
			</div>
			<div class="grt-chat-header-right">
				<?php if ( ( $enable_direct_call || $enable_direct_sms ) && ! empty( $support_phone ) ) : ?>
					<div class="grt-direct-contact-header">
						<?php if ( $enable_direct_call ) : ?>
							<a href="tel:<?php echo esc_attr( $support_phone ); ?>" class="grt-action-btn grt-call-btn" title="<?php esc_attr_e( 'Call Us', 'grt-ticket' ); ?>">
								<span class="grt-icon">📞</span> <span class="grt-btn-text"><?php esc_html_e( 'Call', 'grt-ticket' ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( $enable_direct_sms ) : ?>
							<a href="sms:<?php echo esc_attr( $support_phone ); ?>?body=<?php echo rawurlencode( $sms_body ); ?>" class="grt-action-btn grt-sms-btn" title="<?php esc_attr_e( 'SMS Us', 'grt-ticket' ); ?>">
								<span class="grt-icon">💬</span> <span class="grt-btn-text"><?php esc_html_e( 'SMS', 'grt-ticket' ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( is_user_logged_in() ) : ?>
					<?php $current_user = wp_get_current_user(); ?>
					<div class="grt-chat-header-profile">
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="grt-profile-link" title="<?php esc_attr_e( 'Go to Profile', 'grt-ticket' ); ?>">
							<span class="grt-profile-icon"><?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?></span>
							<span class="grt-profile-name"><?php echo esc_html( $current_user->display_name ); ?></span>
						</a>
					</div>
				<?php endif; ?>
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
				<?php esc_html_e( '✓ This ticket has been marked as solved by our support team.', 'grt-ticket' ); ?>
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
				</div>
			</div>
		<?php endif; ?>

		<input type="hidden" id="grt-ticket-id" value="<?php echo esc_attr( $ticket->id ); ?>">
		<input type="hidden" id="grt-user-email" value="<?php echo esc_attr( $user_email ); ?>">
	</div>
</div>
