# GRT Ticket - Professional WordPress Support Ticket System

**Version:** 1.0.4  
**Requires at least:** 5.0  
**Tested up to:** 6.4  
**Requires PHP:** 7.4  
**License:** GPLv2 or later  

A complete, professional support ticket system with real-time chat functionality, designed for seamless customer support directly within your WordPress site.

## Description

GRT Ticket is a powerful, modern support ticket management plugin for WordPress that bridges the gap between simple contact forms and complex helpdesk software. It provides a **full-screen, real-time chat interface** for users and admins, making support feel personal and immediate.

Unlike standard ticketing plugins, GRT Ticket offers a distraction-free experience with a beautiful UI, AJAX-powered live updates, and smart features like guest submission and direct contact options.

<img width="1917" height="904" alt="image" src="https://github.com/user-attachments/assets/9ac1849a-40e4-47c5-b864-ed1cc7671070" />
<img width="1448" height="394" alt="image" src="https://github.com/user-attachments/assets/b5f15e43-3eb0-4204-98a3-b4d06f56709c" />
<img width="1880" height="591" alt="image" src="https://github.com/user-attachments/assets/6ddb5ea5-174a-4333-861f-85c773ce9671" />
<img width="1705" height="539" alt="image" src="https://github.com/user-attachments/assets/14054b67-c991-4a5a-9903-bd89131ab63d" />
<img width="853" height="793" alt="image" src="https://github.com/user-attachments/assets/e0344a5e-f288-436d-a81f-f9bef8d11f9f" />

## 🚀 Key Features

### 🎨 User Experience (Frontend)
*   **Full-Screen Live Chat**: A distraction-free, WhatsApp-like chat interface for real-time communication.
*   **Modern Ticket Form**: Beautifully designed submission form with custom category icons and validation.
*   **Guest Ticket Submission**: Users don't need an account to submit tickets. The system automatically creates an account and handles secure password generation.
*   **User Dashboard**: Logged-in users can view their profile, track recent tickets, and see status updates.
*   **File Attachments**: Support for image (JPG, PNG, GIF) and PDF uploads directly within the chat.
*   **Responsive Design**: Fully optimized for mobile, tablet, and desktop devices.
*   **Direct Contact Buttons**: Integrated "Call Us" and "SMS Us" buttons within the chat for immediate escalation.
*   **User Ratings**: Built-in feedback system allowing users to rate their support experience (1-5 stars).
*   **Clean Interface**: Automatically hides the WordPress Admin Bar for non-admin users to maintain a professional app-like feel.

### 🛠️ Ticket Management (Admin)
*   **Admin Dashboard**: Visual overview of support performance, including ticket volume, open/closed counts, and average user ratings.
*   **Ticket List**: Filterable list of all tickets with status (Open, Solved, Closed), priority, and category.
*   **Priority System**: Categorize issues by Low, Medium, or High priority.
*   **Canned Responses**: Create and manage pre-saved replies to answer common questions instantly.
*   **Admin Chat Interface**: Reply to tickets using the same beautiful full-screen interface as users.
*   **Status Management**: Easily mark tickets as Solved or Closed.

### ⚙️ Integrations & Settings
*   **Twilio Integration**: Get notified via SMS or WhatsApp when a new ticket is created (requires Twilio API credentials).
*   **Email Piping**: (Beta) Reply to ticket notifications via email to update the chat.
*   **Custom Categories**: Define your own support categories with custom icons.
*   **Smart Asset Loading**: CSS and JS only load on plugin pages, ensuring no impact on your site's speed.

## 📦 Installation

1.  **Upload**: Upload the `grt-ticket` folder to the `/wp-content/plugins/` directory.
2.  **Activate**: Activate the plugin through the 'Plugins' menu in WordPress.
3.  **Setup Page**: Create a new page (e.g., "Support") and add the shortcode `[grt_ticket]`.
4.  **Configure**: Go to **GRT Ticket > Settings** in your dashboard to configure:
    *   Support Categories
    *   Twilio API (Optional)
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
