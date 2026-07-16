---
name: brave-fix-render-block-hack
description: >
  Fix broken `render_block(['blockName' => 'x/y'])` hack in Brave/Sage blade
  templates (used to force-enqueue a block's CSS/JS without rendering it).
  Broke on WP core update: render_block() needs real WP_Block context now.
  Fix: enqueue block's registered handles directly via WP_Block_Type_Registry.
  Trigger on: missing tab/accordion/block styles/scripts on front end,
  render_block() acting up, "fix the block asset hack", or the
  `// Render the required block assets.` comment pattern in .blade.php.
---

# Fix render_block() asset-enqueue hack

## Bug

```blade
@php
	// Render the required block assets.
	render_block(['blockName' => 'yard/tabs']);
	render_block(['blockName' => 'yard/tabs-item']);
@endphp
```

Discards output, only wanted enqueue side effect. Broke because
`WP_Block_Type::render()` now needs real `WP_Block` context.

## Fix

Look up block's registered handles, enqueue directly — same handles WP would've
enqueued, no render, immune to render_block() internals changing again. Late
enqueue (after wp_head) is fine, Sage's wp_footer() flushes late assets.

```php
$blockType = \WP_Block_Type_Registry::get_instance()->get_registered($blockName);

if (! $blockType) {
	return;
}

foreach ([...$blockType->style_handles, ...$blockType->view_style_handles] as $handle) {
	wp_enqueue_style($handle);
}

foreach ([...$blockType->script_handles, ...$blockType->view_script_handles] as $handle) {
	wp_enqueue_script($handle);
}
```

## Steps

1. Find hack: `grep -rn "render_block(\['blockName'" resources/views/`
2. Block has PHP class (`app/Blocks/<Name>/<Name>.php`)? → add private
   `enqueueBlockAssets(string $blockName)` helper there (reuse if exists), call
   from `render()` only for the view branch that needs it:

   ```php
   if ($view === 'tabs') {
   	$this->enqueueBlockAssets('yard/tabs');
   	$this->enqueueBlockAssets('yard/tabs-item');
   }
   ```

   No PHP class (plain blade component) → inline `@php` loop in the blade
   file, no new file/helper:

   ```blade
   @php
   	foreach (['yard/tabs', 'yard/tabs-item'] as $blockName) {
   		$blockType = \WP_Block_Type_Registry::get_instance()->get_registered($blockName);
   		if ($blockType) {
   			foreach ([...$blockType->style_handles, ...$blockType->view_style_handles] as $handle) {
   				wp_enqueue_style($handle);
   			}
   			foreach ([...$blockType->script_handles, ...$blockType->view_script_handles] as $handle) {
   				wp_enqueue_script($handle);
   			}
   		}
   	}
   @endphp
   ```
3. Delete old `render_block()` lines + comment.
4. Verify blockName resolves: `grep -rn "\"name\": \"yard/tabs\"" web/app/plugins/*/build/**/block.json`
