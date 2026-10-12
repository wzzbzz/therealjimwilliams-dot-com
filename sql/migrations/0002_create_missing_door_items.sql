-- Migration 0002 — create the 4 missing door items (playfix issue #3, Part A).
--
-- location_connections.restrictions holds {"door":N} for door ids 4, 5, 11 and
-- 76, but no matching rows exist in `items`, so each exit advertised a door that
-- could not be interacted with (open/close/lock/unlock all returned "You can't
-- see a door here") and movement through the exit was never gated. This migration
-- creates those 4 door items (item_types.id = 2) and places each in BOTH adjoining
-- rooms, mirroring the working doors (3/6/12). The paired code change hardens
-- location::restriction_door() to fail closed when a door item is missing.
--
--   door 4  — 5th Floor Hallway (loc 8)  <-> Apartment 5B (loc 13); key 8 opens it
--   door 5  — 5th Floor Hallway (loc 10) <-> Apartment 5C (loc 14); key 9 opens it
--   door 11 — Welcome Center (loc 2)     <-> Welcome Center Elevator (loc 4); unlocked
--   door 76 — Apartment 5C (loc 14)      <-> loc 38; unlocked
--
-- Keys 8 and 9 already carry opens:["4"]/opens:["5"], so locking doors 4/5 to
-- those keys revives the find-key -> unlock-door loop for apartments 5B and 5C.
--
-- Idempotent: safe to re-run. Each item and item_locations row is inserted only
-- if absent. Explicit ids are used so the existing {"door":N} restrictions resolve.

INSERT INTO items (id, name, type_id, title, description, attributes)
SELECT 4, 'door_apt_5B', 2, 'The Door to Apartment 5B', 'It''s a door.', '{"state":"closed","locked":true,"key":"8"}'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 4);

INSERT INTO items (id, name, type_id, title, description, attributes)
SELECT 5, 'door_apt_5C', 2, 'The Door to Apartment 5C', 'It''s a door.', '{"state":"closed","locked":true,"key":"9"}'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 5);

INSERT INTO items (id, name, type_id, title, description, attributes)
SELECT 11, 'elevator_door_wc', 2, 'The Elevator Door', 'It''s an elevator door', '{"alias":"elevator door","state":"closed","locked":false,"key":0}'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 11);

INSERT INTO items (id, name, type_id, title, description, attributes)
SELECT 76, 'door_5C_east', 2, 'A door', 'It''s a door.', '{"state":"closed","locked":false,"key":0}'
WHERE NOT EXISTS (SELECT 1 FROM items WHERE id = 76);

INSERT INTO item_locations (item_id, location_id)
SELECT 4, 8  WHERE NOT EXISTS (SELECT 1 FROM item_locations WHERE item_id = 4  AND location_id = 8);
INSERT INTO item_locations (item_id, location_id)
SELECT 4, 13 WHERE NOT EXISTS (SELECT 1 FROM item_locations WHERE item_id = 4  AND location_id = 13);
INSERT INTO item_locations (item_id, location_id)
SELECT 5, 10 WHERE NOT EXISTS (SELECT 1 FROM item_locations WHERE item_id = 5  AND location_id = 10);
INSERT INTO item_locations (item_id, location_id)
SELECT 5, 14 WHERE NOT EXISTS (SELECT 1 FROM item_locations WHERE item_id = 5  AND location_id = 14);
INSERT INTO item_locations (item_id, location_id)
SELECT 11, 2 WHERE NOT EXISTS (SELECT 1 FROM item_locations WHERE item_id = 11 AND location_id = 2);
INSERT INTO item_locations (item_id, location_id)
SELECT 11, 4 WHERE NOT EXISTS (SELECT 1 FROM item_locations WHERE item_id = 11 AND location_id = 4);
INSERT INTO item_locations (item_id, location_id)
SELECT 76, 14 WHERE NOT EXISTS (SELECT 1 FROM item_locations WHERE item_id = 76 AND location_id = 14);
INSERT INTO item_locations (item_id, location_id)
SELECT 76, 38 WHERE NOT EXISTS (SELECT 1 FROM item_locations WHERE item_id = 76 AND location_id = 38);
