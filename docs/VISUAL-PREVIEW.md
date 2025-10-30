# FrontBlocks Settings Page - Visual Preview

## Before & After

### Before (Old Design)
```
┌─────────────────────────────────────────────────────────┐
│ Frontblocks Settings                                    │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ Features                                                │
│ Enable or disable Frontblocks features.                │
│                                                         │
│ Enable testimonials                                     │
│ ☐ Enable testimonials                                  │
│                                                         │
│ WooCommerce Features                                    │
│ WooCommerce FrontBlocks PRO is a premium plugin...     │
│ [Buy WooCommerce FrontBlocks PRO]                      │
│                                                         │
│ [Save Changes]                                          │
└─────────────────────────────────────────────────────────┘
```

### After (New Design)
```
┌─────────────────────────────────────────────────────────────────────┐
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                                                               │  │
│  │  FrontBlocks Settings              ┌──────────────────┐      │  │
│  │  Configure your FrontBlocks        │ Version 1.1.0    │      │  │
│  │  features and options.             └──────────────────┘      │  │
│  │                                                               │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │  ┌─────────────────────────────────────────────────────────┐  │  │
│  │  │ Features                                                │  │  │
│  │  │ Enable or disable Frontblocks features.                │  │  │
│  │  └─────────────────────────────────────────────────────────┘  │  │
│  │                                                               │  │
│  │  Enable testimonials                                          │  │
│  │  ☑ Enable testimonials custom post type and functionality    │  │
│  │  When enabled, you can create and manage testimonials        │  │
│  │  in the WordPress admin.                                      │  │
│  │                                                               │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │  ┌─────────────────────────────────────────────────────────┐  │  │
│  │  │ WooCommerce Features                                    │  │  │
│  │  │ WooCommerce FrontBlocks PRO is a premium plugin that    │  │  │
│  │  │ adds more features to WooCommerce FrontBlocks.          │  │  │
│  │  │ [Buy WooCommerce FrontBlocks PRO]                       │  │  │
│  │  └─────────────────────────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ─────────────────────────────────────────────────────────────────  │
│  Changes will be applied        ┌─────────────────────┐            │
│  immediately after saving.      │ ✓ Save Settings     │            │
│                                  └─────────────────────┘            │
│                                                                     │
│              Made with ❤️ by Close·marketing                       │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

## Layout Structure

```
┌─ .frbl-settings-wrapper ────────────────────────────────┐
│ max-width: 1280px                                       │
│ background: gray-50                                     │
│ padding: 32px vertical                                  │
│                                                         │
│ ┌─ Header Section ──────────────────────────────────┐   │
│ │ display: flex                                     │   │
│ │ justify-content: space-between                    │   │
│ │                                                   │   │
│ │ ┌─ Left Side ─────┐  ┌─ Right Side ──────────┐   │   │
│ │ │ Title (3xl)     │  │ Version Badge        │   │   │
│ │ │ Description     │  │ (blue-100/blue-800)  │   │   │
│ │ └─────────────────┘  └──────────────────────┘   │   │
│ └───────────────────────────────────────────────────┘   │
│                                                         │
│ ┌─ Settings Card (Features) ────────────────────────┐   │
│ │ background: white                                 │   │
│ │ border-radius: 8px                                │   │
│ │ shadow: sm                                        │   │
│ │                                                   │   │
│ │ ┌─ Card Header ────────────────────────────────┐  │   │
│ │ │ background: gradient gray-50 → white        │  │   │
│ │ │ padding: 24px                                │  │   │
│ │ │ border-bottom: 1px gray-200                  │  │   │
│ │ │                                              │  │   │
│ │ │ Section Title (xl, semibold)                │  │   │
│ │ │ Section Description (sm, gray-600)          │  │   │
│ │ └──────────────────────────────────────────────┘  │   │
│ │                                                   │   │
│ │ ┌─ Card Body ──────────────────────────────────┐  │   │
│ │ │ padding: 24px                                │  │   │
│ │ │ space-y: 24px                                │  │   │
│ │ │                                              │  │   │
│ │ │ ┌─ Field ──────────────────────────────────┐ │  │   │
│ │ │ │ Label (sm, medium, gray-700)             │ │  │   │
│ │ │ │ ☑ Checkbox with label                    │ │  │   │
│ │ │ │ Help text (sm, gray-500)                 │ │  │   │
│ │ │ └──────────────────────────────────────────┘ │  │   │
│ │ └──────────────────────────────────────────────┘  │   │
│ └───────────────────────────────────────────────────┘   │
│                                                         │
│ ┌─ Settings Card (WooCommerce) ─────────────────────┐   │
│ │ [Same structure as above]                         │   │
│ └───────────────────────────────────────────────────┘   │
│                                                         │
│ ┌─ Submit Section ──────────────────────────────────┐   │
│ │ border-top: 1px gray-200                          │   │
│ │ padding-top: 24px                                 │   │
│ │                                                   │   │
│ │ ┌─ Left ───────┐  ┌─ Right ────────────────────┐ │   │
│ │ │ Help text    │  │ ┌─ Save Button ─────────┐ │ │   │
│ │ │ (gray-500)   │  │ │ ✓ Save Settings       │ │ │   │
│ │ └──────────────┘  │ │ bg: blue-600          │ │ │   │
│ │                   │ │ hover: blue-700       │ │ │   │
│ │                   │ │ shadow-sm             │ │ │   │
│ │                   │ └───────────────────────┘ │ │   │
│ │                   └──────────────────────────────┘   │
│ └───────────────────────────────────────────────────┘   │
│                                                         │
│ ┌─ Footer ──────────────────────────────────────────┐   │
│ │ text-align: center                                │   │
│ │ text: sm, gray-500                                │   │
│ │                                                   │   │
│ │ Made with ❤️ by Close·marketing                  │   │
│ └───────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

