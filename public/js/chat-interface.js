/**
 * GRT Ticket Public - Chat Interface JS
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        if ($('.grt-chat-container').length) {
            // Move chat container to body to ensure full screen overlay works correctly
            // regardless of theme container styles
            $('.grt-chat-container').appendTo('body');
            initChatInterface();
        }
    });

    /**
     * Initialize chat interface
     */
    function initChatInterface() {
        // Ensure localization object exists
        if (typeof grtTicketPublic === 'undefined') {
            console.error('GRT Ticket: grtTicketPublic object is missing.');
            return;
        }

        const ticketId = $('#grt-ticket-id').val();
        const userEmail = $('#grt-user-email').val();
        let lastMessageId = 0;
        let pollInterval;

        // Star Rating System
        $('.grt-rating-stars .grt-star').hover(
            function() {
                $(this).addClass('hover').prevAll().addClass('hover');
            },
            function() {
                $('.grt-rating-stars .grt-star').removeClass('hover');
            }
        );

        $('.grt-rating-stars .grt-star').on('click', function(e) {
            e.preventDefault();
            const rating = $(this).data('value');
            $('#grt-rating-value').val(rating);
            
            $('.grt-rating-stars .grt-star').removeClass('selected');
            $(this).addClass('selected').prevAll().addClass('selected');
        });

        $('#grt-submit-rating').on('click', function(e) {
            e.preventDefault();
            const rating = $('#grt-rating-value').val();
            const feedback = $('#grt-rating-feedback').val();
            const $btn = $(this);

            if (rating == 0) {
                alert('Please select a rating star.');
                return;
            }

            $btn.prop('disabled', true).text('Submitting...');

            $.ajax({
                url: grtTicketPublic.ajax_url,
                type: 'POST',
                data: {
                    action: 'grt_ticket_submit_rating',
                    ticket_id: ticketId,
                    rating: rating,
                    feedback: feedback,
                    nonce: grtTicketPublic.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                        $btn.prop('disabled', false).text('Submit Rating');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('GRT Ticket AJAX Error:', status, error);
                    alert('Error submitting rating: ' + (error || status));
                    $btn.prop('disabled', false).text('Submit Rating');
                }
            });
        });

        // Prevent reload on textarea interaction
        $('#grt-rating-feedback').on('click keydown', function(e) {
            e.stopPropagation();
        });

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

        // Auto-resize textarea
        $('#grt-chat-input').on('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
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

                        // Check if ticket status changed (solved/closed vs open)
                        const isSolvedServer = response.data.status === 'solved' || response.data.status === 'closed';
                        const isSolvedUI = $('.grt-chat-solved-notice').length > 0;

                        if (isSolvedServer && !isSolvedUI) {
                            location.reload();
                        } else if (!isSolvedServer && isSolvedUI) {
                            location.reload();
                        }
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
