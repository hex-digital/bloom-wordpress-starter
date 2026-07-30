---
name: bloom-module-system
description: Bloom's Blocks (incl. ACF Fields/Partials/Options split), Components, Livewire Components, Composers, DTOs, Helpers, and Constants — how to generate, structure, and register each via bloom:module-new and bloom:module-add. Read this before creating or editing any of these.
---

# Bloom module system

All of Bloom's structured code lives under `Bloom/` in the theme root, generated via console commands and registered through `Bloom/config/`.

## Generating modules — `wp acorn bloom:module-new`

```
wp acorn bloom:module-new "Buttons" --block       # -B
wp acorn bloom:module-new "Buttons" --component   # -C
wp acorn bloom:module-new "Footer" --composer     # -K
wp acorn bloom:module-new "Buttons" --all         # -A: block + component + composer
wp acorn bloom:module-new                          # interactive: prompts for name, then a multiselect of types
```

- Module names get converted to `UpperCamelCase` for the directory/class, `kebab-case` for file names, `snake_case` and `camelCase` variants substituted into the template as needed.
- **There is no `--livewire` flag on this command.** Livewire Components are not scaffolded by `bloom:module-new` — they come from importing a Livewire module via `bloom:module-add` (below), or by hand-creating a class under `Bloom/Livewire/` following the existing Components pattern.
- Templates are pulled from `vendor/hex-digital/bloom-modules/templates` — the command errors if `hex-digital/bloom-modules` isn't installed.
- A block/component/composer "module" can contain multiple classes/views, but combining several into one module directory has to be wired up by hand — the generator doesn't support it.

## Blocks (`Bloom/Blocks/`, via `log1x/acf-composer`)

`bloom:module-new Buttons --block` creates `Bloom/Blocks/Buttons/`:

- `Buttons.php` — the ACF Block class.
  - `fields()` — define the block's ACF fields in code (see the ACF Builder cheatsheet: https://github.com/Log1x/acf-builder-cheatsheet). Every field needs clear instructions for the CMS user. **Do not use the ACF GUI.**
  - `with()` — passes data to the view. **Validate/sanitise data here** before it reaches the view — bad data can throw critical errors on the live site.
- `buttons.blade.php` — the view. Often wraps a matching Component so the same logic/markup can be reused standalone or nested inside a bigger block.
- `buttons.css` — block-specific styles (plain CSS, processed by PostCSS/Tailwind — not `.scss`).
- `buttons.js` — block-specific JS.
- Delete any of the above files the block doesn't need.

### ACF structure: Fields / Partials / Options

`log1x/acf-composer` classes are auto-discovered from theme directories, each in their own namespace (see `BloomServiceProvider::initBloomAcf()`):

