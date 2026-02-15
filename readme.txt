=== GRT Ticket ===
Contributors: ridhwanahsann
Tags: support, ticket, helpdesk, chat, webhooks
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A support ticket system with real-time chat, email piping, custom fields, and Webhook integrations (Slack/Discord/Zapier).

== Description ==

**Note on Minified JS**: The `public/js/supabase.js` file is a minified build of the Supabase JS client. Source code available at: https://github.com/supabase/supabase-js

== Features ==

* **Webhooks & Integrations (New!)**: Send notifications to **Slack**, **Discord**, or **Zapier** when a new ticket is created.
* **Supabase Integration**: True real-time chat powered by Supabase Realtime DB (optional)
* **Guest Access (New!)**: Guests can view and reply to their tickets immediately after submission via secure cookie (no login required).
* Create and manage tickets from WordPress admin and frontend
* Real-time chat between users and admins
* **Custom Fields Builder**: Create custom forms with Text, Textarea, Number, Email, URL, Select, and Date fields
* **Advanced Search**: Filter tickets by User Name or Ticket ID instantly
* **Guest Ticket Submission**: Users can submit tickets without an account (secure cookie access)
* **User Profile**: Users can manage their profile and upload avatars
* Auto-Assignment: Automatically assign tickets to agents based on category
* Agent Performance: View stats on agent performance and ticket resolution
* Email Piping: User replies via email are automatically added to ticket chat
* Email Notifications: Chat replies are sent to user email (with direct frontend links)
* IMAP/SMTP settings configurable via plugin settings
* Secure input handling and WordPress coding standards compliant
* Fully GPL-2.0 compatible

== External Services ==

This plugin integrates with the following third-party services:

### 1. Twilio (Optional)
*   **Service**: Twilio API (WhatsApp & SMS)
*   **Usage**: Sends notifications to admins and users via WhatsApp or SMS.
*   **Data Sent**: Message content, phone numbers.
*   **Privacy Policy**: [Twilio Privacy Policy](https://www.twilio.com/legal/privacy)

### 2. Supabase (Optional)
*   **Service**: Supabase Realtime
*   **Usage**: Provides real-time chat capabilities.
*   **Data Sent**: Chat messages, user IDs (for authentication).
*   **Privacy Policy**: [Supabase Privacy Policy](https://supabase.com/privacy)

### 3. Webhooks (Slack, Discord, Zapier)
*   **Usage**: Sends ticket data to external services for notification/automation.
*   **Data Sent**: Ticket details (title, ID, user email, message).

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/grt-ticket` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->GRT Ticket screen to configure the plugin.
4. Add the shortcode `[grt_ticket]` to any page to display the ticket submission form.

=== Supabase Configuration (Optional for Realtime) ===

1. Create a project at [Supabase](https://supabase.com).
2. Get your **Project URL**, **Anon Key**, and **Service Role Secret**.
3. In WordPress, go to **GRT Ticket > Settings** and enter these credentials.
4. In Supabase SQL Editor, run this command to create the messages table:
   `create table public.grt_messages ( id bigint not null, ticket_id bigint not null, sender_type text not null, sender_name text not null, message text null, attachment_url text null, is_internal integer null default 0, created_at timestamp with time zone null default now(), constraint grt_messages_pkey primary key (id) ) TABLESPACE pg_default;`
5. **IMPORTANT**: Go to **Supabase Dashboard > Database > Publications** (or Replication) and enable **"grt_messages"** for Realtime. (Otherwise, no messages will be received!)
6. Enable "Realtime Chat" in plugin settings.

== Changelog ==

= 1.1.4 =
* Security: Removed all user creation and auto-login functionality to prevent security risks.
* Security: Implemented secure cookie-based access for guest tickets.
* Security: Fixed escaping issues in shortcodes and admin pages.
* Security: Added missing nonces and permission checks in AJAX handlers.
* Fix: Removed debug.log and added source code documentation for minified JS.
* Fix: Added 'ridhwanahsann' to contributors list.

= 1.1.3 =
* Minor bug fixes and performance improvements.

= 1.1.2 =
* Feature: **Webhooks & Integrations** - Added support for Slack, Discord, and Zapier notifications.
* Feature: **Guest Access** - Implemented secure cookie-based access for guests (no login required to view own ticket).
* Improvement: Updated email notifications to include direct frontend links to tickets.
* Improvement: Added "Webhooks & Integrations" tab in Settings.

= 1.1.1 =
* Improvement: Updated documentation and README.
* Improvement: Minor UI tweaks.

= 1.1.0 =
* Feature: Added **Supabase Realtime Integration** for instant chat updates without polling.
* Feature: Hybrid architecture (WordPress DB for storage + Supabase for realtime sync).
* Improvement: Added "Test Connection" buttons for Supabase (Read/Write).
* Improvement: Secure Service Role handling in settings.
* Fix: Solved profile image upload issues.
* Fix: Resolved redirect 404 error after ticket submission.

= 1.0.8 =
* Feature: Added Advanced Search Filter in Tickets List (Search by Name or Ticket ID).
* Feature: Introduced Custom Fields Builder for ticket submission forms.
* Improvement: Enhanced ticket list query performance.

= 1.0.7 ==
* Fix: Browser notifications now trigger even when the tab is active.

= 1.0.6 =
* Feature: Added Profile Tab in the chat sidebar.
* Feature: Implemented Profile Image Upload functionality.
* UI: Improved sidebar navigation.
* UI: Added camera overlay effect for profile image upload.

= 1.0.5 =
* Feature: Added automatic ticket assignment to agents based on issue category.
* Feature: Added "Agent Performance" widget to the admin dashboard.
* Feature: Implemented email notifications for agents.
* Improvement: Enhanced dashboard UI.

= 1.0.4 =
* UI Improvements: Enhanced login form styling.
* Security: Improved password handling for guest ticket submissions.
* Fix: Minor bug fixes and performance improvements.

= 1.0.3 =
* UI Improvements: Updated ticket form layout and styling.
* Added guest ticket submission with auto-account creation.
* Improved email piping and notifications.

= 1.0.0 =
* Initial release.
