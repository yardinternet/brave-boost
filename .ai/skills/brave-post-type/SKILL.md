---
name: brave-post-type
description: >
  Scaffold a complete new WordPress post type in a Brave theme — including the Poet
  post type config, taxonomy configs, ACF FieldGroup class, PostData class, FacetWP
  template + facets, Blade loop/template views, Card component, meta component,
  and single template. Use this skill whenever the user says "create a new post type",
  "add a post type", or names a content type they want to build in a Brave project — even if they don't use the word "post type". Always invoke when the user describes wanting to register a new content type with
  taxonomies, meta fields, or FacetWP integration.
---

# Brave Post Type Scaffolder

Follow phases in order. Ask required questions before writing any files.

---

## Naming Convention

**English for identifiers. Dutch only for labels and URL slugs.**

| Thing | Convention | Example |
|-------|-----------|---------|
| Post type slug | Singular English lowercase | `member` |
| Taxonomy slugs | English | `service`, `sector` |
| PHP class names | Singular English PascalCase | `Member`, `MemberData` |
| File names | English slug | `member.php` |
| ACF field keys | `{slug}_{field}` | `member_mail` |
| `MetaPrefix` / `TaxonomyPrefix` | English slug | `member` |
| `getLocation()` post_type | English slug | `'member'` |
| WordPress labels | Dutch | `'Lid'`, `'Leden'` |
| `rewrite` slug | Dutch plural | `leden` |
| FacetWP `name` field | Dutch equivalent | `lid_sector` |

---

## Phase 1 — Gather Information

Ask if not provided:
- **Post type name** — English singular (e.g. "Member", "Supplier")
- **Taxonomies** — "Do you need taxonomies? If yes, what?" Translate Dutch names to English for slugs; labels stay Dutch.
- **Meta fields** — list with type hints
- **FacetWP** — "Should FacetWP archive templates be created? Which fields/taxonomies as facets?"
- **Card fields** — "Which taxonomies/meta fields on the card?"

**Infer, don't ask:**
- Dutch labels, `menu_icon`, Dutch rewrite slug, parent page slug
- ACF field keys (`{slug}_{field_name}`)
- Field types: "website" → `URL`, "email/mail" → `Email`, "phone" → `Text`, "date/deadline/start/end" → `DatePicker`, "address/location" → `GoogleMap`, "related posts" → `Relationship`, "image" → `Image`, "text block/body" → `Textarea`, "number" → `Number`, "select/status" → `Select`, "true/false/toggle" → `TrueFalse`, "link" → `Link`, "file" → `File`

**Always ask:**
- Number fields — slider/range facet or something else?
- Select/status fields — what are the options?

---

## Phase 2 — Post Type Config

**File:** `web/app/themes/sage/config/poet/post/{slug}.php`

```php
<?php
declare(strict_types=1);

return [
    'enter_title_here' => __('Dutch placeholder', 'sage'),
    'menu_icon' => 'dashicons-{icon}',
    'supports' => [
        'title', 'editor', 'author', 'revisions', 'thumbnail',
        'excerpt', 'page-attributes', 'custom-fields',
        'parent-page' => ['slug' => '{dutch-rewrite-slug}'],
        'data-class' => ['classFQN' => App\Data\{Name}Data::class],
    ],
    'show_in_rest' => true,
    'has_archive' => false,
    'labels' => [
        'name'                  => __('Plural Dutch', 'sage'),
        'singular_name'         => __('Singular Dutch', 'sage'),
        'menu_name'             => __('Plural Dutch', 'sage'),
        'name_admin_bar'        => __('Singular Dutch', 'sage'),
        'add_new'               => __('Nieuwe {singular} toevoegen', 'sage'),
        'add_new_item'          => __('Nieuwe {singular} toevoegen', 'sage'),
        'edit_item'             => __('{Singular} bewerken', 'sage'),
        'new_item'              => __('Nieuwe {singular}', 'sage'),
        'view_item'             => __('Bekijk {singular}', 'sage'),
        'search_items'          => __('Zoek {plural}', 'sage'),
        'not_found'             => __('Geen {plural} gevonden', 'sage'),
        'not_found_in_trash'    => __('Geen {plural} gevonden in de prullenbak', 'sage'),
        'all_items'             => __('Alle {plural}', 'sage'),
    ],
    'rewrite' => ['slug' => '{dutch-rewrite-slug}'],
    'template' => [
        ['core/paragraph', ['placeholder' => __('Korte introductie...', 'sage')]],
        ['core/post-featured-image', ['aspectRatio' => '16/9']],
        ['core/heading', ['placeholder' => __('Optionele koptekst', 'sage')]],
        ['core/paragraph', ['placeholder' => __('Schrijf de rest van de inhoud van de post.', 'sage')]],
    ],
];
```

