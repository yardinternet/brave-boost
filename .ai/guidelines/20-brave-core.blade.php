Use bundled skills before hand-rolling:

- **Post types & taxonomies** — `brave-post-type`
- **Gutenberg blocks** — `brave-gutenberg-block`
- **Frontend tooling** — `brave-implement-pnpm-vite`
- **Fonts** — `brave-install-fonts`

Conventions:
- Register ACF field groups via FieldGroup classes (not admin UI).
- Bind view data via Sage view composers (not inline Blade logic). asdf asdfasdf
- Resolve services from the Acorn container; avoid global state and `new`.

@if (! empty($hasVite))
- Frontend assets built with Vite. Add entries to `vite.config.js`.
@endif
