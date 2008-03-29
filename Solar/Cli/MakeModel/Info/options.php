<?php
return array(
    'target' => array(
        'long'    => 'table',
        'descr'   => 'The target directory, typically the PEAR directory.',
        'param'   => 'optional',
    ),
    'table' => array(
        'long'    => 'table',
        'descr'   => 'The table name to derive the model from.',
        'param'   => 'required',
    ),
    'extends' => array(
        'long'    => 'extends',
        'descr'   => 'Extends from this class name.',
        'param'   => 'required',
    ),
    'adapter' => array(
        'long'    => 'adapter',
        'descr'   => 'The SQL adapter class to use.',
        'param'   => 'required',
    ),
    'host' => array(
        'long'    => 'host',
        'descr'   => 'The database host.',
        'param'   => 'required',
    ),
    'port' => array(
        'long'    => 'port',
        'descr'   => 'The database port.',
        'param'   => 'required',
    ),
    'user' => array(
        'long'    => 'user',
        'descr'   => 'The username for the database connection.',
        'param'   => 'required',
    ),
    'pass' => array(
        'long'    => 'pass',
        'descr'   => 'The password for the database connection.',
        'param'   => 'required',
    ),
    'name' => array(
        'long'    => 'name',
        'descr'   => 'The name of the database.',
        'param'   => 'required',
    ),
);
