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

        // Request notification permission if enabled
        if (grtTicketPublic.enable_notification == 1) {
            if ('Notification' in window && Notification.permission !== 'granted') {
                Notification.requestPermission();
            }
        }

        // Load user notification preferences
        loadUserNotificationPreferences();

        function loadUserNotificationPreferences() {
            const soundPref = localStorage.getItem('grt_user_notification_sound');
            const $soundToggle = $('#grt-user-notification-sound');
            
            if (soundPref === 'true') {
                $soundToggle.prop('checked', true);
            } else if (soundPref === 'false') {
                $soundToggle.prop('checked', false);
            } else {
                // Default to admin setting if not set by user
                $soundToggle.prop('checked', grtTicketPublic.enable_sound == 1);
            }
        }

        // Handle user notification preference change
        $('#grt-user-notification-sound').on('change', function() {
            const isChecked = $(this).is(':checked');
            localStorage.setItem('grt_user_notification_sound', isChecked);
        });

        /**
         * Trigger Browser Notification
         */
        function triggerNotification(title, body) {
            if (grtTicketPublic.enable_notification != 1) return;
            
            if (!('Notification' in window)) return;

            if (Notification.permission === 'granted') {
                const notification = new Notification(title, {
                    body: body,
                    icon: grtTicketPublic.notification_icon || ''
                });

                notification.onclick = function() {
                    window.focus();
                    notification.close();
                };
            }
        }

        /**
         * Play Notification Sound
         */
        function playNotificationSound() {
            // Check user preference
            const userPref = localStorage.getItem('grt_user_notification_sound');
            
            // If user explicitly disabled it, don't play
            if (userPref === 'false') return;
            
            // If user hasn't set preference, use global setting
            if (userPref === null && grtTicketPublic.enable_sound != 1) return;
            
            // Let's use a simple beep
            try {
                if (!audioCtx) {
                    unlockAudio();
                }

                if (audioCtx && audioCtx.state === 'running') {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    
                    osc.type = 'sine';
                    osc.frequency.value = 800; // Hz
                    gain.gain.value = 0.1; // Volume
                    
                    osc.start();
                    setTimeout(function() {
                        osc.stop();
                    }, 200);
                } else {
                    console.warn('AudioContext not running (waiting for user interaction)');
                }
            } catch (e) {
                console.error('Audio error', e);
            }
        }

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

        // Profile Image Upload
        $('.grt-profile-wrapper').not('.big').on('click', function() {
            $('#grt-profile-upload').click();
        });

        $('#grt-profile-upload').on('click', function(e) {
            e.stopPropagation();
        });

        $('#grt-profile-upload').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.match('image.*')) {
                alert('Please select an image file.');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'grt_upload_profile_image');
            formData.append('nonce', grtTicketPublic.nonce);
            formData.append('profile_image', file);

            const $icon = $('.grt-profile-icon');
            $icon.css('opacity', '0.5');

            $.ajax({
                url: grtTicketPublic.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        if ($icon.find('img').length) {
                            $icon.find('img').attr('src', response.data.image_url);
                        } else {
                            // Replace text with image and re-add overlay
                            $icon.html('<img src="' + response.data.image_url + '" alt="Profile"><div class="grt-profile-overlay"><span class="dashicons dashicons-camera"></span></div>');
                        }

                        // Update all user avatars in chat
                        $('.grt-chat-message.user .grt-message-avatar img').attr('src', response.data.image_url);
                        $('.grt-chat-message.user .grt-message-avatar .grt-avatar-placeholder').parent().html('<img src="' + response.data.image_url + '" alt="Profile">');

                    } else {
                        alert(response.data.message || 'Upload failed');
                    }
                },
                error: function() {
                    alert('Upload failed');
                },
                complete: function() {
                    $icon.css('opacity', '1');
                    // Reset input
                    $('#grt-profile-upload').val('');
                }
            });
        });

        // Tab Switching
        $('.grt-tab-btn').on('click', function() {
            const tabId = $(this).data('tab');
            
            $('.grt-tab-btn').removeClass('active');
            $(this).addClass('active');
            
            $('.grt-tab-content').removeClass('active');
            $('#grt-tab-' + tabId).addClass('active');
        });

        // Profile Image Upload (Tab)
        $('.grt-profile-wrapper.big').on('click', function() {
            $('#grt-profile-upload-tab').click();
        });

        $('#grt-profile-upload-tab').on('click', function(e) {
            e.stopPropagation();
        });

        $('#grt-profile-upload-tab').on('change', function(e) {
            // Re-use the same upload logic but update the big icon and the header icon
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.match('image.*')) {
                alert('Please select an image file.');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'grt_upload_profile_image');
            formData.append('nonce', grtTicketPublic.nonce);
            formData.append('profile_image', file);

            const $icon = $('.grt-profile-icon.big');
            $icon.css('opacity', '0.5');

            $.ajax({
                url: grtTicketPublic.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        const newImageHtml = '<img src="' + response.data.image_url + '" alt="Profile"><div class="grt-profile-overlay"><span class="dashicons dashicons-camera"></span></div>';
                        
                        // Update big profile icon
                        if ($icon.find('img').length) {
                            $icon.find('img').attr('src', response.data.image_url);
                        } else {
                            $icon.html(newImageHtml);
                        }

                        // Update header profile icon
                        const $headerIcon = $('.grt-chat-header-profile .grt-profile-icon');
                        if ($headerIcon.find('img').length) {
                            $headerIcon.find('img').attr('src', response.data.image_url);
                        } else {
                            $headerIcon.html(newImageHtml);
                        }

                        // Update all user avatars in chat
                        $('.grt-chat-message.user .grt-message-avatar img').attr('src', response.data.image_url);
                        $('.grt-chat-message.user .grt-message-avatar .grt-avatar-placeholder').parent().html('<img src="' + response.data.image_url + '" alt="Profile">');

                    } else {
                        alert(response.data.message || 'Upload failed');
                    }
                },
                error: function() {
                    alert('Upload failed');
                },
                complete: function() {
                    $icon.css('opacity', '1');
                    $('#grt-profile-upload-tab').val('');
                }
            });
        });

        // Start Chat (Realtime or Polling)
        if (grtTicketPublic.supabase && grtTicketPublic.supabase.enabled) {
            initSupabase();
        } else {
            startPolling();
        }

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
            $sendBtn.prop('disabled', true).text('Send');

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

            const tempId = 'temp-' + Date.now();
            const tempText = message ? message : (attachment ? 'Attachment' : '');
            const tempMessage = {
                id: tempId,
                sender_type: 'user',
                sender_name: 'You',
                message: tempText,
                attachment_url: '',
                created_at: new Date().toISOString(),
                is_internal: 0,
                avatar_url: ''
            };
            const $tempMessage = $(createMessageHtml(tempMessage));
            $('.grt-chat-messages').append($tempMessage);
            scrollToBottom();
            $sendBtn.prop('disabled', false).text('Send');

            $.ajax({
                url: grtTicketPublic.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        $('.grt-chat-message[data-message-id="' + tempId + '"]').remove();
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
                        $('.grt-chat-message[data-message-id="' + tempId + '"]').remove();
                        alert(response.data.message || 'Failed to send message.');
                    }
                },
                error: function (xhr, status, error) {
                    $('.grt-chat-message[data-message-id="' + tempId + '"]').remove();
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

                        if ((isSolvedServer && !isSolvedUI) || (!isSolvedServer && isSolvedUI)) {
                            const statusKey = isSolvedServer ? 'solved' : 'open';
                            const reloadKey = 'grt_ticket_status_reload_' + ticketId;
                            const lastReloadedStatus = sessionStorage.getItem(reloadKey);
                            if (lastReloadedStatus !== statusKey) {
                                sessionStorage.setItem(reloadKey, statusKey);
                                location.reload();
                            }
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
            let hasNewMessages = false;
            let lastMsg = null;

            messages.forEach(function (msg) {
                if (msg.is_internal == 1) {
                    return;
                }
                const msgId = parseInt(msg.id);
                if (msgId > lastMessageId) {
                    const messageHtml = createMessageHtml(msg);
                    $messagesContainer.append(messageHtml);
                    lastMessageId = msgId;
                    
                    // Only notify for messages from others (admin)
                    if (msg.sender_type !== 'user') {
                        hasNewMessages = true;
                        lastMsg = msg;
                    }
                }
            });

            if (hasNewMessages && lastMsg) {
                playNotificationSound();
                triggerNotification('New Support Message', lastMsg.sender_name + ': ' + (lastMsg.message ? lastMsg.message.substring(0, 50) : 'Sent an attachment'));
            }
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

            // Avatar Logic
            let avatarHtml = '';
            if (msg.avatar_url) {
                avatarHtml = `<div class="grt-message-avatar"><img src="${escapeHtml(msg.avatar_url)}" alt="${escapeHtml(msg.sender_name)}"></div>`;
            } else if (grtTicketPublic.default_avatar_url) {
                avatarHtml = `<div class="grt-message-avatar"><img src="${escapeHtml(grtTicketPublic.default_avatar_url)}" alt="${escapeHtml(msg.sender_name)}"></div>`;
            } else {
                const initial = msg.sender_name.charAt(0).toUpperCase();
                avatarHtml = `<div class="grt-message-avatar"><div class="grt-avatar-placeholder">${initial}</div></div>`;
            }

            return `
                <div class="grt-chat-message ${senderClass}" data-message-id="${msg.id}">
                    ${avatarHtml}
                    <div class="grt-message-content-wrapper">
                        <div class="grt-message-sender">${escapeHtml(msg.sender_name)}</div>
                        ${messageBubble}
                        ${attachmentHtml}
                        <div class="grt-message-time">${time}</div>
                    </div>
                </div>
            `;
        }

        /**
         * Format time
         */
        function formatTime(datetime) {
            const parsed = parseServerDate(datetime);
            const date = parsed || new Date(datetime);
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

        function parseServerDate(datetime) {
            if (!datetime || typeof datetime !== 'string') return null;
            if (datetime.indexOf('T') !== -1 && (datetime.indexOf('Z') !== -1 || datetime.match(/[+-]\d{2}:\d{2}$/))) {
                return new Date(datetime);
            }
            if (datetime.indexOf(' ') !== -1) {
                const iso = datetime.replace(' ', 'T') + 'Z';
                return new Date(iso);
            }
            return null;
        }

        /**
         * Scroll to bottom of messages
         */
        function scrollToBottom() {
            const $messages = $('.grt-chat-messages');
            $messages.scrollTop($messages[0].scrollHeight);
        }

        /**
         * Initialize Supabase Realtime
         */
        function initSupabase() {
            if (typeof supabase === 'undefined') {
                console.error('Supabase client not loaded. Falling back to polling.');
                startPolling();
                return;
            }

            console.log('Initializing Supabase Realtime for Ticket #' + ticketId);
            const sb = supabase.createClient(grtTicketPublic.supabase.url, grtTicketPublic.supabase.anon_key);

            const channel = sb
                .channel('public:grt_messages')
                .on(
                    'postgres_changes',
                    {
                        event: 'INSERT',
                        schema: 'public',
                        table: 'grt_messages',
                        filter: `ticket_id=eq.${ticketId}`
                    },
                    (payload) => {
                        if (payload.errors) {
                            console.error('Supabase Payload Error:', payload.errors);
                            return;
                        }
                        const newMsg = payload.new;
                        
                        // Inject avatar_url if missing (Supabase doesn't store it)
                        if (!newMsg.avatar_url) {
                            if (newMsg.sender_type === 'user') {
                                // Try to get current user's avatar from the profile icon
                                const $profileImg = $('.grt-profile-icon img');
                                if ($profileImg.length) {
                                    newMsg.avatar_url = $profileImg.attr('src');
                                }
                            } else {
                                // Admin/Support - use default if available
                                if (grtTicketPublic.default_avatar_url) {
                                    newMsg.avatar_url = grtTicketPublic.default_avatar_url;
                                }
                            }
                        }

                        // Append message
                        appendMessages([newMsg]);
                        scrollToBottom();
                    }
                )
                .subscribe();
        }

        /**
         * Start polling for new messages
         */
        function startPolling() {
            // Clear any existing interval just in case
            if (pollInterval) clearInterval(pollInterval);

            loadNewMessages();

            pollInterval = setInterval(function () {
                loadNewMessages();
            }, parseInt(grtTicketPublic.poll_interval) || 3000);
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
