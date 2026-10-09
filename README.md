# oxzoo-php-vue

Deployed with [ox](https://deploywithox.com): deploy a repo to your own server with one command, no Docker. [Docs](https://deploywithox.com/docs) · [Guide for this stack](https://deploywithox.com/docs/guides/php)

An [ox](https://deploywithox.com) deploy example: a framework-free PHP API (one router file, no Composer) with a Vue 3 SPA built by Vite, deployed to your own Ubuntu server. ox installs Ubuntu's `php-cli`, builds the SPA, runs PHP's built-in server under systemd, and Caddy serves `dist/` while sending only `/api` and `/health` to PHP. One variable, `GREETING_TAG`, flows through twice: PHP reads it per request, and Vite bakes it into the bundle.

## Stack

| Piece | Choice | Where |
|---|---|---|
| Backend | PHP (Ubuntu's `php-cli`), built-in server + router file | `router.php` |
| Frontend | Vue 3 + Vite 5 | `client/`, built to `dist/` |
| Package manager | npm (frontend only) | `package-lock.json` |

## ox.toml

```toml
# Framework-free PHP (Ubuntu's php-cli) + a Vue SPA.
packages = ["php-cli"]

[app]
start  = "php -S 127.0.0.1:$PORT router.php"
health = "/health"

[static]
dir = "dist"
spa = true
api = ["/api", "/health"]

[build]
commands = ["npm run build"]

[tools]
node = "24"
```

`packages` installs Ubuntu's `php-cli`; node comes from `[tools]` through mise.

## Environment flow

- **Run time (backend):** `router.php` reads `getenv('GREETING_TAG')` on every `GET /api/greeting` and answers `hello world oxzoo-php-vue_<GREETING_TAG>` as `text/plain`.
- **Build time (SPA):** `vite.config.js` sets `envPrefix: ["GREETING_", "VITE_"]`, so the same variable is `import.meta.env.GREETING_TAG`, baked in by `npm run build`. ox sets your variables before the build, and changing one with `ox vars set` redeploys, which rebuilds the SPA.

## Deploy with ox

```sh
curl -fsSL https://deploywithox.com/install.sh | sh
ox login
ox new https://github.com/saurav-codes/oxzoo-php-vue
printf 'GREETING_TAG=demo\n' | ox review oxzoo-php-vue --from-file - --wait
```

The plan, offline:

```console
$ ox check .
ox check . (manifest: ox.toml)

  app.start                  php -S 127.0.0.1:$PORT router.php                    declared
  app.health                 /health                                              declared
  static.dir                 dist                                                 declared
  static.spa                 true                                                 declared
  static.api                 /api, /health                                        declared
  build.install              npm ci                                               detected:package-lock.json
  build.commands[0]          npm run build                                        declared
  tools.node                 24                                                   declared
  packages                   php-cli                                              declared

  Provided by ox: PORT, HOST, OX_ENV, OX_PROJECT, OX_RELEASE, OX_DATA_DIR, PUBLIC_URL, PUBLIC_HOST
  Set on the dashboard before the first deploy: GREETING_TAG

Ready to deploy.
```

## Expected output

```
frontend: hello world oxzoo-php-vue_<GREETING_TAG>
backend: hello world oxzoo-php-vue_<GREETING_TAG>
```

`GET /health` returns `ok`.

## Local development

```sh
npm install
GREETING_TAG=localtest npm run build   # bakes the tag into dist/
GREETING_TAG=localtest php -S 127.0.0.1:9112 router.php
```

The backend answers only `/api/greeting` and `/health`; on the server, Caddy serves `dist/`.
