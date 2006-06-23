# add 'areas.summ' column
ALTER TABLE areas ADD COLUMN summ VARCHAR(255);

# add 'nodes.status' column
ALTER TABLE nodes ADD COLUMN status VARCHAR(32);
CREATE INDEX nodes__status__i ON nodes (status);

# add 'nodes.moniker' column
ALTER TABLE nodes ADD COLUMN moniker VARCHAR(255);

# add nodes.assign_handle column
ALTER TABLE nodes ADD COLUMN assign_handle VARCHAR(255);
CREATE INDEX nodes__assign_handle__i ON nodes (assign_handle);
