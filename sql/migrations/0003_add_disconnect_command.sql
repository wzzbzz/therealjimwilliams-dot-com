-- Migration 0003 — register the `disconnect` admin command.
--
-- The `disconnect <dir>` command (added in
-- _classes/commander/commands/disconnect.php) only dispatches if a row for it
-- exists in the `commands` table, which lives in MySQL rather than in the repo.
-- This migration registers that row so a fresh deploy of the code actually
-- exposes the command.
--
-- `disconnect` is gated on the `manage_locations` permission (held by the
-- `admin` role, see _lib/php/user/roles.php). It removes the connection leaving
-- the current room in a direction (and its reverse) so a builder can remove or
-- retarget a bad connection in-game. Registered lowercase, OUTPUT, listed=0
-- (unlisted) like the other admin build commands (make/destroy/connect/update).
--
-- Idempotent: safe to re-run. Registers disconnect only if absent.
INSERT INTO commands (command, type, listed)
SELECT 'disconnect', 'OUTPUT', 0
WHERE NOT EXISTS (
    SELECT 1 FROM commands WHERE command = 'disconnect'
);
