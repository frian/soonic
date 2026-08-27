# Soonic

Symfony 8 application for local music playback (library, playlists, radios, audio file scanning).

## Requirements

- PHP `>= 8.4`
- Composer 2
- Node.js + npm
- MariaDB/MySQL (recommended)

### For scan from browser:

- Unix/Linux/macOS shell support (`nohup`)
- Windows PowerShell support (`Start-Process`)

## Installation

```bash
composer install
npm install
```

Configure local environment values in `.env.local` (not committed), especially:

- `DATABASE_URL` (dev database, e.g. `soonic`)
- `DEFAULT_URI` (e.g. `http://127.0.0.1:8000`)

Example:

```dotenv
APP_ENV=dev
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/soonic?serverVersion=11.8.3-MariaDB&charset=utf8mb4"
DEFAULT_URI="http://127.0.0.1:8000"
```

## Database

Create database:

```bash
php bin/console d:d:c #doctrine:database:create
php bin/console d:s:c #doctrine:schema:create
```

Initialize with migrations:

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --no-interaction
```

Or run a full reset with the project command:

```bash
php bin/console soonic:reset --force
```

Notes:

- in `dev`, `soonic:reset` refuses to run unless `DATABASE_URL` points to `soonic`
- in `test`, it requires `soonic_test`

## Run the Application

```bash
symfony server:start
```

## SCSS / Frontend

Build CSS:

```bash
npm run build:scss
```

Watch mode:

```bash
npm run build:scss:watch
```

Lint SCSS:

```bash
npm run lint:scss
```

## Keyboard Navigation

Keyboard shortcuts are handled in `public/js/keyboard.js`.

- `/` or `Ctrl/Cmd + K`: focus search
- `P`: play / pause the topbar player
- `N` / `B`: next / previous song
- `R`, `A`, `L`, `F`: open radios, albums, library, or focus the artist filter
- `ArrowUp` / `ArrowDown`: move the keyboard selection in the current list
- `ArrowRight`: activate the selected item
- `ArrowLeft` / `Esc`: go back or close transient UI
- `Backspace` / `Delete`: remove the selected playlist song
- `Enter` / `Space`: activate focused controls with `role="button"`

Keyboard selection uses `.keyboard-selected`, kept separate from `.active` and `.playing`.

## Business Commands

Scan library:

```bash
php bin/console soonic:scan
```

Add one radio:

```bash
php bin/console soonic:add:radio "Radio Name" "https://stream.example/live" "https://site.example"
```

Import radios from `.csv` or `.m3u`:

```bash
php bin/console soonic:add:radios path/to/radios.csv
php bin/console soonic:add:radios path/to/radios.m3u --format=m3u
php bin/console soonic:add:radios path/to/radios.csv --dry-run
```

## Tests

`bin/check` has four execution modes:

| Command | Checks run inside `bin/check` |
| --- | --- |
| `bin/check` | Static/lint checks, Doctrine checks, PHPUnit, Playwright |
| `bin/check --fast` | Static/lint checks and Doctrine checks; skips PHPUnit and Playwright |
| `bin/check --no-db` | Static/lint checks only; skips Doctrine, PHPUnit and Playwright |
| `bin/check --ci` | Same in-script scope as `--no-db`; GitHub Actions runs PHPUnit and Playwright separately |

Static/lint checks include Composer validation, PHP syntax, PHPStan, JavaScript syntax, SCSS lint, and Symfony Twig/YAML/container linting.

PHPUnit suites can also be run directly:

```bash
php bin/phpunit --testsuite no-music
php bin/phpunit --testsuite with-music
php bin/phpunit --testsuite scan
```

Important notes:

- `with-music` prepares its own test DB and injects a dedicated music dataset
- tests run with `APP_ENV=test` and target `soonic_test`
- controller suites rebuild the test database before seeding (drop/create/schema/fixtures)

## Quality / Lint Commands

```bash
composer validate --no-check-publish
vendor/bin/phpstan analyse --configuration=phpstan.neon.dist
php bin/console lint:twig templates
php bin/console lint:yaml config
php bin/console lint:container
php bin/console doctrine:schema:validate
php bin/console doctrine:migrations:status
```

## CI

The GitHub Actions workflow in `.github/workflows/ci.yml` runs on pushes and pull requests. It:

1. installs PHP and JavaScript dependencies;
2. runs `bin/check --ci` for the static/lint stage;
3. runs all three PHPUnit suites against a MariaDB `soonic_test` service;
4. installs the locked Playwright Chromium and Firefox browsers;
5. rebuilds and seeds the E2E test database;
6. runs the Playwright end-to-end suite.

`--ci` therefore does not mean "all CI checks inside `bin/check`"; it is the non-database/static stage used by the wider CI workflow.

## License

This project is licensed under the MIT License. See `LICENSE`.

## Useful Project Structure

- `src/Controller`: HTTP controllers
- `src/Command`: console commands (`soonic:*`)
- `src/Entity` / `src/Repository`: domain model and DB access
- `templates`: Twig views
- `assets/styles`: SCSS sources
- `public/js`: frontend scripts
- `tests`: PHPUnit suites
