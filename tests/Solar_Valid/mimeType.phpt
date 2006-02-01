--TEST--
Solar_Valid::mimeType()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.php';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	'text/plain',
	'text/xhtml+xml',
	'application/vnd.ms-powerpoint',
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->mimeType($val));
}

// bad, or are blank
$test = array(
    ' ', '',
    'text/',
    '/something',
	0, 1, 2, 5,
	'0', '1', '2', '5',
	"Seven 8 nine",
	"non:alpha-numeric's",
	'someThing8else',
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isFalse($valid->mimeType($val));
}


// blanks allowed
$test = array(
    '', ' ',
	'text/plain',
	'text/xhtml+xml',
	'application/vnd.ms-powerpoint',
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->mimeType($val, null, Solar_Valid::OR_BLANK));
}

// only certain types allowed
$allowed = array('text/plain', 'text/html', 'text/xhtml+xml');
$assert->isTrue($valid->mimeType('text/html', $allowed));
$assert->isFalse($valid->mimeType('application/vnd.ms-powerpoint', $allowed));


// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.php';
?>
--EXPECT--
test complete