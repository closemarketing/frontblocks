# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**FrontBlocks** is a WordPress plugin that extends Gutenberg and GenerateBlocks with additional blocks and features. Developed by CLOSE. The main plugin lives at `wp-content/plugins/frontblocks/`, with a PRO companion at `wp-content/plugins/frontblocks-pro/`.

## Build Commands

Run from `wp-content/plugins/frontblocks/`:

```bash
# Build all JSX → JS modules
npm run build

# Build individual modules
npm run build:carousel
npm run build:animations
npm run build:sticky
npm run build:gallery
npm run build:post
npm run build:headline

# CSS (Tailwind via PostCSS)
npm run build:css      # compile once
npm run watch:css      # watch mode
```

PHP quality tools (via Composer):
```bash
composer lint        # PHP CodeSniffer (WordPress standards)
composer format      # PHPCBF auto-fix
composer phpstan     # Static analysis (level 1)
```

## Architecture

### Plugin Initialization Flow

1. **`frontblocks.php`** — entry point; defines constants (`FRBL_VERSION`, `FRBL_PLUGIN`, `FRBL_PLUGIN_URL`, `FRBL_PLUGIN_PATH`), loads Composer autoloader, hooks `plugins_loaded` → singleton `FrontBlocks\Plugin_Main::get_instance()`
2. **`includes/Plugin_Main.php`** — singleton; `load_modules()` instantiates all 21 feature classes plus Admin classes
3. **`includes/Frontend/*.php`** — each feature is a self-contained class; constructor calls `init_hooks()` which registers WordPress actions/filters for asset enqueuing and block filtering
4. **`includes/Admin/Settings.php`** — settings page at `themes.php?page=frontblocks-settings`

### Module Pattern

Every frontend feature follows the same pattern:
- Class in `includes/Frontend/FeatureName.php`
- Assets in `assets/feature-name/` (JSX source + compiled JS + CSS)
- Scripts/styles registered globally, enqueued only when the relevant block is present
- Block output modified via `render_block` or `render_block_generateblocks/*` filters

### Build Pipeline

- **JS/JSX:** Babel compiles `*-option.jsx` → `*.js` (ES5)
- **CSS:** PostCSS (Tailwind CSS 3 + Autoprefixer) compiles `assets/admin/settings-src.css` → `settings.css`
- **Tailwind prefix:** `tw-` (scoped to `.frbl-settings-wrapper`)

## Coding Standards

- **PHP:** WordPress coding standards; Yoda conditions always (`0 === $count`); tabs for indentation; PHP 7.4+ features (typed properties, arrow functions); `FrontBlocks\` namespace; `frbl_` function prefix; `frontblocks` text domain
- **JS:** Vanilla JavaScript — no jQuery
- **Security:** nonces with `wp_verify_nonce()`, `current_user_can()` capability checks, `wp_kses_post()` / `esc_html()` / `esc_url()` escaping, `sanitize_text_field()` / `absint()` sanitization
- **Comments:** Capital letter start, period at end; concise per-group, not per-line
- **Naming:** files lowercase `frontblocks-*`, CSS classes `frontblocks-*`, DB options stored under `frontblocks_settings` option key
- **Brand name:** Always write `FrontBlocks` (capital F and B) in human-readable contexts: comments, documentation, user-facing strings, class names, readme files. Use lowercase `frontblocks` for technical identifiers: file/directory names, text domains, function prefixes (`frontblocks_` or `frbl_`), CSS class prefixes (`frontblocks-`), DB option/table names.

## CI / GitHub Actions

- `phplint.yml` — runs PHPCS on push/PR
- `deploy.yml` — release automation
- `playground-preview.yml` — spins up WP Playground preview for PRs
