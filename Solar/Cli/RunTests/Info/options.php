<?php
return array(
    'dir' => array(
        'long' => 'dir',
        'param' => 'optional',
        'value' => null,
        'descr' => 'The path to the tests directory.',
    ),
    'only' => array(
        'long' => 'only',
        'param' => null,
        'value' => false,
        'descr' => 'Run only the named test class; do not descend into subclass tests.',
        'filters' => array('validateBool', 'sanitizeBool')
    ),
    'verbose' => array(
        'long' => 'verbose',
        'param' => null,
        'value' => false,
        'descr' => 'Show all diagnostic output.',
        'filters' => array('validateBool', 'sanitizeBool')
    ),
    'test_config' => array(
        'long' => 'test-config',
        'param' => null,
        'value' => false,
        'descr' => 'Use this config file for the test cases themselves.',
        'filters' => array('validateString', 'sanitizeString')
    ),
);
