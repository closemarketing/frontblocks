# FrontBlocks Repository for WordPress Plugin

Repository for WordPress Plugin that add features to GeneratePress blocks ( GenerateBlocks).

## Functionalities

### GenerateBlocks Extensions
- **Container Edge Alignment** - Remove padding from left or right side to create edge-to-edge layouts

### Block Enhancements
- Animations
- Container Effects (Glass Effect / Glassmorphism)
- **FrontBlocks Hover Effects** - Smooth background image zoom on hover with configurable scale
- Carousel/Slider
- Native Gallery
- Sticky Columns
- Insert Post
- Counter Block
- Reading Time Block
- Reading Progress Bar
- Product Categories Block
- Gravity Forms Inline Layout
- Back Button
- Headline Marquee - Infinite scrolling marquee effect for headline/text blocks with customizable speed (Fast, Medium, Slow)
- Modern Settings Page with Card-based Layout
- Custom SVG Animations
- **Cookie Notice** - Configurable cookie consent banner (bar/box/popup) that gates Google Tag Manager, GA4, and supported tracking integrations behind consent, with Google Consent Mode v2 support for compatibility with Google Site Kit and other analytics plugins
- **Review Notice** - Dismissible admin notice inviting site owners to leave a WordPress.org review, shown 14 days after activation
- **Redundant Plugins Notice** - Persistent admin notice detecting third-party plugins made unnecessary by an active FrontBlocks feature (e.g. SVG upload plugins, GDPR cookie consent plugins), extensible via the `frontblocks_redundant_plugins` filter
- **Google Sign-In** - "Sign in / Sign up with Google" button for wp-login.php, WooCommerce My Account and Checkout, plus a shortcode and block, with server-side ID token verification
- **Table of Contents** - Accessible navigation block generated from the headings in the post, with collapsible and sticky options

### PRO Features (FrontBlocks PRO)
- **Custom Post Types Builder** - Create and manage custom post types with advanced configuration options:
  - Create custom post types with a simple interface
  - Configure post type behavior (Post or Page style)
  - Enable/disable categories taxonomy
  - Add custom meta fields (Text, Textarea, URL, Date, File, Number, Email)
  - Individual settings page for each custom post type
  - Delete custom post types easily

- **Full Page Scroll** - Create smooth fullpage scroll experiences:
  - Smooth section-by-section navigation
  - Automatic side navigation with dots
  - Mouse wheel control for precise scrolling
  - Tooltips on navigation dots
  - Fully responsive design
  - Vanilla JavaScript (no dependencies)

- **WooCommerce Enhancements**:
  - Enable Gutenberg in product editor
  - Simple prices for variable products
  - Block after add to cart button
  - Product description behavior options
  - Disable zoom on product images
  - Share buttons on product pages
  - Deactivate product tabs
  - Horizontal product form layout


## Documentation

- [Container Edge Alignment](./docs/CONTAINER-EDGE-ALIGNMENT.md) - Create asymmetric layouts with edge-to-edge content
- [FrontBlocks Hover Effects](./docs/HOVER-BG-SCALE.md) - Smooth background image zoom effect on hover
- [Full Page Scroll](./docs/FULLPAGE-SCROLL.md) - Smooth fullpage scroll with navigation
- [Full Page Implementation](./docs/FULLPAGE-IMPLEMENTATION-SUMMARY.md) - Technical implementation details
- [Changelog Full Page](./docs/CHANGELOG-FULLPAGE.md) - Full Page feature changelog
- [Carousel Pattern](./docs/CAROUSEL-PATTERN.md) - Hero carousel pattern with native WordPress blocks
- [Cookie Notice](./docs/COOKIE-NOTICE.md) - Consent banner and consent-gated tracking integrations
- [Review Notice](./docs/REVIEW-NOTICE.md) - Dismissible WordPress.org review prompt
- [Redundant Plugins Notice](./docs/REDUNDANT-PLUGINS.md) - Detects plugins made unnecessary by FrontBlocks features and how to register new pairings
- [Google Sign-In](./docs/GOOGLE-SIGNIN.md) - "Sign in / Sign up with Google" for wp-login.php and WooCommerce
- [Extending Settings Tabs](./docs/SETTINGS-TAB-EXTENSIONS.md) - Add a companion-plugin tab and matching panel to FrontBlocks settings
- [Table of Contents](./docs/TABLE-OF-CONTENTS.md) - Accessible, headings-based navigation block

## Release
Actions for making a release:
- Update readme Stable Tag, and Version.
- Update Plugin Header and constant.
- Execute `gulp compress`
- Create release in GitHub.
