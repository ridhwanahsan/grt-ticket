/**
 * GRT Ticket Admin - Tickets List JS
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Delete ticket functionality
        $('.grt-delete-ticket').on('click', function () {
            deleteTicket($(this));
        });
    });

    /**
     * Delete ticket
     */
    function deleteTicket($button) {
        if (!confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
            return;
        }

        const ticketId = $button.data('ticket-id');
        const $row = $button.closest('tr');

        $button.prop('disabled', true).text('Deleting...');

        $.ajax({
            url: grtTicketAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'grt_ticket_delete',
                nonce: grtTicketAdmin.nonce,
                ticket_id: ticketId
            },
            success: function (response) {
                if (response.success) {
                    // Remove the row with animation
                    $row.fadeOut(400, function () {
                        $(this).remove();
                        // Check if table is empty
                        if ($('.grt-tickets-table tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.data.message || 'Failed to delete ticket.');
                    $button.prop('disabled', false).text('Delete');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
                $button.prop('disabled', false).text('Delete');
            }
        });
    }

})(jQuery);
