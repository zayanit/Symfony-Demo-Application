# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

This is the **Symfony Demo Application**, the official Symfony reference app demonstrating Symfony Best Practices. It's a simple blog engine with public post/comment browsing and an admin CRUD backend. PHP 7.1+, Symfony 4.3.

## Common commands

### PHP / backend
```bash
composer install                 # install PHP dependencies
php bin/console server:run       # run built-in dev server (or `symfony serve`)
./bin/phpunit                    # run the full test suite
./bin/phpunit tests/Controller/BlogControllerTest.php   # run a single test file
./bin/phpunit --filter testMethodName                   # run a single test method
php bin/console cache:clear
```
The `./bin/phpunit` wrapper (Symfony PHPUnit Bridge) auto-installs the correct PHPUnit version on first run — use it instead of a globally installed `phpunit`.

Database is SQLite (`data/database.sqlite`), configured via `DATABASE_URL` in `.env`. Tests use `DAMADoctrineTestBundle`, which wraps each test in a transaction that's rolled back afterward, so tests can safely persist entities without polluting the DB.

Load demo fixtures (dev/test only, requires `DoctrineFixturesBundle`):
```bash
php bin/console doctrine:fixtures:load
```

Custom console commands (`src/Command/`):
```bash
php bin/console app:add-user
php bin/console app:delete-user
php bin/console app:list-users
```

### Code style
```bash
php-cs-fixer fix                 # fix code style per .php_cs.dist (rules: @Symfony, @Symfony:risky)
```

### Frontend (assets)
```bash
yarn install
yarn dev            # build assets for dev (Webpack Encore)
yarn watch           # dev build with file watching
yarn build            # production build
```

## Architecture

### Layout
Standard Symfony 4 "Flex" skeleton — `src/` is PSR-4 autoloaded as `App\`, config lives in `config/`, templates in `templates/` (Twig), translations in `translations/`.

- `src/Kernel.php` — app kernel using `MicroKernelTrait`; bundles are enabled per-environment via `config/bundles.php`; routes/config are autoloaded via glob imports from `config/{routes,packages}`.
- `src/Controller/` — public controllers (`BlogController`, `UserController`, `SecurityController`). `src/Controller/Admin/BlogController.php` is the separate admin CRUD backend for posts, gated by `ROLE_ADMIN`.
- `src/Entity/` — Doctrine ORM entities: `Post`, `Comment`, `Tag`, `User`. Mapping is via annotations in the entity classes themselves (no separate YAML/XML mapping files).
- `src/Repository/` — Doctrine repositories with custom queries (e.g. `PostRepository` handles pagination via `src/Pagination/Paginator.php`).
- `src/Form/` — form types; note `src/Form/DataTransformer/TagArrayToStringTransformer.php` converts between the `Tag` entity collection and a comma-separated string field, and `src/Form/Type/TagsInputType.php`/`DateTimePickerType.php` are custom widget types.
- `src/Security/PostVoter.php` — authorization voter controlling who can edit/delete a given `Post` (used instead of plain role checks in controllers, via `@IsGranted`/`denyAccessUnlessGranted`).
- `src/EventSubscriber/` — cross-cutting concerns wired via the event dispatcher: `CommentNotificationSubscriber` emails the post author on new comments (listens for `CommentCreatedEvent`), `RedirectToPreferredLocaleSubscriber` handles locale negotiation/redirects, `CheckRequirementsSubscriber` and `ControllerSubscriber` handle other kernel-level concerns.
- `src/Utils/` — small standalone helpers: `Slugger`, `Markdown` (wraps Parsedown), `Validator`, `MomentFormatConverter`.
- `src/DataFixtures/AppFixtures.php` — single fixtures class loading demo users, posts, comments, tags.

### Routing
Routes are defined as annotations on controllers (`@Route`), plus a couple of top-level route files in `config/routes.yaml` and `config/routes/`. All public routes are locale-prefixed via the `{_locale}` requirement bound to the `app_locales` parameter (`config/services.yaml`).

### Security
Configured in `config/packages/security.yaml`: Doctrine-backed `User` provider, form login (`security_login`/`security_logout` routes handled by `SecurityController`), and a single `access_control` rule gating `/{locale}/admin` to `ROLE_ADMIN` (`ROLE_ADMIN` inherits `ROLE_USER` via `role_hierarchy`). Fine-grained per-entity authorization (e.g. "can this user edit this post") is done via `PostVoter`, not `access_control`.

### i18n
The app ships translations for ~20 locales (`translations/`). `app_locales` in `config/services.yaml` is the source of truth for which locales are enabled; it's injected into route requirements and used by `RedirectToPreferredLocaleSubscriber` for locale negotiation.

### Frontend build
Assets (`assets/`) are compiled with Webpack Encore (`webpack.config.js`) into `public/build/`; bundle is registered via `symfony/webpack-encore-bundle`.
