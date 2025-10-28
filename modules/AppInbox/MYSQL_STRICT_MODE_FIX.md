# MySQL Strict Mode / ONLY_FULL_GROUP_BY Fix

## Issue

If you encounter this error:

```
SQLSTATE[42000]: Syntax error or access violation: 1055 
'database_name.i.column_name' isn't in GROUP BY
```

This means your MySQL server has `ONLY_FULL_GROUP_BY` mode enabled (which is default in MySQL 5.7.5+).

## What Was Fixed

The models `Inbox.php` and `InboxComment.php` have been updated to properly handle MySQL strict mode by:

1. **Explicitly listing all columns** in SELECT instead of using `i.*`
2. **Including all non-aggregated columns** in the GROUP BY clause
3. **Using MAX() function** for tag_ids and user_ids which need to be retrieved but not grouped

## Changes Made

### Before (Would Fail)
```php
->select([
    'i.*',
    't.tag_ids',
    DB::raw('GROUP_CONCAT(DISTINCT t2.tag_name) AS tag_names'),
    'u.user_ids',
    DB::raw('GROUP_CONCAT(DISTINCT u2.fullname) AS user_names')
])
->groupBy('i.id')
```

### After (Works with Strict Mode)
```php
->select([
    'i.id',
    'i.user_id',
    'i.account_id',
    // ... all other columns explicitly listed ...
    DB::raw('MAX(t.tag_ids) as tag_ids'),
    DB::raw('GROUP_CONCAT(DISTINCT t2.tag_name) AS tag_names'),
    DB::raw('MAX(u.user_ids) as user_ids'),
    DB::raw('GROUP_CONCAT(DISTINCT u2.fullname) AS user_names')
])
->groupBy([
    'i.id',
    'i.user_id',
    'i.account_id',
    // ... all other columns ...
])
```

## Alternative Solutions

If you prefer to disable strict mode (not recommended for production), you can:

### Option 1: Disable in Laravel Config

Add to `config/database.php` under your MySQL connection:

```php
'mysql' => [
    // ... other settings ...
    'strict' => false,
    'modes' => [
        'STRICT_TRANS_TABLES',
        'NO_ZERO_IN_DATE',
        'NO_ZERO_DATE',
        'ERROR_FOR_DIVISION_BY_ZERO',
        'NO_ENGINE_SUBSTITUTION',
        // Remove ONLY_FULL_GROUP_BY from the list
    ],
],
```

### Option 2: Disable in MySQL Config

Edit your `my.cnf` or `my.ini` file:

```ini
[mysqld]
sql_mode=STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
```

Then restart MySQL.

### Option 3: Disable at Runtime (Temporary)

In your controller or service provider:

```php
DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
```

## Why This Happens

When using `GROUP BY`, MySQL's strict mode requires that:
- Every column in SELECT must either be:
  1. In the GROUP BY clause, OR
  2. Used with an aggregate function (COUNT, MAX, MIN, SUM, GROUP_CONCAT, etc.)

Our query groups by `i.id` but selects many columns from the `i` table, so we need to include all those columns in GROUP BY.

## Affected Methods

The following methods in the models were fixed:

### Inbox Model
- `getInboxList()`

### InboxComment Model  
- `getInboxCommentsList()`
- `getInboxCommentsDetail()`

## Testing

After the fix, the queries should work correctly. Test with:

```php
// In tinker or a test controller
use Modules\Inbox\Models\Inbox;
use Modules\Inbox\Models\InboxComment;

$inbox = Inbox::getInboxList(['to_type' => 'me', 'is_deleted' => 0]);
$comments = InboxComment::getInboxCommentsList(['to_type' => 'me']);
```

## References

- [MySQL Documentation: ONLY_FULL_GROUP_BY](https://dev.mysql.com/doc/refman/8.0/en/group-by-handling.html)
- [Laravel Database Configuration](https://laravel.com/docs/11.x/database#configuration)

---

**Status:** ✅ Fixed in latest version
**Date:** October 28, 2025
