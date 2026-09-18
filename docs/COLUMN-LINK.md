# Column Link

## Overview

The Column Link feature adds a "FrontBlocks - Column Link" panel to the native `core/column` block's Inspector Controls. Setting a URL there makes the entire column clickable on the frontend, redirecting to that URL — a common "linked card/container" pattern, available with no custom code.

## How to Use

### In the Block Editor

1. Select a **Column** block (inside a Columns block, or standalone).
2. In the right sidebar (Inspector), open the **"FrontBlocks - Column Link"** panel.
3. Enter a **Link URL**.
4. Optionally enable **Open in new tab**.

Leaving the URL empty disables the feature for that column — its markup is left completely untouched.

## Frontend Behavior

- Clicking anywhere in the column's background navigates to the configured URL.
- Clicks that land on a link, button, form field, or other interactive element already inside the column are **not** hijacked — that element keeps working normally.
- The column is keyboard-accessible: it receives `role="link"` and `tabindex="0"`, and pressing **Enter** or **Space** while the column itself is focused navigates the same way a click would. A nested control keeps handling its own Enter/Space activation.
- Opening in a new tab uses `noopener,noreferrer`, same protection a native link with `target="_blank" rel="noopener noreferrer"` gets.

## Editor Preview

The linked column gets a dashed outline in the editor canvas as a visual hint, without making the canvas itself navigate on click — clicking to select and edit the column's content still works as expected.

## Implementation Notes

- Follows the same native-block-extension pattern already used for `core/columns` (Columns Same Height) and `core/button` (Download Button): attributes are registered server-side via `register_block_type_args`, and the saved markup is rewritten at render time via the `render_block_core/column` filter — nothing is altered client-side in the saved block content.
- The link URL is escaped with `esc_url()` before being written into the output; a disallowed scheme (e.g. `javascript:`) is rejected and the column is rendered exactly as if no link were set.
- Assets (a small stylesheet and a vanilla-JS click/keyboard handler) are only enqueued on pages that actually contain a column with a configured link.
