# Inline marker (info-window) content builder

Server-side HTML for a marker's info window — `FacetWPMap::setMarkerArgs()` via
`$args['content']`. FacetWP < 4.5.1 path: build an HTML string in PHP; per-marker
Blade `view()` measurably slows map load.

**Ask before building.** Confirm with the user what content the info window shows —
which meta fields (mail, phone, website, address, ...), which taxonomy as labels, and
whether the title links to the post. Build only the requested blocks; drop the rest
(meta list, labels, arrow) from the builder below.

Adapt field keys (`{slug}_mail`, ...) and taxonomy (`{slug}_sector`) to the post
type + confirmed answers. Output is escaped; classes match `assets/facetwp-map.css`.

```php
private function buildMarkerContent(int $postID): string
{
	// Fetch data directly for performance, instead of making a PostData object.
	$url = get_permalink($postID);
	$title = get_the_title($postID);
	$mail = get_field('{slug}_mail', $postID);
	$phone = get_field('{slug}_phone', $postID);
	$website = get_field('{slug}_website', $postID);
	$address = get_field('{slug}_address', $postID);

	$mail = is_string($mail) ? trim($mail) : '';
	$phone = is_string($phone) ? trim($phone) : '';
	$website = is_string($website) ? trim($website) : '';
	$addressText = is_array($address) ? trim((string) ($address['address'] ?? '')) : '';

	$content = '<div class="relative md:pr-8">';
	$content .= '<h3 class="mb-3 text-base leading-snug">';
	$content .= '<a href="' . esc_url(is_string($url) ? $url : '') . '" class="a-linkable-area no-underline text-black focus:text-black hover:text-primary leading-snug!">';
	$content .= esc_html(is_string($title) ? $title : '');
	$content .= '</a></h3>';

	$content .= $this->buildMetaList($mail, $phone, $website, $addressText);
	$content .= $this->buildSectorLabels($this->getSectorNames($postID));

	$content .= '<i class="fa-regular fa-arrow-right text-[1rem] text-primary pointer-events-none absolute bottom-0 right-2 leading-none"></i>';
	$content .= '</div>';

	return $content;
}

private function buildMetaList(string $mail, string $phone, string $website, string $addressText): string
{
	if (! $mail && ! $phone && ! $website && ! $addressText) {
		return '';
	}

	$items = '';

	if ($mail) {
		$items .= $this->buildMetaLink('fa-paper-plane', esc_attr('mailto:' . $mail), esc_html($mail));
	}

	if ($phone) {
		$phoneUrl = preg_replace('/\s+/', '', $phone) ?: '';
		$items .= $this->buildMetaLink('fa-phone', esc_attr('tel:' . $phoneUrl), esc_html($phone));
	}

	if ($website) {
		$items .= $this->buildMetaLink('fa-globe', esc_url($website), esc_html__('Website', 'sage'));
	}

	if ($addressText) {
		$items .= '<li class="meta-item flex items-baseline gap-x-3 leading-snug text-sm">';
		$items .= '<i class="fa-light text-primary fa-fw fa-location-dot min-w-5"></i>';
		$items .= esc_html($addressText);
		$items .= '</li>';
	}

	return '<ul class="meta-list font-sans font-normal flex flex-col flex-wrap gap-x-8 gap-y-2">' . $items . '</ul>';
}

/**
 * Builds a single icon + link list item. $href and $label must already be escaped.
 */
private function buildMetaLink(string $icon, string $href, string $label): string
{
	return '<li class="meta-item flex items-baseline gap-x-3 leading-snug text-sm">'
		. '<i class="fa-light text-primary fa-fw ' . $icon . ' min-w-5"></i>'
		. '<a href="' . $href . '" class="z-1 relative text-inherit no-underline focus:underline">'
		. $label
		. '</a></li>';
}

private function buildSectorLabels(array $sectorNames): string
{
	if ([] === $sectorNames) {
		return '';
	}

	$labels = '';

	foreach ($sectorNames as $sectorName) {
		$labels .= '<div class="general-label rounded-(--general-label-radius) border-(length:--general-label-border-width) border-(--general-label-border-color) bg-(--general-label-bg-color) p-(--general-label-padding) text-(--general-label-color) font-(--general-label-font-weight) text-xs w-fit">' . esc_html($sectorName) . '</div>';
	}

	return '<div class="mt-4 flex flex-wrap items-center gap-2">' . $labels . '</div>';
}

/**
 * @return string[]
 */
private function getSectorNames(int $postID): array
{
	$sectors = get_the_terms($postID, '{slug}_sector');

	if (! is_array($sectors) || is_wp_error($sectors)) {
		return [];
	}

	return array_filter(array_map(
		static fn ($term): string => $term instanceof \WP_Term ? trim($term->name) : '',
		$sectors,
	));
}
```

## Notes

- **`a-linkable-area`** (title link) + the absolute arrow `<i>` make the whole card
  clickable while inner meta links (`z-1 relative`) stay independently clickable.
  Keep both or neither.
- **Escaping**: `esc_url` hrefs, `esc_html` text, `esc_attr` `mailto:`/`tel:`.
  `buildMetaLink` gets already-escaped `$href`/`$label`.
- **Taxonomy labels** reuse the `general-label` design-system component
  (`--general-label-*` tokens). Swap the classes if named differently.
- Drop any block (meta list, labels, arrow) not asked for in Phase 0.

## FacetWP ≥ 4.5.1 alternative

Render a Blade component async instead of string-building:

```php
#[Filter('facetwp_map_marker_content')]
public function markerContent(string $content, array $settings): string
{
	return (string) view('components.facetwp.map-window-content', [
		'postID' => (int) ($settings['post_id'] ?? 0),
	]);
}
```

(plus `facetwp_map_marker_header_content` for the header). Verify the installed
FacetWP version is ≥ 4.5.1 before choosing this path.
