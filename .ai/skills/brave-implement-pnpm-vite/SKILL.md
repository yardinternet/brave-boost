---
name: brave-implement-pnpm-vite
description: Migrate a Brave/Sage WordPress theme from @wordpress/scripts (Webpack) to Vite, switch from npm to pnpm, and update all dev tooling. Use whenever a Brave project needs to move from the old webpack/npm setup to Vite/pnpm. Triggers when the user says "migrate to vite", "implement pnpm", "switch to vite", "vite migration", "replace @wordpress/scripts", or wants to upgrade the build tooling in a Brave/Yard/Sage theme — even if they just say "set up Vite" or "migrate the build".
---

# brave-implement-pnpm-vite

Project structure: theme at `web/app/themes/[theme]/` (often `sage`), scripts at `resources/scripts/`, styles at `resources/styles/`.

---

## Phase 1: Root tooling

**Delete if present:** `cli.js`, `package-lock.json`, `postcss.config.js`, `scripts/` directory

**package.json** — set `engines.node` → `">=22.12.0"`, replace scripts:
```json
{
  "watch": "pnpm run watch:themes & pnpm run watch:blocks",
  "watch:themes": "yard-toolkit watch themes",
  "watch:blocks": "yard-toolkit watch blocks",
  "build": "pnpm run build:themes && pnpm run build:blocks",
  "build:themes": "yard-toolkit build themes",
  "build:blocks": "yard-toolkit build blocks",
  "lint:css": "yard-toolkit lint css",
  "lint:js": "yard-toolkit lint js",
  "format:css": "yard-toolkit format css",
  "format:js": "yard-toolkit format js",
  "format:blade": "yard-toolkit format blade",
  "start": "pnpm run watch",
  "prod-all": "pnpm run build",
  "yard-toolkit": "yard-toolkit"
}
```

Replace `devDependencies`:
```json
{
  "@yardinternet/eslint-config": "^1.2.0",
  "@yardinternet/prettier-config": "^2.0.3",
  "@yardinternet/stylelint-config": "^1.1.3",
  "@yardinternet/toolkit": "^2.0.4",
  "@yardinternet/vite-config": "^1.0.12",
  "tailwindcss": "^4.x",
  "vite": "^7.x"
}
```

Remove from `devDependencies`: `@tailwindcss/postcss`, `@wordpress/scripts`, `@yardinternet/gutenberg-webpack-loaders`, `commander`, `deepmerge`, `inquirer`, `minimist`, `npm`, `npm-run-all`, `prettier` (wp-prettier alias), `shelljs`

Remove from `dependencies`: all `@wordpress/*` packages. Update remaining `@yardinternet/` packages to latest.

**pnpm-workspace.yaml** (new):
```yaml
publicHoistPattern:
    - prettier
    - stylelint
    - eslint
    - stylelint-config-recommended
    - stylelint-config-idiomatic-order
    - '@babel/preset-react'
```

**vite.config.js** (new):
```js
import { braveConfig } from '@yardinternet/vite-config';

export default braveConfig( {
	theme: process.env.THEME,
	entryPoints: [
		'resources/scripts/editor/editor.js',
		'resources/scripts/frontend/frontend.js',
		'resources/styles/editor.css',
		'resources/styles/frontend.css',
	],
} );
```

**vite-blocks.config.js** (new):
```js
import { defineConfig } from 'vite';
import { braveBlocksConfig } from '@yardinternet/vite-config';

export default defineConfig(
	braveBlocksConfig( { blockPath: process.env.BLOCK_PATH } )
);
```

**Lint configs** — rename `.js` → `.cjs`, contents become single re-exports:
- `.prettierrc.cjs`: `module.exports = require( '@yardinternet/prettier-config' );`
- `.stylelintrc.cjs`: `module.exports = require( '@yardinternet/stylelint-config' );`
- `eslint.config.cjs`: `module.exports = require( '@yardinternet/eslint-config' );`

