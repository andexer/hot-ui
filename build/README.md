# Hot-UI Build Configuration (Tailwind CSS v4)

This directory contains optional build configuration files for Tailwind CSS v4, PostCSS, and Vite. These files allow you to compile and optimize Hot-UI's CSS for production use.

## Why Use Build Configuration?

Hot-UI components use Tailwind CSS v4 classes. By default, the package provides pre-compiled CSS, but you can:

- **Optimize CSS**: Only include the classes you actually use
- **Customize**: Add your own Tailwind v4 configuration
- **Production-ready**: Minify and optimize for faster loading
- **Development**: Use Vite for hot module replacement during development

## Installation

### Option 1: Publish Configs (Recommended)

```bash
php spark hot-ui:publish build
```

This copies the configuration files to your project root:
- `tailwind.config.js` (ES Module for Tailwind v4)
- `postcss.config.js`
- `vite.config.js`

### Option 2: Publish via Install Wizard

When running the installation wizard:

```bash
php spark hot-ui:install
```

You'll be prompted to publish build configs (choose 'y' when asked).

## Setup

### 1. Install Dependencies

```bash
npm install -D tailwindcss@next postcss autoprefixer vite
```

**Note**: Use `tailwindcss@next` for Tailwind v4 (current stable release may vary).

### 2. Configure tailwind.config.js

The published `tailwind.config.js` is pre-configured for Hot-UI with Tailwind v4 syntax:

```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './app/Views/**/*.php',
    './app/Views/**/*.html',
    './app/Components/**/*.php',
    './resources/views/**/*.php',
    './resources/views/**/*.html',
  ],
  theme: {
    extend: {
      // Hot-UI theme tokens are loaded from hot-ui.css via CSS custom properties
      // No need to duplicate them here
    },
  },
  plugins: [
    // Add Tailwind v4 plugins here if needed
    // @tailwindcss/forms,
    // @tailwindcss/typography,
  ],
};
```

**Important**: 
- Uses ES Module syntax (`export default`) for Tailwind v4
- Don't duplicate Hot-UI's theme tokens in your `tailwind.config.js`
- Theme tokens are loaded from `hot-ui.css` via CSS custom properties

### 3. Build CSS

#### Option A: Using Tailwind CLI (Simple)

```bash
npx tailwindcss -i ./app/Views/hotui/css/app.css -o ./public/assets/hot-ui.css
```

#### Option B: Using Vite (Recommended for Development)

Update your `package.json`:

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  }
}
```

Run development server with hot reload:

```bash
npm run dev
```

Build for production:

```bash
npm run build
```

## Usage in Your Application

### Option 1: Use Pre-compiled CSS (Default)

If you don't want to set up build tools, Hot-UI provides pre-compiled CSS:

```php
// In your layout or view
<?= asset('hot-ui/hot-ui.min.css') ?>
```

### Option 2: Use Compiled CSS

After building with Tailwind v4:

```php
// In your layout or view
<?= asset('assets/hot-ui.css') ?>
```

## Customization

### Adding Custom Tailwind v4 Plugins

1. Install the plugin:
```bash
npm install -D @tailwindcss/forms
```

2. Add to `tailwind.config.js`:
```javascript
plugins: [
  @tailwindcss/forms,
  @tailwindcss/typography,
],
```

### Adding Custom Colors/Fonts

Hot-UI uses CSS custom properties for theming. To add custom values:

1. Add to your CSS before importing Hot-UI:
```css
:root {
  --custom-color: oklch(0.5 0.2 250);
}
```

2. Use in Tailwind:
```css
.bg-custom-color {
  background-color: var(--custom-color);
}
```

## Hot-UI Theme System

Hot-UI uses a sophisticated theme system with multiple dimensions:

- **Base colors**: neutral, stone, zinc, mauve, olive, mist, taupe, slate, gray
- **Accent colors**: amber, blue, cyan, emerald, fuchsia, green, indigo, lime, orange, pink, purple, red, rose, sky, teal, violet, yellow
- **Radius**: 0, 0.3, 0.5, 0.625, 0.75, 1rem
- **Fonts**: system, serif, mono, inter, geist, manrope, jakarta, space-grotesk, dm-sans, outfit, sora, lora, source-serif
- **Shadows**: none, sm, lg, xl
- **Spacing**: compact, default, comfortable
- **Tracking**: tight, normal, wide

Switch themes via HTML attributes:
```html
<html data-base="slate" data-theme="blue" data-radius="0.5">
```

## Tailwind v4 Specific Notes

### ES Module Syntax

Tailwind v4 uses ES modules by default. The provided `tailwind.config.js` uses:

```javascript
export default { /* config */ }
```

instead of:

```javascript
module.exports = { /* config */ }
```

### Content Sources

Tailwind v4 automatically detects content from your configured paths. The content array in the config is used for explicit control.

### Theme Tokens

Hot-UI's theme tokens are CSS custom properties defined in `hot-ui.css`. Tailwind v4 respects these CSS variables when they're defined in your CSS.

## Troubleshooting

### CSS Not Compiling

1. Ensure Tailwind v4 is installed: `npm list tailwindcss`
2. Check `tailwind.config.js` uses ES module syntax (`export default`)
3. Verify the input CSS file exists: `./app/Views/hotui/css/app.css`
4. Check that content paths match your project structure

### Missing Classes

1. Ensure `hot-ui.css` is imported before your custom CSS
2. Check that Tailwind can find your PHP files in the content array
3. Run build with `--minify` flag for production

### Vite Not Watching

1. Ensure Vite dev server is running: `npm run dev`
2. Check that files are in the `content` array of `tailwind.config.js`
3. Verify no other processes are blocking the port

### Tailwind v4 Migration Issues

If migrating from Tailwind v3:

1. Update to ES module syntax in `tailwind.config.js`
2. Use `export default` instead of `module.exports`
3. Update plugin imports to use ES modules
4. Check the [Tailwind v4 migration guide](https://tailwindcss.com/docs/upgrade-guide)

## Production Checklist

- [ ] Run `npm run build` to compile and minify CSS
- [ ] Test that compiled CSS loads correctly
- [ ] Verify all Hot-UI components render properly
- [ ] Check that custom Tailwind classes work
- [ ] Ensure assets are properly cached (asset versioning)
- [ ] Test with `hot-ui/hot-ui.min.css` as fallback
- [ ] Verify Tailwind v4 compatibility in production

## Resources

- [Tailwind CSS v4 Documentation](https://tailwindcss.com/docs)
- [Tailwind v4 Upgrade Guide](https://tailwindcss.com/docs/upgrade-guide)
- [Vite Documentation](https://vitejs.dev/)
- [PostCSS Documentation](https://postcss.org/)
- [Hot-UI Documentation](https://github.com/andexer/hot-ui)
