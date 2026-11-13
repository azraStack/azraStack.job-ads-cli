# AGENTS.md

Small PHP CLI that lists, filters, and summarises job ads from `data/job-ads.json`.

## Commands

- `composer check` — runs Pint (style check), PHPStan level 5, then Pest. Run before finishing.
- `composer format` — auto-fix style (Pint, default Laravel preset; no `pint.json`).
- Single test: `vendor/bin/pest tests/Unit/JobAdTest.php --filter="midpoint"`.
- Run the CLI: `php bin/jobs list`, `php bin/jobs filter --skill=laravel --min-salary=5000`, `php bin/jobs stats`, `php bin/jobs help`.
- Fresh checkout: `composer install` (PHP ^8.5 is required; CI uses 8.5).

## Layout and flow

- `bin/jobs` is the only entry point. It constructs `JobAdRepository` with the hardcoded path `data/job-ads.json` and exits with `Cli::run()`'s status.
- `src/Cli.php` parses arguments, dispatches commands, and owns exit codes: `0` success, `1` invalid job-ads file, `2` usage error.
- `JobAdRepository` handles all file/JSON validation; `JobAd`/`Seniority` are the domain model, `JobAdFilter` matching, `SalaryStats` aggregation.
- No framework is used. The PHPUnit config contains leftover Laravel env vars (`APP_ENV`, `CACHE_STORE`, etc.) — ignore them and do not add Laravel dependencies.

## Behavior contract

- `data/job-ads.json` must be a JSON list. Each ad requires `id`, `company`, `title`, `seniority`, `location`, `salary_min`, `salary_max`, `skills`.
- `seniority` must be `junior`, `mid`, or `senior`; skills are lowercased when loaded.
- CLI options must use `--name=value`; bare or space-separated options are rejected.
- `--skill` is case-insensitive. `--min-salary` matches ads whose `salary_max` is at least the value.
- Data problems must surface as `InvalidJobAdsFile`, not raw exceptions; CLI catches it and returns `1`.

## Tests

- Pest tests live in `tests/Unit` and `tests/Feature`; only Feature tests are bound to `Tests\TestCase` (see `tests/Pest.php`).
- The global `jobAd([...overrides])` helper in `tests/Pest.php` builds a valid ad; use it for unit tests.
- CI also runs `composer validate --strict`, `php -l` on `bin/jobs` and `src`, and CLI smoke tests for `list`, `filter`, and `stats`.