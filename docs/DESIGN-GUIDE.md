# FrontBlocks Settings Page Design Guide

## Design System

### Color Palette

#### Primary Colors
- **Blue 600**: `#2563eb` - Primary actions, links
- **Blue 700**: `#1d4ed8` - Hover states
- **Blue 100**: `#dbeafe` - Badges, light backgrounds

#### Neutral Colors
- **Gray 900**: `#111827` - Headings
- **Gray 700**: `#374151` - Body text
- **Gray 600**: `#4b5563` - Secondary text
- **Gray 500**: `#6b7280` - Tertiary text, hints
- **Gray 200**: `#e5e7eb` - Borders
- **Gray 100**: `#f3f4f6` - Light backgrounds
- **Gray 50**: `#f9fafb` - Page background
- **White**: `#ffffff` - Cards, buttons

#### Accent Colors
- **Green 50**: `#f0fdf4` - Success background
- **Green 400**: `#4ade80` - Success border
- **Green 700**: `#15803d` - Success text

### Typography

#### Font Families
Uses system font stack for optimal performance:
```css
font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
```

#### Font Sizes
- **3xl**: 1.875rem (30px) - Page title
- **xl**: 1.25rem (20px) - Section headings
- **base**: 1rem (16px) - Body text, buttons
- **sm**: 0.875rem (14px) - Helper text
- **xs**: 0.75rem (12px) - Badges

#### Font Weights
- **Bold**: 700 - Headings
- **Semibold**: 600 - Subheadings
- **Medium**: 500 - Labels, badges
- **Normal**: 400 - Body text

### Spacing Scale

Following Tailwind's spacing scale (1 unit = 0.25rem = 4px):

- **1**: 0.25rem (4px)
- **2**: 0.5rem (8px)
- **3**: 0.75rem (12px)
- **4**: 1rem (16px)
- **5**: 1.25rem (20px)
- **6**: 1.5rem (24px)
- **8**: 2rem (32px)

### Border Radius

- **Default**: 0.5rem (8px) - Cards
- **lg**: 0.75rem (12px) - Buttons
- **full**: 9999px - Badges, pills

### Shadows

- **sm**: `0 1px 2px 0 rgb(0 0 0 / 0.05)` - Default cards
- **md**: `0 4px 6px -1px rgb(0 0 0 / 0.1)` - Hover state
- **lg**: `0 10px 15px -3px rgb(0 0 0 / 0.1)` - Elevated elements

## Component Specifications

### Page Header

```html
<div class="tw-mb-8">
  <div class="tw-flex tw-items-center tw-justify-between">
    <!-- Title and description on left -->
    <div>
      <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-mb-2">
      <p class="tw-text-gray-600">
    </div>
    <!-- Version badge on right -->
    <div class="tw-flex tw-items-center tw-space-x-2">
      <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-blue-100 tw-text-blue-800">
    </div>
  </div>
</div>
```

**Spacing**:
- Bottom margin: 2rem (32px)
- Title bottom margin: 0.5rem (8px)
- Badge padding: 0.75rem × 0.25rem (12px × 4px)

### Settings Card

```html
<div class="frbl-card tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
  <!-- Card Header -->
  <div class="tw-px-6 tw-py-5 tw-border-b tw-border-gray-200 tw-bg-gradient-to-r tw-from-gray-50 tw-to-white">
    <h2 class="tw-text-xl tw-font-semibold tw-text-gray-900">
    <div class="tw-mt-2 tw-text-sm tw-text-gray-600">
  </div>
  
  <!-- Card Body -->
  <div class="tw-px-6 tw-py-5">
    <div class="tw-space-y-6">
      <!-- Fields here -->
    </div>
  </div>
</div>
```

**Spacing**:
- Header/Body padding: 1.5rem × 1.25rem (24px × 20px)
- Field spacing: 1.5rem (24px) vertical gap

**Visual Effects**:
- Border: 1px solid gray-200
- Shadow: sm
- Hover shadow: md (with transition)
- Header gradient: gray-50 → white (left to right)

### Checkbox Field

```html
<div class="tw-flex tw-items-center">
  <input type="checkbox" class="frbl-checkbox">
  <label class="tw-ml-3 tw-text-sm tw-text-gray-700">
</div>
<p class="tw-mt-2 tw-text-sm tw-text-gray-500">
```

**Specifications**:
- Checkbox size: 1.25rem × 1.25rem (20px × 20px)
- Checkbox color: blue-600
- Label margin-left: 0.75rem (12px)
- Help text margin-top: 0.5rem (8px)
- Help text color: gray-500

### Submit Button