| Directory | Namespace | Purpose |
| --- | --- | --- |
| `Bloom/Blocks` | `Bloom\Blocks\` | Gutenberg blocks |
| `Bloom/Fields` | `Bloom\Fields\` | Shared/reusable ACF field groups (not tied to one block) |
| `Bloom/Partials` | `Bloom\Partials\` | Reusable field-group partials, composed into Fields/Blocks via `addPartial()` |
| `Bloom/Options` | `Bloom\Options\` | ACF Options pages (site settings) |

There is no `Bloom/Widgets` directory — Bloom doesn't use ACF-registered widgets, don't scaffold or reference one.

### Headings and eyebrows

Don't create a `Heading` or `Eyebrow` component, and don't use Gutenberg's native Heading block. Instead, expose a heading-level field (e.g. a select of `h1`–`h6`) directly on the block's ACF fields, and render the corresponding semantic tag in the block's view. This keeps document heading hierarchy under editorial/developer control per-block rather than letting CMS users pick arbitrary levels via the native block, and avoids a shared Heading/Eyebrow component masking what's actually a per-block semantic decision. Styling for these (`.text-eyebrow`, heading typography scale) lives in `resources/css/base/_typography.css` — see the styling skill.

### Inner/outer block pattern

Most content lives inside the "outer" `Section` block (from Bloom Modules). E.g. a CTA block that should respect the standard container goes *inside* `Section`. Modules that need to break out of the container get their own container markup and sit outside `Section`.

### `<InnerBlocks />`

Used in modules like `Section`/`Hero` to allow nesting blocks inside outer blocks. Reference: https://www.billerickson.net/innerblocks-with-acf-blocks/

## Components (`Bloom/Components/`)

`bloom:module-new Buttons --component` creates `Bloom/Components/Buttons/`:

- `Buttons.php` — only needed if the component fetches/manipulates data; otherwise delete it and treat the component as anonymous (see Laravel Blade component docs: https://laravel.com/docs/11.x/blade#components).
- `buttons.blade.php` — the component view.
- `buttons.css`, `buttons.js` — component-specific styles/JS.

Components must be **manually registered** in `Bloom/config/components.php` — see below.

## Livewire Components (`Bloom/Livewire/`)

Same generated shape as Components conceptually, but lives in `Bloom/Livewire/` and gives dynamic/interactive front-end behaviour in PHP without Vue/React (https://livewire.laravel.com/). Used for things like filterable article listings, avoiding hand-rolled AJAX. Not generated by `bloom:module-new` (see above) — comes via `bloom:module-add` or manual creation. Must be manually registered unless auto-merged by `bloom:module-add`.

## Composers (`Bloom/Composers/`)

`bloom:module-new Footer --composer`. A View Composer gathers data (ACF options, WP menus, etc.) once and shares it across multiple view files that need it — e.g. a `Footer.php` composer feeding several footer components — so views don't duplicate query/fetch logic. Must be manually registered.

## Data Transfer Objects (`Bloom/Data/`, via `spatie/laravel-data`)

Used to combine multiple data sources into a single object passed to a view — e.g. an article-listing DTO combining title, author, taxonomies, excerpt (field or helper-derived), extra ACF fields, and post type into one object per item. The stub `Bloom/Data/` ships empty with a commented-out example class only — there's no generator for these, write them by hand.

## Helpers (`Bloom/Helpers/`)

Reusable static-ish methods grouped by concern:

- `AcfHelper` — sanity-checks ACF fields before they reach views; generates ACF oEmbed markup.
- `PostHelper` — general post helpers.
- `GutenbergHelper` — working with block/Gutenberg content.
- `QueryHelper` — common `WP_Query` patterns.
- `TermHelper` — working with/fetching WP terms.

## Constants (`Bloom/Constants/`)

Use constants instead of raw strings for post types, taxonomies, etc. `Bloom/Constants/AbstractConstant.php` provides `getByName()`/`getByValue()`/`getConstants()` via reflection for any constant class that extends it.

```php
$query = new \WP_Query([
    'post_type' => PostType::TEAM_MEMBER,
    'tax_query' => [[
        'taxonomy' => Taxonomy::ROLE,
        'terms' => $role,
    ]],
    'posts_per_page' => -1,
]);
```

## Manually registering modules

`bloom:module-new` does **not** auto-register Components, Livewire Components, or Composers — register them yourself in `Bloom/config/`. (`Bloom/config/commands.php` is also supported for registering extra project-level console commands, if that file exists — it's optional and not scaffolded by default.)

**Components** — `Bloom/config/components.php`:

```php
// Class-based
'buttons' => Bloom\Components\Buttons\Buttons::class,
// -> <x-bloom-buttons />

'article-card' => Bloom\Components\ArticleCard\ArticleCard::class,
// -> <x-bloom-article-card />

// Anonymous component
'component-alias' => 'Components.[ModuleDir].[ModuleName]',

// Dot-grouped
'header' => 'Components.Header.header',
'header.logo' => 'Components.Header.header-logo',
'header.nav' => 'Components.Header.header-nav',
// -> <x-bloom-header />, <x-bloom-header.logo />, <x-bloom-header.nav />
```

**Livewire Components** — `Bloom/config/livewire.php`:

```php
'livewire-component' => Bloom\Livewire\[ModuleDir]\[ModuleName]::class,
```

**Composers** — `Bloom/config/composers.php`:

```php
'sections.footer' => \Bloom\Composers\Footer\Footer::class,
'Components.Header.header*' => \Bloom\Composers\Header\Header::class,
```

## Bloom Modules (companion package) — `wp acorn bloom:module-add`

`hex-digital/bloom-modules` is a separate Composer package providing pre-built, on-brand-able Blocks/Fields/Partials/Components/Composers/Livewire modules (e.g. a keyboard-navigable `Header` with dropdown, a `Section` outer block, buttons with hover states). Modules are **copied** into the project (not symlinked), so they can be customised/branded freely without touching the original package.

```
wp acorn bloom:module-add            # interactive multiselect of available modules
wp acorn bloom:module-add <module>   # import one module by name
```

Unlike `bloom:module-new`, importing a module this way **auto-merges its config** into `Bloom/config/components.php`, `livewire.php`, and `composers.php` where the module ships a matching `config/` directory — you don't need to hand-register imported modules the way you do for freshly-generated ones.
