# AGENTS.md — FrontBlocks

Guidelines for AI coding agents working on this WordPress plugin.

## Project Overview

**FrontBlocks** is a free WordPress plugin that extends Gutenberg and GeneratePress blocks. The PRO companion lives at `wp-content/plugins/frontblocks-pro/`.

- **PHP minimum:** 7.0
- **WordPress minimum:** 5.5
- **Namespace:** `FrontBlocks\`
- **Prefix (constants/functions):** `FRBL_` / `frbl_` / `frontblocks_`
- **Text domain:** `frontblocks`

## Language

**Everything must be written in English** — no exceptions:
- All code: variable names, function names, class names, constants.
- All comments: inline, docblocks, and `/* translators: */` notes.
- All documentation: `AGENTS.md`, `CLAUDE.md`, `/docs/`, `readme.md`, commit messages, PR descriptions.
- User-facing strings in `__()` / `esc_html__()` etc. are in English; translations are handled by `.po`/`.mo` files in `/languages/`.

## Directory Structure

```
frontblocks/
├── frontblocks.php          # Plugin entry point, constants
├── includes/
│   ├── Plugin_Main.php      # Singleton loader
│   ├── Admin/               # Admin-only classes (Settings, etc.)
│   └── Frontend/            # Frontend feature classes
├── assets/
│   ├── admin/               # Settings CSS (Tailwind)
│   ├── carousel/
│   ├── animations/
│   ├── sticky/
│   ├── gallery/
│   ├── post/
│   └── headline/
├── docs/                    # Documentation (linked from readme.md)
├── tests/
├── vendor/                  # Composer dependencies (do not edit)
└── node_modules/            # npm dependencies (do not edit)
```

## Code Style

### PHP
- Follow **WordPress PHP Coding Standards** (`phpcs.xml.dist` is the source of truth).
- Use **tabs** for indentation, never spaces.
- Use **Yoda conditions** always: `if ( true === $var )`.
- Inline comments: start with a capital letter, end with a period. Comment chunks of functionality, not individual lines.
- All globals (functions, constants, hooks) must use one of the registered prefixes: `FRBL_`, `frbl_`, `FrontBlocks`, `frontblocks_`.
- PSR-4 autoloading is active — class file names follow PSR-4, not WordPress hyphenated conventions.

### JavaScript
- **No jQuery.** Use Vanilla JavaScript.
- JSX files in `assets/` are compiled via Babel — edit the `.jsx` source, not the compiled output.

### CSS
- Admin CSS uses Tailwind CSS 3 with the `tw-` prefix, scoped to `.frbl-settings-wrapper`.
- Build with `npm run build:css`.

### Naming: Brand Capitalization
- Always write **FrontBlocks** (capital F and B) in comments, docs, and user-facing strings.
- File names, function prefixes, text domains, and CSS classes use lowercase: `frontblocks`, `frontblocks_`, `frontblocks-`.

## Development Commands

```bash
# PHP linting and formatting
composer lint          # phpcs — check coding standards
composer format        # phpcbf — auto-fix coding standards
composer phpstan       # static analysis

# JavaScript — compile JSX to JS
npm run build              # build all assets
npm run build:carousel
npm run build:animations
npm run build:sticky
npm run build:gallery
npm run build:post
npm run build:headline

# CSS (Tailwind via PostCSS)
npm run build:css          # compile once
npm run watch:css          # watch mode
```

Always run `composer lint` and `composer phpstan` before considering PHP work done.

## WordPress Best Practices

- Use WordPress hooks (actions/filters) — never modify core or plugin files directly.
- Use `prepare()` for all database queries via `$wpdb`.
- Sanitize all input; escape all output with the appropriate WordPress escaping function.
- Use nonces for form submissions and AJAX requests.
- Use WordPress capabilities for authorization checks.

## Architecture

### Plugin Initialization Flow

1. **`frontblocks.php`** — entry point; defines constants (`FRBL_VERSION`, `FRBL_PLUGIN`, `FRBL_PLUGIN_URL`, `FRBL_PLUGIN_PATH`), loads Composer autoloader, hooks `plugins_loaded` → singleton `FrontBlocks\Plugin_Main::get_instance()`.
2. **`includes/Plugin_Main.php`** — singleton; `load_modules()` instantiates all frontend and admin feature classes.
3. **`includes/Frontend/*.php`** — each feature is a self-contained class; constructor calls `init_hooks()` which registers WordPress actions/filters for asset enqueuing and block filtering.
4. **`includes/Admin/Settings.php`** — settings page at `themes.php?page=frontblocks-settings`.

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

## CI / GitHub Actions

- `phplint.yml` — runs PHPCS on push/PR
- `deploy.yml` — release automation
- `playground-preview.yml` — spins up WP Playground preview for PRs

## Adding New Features

1. Create a class in `includes/Admin/` (admin-only) or `includes/Frontend/` (frontend).
2. Register it in `Plugin_Main.php`.
3. Add assets in the matching `assets/` subdirectory.
4. Update `readme.md` with the new feature.
5. Add documentation to `/docs/` and link it from `readme.md`.
6. If the new feature replaces functionality commonly provided by a third-party plugin, register that plugin via the `frontblocks_redundant_plugins` filter so the Redundant Plugins Notice can flag it — see `docs/REDUNDANT-PLUGINS.md`.

## What NOT to Do

- Do not edit files inside `vendor/` or `node_modules/`.
- Do not use jQuery.
- Do not skip `phpcs` or `phpstan` checks.
- Do not use global functions without the required prefix.
- Do not commit compiled JS/CSS without also committing the source files.
- Do not write code, comments, or documentation in any language other than English.
