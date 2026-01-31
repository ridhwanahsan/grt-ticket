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

		<!-- Tabs -->
		<div class="grt-sidebar-tabs">
			<button class="grt-tab-btn active" data-tab="tickets"><?php esc_html_e( 'Tickets', 'grt-ticket' ); ?></button>
			<button class="grt-tab-btn" data-tab="profile"><?php esc_html_e( 'Profile', 'grt-ticket' ); ?></button>
		</div>

		<!-- Tickets Tab Content -->
		<div id="grt-tab-tickets" class="grt-tab-content active">
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

		<!-- Profile Tab Content -->
		<div id="grt-tab-profile" class="grt-tab-content">
			<?php if ( is_user_logged_in() ) : ?>
				<?php $current_user = wp_get_current_user(); ?>
				<div class="grt-profile-section">
					<h3><?php esc_html_e( 'Your Profile', 'grt-ticket' ); ?></h3>
					
					<?php 
					$profile_image_id = get_user_meta( $current_user->ID, 'grt_profile_image', true );
					$profile_image_url = $profile_image_id ? wp_get_attachment_url( $profile_image_id ) : '';
					?>
					<div class="grt-profile-image-container">
						<div class="grt-profile-wrapper big" title="<?php esc_attr_e( 'Change Profile Picture', 'grt-ticket' ); ?>">
							<div class="grt-profile-icon big">
								<?php if ( $profile_image_url ) : ?>
									<img src="<?php echo esc_url( $profile_image_url ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>">
								<?php else : ?>
									<?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?>
								<?php endif; ?>
								<div class="grt-profile-overlay">
									<span class="dashicons dashicons-camera"></span>
								</div>
							</div>
							<input type="file" id="grt-profile-upload-tab" accept="image/*" style="display: none;">
						</div>
						<p class="grt-profile-upload-text"><?php esc_html_e( 'Click image to upload', 'grt-ticket' ); ?></p>
					</div>

					<div class="grt-profile-info">
						<p><strong><?php esc_html_e( 'Name:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $current_user->display_name ); ?></p>
						<p><strong><?php esc_html_e( 'Username:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $current_user->user_login ); ?></p>
						<p><strong><?php esc_html_e( 'Email:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $current_user->user_email ); ?></p>
					</div>
				</div>
			<?php else : ?>
				<div class="grt-profile-section">
					<p><?php esc_html_e( 'Please login to view your profile.', 'grt-ticket' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="grt-chat-main">
		<div class="grt-chat-header">
			<button type="button" id="grt-sidebar-toggle" class="grt-sidebar-toggle" title="<?php esc_attr_e( 'Toggle Sidebar', 'grt-ticket' ); ?>">
				<span class="grt-icon-menu">☰</span>
			</button>
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
						<?php 
						$profile_image_id = get_user_meta( $current_user->ID, 'grt_profile_image', true );
						$profile_image_url = $profile_image_id ? wp_get_attachment_url( $profile_image_id ) : '';
						?>
						<div class="grt-profile-wrapper" title="<?php esc_attr_e( 'Change Profile Picture', 'grt-ticket' ); ?>">
							<div class="grt-profile-icon">
								<?php if ( $profile_image_url ) : ?>
									<img src="<?php echo esc_url( $profile_image_url ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>">
								<?php else : ?>
									<?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?>
								<?php endif; ?>
								<div class="grt-profile-overlay">
									<span class="dashicons dashicons-camera"></span>
								</div>
							</div>
							<input type="file" id="grt-profile-upload" accept="image/*" style="display: none;">
							<span class="grt-profile-name"><?php echo esc_html( $current_user->display_name ); ?></span>
						</div>
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
								<?php 
								$file_ext = pathinfo( $message->attachment_url, PATHINFO_EXTENSION );
								if ( strtolower( $file_ext ) === 'pdf' ) : ?>
									<div class="grt-pdf-attachment">
										<span class="dashicons dashicons-pdf" style="font-size: 40px; width: 40px; height: 40px; color: #d00000;"></span>
										<span><?php echo esc_html( basename( $message->attachment_url ) ); ?></span>
									</div>
								<?php else : ?>
									<img src="<?php echo esc_url( $message->attachment_url ); ?>" alt="<?php esc_attr_e( 'Attachment', 'grt-ticket' ); ?>" style="max-width: 300px; border-radius: 8px;">
								<?php endif; ?>
							</a>
						</div>
					<?php endif; ?>
					<div class="grt-message-time"><?php echo esc_html( human_time_diff( strtotime( $message->created_at ), current_time( 'timestamp' ) ) . ' ago' ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $is_solved ) : ?>
			<div class="grt-chat-solved-notice">
				<?php esc_html_e( '✓ This ticket has been marked as solved.', 'grt-ticket' ); ?>
				
				<?php if ( isset( $ticket->rating ) && $ticket->rating > 0 ) : ?>
					<div class="grt-rating-display">
						<p><strong><?php esc_html_e( 'Your Rating:', 'grt-ticket' ); ?></strong></p>
						<div class="grt-stars readonly">
							<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
								<span class="grt-star <?php echo $i <= $ticket->rating ? 'selected' : ''; ?>">★</span>
							<?php endfor; ?>
						</div>
						<?php if ( ! empty( $ticket->rating_feedback ) ) : ?>
							<p class="grt-feedback-text">"<?php echo esc_html( $ticket->rating_feedback ); ?>"</p>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<div class="grt-rating-section">
						<h4><?php esc_html_e( 'Rate our support', 'grt-ticket' ); ?></h4>
						<div class="grt-rating-stars">
							<span class="grt-star" data-value="1">★</span>
							<span class="grt-star" data-value="2">★</span>
							<span class="grt-star" data-value="3">★</span>
							<span class="grt-star" data-value="4">★</span>
							<span class="grt-star" data-value="5">★</span>
						</div>
						<input type="hidden" id="grt-rating-value" value="0">
						<textarea id="grt-rating-feedback" placeholder="<?php esc_attr_e( 'Optional feedback...', 'grt-ticket' ); ?>"></textarea>
						<button type="button" id="grt-submit-rating" class="grt-btn-primary" style="margin-top: 10px;"><?php esc_html_e( 'Submit Rating', 'grt-ticket' ); ?></button>
					</div>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="grt-chat-input-container">
				<div id="grt-attachment-preview" class="grt-attachment-preview" style="display: none;">
					<div id="grt-preview-content"></div>
					<button type="button" id="grt-remove-attachment" class="grt-remove-attachment">×</button>
				</div>

				<div class="grt-chat-input-bar">
					<input type="file" id="grt-chat-attachment" accept="image/*,application/pdf" style="display: none;">
					<button type="button" id="grt-chat-attach-btn" class="grt-chat-attach-btn" title="<?php esc_attr_e( 'Attach File', 'grt-ticket' ); ?>">
						<span class="dashicons dashicons-paperclip"></span>
					</button>
					<textarea id="grt-chat-input" class="grt-chat-input" placeholder="<?php esc_attr_e( 'Type your message...', 'grt-ticket' ); ?>"></textarea>
					<button type="button" id="grt-chat-send-btn" class="grt-chat-send-btn" title="<?php esc_attr_e( 'Send', 'grt-ticket' ); ?>">
						<?php esc_html_e( 'Send', 'grt-ticket' ); ?>
					</button>
				</div>
			</div>
		<?php endif; ?>

		<input type="hidden" id="grt-ticket-id" value="<?php echo esc_attr( $ticket->id ); ?>">
		<input type="hidden" id="grt-user-email" value="<?php echo esc_attr( $user_email ); ?>">
	</div>
</div>
