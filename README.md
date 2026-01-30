# GRT Ticket

A complete WordPress support ticket system with real-time chat functionality.

## Description

GRT Ticket is a professional support ticket management plugin for WordPress that provides a seamless experience for both administrators and users. It features a modern, full-screen chat interface with real-time message updates.

## Features

- **Easy Ticket Submission**: Users can submit tickets through a beautiful full-screen interface with category selection
- **Real-Time Chat**: AJAX-powered chat system with automatic polling for new messages
- **Full-Screen Interface**: Modern, distraction-free chat experience for both admin and users
- **Ticket Management**: Comprehensive admin panel to manage all support tickets
- **Status Tracking**: Track ticket status (Open, Solved, Closed)
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Security First**: Built with WordPress security best practices (nonces, sanitization, escaping)
- **Translation Ready**: Fully internationalized and ready for translation

## Installation

1. Upload the `grt-ticket` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to GRT Ticket > Settings to configure your options
4. Add the `[grt_ticket]` shortcode to any page

## Usage

### Shortcode

Add the following shortcode to any page where you want to display the ticket submission form:

```
[grt_ticket]
```

### Admin Panel

Navigate to **GRT Ticket** in your WordPress admin menu to:

- View all submitted tickets
- Chat with users in real-time
- Mark tickets as solved
- Configure plugin settings

### Settings

Configure the following options in **GRT Ticket > Settings**:

- **Issue Categories**: Comma-separated list of ticket categories
- **Admin Display Name**: Name shown for admin messages in chat
- **Tickets Per Page**: Number of tickets to display per page
- **Chat Polling Interval**: How often to check for new messages (in milliseconds)

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

## Changelog

### 1.0.0
- Initial release
- Ticket submission system
- Real-time chat functionality
- Admin management panel
- Settings page

## Support

For support and feature requests, please use the support forum.

## License

This plugin is licensed under the GPL v2 or later.
