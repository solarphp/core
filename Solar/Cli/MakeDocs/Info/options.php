<?php
return array(
    'source' => array(
        'descr'   => 'The source directory, typically the PEAR directory.',
        'param'   => 'optional',
    ),
    'class_dir' => array(
        'descr'   => 'Write class API docs to this directory.',
        'param'   => 'required', 
        'require' => true,
    ),
    'package_dir' => array(
        'descr'   => 'Write package docs to this directory.',
        'param'   => 'required', 
        'require' => true,
    ),
);
