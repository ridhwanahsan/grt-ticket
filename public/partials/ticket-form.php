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

// Get Form Structure
$form_structure = get_option( 'grt_ticket_form_structure', array() );

// If structure is empty (first run), initialize with default fields + existing custom fields
if ( empty( $form_structure ) ) {
	$default_fields = array(
		array( 'id' => 'category', 'type' => 'select', 'label' => 'Category', 'required' => true, 'width' => '100', 'is_system' => true ),
		array( 'id' => 'user_name', 'type' => 'text', 'label' => 'Your Name', 'required' => true, 'width' => '50', 'is_system' => true ),
		array( 'id' => 'user_email', 'type' => 'email', 'label' => 'Your Email', 'required' => true, 'width' => '50', 'is_system' => true ),
		array( 'id' => 'theme_name', 'type' => 'text', 'label' => 'Theme / Template Name', 'required' => true, 'width' => '50', 'is_system' => true ),
		array( 'id' => 'license_code', 'type' => 'text', 'label' => 'License Code', 'required' => true, 'width' => '50', 'is_system' => true ),
		array( 'id' => 'title', 'type' => 'text', 'label' => 'Issue Title', 'required' => true, 'width' => '100', 'is_system' => true ),
		array( 'id' => 'priority', 'type' => 'select', 'label' => 'Priority', 'required' => true, 'width' => '100', 'is_system' => true ),
		array( 'id' => 'description', 'type' => 'textarea', 'label' => 'Describe Your Issue', 'required' => true, 'width' => '100', 'is_system' => true ),
	);

	$existing_custom_fields = get_option( 'grt_ticket_custom_fields', array() );
	if ( is_array( $existing_custom_fields ) ) {
		foreach ( $existing_custom_fields as &$field ) {
			$field['width'] = '100'; 
			$field['is_system'] = false;
		}
		$form_structure = array_merge( $default_fields, $existing_custom_fields );
	} else {
		$form_structure = $default_fields;
	}
}
?>