---

## Phase 3 — Taxonomy Configs

**Files:** `web/app/themes/sage/config/poet/taxonomy/{slug}_{taxonomy}.php`

```php
<?php
declare(strict_types=1);

return [
    'links' => ['{post-type-slug}'],
    'show_in_rest' => true,
    'labels' => [
        'name'                  => __('Plural Dutch', 'sage'),
        'singular_name'         => __('Singular Dutch', 'sage'),
        'menu_name'             => __('Plural Dutch', 'sage'),
        'all_items'             => __('Alle {plural}', 'sage'),
        'edit_item'             => __('{Singular} bewerken', 'sage'),
        'view_item'             => __('Bekijk {singular}', 'sage'),
        'update_item'           => __('Update {singular}', 'sage'),
        'add_new_item'          => __('Nieuwe {singular} toevoegen', 'sage'),
        'new_item_name'         => __('Nieuwe {singular} naam', 'sage'),
        'parent_item'           => __('Hoofd{singular}', 'sage'),
        'parent_item_colon'     => __('Hoofd{singular}:', 'sage'),
        'search_items'          => __('Zoek {plural}', 'sage'),
        'popular_items'         => __('Populaire {plural}', 'sage'),
        'not_found'             => __('Geen {plural} gevonden.', 'sage'),
        'no_terms'              => __('Geen {plural}', 'sage'),
        'items_list'            => __('{Singular} lijst', 'sage'),
        'items_list_navigation' => __('{Singular} lijst navigatie', 'sage'),
    ],
];
```

---

## Phase 4 — ACF FieldGroup Class

**File:** `web/app/themes/sage/app/FieldGroups/{Name}.php`

- Namespace: `App\FieldGroups`
- Extends `Yard\Acf\Registrar\FieldGroup`
- Field key constants: `final public const FIELD_{NAME} = '{slug}_{field_name}'`
- Field type imports from `Extended\ACF\Fields\{Type}` — check `vendor/vinkla/extended-acf/src/Fields/` for exact class names
- `getFields()` returns associative array keyed by field constant (allows child theme overrides)
- `DatePicker` fields: always chain `.format('Ymd')`
- `Relationship` fields: always add `.bidirectional('field_' . self::FIELD_X)->key('field_' . self::FIELD_X)`
- `GoogleMap` fields: append to `.env.example` if missing: `GOOGLE_MAPS_API_KEY=`
- `getLocation()`: `[Location::where('post_type', '==', '{slug}')]`
- `getTitle()`: Dutch admin title wrapped in `__('...', 'sage')`

```php
<?php
declare(strict_types=1);

namespace App\FieldGroups;

use Extended\ACF\Fields\Email;
use Extended\ACF\Fields\URL;
use Yard\Acf\Registrar\FieldGroup;
use WordPlate\Acf\Location;

class {Name} extends FieldGroup
{
    final public const FIELD_MAIL    = '{slug}_mail';
    final public const FIELD_WEBSITE = '{slug}_website';

    public function getFields(): array
    {
        return [
            static::FIELD_MAIL    => Email::make(__('E-mailadres', 'sage'), self::FIELD_MAIL),
            static::FIELD_WEBSITE => URL::make(__('Website', 'sage'), self::FIELD_WEBSITE),
        ];
    }

    public function getLocation(): array
    {
        return [Location::where('post_type', '==', '{slug}')];
    }

    public function getTitle(): string
    {
        return __('Dutch title', 'sage');
    }
}
```

After creating, add to `config/acf.php`:
```php
use App\FieldGroups\{Name};
// add {Name}::class to the field_groups array
```

---

## Phase 5 — PostData Class

**File:** `web/app/themes/sage/app/Data/{Name}Data.php`

- Namespace: `App\Data`
- Extends `Yard\Data\PostData`, uses `Yard\Database\Traits\Related`
- Attributes: `#[MetaPrefix(prefix: '{slug}')]`, `#[TaxonomyPrefix(prefix: '{slug}')]`
- Taxonomies: `#[Terms]`, type `Collection<int, TermData>`, plural var name (`$sectors`)
- Meta types: Text/Email/URL → `string $x = ''`, DatePicker → `?CarbonImmutable $x = null`, GoogleMap/Image → `?array $x = null`, Relationship → `array $x = []`, Number → `int|float $x = 0`, TrueFalse → `bool $x = false`

