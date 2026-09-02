# Project structure and file-placement guide

This project follows Laravel conventions. Keeping code in the expected folder
allows Laravel's autoloader, routing, migrations, and tests to find it without
manual configuration.

| When adding… | Put it in… | Naming convention |
| --- | --- | --- |
| A page controller | `app/Http/Controllers` | `SomethingController.php` |
| A database model | `app/Models` | singular PascalCase, e.g. `OrderItem.php` |
| Business logic shared by controllers | `app/Services` | `SomethingService.php` |
| A route | `routes/web.php` | group related routes and use named routes |
| A schema change | `database/migrations` | generated timestamped migration |
| Repeatable sample data | `database/seeders` | `SomethingSeeder.php` |
| A test | `tests/Feature` or `tests/Unit` | `SomethingTest.php` |
| A Blade page | `resources/views` | lowercase folders and `kebab-case` names |
| A CSS, image, or browser script | `public/assets/css`, `images`, or `js` | lowercase `kebab-case` |

## Active versus legacy files

Only files under `app`, `bootstrap`, `config`, `database/migrations`,
`database/seeders`, `public`, `resources`, `routes`, and `tests` are used by the
application.

The following folders preserve historical thesis artifacts for reference only:

- `docs/legacy` holds old route drafts.
- `scripts/legacy` holds one-time code-generation and repair scripts.
- `database/backups/legacy` holds old SQL exports.
- `public/assets/legacy` holds duplicate or previous stylesheet versions.

Never run a script in `scripts/legacy` against a working database. If a legacy
file becomes useful, copy its idea into a normal migration, seeder, service, or
test instead of executing it directly.
