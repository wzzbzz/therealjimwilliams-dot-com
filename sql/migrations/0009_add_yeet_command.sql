-- Migration 0009 — registers the `yeet` admin command, which pulls any item
-- into the admin's inventory by id, name, alias or title
-- (_classes/commander/commands/yeet.php). Gated on manage_items.
--
-- Idempotent: safe to re-run.

INSERT INTO commands (command, type, listed)
SELECT 'YEET', 'OUTPUT', 0
WHERE NOT EXISTS (SELECT 1 FROM commands WHERE command = 'YEET');
