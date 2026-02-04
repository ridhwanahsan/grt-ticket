<?php
/**
 * Admin Dashboard Page
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/admin/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap grt-dashboard-wrap">
	<div class="grt-header">
		<h1><?php esc_html_e( 'Support Dashboard', 'grt-ticket' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Overview of your support performance and statistics.', 'grt-ticket' ); ?></p>
	</div>

	<!-- General Overview -->
	<div class="grt-stats-grid">
		<!-- Total Agents -->
		<div class="grt-stat-card">
			<div class="stat-icon dashicons dashicons-businessperson"></div>
			<div class="stat-content">
				<h3><?php echo esc_html( $stats['total_agents'] ); ?></h3>
				<p><?php esc_html_e( 'Total Agents', 'grt-ticket' ); ?></p>
			</div>
		</div>

		<!-- Total Customers -->
		<div class="grt-stat-card">
			<div class="stat-icon dashicons dashicons-groups"></div>
			<div class="stat-content">
				<h3><?php echo esc_html( $stats['total_customers'] ); ?></h3>
				<p><?php esc_html_e( 'Total Customers', 'grt-ticket' ); ?></p>
			</div>
		</div>

		<!-- Departments -->
		<div class="grt-stat-card">
			<div class="stat-icon dashicons dashicons-category"></div>
			<div class="stat-content">
				<h3><?php echo esc_html( $stats['total_departments'] ); ?></h3>
				<p><?php esc_html_e( 'Departments', 'grt-ticket' ); ?></p>
			</div>
		</div>

		<!-- Projects -->
		<div class="grt-stat-card">
			<div class="stat-icon dashicons dashicons-portfolio"></div>
			<div class="stat-content">
				<h3><?php echo esc_html( $stats['total_projects'] ); ?></h3>
				<p><?php esc_html_e( 'Projects', 'grt-ticket' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Ticket Statistics -->
	<div class="grt-stats-grid">
		<!-- Total Tickets -->
		<div class="grt-stat-card">
			<div class="stat-icon dashicons dashicons-tickets-alt"></div>
			<div class="stat-content">
				<h3><?php echo esc_html( $stats['total_tickets'] ); ?></h3>
				<p><?php esc_html_e( 'Total Tickets', 'grt-ticket' ); ?></p>
			</div>
		</div>

		<!-- Open Tickets -->
		<div class="grt-stat-card warning">
			<div class="stat-icon dashicons dashicons-warning"></div>
			<div class="stat-content">
				<h3><?php echo esc_html( $stats['open_tickets'] ); ?></h3>
				<p><?php esc_html_e( 'Open Tickets', 'grt-ticket' ); ?></p>
			</div>
		</div>

		<!-- Tickets Today -->
		<div class="grt-stat-card info">
			<div class="stat-icon dashicons dashicons-calendar-alt"></div>
			<div class="stat-content">
				<h3><?php echo esc_html( $stats['tickets_today'] ); ?></h3>
				<p><?php esc_html_e( 'New Today', 'grt-ticket' ); ?></p>
			</div>
		</div>

		<!-- Avg Resolution Time -->
		<div class="grt-stat-card success">
			<div class="stat-icon dashicons dashicons-clock"></div>
			<div class="stat-content">
				<h3><?php echo esc_html( $stats['avg_resolution_time'] ); ?> <span class="unit">h</span></h3>
				<p><?php esc_html_e( 'Avg Resolution Time', 'grt-ticket' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Charts Row -->
	<div class="grt-dashboard-row" style="grid-template-columns: 1fr 1fr 1fr; margin-bottom: 20px;">
		
		<!-- Ticket Status Chart -->
		<div class="grt-dashboard-widget chart-widget">
			<h2><?php esc_html_e( 'Ticket Status', 'grt-ticket' ); ?></h2>
			<div class="chart-container">
				<?php 
				$total_status = $stats['open_tickets'] + $stats['solved_tickets'] + $stats['closed_tickets'];
				$statuses = array(
					'open'   => array( 'label' => __( 'Open', 'grt-ticket' ), 'count' => $stats['open_tickets'], 'class' => 'status-open' ),
					'solved' => array( 'label' => __( 'Solved', 'grt-ticket' ), 'count' => $stats['solved_tickets'], 'class' => 'status-solved' ),
					'closed' => array( 'label' => __( 'Closed', 'grt-ticket' ), 'count' => $stats['closed_tickets'], 'class' => 'status-closed' ),
				);
				
				foreach ( $statuses as $status ) :
					$percent = $total_status > 0 ? ( $status['count'] / $total_status ) * 100 : 0;
				?>
				<div class="chart-row <?php echo esc_attr( $status['class'] ); ?>">
					<div class="chart-label"><?php echo esc_html( $status['label'] ); ?></div>
					<div class="chart-bar-bg">
						<div class="chart-bar-fill" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
					</div>
					<div class="chart-value"><?php echo esc_html( $status['count'] ); ?></div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Ticket Priority Chart -->
		<div class="grt-dashboard-widget chart-widget">
			<h2><?php esc_html_e( 'Ticket Priority', 'grt-ticket' ); ?></h2>
			<div class="chart-container">
				<?php 
				$priorities = $stats['stats_by_priority'];
				$total_priority = array_sum( $priorities );
				// Sort by count desc if not already
				arsort($priorities);
				
				if ( empty( $priorities ) ) {
					echo '<p>' . esc_html__( 'No data available.', 'grt-ticket' ) . '</p>';
				} else {
					foreach ( $priorities as $priority => $count ) :
						$percent = $total_priority > 0 ? ( $count / $total_priority ) * 100 : 0;
						$priority_class = 'priority-' . strtolower( $priority );
					?>
					<div class="chart-row <?php echo esc_attr( $priority_class ); ?>">
						<div class="chart-label"><?php echo esc_html( ucfirst( $priority ) ); ?></div>
						<div class="chart-bar-bg">
							<div class="chart-bar-fill" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
						</div>
						<div class="chart-value"><?php echo esc_html( $count ); ?></div>
					</div>
					<?php endforeach;
				} ?>
			</div>
		</div>

		<!-- Tickets per Product -->
		<div class="grt-dashboard-widget chart-widget">
			<h2><?php esc_html_e( 'Tickets per Product', 'grt-ticket' ); ?></h2>
			<div class="chart-container">
				<?php 
				$products = $stats['stats_by_product'];
				$total_products = array_sum( $products );
				
				if ( empty( $products ) ) {
					echo '<p>' . esc_html__( 'No data available.', 'grt-ticket' ) . '</p>';
				} else {
					foreach ( $products as $product => $count ) :
						$percent = $total_products > 0 ? ( $count / $total_products ) * 100 : 0;
					?>
					<div class="chart-row">
						<div class="chart-label" title="<?php echo esc_attr( $product ); ?>"><?php echo esc_html( $product ); ?></div>
						<div class="chart-bar-bg">
							<div class="chart-bar-fill" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
						</div>
						<div class="chart-value"><?php echo esc_html( $count ); ?></div>
					</div>
					<?php endforeach;
				} ?>
			</div>
		</div>

	</div>

	<div class="grt-dashboard-row">
		<!-- Rating Overview -->
		<div class="grt-dashboard-widget rating-widget">
			<h2><?php esc_html_e( 'Customer Satisfaction', 'grt-ticket' ); ?></h2>
			<div class="rating-summary">
				<div class="big-rating">
					<span class="score"><?php echo esc_html( $stats['avg_rating'] ); ?></span>
					<span class="out-of">/ 5</span>
					<div class="stars">
						<?php
						$rating = round( $stats['avg_rating'] );
						for ( $i = 1; $i <= 5; $i++ ) {
							echo '<span class="dashicons dashicons-star-' . ( $i <= $rating ? 'filled' : 'empty' ) . '"></span>';
						}
						?>
					</div>
				</div>
				<div class="rating-breakdown">
					<?php foreach ( $stats['rating_distribution'] as $stars => $count ) : ?>
						<div class="rating-bar-row">
							<span class="star-label"><?php echo esc_html( $stars ); ?> ★</span>
							<div class="bar-container">
								<?php
								$total_rated = array_sum( $stats['rating_distribution'] );
								$percent = $total_rated > 0 ? ( $count / $total_rated ) * 100 : 0;
								?>
								<div class="bar" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
							</div>
							<span class="count"><?php echo esc_html( $count ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="grt-dashboard-widget actions-widget">
			<h2><?php esc_html_e( 'Quick Actions', 'grt-ticket' ); ?></h2>
			<ul class="grt-action-list">
				<li>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-list' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'View All Tickets', 'grt-ticket' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-settings' ) ); ?>" class="button">
						<?php esc_html_e( 'Settings', 'grt-ticket' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=grt-ticket-canned-responses' ) ); ?>" class="button">
						<?php esc_html_e( 'Manage Canned Responses', 'grt-ticket' ); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>

	<!-- Agent Performance (Top Agents) -->
	<?php if ( ! empty( $stats['agent_stats'] ) ) : ?>
	<div class="grt-dashboard-row" style="margin-top: 20px; grid-template-columns: 1fr;">
		<div class="grt-dashboard-widget agent-stats-widget">
			<h2><?php esc_html_e( 'Top Agents', 'grt-ticket' ); ?></h2>
			<div class="agent-stats-list">
				<?php foreach ( $stats['agent_stats'] as $agent ) : ?>
					<div class="agent-stat-item">
						<div class="agent-avatar">
							<img src="<?php echo esc_url( $agent['avatar'] ); ?>" alt="<?php echo esc_attr( $agent['agent_name'] ); ?>" width="48" height="48">
						</div>
						<div class="agent-info">
							<h4><?php echo esc_html( $agent['agent_name'] ); ?></h4>
							<div class="agent-ticket-count">
								<span class="dashicons dashicons-tickets-alt"></span>
								<?php
								/* translators: %s: Number of tickets assigned */
								echo esc_html( sprintf( _n( '%s Ticket Assigned', '%s Tickets Assigned', $agent['open_count'], 'grt-ticket' ), number_format_i18n( $agent['open_count'] ) ) );
								?>
							</div>
							<div class="agent-solved-count">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php
								/* translators: %s: Number of solved tickets */
								echo esc_html( sprintf( _n( '%s Solved Ticket', '%s Solved Tickets', $agent['solved_count'], 'grt-ticket' ), number_format_i18n( $agent['solved_count'] ) ) );
								?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
