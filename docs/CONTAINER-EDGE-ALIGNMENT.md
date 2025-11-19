# Container Edge Alignment

## Overview

The Container Edge Alignment feature adds custom controls to GenerateBlocks Container blocks, allowing you to remove padding from the left or right side. This creates an edge-to-edge effect where one side of the content aligns to the browser edge while the other maintains normal spacing.

## Use Case

This feature is particularly useful when you have a nested container structure like:

1. **Outer Container** - Full width
2. **Middle Container** - Page width defined by GeneratePress (uses global max-width)
3. **Inner Content** - Your actual content

You can apply edge alignment to the middle container (with the defined page width) to create asymmetric layouts where content extends to one edge of the browser while maintaining proper spacing on the other side.

## Important Note

**This feature ONLY appears for containers that use GeneratePress global max-width settings.** It will not show up for containers with custom pixel widths or no width restrictions.

## How to Use

### In the Block Editor

1. Create a GenerateBlocks **Container** block
2. Configure it to use **GeneratePress global max-width** (either through global settings or CSS variables)
3. Select the container
4. In the right sidebar (Inspector), scroll down to find the **"Edge Alignment"** panel
5. Open the panel and you'll see two checkboxes:
   - **Remove Left Padding** - Content will align to the left edge of the browser
   - **Remove Right Padding** - Content will align to the right edge of the browser
6. Check one or both options depending on your desired layout

### Options

#### Remove Left Padding
- Removes all left padding and margin from the container
- Content on the left side will touch the browser edge
- Right side maintains normal spacing

#### Remove Right Padding
- Removes all right padding and margin from the container
- Content on the right side will touch the browser edge
- Left side maintains normal spacing

#### Both Options Selected
- Removes padding and margin from both sides
- Creates a full-width container
- Useful for breaking out of the constrained width

## Visual Example

### Normal Container
```
|  [                Content                ]  |
   ←padding→                    ←padding→
```

### Left Edge Alignment
```
|[Content                                 ]  |
            ←no padding    ←padding→
```

### Right Edge Alignment
```
|  [                                 Content]|
   ←padding→              no padding→
```

## Technical Details

### CSS Classes Applied

When you enable edge alignment, the following CSS classes are automatically added to your container:

- `.frbl-edge-left` - Removes left padding/margin
- `.frbl-edge-right` - Removes right padding/margin

### CSS Rules

```css
.frbl-edge-left {
	padding-left: 0 !important;
	margin-left: 0 !important;
}

.frbl-edge-right {
	padding-right: 0 !important;
	margin-right: 0 !important;
}
```

### Responsive Behavior

By default, edge alignment is maintained on all screen sizes, including mobile. If you need to adjust this behavior, you can add custom CSS to restore normal padding on smaller screens.

## Common Layouts

### Image on One Side, Content on Other
Perfect for hero sections or feature blocks where you want an image to extend to the screen edge while text maintains proper margins.

### Full-Width Background with Offset Content
Create visually interesting layouts where background colors or images extend to one edge while content respects the page width.

### Asymmetric Grid Layouts
Break the typical centered layout by pushing content to one side of the screen while maintaining structure.

## Best Practices

1. **Use with Global Width Containers** - This feature only appears on containers that use GeneratePress global max-width settings

2. **Nested Structure** - Maintain the three-level structure:
   - Outer: Full width
   - Middle: Page width with edge alignment (uses global width)
   - Inner: Your content

3. **Test Responsively** - Always check how your edge-aligned containers look on different screen sizes

4. **Don't Overuse** - Edge alignment is most effective when used sparingly for emphasis

5. **Combine with Other Features** - Works great with:
   - Background colors/images on the outer container
   - Grid layouts inside the container
   - GenerateBlocks flexbox controls

## Troubleshooting

### Edge alignment panel not showing
- **Verify the container uses GeneratePress global max-width** - Check that your container is configured to use the global width from GeneratePress settings or CSS variables like `var(--gp-page-width)`
- This feature intentionally only shows for containers with global width settings

### Edge alignment not working
- Ensure you're applying it to a GenerateBlocks Container block (not a WordPress core group block)
- Check that the container doesn't have `overflow: hidden` which might clip the content
- Verify that no theme CSS is overriding the alignment with higher specificity

### Content still has spacing
- The container might have child elements with their own padding
- Check GenerateBlocks' own spacing settings in the block controls
- Use browser DevTools to inspect which element is adding the spacing

### Works in editor but not frontend
- Clear your site cache (if using a caching plugin)
- Check that the CSS file is being loaded (look in browser DevTools Network tab)
- Verify the classes are being added to the HTML

## Browser Support

This feature works in all modern browsers that support CSS and WordPress block editor:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers

## Version

Introduced in FrontBlocks version 1.2.2