<div class="grt-ticket-container">
	<div class="grt-ticket-inner">
		
		<!-- Tabs Navigation -->
		<div class="grt-tabs-nav">
			<div class="grt-ticket-header">
					<h2><?php esc_html_e( 'Submit a Support Ticket', 'grt-ticket' ); ?></h2>
					<p><?php esc_html_e( 'Select an issue category to get started', 'grt-ticket' ); ?></p>
				</div>
			<button class="grt-tab-link active" data-tab="grt-tab-submit"><?php esc_html_e( 'Submit a Support Ticket', 'grt-ticket' ); ?></button>
			<?php if ( $is_logged_in ) : ?>
				<?php /* translators: %s: User name */ ?>
				<button class="grt-tab-link" data-tab="grt-tab-profile"><?php printf( esc_html__( 'Your Profile: %s', 'grt-ticket' ), esc_html( $user_name ) ); ?></button>
				<button class="grt-tab-link" data-tab="grt-tab-recent"><?php esc_html_e( 'Your Recent Tickets', 'grt-ticket' ); ?></button>
			<?php else : ?>
				<button class="grt-tab-link" data-tab="grt-tab-login"><?php esc_html_e( 'Login to Your Profile', 'grt-ticket' ); ?></button>
			<?php endif; ?>
		</div>

		<div class="grt-tabs-content-wrapper"> 
			<!-- Tab 1: Submit Ticket -->
			<div id="grt-tab-submit" class="grt-tab-content active"> 
				
				<form id="grt-ticket-submit-form" class="grt-ticket-form">
					
					<?php foreach ( $form_structure as $field ) : 
						$field_id = isset( $field['id'] ) ? esc_attr( $field['id'] ) : '';
						$field_type = isset( $field['type'] ) ? esc_attr( $field['type'] ) : 'text';
						$field_label = isset( $field['label'] ) ? esc_html( $field['label'] ) : '';
						$field_placeholder = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
						$field_width = isset( $field['width'] ) ? esc_attr( $field['width'] ) : '100';
						$is_required_attr = ! empty( $field['required'] ) ? 'required' : '';
						$required_class = ! empty( $field['required'] ) ? 'required' : '';
						$required_mark = ! empty( $field['required'] ) ? '<span class="required">*</span>' : '';
						
						// Handle System Fields
						if ( ! empty( $field['is_system'] ) ) {
							if ( $field_id === 'category' ) : ?>
								<div class="grt-category-selector grt-full-width" style="width: 100%; margin-bottom: 30px;">
									<label><?php echo esc_html( $field_label ); ?></label>
									<input type="hidden" id="grt-selected-category" name="category" value="">
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
							<?php elseif ( $field_id === 'user_name' ) : ?>
								<div class="grt-form-group <?php echo esc_attr( $required_class ); ?> grt-width-<?php echo esc_attr( $field_width ); ?>">
									<label for="grt-user-name"><?php echo esc_html( $field_label ) . ' ' . wp_kses_post( $required_mark ); ?></label>
									<input type="text" id="grt-user-name" name="user_name" value="<?php echo esc_attr( $user_name ); ?>" <?php echo $is_logged_in ? 'readonly' : ''; ?> <?php echo esc_attr( $is_required_attr ); ?>>
								</div>
							<?php elseif ( $field_id === 'user_email' ) : ?>
								<div class="grt-form-group <?php echo esc_attr( $required_class ); ?> grt-width-<?php echo esc_attr( $field_width ); ?>">
									<label for="grt-user-email"><?php echo esc_html( $field_label ) . ' ' . wp_kses_post( $required_mark ); ?></label>
									<input type="email" id="grt-user-email" name="user_email" value="<?php echo esc_attr( $user_email ); ?>" <?php echo $is_logged_in ? 'readonly' : ''; ?> <?php echo esc_attr( $is_required_attr ); ?>>
								</div>
								
							<?php elseif ( $field_id === 'theme_name' ) : ?>
								<div class="grt-form-group <?php echo esc_attr( $required_class ); ?> grt-width-<?php echo esc_attr( $field_width ); ?>">
									<label for="grt-theme-name"><?php echo esc_html( $field_label ) . ' ' . wp_kses_post( $required_mark ); ?></label>
									<input type="text" id="grt-theme-name" name="theme_name" <?php echo esc_attr( $is_required_attr ); ?>>
								</div>
							<?php elseif ( $field_id === 'license_code' ) : ?>
								<div class="grt-form-group <?php echo esc_attr( $required_class ); ?> grt-width-<?php echo esc_attr( $field_width ); ?>">
									<label for="grt-license-code"><?php echo esc_html( $field_label ) . ' ' . wp_kses_post( $required_mark ); ?></label>
									<input type="text" id="grt-license-code" name="license_code" <?php echo esc_attr( $is_required_attr ); ?>>
								</div>
							<?php elseif ( $field_id === 'title' ) : ?>
								<div class="grt-form-group <?php echo esc_attr( $required_class ); ?> grt-width-<?php echo esc_attr( $field_width ); ?>">
									<label for="grt-issue-title"><?php echo esc_html( $field_label ) . ' ' . wp_kses_post( $required_mark ); ?></label>
									<input type="text" id="grt-issue-title" name="title" <?php echo esc_attr( $is_required_attr ); ?>>
								</div>
							<?php elseif ( $field_id === 'priority' ) : ?>
								<div class="grt-form-group <?php echo esc_attr( $required_class ); ?> grt-width-<?php echo esc_attr( $field_width ); ?>">
									<label for="grt-issue-priority"><?php echo esc_html( $field_label ) . ' ' . wp_kses_post( $required_mark ); ?></label>
									<select id="grt-issue-priority" name="priority" <?php echo esc_attr( $is_required_attr ); ?>>
										<option value="low"><?php esc_html_e( 'Low - General Question', 'grt-ticket' ); ?></option>
										<option value="medium" selected><?php esc_html_e( 'Medium - Normal Issue', 'grt-ticket' ); ?></option>
										<option value="high"><?php esc_html_e( 'High - Critical Issue', 'grt-ticket' ); ?></option>
									</select>
								</div>
							<?php elseif ( $field_id === 'description' ) : ?>
								<div class="grt-form-group <?php echo esc_attr( $required_class ); ?> grt-width-<?php echo esc_attr( $field_width ); ?>">
									<label for="grt-issue-description"><?php echo esc_html( $field_label ) . ' ' . wp_kses_post( $required_mark ); ?></label>
									<textarea id="grt-issue-description" name="description" <?php echo esc_attr( $is_required_attr ); ?>></textarea>
								</div>
							<?php endif; 
						} else { 
							// Handle Custom Fields
							$options = isset( $field['options'] ) ? $field['options'] : '';
							?>
							<div class="grt-form-group <?php echo esc_attr( $required_class ); ?> grt-custom-field-<?php echo esc_attr( $field_type ); ?> grt-width-<?php echo esc_attr( $field_width ); ?>">
								<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $field_label ) . ' ' . wp_kses_post( $required_mark ); ?></label>
								
								<?php if ( $field_type === 'textarea' ) : ?>
									<textarea name="custom_fields[<?php echo esc_attr( $field_id ); ?>]" id="<?php echo esc_attr( $field_id ); ?>" placeholder="<?php echo esc_attr( $field_placeholder ); ?>" <?php echo esc_attr( $is_required_attr ); ?>></textarea>
								
								<?php elseif ( $field_type === 'select' ) : 
									$opts = explode( "\n", $options );
								?>
									<select name="custom_fields[<?php echo esc_attr( $field_id ); ?>]" id="<?php echo esc_attr( $field_id ); ?>" <?php echo esc_attr( $is_required_attr ); ?>>
										<option value=""><?php esc_html_e( 'Select option', 'grt-ticket' ); ?></option>
										<?php foreach ( $opts as $opt ) : 
											$opt = trim( $opt );
											if ( empty( $opt ) ) continue;
										?>
											<option value="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></option>
										<?php endforeach; ?>
									</select>

								<?php else : ?>
									<input type="<?php echo esc_attr( $field_type ); ?>" name="custom_fields[<?php echo esc_attr( $field_id ); ?>]" id="<?php echo esc_attr( $field_id ); ?>" placeholder="<?php echo esc_attr( $field_placeholder ); ?>" <?php echo esc_attr( $is_required_attr ); ?>>
								<?php endif; ?>
							</div>
						<?php } 
					endforeach; ?>

					<button type="submit" id="grt-submit-btn" class="grt-submit-btn grt-full-width">
						<?php esc_html_e( 'Submit Ticket', 'grt-ticket' ); ?>
					</button>
				</form>
			</div>

			<?php if ( $is_logged_in ) : ?>
				<!-- Tab 2: Profile -->
				<div id="grt-tab-profile" class="grt-tab-content">
					<div class="grt-profile-section">
						<div class="grt-profile-info">
							<h3><?php esc_html_e( 'Your Profile', 'grt-ticket' ); ?></h3>
							<p><strong><?php esc_html_e( 'Name:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $user_name ); ?></p>
							<p><strong><?php esc_html_e( 'Username:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $current_user->user_login ); ?></p>
							<p><strong><?php esc_html_e( 'Email:', 'grt-ticket' ); ?></strong> <?php echo esc_html( $user_email ); ?></p>
							<a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>" class="grt-logout-link"><?php esc_html_e( 'Logout', 'grt-ticket' ); ?></a>
						</div>
					</div>
				</div>

				<!-- Tab 3: Recent Tickets -->
				<div id="grt-tab-recent" class="grt-tab-content">
					<div class="grt-recent-tickets-section">
						<h3><?php esc_html_e( 'Your Recent Tickets', 'grt-ticket' ); ?></h3>
						
						<?php if ( ! empty( $recent_tickets ) ) : ?>
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
						<?php else : ?>
							<p><?php esc_html_e( 'No tickets found.', 'grt-ticket' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<!-- Tab 2: Login (For Guests) -->
				<div id="grt-tab-login" class="grt-tab-content">
					<div class="grt-login-section">
						<div class="grt-ticket-header">
							<h3><?php esc_html_e( 'Login to Your Profile', 'grt-ticket' ); ?></h3>
							<p><?php esc_html_e( 'Please login to view your tickets and profile.', 'grt-ticket' ); ?></p>
						</div>
						
						<div class="grt-login-form-wrapper" style="max-width: 400px; margin: 0 auto;">
							<?php 
							wp_login_form( array( 
								'redirect' => get_permalink(),
								'label_username' => __( 'Username or Email Address', 'grt-ticket' ),
								'label_log_in' => __( 'Login', 'grt-ticket' ),
							) ); 
							?>
							<p style="margin-top: 15px; text-align: center;">
								<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'grt-ticket' ); ?></a>
							</p>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>