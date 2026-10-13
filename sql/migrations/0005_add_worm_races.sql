-- Migration 0005 — worm races at the racetrack: the `worm_races` table and the
-- `watch` command.
--
-- The race logic lives in _classes/worm_races.php, the command in
-- _classes/commander/commands/watch.php and the playback in
-- _classes/commander/commands/js/worm_race.js. The racetrack itself is marked
-- in-game by an admin:
--
--   set location attribute racetrack yes    (in the room with the track)
--
-- A race starts every 2 minutes. Each row is one race's saved number sequence
-- (one array of lane numbers per tick), made the first time someone watches,
-- so every player replays the same race.
--
-- Idempotent: safe to re-run.

CREATE TABLE IF NOT EXISTS worm_races (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    start_time INT UNSIGNED NOT NULL,
    ticks MEDIUMTEXT NOT NULL,
    winner TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY start_time (start_time)
);

INSERT INTO commands (command, type, listed)
SELECT 'WATCH', 'OUTPUT', 1
WHERE NOT EXISTS (SELECT 1 FROM commands WHERE command = 'WATCH');
