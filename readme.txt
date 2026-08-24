=== FrontBlocks for Gutenberg/GeneratePress ===
Contributors: davidperez, sacrajaimez, alexbreagarcia, matiasquero, amulero, mit2sumit, alexcm13
Tags: carrusel, slider, lightweight, generatepress, gutenberg
Donate link: https://close.marketing/go/donate/
Requires at least: 5.0
Tested up to: 7.1
Stable tag: 1.5.1
Version: 1.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin extending Gutenberg and GeneratePress with carousel, slider, animations, sticky columns, edge alignment and post insertion capabilities.

== Description ==

**Container Edge Alignment**
Add custom controls to GenerateBlocks Container blocks and native WordPress Group/Container blocks to remove padding from the left or right side, creating edge-to-edge layouts. This feature only appears for containers using GeneratePress global max-width settings, perfect for creating asymmetric layouts where content extends to one browser edge while maintaining proper spacing on the other side.

**Carousel/Slider for GenerateBlocks Grid and Query Loop**
We have added options to Gutenberg blocks that enable you to create a carousel or slider using your preferred blocks. Supported blocks include GenerateBlocks Grid, GenerateBlocks Element, core/group, and the native WordPress Query Loop (core/query).

To start using the carousel, go to the grid block or Query Loop block and select the 'Carousel' or 'Slider' option in the 'FrontBlocks Grid Options' section.

Carousel/Slider attributes:
- Autoplay: automatically changes the slides after a certain amount of time (in seconds).
- Items to view: configure the number of items to display for different screen sizes:
  * Desktop (>1200px): number of items to show on desktop screens.
  * Laptop (992px-1199px): number of items to show on laptop screens.
  * Tablet (768px-991px): number of items to show on tablet screens.
  * Mobile (<768px): number of items to show on mobile devices.
- Buttons: the type of buttons to display in the carousel/slider (bullets, arrows or none).
- Button colour: colour of the buttons.
- Button background colour: background colour of the buttons (can be transparent).

**Carousel Pattern:**
We provide a ready-to-use Hero Carousel pattern using native WordPress Cover blocks. This pattern is automatically registered in the WordPress editor's "Patterns" tab under the "FrontBlocks" category. Simply click the + button in the editor, go to Patterns, and search for "Hero Carousel" or browse the FrontBlocks category. The pattern creates full-width hero sliders with smooth transitions, perfect for landing pages and promotional content. It includes three customizable slides with gradients, colors, headings, text, and call-to-action buttons. See the documentation for complete implementation details and customization options.

**Enhanced WordPress native gallery**
We have added options to the native WordPress gallery that allow you to create a different layout, such as grid or masonry, and also enable you to create a carousel with images that can be clicked on.

**Animations for Blocks**
You can add animations to the blocks you want. To do this, go to the block settings and select the 'FrontBlocks Animation Option' section. There you will find a list of animations that you can apply to the block.

