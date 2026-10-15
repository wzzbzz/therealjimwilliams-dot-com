-- Migration 0010 — widen users.attributes to 1024 characters, matching dev.
--
-- Live had it at varchar(256). Adding "roles" pushed a player's attributes past
-- that, MySQL cut the JSON off, the status poller couldn't parse it and reset the
-- player to {"hunger":..,"shit":..,"belly":..}, wiping their roles and stats.
--
-- Idempotent: safe to re-run.

ALTER TABLE users MODIFY attributes VARCHAR(1024) NOT NULL DEFAULT '{}';
