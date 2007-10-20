<?php
return array(
    'source' => array(
        'long'    => 'source',
        'descr'   => 'The source directory, typically the PEAR directory.',
        'param'   => 'optional',
    ),
    'class' => array(
        'long'    => 'class',
        'descr'   => 'Start documentation with class name and descend recursively.',
        'param'   => 'required',
    ),
    'api_dir' => array(
        'long'    => 'api-dir',
        'descr'   => 'Write API docs to this directory.',
        'param'   => 'optional',
    ),
    'pkg_dir' => array(
        'long'    => 'pkg-dir',
        'descr'   => 'Write package docs to this directory.',
        'param'   => 'optional',
    ),
);