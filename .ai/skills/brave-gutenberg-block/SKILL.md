---
name: brave-gutenberg-block
description: >
  Scaffold a new Gutenberg block in a Brave/Sage WordPress theme. Creates all required
  JS files (block.json, edit.jsx, editor-style.css, icon.jsx, index.jsx, style.css),
  the PHP Block class (app/Blocks/<Name>/<Name>.php), the Blade template
  (resources/views/components/<name>.blade.php), and registers the block in
  config/blocks.php. Use this skill whenever the user wants to create, add, scaffold,
  or generate a new Gutenberg/WordPress block in a Brave or Sage project — even if they
  just say "new block", "add a block", "create a block", or name a block type they want.
---

# Brave Gutenberg Block Scaffolder

Scaffolds all files for a new custom Gutenberg block in a Brave/Sage WordPress theme.

## Step 1 — Gather info

If the user hasn't provided a **block name** (kebab-case, e.g. `test-block`), ask for it now. Do not proceed without it.

Also check if the user mentioned any **specific attributes** (like ToggleControl, SelectControl, TextControl, etc.). If not, default to one `ToggleControl` boilerplate attribute called `isEnabled` (boolean, default false).

Derive all naming variants:
- **kebab**: `test-block`
- **PascalCase**: `TestBlock` (capitalize each word after `-`)
- **Title**: `Test Block`
- **CSS class prefix**: `wp-block-theme-test-block`

## Step 2 — Detect theme root

Theme root is the directory containing `config/blocks.php`. In most Brave projects this is:
`web/app/themes/sage/`

Verify it exists. If working path differs, adapt paths accordingly.

## Step 3 — Create JS block files

Create directory: `resources/scripts/blocks/<kebab>/`

### block.json

```json
{
	"$schema": "https://schemas.wp.org/trunk/block.json",
	"apiVersion": 3,
	"name": "theme/<kebab>",
	"title": "<Title>",
	"category": "theme",
	"description": "",
	"supports": {
		"multiple": true,
		"html": false,
		"reusable": false
	},
	"attributes": {
		<ATTRIBUTES_JSON>
	},

	"editorScript": "file:./index.js",
	"editorStyle": "file:./editor-style.css",
	"style": "file:./style.css"
}
```

For the default ToggleControl boilerplate, `<ATTRIBUTES_JSON>` is:
```json
"isEnabled": {
    "type": "boolean",
    "default": false
}
```

Map user-requested attributes to appropriate Gutenberg attribute types:
- ToggleControl → `"type": "boolean", "default": false`
- SelectControl → `"type": "string", "default": ""`
- TextControl → `"type": "string", "default": ""`
- NumberControl → `"type": "number", "default": 0`

### edit.jsx

Import only what's used. Default boilerplate uses one ToggleControl inside InspectorControls.

For simple blocks (no custom editor UI needed, just render the PHP output in the editor), use `ServerSideRender` instead of a placeholder `<p>` tag. This shows the live PHP-rendered output inside the editor. Use this when:
- The block has no complex editor interactions (no RichText, no InnerBlocks, no inline editing)
- The block renders a self-contained component (newsletter, card, CTA, etc.)

```jsx
/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import './editor-style.css';

const Edit = ( { attributes, setAttributes } ) => {
	const { isEnabled } = attributes;

	return (
		<>
			<div { ...useBlockProps() }>
				<ServerSideRender
					block="theme/<kebab>"
					attributes={ attributes }
				/>
			</div>

			<InspectorControls>
				<PanelBody
					title={ __( 'Instellingen', 'sage' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Ingeschakeld', 'sage' ) }
						checked={ isEnabled }
						onChange={ ( value ) =>
							setAttributes( { isEnabled: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
};

export default Edit;
```

Without ServerSideRender (complex editor interactions), use a placeholder `<p>` instead of the `ServerSideRender` import and component.

If the user requested **multiple attributes**, destructure them all and add the appropriate control per type. If a SelectControl is requested, add a sensible `options` array. Adapt imports accordingly (e.g. add `SelectControl` to the `@wordpress/components` import).

**RadioControl** — use when the block has mutually exclusive named modes that load different views:
```jsx
import { RadioControl } from '@wordpress/components';

<RadioControl
    label={ __( 'Variant', 'sage' ) }
    selected={ variant }
    options={ [
        { label: __( 'Option A', 'sage' ), value: 'option-a' },
        { label: __( 'Option B', 'sage' ), value: 'option-b' },
    ] }
    onChange={ ( value ) => setAttributes( { variant: value } ) }
/>
```

