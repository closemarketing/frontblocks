# FrontBlocks Pattern Examples

This directory contains ready-to-use pattern examples for FrontBlocks features.

## Available Patterns

### 🎠 Carousel Hero Pattern

**File:** `carousel-hero-pattern.json`

**🎯 Quick Access:** This pattern is **automatically registered in WordPress**! 
- Go to Block Editor → Click **+** → Select **"Patterns"** tab
- Find it under **"FrontBlocks"** category
- Or search: `carousel`, `hero`, `slider`, `banner`

**Description:** Full-width hero carousel with smooth transitions, featuring gradient backgrounds, headings, descriptive text, and call-to-action buttons.

**Features:**
- ✅ Full-width slides with Cover blocks
- ✅ Customizable gradients and colors
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ Arrow navigation on sides
- ✅ Smooth transitions
- ✅ Native WordPress blocks
- ✅ One-click insertion from Patterns tab

**Use Cases:**
- Landing page hero sections
- Promotional content
- Product showcases
- Event announcements
- Marketing campaigns

**Documentation:** [See CAROUSEL-PATTERN.md](../CAROUSEL-PATTERN.md)

---

## How to Use Patterns

### Method 1: From WordPress Editor (Recommended)

1. Click the **+** button in WordPress editor
2. Select **"Patterns"** tab
3. Find under **"FrontBlocks"** category
4. Click on **"Hero Carousel"**
5. Customize the content

### Method 2: Copy and Paste

1. Open the pattern JSON file
2. Copy the `content` value (the HTML code)
3. In WordPress editor, click the `+` button
4. Select "Code Editor" view (⋮ menu → Code editor)
5. Paste the HTML code
6. Switch back to Visual editor
7. Customize the content

---

## Pattern Structure

Each pattern file includes:

```json
{
  "name": "pattern-slug",
  "title": "Pattern Display Name",
  "description": "What this pattern does",
  "keywords": ["searchable", "terms"],
  "categories": ["category"],
  "content": "<!-- WordPress block HTML -->",
  "instructions": {
    "how_to_use": [],
    "customization": {}
  },
  "requirements": [],
  "version": "1.0"
}
```

---

## Requirements

All patterns require:
- ✅ FrontBlocks plugin installed and activated
- ✅ WordPress 5.0 or higher
- ⚡ GeneratePress theme (recommended but not required)

Specific pattern requirements are listed in each pattern file.

---

## Customization Tips

### Changing Colors

**Gradients:**
```html
customGradient="linear-gradient(135deg,rgb(224,15,15) 0%,rgb(225,15,15) 100%)"
```

**Solid Colors:**
```html
customOverlayColor="#006f49"
```

### Adjusting Spacing

```html
style="padding-top:120px;padding-bottom:120px"
```

### Content Width

```html
"contentSize":"800px"
```

Change to: `600px`, `1000px`, `1200px`, etc.

### Typography

```html
style="font-size:52px;font-weight:700;line-height:1.2"
```

---

## Support

- 📖 [Full Documentation](../README.md)
- 🐛 [Report Issues](https://github.com/closemarketing/frontblocks/issues)
- 💬 [Community Support](https://close.technology/support)

---

## License

All patterns are provided under GPL-2.0+ license, same as FrontBlocks plugin.

**Created by:** Closemarketing - https://close.technology
