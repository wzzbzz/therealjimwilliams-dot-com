-- Migration 0004 — food truck: the `truck` item type and the `buy` / `menu` commands.
--
-- The truck's behavior lives in _classes/items/food_trucks.php and the commands
-- in _classes/commander/commands/{buy,menu}.php. This only adds the rows that
-- code needs; placing trucks and marking the farm is done in-game by an admin:
--
--   make truck                         (in the room where the truck should park)
--   set location attribute farm yes    (in the room the truck restocks at)
--
-- A new truck starts open with 5 burritos at $5 each (base_attributes). When it
-- sells its last one it drives to the farm, loads up, and drives back.
--
-- Idempotent: safe to re-run.

INSERT INTO item_types (parent_type, title, name, plural_title, description, actions, action_responses, base_attributes)
SELECT 0, 'A food truck', 'truck', 'some food trucks',
       'A food truck with a hand-painted menu on the side. Type ''menu'' to read it, or ''buy burrito''.',
       '["look"]', '{}',
       '{"state":"open","stock":5,"capacity":5,"menu":{"burrito":5}}'
WHERE NOT EXISTS (SELECT 1 FROM item_types WHERE name = 'truck');

INSERT INTO commands (command, type, listed)
SELECT 'BUY', 'OUTPUT', 1
WHERE NOT EXISTS (SELECT 1 FROM commands WHERE command = 'BUY');

INSERT INTO commands (command, type, listed)
SELECT 'MENU', 'OUTPUT', 1
WHERE NOT EXISTS (SELECT 1 FROM commands WHERE command = 'MENU');
