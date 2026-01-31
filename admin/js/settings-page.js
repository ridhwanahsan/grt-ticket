/**
 * GRT Ticket Admin - Settings Page JS
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Ensure localization object exists
        if (typeof grtTicketAdmin === 'undefined') {
            console.error('GRT Ticket: grtTicketAdmin object is missing.');
            return;
        }
        
        // Tab Switching Logic
        var $tabs = $('.grt-settings-tabs .nav-tab');
        var $contents = $('.grt-tab-content');

        $tabs.on('click', function(e) {
            e.preventDefault();

            var targetId = $(this).attr('href');

            // Remove active class from all tabs and contents
            $tabs.removeClass('nav-tab-active');
            $contents.removeClass('active');

            // Add active class to clicked tab and target content
            $(this).addClass('nav-tab-active');
            $(targetId).addClass('active');

            // Save active tab to localStorage
            localStorage.setItem('grt_ticket_active_tab', targetId);
        });

        // Initialize Tab from localStorage or default to first
        var savedTab = localStorage.getItem('grt_ticket_active_tab');
        if (savedTab && $(savedTab).length > 0) {
            // Trigger click on the saved tab
            $('.grt-settings-tabs .nav-tab[href="' + savedTab + '"]').trigger('click');
        } else {
            // Default to the first tab
            $tabs.first().trigger('click');
        }

        // Category Management
        var $wrapper = $('#grt-categories-wrapper');
        var $addBtn = $('#grt-add-category');
        
        // Calculate initial count based on existing items to avoid index collision
        var maxIndex = -1;
        $wrapper.find('.grt-category-item input[name^="grt_categories"]').each(function() {
            var name = $(this).attr('name');
            var match = name.match(/\[(\d+)\]/);
            if (match) {
                var index = parseInt(match[1]);
                if (index > maxIndex) maxIndex = index;
            }
        });
        var count = maxIndex + 1;

        // Add Category
        $addBtn.on('click', function() {
            var agentOptions = '<option value="0">' + grtTicketAdmin.i18n.select_agent + '</option>';
            if (grtTicketAdmin.agents && grtTicketAdmin.agents.length > 0) {
                $.each(grtTicketAdmin.agents, function(index, agent) {
                    agentOptions += '<option value="' + agent.id + '">' + agent.name + '</option>';
                });
            }

            var item = `
                <div class="grt-category-item">
                    <input type="text" name="grt_categories[${count}][name]" placeholder="${grtTicketAdmin.i18n.category_name}" class="regular-text">
                    
                    <select name="grt_categories[${count}][agent_id]" class="grt-cat-agent-select">
                        ${agentOptions}
                    </select>

                    <div class="grt-image-upload-wrapper">
                        <input type="hidden" name="grt_categories[${count}][image]" class="grt-cat-image-url">
                        <div class="grt-image-preview"></div>
                        <button type="button" class="button grt-upload-image">${grtTicketAdmin.i18n.select_image}</button>
                        <button type="button" class="button grt-remove-image" style="display:none;">×</button>
                    </div>

                    <button type="button" class="button grt-remove-category">${grtTicketAdmin.i18n.remove}</button>
                </div>
            `;
            $wrapper.append(item);
            count++;
        });

        // Remove Category
        $wrapper.on('click', '.grt-remove-category', function() {
            if (confirm(grtTicketAdmin.i18n.are_you_sure)) {
                $(this).closest('.grt-category-item').remove();
            }
        });

        // Media Uploader
        var frame;
        var currentUploadWrapper;

        $wrapper.on('click', '.grt-upload-image', function(e) {
            e.preventDefault();
            currentUploadWrapper = $(this).closest('.grt-image-upload-wrapper');

            // Create a new frame every time or reuse if appropriate
            // If we reuse, we must update the 'select' handler, because currentUploadWrapper changes
            // So simpler to just create new one or unbind 'select'
            
            if (frame) {
                frame.off('select'); // Remove previous handlers
            } else {
                 // Create frame only once if possible, but simpler to recreate if we want to be safe with state
                 // Actually standard pattern is reuse but rebind.
                 frame = wp.media({
                    title: grtTicketAdmin.i18n.select_category_image,
                    button: {
                        text: grtTicketAdmin.i18n.use_this_image
                    },
                    multiple: false
                });
            }

            // Re-bind select event
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                
                // Update fields
                currentUploadWrapper.find('.grt-cat-image-url').val(attachment.url);
                
                // Update preview
                var previewHtml = '<img src="' + attachment.url + '" alt="Preview">';
                currentUploadWrapper.find('.grt-image-preview').html(previewHtml);
                
                // Show remove button
                currentUploadWrapper.find('.grt-remove-image').show();
            });

            frame.open();
        });

        // Remove Image
        $wrapper.on('click', '.grt-remove-image', function() {
            var wrapper = $(this).closest('.grt-image-upload-wrapper');
            wrapper.find('.grt-cat-image-url').val('');
            wrapper.find('.grt-image-preview').empty();
            $(this).hide();
        });

    });

})(jQuery);
