Use the bundled Brave skills — they encode the exact scaffolding for common tasks. Reach for them before hand-rolling:

- **Post types & taxonomies** — `brave-post-type`: Poet config, ACF FieldGroup, PostData, FacetWP template + facets, Blade loop/card/single views.
- **Gutenberg blocks** — `brave-gutenberg-block`: the Brave block structure.
- **Frontend tooling** — `brave-implement-pnpm-vite`: pnpm + Vite setup.
- **Fonts** — `brave-install-fonts`: self-hosted woff2, Google Fonts, or Typekit.

Conventions:
- Register ACF field groups in PHP via FieldGroup classes, not the admin UI, so they stay version-controlled.
- Bind view data with Sage view composers, not inline logic in Blade templates.
- Resolve services from the Acorn container; avoid global state and `new` for app services.
@if (! empty($hasVite))

- Frontend assets are built with Vite. Add new entries to `vite.config.js`.
@endif
