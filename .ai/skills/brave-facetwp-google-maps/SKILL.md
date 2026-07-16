---
name: brave-facetwp-google-maps
description: >
  Add a FacetWP map view (Google Maps) to a post type's archive in a Brave/Sage
  WordPress theme — map/list toggle, clustered markers, custom marker info
  windows, and optional proximity (radius) search by postcode/place. Use whenever
  the user wants to show FacetWP results on a map, add a map facet, a
  location/address filter, a "view on map" toggle, marker clustering, or
  proximity/distance search to a Brave project — even if they only say "add a
  map", "map view for members", "show suppliers on a map", "filter by distance",
  or "search by postcode". Always invoke when a FacetWP archive should gain a
  geographic map or radius filter.
---

# Implement Map View for FacetWP

Adds a Google Maps view to an existing FacetWP archive: list/map toggle, clustered
markers, styled per-marker info window, optional proximity radius filter. Facets
auto-register from `config/facetwp/`; the Maps API key is wired via `yard/brave-hooks`.

Follow phases in order. **Ask Phase 0 questions before writing files.**

---

## Naming Convention

English identifiers, Dutch labels + FacetWP `name` field (same as `brave-post-type`).

| Thing | Convention | Example |
|-------|-----------|---------|
| Post type slug | English singular | `member` |
| ACF address field key | `{slug}_address` | `member_address` |
| Map facet file / `name` | `{slug}_map` | `member_map` |
| Proximity facet file | `{slug}_proximity.php` | `member_proximity.php` |
| Proximity facet `name` | Dutch-ish slug | `proximity_distance` |
| Hook class | `App\Hooks\FacetWPMap` (one per project) | |

Map facet `name` must match the `map-facet` Blade view and the JS toggler.

---

## Phase 0 — Ask

1. **Which post type?** Must already have a FacetWP archive
   (`config/facetwp/templates/{slug}.php` + `blocks/FacetWP/templates/{slug}.blade.php`).
   If not, run `brave-post-type` first.
2. **Default view** — map or list? (List is more accessible; sets `defaultView`.)
3. **Info-window content** — which meta fields + taxonomies in the popup? Typical:
   title (always, links to post), mail, phone, website, address, one taxonomy as labels.
4. **Proximity?** If yes: unit (default `km`), radius options (default
   `5, 10, 15, 25, 50, 100`), default radius, autocomplete country (default `nl`).

Infer from theme tokens: Dutch labels, pin colours (`--color-primary`), map height, cluster styling.

---

## Phase 1 — Prerequisites (the silent-failure points)

### 1a. Google Maps API key
Wired globally (`facetwp_gmaps_api_key` → `env('GOOGLE_MAPS_API_KEY')`); just confirm the value:
- `GOOGLE_MAPS_API_KEY=...` set in `.env` (placeholder in `.env.example`).
- Key needs **Maps JavaScript API** + **Places API**. No Places → proximity autocomplete returns nothing.

### 1b. ACF Google Map address field
Map + proximity both read one ACF **Google Map** field. Check
`web/app/themes/sage/app/FieldGroups/{Name}.php` for `GoogleMap::make(...)`. If missing:

```php
use Extended\ACF\Fields\GoogleMap;

final public const FIELD_ADDRESS = '{slug}_address';

// inside getFields():
static::FIELD_ADDRESS => GoogleMap::make(__('Adres', 'sage'), self::FIELD_ADDRESS),
```

If the post type has a `PostData` class, expose it:

```php
#[Meta({Name}FieldGroup::FIELD_ADDRESS)]
public ?array $address = null;

public function addressFormatted(): string
{
	return $this->address['address'] ?? '';
}
```

Then save each post (ACF stores lat/lng) and re-index (Phase 8). Empty address = no marker.

### 1c. Remove the `cookie-law-info` plugin
**Required.** CookieYes defers/wraps inline scripts, breaking the
**OverlappingMarkerSpiderfier** library FacetWP loads to fan out markers on the same
coordinates. Symptom: overlapping pins won't separate / map JS errors after consent loads.

Remove from `composer.json`:

```diff
- "wp-plugin/cookie-law-info": "^3.2",
```

Then `composer update wp-plugin/cookie-law-info --no-install` (or full `composer update`)
to drop it from `composer.lock`. Also remove the `@yardinternet/a11y-cookie-yes` and related JavaScript and CSS code. 

