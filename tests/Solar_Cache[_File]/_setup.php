<?php
// Solar_Cache config
$config = array(
    'class' => 'Solar_Cache_File',
    'options' => array(
        'path' => '/tmp/Solar_Cache_Testing',
        'life' => 7, // 7 seconds
    ),
);

// create a Solar_Cache with the Solar_Cache_File driver
$cache = Solar::factory('Solar_Cache', $config);

// remove all previous entries
$cache->deleteAll();
?>