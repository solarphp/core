# 'rank' becomes 'position'
ALTER TABLE nodes ADD COLUMN position INTEGER;
CREATE INDEX nodes__position__i ON nodes (position);
UPDATE nodes SET position = rank;
DROP INDEX nodes__rank__i ON nodes;
ALTER TABLE nodes DROP COLUMN rank;

# 'part_of' becomes 'parent_id'
ALTER TABLE nodes ADD COLUMN parent_id INTEGER;
CREATE INDEX nodes__parent_id__i ON nodes (parent_id);
UPDATE nodes SET parent_id = part_of;
DROP INDEX nodes__parent_id__i ON nodes;
ALTER TABLE nodes DROP COLUMN part_of;
