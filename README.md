# GRT Ticket - Professional WordPress Support Ticket System

**Version:** 1.1.1  
**Requires at least:** 5.0  
**Tested up to:** 6.4  
**Requires PHP:** 7.4  
**License:** GPLv2 or later  

A complete, professional support ticket system with real-time chat functionality, designed for seamless customer support directly within your WordPress site.

## Description

GRT Ticket is a powerful, modern support ticket management plugin for WordPress that bridges the gap between simple contact forms and complex helpdesk software. It provides a **full-screen, real-time chat interface** for users and admins, making support feel personal and immediate.

Unlike standard ticketing plugins, GRT Ticket offers a distraction-free experience with a beautiful UI, AJAX-powered live updates, and smart features like guest submission and direct contact options.

<p align="center">
  <img src="https://github.com/user-attachments/assets/9ac1849a-40e4-47c5-b864-ed1cc7671070" width="100%" alt="GRT Ticket Dashboard" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);" />
  <br /><br />
  <img src="https://github.com/user-attachments/assets/b5f15e43-3eb0-4204-98a3-b4d06f56709c" width="100%" alt="Ticket List" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);" />
  <br /><br />
  <img src="https://github.com/user-attachments/assets/6ddb5ea5-174a-4333-861f-85c773ce9671" width="100%" alt="Chat Interface" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);" />
  <br /><br />
  <img src="https://github.com/user-attachments/assets/14054b67-c991-4a5a-9903-bd89131ab63d" width="100%" alt="User View" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);" />
  <br /><br />
  <img src="https://github.com/user-attachments/assets/e0344a5e-f288-436d-a81f-f9bef8d11f9f" width="48%" alt="Mobile View" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);" />
<br /><br />
  <img width="1272" height="858" alt="image" src="https://github.com/user-attachments/assets/d503a9a8-d965-4b80-9c15-ba61488a108b" />

</p>

## 🚀 Key Features

### ⚡ Supabase Integration (New!)
*   **True Real-Time Chat**: Integrated with Supabase Realtime DB for instant, zero-latency messaging.
*   **Hybrid Architecture**: Keeps your WordPress database as the main source of truth while using Supabase for high-speed message delivery.
*   **Zero Server Load**: Offloads chat polling to Supabase, significantly reducing the load on your WordPress server.

### 🚀 Easy Setup
*   **Automatic Page Creation**: The plugin automatically creates a "GRT Ticket" page with the required shortcode upon activation, so you're ready to go instantly.

### 🎨 User Experience (Frontend)
*   **Custom Fields Builder**: Admins can create dynamic forms with Text, Number, Date, URL, and Select fields to capture specific ticket details.
*   **Full-Screen Live Chat**: A distraction-free, WhatsApp-like chat interface for real-time communication.
*   **Modern Ticket Form**: Beautifully designed submission form with custom category icons and validation.
*   **Guest Ticket Submission**: Users don't need an account to submit tickets. The system automatically creates an account and handles secure password generation.
*   **User Dashboard**: Logged-in users can view their profile, track recent tickets, and see status updates.
*   **User Profile & Avatars**: Users can update their profile information and upload custom avatars with a modern camera overlay.
*   **File Attachments**: Support for image (JPG, PNG, GIF) and PDF uploads directly within the chat.
*   **Responsive Design**: Fully optimized for mobile, tablet, and desktop devices.
*   **Direct Contact Buttons**: Integrated "Call Us" and "SMS Us" buttons within the chat for immediate escalation.
*   **User Ratings**: Built-in feedback system allowing users to rate their support experience (1-5 stars).
*   **Clean Interface**: Automatically hides the WordPress Admin Bar for non-admin users to maintain a professional app-like feel.
  
### 🛠️ Ticket Management (Admin)
*   **Advanced Search**: Quickly find tickets by User Name or Ticket ID using the smart search filter.
*   **Admin Dashboard**: Visual overview of support performance, including ticket volume, open/closed counts, and average user ratings.
*   **Agent Performance**: Track individual agent performance with detailed stats on assigned and solved tickets.
*   **Auto-Assignment**: Automatically assign tickets to specific agents based on the issue category.
*   **Ticket List**: Filterable list of all tickets with status (Open, Solved, Closed), priority, and category.
*   **Priority System**: Categorize issues by Low, Medium, or High priority.
*   **Canned Responses**: Create and manage pre-saved replies to answer common questions instantly.
*   **Admin Chat Interface**: Reply to tickets using the same beautiful full-screen interface as users.
*   **Status Management**: Easily mark tickets as Solved or Closed.

### ⚙️ Integrations & Settings
*   **Agent Notifications**: Automatically notify agents via email when a new ticket is assigned to them.
*   **Custom Categories**: Define your own support categories with custom icons.
*   **Smart Asset Loading**: CSS and JS only load on plugin pages, ensuring no impact on your site's speed.

### 💎 Advanced & Premium Capabilities (Included)
Unlike other plugins that charge for essential features, GRT Ticket includes these powerful capabilities out of the box:
*   **Email Piping (Beta)**: Two-way sync between email and chat. Reply to ticket notifications via email to automatically update the chat.
*   **Twilio Integration**: Get notified via SMS or WhatsApp when a new ticket is created (requires Twilio API credentials).
*   **Auto-Assignment**: Smart routing of tickets to specific agents based on the issue category.
*   **Agent Performance Analytics**: Detailed insights into agent productivity with visual stats on assigned and solved tickets.
*   **Real-time Chat**: Full-screen, real-time communication interface for users and admins without relying on external services.
*   **Guest Ticket Submission**: Seamless support for non-logged-in users with automatic secure account creation.

## ⚡ Supabase Configuration (Real-Time Chat)

