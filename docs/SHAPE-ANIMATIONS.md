# Custom SVG Animations for Shapes

Add custom animated graphics to GenerateBlocks Shape blocks via JSON configuration. This feature supports both **Lottie/Bodymovin** animations and custom **CSS animations**, automatically detecting the format you provide.

## Features

- **Dual Format Support**: 
  - **Lottie/Bodymovin**: Paste Lottie JSON directly from After Effects
  - **Custom CSS**: Define SVG + `@keyframes` animations
- **Auto-Detection**: Automatically detects whether your JSON is Lottie or CSS format
- **Lottie Library Included**: No external dependencies needed, lottie-web loads automatically
- **JSON Configuration**: Simple JSON format that works for both animation types
- **Full Control**: Loop, autoplay, speed control for Lottie; triggers, duration, delay for CSS
- **Performance Optimized**: Uses CSS transforms and Intersection Observer
- **Accessibility**: Respects user's motion preferences (prefers-reduced-motion)
- **Editor Validation**: Real-time JSON validation with format detection
- **Example Templates**: Built-in example to get started quickly

## JSON Structure

The feature automatically detects and handles two formats:

### Format 1: Lottie/Bodymovin (Recommended)

Simply paste your exported Lottie JSON directly. The system will detect it automatically:

```json
{
  "v": "5.7.4",
  "fr": 30,
  "ip": 0,
  "op": 60,
  "w": 200,
  "h": 200,
  "nm": "My Animation",
  "ddd": 0,
  "assets": [],
  "layers": [...]
}
```

**Optional**: Add animation settings:
```json
{
  "v": "5.7.4",
  "fr": 30,
  ... (rest of Lottie JSON)
  "animation": {
    "loop": true,
    "autoplay": true,
    "speed": 1
  }
}
```

### Format 2: Custom CSS Animation

For custom SVG with CSS keyframes:

```json
{
  "svg": "<svg>...</svg>",
  "animation": {
    "name": "animationName",
    "keyframes": "@keyframes animationName { ... }",
    "duration": "2s",
    "delay": "0s",
    "infinite": true,
    "trigger": "load"
  }
}
```

### JSON Properties

#### svg (required)
- **Type**: String
- **Description**: The complete SVG markup you want to display
- **Example**: `"<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path d='M12...'/></svg>"`

#### animation (required)
Object containing animation configuration:

- **name** (required): Animation identifier (must match the name in keyframes)
- **keyframes** (required): CSS @keyframes definition as a string
- **duration**: Animation duration (default: "1s")
- **delay**: Delay before animation starts (default: "0s")
- **infinite**: Boolean, loop animation continuously (default: false)
- **trigger**: When to trigger animation: "load" or "hover" (default: "load")

## How to Use

### Step 1: Add a Shape Block

1. In the WordPress block editor, add a **GenerateBlocks Shape** block
2. The default shape doesn't matter as it will be replaced by your custom SVG

### Step 2: Enable Custom SVG Animation

1. With the Shape block selected, look in the right sidebar
2. Find the **"FrontBlocks Custom SVG Animation"** panel
3. Toggle **"Enable Custom SVG Animation"** to ON

### Step 3: Configure with JSON

1. In the **JSON Configuration** textarea, paste your JSON
2. The editor will validate your JSON in real-time
3. If there are errors, they'll be shown immediately
4. A green checkmark appears when JSON is valid

### Step 4: Use Example Template (Optional)

1. Click **"📋 Show example JSON"** to expand the example
2. Review the example structure
3. Click **"Use this example"** to populate the field
4. Modify the example to suit your needs

## Examples

### Lottie Animation Examples

#### Example 1: Using a Free Lottie from LottieFiles