**Derived methods (add based on field names):**
- `DatePicker` → `{field}Formatted(string $format = 'j F Y'): string` using `wp_date()` + `CarbonImmutable->getTimestamp()`
- Field named "deadline/end/expires" → also add `{field}HasPassed(): bool` using `->isPast()`
- Field named "start/starts" → also add `isUpcoming(): bool` using `->isFuture()`

**Always add `related()`:**
```php
public function related(): Collection
{
    $articles = collect($this->relatedPosts); // or collect([]) if no relationship field

    if ($articles->isEmpty()) {
        $articles = $this->relatedPostsByTaxonomy($this->postType, '{slug}_{first_taxonomy}', $this->{firstTaxonomy}, 3, 'DESC');
    }

    if ($articles->isEmpty()) {
        $articles = $this->nextPosts($this->postType);
    }

    return {Name}Data::collect($articles);
}
```
If no taxonomies, skip straight to `$this->nextPosts()`.

---

## Phase 6 — FacetWP Configuration

### Template
**File:** `web/app/themes/sage/config/facetwp/templates/{slug}.php`

```php
<?php
declare(strict_types=1);

return [
    'name'     => '{slug}',
    'label'    => __('{Dutch plural}', 'sage'),
    'query'    => '',
    'template' => "<?php echo view('blocks.FacetWP.loops.{slug}-loop'); ?>",
    'layout'   => [],
    'query_obj' => [
        'post_type'      => [['label' => __('{Dutch plural}', 'sage'), 'value' => '{slug}']],
        'posts_per_page' => 9,
        'orderby'        => [],
        'filters'        => [],
    ],
    'modes' => ['display' => 'advanced', 'query' => 'visual'],
];
```

### Facets
**Files:** `web/app/themes/sage/config/facetwp/facets/{slug}_{facet}.php`

Taxonomy / text meta → checkboxes:
```php
return [
    'name'             => '{dutch_facet_name}',
    'label'            => __('{Dutch label}', 'sage'),
    'type'             => 'checkboxes',
    'source'           => 'tax/{taxonomy_slug}', // or 'cf/{meta_key}'
    'parent_term'      => '',
    'hierarchical'     => 'no',
    'show_expanded'    => 'no',
    'ghosts'           => 'yes',
    'preserve_ghosts'  => 'no',
    'operator'         => 'and',
    'orderby'          => 'count',
    'count'            => '-1',
    'soft_limit'       => '7',
];
```

Date field → `'type' => 'date_range'` with `'source' => 'cf/{meta_key}'`.

Number field → ask: slider, range, checkboxes, or radio.

**Always create result count facet:**
**File:** `web/app/themes/sage/config/facetwp/facets/{slug}_result_count.php`
```php
return [
    'name'                  => '{dutch_slug}_result_count',
    'label'                 => __('{Dutch} resultaten', 'sage'),
    'type'                  => 'pager',
    'pager_type'            => 'counts',
    'count_text_plural'     => __('Er zijn <span>[total]</span> {dutch_plural} gevonden.', 'sage'),
    'count_text_singular'   => __('Er is <span>1</span> {dutch_singular} gevonden.', 'sage'),
    'count_text_none'       => __('Er zijn geen {dutch_plural} gevonden.', 'sage'),
];
```

**Note:** `name` key uses Dutch slug (e.g. filename `member_sector.php` → `name: 'lid_sector'`).

### Update search.php
Add to `web/app/themes/sage/config/facetwp/templates/search.php` `post_type` array:
```php
['label' => __('{Dutch plural}', 'sage'), 'value' => '{slug}'],
```

---

## Phase 7 — Blade FacetWP Views

**FacetWP template view:**
`web/app/themes/sage/resources/views/blocks/FacetWP/templates/{slug}.blade.php`
```blade
@include('blocks.FacetWP.templates.default', ['resultCountFacet' => '{dutch_slug}_result_count'])
```

**FacetWP loop view:**
`web/app/themes/sage/resources/views/blocks/FacetWP/loops/{slug}-loop.blade.php`

Grid (cards/people/visual): use `js-facetwp-animation *:opacity-0 md:grid md:grid-cols-2 md:gap-6`.
List (detailed rows): use `flex flex-col gap-4`.

```blade
{{-- Grid --}}
<div class="js-facetwp-animation flex flex-col gap-4 *:opacity-0 md:grid md:grid-cols-2 md:gap-6">
    @forelse ($postDataCollection as $postData)
        <x-card.{slug} :postData="$postData" />
    @empty
        <x-facetwp.no-results />
    @endforelse
</div>
```

If using `Direction` enum: add `@use(App\View\Components\Card\Enums\Direction)` at top.

---

## Phase 8 — Card Component

### Card.php
**File:** `web/app/themes/sage/app/View/Components/Card.php`