```html
<button type="submit" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-lg tw-shadow-sm tw-text-white tw-bg-blue-600 hover:tw-bg-blue-700">
  <svg class="tw-w-5 tw-h-5 tw-mr-2 tw--ml-1">
  Save Settings
</button>
```

**Specifications**:
- Padding: 1.5rem × 0.75rem (24px × 12px)
- Font size: 1rem (16px)
- Border radius: 0.75rem (12px)
- Icon size: 1.25rem × 1.25rem (20px × 20px)
- Icon margin-right: 0.5rem (8px)
- Background: blue-600
- Hover background: blue-700
- Focus ring: 2px blue-500

## Responsive Breakpoints

### Mobile (< 640px)
- Stack header elements vertically
- Full-width buttons
- Reduced padding (4px → 2px)

### Tablet (640px - 1024px)
- Maintain horizontal layout
- Standard padding
- Optimized for touch targets

### Desktop (> 1024px)
- Maximum width: 1280px (80rem)
- Centered layout with horizontal padding
- Optimal line lengths for readability

## Accessibility Standards

### Contrast Ratios
All text meets WCAG AA standards:
- **Large text** (≥ 18pt): 3:1 minimum
- **Normal text** (< 18pt): 4.5:1 minimum

### Focus States
All interactive elements have visible focus indicators:
- **Outline**: 2px solid blue-500
- **Ring**: 4px blue-500 with 2px offset

### Touch Targets
Minimum touch target size: 44px × 44px
- Buttons: 48px × 40px (exceeds minimum)
- Checkboxes: 20px × 20px + padding (total 32px)

### Keyboard Navigation
- **Tab**: Navigate between fields
- **Space**: Toggle checkboxes
- **Enter**: Submit form

## Animation & Transitions

### Page Load Animation
```css
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
```
- Duration: 300ms
- Easing: ease-out

### Hover Transitions
- Duration: 200ms
- Easing: cubic-bezier(0.4, 0, 0.2, 1)
- Properties: background-color, box-shadow

### Focus Transitions
- Duration: 150ms
- Easing: cubic-bezier(0.4, 0, 0.2, 1)
- Properties: box-shadow, outline

## Best Practices

### Do's ✅
- Use Tailwind utility classes with `tw-` prefix
- Maintain consistent spacing using the scale
- Follow the established color palette
- Test on all breakpoints
- Ensure proper contrast ratios
- Add descriptive help text
- Use semantic HTML elements

### Don'ts ❌
- Don't use inline styles
- Don't use arbitrary values (use scale)
- Don't mix prefixed and non-prefixed classes
- Don't forget mobile responsiveness
- Don't skip focus states
- Don't use colors outside the palette
- Don't create unnecessary custom CSS

## Tailwind Classes Reference

### Most Used Utilities

#### Layout
- `tw-flex`, `tw-inline-flex`
- `tw-items-center`, `tw-items-start`
- `tw-justify-between`
- `tw-space-x-2`, `tw-space-y-6`
- `tw-max-w-5xl`
- `tw-mx-auto`

#### Spacing
- `tw-px-{n}`, `tw-py-{n}` - Padding
- `tw-mx-{n}`, `tw-my-{n}` - Margin
- `tw-mt-{n}`, `tw-mb-{n}` - Margin top/bottom

#### Typography
- `tw-text-{size}` - Font size
- `tw-font-{weight}` - Font weight
- `tw-text-{color}` - Text color

#### Backgrounds
- `tw-bg-{color}` - Background color
- `tw-bg-gradient-to-r` - Gradient direction

#### Borders
- `tw-border`, `tw-border-{side}`
- `tw-border-{color}`
- `tw-rounded-{size}`

#### Effects
- `tw-shadow-{size}`
- `tw-transition-{property}`
- `tw-duration-{time}`

#### Responsive
- `sm:tw-{utility}` - Tablet and up (640px+)
- `lg:tw-{utility}` - Desktop and up (1024px+)

## Maintenance

### Adding New Settings
1. Follow the card layout pattern
2. Use consistent spacing (space-y-6)
3. Add descriptive help text
4. Ensure mobile responsiveness
5. Test keyboard navigation

### Modifying Colors
1. Update colors in `tailwind.config.js`
2. Rebuild CSS: `npm run build:css`
3. Test contrast ratios
4. Update this design guide

### Custom Components
1. Add to `assets/admin/settings-src.css`
2. Use existing design tokens
3. Document in this guide
4. Rebuild: `npm run build:css`

---

**Version**: 1.0  
**Last Updated**: October 30, 2025  
**Maintained by**: Close·marketing

