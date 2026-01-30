/**
 * GRT Ticket Public - Ticket Form JS
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        if ($('.grt-ticket-container').length) {
            initTicketForm();
        }
    });

    /**
     * Initialize ticket form
     */
    function initTicketForm() {
        // Custom Dropdown Logic
        var $dropdown = $('#grt-category-dropdown');
        var $selected = $dropdown.find('.grt-dropdown-selected');
        var $options = $dropdown.find('.grt-dropdown-options');
        var $input = $('#grt-selected-category');

        // Toggle dropdown
        $selected.on('click', function(e) {
            e.stopPropagation();
            $dropdown.find('.grt-dropdown-selected').toggleClass('active');
            $options.toggleClass('show');
        });

        // Close when clicking outside
        $(document).on('click', function() {
            $selected.removeClass('active');
            $options.removeClass('show');
        });

        // Select item
        $dropdown.on('click', '.grt-dropdown-item', function() {
            var value = $(this).data('value');
            var html = $(this).html();
            
            // Update selected view
            $selected.find('.grt-selected-text').html('<div class="grt-selected-content">' + html + '</div>');
            
            // Update input value
            $input.val(value);
            
            // Close dropdown
            $selected.removeClass('active');
            $options.removeClass('show');
            
            // Show form
            $('.grt-ticket-form').addClass('active');
            
            // Smooth scroll to form
            $('html, body').animate({
                scrollTop: $('.grt-ticket-form').offset().top - 50
            }, 500);
        });

        // Submit ticket form
        $('#grt-ticket-submit-form').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $('#grt-submit-btn');
            const formData = $form.serialize();

            // Clear previous messages
            $('.grt-message').remove();

            // Validate
            let isValid = true;
            $form.find('[required]').each(function () {
                if (!$(this).val().trim()) {
                    isValid = false;
                    $(this).css('border-color', '#e74c3c');
                } else {
                    $(this).css('border-color', '#e0e0e0');
                }
            });

            if (!isValid) {
                showMessage('Please fill in all required fields.', 'error');
                return;
            }

            $submitBtn.prop('disabled', true).text('Submitting...');

            $.ajax({
                url: grtTicketPublic.ajax_url,
                type: 'POST',
                data: formData + '&action=grt_ticket_submit&nonce=' + grtTicketPublic.nonce,
                success: function (response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');

                        // Redirect to chat after 2 seconds
                        setTimeout(function () {
                            const ticketId = response.data.ticket_id;
                            // Clean professional URL structure: current-url/ticket/123/
                            // Remove trailing slashes from path to avoid double slashes
                            let currentPath = window.location.pathname.replace(/\/+$/, '');

                            // Check if we are already in a permalink structure (unlikely on submission, but safe check)
                            if (currentPath.indexOf('/ticket/') === -1) {
                                window.location.href = currentPath + '/ticket/' + ticketId + '/';
                            } else {
                                // Fallback or weird edge case
                                window.location.href = window.location.href + '?ticket_id=' + ticketId;
                            }
                        }, 2000);
                    } else {
                        showMessage(response.data.message || 'Failed to submit ticket.', 'error');
                        $submitBtn.prop('disabled', false).text('Submit Ticket');
                    }
                },
                error: function () {
                    showMessage('An error occurred. Please try again.', 'error');
                    $submitBtn.prop('disabled', false).text('Submit Ticket');
                }
            });
        });

        function showMessage(message, type) {
            const $message = $('<div class="grt-message ' + type + '">' + message + '</div>');
            $('.grt-ticket-form').prepend($message);
        }
    }

})(jQuery);
