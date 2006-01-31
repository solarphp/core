--TEST--
Solar_Valid::minLength()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

$len = strlen("I am the very model");

// good
$test = array(
	"I am the very model",
	"I am the very model of a modern",
	"I am the very model of a moden Major-General",
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->minLength($val, $len));
}

// bad, or are blank
$test = array(
	"", " ",
	0,
	"I am",
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isFalse($valid->minLength($val, $len));
}

// blanks allowed
$test = array(
	"", ' ',
	"I am the very model",
	"I am the very model of a modern",
	"I am the very model of a moden Major-General",
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->minLength($val, $len, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete