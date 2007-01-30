<?php
/**
 * 
 * Copy this file, edit the SOLAR_CONFIG_PATH to point to your
 * config file, then run from the command line.
 * 
 */
define('SOLAR_CONFIG_PATH', '/path/to/Solar.config.php');

require 'Solar.php';
Solar::start();
$sql = Solar::factory('Solar_Sql');

// DROP INDEX nodes__unique_in_area__i ON nodes;
$sql->dropIndex('nodes', 'unique_in_area');

// CREATE UNIQUE INDEX nodes__area_type_name__i ON nodes (area_id, type, name);
$sql->createIndex(
    'nodes',                            // table
    'area_type_name',                   // index name
    true,                               // unique
    array('area_id', 'type', 'name')    // cols
);

// done!
echo "Done!\n";
?>