# Table of Contents

An accessible "Table of Contents" block (`frontblocks/table-of-contents`) that generates navigation from the headings in the current post.

## How it works

The block is dynamic: inserting it only stores its settings. When the post is rendered, `FrontBlocks\Frontend\TableOfContents` scans the fully rendered post content for every `<h1>`-`<h6>` tag (regardless of which block produced it — core Heading, GenerateBlocks, or anything else), and:

- Assigns a stable, unique id to any heading that doesn't already have one, generated from its text (`sanitize_title()`, disambiguated with a `-2`, `-3`... suffix on collisions). An author-supplied `id` (e.g. via the core Heading block's "HTML anchor" field) is always preserved untouched.
- Adds `tabindex="-1"` to headings that don't already have one, so clicking a Table of Contents entry can move keyboard/screen-reader focus to the destination, not just scroll to it.
- Builds the navigation list from the headings within the block's configured level range, and inserts it in place of the block.

Because heading discovery depends on the *whole* post being rendered first, this happens on a late `the_content` filter (priority 30, after `do_blocks()`), not in the block's own render callback — this lets the Table of Contents work correctly even when it's inserted before the headings it links to.

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
- Frontend assets are only enqueued on pages that actually contain the block (`has_block()`).

## Editor preview

The block's live preview in the editor only lists core Heading blocks — GenerateBlocks and other third-party heading output isn't a distinct, reliably identifiable block type in the editor's block tree, so it can't be enumerated there. This is a preview-only limitation: the published page is unaffected, since the frontend discovers every heading tag in the final rendered HTML regardless of its source block.
