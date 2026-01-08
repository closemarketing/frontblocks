# Fluid Typography Module

## Description

The Fluid Typography module automatically converts GeneratePress Pro's static typography settings (with separate Desktop, Tablet, and Mobile values) into modern fluid typography using CSS `clamp()` function.

**Now supports ALL typography elements** - not just headings, but navigation, buttons, site title, footer, and everything else!

## How it Works

Instead of using media queries that cause abrupt "jumps" at specific breakpoints, this module generates smooth, responsive font sizes that scale fluidly between viewports.

### Traditional Approach (Media Queries)
```css
h1 {
  font-size: 20px; /* Mobile */
}

@media (max-width: 1024px) {
  h1 {
    font-size: 40px; /* Tablet */
  }
}

@media (min-width: 1025px) {
  h1 {
    font-size: 80px; /* Desktop */
  }
}
```
❌ **Problem**: Abrupt jumps at 768px and 1024px breakpoints

### Fluid Typography Approach (clamp())
```css
h1 {
  font-size: clamp(20px, calc(20px + (80 - 20) * ((100vw - 320px) / 1120)), 80px) !important;
}
```
✅ **Solution**: Smooth, gradual scaling from 320px to 1440px viewport

## Features

- ✅ **Universal support** - Works with ALL GeneratePress typography elements
- ✅ **Automatic detection** - Reads from GeneratePress dynamic CSS
- ✅ **Smooth transitions** - No jumps between breakpoints
- ✅ **Modern CSS** - Uses native `clamp()` function
- ✅ **Easy toggle** - Enable/disable in FrontBlocks settings
- ✅ **Zero configuration** - Works automatically with existing GeneratePress settings
- ✅ **Debug mode** - Troubleshooting tools included

## Supported Elements

The module automatically processes **ALL typography elements** configured in GeneratePress, including:

- **Body text** - Main content text
- **Headings** - H1, H2, H3, H4, H5, H6
- **Navigation** - Main navigation links, menu toggle, menu bar items
- **Buttons** - All button types (button, input[type="button"], submit, reset, .button, WP Block buttons)
- **Site branding** - Site title, site description/tagline
- **Header & Footer** - Top bar, footer widgets, site info
- **And any other element** with responsive font-size settings in GeneratePress

The module intelligently detects which elements have different font sizes across devices and only applies fluid typography where needed.

## Requirements

- GeneratePress theme (or child theme)
- GeneratePress Pro plugin (with Typography module)
- Typography settings configured in GeneratePress Customizer

## Usage

### Basic Setup

1. Go to **FrontBlocks Settings** in your WordPress admin
2. Find **"Enable Fluid Typography"** in the **Optional Features** section
3. Toggle it **ON**
4. Save settings

That's it! The module will automatically:
- Read your GeneratePress dynamic CSS
- Detect all responsive typography elements
- Generate fluid `clamp()` rules
- Apply them to your site

### Debug Mode

To troubleshoot or verify the module is working:

1. Visit your site with `?frbl_debug=1` at the end of the URL
2. View page source (Ctrl+U or Cmd+U)
3. Search for `<!-- FRBL Fluid Typography DEBUG`
4. You'll see the generated fluid CSS

## Technical Details

### How It Works Internally

The module:
1. **Reads** `generate_dynamic_css_output` from WordPress database
2. **Parses** the CSS to extract font-size values for each selector
3. **Detects** desktop (base), tablet (1024px), and mobile (768px) values
4. **Generates** fluid `clamp()` rules only for elements with varying sizes
5. **Applies** the CSS inline with `!important` to override defaults

### Formula

```
clamp(MIN, PREFERRED, MAX)
```

Where:
- **MIN** = Mobile font size (smallest)
- **MAX** = Desktop font size (largest)
- **PREFERRED** = `MIN + (MAX - MIN) * ((100vw - 320px) / 1120)`

### Viewport Range

- **Start**: 320px (small mobile phones)
- **End**: 1440px (large desktop screens)
- **Range**: 1120px of continuous fluid scaling

