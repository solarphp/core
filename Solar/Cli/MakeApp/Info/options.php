<?php
return array(
    'target' => array(
        'long'    => 'table',
        'descr'   => 'The target directory, typically the PEAR directory.',
        'param'   => 'optional',
    ),
    'extends' => array(
        'long'    => 'extends',
        'descr'   => 'Extends from this class name.',
        'param'   => 'required',
    ),
    'model' => array(
        'long'    => 'model',
        'descr'   => 'Add this model class automatically.',
        'param'   => 'required',
    ),
);