1. Visit [LottieFiles.com](https://lottiefiles.com/)
2. Find an animation you like
3. Click "Download" → "Lottie JSON"
4. Copy the entire JSON content
5. Paste directly into the textarea in FrontBlocks

The system will automatically detect it's a Lottie animation and render it perfectly!

#### Example 2: Custom Lottie with Settings

```json
{
  "v": "5.7.4",
  "fr": 60,
  "ip": 0,
  "op": 120,
  "w": 500,
  "h": 500,
  ... (rest of your Lottie JSON from After Effects)
  "animation": {
    "loop": true,
    "autoplay": true,
    "speed": 1.5
  }
}
```

### CSS Animation Examples

#### Example 1: Rotating Shield Icon

```json
{
  "svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" width=\"48\" height=\"48\" fill=\"currentColor\"><path d=\"M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z\"/></svg>",
  "animation": {
    "name": "rotateShield",
    "keyframes": "@keyframes rotateShield { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }",
    "duration": "3s",
    "delay": "0s",
    "infinite": true,
    "trigger": "load"
  }
}
```

### Example 2: Pulsing Heart on Hover

```json
{
  "svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" width=\"48\" height=\"48\" fill=\"#e74c3c\"><path d=\"M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z\"/></svg>",
  "animation": {
    "name": "pulseHeart",
    "keyframes": "@keyframes pulseHeart { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }",
    "duration": "0.6s",
    "delay": "0s",
    "infinite": false,
    "trigger": "hover"
  }
}
```

### Example 3: Bouncing Arrow

```json
{
  "svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" width=\"48\" height=\"48\" fill=\"currentColor\"><path d=\"M7 10l5 5 5-5z\"/></svg>",
  "animation": {
    "name": "bounceArrow",
    "keyframes": "@keyframes bounceArrow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }",
    "duration": "1s",
    "delay": "0.5s",
    "infinite": true,
    "trigger": "load"
  }
}
```

### Example 4: Complex Multi-Step Animation

```json
{
  "svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" width=\"48\" height=\"48\" fill=\"#3498db\"><circle cx=\"12\" cy=\"12\" r=\"10\"/></svg>",
  "animation": {
    "name": "complexMotion",
    "keyframes": "@keyframes complexMotion { 0% { transform: rotate(0deg) scale(1); opacity: 1; } 25% { transform: rotate(90deg) scale(1.2); opacity: 0.8; } 50% { transform: rotate(180deg) scale(1); opacity: 1; } 75% { transform: rotate(270deg) scale(0.8); opacity: 0.8; } 100% { transform: rotate(360deg) scale(1); opacity: 1; } }",
    "duration": "4s",
    "delay": "0s",
    "infinite": true,
    "trigger": "load"
  }
}
```

## Creating Custom Animations

### Step 1: Prepare Your SVG

1. Create or find an SVG icon (e.g., from [heroicons.com](https://heroicons.com/) or [fontawesome.com](https://fontawesome.com/))
2. Copy the complete SVG markup
3. Ensure it includes the `xmlns` attribute
4. Test that it displays correctly by pasting in a browser

**Tip**: Use `fill="currentColor"` to inherit the color from CSS.

### Step 2: Design Your Animation

1. Decide what you want to animate: rotation, scale, translation, opacity, etc.
2. Write the `@keyframes` rule:

```css
@keyframes myAnimation {
  0% { /* starting state */ }
  50% { /* middle state */ }
  100% { /* ending state */ }
}
```

3. Use CSS transforms for best performance:
   - `transform: translateX(10px)` - Move horizontally
   - `transform: translateY(10px)` - Move vertically
   - `transform: rotate(45deg)` - Rotate
   - `transform: scale(1.2)` - Scale up
   - `opacity: 0.5` - Fade

### Step 3: Combine in JSON

1. Escape quotes in your SVG and keyframes
2. Format as JSON
3. Test in the block editor

**JSON Escaping Tips**:
- Replace `"` with `\"` inside strings
- Or use single quotes in SVG/CSS and double quotes for JSON
- Use online JSON validators if needed

## Performance Best Practices

### 1. Use CSS Transforms

✅ **Good** (GPU-accelerated):
```css
@keyframes good { 
  0% { transform: translateX(0); } 
  100% { transform: translateX(100px); } 
}
```

❌ **Avoid** (causes reflow/repaint):
```css
@keyframes bad { 
  0% { left: 0; } 
  100% { left: 100px; } 
}
```

### 2. Limit Simultaneous Animations

- Use 3-5 animated shapes per page maximum
- More animations = slower page performance
- Consider using animations only above the fold

### 3. Choose Appropriate Duration

- **Quick animations**: 0.3-0.8s (hover effects)
- **Medium animations**: 1-2s (on-load effects)
- **Slow animations**: 2-5s (ambient/background effects)

### 4. Infinite Animations

- Infinite animations are paused when out of viewport (automatic optimization)
- Still, use infinite animations sparingly
- Best for background decorations or loading indicators

## Validation and Error Handling

### Common Errors

**"Invalid JSON format"**
- Check for missing commas, quotes, or brackets
- Use a JSON validator tool online
- Copy-paste from a working example and modify

**"Missing svg property in JSON"**
- Ensure your JSON has an "svg" key
- The SVG string cannot be empty

**"Missing required animation properties"**
- Your "animation" object must have "name" and "keyframes"
- Check spelling of property names

**"This block has encountered an error"**
- Your SVG might contain invalid markup
- Try simplifying the SVG
- Check browser console for specific error

### Testing Your JSON

1. Use [jsonlint.com](https://jsonlint.com/) to validate JSON syntax
2. Test SVG separately in [codepen.io](https://codepen.io/)
3. Start with the built-in example and modify incrementally
4. Check the browser console for detailed errors

## Accessibility Considerations

### Motion Preferences

The feature automatically respects `prefers-reduced-motion`:
- Users who prefer reduced motion won't see animations
- Animations are completely disabled, not just reduced
- This is handled automatically, no additional setup needed

### SVG Accessibility

Add ARIA attributes to your SVG:

```json
{
  "svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" aria-hidden=\"true\" role=\"img\"><path d=\"...\"/></svg>",
  ...
}
```

- Use `aria-hidden="true"` for decorative icons
- Use `role="img"` and `aria-label` for meaningful icons

## Troubleshooting

### Animation doesn't appear

**Solution**:
1. Check that "Enable Custom SVG Animation" toggle is ON
2. Validate your JSON (green checkmark should appear)
3. Ensure trigger is set correctly ("load" or "hover")
4. Check browser console for errors

### SVG doesn't display

**Solution**:
1. Test your SVG in isolation (paste in browser)
2. Ensure `xmlns="http://www.w3.org/2000/svg"` is present
3. Check viewBox attribute is correct
4. Verify width/height are reasonable values

### Animation is too fast/slow

**Solution**:
1. Adjust "duration" in animation object
2. Use "s" for seconds (e.g., "2s") or "ms" for milliseconds (e.g., "500ms")
3. Test with different values until it feels right

### Animation doesn't loop

**Solution**:
1. Set `"infinite": true` in animation object
2. Infinite only works with `"trigger": "load"`
3. Hover animations repeat on each hover automatically

### JSON validation fails

**Solution**:
1. Use the built-in example as a starting point
2. Copy your JSON to [jsonlint.com](https://jsonlint.com/) for detailed errors
3. Check for:
   - Missing commas between properties
   - Unescaped quotes inside strings
   - Missing closing brackets

## Advanced Techniques

### Multiple Transform Properties

Combine multiple transforms in one animation:

```css
@keyframes complex {
  0% { 
    transform: translateX(0) rotate(0deg) scale(1);
  }
  100% { 
    transform: translateX(50px) rotate(180deg) scale(1.2);
  }
}
```

### Easing Functions

While the default is `ease-in-out`, you can't customize easing via JSON. To achieve different easing:

```css
@keyframes custom {
  0% { transform: scale(1); }
  30% { transform: scale(1.3); }  /* Overshoot */
  50% { transform: scale(0.9); }  /* Back down */
  100% { transform: scale(1); }   /* Settle */
}
```

### Combining Animations with Transforms

Use CSS `transform-origin` for rotation around specific points:

- Add to your SVG: `style="transform-origin: center;"`
- Or define in keyframes if needed

### Sequencing Multiple Elements

If you want to animate multiple shapes in sequence:
1. Add multiple Shape blocks
2. Use different `delay` values for each
3. Example: Block 1 (delay: 0s), Block 2 (delay: 0.5s), Block 3 (delay: 1s)

## Browser Support

- **Chrome/Edge**: 88+ ✅
- **Firefox**: 78+ ✅
- **Safari**: 14+ ✅
- **Mobile**: iOS Safari 14+, Chrome Android 88+ ✅

All features gracefully degrade in older browsers.

## Security

### SVG Sanitization

All SVG content is sanitized using WordPress's `wp_kses()` with a whitelist of safe SVG elements:
- `svg`, `path`, `circle`, `rect`, `line`, `polygon`, `polyline`, `ellipse`, `g`, `defs`, `clipPath`, `use`

Unsafe elements and attributes are automatically removed.

### CSS Injection

Keyframes are escaped before being output to prevent CSS injection attacks.

## Requirements

- **GenerateBlocks**: Version 1.0+
- **WordPress**: 5.8+
- **PHP**: 7.4+
- **Browser**: Modern browser with CSS animation and JSON support

## Tips and Tricks

### 1. Use SVG Optimization Tools

Before using an SVG:
- Use [SVGOMG](https://jakearchibald.github.io/svgomg/) to optimize
- Reduces file size
- Removes unnecessary attributes

### 2. Color Inheritance

Use `fill="currentColor"` in your SVG to inherit color from CSS:
```html
<svg fill="currentColor">...</svg>
```
Then style with CSS on the Shape block.

### 3. Responsive Sizing

The SVG automatically inherits the size from the Shape block settings. Use GenerateBlocks' responsive controls for width/height.

### 4. Testing Animations

Use [Codepen](https://codepen.io/) to test your keyframes before adding to JSON:
1. Create a simple HTML element
2. Apply your animation
3. Adjust until perfect
4. Copy keyframes to JSON

### 5. Animation Library

Build a library of your favorite animations:
1. Create a note/document with JSON templates
2. Save successful animations for reuse
3. Share with team members

## Related Features

- **Animations Module**: For animating other block types with Animate.css
- **GenerateBlocks**: Required plugin for Shape blocks
- **GenerateBlocks Pro**: Additional shape options

## Support

For issues or questions:

1. Check this documentation first
2. Validate JSON at [jsonlint.com](https://jsonlint.com/)
3. Visit [FrontBlocks support forum](https://wordpress.org/support/plugin/frontblocks/)
4. Report bugs on [GitHub](https://github.com/closemarketing/frontblocks)

## Changelog

### Version 1.3.0
- Initial release of Custom SVG Animation feature
- JSON-based configuration
- Support for custom keyframes
- Real-time validation in editor
- Intersection Observer for performance
