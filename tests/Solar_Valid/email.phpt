--TEST--
Solar_Valid::email()
--FILE---
<?php
require dirname(dirname(__FILE__)) . '/_prepend.inc';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
	"pmjones@solarphp.net",
	"no.body@no.where.com",
	"any-thing@gmail.com",
	"any_one@hotmail.com",
	"nobody1234567890@yahoo.co.uk",
	"something+else@example.com",
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->email($val));
}

// bad, or are blank
$test = array(
	"something @ somewhere.edu",
	"the-name.for!you",
	"non:alpha@example.com",
	"",
	"\t\n",
	" ",
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isFalse($valid->email($val));
}


// blanks allowed
$test = array(
	"",
	"\t\n",
	" ",
	"pmjones@solarphp.net",
	"no.body@no.where.com",
	"any-thing@gmail.com",
	"any_one@hotmail.com",
	"nobody1234567890@yahoo.co.uk",
);
foreach ($test as $val) {
    $assert->setLabel($val);
    $assert->isTrue($valid->email($val, Solar_Valid::OR_BLANK));
}



// ---------------------------------------------------------------------
require dirname(dirname(__FILE__)) . '/_append.inc';
?>
--EXPECT--
