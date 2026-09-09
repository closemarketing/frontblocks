# Table of Contents

An accessible "Table of Contents" block (`frontblocks/table-of-contents`) that generates navigation from the headings in the current post.

## How it works

The block is dynamic: inserting it only stores its settings. When the post is rendered, `FrontBlocks\Frontend\TableOfContents` scans the fully rendered post content for every `<h1>`-`<h6>` tag (regardless of which block produced it — core Heading, GenerateBlocks, or anything else), and:

- Assigns a stable, unique id to any heading that doesn't already have one, generated from its text (`sanitize_title()`, disambiguated with a `-2`, `-3`... suffix on collisions). An author-supplied `id` (e.g. via the core Heading block's "HTML anchor" field) is always preserved untouched.
- Adds `tabindex="-1"` to headings that don't already have one, so clicking a Table of Contents entry can move keyboard/screen-reader focus to the destination, not just scroll to it.
- Builds the navigation list from the headings within the block's configured level range, and inserts it in place of the block.

Because heading discovery depends on the *whole* post being rendered first, this happens on a late `the_content` filter (priority 30, after `do_blocks()`), not in the block's own render callback — this lets the Table of Contents work correctly even when it's inserted before the headings it links to.

`the_content` alone only covers a block placed inside the Post Content block, since that's the only core block that runs it. When the Table of Contents is instead placed directly in a block-theme Template (as a sibling of Post Content, e.g. in the Single Post template), a whole-page output buffer (`maybe_start_output_buffer()`, started on `template_redirect`) runs the same heading-discovery and placeholder-replacement logic over the final assembled page, so the block still resolves there. This buffer is skipped for admin, feed, REST, XML-RPC, AJAX, and cron requests, and is a no-op whenever no placeholder is present in the page.

## Settings

- **Title** — heading text shown above the list, also used as the navigation landmark's accessible name (`aria-label`).
- **List style** — bulleted, numbered, or plain (no bullets, still a real list for screen readers).
- **From/To heading level** — which `<h2>`-`<h6>` levels to include.
- **Collapsible** — renders as a native `<details>`/`<summary>`, giving free, fully keyboard-accessible expand/collapse with no custom JavaScript.
- **Sticky** — keeps the Table of Contents in view while scrolling (`position: sticky`).
- **Accent color** — applied via a CSS custom property, used for the active-section highlight and focus outlines.

## Frontend behavior

- Clicking an entry scrolls to (and moves focus onto) its heading, using `scroll-behavior: smooth` unless the visitor's browser reports `prefers-reduced-motion: reduce`, in which case scrolling is instant.
- An `IntersectionObserver` marks whichever section is currently in view via `aria-current="location"` on its link — this updates silently (no `aria-live` region), so it never triggers a screen-reader announcement on every scroll tick.
- Frontend assets are enqueued unconditionally (not gated behind `has_block()`), since that check only inspects the current post's content and would miss the block when it's placed directly in a Template instead. The combined CSS/JS is a few KB, so this is a simpler and more reliable trade-off than detecting every possible placement up front.

## Editor preview

The block's live preview in the editor only lists core Heading blocks — GenerateBlocks and other third-party heading output isn't a distinct, reliably identifiable block type in the editor's block tree, so it can't be enumerated there. This is a preview-only limitation: the published page is unaffected, since the frontend discovers every heading tag in the final rendered HTML regardless of its source block.

The block's stylesheet (including the per-level indentation rules) is enqueued via `enqueue_block_assets`, not `enqueue_block_editor_assets`: since WordPress 5.9 the block canvas renders inside an iframe, and only styles registered through `enqueue_block_assets` are mirrored into it — a style enqueued solely via `enqueue_block_editor_assets` only reaches the top-level admin document, never the iframed preview.

## Heading id collisions

The occupied-id set used to disambiguate generated heading ids is seeded from every existing `id` attribute in the rendered content before any heading is processed — not only from other headings' ids. This prevents a generated heading anchor from colliding with an `id` already used by an unrelated, non-heading element (e.g. a Group block's HTML anchor) earlier in the page.
