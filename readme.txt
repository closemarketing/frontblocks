=== FrontBlocks for GeneratePress ===
Contributors: davidperez, sacrajaimez, alexbreagarcia, matiasquero, amulero, mit2sumit, alexcm13
Tags: carrusel, slider, lightweight, generatepress
Donate link: https://close.marketing/go/donate/
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.1.0
Version: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extends GeneratePress with carousel, slider, animations, sticky column, and insert post functionality.

== Description ==

**Carousel/Slider for GenerateBlocks Grid**
We have added options to the Gutenberg blocks that allow you to create a carousel or slider with the blocks you want.

To start using the Carousel, go to the grid block and select the "Carousel" or "Slider" option in the "FrontBlocks Grid Option" section.

Attributes for the Carousel/Slider:
- Autoplay - automatically change the slides after a certain time in seconds.
- View - number of items to show in the carousel/slider.
- Responsive View - number of items to show in the carousel/slider in responsive view.
- Buttons - type of buttons to show in the carousel/slider (bullets, arrows, none).
- Buttons Color - color of the buttons.
- Buttons Background Color - background color of the buttons (can be transparent).

**Enhanced WordPress Native Gallery**
We have added options to the native WordPress gallery that allow you to create a different layout as Grid or Masonry, and also make click to carousel with the images.

**Animations for Blocks**
You can add animations to the blocks you want. To do this, go to the block settings and select the "FrontBlocks Animation Option" section. There you will find a list of animations that you can apply to the block.

Animations are based on [Animate.css](https://animate.style/).

- Attention seekers: bounce, flash, pulse, rubberBand, shakeX, shakeY, headShake, swing, tada, wobble, jello, heartBeat
- Back entrances: backInDown, backInLeft, backInRight, backInUp
- Back exits: backOutDown, backOutLeft, backOutRight, backOutUp
- Bouncing entrances: bounceIn, bounceInDown, bounceInLeft, bounceInRight, bounceInUp
- Bouncing exits: bounceOut, bounceOutDown, bounceOutLeft, bounceOutRight, bounceOutUp
- Fading entrances: fadeIn, fadeInDown, fadeInDownBig, fadeInLeft, fadeInLeftBig, fadeInRight, fadeInRightBig, fadeInUp, fadeInUpBig, fadeInTopLeft, fadeInTopRight, fadeInBottomLeft, fadeInBottomRight
- Fading exits: fadeOut, fadeOutDown, fadeOutDownBig, fadeOutLeft, fadeOutLeftBig, fadeOutRight, fadeOutRightBig, fadeOutUp, fadeOutUpBig, fadeOutTopLeft, fadeOutTopRight, fadeOutBottomRight, fadeOutBottomLeft
- Flippers: flip, flipInX, flipInY, flipOutX, flipOutY, Lightspeed, lightSpeedInRight, lightSpeedInLeft, lightSpeedOutRight, lightSpeedOutLeft
- Rotating entrances: rotateIn, rotateInDownLeft, rotateInDownRight, rotateInUpLeft, rotateInUpRight
- Rotating exits: rotateOut, rotateOutDownLeft, rotateOutDownRight, rotateOutUpLeft, rotateOutUpRight
- Specials: hinge, jackInTheBox, rollIn, rollOut
- Zooming entrances: zoomIn, zoomInDown, zoomInLeft, zoomInRight, zoomInUp
- Zooming exits: zoomOut, zoomOutDown zoomOutLeft, zoomOutRight, zoomOutUp
- Sliding entrances: slideInDown, slideInLeft, slideInRight, slideInUp, Sliding, exits slideOutDown, slideOutLeft, slideOutRight, slideOutUp

**Sticky option for Grid block:**
Sticky option allows you to make the Grid block stick to the top of the viewport when scrolling down. To use this feature, you will have the option in Grid block settings to enable the "Sticky" option. When enabled, the Grid block will remain fixed at the top of the viewport as you scroll down the page.

**Insert Post Block:**
Display content from other posts, pages, or custom post types. Search and select any published content to display its title as H2 and full content. Perfect for creating dynamic content sections without duplicating content.

**Decoration for Headline block:**
Add a decorative line to the Headline block. You can choose between a vertical line on the right or a horizontal line on the right.

**Product Categories block:**
Display product categories from WooCommerce. You can choose the number of categories to display, the order by, and the order. You can also choose to hide empty categories. You can also choose the columns to display the categories. You can also choose the background color, border color, border width, border radius, text color, hover background color, hover border color, and hover text color.

**Counter Block:**
Display a counter with a start value, end value, and a duration. The counter will increment from the start value to the end value in the duration.

**Decoration for Headline block:**
Add a decorative line to the Headline block. You can choose between a vertical line on the right or a horizontal line on the right.

**Testimonials:**
Display testimonials from other posts, pages, or custom post types. Search and select any published content to display its title as H2 and full content. Perfect for creating dynamic content sections without duplicating content.

Shortcode: [frontblocks_testimonials_carousel]

**Reading Time block:**
Display the reading time of a post. You can choose the number of words per minute to calculate the reading time.
Shortcode: [frontblocks_reading_time]

**WooCommerce Features:**
Included features for WooCommerce FrontBlocks PRO.

**FrontBlocks PRO:**
FrontBlocks PRO is a premium plugin that extends the functionality of FrontBlocks. It includes additional features and improvements over the free version.

More information in the [FrontBlocks PRO](https://close.technology/wordpress-plugins/front-blocks-pro/?ref=WordPressPlugin) page.

Others Plugins:
- [Closemarketing Plugins](https://profiles.wordpress.org/closemarketing/#content-plugins)

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your
WordPress installation and then activate the Plugin from Plugins page.


== Developers ==
[Official Repository Github](https://github.com/closemarketing/frontblocks)

== Changelog ==

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