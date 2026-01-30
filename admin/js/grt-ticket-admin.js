/**
 * GRT Ticket Admin JavaScript
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // Chat interface functionality
        if ($('.grt-chat-container').length) {
            initChatInterface();
        }

        // Delete ticket functionality
        $('.grt-delete-ticket').on('click', function () {
            deleteTicket($(this));
        });

    });

    /**
     * Initialize chat interface
     */
    function initChatInterface() {
        const ticketId = $('#grt-ticket-id').val();
        let lastMessageId = 0;
        let pollInterval;

        // Get initial last message ID
        $('.grt-chat-message').each(function () {
            const msgId = parseInt($(this).data('message-id'));
            if (msgId > lastMessageId) {
                lastMessageId = msgId;
            }
        });

        // Scroll to bottom initially
        scrollToBottom();

        // Send message
        $('#grt-chat-send-btn').on('click', function () {
            sendMessage();
        });

        // Send on Enter (Shift+Enter for new line)
        $('#grt-chat-input').on('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Mark as solved
        $('#grt-chat-solve-btn').on('click', function () {
            markAsSolved();
        });

        // File attachment handling
        $('#grt-chat-attach-btn').on('click', function () {
            $('#grt-chat-attachment').click();
        });

        $('#grt-chat-attachment').on('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file.');
                    return;
                }
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB.');
                    return;
                }
                // Show preview
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#grt-preview-image').attr('src', e.target.result);
                    $('#grt-attachment-preview').show();
                };
                reader.readAsDataURL(file);
            }
        });

        $('#grt-remove-attachment').on('click', function () {
            $('#grt-chat-attachment').val('');
            $('#grt-attachment-preview').hide();
            $('#grt-preview-image').attr('src', '');
        });

        // Start polling for new messages
        startPolling();

        /**
         * Send a message
         */
        function sendMessage() {
            const message = $('#grt-chat-input').val().trim();
            const attachment = $('#grt-chat-attachment')[0].files[0];

            if (!message && !attachment) {
                return;
            }

            const $sendBtn = $('#grt-chat-send-btn');
            $sendBtn.prop('disabled', true).text('Sending...');

            // Use FormData to handle file upload
            const formData = new FormData();
            formData.append('action', 'grt_ticket_send_message');
            formData.append('nonce', grtTicketAdmin.nonce);
            formData.append('ticket_id', ticketId);
            if (message) {
                formData.append('message', message);
            }
            if (attachment) {
                formData.append('attachment', attachment);
            }

            $.ajax({
                url: grtTicketAdmin.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        $('#grt-chat-input').val('');
                        $('#grt-chat-attachment').val('');
                        $('#grt-attachment-preview').hide();
                        $('#grt-preview-image').attr('src', '');
                        loadNewMessages();
                    } else {
                        alert(response.data.message || 'Failed to send message.');
                    }
                },
                error: function () {
                    alert('An error occurred. Please try again.');
                },
                complete: function () {
                    $sendBtn.prop('disabled', false).text('Send');
                }
            });
        }

        /**
         * Mark ticket as solved
         */
        function markAsSolved() {
            if (!confirm('Are you sure you want to mark this ticket as solved?')) {
                return;
            }

            const $solveBtn = $('#grt-chat-solve-btn');
            $solveBtn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: grtTicketAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'grt_ticket_mark_solved',
                    nonce: grtTicketAdmin.nonce,
                    ticket_id: ticketId
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Failed to mark as solved.');
                        $solveBtn.prop('disabled', false).text('Mark as Solved');
                    }
                },
                error: function () {
                    alert('An error occurred. Please try again.');
                    $solveBtn.prop('disabled', false).text('Mark as Solved');
                }
            });
        }

        /**
         * Load new messages
         */
        function loadNewMessages() {
            $.ajax({
                url: grtTicketAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'grt_ticket_get_messages',
                    nonce: grtTicketAdmin.nonce,
                    ticket_id: ticketId,
                    since_id: lastMessageId
                },
                success: function (response) {
                    if (response.success && response.data.messages.length > 0) {
                        appendMessages(response.data.messages);
                        scrollToBottom();
                    }
                }
            });
        }

        /**
         * Append messages to chat
         */
        function appendMessages(messages) {
            const $messagesContainer = $('.grt-chat-messages');

            messages.forEach(function (msg) {
                if (msg.id > lastMessageId) {
                    const messageHtml = createMessageHtml(msg);
                    $messagesContainer.append(messageHtml);
                    lastMessageId = msg.id;
                }
            });
        }

        /**
         * Create message HTML
         */
        function createMessageHtml(msg) {
            const senderClass = msg.sender_type === 'admin' ? 'admin' : 'user';
            const time = formatTime(msg.created_at);

            return `
                <div class="grt-chat-message ${senderClass}" data-message-id="${msg.id}">
                    <div class="grt-message-sender">${escapeHtml(msg.sender_name)}</div>
                    <div class="grt-message-bubble">${escapeHtml(msg.message)}</div>
                    <div class="grt-message-time">${time}</div>
                </div>
            `;
        }

        /**
         * Format time
         */
        function formatTime(datetime) {
            const date = new Date(datetime);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);

            if (minutes < 1) {
                return 'Just now';
            } else if (minutes < 60) {
                return minutes + ' min ago';
            } else if (minutes < 1440) {
                return Math.floor(minutes / 60) + ' hours ago';
            } else {
                return date.toLocaleDateString();
            }
        }

        /**
         * Scroll to bottom of messages
         */
        function scrollToBottom() {
            const $messages = $('.grt-chat-messages');
            $messages.scrollTop($messages[0].scrollHeight);
        }

        /**
         * Start polling for new messages
         */
        function startPolling() {
            pollInterval = setInterval(function () {
                loadNewMessages();
            }, grtTicketAdmin.poll_interval);
        }

        /**
         * Escape HTML
         */
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function (m) { return map[m]; });
        }
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
