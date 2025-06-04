# Database Migrations

This system allows for tracking database schema changes through versioned SQL migration files.

## How It Works

1. SQL files in the `database/migrations` directory are automatically detected
2. Each migration is executed in alphabetical order exactly once
3. The system keeps track of which migrations have been run in the `tbl_migrations` table
4. Each migration batch is tracked for better rollback capability

## Creating New Migrations

1. Create a new SQL file in the `database/migrations` directory
2. Name it descriptively with a timestamp prefix, e.g., `20230615_create_users_table.sql`
3. Add your SQL schema changes to the file
4. Run the migration using one of the methods below

## Running Migrations

### Web Interface

1. Navigate to `http://your-site/migrate.php` in your browser
2. Click the "Run Migrations" button

### Command Line

You can run migrations from the command line:

```
# Show help
php migrate.php help

# Run pending migrations
php migrate.php run

# Show migration status
php migrate.php status
```

Or use the helper scripts:

- Windows: Run `run-migrations.bat`
- Linux/Mac: Run `./run-migrations.sh`

## Best Practices

1. Never modify existing migrations once they've been applied to production
2. Always create new migrations for schema changes
3. Make migrations idempotent (can be run multiple times without error)
4. Use `IF NOT EXISTS` and `IF EXISTS` clauses when appropriate
5. Test migrations in development before applying to production
