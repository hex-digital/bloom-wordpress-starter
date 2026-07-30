---
name: bloom-installer
description: How the hex-digital/bloom Composer package scaffolds itself into a Roots Sage theme (bloom:install), plus the bloom:setup demo-content/plugins command. Read this before touching stub mapping, Vite config patching, or reinstalling/updating Bloom scaffolding.
---

# Bloom installer

Bloom is not a theme in itself — it's a layer of conventions and generated code scaffolded into a Roots Sage theme by the `hex-digital/bloom` Composer package (source: `bloom-wordpress-starter`), via a stub-copy installer.

## `wp acorn bloom:install`

```
wp acorn bloom:install               # scaffold Bloom into the current theme
wp acorn bloom:install --force       # overwrite existing scaffolded files
wp acorn bloom:install --diff        # show what changed in stubs since the last install
```

What it does:

- Copies stub directories into the theme:
  - `stubs/bloom` → `Bloom/`
  - `stubs/app` → `app/`
  - `stubs/resources` → `resources/`
  - `stubs/root` → theme root (this is how `.agents/` itself gets here)
- Patches `resources/css/app.css` to include Bloom CSS.
- Patches `vite.config.js`/`vite.config.ts` with a `@bloom` alias and `editor.css`/`admin.css` build entries, plus a Bedrock-aware `base` path:
  - Bedrock: `/app/themes/{theme-name}/public/build/`
  - Non-Bedrock: `/wp-content/themes/{theme-name}/public/build/`
  - The theme directory name is auto-detected from the current path.
- Patches `composer.json` with the `Bloom\` → `Bloom/` autoload entry when missing.
- Config files (`Bloom/config/*.php`) are only copied if missing — they won't be clobbered on a plain re-run. Use `--force` to overwrite everything, or `--diff` first to see what would change.

Re-running the installer (e.g. after upgrading the `hex-digital/bloom` package) can update this theme's previously-scaffolded files — run `--diff` before assuming files under `Bloom/`, `app/`, or `resources/` are purely hand-written project code.

If your project structure differs from the Bedrock/non-Bedrock assumption above, adjust the Vite `base` manually.

## `wp acorn bloom:setup`

A separate, interactive setup command — `Setup Bloom - config, plugins, demo content`. Prompts (multiselect) to run any combination of:

- **General Setup** — discourage search engines.
- **Install Starter Plugins** — required + premium.
- **Create Common Pages** — Frontpage, About, Contact, Legal.
- **Generate Demo Content** — posts for a selected post type.
- **Create Demo Nav** — primary navigation and menu items.
- **Create Demo Footer** — footer navigation + legal links.

This is a one-time project bootstrap helper, not something re-run routinely like `bloom:install`.

## Requirements

- PHP >= 8.2 (per `composer.json`'s `require.php`; the package README recommends 8.3+ — treat 8.2 as the hard floor).
- Roots Sage with Acorn (`roots/acorn` ^6.0).
- Node.js/npm for asset builds.

## Install in an existing Sage theme (from the package README)

1. Add the package repository to the theme's `composer.json`:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "git@github.com:hex-digital/bloom-wordpress-starter.git"
  }
]
```

2. `composer require hex-digital/bloom`
3. `wp acorn bloom:install`
4. `npm install && npm run build`
