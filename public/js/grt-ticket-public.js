/**
 * GRT Ticket Public JavaScript
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // Ticket form functionality
        if ($('.grt-ticket-container').length) {
            initTicketForm();
        }

        // Chat interface functionality
        if ($('.grt-chat-container').length) {
            initChatInterface();
        }

    });

    /**
     * Initialize ticket form
     */
    function initTicketForm() {
        // Show form when category is selected
        $('#grt-category-select').on('change', function () {
            const category = $(this).val();
            if (category) {
                $('#grt-selected-category').val(category);
                $('.grt-ticket-form').addClass('active');
            } else {
                $('.grt-ticket-form').removeClass('active');
            }
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

    /**
     * Initialize chat interface
     */
    function initChatInterface() {
        const ticketId = $('#grt-ticket-id').val();
        const userEmail = $('#grt-user-email').val();
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
            formData.append('nonce', grtTicketPublic.nonce);
            formData.append('ticket_id', ticketId);
            if (message) {
                formData.append('message', message);
            }
            if (attachment) {
                formData.append('attachment', attachment);
            }

            $.ajax({
                url: grtTicketPublic.ajax_url,
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
                error: function () {
                    alert('An error occurred. Please try again.');
                },
                complete: function () {
                    $sendBtn.prop('disabled', false).text('Send');
                }
            });
        }

        /**
         * Load new messages
         */
        function loadNewMessages() {
            $.ajax({
                url: grtTicketPublic.ajax_url,
                type: 'POST',
                data: {
                    action: 'grt_ticket_get_messages',
                    nonce: grtTicketPublic.nonce,
                    ticket_id: ticketId,
                    since_id: lastMessageId
                },
                success: function (response) {
                    if (response.success) {
                        if (response.data.messages.length > 0) {
                            appendMessages(response.data.messages);
                            scrollToBottom();
                        }

                        // Check if ticket was solved
                        if (response.data.status === 'solved' || response.data.status === 'closed') {
                            location.reload();
                        }
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
            }, grtTicketPublic.poll_interval);
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
