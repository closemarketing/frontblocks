# FrontBlocks Development Guide

## Prerequisites

- Node.js (v16 or higher)
- npm (v8 or higher)
- Composer

## Installation

Install npm dependencies:

```bash
npm install
```

Install PHP dependencies:

```bash
composer install
```

## Building Assets

### Build All Assets

Build all JavaScript and CSS assets:

```bash
npm run build
```

### Build Individual Components

Build specific components:

```bash
# Build carousel
npm run build:carousel

# Build animations
npm run build:animations

# Build sticky column
npm run build:sticky

# Build gallery
npm run build:gallery

# Build insert post
npm run build:post

# Build headline
npm run build:headline

# Build product categories
npm run build:product-categories

# Build reading time
npm run build:reading-time

# Build admin CSS (Tailwind)
npm run build:css
```

### Watch Mode for CSS

For development, you can watch CSS changes:

```bash
npm run watch:css
```

## Admin Settings Page

The admin settings page uses **Tailwind CSS** with a custom prefix (`tw-`) to avoid conflicts with WordPress admin styles.

### Source Files

- **CSS Source**: `assets/admin/settings-src.css`
- **Compiled CSS**: `assets/admin/settings.css` (auto-generated, do not edit)
- **PHP Template**: `includes/Admin/Settings.php`
- **Tailwind Config**: `tailwind.config.js`
- **PostCSS Config**: `postcss.config.js`

### Modifying Admin Styles

1. Edit `assets/admin/settings-src.css`
2. Run `npm run build:css` or `npm run watch:css`
3. Test in WordPress admin

### Tailwind Configuration

The Tailwind configuration includes:

- **Prefix**: `tw-` (e.g., `tw-bg-blue-600`, `tw-text-gray-900`)
- **Important Selector**: `.frbl-settings-wrapper` (ensures styles override WordPress admin)
- **Content Paths**: Scans `includes/Admin/**/*.php` and `assets/admin/**/*.{js,jsx}`

## Code Quality

### PHP Linting

Run PHP CodeSniffer:

```bash
composer lint
```

Auto-fix PHP issues:

```bash
composer format
```

### PHPStan

Run static analysis:

```bash
composer phpstan
```

## File Structure

```
frontblocks/
├── assets/
│   ├── admin/
│   │   ├── settings-src.css  # Tailwind source
│   │   └── settings.css      # Compiled CSS
│   ├── animations/
│   ├── carousel/
│   ├── counter/
│   ├── gallery/
│   ├── headline/
│   ├── insert-post/
│   ├── product-categories/
│   ├── reading-time/
│   ├── sticky-column/
│   └── testimonials/
├── includes/
│   ├── Admin/
│   │   └── Settings.php      # Admin settings page
│   ├── Frontend/
│   └── Plugin_Main.php
├── vendor/                    # Composer dependencies
├── node_modules/              # npm dependencies
├── composer.json
├── package.json
├── tailwind.config.js
├── postcss.config.js
├── phpcs.xml.dist
└── phpstan.neon.dist
```

## Deployment

Before deploying, make sure to:

1. Run `npm run build` to compile all assets
2. Run `composer lint` to check code quality
3. Run `composer phpstan` to run static analysis
4. Test the plugin in a staging environment

## Notes

- The compiled `assets/admin/settings.css` file is tracked in Git to ensure the plugin works out of the box
- Always run `npm run build` before committing changes to admin styles
- The `.frbl-settings-wrapper` class is the main container and ensures Tailwind styles are scoped properly

