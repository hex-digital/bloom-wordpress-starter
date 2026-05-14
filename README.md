# 🌿 Bloom - WIP and likely to chanage

<p>
  <strong>WordPress starter theme with a modern development workflow</strong>
  <br />
  Built by Hex Digital - https://www.hexdigital.com
</p>


Bloom is an opinionated starter layer for Roots Sage themes. It scaffolds project files into `Bloom/`, `app/`, `resources/`, and theme root using a predictable `stubs/` mapping.

## Requirements

- PHP 8.3+
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

- copies `stubs/bloom/*` into `Bloom/`
- copies `stubs/app/*` into `app/`
- copies `stubs/resources/*` into `resources/`
- copies `stubs/root/*` into the theme root
- patches `resources/css/app.css` to include Bloom CSS
- patches `vite.config.js`/`vite.config.ts` to include Bloom aliases and entries
- patches `composer.json` with `Bloom\\ => Bloom/` autoload when missing

Config files are copied only when missing. Use `wp acorn bloom:install --force` to overwrite existing files.

## Stub Directory Mapping

Use these package directories to control destination paths during install:

- `stubs/bloom` -> `Bloom/`
- `stubs/app` -> `app/`
- `stubs/resources` -> `resources/`
- `stubs/root` -> theme root

## Vite Updates Applied by Installer

Bloom updates your Vite config to include:

- `resolve.alias`:
  - `@bloom: '/Bloom'`
- `laravel.input` entries:
  - `'resources/css/editor.css'`
  - `'resources/css/admin.css'`

For non-Bedrock Sage themes, Bloom also ensures:

- `base: '/wp-content/themes/{theme-name}/public/build/'`

During `bloom:install`, Bloom auto-detects the theme directory name from the current path (for example, `wp-content/themes/{theme-name}`) and uses that value to build the Vite `base` path.

## Bedrock vs Non-Bedrock Base Path

- Bedrock example:
  - `/app/themes/{theme-name}/public/build/`
- Non-Bedrock example:
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

## Development

Run code style checks with Laravel Pint:

```bash
composer run lint
```

Auto-fix style issues:

```bash
composer run format
```

