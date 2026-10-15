-- Migration 0007 — registers the `stable` command, which lists the worms at the
-- racetrack with their ratings, records, stats and form
-- (_classes/commander/commands/stable.php).
--
-- Idempotent: safe to re-run.

INSERT INTO commands (command, type, listed)
SELECT 'STABLE', 'OUTPUT', 1
WHERE NOT EXISTS (SELECT 1 FROM commands WHERE command = 'STABLE');
