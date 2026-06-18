Brave WordPress project on Roots stack. Layer ownership:

- **Bedrock** — project structure, Composer, `.env`, `config/application.php`, mu-plugins. Never edit `web/wp/` or commit credentials.
- **Sage** — theme: Blade templates, components, view composers, Vite assets under `app/` and `resources/`.
- **Acorn** — Laravel container: service providers, routes, WP-CLI, Blade rendering. Use container, facades, DI.

Rules:
- Console: `wp acorn <command>`, not `php artisan`.
- Prefer Roots/Brave conventions over generic WP or Laravel advice.
- Keep responsibilities in their layer — no theme logic in providers, no config logic in templates.
