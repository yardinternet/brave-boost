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

## Step 1 — Gather info

Ask for **block name** if not provided (kebab-case, e.g. `test-block`). Do not proceed without it.
Check for **attributes**; default: `isEnabled` (boolean ToggleControl).

Derive naming variants:
- **kebab**: `test-block`
- **PascalCase**: `TestBlock`
- **Title**: `Test Block`
- **CSS class prefix**: `wp-block-theme-test-block`

## Step 2 — Detect theme root

Theme root = dir with `config/blocks.php`, usually:
`web/app/themes/sage/`

Verify it exists. Adapt paths if different.

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

Default:
```json
"isEnabled": {
    "type": "boolean",
    "default": false
}
```

Attribute type map:
- ToggleControl → `"type": "boolean", "default": false`
- SelectControl → `"type": "string", "default": ""`
- TextControl → `"type": "string", "default": ""`
- NumberControl → `"type": "number", "default": 0`

### edit.jsx

Use `ServerSideRender` for blocks without complex editor interactions (renders live PHP output). When:
- No RichText, InnerBlocks, or inline editing
- Self-contained component (newsletter, card, CTA, etc.)

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

Without ServerSideRender: use `<p>` placeholder.
Multiple attributes: destructure all, add matching control, adapt imports.

**RadioControl** — use for mutually exclusive named modes:
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

RadioControl view switching — PHP render:
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

Relevant icon; fallback to generic layout icon. Generate SVG (`viewBox="0 0 640 640"`), wrap with `BlockIconColor`:

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

### index.jsx

Server-side rendered; `save` returns `null`:

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

Exception: InnerBlocks → `save: () => <InnerBlocks.Content />` with matching import.

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

Safe attribute assignments:
- boolean: `'isEnabled' => $attributes['isEnabled'] ?? false,`
- string: `'label' => $attributes['label'] ?? '',`
- number: `'count' => $attributes['count'] ?? 0,`
- object/array: `'focalPoint' => $attributes['focalPoint'] ?? [],`

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

Strings: `{{ }}`. WP HTML only: `{!! !!}`. Booleans: `@if`.

## Step 6 — Register in config/blocks.php

Use (alphabetical):
```php
use App\Blocks\<PascalCase>\<PascalCase>;
```

Array entry:
```php
'<kebab>' => [
    'block_type' => '<kebab>',
    'args' => [
        'render_callback' => (new <PascalCase>())->render(...),
    ],
],
```

Edit, don't overwrite. Insert after last `],`.

## Step 7 — Confirm

List all created/modified files. Vite auto-discovers block dirs.

---

## Naming Reference

| Input | kebab | PascalCase | CSS prefix |
|-------|-------|------------|------------|
| `test-block` | `test-block` | `TestBlock` | `wp-block-theme-test-block` |
| `hero-section` | `hero-section` | `HeroSection` | `wp-block-theme-hero-section` |
| `card` | `card` | `Card` | `wp-block-theme-card` |

## Notes

- `render_callback` uses PHP 8.1 first-class callable syntax `->render(...)`.
- Block class in `app/Blocks/` handles only attribute passing. Complex logic belongs in a View Component (`app/View/Components/`) — only create if requested or clearly warranted.
