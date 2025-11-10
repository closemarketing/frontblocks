=== FrontBlocks for GeneratePress ===
Contributors: davidperez, sacrajaimez, alexbreagarcia, matiasquero, amulero, mit2sumit, alexcm13
Tags: carrusel, slider, lightweight, generatepress
Donate link: https://close.marketing/go/donate/
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.2.1
Version: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin extends the functionality of GeneratePress by adding a carousel, slider, animations, sticky columns and the ability to insert posts.

== Description ==

**Carousel/Slider for GenerateBlocks Grid**
We have added options to Gutenberg blocks that enable you to create a carousel or slider using your preferred blocks.

To start using the carousel, go to the grid block and select the 'Carousel' or 'Slider' option in the 'FrontBlocks Grid Options' section.

Carousel/Slider attributes:
- Autoplay: automatically changes the slides after a certain amount of time (in seconds).
- View: the number of items to display in the carousel/slider.
Responsive view: the number of items to show in the carousel/slider in responsive view.
- Buttons: the type of buttons to display in the carousel/slider (bullets, arrows or none).
- Button colour: colour of the buttons.
- Button background colour: background colour of the buttons (can be transparent).

**Enhanced WordPress native gallery**
We have added options to the native WordPress gallery that allow you to create a different layout, such as grid or masonry, and also enable you to create a carousel with images that can be clicked on.

**Animations for Blocks**
You can add animations to the blocks you want. To do this, go to the block settings and select the 'FrontBlocks Animation Option' section. There you will find a list of animations that you can apply to the block.

The animations are based on [Animate.css](https://animate.style/): Attention seekers, Back entrances, Back exits, Bouncing entrances, Bouncing exits, Fading entrances, Fading exits, Flippers, Rotating entrances, Rotating exits, Specials, Zooming entrances, Zooming exits, and Sliding entrances.

**Sticky option for Grid block:**
The sticky option allows you to make the grid block stick to the top of the viewport when scrolling down. To use this feature, enable the "Sticky" option in the Grid block settings. When enabled, the grid block will remain fixed at the top of the viewport as you scroll down the page.

**Insert Post Block:**
Display content from other posts, pages or custom post types. Search and select any published content to display its title as an H2 heading and its full content. This is perfect for creating dynamic content sections without duplicating content.

**Decoration for Headline block:**
Add a decorative line to the Headline Block. You can choose between a vertical or horizontal line on the right.

**Product Categories block:**
Display product categories from WooCommerce. Choose the number of categories to display, the order by and the order. You can also choose to hide empty categories. You can also select the number of columns in which to display the categories. You can also customise the background colour, border colour, border width, border radius, text colour, hover background colour, hover border colour and hover text colour.

**Counter Block:**
Display a counter with a start value, end value and duration. The counter will increment from the start value to the end value within the specified time frame.

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

**Gravity Forms Inline Layout:**
Display Gravity Forms with fields and buttons on the same line. Perfect for newsletter signup forms (email + subscribe button) or search forms (input + search button). 

To use this feature:
1. Select a form in the Gravity Forms block
2. Find the "FrontBlocks Gravity Form Options" panel in the block settings where you can:
   - Enable inline layout with a simple toggle
   - Adjust the gap between elements (0-50px)
   - Responsive design: automatically stacks on mobile devices

This feature eliminates the need for custom CSS to achieve inline form layouts.

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
- Disable tabs on the product page.
- Horizontal product form layout (price, quantity, and add to cart button in one row).

More information in the [FrontBlocks PRO](https://close.technology/en/wordpress-plugins/frontblocks-pro/?utm_source=WordPressORGReadme&utm_medium=link&utm_campaign=frontblocks) page.

== Installation ==

Extract the ZIP file and simply copy its contents into the wp-content/plugins/ directory of your WordPress installation. Then activate the plugin from the Plugins page.


== Developers ==
[Official Repository Github](https://github.com/closemarketing/frontblocks)

== Changelog ==

== n.e.x.t ==
*   Added: Gravity Forms Inline Layout option for displaying fields and buttons on the same line.
*   Added: Gap control for inline form elements (0-50px).
*   Added: Responsive design support - automatically stacks on mobile.
*   Fixed: REST API validation errors with custom block attributes.
*   Added: Reading Progress Bar - Display a visual progress indicator on the right side of posts as users scroll through content.
*   Added: Back Button feature with floating button to navigate to previous page.
*   PRO: Horizontal Product Form Layout - Align price, quantity, and add to cart button in one row.

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