When using RadioControl for view switching, the PHP render method should match on the attribute value and return the corresponding view:
```php
$variant = $attributes['variant'] ?? 'default';
$view = match ($variant) {
    'option-b' => 'components.<kebab>-option-b',
    default    => 'components.<kebab>',
};
return view($view);
```
Create a separate Blade file for each variant.

### editor-style.css

```css
@reference '@sage/styles/base/config.css';
@reference '@sage/styles/base/utilities.css';
@reference '@sage/styles/base/custom-variants.css';

.wp-block-theme-<kebab> {
	/* Editor-specific styles */
}
```

### icon.jsx

Generate a relevant SVG icon (viewBox="0 0 640 640", `<path>` based). Wrap with `BlockIconColor` from `@yardinternet/gutenberg-components`:

```jsx
/**
 * External dependencies
 */
import { BlockIconColor } from '@yardinternet/gutenberg-components';

const icon = {
	src: (
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
			<path d="<SVG_PATH_HERE>" />
		</svg>
	),
	...BlockIconColor,
};

export default icon;
```

Design an appropriate icon for the block's purpose. If the block name gives no clear meaning, use a generic layout/block icon (rectangle with content lines).

### index.jsx

Server-side rendered block (PHP render_callback), so save returns `null`:

```jsx
/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './edit';
import icon from './icon';
import metadata from './block.json';
import './style.css';

registerBlockType( metadata, {
	edit,
	icon,
	save: () => null,
} );
```

Exception: if the user requests InnerBlocks, use `save: () => <InnerBlocks.Content />` and add the InnerBlocks import.

### style.css

```css
@reference '@sage/styles/base/config.css';
@reference '@sage/styles/base/utilities.css';
@reference '@sage/styles/base/custom-variants.css';

.wp-block-theme-<kebab> {
	/* Frontend styles */
}
```

## Step 4 — Create PHP Block class

Path: `app/Blocks/<PascalCase>/<PascalCase>.php`

```php
<?php

declare(strict_types=1);

namespace App\Blocks\<PascalCase>;

use Illuminate\Contracts\View\View;

class <PascalCase>
{
	public function render(array $attributes): View
	{
		return view('components.<kebab>', [
			// Safely pass attributes with fallback defaults:
			<ATTRIBUTE_ASSIGNMENTS>
		]);
	}
}
```

For each attribute from block.json, add a safe assignment line like:
- boolean: `'isEnabled' => $attributes['isEnabled'] ?? false,`
- string: `'label' => $attributes['label'] ?? '',`
- number: `'count' => $attributes['count'] ?? 0,`
- object/array: `'focalPoint' => $attributes['focalPoint'] ?? [],`

Always use `?? <default>` for safety.

## Step 5 — Create Blade template

Path: `resources/views/components/<kebab>.blade.php`

```blade
<div {!! get_block_wrapper_attributes() !!}>
    {{-- Block content here --}}
    @if ($isEnabled)
        <p>Block is enabled</p>
    @endif
</div>
```

Output all passed attributes safely using Blade syntax. For strings use `{{ $attr }}`. For raw HTML use `{!! $attr !!}` only when it's WordPress-generated HTML (like `wp_get_attachment_image`). Booleans use `@if`.

## Step 6 — Register in config/blocks.php

Add the use statement at the top (with other use statements) and a new entry in the return array.

**Add use statement** (alphabetically among existing ones):
```php
use App\Blocks\<PascalCase>\<PascalCase>;
```

**Add array entry** (at end of array, before closing `]`):
```php
'<kebab>' => [
    'block_type' => '<kebab>',
    'args' => [
        'render_callback' => (new <PascalCase>())->render(...),
    ],
],
```

Edit the file with Edit tool — don't overwrite it. Find the last `],` before the closing `];` and insert after it.

## Step 7 — Confirm

List all created/modified files and their paths. Mention that Vite will auto-pick up the new block directory.

---

## Naming Reference

| Input | kebab | PascalCase | CSS prefix |
|-------|-------|------------|------------|
| `test-block` | `test-block` | `TestBlock` | `wp-block-theme-test-block` |
| `hero-section` | `hero-section` | `HeroSection` | `wp-block-theme-hero-section` |
| `card` | `card` | `Card` | `wp-block-theme-card` |

## Notes

- Vite auto-discovers block files; no manual enqueue needed.
- `render_callback` uses PHP 8.1 first-class callable syntax `->render(...)`.
- The Block class in `app/Blocks/` handles only attribute passing. Complex logic (computed properties, helper methods) belongs in a separate View Component (`app/View/Components/`) — only create that if the user requests it or if logic clearly warrants it.
- Keep CSS files minimal at scaffold time; developer fills in real styles.