The animations are based on [Animate.css](https://animate.style/): Attention seekers, Back entrances, Back exits, Bouncing entrances, Bouncing exits, Fading entrances, Fading exits, Flippers, Rotating entrances, Rotating exits, Specials, Zooming entrances, Zooming exits, and Sliding entrances.

**Container Effects**
Apply glassmorphism effects to any block with customizable blur intensity. In the block settings, open the 'Container Effects' panel to enable the glass effect and adjust the blur level (0-50px) for a modern, frosted glass appearance. The effect includes a semi-transparent background, subtle border, and soft shadow, creating a beautiful layered design. Perfect for hero sections, cards, and overlays.

**Hover Effects**
Add smooth zoom effects to background images when users hover over elements. Perfect for post grids, galleries, and cards. In the block settings, open the 'FrontBlocks Hover Effects' panel to enable background scaling. Features:
- Compatible with GenerateBlocks Query Loop (--inline-bg-image)
- Works with standard CSS background-image
- Configurable scale amount from 1.0 to 2.0 (default: 1.1 for 110% zoom)
- Smooth 0.4s transition with GPU acceleration
- Content remains readable and properly positioned above the scaled image
- Overflow protection ensures images don't extend beyond container

**Sticky Columns:**
The sticky option allows you to make a column stick to the top of the viewport when scrolling. Works with both GenerateBlocks Grid blocks and native WordPress blocks. Enable the "Sticky" option in the block settings and the column will remain fixed at the top of the viewport as the rest of the page scrolls.

**Insert Post Block:**
Display content from other posts, pages or custom post types. Search and select any published content to display its title as an H2 heading and its full content. This is perfect for creating dynamic content sections without duplicating content.

**Decoration for Headline block:**
Add a decorative line to the Headline Block. You can choose between a vertical or horizontal line on the right.

**Headline Marquee Effect:**
Add an infinite scrolling marquee effect to Headline/Text blocks. Works with both GenerateBlocks Headline blocks and native WordPress Heading/Paragraph blocks. The text scrolls continuously from right to left, automatically adapting to the container width. Short text repeats more times, long text repeats less. Features:
- Toggle to enable/disable the marquee effect
- Speed control with three presets: Fast (10s), Medium (20s), Slow (40s)
- Seamless infinite loop with no jumps or interruptions
- Automatically fills container width with appropriate text repetitions
- Smooth, fluid animation optimized for performance

**Product Categories block:**
Display product categories from WooCommerce. Choose the number of categories to display, the order by and the order. You can also choose to hide empty categories. You can also select the number of columns in which to display the categories. You can also customise the background colour, border colour, border width, border radius, text colour, hover background colour, hover border colour and hover text colour.

**Counter Block:**
Display a counter with a start value, end value and duration. The counter will increment from the start value to the end value within the specified time frame. Compatible with GenerateBlocks text and headline blocks, as well as native WordPress core/heading and core/paragraph blocks.

**Testimonials:**
Display testimonials from other posts, pages or custom post types. Search and select any published content to display its title as an H2 heading and its full content. This is perfect for creating dynamic content sections without duplicating content.

Shortcode: [frontblocks_testimonials_carousel]

**Reading Time block:**
Display the reading time of a post. You can choose the number of words per minute to use for the calculation.
Shortcode: [frontblocks_reading_time]

**Reading Progress Bar:**
Display a vertical progress bar on the right side of posts that fills up as users scroll through the content. The progress bar uses your website's primary color and provides a visual indicator of reading progress. This feature can be enabled/disabled from the FrontBlocks settings page in the WordPress admin.

**Back Button:**
Display a floating back button in the bottom left corner that allows users to navigate to the previous page. Enable it from the FrontBlocks settings page.

**Fluid Typography:**
Automatically converts your theme's static typography settings into modern fluid typography using CSS clamp(). Instead of abrupt font size changes at breakpoints, this creates smooth, gradual scaling from mobile (320px) to desktop (1440px). Compatible with all WordPress themes.

Supports all typography elements:
- Body text and paragraphs (including GenerateBlocks headline elements)
- All headings (H1-H6)
- Each element maintains its own responsive values
- Zero configuration - automatically reads from your theme's dynamic CSS
- Smooth transitions across all viewport sizes without jumps

Simply enable "Fluid Typography" in FrontBlocks settings, and all your responsive typography will scale smoothly between devices!

**Custom SVG Animations:**
Add animated graphics to GenerateBlocks Shape blocks and native WordPress Icon blocks by importing JSON files. Supports two formats that are automatically detected: **Lottie/Bodymovin** (import JSON from After Effects or LottieFiles.com) and **Custom CSS** (SVG + @keyframes).

**Text Animation Block:**
Add dynamic animated text effects to any page or post. Insert the block from the Gutenberg inserter under the FrontBlocks category and choose from 30+ animation types to make your headings and copy stand out.

Animation types available:
- Entrance effects: fade-in, blur-in, scale-in, bounce-in, drop-in, roll-in, flip-in, rotate-in, glow-in, slide-up, slide-down, slide-left, slide-right
- Text reveals: typewriter, block-reveal, tracking-expand, terminal-type, random-reveal, shuffle-text
- Attention effects: pulse, flash, rubber-band, wave, swing, stretch, squeeze
- Creative effects: glitch, glitch-rgb, flicker, water-drop, shadow-pop, solid-outline

Features:
- Live animated preview directly in the Gutenberg editor.
- Full typography controls: heading tag (H1–H6, p, span), font family, size, weight, style, and color.
- Background color and padding/margin controls.
- Max-width and text alignment options.
- Speed control for each animation.

**Before/After Comparison Block:**
Display an interactive image comparison slider that lets visitors drag a handle to reveal the difference between two images. Perfect for showcasing makeovers, retouching results, renovation work, or any visual transformation. Add the block from the Gutenberg inserter under the FrontBlocks category.

Features:
- Upload separate "Before" and "After" images from the WordPress media library.
- Draggable handle with left/right arrows — also controllable via keyboard for accessibility.
- Customizable labels displayed over each image (default: "Before" / "After").
- Initial slider position control (0–100%) to choose how much of each image is revealed on load.
- Fixed height mode with a configurable pixel value, keeping the block compact regardless of image proportions.

**SVG Uploads:**
Upload SVG files directly to the WordPress media library. Only administrators can upload SVG files. All files are automatically sanitized on upload — dangerous elements (`<script>`, `<iframe>`, `<object>`, `<embed>`) and event-handler attributes (`on*`) are stripped before saving, protecting against XSS attacks.

**Login Block:**
Add a native Gutenberg login form block to any page or post. Insert the block from the Gutenberg inserter under the FrontBlocks category. Features a toggle to switch between login form and registration form views, with fully customizable labels and styles. Ideal for membership sites or pages requiring a clean, integrated login experience without a shortcode.

**Post Meta (Native) — FrontBlocks PRO:**
Display custom field values from post meta inside native WordPress blocks directly in the editor. Select any registered meta key and output its value inline within heading, paragraph, or other text blocks — no shortcodes or custom PHP required. Useful for displaying dynamic data such as prices, dates, or any custom field stored in post meta. Available in [FrontBlocks PRO](https://close.technology/en/wordpress-plugins/frontblocks-pro/?utm_source=WordPressORGReadme&utm_medium=link&utm_campaign=frontblocks).

**FAQ Schema (JSON-LD):**
Automatically generate FAQPage structured data for search engines. Enable the "FAQ Schema" toggle in any accordion block (core/details) to mark it as an FAQ item. FrontBlocks collects all enabled items on the page and outputs a valid FAQPage JSON-LD script in the footer, improving your chances of appearing as rich results in Google Search.

**Gravity Forms Inline Layout:**
Display Gravity Forms with fields and buttons on the same line. Perfect for newsletter signup forms (email + subscribe button) or search forms (input + search button). 

To use this feature:
1. Select a form in the Gravity Forms block
2. Find the "FrontBlocks Gravity Form Options" panel in the block settings where you can:
   - Enable inline layout with a simple toggle
   - Adjust the gap between elements (0-50px)
   - Responsive design: automatically stacks on mobile devices

This feature eliminates the need for custom CSS to achieve inline form layouts.

**Stacked Images Block:**
Display a set of images with an animated stacking effect. Images appear layered on top of each other and animate in from a configurable direction (top, bottom, left, or right). Insert the block from the Gutenberg inserter under the FrontBlocks category and upload multiple images. Features:
- Configurable animation duration and delay between each image.
- Container height control to fit any layout.
- Smooth entrance animations driven by CSS/JS with no dependencies.

**Events:**
Register and manage events directly from WordPress. Enable the Events feature from the FrontBlocks settings page and choose between two modes: a dedicated Custom Post Type (with its own menu and category taxonomy) or storing events as regular posts. Each event includes meta fields for:
- All-day toggle
- Start and end date and time
- Cost
- Event website URL

**Language Recommendation Banner:**
Display a smart banner that detects the visitor's browser language and suggests switching to the matching version of the site. Enable the banner from the FrontBlocks settings page. The banner only appears when the site has a translated version matching the user's browser language, and respects user dismissal via a cookie.

**Cookie Notice:**
Show a lightweight, configurable cookie consent banner with Accept and Reject actions. Choose between a full-width bottom bar, a boxed panel (bottom-left or bottom-right) or a centered popup, and customize the message, button labels, cookie policy page, accent color and cookie expiration. Google Tag Manager and/or GA4 are only requested and loaded after a visitor accepts — never before — and returning visitors who already accepted get the scripts on normal page load. The settings page also shows a simple accepted/rejected acceptance-rate stat (admins are excluded from the count). Implements Google Consent Mode v2, so it also holds back tracking from other analytics/ads plugins that respect it — including **Google Site Kit** — until the visitor decides.

**WooCommerce Features:**
Included features for WooCommerce FrontBlocks PRO.

**FrontBlocks PRO:**
FrontBlocks PRO is a premium plugin that extends the functionality of FrontBlocks. It includes additional features and improvements over the free version.

Features:
- Enable Gutenberg in the product editor.
- Enable simple prices for variable products.
- Block added after button.
- Product description behaviour.
- Disable zoom on product image.
- Share buttons.
- Custom Post Types Builder: Create and manage custom post types with advanced configuration options:
  * Create custom post types with a simple interface from the FrontBlocks settings page
  * Configure post type behavior (Post or Page style - hierarchical or not)
  * Enable/disable categories taxonomy for each custom post type
  * Add custom meta fields with multiple field types (Text, Textarea, URL, Date, File, Number, Email)
  * Individual settings page for each custom post type accessible from the post type menu
  * Delete custom post types easily with a single click
- Disable tabs on the product page.
- Horizontal product form layout (price, quantity, and add to cart button in one row).
- Checkout Inline: Display billing/shipping fields (email + phone, address line 1 + address line 2) side by side in a two-column layout. Compatible with Block Checkout (WooCommerce 8+) and legacy shortcode checkout.
- Full Page Scroll: Create fullpage scroll experiences with smooth section-by-section navigation and automatic side navigation dots. Perfect for landing pages, portfolios, and presentations.
- Footer Patterns: Ready-to-use footer block patterns (3 columns, 4 columns, hero contact) for native WordPress and Gutenberg.
- Login Block: Native Gutenberg block for user login and registration forms with customizable labels and styles.
- Popup Native: Native popup system with multiple trigger conditions (exit intent, scroll percentage, time delay, on click), entrance animations, overlay, and cookie-based frequency control.

More information in the [FrontBlocks PRO](https://close.technology/en/wordpress-plugins/frontblocks-pro/?utm_source=WordPressORGReadme&utm_medium=link&utm_campaign=frontblocks) page.

== Installation ==

1. Go to Plugins > Add New > Search for "FrontBlocks" > Install and Activate.
2. Go to Settings > FrontBlocks > Features and enable the features you want to use.

== Changelog ==

== 1.5.1 ==
*   Added: Dismissible admin notice inviting users to leave a review on WordPress.org, shown 14 days after activation.
*   Added: Cookie Notice extension hooks (`frbl_cookie_notice_before_actions`, `frbl_cookie_notice_after_banner`, default-label filters, and the client-side `window.frblCookieNoticeConsentModeState()` override) so an add-on can extend the banner with per-category consent without forking it — used by FrontBlocks PRO's new Advanced Cookie Management.

== 1.5.0 ==
*   Added: Cookie Notice — configurable cookie consent banner (full-width bar, boxed panel or centered popup) with Accept/Reject actions, custom message, accent color and expiration. Google Tag Manager and GA4 only load after consent is accepted, an aggregate acceptance-rate stat is shown in the settings page, and Google Consent Mode v2 support holds back tracking from other analytics/ads plugins (including Google Site Kit) until the visitor decides.
*   Fixed: WordPress 7.1 compatibility — Reading Time, Gallery, Container Edge Alignment and Shape Animations now enqueue their block editor preview styles through `enqueue_block_assets` so they load correctly inside the editor's iframed canvas, resolving console warnings introduced by WordPress 7.1.

== 1.4.0 ==
*   Added: Carousel Pattern — ready-to-use Hero Carousel pattern using native WordPress Cover blocks, registered in the Patterns tab under the FrontBlocks category.
*   Added: Language Recommendation Banner — settings integration to enable a smart banner that suggests switching to the user's browser language.
*   Added: Hover Background Scale — adds smooth zoom effect on background images when hovering over blocks (GenerateBlocks Query Loop and standard CSS backgrounds).
*   Improved: Carousel editor — revamped carousel editor UI with live preview and improved advanced options panel.
*   Improved: Conditional asset loading — scripts and styles for Animations, Carousel, Counter, ContainerEdgeAlignment, GravityFormsInline, Headline, InsertPost, ShapeAnimations and StickyColumn only enqueue on pages where the feature is used.
*   Improved: Settings moved to Appearance submenu child page for better organization.
*   Fixed: Accordion/Gravity Forms inline layout CSS conflicts.
*   Fixed: Carousel display, bullets, and JavaScript issues.
*   Fixed: Carousel bullet accessibility — added aria-label attributes and increased clickable area.
*   Added: [PRO] Checkout Inline — displays WooCommerce checkout fields (email/phone and address lines) side by side in a two-column layout. Compatible with both Block Checkout (WooCommerce 8+) and legacy shortcode checkout.
*   Added: [PRO] Popup Native — native popup system with trigger conditions (exit intent, scroll percentage, time delay, click), animations, overlay, and cookie-based frequency control.
*   Added: [PRO] Meta Fields Native — display custom post meta values inside native WordPress blocks directly from the Gutenberg editor.
*   Added: [PRO] Login Block — block for displaying a user login form with customizable options.
*   Added: [PRO] Full Page Scroll Pattern — ready-to-use full page scroll pattern for native WordPress and Gutenberg blocks.
*   Added: [PRO] Footer Patterns — new footer patterns (3 columns, 4 columns, hero contact) for native WordPress and Gutenberg.
*   Added: [PRO] Language Recommendation Banner — smart banner that suggests switching to the user's browser language.
*   Fixed: [PRO] Duplicate settings entry in PRO Showcase panel.

== 1.3.6 ==
*   Added: SVG Uploads — allows administrators to upload SVG files to the WordPress media library with automatic server-side sanitization.
*   Added: Login Block — native Gutenberg block for login form with toggle options and customizable UI.
*   Added: Meta Native — display custom field meta values inside native WordPress blocks directly from the editor.
*   Added: Before/After Effect Editor — interactive image comparison block now fully editable inside the Gutenberg editor.
*   Added: Fluid Typography for all themes — fluid typography scaling now applies across all active WordPress themes, not just GeneratePress.
*   Added: Headline Marquee Native — scrolling marquee/ticker text support for native WordPress headline blocks.
*   Added: Container Edge Alignment Native — edge-to-edge alignment option for native WordPress container/group blocks.
*   Added: Shape Animations Native Icon — shape animation support extended to native WordPress icon blocks.
*   Added: Sticky Column Native — sticky column behavior for native WordPress blocks.
*   Fixed: Product category filter display on WooCommerce product pages.
*   Improved: Block and function names prefixed with `frontblocks-` for consistency and conflict prevention.
*   Dev: Added WordPress Playground PR preview — one-click preview button added to every pull request.

== 1.3.5 ==
*   Added: FAQ Schema — automatically generates FAQPage JSON-LD structured data from accordion blocks (core/details) marked as FAQ items, improving SEO visibility in search engines.
*   Added: Carousel/Slider support for WordPress core Query Loop block (core/query) — use the FrontBlocks carousel options directly on native Query Loop blocks.
*   Improved: Counter block now works with native WordPress blocks (core/heading, core/paragraph) in addition to GenerateBlocks text and headline blocks.

== 1.3.4 ==
*   Added: Text Animation block with 30+ animation types — fade-in, typewriter, shuffle-text, slide-up/down/left/right, drop-in, swing, pulse, flash, rubber-band, wave, stretch, squeeze, roll-in, glitch, glitch-rgb, random-reveal, flicker, block-reveal, tracking-expand, terminal-type, solid-outline, water-drop, shadow-pop, scale-in, blur-in, glow-in, bounce-in, flip-in, rotate-in.
*   Added: Text Animation block — full typography controls (tag, font family, size, weight, style, color, background, alignment, padding, margin, max-width).
*   Added: Text Animation block — live animated preview in the Gutenberg editor.
*   Added: Before/After Comparison block — interactive drag-to-reveal image comparison with customizable labels, initial position, and fixed height mode.
*   Added: Before/After block — keyboard-accessible draggable handle with left/right arrow indicators.
*   Improved: Conditional asset loading for Carousel, Animations, Text Animation, and Before/After — scripts/styles only enqueue on pages where the feature is used.

== 1.3.3 ==
*   Fixed: Carousel bullets display and behavior.
*   Fixed: Carousel editor styling and functionality.
*   Fixed: Carousel in native (core) blocks.
*   Fixed: Carousel JavaScript and CSS issues.
*   Fixed: Stacked images block display.
*   Fixed: Accordion in Gravity Forms inline layout.
*   Improved: Carousel styles - updated classes and removed unnecessary declarations.
*   Improved: Settings page and carousel advanced options.
*   Improved: Shape animations option component.
*   Improved: PHPStan compliance and code quality.

== 1.3.2 ==
*   Added: FrontBlocks Hover Effects - Smooth background image zoom on hover for Query Loops, grids, and cards.
*   Added: Configurable scale amount (1.0-2.0) for hover background zoom effect.
*   Added: Support for GenerateBlocks --inline-bg-image and standard CSS background-image.
*   Added: GPU-accelerated smooth transitions (0.4s) for optimal performance.
*   Added: Hero Carousel Pattern - Ready-to-use block pattern automatically registered in WordPress Patterns tab.
*   Added: Pattern includes 3 full-width hero slides with customizable gradients, headings, text, and CTA buttons.
*   Added: One-click pattern insertion under "FrontBlocks" category in block editor.
*   Added: Pattern searchable by keywords: carousel, hero, slider, banner, header.
*   Improved: Carousel single-slide view now displays full width (100%) instead of 50% of two slides.
*   Improved: Dynamic gap calculation - 0px gap when showing 1 slide, 20px gap for multiple slides.
*   Improved: Smooth carousel transitions with cubic-bezier easing for fluid animations.
*   Improved: Carousel responsive behavior with proper width and spacing across all devices.
*   Fixed: Carousel appearing blank/white when initialized.
*   Fixed: Slides being cut in half or showing partial content.
*   Fixed: Autoplay not respecting empty or zero values.
*   Improved: Increased carousel bullet size from 9px to 13px for better accessibility and easier interaction.
*   Improved: Updated carousel bullets spacing using CSS gap property for more consistent layout.
*   Added: Fluid Typography - Automatically converts GeneratePress typography to smooth fluid scaling using CSS clamp().
*   Added: Support for all typography elements (body, h1-h6) with individual responsive values.
*   Added: Smart detection of multi-selector CSS patterns (body, button, input, textarea).
*   Added: Automatic conversion from static breakpoints to fluid viewport scaling (320px-1440px).
*   Added: High specificity CSS to properly override GenerateBlocks inline styles.
*   Added: Debug mode for Fluid Typography troubleshooting (?frbl_debug=1).
*   Improved: Better CSS parsing for media queries and responsive font sizes.
*   Added: Full Page Scroll toggle in settings (PRO feature).
*   PRO: Full Page Scroll - Create smooth fullpage scroll experiences with automatic section navigation.
*   PRO: Side navigation with dots that updates automatically as you scroll.
*   PRO: Smooth scroll between sections with mouse wheel control.
*   PRO: Responsive design with mobile-optimized navigation.
*   Improved: Carousel/Slider - Added individual controls for desktop, laptop, tablet, and mobile view items instead of hardcoded values.

== 1.3.1 ==
*   Improved: Custom SVG Animations now uses file upload instead of textarea for importing JSON files.
*   Added: Download example JSON button for Custom SVG Animations feature.
*   Added: Clear button to remove imported animation files.
*   Added: Visual file name display with icon for imported JSON files.
*   Improved: Better user experience with file import workflow for Shape animations.
*   Fixed: File input now properly resets after clearing, allowing immediate re-import of files.

== 1.3.0 ==
*   Added: Container Effects with Glass Effect (Glassmorphism) - Apply customizable glass effect with adjustable blur intensity (0-50px) to any block.
*   Improved: Complete redesign of the Settings page with modern card-based layout.
*   Improved: Each feature now displays in its own card with icon, title, and toggle switch.
*   Improved: PRO features show distinctive badge in the top-left corner.
*   Improved: Responsive grid layout that adapts to mobile, tablet, and desktop screens.
*   Improved: License section displays as full-width card for better visibility.
*   Added: Container Edge Alignment - Remove padding from left or right side of GenerateBlocks containers to create asymmetric edge-to-edge layouts (only for containers using GeneratePress global max-width).
*   Added: Custom Animations for Shapes - Full Lottie/Bodymovin support + custom CSS animations via JSON. Auto-detects format.
*   Added: Lottie-web library integration for After Effects animations.
*   Added: Gravity Forms Inline Layout option for displaying fields and buttons on the same line.
*   Added: Gap control for inline form elements (0-50px).
*   Added: Responsive design support - automatically stacks on mobile.
*   Fixed: REST API validation errors with custom block attributes.
*   Added: Reading Progress Bar - Display a visual progress indicator on the right side of posts as users scroll through content.
*   Added: Back Button feature with floating button to navigate to previous page.
*   PRO: Horizontal Product Form Layout - Align price, quantity, and add to cart button in one row.
*   Added: Remove box shadow in Carrusel bullets.

== 1.2.1 ==
*   Fixed: Carousel/Slider not working correctly.

== 1.2.0 ==
*   Improved: Settings page.
*   Added: Toggle to disable animations on mobile.
*   PRO: Deactivate Product Tabs setting.
*   PRO: Share buttons.
*   PRO: Zoom disable in Product Image.
*   PRO: Product description behavior.
*   PRO: Block after buttom.
*   PRO: Simple Prices for variable products.

= 1.1.0 =
*   Added: FrontBlocks PRO compatibility.
*   Added: Show a preview of the product categories in Gutenberg editor.
*   Fixed: Change bullets logic count up to 10 items.
*   Added: New block for reading time of a post.
*   Added: Carousel/Slider: Add item to view in laptop and mobile.
*   Added: Carousel/Slider: Add option to deactivate Carousel/Slider in Desktop.
*   Fixed: Mansonry effect upgraded in Gallery.
*   Fixed: translations in block options.

= 1.0.4 =
*   Added: Product Categories block.
*   Added: Options to the Product Categories block.
*   Added: Counter effect for Headline block.
*   Added: Counter attribute for Headline block.
*   Added: Add decoration attribute for Headline block.

= 1.0.3 =
*   Added: Settings page and simple testimonials feature.
*   Fixed: interaction with Gravity Forms.
*   Fixed: not rendering correctly the content of the insert post block.

= 1.0.1 & 1.0.2 =
*   Fixed: options for carousel/slider not showing.

= 1.0.0 =
*   Added sticked option for Grid block.
*   Improved interface for animations.
*   Improved interface for carousel/slider.
*   Added options in native gallery block.
*   Added: Insert Post block to display content from other posts, pages, or custom post types.

= 0.2.5 =
*   Update version.

= 0.2.4 =
*   Fixed: Buttons not overlapping the carousel.
*   Added: New data attribute for carousel buttons color.
*   Added: Not show bullets in responsive view and more than 5 items.

= 0.2.3 =
*   Updated Glide autoplay value assignation.

= 0.2.2 =
*   Updated images.

= 0.2.1 =
*   New image for WordPress Plugin.

= 0.2.0 =
*   Created Animation feature.

= 0.1.0 =
*   Created Carousel/Slider class block.
*   First released.

== Links ==

*   [Closemarketing](https://close.marketing/)
*   [Close·Technology](https://close.technology/)
