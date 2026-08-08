# ICON CTP deployment cleanup checklist

Use this before uploading to live server.

## Do not deploy

- `_archive_old_files/`
- `database/audit/` if present
- local ZIP/RAR backups
- temporary screenshots, clipboard images, and test files
- `.git/`, `.agents/`, `.codex/`

## Must deploy

- application PHP files
- `assets/css/icon-ui.css`
- `includes/pdf_report_helper.php`
- `secure_session.php`
- new pages under `Employees/`, `Stocks/`, `Supplier/`, `Accounting/`, `Jobs/`

## Database

Run:

```sql
SOURCE database/migrations/20260808_performance_indexes.sql;
```

If production already has any index, skip that duplicate line and continue.

## Quick checks after deploy

- Login with an admin user.
- Open Dashboard.
- Open Customer/Supplier/Stocks list pages.
- Open Accounting Trial Balance, Balance Sheet, Profit & Loss.
- Open Sales Report and Monthly Bill PDF.
- Open Jobs Completed Orders and test pagination/search.
