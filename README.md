# Bloom for Sage

Bloom is an opinionated starter layer for Roots Sage themes. It scaffolds a `Bloom/` directory, starter view files, CSS tokens/base styles, helper classes, and config used by the Bloom workflow.

## Requirements

- PHP 8.2+
- Roots Sage with Acorn
- Node.js/npm for asset builds

## Private Package Access (Required)

`hex-digital/bloom` is currently a private GitHub package. Before running `composer require`, configure your theme project to access private GitHub repositories.

1. Add the package repository to your theme `composer.json`:

```json
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:hex-digital/bloom-wordpress-starter.git"
    }
  ]
```

Check you're authenticated with Github, by running the following code in your terminal:

```
  ssh -T git@github.com
```

If successful you should see a message like:
> Hi! You've successfully authenticated, but GitHub does not provide shell access.


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
- copies package root files into the theme root (`.env`, `screenshot.png`)
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

- `base: process.env.BASE_PATH || '/'`

Set `BASE_PATH` in your theme `.env` file, for example:

```env
BASE_PATH=/wp-content/themes/your-theme/public/build/
```

## Bedrock vs Non-Bedrock Base Path

- Bedrock example:
  - `/app/themes/{theme-name}/public/build/`
- Non-Bedrock example:
  - `/wp-content/themes/{theme-name}/public/build/`

If your project structure differs, update `BASE_PATH` in `.env` rather than hardcoding the Vite `base`.

## Reinstalling / Updating Stubs

- Show what changed in stubs since your last install:

```bash
wp acorn bloom:install --diff
```

- Force overwrite scaffolded files:

```bash
wp acorn bloom:install --force
```

