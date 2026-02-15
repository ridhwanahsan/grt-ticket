/**
 * GRT Ticket Admin - Tickets List JS
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Delete ticket functionality
        $(document).on('click', '.grt-delete-ticket', function () {
            deleteTicket($(this));
        });

        // Assign agent functionality
        $(document).on('change', '.grt-assign-agent-list', function () {
            assignAgent($(this));
        });

        // Bulk Actions: Select All
        $('#cb-select-all-1').on('change', function () {
            $('input[name="ticket[]"]').prop('checked', $(this).is(':checked'));
        });

        // Bulk Actions: Apply
        $('.grt-bulk-action-apply').on('click', function (e) {
            e.preventDefault();
            
            if (typeof grtTicketAdmin === 'undefined') {
                console.error('GRT Ticket: grtTicketAdmin is missing.');
                alert('System Error: Configuration missing. Please reload the page.');
                return;
            }

            const action = $('#bulk-action-selector-top').val();
            if (action === '-1') {
                alert('Please select an action.');
                return;
            }

            const selectedTickets = [];
            $('input[name="ticket[]"]:checked').each(function () {
                selectedTickets.push($(this).val());
            });

            if (selectedTickets.length === 0) {
                alert('Please select at least one ticket.');
                return;
            }

            if (action === 'delete' && !confirm('Are you sure you want to delete selected tickets?')) {
                return;
            }

            const $button = $(this);
            $button.prop('disabled', true).val('Applying...');

            $.ajax({
                url: grtTicketAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'grt_bulk_action',
                    bulk_action: action,
                    ticket_ids: selectedTickets,
                    nonce: grtTicketAdmin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Failed to apply bulk action.');
                        $button.prop('disabled', false).val('Apply');
                    }
                },
                error: function () {
                    alert('An error occurred. Please try again.');
                    $button.prop('disabled', false).val('Apply');
                }
            });
        });
    });

    /**
     * Assign agent
     */
    function assignAgent($select) {
        // Ensure localization object exists
        if (typeof grtTicketAdmin === 'undefined') {
            console.error('GRT Ticket: grtTicketAdmin object is missing.');
            alert('System Error: Configuration missing. Please reload the page.');
            return;
        }

        const ticketId = $select.data('ticket-id');
        const agentId = $select.val();

        // Disable select temporarily and show saving state
        $select.prop('disabled', true);
        const originalColor = $select.css('background-color');
        $select.css('background-color', '#fff3cd'); // Yellow for "saving"
        
        console.log('Assigning ticket ID:', ticketId, 'to Agent ID:', agentId);

        $.ajax({
            url: grtTicketAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'grt_ticket_assign_agent',
                ticket_id: ticketId,
                agent_id: agentId,
                nonce: grtTicketAdmin.nonce
            },
            success: function (response) {
                $select.prop('disabled', false);
                if (response.success) {
                    // Success visual indicator (Green flash)
                    $select.css('background-color', '#d4edda');
                    $select.css('border-color', '#28a745');
                    
                    setTimeout(function() {
                        $select.css('background-color', originalColor);
                        $select.css('border-color', '');
                    }, 1000);
                } else {
                    $select.css('background-color', '#f8d7da'); // Red for error
                    alert(response.data.message || 'Failed to assign agent.');
                    setTimeout(function() {
                        $select.css('background-color', originalColor);
                    }, 2000);
                }
            },
            error: function (xhr, status, error) {
                $select.prop('disabled', false);
                $select.css('background-color', '#f8d7da'); // Red for error
                console.error('AJAX Error:', status, error);
                alert('An error occurred. Please check console for details.');
            }
        });
    }

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