---

## Phase 2 — CSP Directives

Google domains are CSP-blocked by default. In `web/app/themes/sage/app/Csp/Policy.php`:

```php
use Spatie\Csp\Directive;

public function configure(): void
{
	parent::configure();

	// Google Maps
	$this->addDirective(Directive::CONNECT, ['https://maps.googleapis.com/', 'https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js.map', 'https://places.googleapis.com/$rpc/google.maps.places.v1.Places/AutocompletePlaces'])
		->addDirective(Directive::STYLE, ['https://fonts.googleapis.com/'])
		->addDirective(Directive::IMG, ['https://maps.gstatic.com', 'https://maps.googleapis.com']);
}
```

The `unpkg.com/...markerclusterer...js.map` entry silences a clusterer sourcemap fetch.

---

## Phase 3 — Facets

Any file in `config/facetwp/facets/` auto-merges into `facetwp_facets` (via
`yard/brave-hooks`). Create the file, re-index (Phase 8).

### 3a. Map facet — `config/facetwp/facets/{slug}_map.php`

```php
<?php

declare(strict_types=1);

return [
	'name' => '{slug}_map',
	'label' => 'Kaart',
	'type' => 'map',
	'source' => 'acf/{slug}_address',
	'map_design' => 'default',
	'btn_label' => '',
	'reset_label' => 'Reset',
	'cluster' => 'yes',
	'ajax_markers' => 'yes',
	'limit' => 'all',
	'map_width' => '100%',
	'map_height' => '100%',
	'min_zoom' => '1',
	'max_zoom' => '20',
	'default_lat' => '',
	'default_lng' => '',
	'default_zoom' => '',
	'marker_content' => '',
];
```

`ajax_markers => 'yes'` loads markers over AJAX (needed for server-built info-window
content, Phase 4). `cluster => 'yes'` feeds the JS clusterer. `map_height => '100%'`
lets the Blade wrapper set the real height.

### 3b. Proximity facet (if requested) — `config/facetwp/facets/{slug}_proximity.php`

Point `source` at the **same ACF Google Map field**. FacetWP's ACF integration splits a
`google_map` value into lat (`facet_value`) + lng (`facet_display_value`) at index time;
the proximity indexer consumes that directly.

```php
<?php

declare(strict_types=1);

return [
	'name' => 'proximity_distance',
	'label' => 'Afstand',
	'type' => 'proximity',
	'source' => 'acf/{slug}_address',
	'unit' => 'km',
	'radius_ui' => 'dropdown',
	'radius_options' => '5, 10, 15, 25, 50, 100',
	'radius_min' => '1',
	'radius_max' => '100',
	'radius_default' => '15',
	'placeholder' => 'Zoek op postcode of plaats',
];
```
Proximity renders in the **filters sidebar**, not on the map. Note the user that the user should add the Facet to the page containing the FacetWP overview.

---

## Phase 4 — Hooks Class

One shared class for every FacetWP map; extend `buildMarkerContent` per post type.

### 4a. Register — `config/hooks.php`

```php
'facetwp-map' => \App\Hooks\FacetWPMap::class,
```

### 4b. `app/Hooks/FacetWPMap.php`

Filters: pin colours, info-window HTML, proximity autocomplete restriction, map controls.

```php
<?php

declare(strict_types=1);

namespace App\Hooks;

use Yard\Hook\Filter;

class FacetWPMap
{
	#[Filter('facetwp_map_marker_args')]
	public function setMarkerArgs(array $args, int $postID): array
	{
		$args['pinOptions']['background'] = 'var(--color-primary)';
		$args['pinOptions']['borderColor'] = 'var(--color-primary)';
		$args['pinOptions']['glyphColor'] = 'var(--color-white)';

		$args['content'] = $this->buildMarkerContent($postID);

		return $args;
	}

	#[Filter('facetwp_proximity_autocomplete_options')]
	public function limitProximityAutocompleteResults(array $options): array
	{
		$options['componentRestrictions'] = [
			'country' => ['nl'],
		];

		return $options;
	}

	#[Filter('facetwp_map_init_args')]
	public function setMapOptions(array $args): array
	{
		$args['init']['zoomControl'] = true;
		$args['init']['cameraControl'] = false;
		$args['init']['mapTypeControl'] = false;
		$args['init']['streetViewControl'] = false;
		$args['init']['fullscreenControl'] = false;

		return $args;
	}

	private function buildMarkerContent(int $postID): string
	{
		// Fetch fields directly for performance instead of building a PostData object.
		$url = get_permalink($postID);
		$title = get_the_title($postID);
		$mail = get_field('{slug}_mail', $postID);
		// build + return the info-window HTML string — see references/marker-content.md
	}
}
```

