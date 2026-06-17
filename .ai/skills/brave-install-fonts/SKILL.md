---
name: brave-install-fonts
description: How to install fonts in a Brave/Sage WordPress theme — custom self-hosted .woff2 files, Google Fonts via spatie/laravel-google-fonts, or Adobe Typekit/Adobe Fonts via a kit URL. Use this skill whenever adding, swapping, or removing fonts in a Brave ecosystem project: mentioning brave, placing woff2 files, writing @font-face declarations, registering Tailwind font utility classes in config.css, enqueuing a Typekit stylesheet in Assets.php, or setting up the Google Fonts package. Also use when someone asks "how do I change the font" or "add a new font family" in a Sage/Brave theme.
---

# Installing Fonts in Brave/Sage

Three approaches depending on font source:

- **Custom/licensed fonts** → self-host as `.woff2` files
- **Google Fonts** → use `spatie/laravel-google-fonts` package
- **Adobe Fonts (Typekit)** → enqueue kit URL in `Assets.php`

---

## Approach 1: Custom Local Fonts

### 1. Add font files

Place `.woff2` files in:

```
web/app/themes/sage/resources/fonts/
```

Naming convention: `FontName-Weight.woff2` (e.g. `ESKlarheitKurrent-Bd.woff2`, `Akkurat.woff2`)

### 2. Declare @font-face

Add to `resources/styles/base/typography.css`:

```css
@font-face {
    font-display: swap;
    font-family: 'Your Font Name';
    font-style: normal;
    font-weight: 400;
    src: url( '@sage/fonts/YourFont.woff2' ) format( 'woff2' );
}
```

- Use the `@sage/fonts/` alias — it resolves to `resources/fonts/` via the Brave Vite config
- One `@font-face` block per weight/style variant
- Always include `font-display: swap`

For multiple weights of the same family:

```css
@font-face {
    font-display: swap;
    font-family: 'ES Klarheit Kurrent';
    font-style: normal;
    font-weight: 400;
    src: url( '@sage/fonts/ESKlarheitKurrent-Md.woff2' ) format( 'woff2' );
}

@font-face {
    font-display: swap;
    font-family: 'ES Klarheit Kurrent';
    font-style: normal;
    font-weight: 700;
    src: url( '@sage/fonts/ESKlarheitKurrent-Bd.woff2' ) format( 'woff2' );
}
```

### 3. Register Tailwind font utility

Add to `resources/styles/base/config.css` inside the `@theme static {}` block, under the Typography section:

```css
--font-[slug]: 'Your Font Name', sans-serif;
```

Tailwind 4 automatically generates a `font-[slug]` utility class from this CSS variable.

**Example:**

```css
--font-akkurat: 'Akkurat', sans-serif;
--font-klarheit: 'ES Klarheit Kurrent', serif;
```

→ generates `font-akkurat` and `font-klarheit` utility classes.

### 4. Use in templates and CSS

In CSS in via Tailwind's `@apply` in `resources/styles/base/base.css` and `resources/styles/base/base-editor.css`:

```css
body {
    @apply font-akkurat;
}

h1, h2, h3 {
    @apply font-klarheit;
}
```

```css
.editor-styles-wrapper {
    @apply font-akkurat;
}
```
`typography.css` is imported in both `frontend.css` and `editor.css`, so fonts load in both the frontend and the Gutenberg block editor.

---

## Approach 2: Google Fonts

This is the default approach. It already exists in projects. Check if it already has it. 

### 1. Install the package

```bash
composer require spatie/laravel-google-fonts
```

### 2. Create the config file

Create `web/app/themes/sage/config/google-fonts.php`:

```php
<?php

declare(strict_types=1);

return [
    'fonts' => [
        'default' => 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300..800&display=swap',
    ],
    'disk' => 'storage-cache',
    'fallback' => env('WP_ENV') === 'production',
];
```

### 4. Register Tailwind font utility

Same as custom fonts — add to `resources/styles/base/config.css` inside `@theme static {}`:

```css
--font-open-sans: 'Open Sans', sans-serif;
```

### 5. Output the font link tag

In `app/Hooks/Assets.php`, add the `registerGoogleFontsFrontend()` method:

```php
// Remove this whole method:
#[Action('admin_head')] 
#[Action('wp_head')]
public function registerGoogleFontsFrontend(): void
{
    echo app(\Spatie\GoogleFonts\GoogleFonts::class)->load(['nonce' => csp_nonce()])->toHtml();
}
```

---

## Approach 3: Adobe Fonts (Typekit)

No package needed. Adobe generates a kit URL; enqueue it as a stylesheet so it loads in both the frontend and the block editor.

### 1. Get your kit URL

In Adobe Fonts, go to your Web Project and copy the embed URL. It looks like:

```
https://use.typekit.net/abc1234.css
```

### 2. Enqueue in Assets.php

Add a `wp_enqueue_style` call inside `registerBlockAssets()` in `app/Hooks/Assets.php`. That hook fires on both frontend and editor (`enqueue_block_assets`):

```php
#[Action('enqueue_block_assets')]
public function registerBlockAssets(): void
{
    wp_enqueue_script('fontawesome', config('app.fontawesome.url'), [], null, true);
    wp_enqueue_style('typekit', 'https://use.typekit.net/abc1234.css', [], null);
}
```

### 3. Register Tailwind font utility

Add to `resources/styles/base/config.css` inside `@theme static {}`.

Use the font family name exactly as Adobe defines it (check the kit's CSS or the Adobe Fonts site):

```css
--font-proxima: 'proxima-nova', sans-serif;
```

No `@font-face` declaration needed in `typography.css` — Adobe's stylesheet handles that.

---

## Remove Google Fonts if switching approach

If the project previously used `spatie/laravel-google-fonts`, clean it up:

```bash
composer remove spatie/laravel-google-fonts
```

Then in `app/Hooks/Assets.php`, delete the `registerGoogleFontsFrontend()` method entirely:

```php
// Remove this whole method:
#[Action('admin_head')]
#[Action('wp_head')]
public function registerGoogleFontsFrontend(): void
{
    echo app(\Spatie\GoogleFonts\GoogleFonts::class)->load(['nonce' => csp_nonce()])->toHtml();
}
```

Then in `config/app.php`, remove the Google Fonts config:

```php
	'providers' => ServiceProvider::defaultProviders()->merge([
		//...
         // Remove this mention: 
		Spatie\GoogleFonts\GoogleFontsServiceProvider::class,
        //...
	])->toArray(),
```

And delete `config/google-fonts.php`.

## What NOT to change

- `theme.json` — `fontFamilies` is intentionally empty; the Gutenberg font picker is disabled in this project