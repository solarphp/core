# 'position' becomes 'pos'
ALTER TABLE nodes ADD COLUMN pos INTEGER;
CREATE INDEX nodes__pos__i ON nodes (pos);
UPDATE nodes SET pos = position;
DROP INDEX nodes__position__i ON nodes;
ALTER TABLE nodes DROP COLUMN position;
