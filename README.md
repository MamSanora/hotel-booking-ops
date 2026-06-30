# Hotel Booking Ops V5

A Laravel 12 hotel booking & operations application.

> A more detailed, printable version of this guide is available as
> [`Hotel_Booking_Ops_Setup_Guide.docx`](./Hotel_Booking_Ops_Setup_Guide.docx) in the project root.

## Tech Stack

- **Backend:** PHP 8.2+, Laravel 12, Livewire 3, Sanctum
- **Frontend:** Vite 7, Tailwind CSS 3, Alpine.js
- **Database:** MySQL (via XAMPP) or SQLite
- **Tooling:** Composer, npm, Pest (tests), Pint (formatting)

## Why the repo looks "incomplete" after cloning

After cloning you will **not** see `vendor/`, `node_modules/`, or a `.env` file.
This is intentional — they are listed in `.gitignore` and are regenerated locally
from the files that *are* committed.

| Missing item     | How it is restored          | Source of truth                  |
| ---------------- | --------------------------- | -------------------------------- |
| `vendor/`        | `composer install`          | `composer.json` / `composer.lock` |
| `node_modules/`  | `npm install`               | `package.json` / `package-lock.json` |
| `public/build/`  | `npm run build`             | Vite + your source assets        |
| `.env`           | copy from `.env.example`    | `.env.example`                   |
| App key          | `php artisan key:generate`  | generated locally                |
| `public/storage` | `php artisan storage:link`  | symlink to `storage/app/public`  |

## Prerequisites

- PHP 8.2 or higher (XAMPP provides this)
- Composer
- Node.js 18+ and npm
- MySQL (bundled with XAMPP) or SQLite
- Git

## Quick Start (recommended)

The project ships with a `setup` script in `composer.json` that automates everything:

```bash
git clone <your-repo-url>
cd Hotel_Booking_Ops_V5
composer setup
```

`composer setup` runs, in order:

```text
composer install         # restore PHP dependencies (vendor/)
copy .env.example .env    # create the environment file
php artisan key:generate  # generate the APP_KEY
php artisan migrate       # build the database schema
npm install               # restore JS dependencies (node_modules/)
npm run build             # compile assets into public/build/
```

> **Configure your database first** (see below). The `migrate` step fails if the
> database connection isn't set up.

## Manual Setup (step by step)

```bash
# 1. Install PHP dependencies
composer install

# 2. Create your environment file
copy .env.example .env      # Windows
cp .env.example .env        # macOS / Linux

# 3. Generate the application key
php artisan key:generate

# 4. Configure the database in .env (see below), then:
php artisan migrate
# or, to also load demo/seed data:
php artisan migrate --seed

# 5. Install and compile front-end assets
npm install
npm run build

# 6. Link the public storage folder (for uploaded images, etc.)
php artisan storage:link
```

## Database Configuration

Open `.env` and set your database connection. For a typical XAMPP / MySQL setup,
create an empty database in phpMyAdmin first, then set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_booking_ops
DB_USERNAME=root
DB_PASSWORD=
```

Prefer SQLite (no server needed)? Set `DB_CONNECTION=sqlite` and create the file:

```bash
type nul > database\database.sqlite   # Windows
touch database/database.sqlite          # macOS / Linux
```

## Running the App

Run the PHP server, queue worker, and Vite dev server together:

```bash
composer dev
```

Or start pieces individually:

- `php artisan serve` — web server at http://127.0.0.1:8000
- `npm run dev` — Vite dev server with hot reloading for assets
- `php artisan queue:listen` — process background jobs

## Useful Commands

| Command                              | What it does                          |
| ------------------------------------ | ------------------------------------- |
| `composer setup`                     | Full one-shot setup of a fresh clone  |
| `composer dev`                       | Run server + queue + Vite together    |
| `composer test`                      | Run the Pest test suite               |
| `php artisan migrate:fresh --seed`   | Wipe & rebuild DB with seed data      |
| `php artisan optimize:clear`         | Clear cached config/routes/views      |
| `./vendor/bin/pint`                  | Auto-format PHP code to project style |
| `npm run build`                      | Recompile front-end assets            |

## After Pulling New Changes

When you pull updates from GitHub, re-run these in case dependencies or the
schema changed:

```bash
composer install
npm install
php artisan migrate
```

## Troubleshooting

| Symptom                                          | Likely cause & fix                                         |
| ------------------------------------------------ | ---------------------------------------------------------- |
| "No application encryption key has been specified" | Run `php artisan key:generate`.                          |
| `SQLSTATE` / database connection error           | Check `.env` DB settings and that MySQL is running.        |
| Vite manifest not found                          | Run `npm install` then `npm run build`.                    |
| Styles / images not loading                      | Run `php artisan storage:link` and rebuild assets.         |
| Class not found after pulling changes            | Run `composer install` and `composer dump-autoload`.       |

---

Built with [Laravel](https://laravel.com). See the [Laravel documentation](https://laravel.com/docs) for framework details.
