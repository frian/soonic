# Sass Organization

This folder contains the Sass sources used to generate the CSS files in `public/css`.

## Structure

- `layout.scss`: structural/layout rules
- `screen.scss`: default visual theme
- `_reset.scss`, `_hamburger.scss`: shared partials
- `themes/_base.scss`: shared theme rules
- `themes/<theme>/screen.scss`: theme-specific variables and overrides

Current theme sources:

- `themes/default-clear/screen.scss`
- `themes/default-dark/screen.scss`
- `themes/guitar-dark/screen.scss`

## Build commands (Dart Sass)

From the project root:

```bash
sass public/sass/layout.scss public/css/layout.css
sass public/sass/screen.scss public/css/screen.css
sass public/sass/themes/default-clear/screen.scss public/css/themes/default-clear/screen.css
sass public/sass/themes/default-dark/screen.scss public/css/themes/default-dark/screen.css
sass public/sass/themes/guitar-dark/screen.scss public/css/themes/guitar-dark/screen.css
```

Optional watch mode:

```bash
sass --watch public/sass:public/css
```
