--TEST--
Solar_Valid::custom()
--FILE---
<?php
// include ../_prepend.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_prepend.inc')) {
    require dirname(dirname(__FILE__)) . '/_prepend.inc';
}

// include ./_prepend.inc
if (is_readable(dirname(__FILE__) . '/_prepend.inc')) {
    require dirname(__FILE__) . '/_prepend.inc';
}

// ---------------------------------------------------------------------


class customValid {
    static public function staticIsInt($val)
    {
        return is_int($val);
    }
    
    public function isInt($val)
    {
        return is_int($val);
    }
}

$valid = Solar::factory('Solar_Valid');

$callbacks = array(
    'function()'        => 'is_int',
    'static::method()'  => array('customValid', 'staticIsInt'),
    '$object->method()' => array(new customValid(), 'isInt'),
);
    
// good
$test = array(
	1, 2, 5
);
foreach ($callbacks as $callbackName => $callback) {
    foreach ($test as $val) {
        $assert->setLabel("$callbackName, '$val'");
        $assert->isTrue($valid->custom($val, $callback));
    }
}

// bad, or are blank
$test = array(
    ' ', '',
    4.5,
	'0', '1', '2', '5',
	"Seven 8 nine",
);
foreach ($callbacks as $callbackName => $callback) {
    foreach ($test as $val) {
        $assert->setLabel("$callbackName, '$val'");
        $assert->isFalse($valid->custom($val, $callback));
    }
}


// ---------------------------------------------------------------------

// include ./_append.inc
if (is_readable(dirname(__FILE__) . '/_append.inc')) {
    require dirname(__FILE__) . '/_append.inc';
}
// include ../_append.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_append.inc')) {
    require dirname(dirname(__FILE__)) . '/_append.inc';
}
?>
--EXPECT--