### 4c. Info-window rendering — pick by FacetWP version

**Ask first.** Before writing `buildMarkerContent`, ask what content goes in the info window
— which meta fields (mail, phone, website, address, ...) + which taxonomy labels, and
whether title links to the post. Build only the blocks requested; drop the rest. Then
follow `references/marker-content.md` with the confirmed fields.

Check `Version:` in `web/app/plugins/facetwp/index.php` (or FacetWP → Settings → Support).

- **< 4.5.1**: build HTML **inline** in PHP. Per-marker Blade `view()` measurably slows
  map load. Default path.
- **≥ 4.5.1**: may render a Blade component over AJAX via `facetwp_map_marker_content` +
  `facetwp_map_marker_header_content`. Only after confirming the version.

Full inline builder (meta list, escaping, taxonomy labels): `references/marker-content.md`.
Adapt field/taxonomy names to Phase 0.

---

## Phase 5 — Blade Views

### 5a. Make `templates/default.blade.php` map-aware
Add props:

```blade
@php
	$displayResultCount ??= true;
	$displaySelections ??= true;
	$resultCountFacet ??= 'result_count';
	$hasMap ??= false;
	$defaultView ??= 'map'; // 'map' or 'list'
	$isMapDefault = $hasMap && $defaultView === 'map';
@endphp
```

Pass flags to result-count:

```blade
<x-facetwp.result-count :facetName="$resultCountFacet" class="bg-primary-200 w-full border-b border-white p-4 sm:p-6"
	:hasMap="$hasMap" :defaultView="$defaultView" />
```

Replace direct template/pagination output with:

```blade
@if ($hasMap)
	<x-facetwp.map-facet :isActive="$isMapDefault" />
@endif

<div @class([
	'facetwp-template-list',
	'is-hidden' => $isMapDefault,
])>
	{!! facetwp_display('template', $template['name']) !!}
	{!! facetwp_display('facet', 'pagination') !!}
</div>
```

### 5b. Enable map for the archive — `templates/{slug}.blade.php`

```blade
@include('blocks.FacetWP.templates.default', [
	'resultCountFacet' => '{dutch_slug}_result_count',
	'hasMap' => true,
	'defaultView' => '{map|list}', // from Phase 0
])
```

### 5c. New components

`components/facetwp/map-facet.blade.php`:

```blade
@props([
	'isActive' => false,
])

<div @class([
	'facetwp-template-map h-(--facetwp-map-height) relative w-full',
	'is-hidden' => !$isActive,
])>
	<div class="rounded-large absolute inset-0 overflow-hidden">
		{!! facetwp_display('facet', '{slug}_map') !!}
	</div>
</div>
```

`components/facetwp/map-toggle-button.blade.php`:

```blade
@props([
    'id' => null,
    'label' => '',
    'icon' => '',
    'active' => false,
])

<button id="{{ $id }}" @class([
	'is-button is-button-small is-button-subtle whitespace-nowrap px-3 py-2 hover:border-white! hover:bg-white!',
	'aria-pressed:pointer-events-none aria-pressed:border-black! aria-pressed:bg-black! aria-pressed:text-white!',
]) aria-pressed="{{ $active ? 'true' : 'false' }}"
	{{ $attributes }}>
	<span>{{ $label }}</span>
	<i class="fa-regular {{ $icon }} -mr-1"></i>
</button>
```

`components/facetwp/map-toggle-buttons.blade.php` — button `id`s must match the JS
`SELECTORS`. Keep the `aria-label`s (they steer screen-reader users to the list view).

