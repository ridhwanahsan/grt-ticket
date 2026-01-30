# GRT Ticket

A complete, professional WordPress support ticket system with real-time chat functionality, designed for seamless customer support.

## Description

GRT Ticket is a powerful support ticket management plugin for WordPress that provides a seamless experience for both administrators and users. Unlike standard contact forms, GRT Ticket offers a full-fledged ticketing system with a modern, full-screen chat interface, real-time message updates, and category-based ticket organization.

It is built with performance and modularity in mind, ensuring that assets are only loaded where necessary.

## Features

- **User-Friendly Ticket Submission**: Users can submit tickets through a beautiful, responsive form with custom categories and icons.
- **Real-Time Chat Interface**: AJAX-powered chat system with automatic polling for new messages, ensuring live conversation capabilities.
- **Full-Screen Experience**: A distraction-free, full-screen overlay for the chat interface on both frontend and admin panels.
- **Smart Asset Loading**: Modular CSS and JS files are loaded only on relevant pages for optimal performance.
- **Ticket Management**: Comprehensive admin panel to view, manage, and reply to support tickets.
- **Status Tracking**: Clear status indicators (Open, Solved, Closed) for efficient workflow management.
- **File Attachments**: Users and admins can share images within the chat.
- **Twilio Integration**: Send automated notifications via WhatsApp and SMS to admins when new tickets are created.
- **Direct Contact Options**: Offer direct "Call Us" and "SMS Us" buttons within the chat interface for immediate support.
- **Responsive Design**: Fully responsive interface that works perfectly on desktop, tablet, and mobile devices.
- **Security First**: Built with WordPress security best practices (nonces, input sanitization, output escaping).

## Installation

1. **Upload**: Upload the `grt-ticket` folder to the `/wp-content/plugins/` directory.
2. **Activate**: Activate the plugin through the 'Plugins' menu in WordPress.
3. **Configure**: Go to **GRT Ticket > Settings** to configure your options (Categories, Twilio, Direct Contact).
4. **Deploy**: Add the `[grt_ticket]` shortcode to any page to create the support portal.

## Usage

### Public Side (For Users)

To display the ticket submission form and user dashboard, add the following shortcode to any page:

```shortcode
[grt_ticket]
```

- **Guest Users**: Will see a login prompt or a simplified submission form (if enabled).
- **Logged-in Users**: Will see their profile, a list of their recent tickets, and the submission form.
- **Chat Access**: Clicking on a ticket opens the full-screen live chat interface.

### Admin Side (For Support Agents)

Navigate to **GRT Ticket** in your WordPress admin menu.

1. **Tickets List**: View all submitted tickets with their status, user details, and category.
2. **Support Chat**: Select a ticket to enter the real-time chat interface.
   - Reply to users instantly.
   - Mark tickets as **Solved** when the issue is resolved.
   - Delete spam or invalid tickets.
3. **Settings**: Customize the plugin behavior.

## Configuration

Configure the plugin in **GRT Ticket > Settings**:

| Setting | Description | Default |
|---------|-------------|---------|
| **Issue Categories** | Define support categories (e.g., Bug Report, Feature Request). You can also upload icons for each category. | Pre-populated list |
| **Admin Display Name** | The name displayed to users when an admin replies (e.g., "Support Team"). | Support Team |
| **Tickets Per Page** | Number of tickets to show in the admin list pagination. | 20 |
| **Chat Polling Interval** | How frequently (in ms) the chat checks for new messages. Lower values = faster updates but higher server load. | 3000 (3 seconds) |

## Project Structure

The plugin follows a modular architecture for better maintainability and performance:

```text
grt-ticket/
├── admin/                  # Admin-specific functionality
│   ├── css/                # Modular CSS files (tickets-list, chat-interface, settings)
│   ├── js/                 # Modular JS files (tickets-list, chat-interface, settings)
│   ├── partials/           # PHP views for admin screens
│   └── class-grt-ticket-admin.php
├── public/                 # Public-facing functionality
│   ├── css/                # Modular CSS files (ticket-form, chat-interface)
│   ├── js/                 # Modular JS files (ticket-form, chat-interface)
│   ├── partials/           # PHP views for public shortcodes
│   └── class-grt-ticket-public.php
├── includes/               # Core plugin logic
│   ├── class-grt-ticket-database.php  # Custom table management
│   ├── class-grt-ticket-ajax.php      # AJAX handlers for chat
│   └── ...
└── languages/              # Translation files
```

### Recent Updates (Modularization)
As of the latest update, monolithic CSS/JS files have been split into modular assets. This means `chat-interface.css` is only loaded when the chat is active, reducing page weight on other screens.

## Developer Notes

- **Database**: The plugin creates two custom tables on activation:
  - `wp_grt_tickets`: Stores ticket metadata (user, title, status).
  - `wp_grt_messages`: Stores chat messages and attachment URLs.
- **AJAX**: All chat interactions use `admin-ajax.php` with secured nonces.
- **Extensibility**: The plugin uses standard WordPress hooks. You can customize the `grt_ticket_categories` option directly via filter if needed.

## Changelog

### 1.1.0
- **Improvement**: Modularized CSS and JS assets for better performance.
- **Fix**: Solved layout issues in the live chat interface (z-index and container positioning).
- **Update**: Enhanced README documentation.

### 1.0.0
- Initial release.
- Ticket submission system.
- Real-time chat functionality.
- Admin management panel.

## License

This plugin is licensed under the GPL v2 or later.
