# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Arabic (RTL) Laravel 11 dashboard for managing patient operation records (`Record`), their financiers (`Financier`), users/permissions and an audit log. UI text, labels and validation-facing strings are in Arabic. Stack: Laravel 11, Fortify (auth), Yajra DataTables (server-side tables), Maatwebsite Excel, mPDF (`Mccarlosen\LaravelMpdf`), Livewire 3 (only `TableRecord`), MySQL.

## Commands

- `php artisan serve` — dev server (`APP_URL=http://127.0.0.1:8000`); `npm run dev` / `npm run build` for Vite assets (most CSS/JS is served from `public/` and loaded by the Blade layouts).
- `php artisan migrate` — DB is MySQL (`operations-projects-db`); the `DB_CONNECTION=sqlite` lines in `phpunit.xml` are commented out, so tests would hit the configured DB unless you enable them.
- `php artisan test` / `php artisan test --filter=Name` — PHPUnit 11; only the default example tests exist.
- `./vendor/bin/pint` — code style.
- `php artisan view:cache && php artisan view:clear` — quick check that Blade templates compile (used as the verification gate for view-only edits; there is no JS test setup).
- Default seeder creates a super-admin user.

## Architecture

- **Routing**: `routes/web.php` plus `routes/dashboard.php` (all behind `auth`). `/` redirects to `records.index`. Resource controllers for `records`, `financiers`, `users`, plus extra POST routes (`records/import|export|print|archivedRow`, `financiers/print|archive`) and `logs`.
- **Authorization is custom, not stock Laravel**: permissions are rows in `role_users` (`role_name` like `records.financial`). `app/Policies/ModelPolicy` resolves any ability via `__call` to `<plural-model>.<kebab-ability>` and checks the user's `roles`; concrete policies just extend it. `Gate::before` in `AppServiceProvider` grants everything to `users.super_admin`. The full list of abilities (and their Arabic names, used by the user-permissions form) lives in `data/abilities.php` — add new abilities there. Blade uses `@can('financial','App\\Models\Record')` to hide the financial columns.
- **Records UI** (`resources/views/dashboard/records/{index,archived,editModal}.blade.php`): server-side Yajra DataTables fed by `RecordController@index`/`archived` (`->addColumn(...)` for computed/label columns). The table definition, filters and edit-modal wiring are all **inline JS in the Blade files**. Important gotchas when adding/removing a record column:
  - The table is built by the shared `public/js/records-table.js` (`RecordsTable.init(config)`, config inline in each view: `columns`, `archived`, `canFinancial`, `financialColumns`, urls). It owns paging (100/page, opens on the last page), the year/search navbar control, column filters, totals and export/print. Filters are keyed by **column name** (`data-col` in `_th-filter.blade.php`), sent as a `filters` JSON + `year`/`q`/ranges, and applied server-side by a whitelist in `RecordController` (list options come from `records.filterOptions`; export/print re-run the same filtered query). Adding a column: add it to the `columns` array and the `<th>`/`<tfoot>` cells in the same position in both views, and to the filter whitelist if it should be filterable. Do not use numeric column indexes.
  - The edit modal fills a JS `record` object from the `show`/edit JSON response (two places in `index`), so a new field also needs adding to that object.
- **Single source of truth for a record's enum-like fields**: constants on `App\Models\Record` (`DISPLACEMENT_STATUSES`, `INCOME_STATUSES`, `FAMILY_BURDENS`, key => Arabic label, plus `*_label` accessors). DB stores the English key; forms, validation (`Rule::in`), tables, export, import and reports map to the Arabic label. `distributed` is a nullable boolean shown as نعم/لا.
- **A new `Record` field must be threaded through**: migration, `Record::$fillable`, `RecordController` (validation + DataTable columns), `editModal` form, `index` + `archived` views, `RecordsExport` (row array **and** headings, same order), `RecordsImport` (Arabic header slugs, e.g. `mlahthat_thanoy`), `database/factories/RecordFactory.php`, `reports/basic|mali.blade.php`, and `app/Livewire/TableRecord.php`.
- **Audit log**: `RecordObserver` (registered in `Record::boot()`) writes `Logs` rows using `Auth::user()` — so creating records outside an authenticated context (tinker/scripts) throws; log in first (`Auth::loginUsingId`).
- **Exports/reports**: `RecordsExport` receives the already-filtered DataTables collection; PDFs via mPDF in `RecordController@print` and `FinancierController@print`.

## Deployment / DB notes

- Shared-hosting layout: root `index.php` + `.htaccess` forward to `public/`. In production `AppServiceProvider` overrides `app.url` / `app.asset_url` (`/public/`) from the request host — requires `APP_ENV=production`.
- Some columns exist in the real DB only because they were added manually via phpMyAdmin (`notes_2`, `address`); the migration `2026_10_17_000000_add_address_to_records_table.php` adds them only if missing. Don't assume the early migrations alone reproduce the full `records` schema.
