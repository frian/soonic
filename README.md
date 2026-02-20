# Soonic

Symfony 8 application for local music playback (library, playlists, radios, audio file scanning).

## Requirements

- PHP `>= 8.4`
- Composer 2
- Node.js + npm
- MariaDB/MySQL (recommended)

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

Run all project checks:

```bash
bin/check
```

Fast mode (without PHPUnit suites):

```bash
bin/check --fast
```

PHPUnit suites:

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
php bin/console lint:twig templates
php bin/console lint:yaml config
php bin/console lint:container
php bin/console doctrine:schema:validate
php bin/console doctrine:migrations:status
```

## Useful Project Structure

- `src/Controller`: HTTP controllers
- `src/Command`: console commands (`soonic:*`)
- `src/Entity` / `src/Repository`: domain model and DB access
- `templates`: Twig views
- `assets/styles`: SCSS sources
- `public/js`: frontend scripts
- `tests`: PHPUnit suites
