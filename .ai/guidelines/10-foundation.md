This is a Brave WordPress project on the Roots stack. Respect each layer's ownership:

- **Bedrock** — project structure, Composer, `.env`, `config/application.php`, mu-plugins. WordPress core and plugins are Composer dependencies; never edit `web/wp/` or commit credentials.
- **Sage** — the theme: Blade templates, components, view composers, Vite assets under `app/` and `resources/`.
- **Acorn** — the Laravel container inside WordPress: service providers, routes, WP-CLI, Blade rendering. Use the container, facades, and dependency injection.
- **Trellis** — provisioning and deploys. Only touch it when the project is Trellis-based.

Rules:
- Console commands are Acorn commands: run `wp acorn <command>`, not bare `php artisan`.
- Prefer Roots/Brave conventions over generic WordPress or generic Laravel advice.
- Keep responsibilities in their layer — don't put theme logic in providers or config logic in templates.
