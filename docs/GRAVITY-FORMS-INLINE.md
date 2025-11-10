# Gravity Forms Inline Layout

## Overview

The Gravity Forms Inline Layout feature allows you to display form fields and submit buttons on the same line, perfect for simple forms like newsletter signups or search bars.

## Features

- **One-click activation**: Simply toggle the inline layout option in the block settings
- **Customizable spacing**: Adjust the gap between form elements (0-50px)
- **Responsive design**: Automatically stacks vertically on mobile devices (≤768px)
- **No custom CSS required**: Everything works out of the box
- **Works with all Gravity Forms field types**: Email, text, select, and more

## How to Use

### Step 1: Enable the Feature in Settings

1. Go to **Appearance > FrontBlocks** in your WordPress admin
2. In the **Features** section, find **"Gravity Forms Inline Layout"**
3. Toggle it **ON**
4. Click **Save Settings**

### Step 2: Add a Gravity Forms Block

1. In the WordPress block editor, add a **Gravity Forms** block
2. Select the form you want to display from the form selector

### Step 3: Enable Inline Layout

1. With the Gravity Forms block selected, look in the right sidebar (Block settings)
2. Find the **"FrontBlocks Inline Layout"** panel
3. Toggle **"Enable Inline Layout"** to ON

**Note**: If you don't see the "FrontBlocks Inline Layout" panel, make sure you've enabled the feature in Settings (Step 1).

### Step 4: Customize (Optional)

1. Adjust the **"Gap between elements"** slider to set the spacing between fields and button
2. The preview will update in real-time in the editor

## Use Cases

### Newsletter Signup Form

Perfect for a simple email + subscribe button layout:

```
[Email Field] [Subscribe Button]
```

### Search Form

Ideal for search bars with inline submit button:

```
[Search Field] [Search Button]
```

### Contact Form (Horizontal)

Create compact horizontal forms with multiple fields:

```
[Name] [Email] [Submit]
```

## Technical Details

### CSS Classes

When inline layout is enabled, the following class is added:

- `.frontblocks-gf-inline` - Main wrapper class

### Data Attributes

- `data-gf-inline-enabled="true"` - Indicates inline layout is active
- `data-gf-inline-gap="10"` - Gap value in pixels

### CSS Custom Properties

The gap is set using a CSS custom property:

```css
--gf-inline-gap: 10px;
```

You can override this in your theme if needed.

## Responsive Behavior

- **Desktop (>768px)**: Fields and button display inline
- **Mobile (≤768px)**: Fields and button stack vertically for better usability

## Browser Support

Works in all modern browsers:

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Accessibility

- Labels are visually hidden but remain accessible to screen readers
- Maintains proper form structure for keyboard navigation
- Preserves Gravity Forms validation and error messages

## Compatibility

- **Requires**: Gravity Forms plugin (active)
- **Works with**: GeneratePress, GenerateBlocks
- **WordPress**: 5.0+
- **PHP**: 7.0+

## Troubleshooting

### The inline layout option doesn't appear

**Solution**: Make sure you have:
1. **Enabled the feature in FrontBlocks Settings** (Appearance > FrontBlocks > Features > Gravity Forms Inline Layout)
2. Selected a form from the Gravity Forms block selector
3. The option only appears after a form is selected

### Block shows error "This block has encountered an error"

**Solution**: This was an issue with early versions where custom attributes caused REST API validation errors. If you see this:
1. Make sure you're using the latest version of FrontBlocks
2. Clear browser cache and reload the editor
3. The plugin now filters custom attributes from REST API requests automatically

### Fields are not displaying inline

**Solution**: 
1. Check that the toggle is enabled in the block settings
2. Clear browser cache
3. Ensure no conflicting CSS from your theme

### Gap setting not working

**Solution**:
1. The gap uses CSS custom properties
2. Some older browsers may not support this
3. Update to a modern browser version

## Advanced Customization

### Custom CSS

You can add your own CSS to further customize the inline layout:

```css
/* Change button width */
.frontblocks-gf-inline .gform_footer {
    flex: 0 0 150px;
}

/* Keep labels visible */
.frontblocks-gf-inline .gfield_label {
    position: static;
    width: auto;
    height: auto;
}

/* Custom mobile breakpoint */
@media (max-width: 992px) {
    .frontblocks-gf-inline .gform_wrapper form {
        flex-direction: column;
    }
}
```

### Hooks and Filters

The feature uses standard WordPress hooks:

**Filter**: `render_block_gravityforms/form`
- Modify block output before rendering

**Action**: `gform_post_render`
- Reinitialize after AJAX form submission

## Support

For issues or questions:

1. Check the [GitHub repository](https://github.com/closemarketing/frontblocks)
2. Visit [Close Technology support](https://close.technology/)
3. WordPress.org support forum

## Changelog

See the main plugin changelog in `readme.txt` for version history.

