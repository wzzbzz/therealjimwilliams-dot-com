-- Migration 0001 — register the `elevate` admin command.
--
-- The `elevate <username>` command (added in _classes/commander/commands/elevate.php)
-- only dispatches if a row for it exists in the `commands` table, which lives in
-- MySQL rather than in the repo. This migration registers that row so a fresh
-- deploy of the code actually exposes the command.
--
-- `elevate` is gated on the `manage_users` permission, which is held by the
-- `admin` role (see _lib/php/user/roles.php). Roles are assigned per-user in the
-- users.attributes JSON blob; there is no roles table to seed here. To create the
-- first admin on a fresh database, grant the admin role to a user by id, e.g.:
--
--   UPDATE users
--   SET attributes = JSON_SET(COALESCE(NULLIF(attributes,''),'{}'), '$.roles', JSON_ARRAY('admin'))
--   WHERE id = <your admin user id>;
--
-- (left commented — it is environment-specific; after that admin exists, further
-- admins can be made in-game with `elevate <username>`.)
--
-- Idempotent: safe to re-run. Registers ELEVATE only if absent.

INSERT INTO commands (command, type, listed)
SELECT 'ELEVATE', 'OUTPUT', 0
WHERE NOT EXISTS (
    SELECT 1 FROM commands WHERE command = 'ELEVATE'
);
