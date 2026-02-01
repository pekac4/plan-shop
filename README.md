# Plan&Shop

Plan&Shop is a Laravel 12 meal-planning app with recipes, meal plans, shopping lists, and community highlights.

## Stack

- PHP 8.5 (Laravel 12)
- Livewire 4 + Flux UI (free)
- Tailwind CSS v4
- Fortify + Socialite
- MySQL 8.4 (via Sail)
- Pest 4 / PHPUnit 12

## Key packages

- laravel/framework
- livewire/livewire
- livewire/flux
- laravel/fortify
- laravel/socialite
- tailwindcss
- pestphp/pest

## Requirements

- Docker + Docker Compose (Laravel Sail)
- Node.js (if you want to run Vite locally) or use Sail for Node commands

## Local setup (Laravel Sail)

```bash
# 1) Clone
git clone <your-fork-url>
cd booster-app

# 2) Env + deps
cp .env.example .env
vendor/bin/sail composer install
vendor/bin/sail npm install

# 3) Start containers
vendor/bin/sail up -d

# 4) App key, storage, database
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan storage:link
vendor/bin/sail artisan migrate --no-interaction

# 5) Build frontend
vendor/bin/sail npm run dev

# 6) Open the app
vendor/bin/sail open
```

Notes:
- Default ports are `APP_PORT=80` and `VITE_PORT=5173` (see `compose.yaml`).
- If Vite assets are missing, run `vendor/bin/sail npm run dev` or `vendor/bin/sail npm run build`.

## Testing

```bash
vendor/bin/sail artisan test --compact
```

## Formatting

```bash
vendor/bin/sail bin pint --dirty
```

## Laravel Boost (MCP)

This repo includes Laravel Boost tooling. To install and run it:

```bash
vendor/bin/sail artisan boost:install
vendor/bin/sail artisan boost:mcp
```

## AI-assisted code

Some code in this repository may be generated or edited with the Codex CLI agent. Please review changes carefully before merging and keep PR descriptions explicit about AI-assisted changes.

## Contributing

- Follow existing conventions in `AGENTS.md` and `CLAUDE.md`.
- Use Sail for all Artisan, Composer, and Node commands.
- Add tests for changes and run the smallest relevant test set.
