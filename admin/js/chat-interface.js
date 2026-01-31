/**
 * GRT Ticket Admin - Chat Interface JS
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        if ($('.grt-chat-container').length) {
            // Move chat container to body to ensure full screen overlay works correctly
            $('.grt-chat-container').appendTo('body');
            initChatInterface();
        }
    });

    /**
     * Initialize chat interface
     */
    function initChatInterface() {
        // Ensure localization object exists
        if (typeof grtTicketAdmin === 'undefined') {
            console.error('GRT Ticket: grtTicketAdmin object is missing.');
            return;
        }

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

        // Sidebar Toggle
        $('#grt-sidebar-toggle').on('click', function() {
            $('.grt-chat-container').toggleClass('sidebar-collapsed');
        });

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

        // Auto-resize textarea
        $('#grt-chat-input').on('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            
            // Check if it exceeds max-height (defined in CSS, e.g. 200px)
            // If scrollHeight > clientHeight when max-height is reached, it will scroll automatically
        });

        // Mark as solved
        $('#grt-chat-solve-btn').on('click', function () {
            markAsSolved();
        });

        // Assign Agent
        $('#grt-assign-agent').on('change', function () {
            const ticketId = $(this).data('ticket-id');
            const agentId = $(this).val();
            
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
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('GRT Ticket AJAX Error:', status, error);
                    alert('Error assigning agent: ' + (error || status));
                }
            });
        });

        // File attachment handling
        $('#grt-chat-attach-btn').on('click', function () {
            $('#grt-chat-attachment').click();
        });

        $('#grt-chat-attachment').on('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.match('image.*') && file.type !== 'application/pdf') {
                    alert('Please select an image or PDF file.');
                    return;
                }
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB.');
                    return;
                }
                
                // Show preview
                $('#grt-attachment-preview').show();
                
                if (file.type === 'application/pdf') {
                    $('#grt-preview-content').html('<div class="grt-pdf-preview"><span class="dashicons dashicons-pdf" style="font-size: 30px; width: 30px; height: 30px; color: #d00000; display:inline-block; vertical-align:middle;"></span> <span style="vertical-align:middle;">' + file.name + '</span></div>');
                } else {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $('#grt-preview-content').html('<img src="' + e.target.result + '" alt="Preview" style="max-height: 100px; max-width: 100px; object-fit: cover;">');
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        $('#grt-remove-attachment').on('click', function () {
            $('#grt-chat-attachment').val('');
            $('#grt-attachment-preview').hide();
            $('#grt-preview-content').empty();
        });

        // Canned response selection
        $('#grt-canned-response-select').on('change', function () {
            const content = $(this).val();
            if (content) {
                const $textarea = $('#grt-chat-input');
                const currentVal = $textarea.val();
                
                if (currentVal) {
                    $textarea.val(currentVal + '\n' + content);
                } else {
                    $textarea.val(content);
                }
                
                // Reset select
                $(this).val('');
                $textarea.focus();
            }
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
                        $('#grt-preview-content').empty();
                        
                        // If we received the new message object, append it directly
                        if (response.data.chat_message) {
                            appendMessages([response.data.chat_message]);
                            scrollToBottom();
                        } else {
                            loadNewMessages();
                        }
                    } else {
                        alert(response.data.message || 'Failed to send message.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('GRT Ticket AJAX Error:', status, error);
                    alert('An error occurred: ' + (error || status));
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
                error: function (xhr, status, error) {
                    console.error('GRT Ticket AJAX Error:', status, error);
                    alert('An error occurred: ' + (error || status));
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
                },
                error: function (xhr, status, error) {
                    console.error('GRT Ticket Polling Error:', status, error);
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
            let attachmentHtml = '';

            if (msg.attachment_url) {
                attachmentHtml = `
                    <div class="grt-message-attachment">
                        <a href="${escapeHtml(msg.attachment_url)}" target="_blank">
                            <img src="${escapeHtml(msg.attachment_url)}" alt="Attachment" style="max-width: 300px; border-radius: 8px;">
                        </a>
                    </div>
                `;
            }

            let messageBubble = '';
            if (msg.message) {
                messageBubble = `<div class="grt-message-bubble">${escapeHtml(msg.message)}</div>`;
            }

            return `
                <div class="grt-chat-message ${senderClass}" data-message-id="${msg.id}">
                    <div class="grt-message-sender">${escapeHtml(msg.sender_name)}</div>
                    ${messageBubble}
                    ${attachmentHtml}
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

})(jQuery);
