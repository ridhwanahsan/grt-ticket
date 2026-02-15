<?php
/**
 * Form Builder Page
 *
 * @package    GRT_Ticket
 * @subpackage GRT_Ticket/admin/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap grt-ticket-form-builder-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Ticket Form Builder', 'grt-ticket' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Drag and drop fields to reorder. You can mix system fields with custom fields.', 'grt-ticket' ); ?></p>
	<hr class="wp-header-end">

	<div class="grt-fb-container">
		
		<div class="grt-fb-sidebar">
			<h3><?php esc_html_e( 'Add Custom Field', 'grt-ticket' ); ?></h3>
			<div class="grt-fb-field-types">
				<button type="button" class="grt-fb-add-field button" data-type="text">
					<span class="dashicons dashicons-text"></span> <?php esc_html_e( 'Text', 'grt-ticket' ); ?>
				</button>
				<button type="button" class="grt-fb-add-field button" data-type="textarea">
					<span class="dashicons dashicons-editor-paragraph"></span> <?php esc_html_e( 'Textarea', 'grt-ticket' ); ?>
				</button>
				<button type="button" class="grt-fb-add-field button" data-type="number">
					<span class="dashicons dashicons-calculator"></span> <?php esc_html_e( 'Number', 'grt-ticket' ); ?>
				</button>
				<button type="button" class="grt-fb-add-field button" data-type="email">
					<span class="dashicons dashicons-email"></span> <?php esc_html_e( 'Email', 'grt-ticket' ); ?>
				</button>
				<button type="button" class="grt-fb-add-field button" data-type="url">
					<span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'URL', 'grt-ticket' ); ?>
				</button>
				<button type="button" class="grt-fb-add-field button" data-type="select">
					<span class="dashicons dashicons-arrow-down-alt2"></span> <?php esc_html_e( 'Dropdown', 'grt-ticket' ); ?>
				</button>
				<button type="button" class="grt-fb-add-field button" data-type="date">
					<span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Date', 'grt-ticket' ); ?>
				</button>
			</div>
			
			<div class="grt-fb-actions">
				<button type="button" id="grt-fb-save-btn" class="button button-primary button-large">
					<?php esc_html_e( 'Save Form Structure', 'grt-ticket' ); ?>
				</button>
				<span class="spinner"></span>
			</div>
		</div>

		<div class="grt-fb-main">
			<div id="grt-fb-fields-container" class="grt-fb-fields-list">
				<!-- Fields will be injected here via JS -->
			</div>
		</div>

	</div>
</div>

<!-- Template for Field Item -->
<script type="text/template" id="grt-fb-field-template">
	<div class="grt-fb-field-item grt-fb-width-{{width}} {{systemClass}}" data-id="{{id}}" data-type="{{type}}" data-width="{{width}}" data-system="{{isSystem}}">
		
		<!-- Visual Preview (Sortable Handle Wrapper) -->
		<div class="grt-fb-field-preview">
			<div class="grt-fb-preview-header">
				<div class="grt-fb-handle-area">
					<span class="grt-fb-handle dashicons dashicons-move"></span>
					<span class="grt-fb-type-label">{{typeLabel}}</span>
				</div>
				<div class="grt-fb-actions-area">
					<button type="button" class="grt-fb-edit-btn" title="Edit Field"><span class="dashicons dashicons-edit"></span></button>
					{{removeBtn}}
				</div>
			</div>
			
			<div class="grt-fb-preview-content">
				<label class="grt-fb-preview-label">{{label}} <span class="grt-fb-required-mark">{{requiredMark}}</span></label>
				<div class="grt-fb-preview-input-wrapper">
					{{inputPreview}}
				</div>
			</div>
		</div>

		<!-- Settings Panel (Hidden by default) -->
		<div class="grt-fb-field-settings">
			<div class="grt-fb-row">
				<div class="grt-fb-col">
					<label><?php esc_html_e( 'Label', 'grt-ticket' ); ?></label>
					<input type="text" class="grt-fb-input-label" value="{{label}}">
				</div>
				<div class="grt-fb-col">
					<label><?php esc_html_e( 'Width', 'grt-ticket' ); ?></label>
					<select class="grt-fb-input-width">
						<option value="100" {{width100}}>100% (Full Row)</option>
						<option value="50" {{width50}}>50% (Half Row)</option>
						<option value="33" {{width33}}>33% (One Third)</option>
					</select>
				</div>
			</div>
			
			<div class="grt-fb-row">
				<div class="grt-fb-col">
					<label><?php esc_html_e( 'Placeholder', 'grt-ticket' ); ?></label>
					<input type="text" class="grt-fb-input-placeholder" value="{{placeholder}}">
				</div>
				<div class="grt-fb-col">
					<label style="margin-top: 25px;">
						<input type="checkbox" class="grt-fb-input-required" {{requiredChecked}} {{requiredDisabled}}>
						<?php esc_html_e( 'Required Field', 'grt-ticket' ); ?>
					</label>
				</div>
			</div>

			{{optionsHtml}}
			
			<div class="grt-fb-settings-footer">
				<button type="button" class="button grt-fb-close-settings"><?php esc_html_e( 'Done', 'grt-ticket' ); ?></button>
			</div>
		</div>
	</div>
</script>

<!-- Template for Options (Select) -->
<script type="text/template" id="grt-fb-options-template">
	<div class="grt-fb-options-wrapper">
		<label><?php esc_html_e( 'Options (One per line)', 'grt-ticket' ); ?></label>
		<textarea class="grt-fb-input-options" rows="3">{{options}}</textarea>
		<p class="description"><?php esc_html_e( 'Enter options separated by new lines. Format: value : label (or just value)', 'grt-ticket' ); ?></p>
	</div>
</script>
