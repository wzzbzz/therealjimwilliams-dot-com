-- Migration 0006 — the worm stable: the `worms` table, the weather and lineup
-- each race ran with, and the `hatch` admin command.
--
-- Worms are hatched in-game by an admin (`hatch <name>`); the track fields the
-- five worms nearest their sweet spot for rest. See _classes/worm_races.php.
--
-- Idempotent: safe to re-run.

CREATE TABLE IF NOT EXISTS worms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    wriggle TINYINT UNSIGNED NOT NULL,
    stamina TINYINT UNSIGNED NOT NULL,
    constitution TINYINT UNSIGNED NOT NULL,
    grit TINYINT UNSIGNED NOT NULL,
    hatched_at INT UNSIGNED NOT NULL,
    last_raced_at INT UNSIGNED NULL,
    races INT UNSIGNED NOT NULL DEFAULT 0,
    wins INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY name (name)
);

-- Races from before the stable have no lineup to replay; drop them.
DELETE FROM worm_races
WHERE NOT EXISTS (SELECT 1 FROM information_schema.columns
                  WHERE table_schema = DATABASE() AND table_name = 'worm_races' AND column_name = 'lineup');

SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE worm_races ADD COLUMN weather VARCHAR(20) NOT NULL DEFAULT ''Mild''',
    'SELECT 1')
    FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'worm_races' AND column_name = 'weather');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE worm_races ADD COLUMN lineup MEDIUMTEXT NULL',
    'SELECT 1')
    FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'worm_races' AND column_name = 'lineup');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO commands (command, type, listed)
SELECT 'HATCH', 'OUTPUT', 0
WHERE NOT EXISTS (SELECT 1 FROM commands WHERE command = 'HATCH');
