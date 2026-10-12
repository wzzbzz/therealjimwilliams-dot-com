# Database migrations

This app keeps most of its state in MySQL (`therealjimwilliams`) — including
things the code depends on, like the `commands` table that registers which
commands are dispatchable. Those rows are **not** in the repo, so a code change
that adds a command (or otherwise relies on new DB state) needs a matching
migration here so a fresh deploy works.

## Convention

- One file per change in `sql/migrations/`, named `NNNN_short_description.sql`
  with a zero-padded, increasing number (`0001_...`, `0002_...`).
- Make each migration **idempotent** (safe to re-run): guard inserts with
  `WHERE NOT EXISTS (...)`, use `CREATE TABLE IF NOT EXISTS`, `JSON_SET`, etc.
- Keep environment-specific steps (e.g. which user becomes the first admin)
  commented, with instructions — don't hard-code ids.

## Applying

Apply in order against the game database:

```bash
for f in sql/migrations/*.sql; do
  echo "applying $f"
  mysql -h127.0.0.1 -uroot therealjimwilliams < "$f"
done
```

(Locally the dev DB connects as `root` with no password. There is no migration
tracking table yet; migrations are written to be re-runnable, so re-applying all
of them is safe.)

## Migrations

- `0001_add_elevate_command.sql` — registers the `ELEVATE` command row so the
  `elevate <username>` admin command (in
  `_classes/commander/commands/elevate.php`) is dispatchable.