**.prettierignore** — add: `web/app/themes/*/resources/views/block-patterns`

**.env.example** — after `WP_SITEURL` line add: `APP_URL="${WP_HOME}"`

**.lando.yml** — node service: `type: node:22`, build: `pnpm install && pnpm run prod-all`

**.vscode/settings.json** — update:
- `"prettier.configPath"` → `".prettierrc.cjs"`
- Add `"eslint.validate": ["javascript", "javascriptreact", "typescript", "typescriptreact"]`
- Add `"material-icon-theme.files.associations": { "vite-blocks.config.js": "vite" }`

---

## Phase 2: Theme entrypoints

**frontend.js** — remove `import domReady from '@wordpress/dom-ready'`, change `domReady( () => {` → `window.addEventListener( 'DOMContentLoaded', () => {`, remove webpack HMR line (`import.meta.webpackHot`)

**editor.js** — remove `import './blocks'` if present

**resources/styles/base/config.css** — add `jsx` to source glob: `@source './../../**/*.{blade.php,php,js,jsx}';`

---

## Phase 3: Migrate blocks

For each block in `resources/scripts/editor/blocks/[block-name]/`:

1. **Move** to `resources/scripts/blocks/[block-name]/`. If `block.json` lives in root `blocks/[block-name]/`, move it there too.
2. **Rename** all `.js` → `.jsx` (edit, icon, index, any others).
3. **block.json**: ensure `"editorScript": "file:./index.js"` exists; change `"editorStyle"` to `"file:./editor-style.css"`.
4. **CSS**: rename the editor style file to `editor-style.css`; update the import in `edit.jsx` to `import './editor-style.css'`.
5. **index.jsx** — fix `registerBlockType` if using old pattern:
```js
// Old:
registerBlockType( metadata.name, { ...metadata, edit, icon, save } );
// New:
registerBlockType( metadata, { edit, icon, save } );
```
6. **Import extensions** — add `.jsx` to all imports of files just renamed: `import icon from './icon'` → `import icon from './icon.jsx'`

---

## Phase 4: Rename all JSX-using scripts

Vite requires `.jsx` extension for any file containing JSX.

**Detect JSX** — file needs renaming if it contains: angle-bracket tags (`<ComponentName`, `<svg`, `<div`), JSX in call arguments (`icon: { src: (<svg`), or JSX spread (`<BlockEdit { ...props } />`).

Scan `.js` files in:
- `resources/scripts/editor/*.js` — top-level: `block-variations.js`, `block-styles.js`, `hooks.js`, etc.
- `resources/scripts/editor/block-hooks/*.js`
- `resources/scripts/editor/components/*.js`
- `resources/scripts/editor/hooks/*.js`
- `resources/scripts/frontend/**/*.js` — all subdirectories (e.g. `search/`, `views/`)

Rename JSX-containing files `.js` → `.jsx`, then update all importers:
- Explicit extension: `import './block-variations'` → `import './block-variations.jsx'`
- Bare directory where index became `.jsx`: `import './search'` → `import './search/index.jsx'`
- Check entry files (`editor.js`, `frontend.js`) for references to renamed files

---

## Phase 5: Fix CSS @reference paths

Scan every `.css` file in `resources/styles/` and `resources/scripts/`. Rewrite relative `@reference` paths to the `@sage` alias (`@sage` maps to `resources/`):

```css
/* From (relative): */
@reference '../../../../styles/base/config';
/* To (@sage alias, always add .css): */
@reference '@sage/styles/base/config.css';
```

---

## Phase 6: Assets.php

Replace use statements:
```php
use Illuminate\Support\Facades\Vite;
use Yard\Hook\Action;
use Yard\Hook\Filter;
```

