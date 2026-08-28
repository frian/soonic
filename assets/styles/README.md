# SCSS Conventions

Ce dossier suit une separation stricte:

- `layout/`: structure uniquement (position, display, dimensions, spacing, overflow).
- `screen/`: apparence uniquement (typo, couleurs, bordures, ombres, interactions visuelles).
- `config/`: variables, tokens, breakpoints, mixins.

## Breakpoints

Toujours passer par le mixin `config.up(...)` (pas de `@media` en dur):

- `xs`: `500px`
- `sm`: `700px`
- `md`: `1024px`
- `lg`: `1200px`
- `xl`: `1600px`

Exemple:

```scss
@use '../config/layout' as config;

@include config.up(md) {
    .my-block {
        display: flex;
    }
}
```

## Ordre des proprietes

Appliquer cet ordre dans chaque bloc:

1. Positionnement
2. Display / modele de layout
3. Dimensions
4. Espacement
5. Overflow / clipping
6. Typo / contenu
7. Couleurs / apparence
8. Interaction / animation

Laisser une ligne vide entre groupes.

## Specificite

- Eviter les selecteurs trop profonds.
- Preferer des classes explicites (`.top-nav-link`, `.settings-value`) plutot que `table tr td ...`.
- Limiter les `id` aux points necessaires pour le JS.

## Workflow

- Lint: `npm run lint:scss`
- Auto-fix: `npm run lint:scss:fix`
- Build: `npm run build:scss`

Refactor conseiller:

1. Modifier d'abord `layout/` (structure).
2. Ajuster ensuite `screen/` (skin).
3. Lancer `lint:scss:fix`.
4. Verifier visuellement mobile + desktop.
