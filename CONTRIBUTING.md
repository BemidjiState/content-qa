# Contributing

Validates and modifies content based on configurable rules/modules. Plain PHP: the package lives in `src/`, and `npm run build` assembles
it verbatim into `dist/content-qa/` — what the release workflow zips and the
BSUwp theme's build fetches into `artifacts/content-qa/`.

## Prerequisites

- [Node.js](https://nodejs.org) 24.18.0 — use [nvm](https://github.com/nvm-sh/nvm) (a `.nvmrc` is included)
- npm 11.6+

`npm ci` installs BSU lint tooling from the GitHub Packages registry, which
needs a GitHub personal access token (classic) with `read:packages` in
`~/.npmrc`:

```bash
echo '//npm.pkg.github.com/:_authToken=PASTE_WHOLE_TOKEN_HERE' >> ~/.npmrc
npm whoami --registry=https://npm.pkg.github.com
```

CI needs nothing extra — it authenticates with its own workflow token.

## Branches and releases

- Feature branch → PR into `release` — cuts an `X.Y.Z-rc` prerelease.
- `release` → `main` promotes the stable release and mirrors the zip to
  `bsuwp-release-assets` as `content-qa@X.Y.Z`.
- Versions come from Conventional Commit messages (`feat` → minor,
  `!`/`BREAKING CHANGE` → major, else patch), continuing from the existing
  tags. PR titles into `release` follow the same convention — avoid `feat:`
  unless a minor bump is intended.
- The BSUwp theme pins `content-qa@<version>`; a change here reaches the theme
  when it bumps that pin.

## Linting

`npm run lint:php` runs PHPCS with the BSUPhpLint standard; `npm run fix:php`
auto-fixes what it can. The standard found 334 pre-existing violations
when tooling arrived; until that debt is cleared, linting is not a CI gate
here — run it on the code you touch and leave things cleaner than you found
them.
