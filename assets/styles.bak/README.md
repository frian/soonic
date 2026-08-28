# Sass Organization

This folder contains the Sass sources used to generate the CSS files in `public/css`.

## Structure

- `layout.scss`: structural/layout rules
- `screen.scss`: default visual theme
- `_reset.scss`, `_hamburger.scss`: shared partials
- `layout/*.scss`: extracted layout sections (e.g. topbar, artists navigation)
- `screen/*.scss`: extracted visual sections (e.g. topbar, artists navigation, songs/playlist)
- `themes/_base.scss`: shared theme rules
- `themes/<theme>/screen.scss`: theme-specific variables and overrides

## Conventions

- Prefer `@use` over legacy `@import`.
- Keep shared theme rules in `themes/_base.scss`.
- In each `themes/<theme>/screen.scss`, configure variables via:
  `@use "../base" with (...)`
- Keep visual overrides (e.g. background image/gradient) after `@use`.

Current theme sources:

- `themes/default-clear/screen.scss`
- `themes/default-dark/screen.scss`
- `themes/guitar-dark/screen.scss`

## Build commands (Dart Sass)

From the project root:

```bash
sass assets/styles/layout.scss public/css/layout.css
sass assets/styles/screen.scss public/css/screen.css
sass assets/styles/themes/default-clear/screen.scss public/css/themes/default-clear/screen.css
sass assets/styles/themes/default-dark/screen.scss public/css/themes/default-dark/screen.css
sass assets/styles/themes/guitar-dark/screen.scss public/css/themes/guitar-dark/screen.css
```

Equivalent npm script:

```bash
npm run build:scss
```

Optional watch mode:

```bash
sass --watch assets/styles:public/css
```

Equivalent npm script:

```bash
npm run build:scss:watch
```

## Add a new theme

1. Create `assets/styles/themes/<name>/screen.scss`.
2. Start from an existing theme file and adjust the `@use "../base" with (...)` values.
3. Add theme-specific overrides (background image, etc.) below the `@use`.
4. Compile to `public/css/themes/<name>/screen.css`.

## Lint SCSS

Install lint dependencies from project root:

```bash
npm install
```

Run lint:

```bash
npm run lint:scss
```

Lint + build check:

```bash
npm run check:scss
```

Auto-fix where possible:

```bash
npm run lint:scss:fix
```
