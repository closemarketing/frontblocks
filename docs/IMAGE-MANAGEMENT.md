# FrontBlocks: Image Management

**Plugin:** FrontBlocks for Gutenberg/GeneratePress
**Feature:** Image size control and automatic WebP/AVIF delivery
**Since:** 1.6.0

---

## Overview

Image Management is an optional module that gives site owners control over the
intermediate image sizes WordPress generates on upload, and automatically
serves modern image formats (WebP/AVIF) on the frontend when the server
supports them.

Enable it from **Appearance → FrontBlocks → Image Management**.

## Settings

| Field | Description |
| --- | --- |
| Registered image sizes table | Lists every registered size (core, theme, and plugin), its dimensions, crop mode, and an estimated disk-usage figure sampled from existing media. Each row can be disabled (stops future generation), or have its width/height/crop overridden. |
| Add custom size | Defines an additional named size, generated alongside the existing ones. |
| Modern format | `Off`, `WebP`, `AVIF`, or `WebP and AVIF`. A warning is shown if the current server's PHP image library (GD/Imagick) doesn't support a chosen format. |
| Quality | Compression quality (1–100) used when generating WebP/AVIF variants. |
| Regenerate thumbnails | Bulk-regenerates intermediate sizes for existing media library items, using the currently saved size settings. Processed in small batches with a progress bar. |
| Convert to modern formats | Bulk-generates WebP/AVIF variants for existing media library items, using the currently saved format settings. Processed in small batches with a progress bar. |

Save your settings before running either bulk action — both use the saved
configuration, not the form's current unsaved state.

## How it works

- **Disabling a size** removes it from `intermediate_image_sizes`/
  `intermediate_image_sizes_advanced`, so WordPress stops generating that file
  on future uploads. It does not delete already-generated files.
- **Overriding thumbnail/medium/medium_large/large** updates their standard
  `_size_w`/`_size_h`/`_crop` options directly (the same options the
  Settings → Media screen writes to). Overriding any other registered size, or
  defining a custom one, calls `add_image_size()` on every request.
- **Format generation** happens right after WordPress generates an
  attachment's intermediate sizes (`wp_generate_attachment_metadata`): for the
  full-size image and every generated size, a WebP and/or AVIF variant is
  saved alongside the original via `wp_get_image_editor()`. Generated variant
  filenames are stored in the attachment's metadata (`frbl_image_variants`).
- **Frontend delivery** rewrites `<img>` tags in post content
  (`wp_content_img_tag`) into a `<picture>` element with `<source
  type="image/avif">` / `<source type="image/webp">` entries pointing at the
  generated variants, keeping the original `<img>` as the last child so
  browsers without modern-format support — or an image with no generated
  variant — fall back to it automatically. No server-side Accept-header
  sniffing or server configuration is required.

## Out of scope

- No integration with an external image CDN or optimization API — this module
  only manages local, built-in size generation and format conversion.
- The `<picture>` rewrite currently targets the single `src` shown for a given
  content image, not every entry in its `srcset`.
