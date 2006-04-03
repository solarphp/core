<?php
// Solar_Cache config
$config = array(
    'driver' => 'Solar_Cache_File',
    'path'   => '/tmp/Solar_Cache_Testing',
    'life'   => 7, // 7 seconds
);

// create a Solar_Cache with the Solar_Cache_File driver
$cache = Solar::factory('Solar_Cache', $config);

// remove all previous entries
$cache->deleteAll();
?>