No breakpoints, no jumps - just smooth, proportional scaling! 🎯

## Example Output

For H1 with these settings:
- Mobile: 20px
- Tablet: 40px
- Desktop: 80px

The module generates:
```css
h1 {
  font-size: clamp(20px, calc(20px + (80 - 20) * ((100vw - 320px) / 1120)), 80px) !important;
}
```

This creates **completely fluid scaling**:
- At 320px viewport: **20px** (minimum)
- At 768px viewport: **~44px** (automatically calculated)
- At 1024px viewport: **~58px** (automatically calculated)  
- At 1440px viewport: **80px** (maximum)

### Visual Representation

```
Viewport        →  H1 Size    Navigation    Buttons
═══════════════════════════════════════════════════
320px (mobile)  →  20px       15px          16px
500px           →  30px       16px          17px
768px (tablet)  →  44px       17px          18px
1024px          →  58px       18px          19px
1200px          →  70px       19px          20px
1440px (desktop)→  80px       20px          21px
```

All elements scale smoothly and proportionally! 🚀

## Benefits

1. **Better UX** - Smoother transitions between all device sizes
2. **Perfect for tablets** - Natural scaling through 768px-1024px range
3. **Future-proof** - Works on foldables, ultra-wide, and any screen size
4. **Cleaner CSS** - Reduces media queries significantly
5. **Modern standards** - Leverages latest CSS capabilities
6. **Better readability** - Optimal font sizes at every viewport width
7. **SEO friendly** - Improves user experience metrics

## Browser Support

CSS `clamp()` is supported in all modern browsers:
- ✅ Chrome 79+ (Dec 2019)
- ✅ Firefox 75+ (Apr 2020)
- ✅ Safari 13.1+ (Mar 2020)
- ✅ Edge 79+ (Jan 2020)

Coverage: **96%+ of global users**

## Troubleshooting

### Typography not changing

**Check these:**
1. ✅ GeneratePress Pro is active
2. ✅ Typography settings are configured in Customizer (Appearance → Customize → Typography)
3. ✅ Module is enabled in FrontBlocks Settings
4. ✅ Cache is cleared (browser + WordPress plugins)

**Try this:**
- Visit site with `?frbl_debug=1`
- View source and look for debug comment
- Check if CSS is being generated

### Font sizes look wrong

**Verify:**
1. Mobile and Desktop values are different in GeneratePress
2. Units are the same (both px, or both rem, etc.)
3. Values are numeric (not empty or "auto")

**Common issues:**
- ❌ Same size on all devices → No fluid typography needed
- ❌ Different units (px vs rem) → Cannot calculate
- ❌ Empty values → Nothing to process

### Module not applying

**Check:**
1. FrontBlocks Settings → "Enable Fluid Typography" is **ON**
2. WordPress theme is GeneratePress (or child theme)
3. GeneratePress Pro is installed and active
4. Typography module in GP Pro is enabled

## Developer Notes

### Hook Priority

The module hooks into `wp_enqueue_scripts` with priority **999** to ensure it runs after GeneratePress loads its styles.

### CSS Application

Custom CSS is added inline via `wp_add_inline_style()` targeting the `generate-style` handle.

### Performance

- Minimal overhead (one database query)
- CSS is cached by browser
- No JavaScript required
- Regex parsing is efficient

### Extending

Developers can filter the selectors list or CSS output by hooking into the module (contact support for custom implementations).

## Changelog

### Version 1.0.0
- ✨ Initial release
- ✅ Support for ALL GeneratePress typography elements
- ✅ Automatic detection from dynamic CSS
- ✅ Smooth fluid scaling (320px to 1440px)
- ✅ Debug mode for troubleshooting
- ✅ Toggle in FrontBlocks settings

## Credits

Developed with ❤️ by [Close·Technology](https://close.technology)

Part of the FrontBlocks plugin ecosystem for GeneratePress Pro.
