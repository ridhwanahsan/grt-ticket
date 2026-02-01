=== GRT Ticket ===
Contributors: ridhwanahsan
Tags: support, ticket, chat, email piping, helpdesk, supabase, realtime
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

GRT Ticket is a complete support ticket system for WordPress, providing real-time chat functionality, email piping, and seamless ticket management. Users can create tickets via the website, reply directly through email, and have two-way conversation sync between chat and email. Now with **Supabase Realtime** integration for instant messaging!

== Features ==

* **Supabase Integration (New!)**: True real-time chat powered by Supabase Realtime DB (optional)
* Create and manage tickets from WordPress admin and frontend
* Real-time chat between users and admins
* **Custom Fields Builder**: Create custom forms with Text, Textarea, Number, Email, URL, Select, and Date fields
* **Advanced Search**: Filter tickets by User Name or Ticket ID instantly
* Guest Ticket Submission: Users can submit tickets without an account (auto-creates account)
* User Profile: Users can manage their profile and upload avatars
* Auto-Assignment: Automatically assign tickets to agents based on category
* Agent Performance: View stats on agent performance and ticket resolution
* Email Piping: User replies via email are automatically added to ticket chat
* Email Notifications: Chat replies are sent to user email
* IMAP/SMTP settings configurable via plugin settings
* Secure input handling and WordPress coding standards compliant
* Fully GPL-2.0 compatible

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
5. Enable "Realtime Chat" in plugin settings.

== Changelog ==

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

= 1.0.7 =
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
