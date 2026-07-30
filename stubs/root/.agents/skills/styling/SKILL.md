---
name: bloom-styling
description: Bloom's Tailwind v4 + PostCSS setup - the @theme token system, no class prefix, no CaptainCSS, BEM naming, and current CSS nesting conventions. Read this before writing or reviewing any CSS or Tailwind classes in a Bloom theme, and before assuming anything from older docs about u- prefixes or theme('*') lookups.
---

# Bloom styling conventions

**This supersedes older written references that may still describe Bloom's styling setup incorrectly.** Two things changed in this repo:

1. Bloom moved from a Sass/Vite-plugin-based Tailwind setup to **Tailwind v4 processed through PostCSS** (commit `feat: refactor to postcss`). Style files are plain `.css`, not `.scss`.
2. The Tailwind class **prefix (`u-`) was removed** (commit `#7 remove tailwind u- prefix`) — write plain Tailwind utility classes (`flex`, `flex-col`), not `u-flex u-flex-col`.

**CaptainCSS is not a dependency of this project** — there is no `captaincss.hexdigital.com` package pulled in here, and no `captainWrapper.js`/`tools/tailwind/` config. Don't add it or reference it as if it's wired in.

## Tailwind v4 via PostCSS

- No `tailwind.config.js` / `tailwind.config.ts`. Tailwind v4 is configured **in CSS**, via an `@theme` block — see `resources/css/base/bloom-tokens.css`. All design tokens (colors, background/text/border/field semantic aliases, font families, font sizes, font weights, line heights, letter spacing, spacing scale, breakpoints, transition durations/easings, z-index) are defined there as CSS custom properties inside `@theme { ... }`.
- **`theme('colors.x')`-style JS/SCSS lookups no longer exist** (that was Tailwind v3). Reference tokens directly as CSS custom properties: `var(--color-success)`, `var(--text-36)`, `var(--spacing-24)`, etc. — both inside plain CSS and via Tailwind's arbitrary-value syntax (`text-[var(--color-success)]` if ever needed, though prefer the semantic utility if Tailwind generates one from the token automatically).
- No class prefix. Write `flex flex-col gap-4`, not `u-flex u-flex-col u-gap-4`.
- `@plugin "@tailwindcss/typography";` is enabled (used for `.prose`).
- `@source` directives (not a JS `content: []` array) tell Tailwind where to scan for class usage — see `app.css`:
  ```css
  @source "../../app/**/*.php";
  @source "../**/*.blade.php";
  @source "../**/*.js";
  @source "../../Bloom/**/*.css";
  ```
  If you add a new file type or location that contains Tailwind classes, add a matching `@source`.
- Entry points:
  - `resources/css/app.css` — `@import "tailwindcss" theme(static);` then Bloom base styles, forms, page styles, the typography plugin, `@source`s, and page-specific overrides (`.prose`, `.nav-links`, etc.).
  - `resources/css/editor.css` / `editor-base.css` — `@import "tailwindcss";` for the block-editor iframe (Gutenberg), plus editor-specific resets in `editor.css`.
  - `resources/css/admin.css` — WP login/admin screen styling, unrelated to Tailwind/the frontend build.
- `resources/css/base/_mixins.css` uses `@define-mixin` (a PostCSS mixins plugin, not Sass) — this is the one place custom mixin syntax survives; don't assume Sass `@mixin`/`@include` semantics.

## Class naming convention (`o-` / `c-`)

CSS in `resources/css/base/_layout.css` and `_page.css` still uses an object/component naming split — `o-` for layout objects (`.o-wrapper`, `.o-cluster`), `c-` for components (`.c-section`, `.c-outerblock`, `.c-skip-link`). This is a **hand-written in-house convention**, not an external CaptainCSS package — treat it as local naming style to follow for new layout/structural classes, not a library to import or reference by URL.

## BEM + nesting

- BEM methodology for component class names (https://getbem.com/): `.card`, `.card__title`, `.card--featured`.
- Keep specificity low — target classes, avoid tag selectors where practical.
- Native CSS nesting (via PostCSS) is used liberally in this codebase — for pseudo-classes, state/modifier selectors, and descendants scoped within a single block/component. This is broader than "pseudo-classes only":

```css
/* pseudo-class */
.wp-core-ui .button-primary {
  &:hover { background: #000; }
}

/* modifier, combined with the base class via &.classname (not &--shorthand) */
.c-section {
  &.c-theme--light { background-color: var(--background-primary); }
  &.c-theme--mid { background-color: var(--background-secondary); }
}

/* descendants scoped to a component */
#site-content {
  & ul, & ol { margin-bottom: var(--spacing-30); }
}
```

- Still avoid using `&` to *generate* BEM element/modifier class names Sass-style (e.g. don't write `&__title` inside `.card` to produce `.card__title`) — write BEM element/modifier classes out flat (`.card__title { ... }`) even when nesting is otherwise used for state/descendants.
- Use CSS custom properties for themeable block/element options:

```css
:root {
  --ctaHeadingColor: black;
}
.c-cta--blue { --ctaHeadingColor: white; }
.c-cta--red  { --ctaHeadingColor: cream; }
.c-cta__heading { color: var(--ctaHeadingColor); }
```
