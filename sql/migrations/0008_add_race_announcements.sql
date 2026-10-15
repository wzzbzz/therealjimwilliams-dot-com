-- Migration 0008 — race announcements: worm_races remembers which
-- announcements it has made (the next race's card, then the winner) so each
-- goes out once. See worm_races::run_schedule() in _classes/worm_races.php.
--
-- Idempotent: safe to re-run.

SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE worm_races ADD COLUMN announced TINYINT UNSIGNED NOT NULL DEFAULT 0',
    'SELECT 1')
    FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'worm_races' AND column_name = 'announced');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(COUNT(*) = 0,
    'ALTER TABLE worm_races ADD COLUMN claim VARCHAR(32) NULL',
    'SELECT 1')
    FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'worm_races' AND column_name = 'claim');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Races from before announcements count as already announced.
UPDATE worm_races SET announced = 2 WHERE announced = 0 AND claim IS NULL AND start_time < UNIX_TIMESTAMP();
