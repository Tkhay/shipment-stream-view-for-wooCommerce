# Shipment Stream View for WooCommerce

A modern, visual order tracking system for WooCommerce that displays order status with an elegant progress bar.

![WordPress Version](https://img.shields.io/badge/WordPress-6.0%2B-blue)
![WooCommerce Version](https://img.shields.io/badge/WooCommerce-8.0%2B-purple)
![PHP Version](https://img.shields.io/badge/PHP-7.2%2B-777BB4)
![License](https://img.shields.io/badge/License-GPL%20v2-green)

## 🚀 Features

- **Visual Progress Tracking** - Beautiful progress bar showing the order journey
- **Customizable Status Steps** - Drag and drop to reorder your tracking milestones
- **Exception Handling** - Special alerts for order issues (on-hold, cancelled, etc.)
- **Easy Integration** - Simple `[ssvfw_order_tracker]` shortcode
- **Responsive Design** - Perfect on desktop, tablet, and mobile devices
- **WooCommerce Compatible** - Works seamlessly with existing WooCommerce setup
- **HPOS Ready** - Full support for High-Performance Order Storage
- **Design Customization** - Choose your brand colors and fonts
- **Milestone & Exception Types** - Categorize statuses as normal progress or special alerts

## 📦 Installation

### Requirements

- WordPress 6.0 or higher
- WooCommerce 8.0 or higher
- PHP 7.2 or higher
- Node.js and npm (for development)

### For Users

1. Download the latest release from the [releases page](https://github.com/Tkhay/shipment-stream-view-for-wooCommerce/releases)
2. Upload to WordPress via **Plugins → Add New → Upload Plugin**
3. Activate the plugin
4. Go to **Shipment Stream** to configure tracking steps
5. Add `[ssvfww_order_tracker]` shortcode to any page

### For Developers

```bash
# Clone the repository
git clone https://github.com/Tkhay/shipment-stream-view-for-woocommerce.git

# Navigate to plugin directory
cd shipment-stream-view-for-woocommerce

# Install dependencies
npm install

# Build assets
npm run build

# For development with watch mode
npm run start
```

## 🎯 Usage

### Basic Setup

1. Navigate to **Shipment Stream** in your WordPress admin
2. Configure your tracking steps:
   - Drag and drop to reorder
   - Edit labels to match your workflow
   - Set type: **Milestone** (progress bar) or **Exception** (alert)
3. Add the shortcode to a page:
   ```
   [ssvfww_order_tracker]
   ```

### Customization

#### Design Settings

Go to **Shipment Stream → Settings** to customize:

- Primary brand color
- Font family
- Theme color usage

#### Status Configuration

**Milestones** - Normal progression (shown on progress bar):

- Order Received
- Processing
- Shipped
- Delivered

**Exceptions** - Special situations (shown as alerts):

- On Hold
- Cancelled
- Failed

## 🛠️ Development

### Project Structure

```
shipment-stream-view-for-woocommerce/
├── build/                    # Compiled assets (gitignored)
├── includes/
│   ├── admin-settings.php   # Admin interface & REST API
│   └── frontend-tracker.php # Customer-facing tracker
├── src/
│   ├── index.js            # Admin React app
│   ├── style.scss          # Admin styles
│   ├── frontend.js         # Frontend scripts (minimal)
│   ├── frontend.scss       # Frontend styles
│   ├── settings.js         # Settings page React app
│   └── settings.scss       # Settings page styles
├── shipment-stream-view-for-woocommerce.php  # Main plugin file
├── readme.txt              # WordPress.org readme
├── README.md              # This file
└── package.json           # Node dependencies
```

### Build Commands

```bash
# Development build with watch mode
npm run start

# Production build (minified)
npm run build

# Lint JS files
npm run lint:js

# Format code
npm run format
```

### Technologies Used

- **Frontend**: React (via @wordpress/element)
- **Build Tool**: @wordpress/scripts (Webpack)
- **Styling**: SCSS with modern CSS features
- **Drag & Drop**: @hello-pangea/dnd
- **API**: WordPress REST API

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-new-feature`
3. Make your changes
4. Build and test: `npm run build`
5. Commit your changes: `git commit -am 'Add some feature'`
6. Push to the branch: `git push origin feature/my-new-feature`
7. Submit a pull request

### Coding Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Use meaningful variable and function names
- Comment complex logic
- Prefix all functions/classes with `ssvfww_`
- Test on latest WordPress and WooCommerce versions

## 📝 Changelog

### 1.0.0 - 2025-12-25

- Initial release
- Visual order tracking with progress bar
- Drag-and-drop milestone management
- Exception handling for special statuses
- Responsive design
- HPOS compatibility
- Customizable design settings

## 🐛 Bug Reports & Feature Requests

Found a bug or have a feature request? Please [open an issue](https://github.com/Tkhay/shipment-stream-view-for-woocommerce/issues) with:

- WordPress version
- WooCommerce version
- PHP version
- Detailed description of the issue
- Steps to reproduce (for bugs)

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Shipment Stream View for WooCommerce
Copyright (C) 2025 Tieku Asare

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👨‍💻 Author

**Tieku Asare**

- Website: [tiekuasare.com](https://www.tiekuasare.com)
- Email: dev@tiekuasare.com

## 💖 Support

If you find this plugin helpful, consider:

- ⭐ Starring the repository
- ☕ [Buy me a coffee](https://buymeacoffee.com/tiekuasare)
- 📝 Writing a review on WordPress.org
- 🐛 Reporting bugs or suggesting features

---

Built with ❤️ for the WordPress community
