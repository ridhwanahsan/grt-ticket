# GRT Ticket

A complete, professional WordPress support ticket system with real-time chat functionality, designed for seamless customer support.

## Description

GRT Ticket is a powerful support ticket management plugin for WordPress that provides a seamless experience for both administrators and users. Unlike standard contact forms, GRT Ticket offers a full-fledged ticketing system with a modern, full-screen chat interface, real-time message updates, and category-based ticket organization.

It is built with performance and modularity in mind, ensuring that assets are only loaded where necessary.

## Features

- **User-Friendly Ticket Submission**: Users can submit tickets through a beautiful, responsive form with custom categories and icons.
- **Real-Time Chat Interface**: AJAX-powered chat system with automatic polling for new messages, ensuring live conversation capabilities.
- **Full-Screen Experience**: A distraction-free, full-screen overlay for the chat interface on both frontend and admin panels.
- **Guest Ticket Submission**: Auto-account creation for guest users with secure password handling.
- **Smart Asset Loading**: Modular CSS and JS files are loaded only on relevant pages for optimal performance.
- **Ticket Management**: Comprehensive admin panel to view, manage, and reply to support tickets.
- **Status Tracking**: Clear status indicators (Open, Solved, Closed) for efficient workflow management.
- **Priority System**: Prioritize tickets with Low, Medium, or High priority levels to handle urgent issues first.
- **File Attachments**: Users and admins can share Images (JPG, PNG, GIF) and PDF files directly within the chat.
- **Analytics Dashboard**: A dedicated admin dashboard to visualize ticket volume, resolution time, and user satisfaction ratings.
- **User Ratings**: Users can rate the support experience and provide feedback after a ticket is solved.
- **Canned Responses**: Admins can save and insert common replies to respond faster.
- **Twilio Integration**: Send automated notifications via WhatsApp and SMS to admins when new tickets are created.
- **Direct Contact Options**: Offer direct "Call Us" and "SMS Us" buttons within the chat interface for immediate support.
- **Responsive Design**: Fully responsive interface that works perfectly on desktop, tablet, and mobile devices.
- **Security First**: Built with WordPress security best practices (nonces, input sanitization, output escaping).

## Installation
1. **Upload**: Upload the `grt-ticket` folder to the `/wp-content/plugins/` directory.
2. **Activate**: Activate the plugin through the 'Plugins' menu in WordPress.
3. **Configure**: Go to **GRT Ticket > Settings** to configure your options (Categories, Twilio, Direct Contact).
4. **Deploy**: Add the `[grt_ticket_form]` shortcode to any page to create the support portal.

## Usage

### Public Side (For Users)

To display the ticket submission form and user dashboard, add the following shortcode to any page:

```shortcode
[grt_ticket_form]
```

- **Guest Users**: Will see a login prompt or a simplified submission form (if enabled).
- **Logged-in Users**: Will see their profile, a list of their recent tickets, and the submission form.
- **Chat Access**: Clicking on a ticket opens the full-screen live chat interface.

### Admin Side (For Support Agents)

Navigate to **GRT Ticket** in your WordPress admin menu.

1. **Dashboard**: Get an overview of your support performance with real-time statistics and rating breakdowns.
2. **Tickets List**: View all submitted tickets with their status, priority, user details, and category.
3. **Support Chat**: Select a ticket to enter the real-time chat interface.
   - Reply to users instantly using text or attachments.
   - Use **Canned Responses** for quick replies.
   - Mark tickets as **Solved** when the issue is resolved.
   - Delete spam or invalid tickets.
4. **Settings**: Customize the plugin behavior.

## Changelog

### 1.0.4
- UI Improvements: Enhanced login form styling.
- Security: Improved password handling for guest ticket submissions.
- Fix: Minor bug fixes and performance improvements.

### 1.0.3
- UI Improvements: Updated ticket form layout and styling.
- Added guest ticket submission with auto-account creation.
- Improved email piping and notifications.

### 1.0.0
- Initial release.
