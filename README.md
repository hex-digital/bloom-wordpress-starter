# Bloom for Sage

Bloom is an opinionated starter layer for Roots Sage themes. It scaffolds a `Bloom/` directory, starter view files, CSS tokens/base styles, helper classes, and config used by the Bloom workflow.

## Requirements

- PHP 8.2+
- Roots Sage with Acorn
- Node.js/npm for asset builds

## Install in an Existing Sage Theme

1. Require the package in your Sage theme:

```bash
composer require hex-digital/bloom
```

2. Run the installer command from your theme root:

```bash
wp acorn bloom:install
```

3. Install/build frontend assets:

```bash
npm install
npm run build
```

## What the Installer Changes

The `bloom:install` command:

- creates `Bloom/` directories (`Blocks`, `Components`, `Composers`, `Livewire`, etc.)
- copies starter files into `resources/views`, `resources/css`, `resources/images`, and `resources/fonts`
- copies Bloom config into `Bloom/config`
- patches `resources/css/app.css` to include Bloom CSS
- patches `vite.config.js`/`vite.config.ts` to include Bloom aliases and entries

## Vite Updates Applied by Installer

Bloom updates your Vite config to include:

- `resolve.alias`:
  - `@bloom: '/Bloom'`
- `laravel.input` entries:
  - `'resources/css/editor.css'`
  - `'resources/css/admin.css'`

For non-Bedrock Sage themes, Bloom also ensures:

- `base: '/wp-content/themes/{theme-name}/public/build/'`

`{theme-name}` is the current theme directory name.

## Bedrock vs Non-Bedrock Base Path

- Bedrock default Sage path is typically:
  - `/app/themes/{theme-name}/public/build/`
- Non-Bedrock WordPress path should be:
  - `/wp-content/themes/{theme-name}/public/build/`

If your project structure differs, adjust `base` manually in Vite.

## Reinstalling / Updating Stubs

- Show what changed in stubs since your last install:

```bash
wp acorn bloom:install --diff
```

- Force overwrite scaffolded files:

```bash
wp acorn bloom:install --force
```

