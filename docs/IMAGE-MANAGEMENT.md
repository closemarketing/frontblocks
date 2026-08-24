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
| Add custom size | Defines an additional named size, generated alongside the existing ones. Each custom size also has a Label and a "Show in picker" checkbox — when checked, the size becomes selectable (under its label) from the image size dropdown shown when inserting or editing an image, the same dropdown that lists `Thumbnail`/`Medium`/`Large`/`Full Size`. |
| Modern format | `Off`, `WebP`, `AVIF`, or `WebP and AVIF`. A warning is shown if the current server's PHP image library (GD/Imagick) doesn't support a chosen format. |
| Quality | Compression quality (1–100) used when generating WebP/AVIF variants. |
| Use a `<picture>` element for delivery | Off by default: the `<img>` tag itself is switched to point at the modern-format file, which keeps the markup unchanged and relies on the format already being one the server (and by extension, in practice, the visiting browser) supports. On: the original file is kept as an explicit `<picture>`-element fallback alongside each modern-format `<source>`, at the cost of extra markup — useful if you want a guaranteed fallback even when a variant file is missing or corrupted. |
| Regenerate thumbnails | Bulk-regenerates intermediate sizes for existing media library items, using the currently saved size settings. Processed in small batches with a progress bar. |
| Convert to modern formats | Bulk-generates WebP/AVIF variants for existing media library items, using the currently saved format settings. Processed in small batches with a progress bar. |
| Delete files for disabled sizes | Bulk-deletes the on-disk files (and any generated WebP/AVIF variants) for sizes currently marked disabled, across existing media library items — this is what actually reclaims the disk space the sizes table estimates. Disabling a size on its own only stops future generation. |

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
- **Custom sizes flagged "Show in picker"** are added to the
  `image_size_names_choose` list, alongside their configured label — this is
  the same mechanism WordPress core uses to label `Thumbnail`/`Medium`/
  `Large` in that dropdown, so a custom size shows up there exactly like a
  built-in one.
- **Format generation** happens right after WordPress generates an
  attachment's intermediate sizes (`wp_generate_attachment_metadata`): for the
  full-size image and every generated size, a WebP and/or AVIF variant is
  saved alongside the original via `wp_get_image_editor()` — the original file
  is never modified or deleted. Each generated variant's filename and file
  size are recorded per size, per MIME type, in the attachment's metadata
  (`frbl_image_variants`).
- **Frontend delivery** rewrites `<img>` tags in post content
  (`wp_content_img_tag`) and featured images (`post_thumbnail_html`). By
  default it rewrites the `src`/`srcset` in place to point at the generated
  variant (AVIF preferred over WebP when both exist) — no extra markup. With
  "Use a `<picture>` element for delivery" enabled, it instead wraps the
  original `<img>` in a `<picture>` element with one `<source>` per generated
  format, keeping the original as the last child so a browser — or a request
  for a size/format with no generated variant — falls back to it
  automatically. No server-side Accept-header sniffing or server
  configuration is required either way.
- **Cleanup** happens automatically in two cases: generated variants are
  re-created from scratch (old files removed first) whenever an attachment's
  metadata is regenerated or converted, so switching formats never leaves
  stale files behind; and all of an attachment's variants are deleted when
  the attachment itself is deleted, since WordPress core has no knowledge of
  these extra files and would otherwise leave them orphaned on disk.

## Out of scope

- No integration with an external image CDN or optimization API — this module
  only manages local, built-in size generation and format conversion.
- No bulk regeneration is triggered automatically — the two bulk actions above
  are manual, so a large media library is never reprocessed without an
  explicit action from the site owner.