Replace `registerFrontendAssets`:
```php
#[Action('wp_head', 99)]
public function registerFrontendAssets(): void
{
    Vite::useHotFile(get_parent_theme_file_path('public/hot'));
    echo Vite::withEntryPoints([
        'web/app/themes/' . get_stylesheet() . '/resources/styles/frontend.css',
        'web/app/themes/' . get_stylesheet() . '/resources/scripts/frontend/frontend.js',
    ])->toHtml();
}
```

Replace `registerBlockEditorAssets`:
```php
#[Action('admin_head')]
public function registerBlockEditorAssets()
{
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }
    $dependencies = json_decode(Vite::content('editor.deps.json'));
    foreach ($dependencies as $dependency) {
        if (! wp_script_is($dependency)) {
            wp_enqueue_script($dependency);
        }
    }
    Vite::useHotFile(get_parent_theme_file_path('public/hot'));
    echo Vite::withEntryPoints([
        'web/app/themes/'. get_stylesheet() . '/resources/scripts/editor/editor.js',
    ])->toHtml();
}
```

Add `injectEditorStyles`:
```php
#[Filter('block_editor_settings_all')]
public function injectEditorStyles($settings)
{
	Vite::useHotFile(get_parent_theme_file_path('public/hot'));
    $style = Vite::asset('web/app/themes/'. get_stylesheet() . '/resources/styles/editor.css');
    $settings['styles'][] = ['css' => "@import url('{$style}')"];
    return $settings;
}
```

If `wp_localize_script` exists, replace with `wp_print_inline_script_tag` in a dedicated `wp_head` / `admin_head` action method.

---

## Phase 7: ACF blocks via Poet (conditional)

Skip if `config/poet.php`'s `'block'` array is empty.

For each `'theme/block-name'` entry:

1. Create `app/Blocks/[BlockName]/[BlockName].php`:
```php
<?php
declare(strict_types=1);
namespace App\Blocks\[BlockName];

class [BlockName]
{
    public function render(array $attributes, string $content)
    {
        return view('blocks.[block-kebab-name]', ['data' => $attributes, 'content' => $content]);
    }
}
```

2. Register in `config/blocks.php`:
```php
'[block-kebab-name]' => [
    'block_type' => '[block-kebab-name]',
    'args' => ['render_callback' => (new [BlockName]())->render(...)],
],
```

3. Remove entry from `config/poet.php`'s `'block'` array.
4. Delete old `app/View/Composers/[BlockName]Composer.php` and its entry in `config/view.php`.
5. Update Blade template — data is now array: add `$data = is_array($data) ? $data : (array) $data;`, replace `$data->prop` → `$data['prop']`.
6. Move `block.json` from root `blocks/[block-name]/` into `resources/scripts/blocks/[block-name]/`.

---

## Phase 8: Finish

```bash
pnpm install
pnpm run format:css
pnpm run format:js
```

---

## Checklist

- [ ] Deleted: cli.js, package-lock.json, postcss.config.js, scripts/
- [ ] package.json: node >=22.12.0, scripts, devDeps
- [ ] pnpm-workspace.yaml, vite.config.js, vite-blocks.config.js created
- [ ] .prettierrc.cjs / .stylelintrc.cjs / eslint.config.cjs (renamed)
- [ ] .prettierignore, .env.example, .lando.yml, .vscode/settings.json updated
- [ ] frontend.js: domReady → addEventListener, webpack HMR removed
- [ ] editor.js: import './blocks' removed
- [ ] config.css: jsx in @source glob
- [ ] Blocks moved to resources/scripts/blocks/, .js → .jsx
- [ ] block.json: editorScript present, editorStyle → editor-style.css
- [ ] index.jsx: registerBlockType uses metadata object (no .name, no spread)
- [ ] All JSX-using .js files renamed → .jsx (editor tree + frontend tree)
- [ ] All import references updated (explicit .jsx, bare dir → ./dir/index.jsx)
- [ ] CSS @reference paths → @sage/ alias
- [ ] Assets.php: Vite enqueuement + injectEditorStyles
- [ ] ACF blocks migrated (if any)
- [ ] pnpm install + format
