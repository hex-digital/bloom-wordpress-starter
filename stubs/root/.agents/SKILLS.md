# Bloom — Agent Reference

Hex Digital's opinionated WordPress starter layer for Roots Sage themes. This theme was scaffolded with Bloom (`hex-digital/bloom`, source repo `bloom-wordpress-starter`), which copies its `stubs/` into a Sage theme's `Bloom/`, `app/`, `resources/`, and root directories — including this `.agents/` directory itself, via `stubs/root/.agents/`.

This file is a short index. The detailed, topic-specific reference lives in `.agents/skills/<topic>/SKILL.md` — read the relevant one before working in that area rather than assuming this index covers everything.

> Grounded directly in the `bloom-wordpress-starter` repo (stubs, commands, service provider, git history) as of July 2026. If anything here disagrees with an older written doc elsewhere, this repo wins — treat these skill files as the source of truth and keep them updated as the repo changes.

## Skills

- [`skills/installer/SKILL.md`](skills/installer/SKILL.md) — how `bloom:install` scaffolds this theme, stub-to-destination mapping, Vite/composer.json patching, `bloom:setup`, reinstalling/diffing, requirements.
- [`skills/module-system/SKILL.md`](skills/module-system/SKILL.md) — Blocks (incl. the ACF Fields/Partials/Options split), Components, Livewire Components, Composers, DTOs, Helpers, Constants: how to generate, structure, and register each.
- [`skills/styling/SKILL.md`](skills/styling/SKILL.md) — Tailwind v4 via PostCSS, the `@theme` token system, class naming, BEM, nesting conventions.
- [`skills/javascript-conventions/SKILL.md`](skills/javascript-conventions/SKILL.md) — JS style expectations.

## Directory structure of a Bloom-based theme

```
themes/<theme>/
├── Bloom/                 # Bloom-specific code (from stubs/bloom)
│   ├── Blocks/            # ACF Blocks (Gutenberg) — see module-system skill
│   ├── Fields/            # Shared/reusable ACF field groups
│   ├── Options/            # ACF Options pages
│   ├── Partials/          # Reusable field-group partials, composed via addPartial()
│   ├── Components/        # View Components
│   ├── Composers/         # View Composers
│   ├── Livewire/          # Livewire Components
│   ├── config/            # components.php / composers.php / livewire.php / (optional) commands.php
│   ├── Constants/         # Constant values (post types, taxonomies, etc.)
│   ├── Data/               # Data Transfer Objects
│   └── Helpers/            # Reusable helper classes
├── app/                    # Sage app code (filters.php, setup.php, View/Composers/App.php)
├── resources/css/          # Tailwind v4 + PostCSS — see styling skill
└── .agents/                 # This directory (scaffolded via stubs/root/.agents)
```

## Development values

- Prefer readable code over terse code, even if longer.
- Descriptive variable/function names.
- Design with accessibility, performance and security in mind.
- **All logic (WP queries, ACF field fetching, data shaping) belongs in the Block/Composer/Component class — never in view/blade files.**
- Strip comments, dead code, `console.log`, and `var_dump` before opening a PR.
- CMS users should never need to hand-edit HTML or add classes for a block to work correctly.
- Avoid hardcoded copy — make content CMS-editable via ACF Options where reasonable.

## Options (Site Settings)

An ACF options page exposes CMS-editable global settings, typically including: header CTAs, social channels (hide the element entirely if a field is empty), footer info (company bio, address, copyright text, etc.). See `Bloom/Options/` in the module-system skill.

## Plugins

Keep plugin usage minimal — only trusted, well-maintained, version-compatible plugins. Prefer building functionality programmatically over adding a plugin, for performance, security, and control.

## Testing

No testing framework is wired into Bloom. Don't assume test infrastructure exists; this is a known gap, not an oversight.

## Key dependencies

- `log1x/poet` — config-based post type, taxonomy, block category and block registration.
- `log1x/navi` — dev-friendly alternative to the WP NavWalker.
- `log1x/sage-directives` — WordPress/ACF/helper blade directives.
- `log1x/acf-composer` — ACF fields and blocks; **do not use the ACF GUI**, define fields in code.
- `spatie/laravel-data` — powers Bloom's DTOs.
- `blade-ui-kit/blade-icons` — SVG icon sets.
- `livewire/livewire` — powers `Bloom/Livewire/` components.
- `laravel/pint` — PHP code style fixer. Run before every PR (`./vendor/bin/pint`, or scoped to a path).

## Requirements

- PHP >= 8.2 (`composer.json`); README recommends 8.3+.
- Roots Sage with Acorn (`roots/acorn` ^6.0).
- Node.js/npm for asset builds.
