# Soonic

Application Symfony 8 de lecture musicale locale (bibliothèque, playlists, radios, scan de fichiers audio).

## Prérequis

- PHP `>= 8.4`
- Composer 2
- Node.js + npm
- MariaDB/MySQL (recommandé)

## Installation

```bash
composer install
npm install
```

Configurer l'environnement local dans `.env.local` (non versionné), en particulier:

- `DATABASE_URL` (base `dev`, ex: `soonic`)
- `DEFAULT_URI` (ex: `http://127.0.0.1:8000`)

Exemple:

```dotenv
APP_ENV=dev
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/soonic?serverVersion=11.8.3-MariaDB&charset=utf8mb4"
DEFAULT_URI="http://127.0.0.1:8000"
```

## Base de données

Initialiser via migrations:

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --no-interaction
```

Ou reset complet via la commande projet:

```bash
php bin/console soonic:reset --force
```

Notes:

- en `dev`, `soonic:reset` refuse de tourner si `DATABASE_URL` ne pointe pas vers `soonic`
- en `test`, elle exige `soonic_test`

## Lancer l'application

```bash
symfony server:start
```

## SCSS / Front

Build CSS:

```bash
npm run build:scss
```

Mode watch:

```bash
npm run build:scss:watch
```

Lint SCSS:

```bash
npm run lint:scss
```

## Commandes métier

Scan bibliothèque:

```bash
php bin/console soonic:scan
```

Ajouter une radio:

```bash
php bin/console soonic:add:radio "Nom Radio" "https://stream.example/live" "https://site.example"
```

Importer des radios depuis `.csv` ou `.m3u`:

```bash
php bin/console soonic:add:radios path/to/radios.csv
php bin/console soonic:add:radios path/to/radios.m3u --format=m3u
php bin/console soonic:add:radios path/to/radios.csv --dry-run
```

## Tests

Lancer toutes les vérifications projet:

```bash
bin/check
```

Mode rapide (sans tests PHPUnit):

```bash
bin/check --fast
```

Suites PHPUnit:

```bash
php bin/phpunit --testsuite no-music
php bin/phpunit --testsuite with-music
php bin/phpunit --testsuite scan
```

Notes importantes:

- `with-music` prépare sa propre base de test et injecte un dataset musique de test
- les tests utilisent `APP_ENV=test` et la base `soonic_test`

## Qualité / Lint utiles

```bash
composer validate --no-check-publish
php bin/console lint:twig templates
php bin/console lint:yaml config
php bin/console lint:container
php bin/console doctrine:schema:validate
php bin/console doctrine:migrations:status
```

## Arborescence utile

- `src/Controller` : contrôleurs HTTP
- `src/Command` : commandes console (`soonic:*`)
- `src/Entity` / `src/Repository` : modèle et accès DB
- `templates` : vues Twig
- `assets/styles` : sources SCSS
- `public/js` : scripts front
- `tests` : suites PHPUnit