Add prefixed nullable constructor params (prefix = post type name in camelCase):
```php
public ?string $memberMail = null,
public ?string $memberPhone = null,
public ?array $memberAddress = null,
```
Never use bare names (`$mail`, `$phone`) — those are shared props.

### Card/{Name}.php
**File:** `web/app/themes/sage/app/View/Components/Card/{Name}.php`

```php
<?php
declare(strict_types=1);

namespace App\View\Components\Card;

use App\View\Components\Card;

class {Name} extends Card
{
    protected function hydrate(): void
    {
        $this->{prefixedParam} ??= $this->postData->{fieldName};

        // Collections: use = not ??= (empty Collection is truthy)
        $this->tags = $this->postData->{taxonomyCollection};

        $this->label ??= $this->postData->{taxonomy}->first()?->name;
    }
}
```

### card.blade.php
**File:** `web/app/themes/sage/resources/views/components/card.blade.php`

Add after existing meta components:
```blade
<x-meta.{slug} :mail="$memberMail" :phone="$memberPhone" :address="$memberAddress" />
```

---

## Phase 9 — Meta Component

**File:** `web/app/themes/sage/resources/views/components/meta/{slug}.blade.php`

```blade
@props([
    'mail'    => null,
    'phone'   => null,
    'website' => null,
    'address' => null,
])

@if ($mail || $phone || $website || $address)
    <x-meta.list {{ $attributes }}>
        @if ($mail)
            <x-meta.item icon="fa-paper-plane" :text="$mail" :url="'mailto:' . $mail" />
        @endif
        @if ($phone)
            <x-meta.item icon="fa-phone" :text="$phone" :url="'tel:' . $phone" />
        @endif
        @if ($website)
            <x-meta.item icon="fa-globe" :text="$website" :url="$website" />
        @endif
        @if ($address)
            <x-meta.item icon="fa-location-dot" :text="$address['address']" />
        @endif
    </x-meta.list>
@endif
```

Icons: `fa-paper-plane` (mail), `fa-phone` (phone), `fa-globe` (website), `fa-location-dot` (address).

---

## Phase 10 — Single Template

**File:** `web/app/themes/sage/resources/views/single-{slug}.blade.php`

Check other single-* templates for layout. Most use `<x-layout.article-aside>`. Output ALL taxonomies and meta fields.

```blade
<x-layout.article-aside>
    <x-slot:article>
        <h1>{!! $postData->title() !!}</h1>

        @if ($postData->{taxonomyPlural}->isNotEmpty())
            @foreach ($postData->{taxonomyPlural} as ${singular})
                <div>{!! ${singular}->name !!}</div>
            @endforeach
        @endif

        <x-meta.{slug}
            :mail="$postData->mail"
            :phone="$postData->phone"
            :website="$postData->website"
            :address="$postData->address"
        />

        @if ($postData->someField)
            <div>{{ __('Label', 'sage') }}: {!! $postData->someField !!}</div>
        @endif

        {!! $postData->content() !!}
    </x-slot:article>

    <x-slot:aside></x-slot:aside>

    <x-slot:bottom>
        @if ($postData->related()->isNotEmpty())
            <div class="container">
                <h2 class="mb-6">{{ __('Gerelateerde {plural}', 'sage') }}</h2>
                <div class="grid gap-6 sm:grid-cols-2">
                    @foreach ($postData->related() as $related)
                        <x-card.{slug} :postData="$related" />
                    @endforeach
                </div>
            </div>
        @endif
    </x-slot:bottom>
</x-layout.article-aside>
```

---

## Checklist

- [ ] `config/poet/post/{slug}.php`
- [ ] `config/poet/taxonomy/{slug}_{taxonomy}.php` (one per taxonomy)
- [ ] `app/FieldGroups/{Name}.php`
- [ ] `config/acf.php` (updated)
- [ ] `app/Data/{Name}Data.php`
- [ ] `config/facetwp/templates/{slug}.php`
- [ ] `config/facetwp/facets/{slug}_{facet}.php` (one per facet)
- [ ] `config/facetwp/facets/{slug}_result_count.php`
- [ ] `config/facetwp/templates/search.php` (updated)
- [ ] `resources/views/blocks/FacetWP/templates/{slug}.blade.php`
- [ ] `resources/views/blocks/FacetWP/loops/{slug}-loop.blade.php`
- [ ] `app/View/Components/Card.php` (updated)
- [ ] `app/View/Components/Card/{Name}.php`
- [ ] `resources/views/components/card.blade.php` (updated)
- [ ] `resources/views/components/meta/{slug}.blade.php`
- [ ] `resources/views/single-{slug}.blade.php`
