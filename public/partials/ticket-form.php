<?php
/**
 * Public ticket submission form
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/public/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
?>

<div class="grt-ticket-container">
	<div class="grt-ticket-inner">
		
		<?php if ( $is_logged_in ) : ?>
			<!-- Profile Section for Logged In Users -->
			<div class="grt-profile-section">
				<div class="grt-profile-info">
					<h3><?php esc_html_e( 'Your Profile', 'grt-ticket' ); ?></h3>
					<p><strong><?php esc_html_e( 'Name:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $user_name ); ?></p>
					<p><strong><?php esc_html_e( 'Email:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $user_email ); ?></p>
					<a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>" class="grt-logout-link"><?php esc_html_e( 'Logout', 'grt-ticket' ); ?></a>
				</div>
			</div>
		<?php else : ?>
			<!-- Login Prompt for Guests -->
			<div class="grt-login-prompt">
				<p><?php esc_html_e( 'Already have an account?', 'grt-ticket' ); ?> <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Login to Your Profile', 'grt-ticket' ); ?></a></p>
			</div>
		<?php endif; ?>
		
		<div class="grt-ticket-header">
			<h2><?php esc_html_e( 'Submit a Support Ticket', 'grt-ticket' ); ?></h2>
			<p><?php esc_html_e( 'Select an issue category to get started', 'grt-ticket' ); ?></p>
		</div>

		<div class="grt-category-selector">
			<label><?php esc_html_e( 'What can we help you with?', 'grt-ticket' ); ?></label>
			
			<div class="grt-custom-dropdown" id="grt-category-dropdown">
				<div class="grt-dropdown-selected">
					<span class="grt-selected-text"><?php esc_html_e( 'Select an issue category', 'grt-ticket' ); ?></span>
					<span class="grt-dropdown-arrow">▼</span>
				</div>
				<div class="grt-dropdown-options">
					<?php foreach ( $categories as $cat ) : ?>
						<div class="grt-dropdown-item" data-value="<?php echo esc_attr( $cat['name'] ); ?>">
							<div class="grt-item-icon">
								<?php if ( ! empty( $cat['image'] ) ) : ?>
									<img src="<?php echo esc_url( $cat['image'] ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>">
								<?php else: ?>
									<span class="grt-item-icon-placeholder">?</span>
								<?php endif; ?>
							</div>
							<span class="grt-item-name"><?php echo esc_html( $cat['name'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<form id="grt-ticket-submit-form" class="grt-ticket-form">
			<input type="hidden" id="grt-selected-category" name="category" value="">

			<div class="grt-form-group required">
				<label for="grt-user-name"><?php esc_html_e( 'Your Name', 'grt-ticket' ); ?></label>
				<input type="text" id="grt-user-name" name="user_name" value="<?php echo esc_attr( $user_name ); ?>" <?php echo $is_logged_in ? 'readonly' : ''; ?> required>
			</div>

			<div class="grt-form-group required">
				<label for="grt-user-email"><?php esc_html_e( 'Your Email', 'grt-ticket' ); ?></label>
				<input type="email" id="grt-user-email" name="user_email" value="<?php echo esc_attr( $user_email ); ?>" <?php echo $is_logged_in ? 'readonly' : ''; ?> required>
			</div>

			<?php if ( ! $is_logged_in ) : ?>
				<!-- Password field for guests only -->
				<div class="grt-form-group">
					<label for="grt-user-password"><?php esc_html_e( 'Set a Password (Optional)', 'grt-ticket' ); ?></label>
					<input type="password" id="grt-user-password" name="user_password" placeholder="<?php esc_attr_e( 'Leave empty to auto-generate', 'grt-ticket' ); ?>">
					<small class="grt-form-help"><?php esc_html_e( 'Create a password to access your tickets later. If left empty, we will email you one.', 'grt-ticket' ); ?></small>
				</div>
			<?php endif; ?>

			<div class="grt-form-group required">
				<label for="grt-theme-name"><?php esc_html_e( 'Theme / Template Name', 'grt-ticket' ); ?></label>
				<input type="text" id="grt-theme-name" name="theme_name" required>
			</div>

			<div class="grt-form-group required">
				<label for="grt-license-code"><?php esc_html_e( 'License Code', 'grt-ticket' ); ?></label>
				<input type="text" id="grt-license-code" name="license_code" required>
			</div>

			<div class="grt-form-group required">
				<label for="grt-issue-title"><?php esc_html_e( 'Issue Title', 'grt-ticket' ); ?></label>
				<input type="text" id="grt-issue-title" name="title" required>
			</div>

			<div class="grt-form-group required">
				<label for="grt-issue-priority"><?php esc_html_e( 'Priority', 'grt-ticket' ); ?></label>
				<select id="grt-issue-priority" name="priority" required>
					<option value="low"><?php esc_html_e( 'Low - General Question', 'grt-ticket' ); ?></option>
					<option value="medium" selected><?php esc_html_e( 'Medium - Normal Issue', 'grt-ticket' ); ?></option>
					<option value="high"><?php esc_html_e( 'High - Critical Issue', 'grt-ticket' ); ?></option>
				</select>
			</div>

			<div class="grt-form-group required">
				<label for="grt-issue-description"><?php esc_html_e( 'Describe Your Issue', 'grt-ticket' ); ?></label>
				<textarea id="grt-issue-description" name="description" required></textarea>
			</div>

			<button type="submit" id="grt-submit-btn" class="grt-submit-btn">
				<?php esc_html_e( 'Submit Ticket', 'grt-ticket' ); ?>
			</button>
		</form>
		
		<?php if ( $is_logged_in && ! empty( $recent_tickets ) ) : ?>
			<!-- Recent Tickets Section -->
			<div class="grt-recent-tickets-section">
				<h3><?php esc_html_e( 'Your Recent Tickets', 'grt-ticket' ); ?></h3>
				<div class="grt-recent-tickets-list">
					<?php foreach ( $recent_tickets as $recent_ticket ) : ?>
						<?php
						$base_url = get_permalink();
						$base_url = rtrim( $base_url, '/' );
						$ticket_url = $base_url . '/ticket/' . $recent_ticket->id . '/';
						?>
						<div class="grt-recent-ticket-item">
							<a href="<?php echo esc_url( $ticket_url ); ?>">
								<h4><?php echo esc_html( $recent_ticket->title ); ?></h4>
								<p>
									<span class="grt-ticket-category"><?php echo esc_html( $recent_ticket->category ); ?></span>
									<span class="grt-ticket-status status-<?php echo esc_attr( $recent_ticket->status ); ?>">
										<?php echo esc_html( ucfirst( $recent_ticket->status ) ); ?>
									</span>
									<span class="grt-ticket-date"><?php echo esc_html( human_time_diff( strtotime( $recent_ticket->created_at ), current_time( 'timestamp' ) ) . ' ago' ); ?></span>
								</p>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
