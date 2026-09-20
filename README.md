# oxzoo-php-vue

This repo is the official ox example for a plain PHP 8 API plus a Vue 3 SPA built with Vite on one Ubuntu VPS: ox installs `php-cli` and Node 22, runs `npm install` and `npm run build`, starts PHP's built-in server (`php -S 127.0.0.1:9112 router.php`) under systemd, and nginx serves the built `dist/` folder while proxying only `/api` and `/health` to it. The backend is framework-free PHP with a single router file, no Composer and no dependencies. One env var, `GREETING_TAG`, flows through the stack twice, once at runtime (PHP reads it per request) and once at build time (Vite bakes it into the SPA bundle), so the deployed page demonstrates both env paths ox supports.

## Stack

| Piece | Choice | Where |
|---|---|---|
| Backend | PHP 8, built-in server + router file | `router.php` |
| Frontend | Vue 3 + Vite 5 | `client/`, built to `dist/` |
| Package manager | npm (frontend devDependencies only) | `package-lock.json` committed |
| Runtime packages | `php-cli`, `nodejs` via NodeSource | `required_packages`, `[[apt_sources]]` in `ox.toml` |
| Edge | nginx (managed by ox) | serves `dist/`, proxies `/api` and `/health` |
| Port | 9112 | `ox.toml` |

## Environment flow

One variable, two paths:

- **Runtime (backend)**: `router.php` reads `getenv('GREETING_TAG')` on every `GET /api/greeting` and answers `hello world oxzoo-php-vue_{GREETING_TAG}` as `text/plain`. PHP runs as its built-in server with a router file, no framework; ox loads the value into the process from the project's env file (`/srv/ox/oxzoo-php-vue/env`).
- **Build time (SPA)**: `vite.config.js` sets `envPrefix: ["GREETING_", "VITE_"]`, so the same variable is exposed to the app as `import.meta.env.GREETING_TAG` and baked into the bundle when `npm run build` runs. Changing it later requires a rebuild (a redeploy does that).

## Deploy with ox

1. Paste the clone URL `git@github.com:saurav-codes/oxzoo-php-vue.git` into the ox dashboard.
2. In the Environment editor, add `GREETING_TAG` (any short tag, for example `v1`) BEFORE the first deploy, so both the API process and the build see it.
3. Press Deploy. ox installs the NodeSource repo plus `nodejs` and `php-cli`, runs `npm install` and `npm run build`, starts `php -S 127.0.0.1:9112 router.php`, and polls `http://127.0.0.1:9112/health` until ready.

nginx serves `dist/` from the current release with an `index.html` fallback (SPA mode) and proxies only `/api` and `/health` to the PHP process.

## Expected output

With `GREETING_TAG=<GREETING_TAG>` set in ox, the page shows the project heading plus:

```
frontend: hello world oxzoo-php-vue_<GREETING_TAG>
backend: hello world oxzoo-php-vue_<GREETING_TAG>
```

`GET /health` returns `ok`.

## Local check

```bash
GREETING_TAG=localtest npm install
GREETING_TAG=localtest npm run build   # bakes the tag into dist/
GREETING_TAG=localtest php -S 127.0.0.1:9112 router.php
```

The backend answers only `/api/greeting` and `/health` (everything else is a 404); serving `dist/` is nginx's job on the VPS.