```blade
@props([
    'defaultView' => 'map',
])

@php
	$isMapDefault = $defaultView === 'map';
@endphp

<div @class([
	'flex flex-nowrap gap-2 max-lg:order-last xl:gap-3',
	$attributes->get('class'),
])>
	<x-facetwp.map-toggle-button id="js-toggle-button-list" label="Lijst" icon="fa-list-ul" :active="!$isMapDefault"
		aria-label="Lijstweergave. Activeer deze weergave als je hulpsoftware gebruikt." />
	<x-facetwp.map-toggle-button id="js-toggle-button-map" label="Kaart" icon="fa-map-marked-alt" :active="$isMapDefault"
		aria-label="Kaartweergave. Deze weergave is niet volledig toegankelijk voor mensen die afhankelijk zijn van hulpsoftware. We raden aan om de overzichtweergave te gebruiken." />
</div>
```

### 5d. Result-count wraps the toggle
Refactor `components/facetwp/result-count.blade.php` from `<h2>` root to a `<div>` holding
an inner `<h2>` count + the toggle buttons on the same row:

```blade
@props([
    'facetName' => 'result_count',
    'hasMap' => false,
    'defaultView' => 'map',
])

<div
	{{ $attributes->merge([
	    'class' => 'facetwp-result-count-container gap-3 justify-between flex lg:min-h-8 items-center ',
	    'aria-live' => 'polite',
	]) }}>
	<h2 class="text-h4 mb-0">{!! facetwp_display('facet', $facetName) !!}</h2>

	@if ($hasMap)
		<x-facetwp.map-toggle-buttons class="ml-auto" :defaultView="$defaultView" />
	@endif
</div>
```

---

## Phase 6 — Frontend JS

1. Copy `assets/FacetWPMap.js` → `resources/scripts/frontend/components/FacetWPMap.js`.
   Does two things: list/map toggler + custom cluster renderer (SVG count bubble, primary
   colour). Adjust `CLUSTER_COLOR` if the primary token differs.
2. Register in `resources/scripts/frontend/frontend.js`:

```js
import FacetWPMap from './components/FacetWPMap';

// inside the DOMContentLoaded callback, with the other internal components:
FacetWPMap();
```

No-op without both templates + both buttons, so safe to load globally.

---

## Phase 7 — CSS

Three files. Build = Tailwind v4 + Vite via `yard-toolkit`.

1. `resources/styles/vendor/facetwp-map.css` — copy `assets/facetwp-map.css` here verbatim.
   Then import it in `resources/styles/frontend.css`:

   ```css
   @import './vendor/facetwp-map.css';
   ```

   Styles: map height, list/map show-hide + fades, Google info-window (offset primary card
   behind), proximity input/results/radius dropdown.
2. `resources/styles/base/variables.css` — add inside `:root`:

```css
--facetwp-map-height: 500px;

@variant lg {
	--facetwp-map-height: 750px;
}
```

3. `resources/styles/base/motion.css` — add:

```css
@keyframes fadeLeft {
	0% {
		opacity: 0;
		transform: translateX( 40px );
	}

	100% {
		opacity: 1;
		transform: translateX( 0 );
	}
}

@keyframes fadeRight {
	0% {
		opacity: 0;
		transform: translateX( -40px );
	}

	100% {
		opacity: 1;
		transform: translateX( 0 );
	}
}
```

Adapt tokens (`primary-100`, `primary-200`, `rounded-large`, `gray-500`) to the project's
design system if named differently.

---

## Phase 8 — Build, Re-index, Verify

From repo root (pnpm workspace):

```bash
pnpm run build          # or: pnpm run build:themes
```

- **Re-index FacetWP** (lat/lng into the index): WP admin → Settings → FacetWP →
  **Re-index**, or `wp facetwp index`. Skip = empty map / empty proximity.
- On the archive, check console:
  - Tiles render, markers appear, clusters show the styled count bubble.
  - Toggle switches list ⇄ map with fade.
  - Marker click opens the info window with chosen fields.
  - Proximity: postcode/place autocompletes (NL), filters by radius.
  - No CSP violations (else revisit Phase 2).

Report build output honestly — surface errors, don't claim success.

---

## Reference files

- `references/marker-content.md` — full inline info-window builder. Read for Phase 4b.
- `assets/FacetWPMap.js` — frontend component, copied verbatim in Phase 6.
- `assets/facetwp-map.css` — component CSS block for Phase 7.