## Component Breakdown

### 1. Page Header
```html
<div class="tw-mb-8">
  <div class="tw-flex tw-items-center tw-justify-between">
    <div>
      <h1>FrontBlocks Settings</h1>
      <p>Configure your FrontBlocks features...</p>
    </div>
    <div>
      <span class="badge">Version 1.1.0</span>
    </div>
  </div>
</div>
```

**Visual**:
- Title: 30px, bold, dark gray
- Description: 16px, regular, medium gray
- Badge: Blue background, rounded pill
- Responsive: Stacks vertically on mobile

### 2. Settings Card
```html
<div class="frbl-card tw-bg-white...">
  <div class="tw-px-6 tw-py-5 tw-bg-gradient-to-r...">
    <h2>Features</h2>
    <div>Enable or disable...</div>
  </div>
  <div class="tw-px-6 tw-py-5">
    <!-- Fields here -->
  </div>
</div>
```

**Visual**:
- White background
- 1px light gray border
- Subtle shadow
- Gradient header (light gray to white)
- Hover: Shadow increases

### 3. Checkbox Field
```html
<div class="tw-flex tw-items-center">
  <input type="checkbox" class="frbl-checkbox">
  <label>Enable testimonials...</label>
</div>
<p class="tw-mt-2 tw-text-sm tw-text-gray-500">
  When enabled, you can...
</p>
```

**Visual**:
- Checkbox: 20px × 20px, blue accent
- Label: 14px, dark gray, 12px left margin
- Help text: 14px, medium gray, 8px top margin
- Focus: Blue ring

### 4. Save Button
```html
<button type="submit" class="...tw-bg-blue-600...">
  <svg>✓</svg>
  Save Settings
</button>
```

**Visual**:
- Background: Blue 600
- Text: White, 16px, medium weight
- Icon: 20px × 20px checkmark
- Padding: 24px × 12px
- Hover: Blue 700
- Focus: Blue ring

## Responsive Behavior

### Desktop (> 1024px)
```
┌─────────────────────────────────────────────┐
│  Header: Title ←──→ Version Badge           │
│  ┌─────────────────────────────────────┐    │
│  │ Card: Full width (max 1280px)      │    │
│  └─────────────────────────────────────┘    │
│  ┌─────────────────────────────────────┐    │
│  │ Card: Full width                   │    │
│  └─────────────────────────────────────┘    │
│  Footer: Help Text ←──→ Save Button         │
└─────────────────────────────────────────────┘
```

### Tablet (640px - 1024px)
```
┌────────────────────────────────┐
│  Header: Title ←→ Badge        │
│  ┌──────────────────────────┐  │
│  │ Card: Full width         │  │
│  └──────────────────────────┘  │
│  ┌──────────────────────────┐  │
│  │ Card: Full width         │  │
│  └──────────────────────────┘  │
│  Footer: Help ←→ Button         │
└────────────────────────────────┘
```

### Mobile (< 640px)
```
┌──────────────────────┐
│  Header:             │
│  ┌────────────────┐  │
│  │ Title          │  │
│  │ Description    │  │
│  │ Version Badge  │  │
│  └────────────────┘  │
│                      │
│  ┌────────────────┐  │
│  │ Card           │  │
│  │ Full width     │  │
│  └────────────────┘  │
│                      │
│  ┌────────────────┐  │
│  │ Card           │  │
│  └────────────────┘  │
│                      │
│  Footer:             │
│  ┌────────────────┐  │
│  │ Help Text      │  │
│  │ Save Button    │  │
│  └────────────────┘  │
└──────────────────────┘
```

## Color Reference

### Text Colors
- **Headings**: `#111827` (gray-900)
- **Body**: `#374151` (gray-700)
- **Secondary**: `#6b7280` (gray-500)
- **Links**: `#2563eb` (blue-600)
- **Links Hover**: `#1d4ed8` (blue-700)

### Background Colors
- **Page**: `#f9fafb` (gray-50)
- **Cards**: `#ffffff` (white)
- **Card Header**: `gradient(#f9fafb → #ffffff)`
- **Badge**: `#dbeafe` (blue-100)
- **Button**: `#2563eb` (blue-600)
- **Button Hover**: `#1d4ed8` (blue-700)

### Border Colors
- **Cards**: `#e5e7eb` (gray-200)
- **Dividers**: `#e5e7eb` (gray-200)

## Spacing Reference

### Page Layout
- Page padding: 32px vertical
- Max width: 1280px
- Horizontal padding: 16px (mobile), 24px (tablet), 32px (desktop)

### Card Spacing
- Header/Body padding: 24px × 20px
- Bottom margin: 24px
- Field spacing: 24px vertical gap

### Typography Spacing
- Title bottom margin: 8px
- Help text top margin: 8px
- Label bottom margin: 8px

## Animation Reference

### Slide In (Page Load)
- From: opacity 0, translateY(10px)
- To: opacity 1, translateY(0)
- Duration: 300ms
- Easing: ease-out

### Hover (Cards & Buttons)
- Property: box-shadow, background-color
- Duration: 200ms
- Easing: cubic-bezier(0.4, 0, 0.2, 1)

### Focus (Interactive Elements)
- Property: box-shadow
- Duration: 150ms
- Easing: cubic-bezier(0.4, 0, 0.2, 1)

---

**This visual preview represents the final design implementation of the FrontBlocks Settings Page.**