To enable ultra-fast real-time chat, GRT Ticket integrates with Supabase. Follow these steps to get your credentials:

1.  **Create a Project**: Go to [Supabase](https://supabase.com) and create a new project.
2.  **Get Credentials**:
    *   Go to **Project Settings** (Cog icon) > **API**.
    *   **Supabase URL**: Copy the "Project URL".
    *   **Supabase Anon Key**: Copy the "anon" / "public" key.
    *   **Supabase Service Role (Secret)**: Copy the "service_role" / "secret" key.
3.  **Configure Plugin**:
    *   Go to your WordPress Admin > **GRT Ticket** > **Settings**.
    *   Scroll down to **Supabase Configuration**.
    *   Enter the URL, Anon Key, and Service Role Secret.
    *   Click **Test Connection** to verify everything is working.
    *   Check **Enable Supabase Realtime** and Save Settings.

4.  **Create Database Table**:
    *   Go to your Supabase Project > **SQL Editor**.
    *   Click **New Query**.
    *   Paste and run the following SQL command to create the required table:

    ```sql
    create table public.grt_messages ( 
      id bigint not null, 
      ticket_id bigint not null, 
      sender_type text not null, 
      sender_name text not null, 
      message text null, 
      attachment_url text null, 
      is_internal integer null default 0, 
      created_at timestamp with time zone null default now(), 
      constraint grt_messages_pkey primary key (id) 
    ) TABLESPACE pg_default;
    ```
    *   This table stores temporary real-time messages. Your WordPress database remains the primary storage.

## 📦 Installation

1.  **Upload**: Upload the `grt-ticket` folder to the `/wp-content/plugins/` directory.
2.  **Activate**: Activate the plugin through the 'Plugins' menu in WordPress.
3.  **Setup Page**: A "GRT Ticket" page is automatically created for you! You can also manually add the shortcode `[grt_ticket]` to any other page.
4.  **Configure**: Go to **GRT Ticket > Settings** in your dashboard to configure:
    *   Support Categories
    *   Twilio API (Optional)
    *   Supabase Realtime (Optional but Recommended)
    *   Direct Contact Numbers
    *   Chat Poll Interval

## 💻 Usage

### Displaying the Support Portal
Add the following shortcode to any page to display the ticket form and user dashboard:

```shortcode
[grt_ticket]
```

*   **Guest Users**: Will see the ticket submission form and a "Login" tab.
*   **Logged-in Users**: Will see their recent tickets list and profile details.

### Managing Tickets
1.  Navigate to **GRT Ticket > Tickets** in the WordPress Admin.
2.  Click on a ticket to open the **Support Chat**.
3.  Type your reply, attach files, or use a **Canned Response**.
4.  Update the ticket status to **Solved** when finished.

## 📋 Changelog

### 1.1.1
*   **Feature**: Automatic creation of the support portal page upon activation.
*   **Improvement**: Removed debug console logs for a cleaner production experience.

### 1.1.0
*   **New Feature**: Supabase Realtime Integration! Now you can use Supabase as a real-time engine for instant chat updates.
*   **Feature**: Added "Service Role" support for secure server-side message syncing.
*   **Feature**: Added "Test Connection" button in settings to verify Supabase credentials.
*   **Improvement**: Hybrid architecture ensures all data remains safely stored in your WordPress database while using Supabase for speed.

### 1.0.8
*   **Feature**: Added Advanced Search Filter in Tickets List (Search by Name or Ticket ID).
*   **Feature**: Introduced Custom Fields Builder for ticket submission forms.
*   **Improvement**: Enhanced ticket list query performance.

### 1.0.7
*   **Fix**: Browser notifications now trigger even when the tab/window is active/focused.

### 1.0.6
*   **Feature**: Added Profile Tab in the chat sidebar for easy access to user information.
*   **Feature**: Implemented Profile Image Upload functionality allowing users to set their own avatars.
*   **UI**: Improved sidebar navigation with tabs for "Tickets" and "Profile".
*   **UI**: Added camera overlay effect for profile image upload interaction.

### 1.0.5
*   **Feature**: Added automatic ticket assignment to agents based on issue category.
*   **Feature**: Added "Agent Performance" widget to the admin dashboard showing assigned and solved ticket counts.
*   **Feature**: Implemented email notifications for agents when tickets are assigned to them.
*   **Improvement**: Enhanced dashboard UI with better agent stats visualization.

### 1.0.4
*   **New**: Added functionality to hide WordPress Admin Bar for non-admin users.
*   **Improvement**: Enhanced CSS focus and hover states for better accessibility and visual feedback (Purple Theme).
*   **Fix**: Resolved CSS conflicts with some themes overriding button styles.
*   **Security**: Improved input validation and capability checks.

### 1.0.3
*   **Feature**: Added Guest Ticket Submission with auto-account creation.
*   **Feature**: Introduced Twilio integration for SMS/WhatsApp notifications.
*   **Update**: Refined email notification templates.

### 1.0.0
*   Initial Release.

## ❓ Frequently Asked Questions

**Q: Can I customize the colors?**  
A: The plugin currently uses a modern purple/blue gradient theme (`#667eea` to `#764ba2`). You can override these in your theme's CSS if needed.

**Q: Does it work with any theme?**  
A: Yes, GRT Ticket is designed to work independently of your theme's styling, using its own scoped CSS.

**Q: Where are files stored?**  
A: Uploaded files are securely stored in your WordPress Media Library and attached to the ticket.

**Q: Do I need Supabase for the plugin to work?**
A: No! Supabase is optional. If you don't enable it, the plugin will use standard AJAX polling (checking for messages every few seconds) which works on any hosting environment.
