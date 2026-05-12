# Soonic

Application Symfony 8 de lecture musicale locale (bibliothèque, playlists, radios, scan de fichiers audio).

## Prérequis

- PHP `>= 8.4`
- Composer 2
- Node.js + npm
- MariaDB/MySQL (recommandé)

### Pour lancer le scan depuis le navigateur :

- Shell Unix/Linux/macOS avec `nohup`
- PowerShell Windows avec `Start-Process`

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

Initialiser la base et le schéma:

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
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

## Navigation clavier

Les raccourcis clavier sont gérés dans `public/js/keyboard.js`.

- `/` ou `Ctrl/Cmd + K` : focus recherche
- `P` : play / pause du player topbar
- `N` / `B` : morceau suivant / précédent
- `R`, `A`, `L`, `F` : ouvrir radios, albums, bibliothèque, ou focus filtre artiste
- `ArrowUp` / `ArrowDown` : déplacer la sélection clavier dans la liste courante
- `ArrowRight` : activer l'élément sélectionné
- `ArrowLeft` / `Esc` : retour ou fermeture des éléments temporaires
- `Backspace` / `Delete` : retirer le morceau sélectionné de la playlist
- `Enter` / `Space` : activer les contrôles focusés avec `role="button"`

La sélection clavier utilise `.keyboard-selected`, séparée de `.active` et `.playing`.

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

Inclut les checks PHP, le lint SCSS, les suites PHPUnit et les tests e2e Playwright.

Mode rapide (sans tests PHPUnit):

```bash
bin/check --fast
```

Mode CI (sans checks DB, sans suites PHPUnit, sans e2e Playwright):

```bash
bin/check --ci
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
- les suites controller reconstruisent la base de test avant seed (drop/create/schema/fixtures)

## Qualité / Lint utiles

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

Un workflow GitHub Actions est disponible dans `.github/workflows/ci.yml`.
Il installe les dépendances puis exécute `bin/check --ci` sur push et pull request.

## Arborescence utile

- `src/Controller` : contrôleurs HTTP
- `src/Command` : commandes console (`soonic:*`)
- `src/Entity` / `src/Repository` : modèle et accès DB
- `templates` : vues Twig
- `assets/styles` : sources SCSS
- `public/js` : scripts front
- `tests` : suites PHPUnit
