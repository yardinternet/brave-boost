---
name: brave-facetwp-sticky-post
description: >
  Add featured / sticky-post support to a post type's FacetWP overview in a
  Brave/Sage theme: ACF TrueFalse field, PostData prop, a Hook rewriting
  `facetwp_query_args` so featured posts sort first, a pin icon on the card, and
  a wp eval one-liner backfilling the meta on existing posts. Use when the user wants
  to pin/feature/spotlight/highlight or make "sticky" a post at the top of a
  FacetWP archive — e.g. "make news featurable", "add an uitgelicht toggle",
  "pin a member to the top of the overview".
---

# Add Featured / Sticky Post to a FacetWP Overview

Editor flags a post "Uitgelicht" → it sorts first in the FacetWP overview and shows
a pin icon. Reference: `news` (`app/Hooks/News.php`, `app/Data/NewsData.php`).

**Ask first:** which post type? It needs an existing FacetWP overview
(`config/facetwp/templates/{slug}.php`) — if not, run `brave-post-type`. Read the
existing `FieldGroups/{Name}.php`, `Data/{Name}Data.php`,
`View/Components/Card/{Name}.php`, and the template file to match style and confirm
the template `name` (usually `= {slug}`).

Naming: English identifiers, Dutch labels. `{Name}` = PascalCase singular, `{slug}`
= lowercase (e.g. `News` / `news`). Meta key `{slug}_featured`.

## 1. ACF field — `FieldGroups/{Name}.php`

`use Extended\ACF\Fields\TrueFalse;`, add constant with the other `FIELD_*`, add as
first entry in `getFields()`:

```php
final public const FIELD_FEATURED = '{slug}_featured';

static::FIELD_FEATURED => TrueFalse::make('Uitgelicht', self::FIELD_FEATURED)
    ->helperText('Maakt het artikel sticky in het overzicht.')
    ->stylized()
    ->key('field_{slug}_featured'),
```

## 2. PostData — `Data/{Name}Data.php`

`{Name}FieldGroup` is already aliased in these classes. Add with the other `#[Meta]`:

```php
#[Meta({Name}FieldGroup::FIELD_FEATURED)]
public bool $featured = false;
```

## 3. Hook — new `Hooks/{Name}.php`

```php
<?php

declare(strict_types=1);

namespace App\Hooks;

use App\FieldGroups\{Name} as {Name}FieldGroup;
use Yard\Hook\Filter;

class {Name}
{
	#[Filter('facetwp_query_args')]
	public function sortFeaturedFirst(array $args, $renderer): array
	{
		if ('{template_name}' !== ($renderer->template['name'] ?? null)) {
			return $args;
		}

		$args['meta_query'] = [
			'relation' => 'OR',
			['key' => {Name}FieldGroup::FIELD_FEATURED, 'compare' => 'EXISTS'],
			['key' => {Name}FieldGroup::FIELD_FEATURED, 'compare' => 'NOT EXISTS'],
		];
		$args['meta_key'] = {Name}FieldGroup::FIELD_FEATURED;
		$args['orderby'] = ['meta_value_num' => 'DESC', 'date' => 'DESC'];

		return $args;
	}
}
```

- Template guard is required — `facetwp_query_args` fires for every template.
- The EXISTS/NOT EXISTS OR keeps posts missing the meta from being dropped by the
  `meta_key` join (Phase 6 backfill makes this rare, but ship both).

Register in `config/hooks.php` (keep alphabetical): `'{slug}' => \App\Hooks\{Name}::class,`

## 4. Card — `View/Components/Card.php` + `Card/{Name}.php`

Add prop to the base `Card` constructor: `public ?bool $isFeatured = null,`
Set it in the subclass: `$this->isFeatured = $this->postData->featured;`

## 5. Card icon — `components/card.blade.php`

Wrap the date in a flex row, move `-order-1` to the wrapper:

```blade
@if ($displayDate && $dateTime && $dateString)
	<div class="-order-1 flex items-baseline justify-between gap-4">
		<time class="card-date text-primary -ml-(--card-padding) mb-3 text-sm leading-none md:mb-4"
			datetime="{{ $dateTime }}">{{ $dateString }}</time>
		@if ($isFeatured)
			<i class="fa-solid fa-thumbtack text-[1rem] text-yellow-600"></i>
		@endif
	</div>
@endif
```

If another card exists ( such as `card-special.blade.php`), do the same reading.

## 6. Backfill existing posts (required)

Posts saved before the field existed have no meta row → undefined sort value → they
slip out of the overview. Write `0` to any post missing it. No command class needed
— give the user this one-line `wp eval` to run once after deploy:

```bash
wp eval 'foreach (get_posts(["post_type" => "{slug}", "post_status" => "any", "posts_per_page" => -1, "fields" => "ids"]) as $id) { if (!metadata_exists("post", $id, "{slug}_featured")) { update_post_meta($id, "{slug}_featured", 0); } }'
```

`metadata_exists` makes it idempotent.

## Verify

Clear Acorn cache. Toggle Uitgelicht → post jumps to top, pin shows. Run backfill →
overview count unchanged. Other FacetWP overviews unaffected.
