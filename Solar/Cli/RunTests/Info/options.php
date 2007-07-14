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
    ),
    'include_path' => array(
        'long' => 'include-path',
        'param' => 'required',
        'descr' => 'Prepends this value to the include-path.',
    ),
);