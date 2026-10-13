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
- `0002_create_missing_door_items.sql` — creates the 4 door items (ids 4, 5,
  11, 76; `item_types.id` = 2) referenced by `location_connections.restrictions`
  but missing from `items`, and places each in both adjoining rooms. Pairs with
  the `location::restriction_door()` fail-closed hardening (playfix issue #3).
- `0003_add_disconnect_command.sql` — registers the `disconnect` command row so
  the `disconnect <dir>` admin command (in
  `_classes/commander/commands/disconnect.php`) is dispatchable (playfix issue #6).
- `0004_add_food_truck.sql` — adds the `truck` item type and registers the
  `buy` and `menu` commands (plus the missing `set` row). An admin places a truck with `make truck` and marks
  the restock spot with `set location attribute farm yes`.
- `0005_add_worm_races.sql` — adds the `worm_races` table and registers the
  `watch` command. An admin marks the racetrack with
  `set location attribute racetrack yes`; players there type `watch`.
- `0006_add_worm_stable.sql` — adds the `worms` table, the `weather` and
  `lineup` columns on `worm_races`, and registers the `hatch` admin command.
  An admin fills the stable with `hatch <name>`; races need at least two worms.
