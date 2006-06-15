# add 'nodes.status' column
ALTER TABLE nodes ADD COLUMN status VARCHAR(32);
CREATE INDEX nodes__status__i ON nodes (status);

# add 'nodes.moniker' column
ALTER TABLE nodes ADD COLUMN moniker VARCHAR(255);
