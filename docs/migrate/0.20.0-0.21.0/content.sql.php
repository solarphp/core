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

// add summ_x as holding column (identical to summ)
$info = array(
    'type' => 'varchar',
    'size' => 255,
);
$sql->addColumn('nodes', 'summ_x', $info);

// copy from summ to summ_x
$sql->query('UPDATE nodes SET summ_x = summ');

// drop original summ column
$sql->dropColumn('nodes', 'summ');

// re-add summ as a clob
$info = array(
    'type' => 'clob',
);
$sql->addColumn('nodes', 'summ', $info);

// copy from summ_x to summ
$sql->query('UPDATE nodes SET summ = summ_x');

// drop summ_x column
$sql->dropColumn('nodes', 'summ_x');

// done!
echo "Done!\n";
?>