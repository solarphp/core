--TEST--
valid: email()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test_good = array(
	"pmjones@solarphp.net",
	"no.body@no.where.com",
	"any-thing@gmail.com",
	"any_one@hotmail.com",
	"nobody1234567890@yahoo.co.uk",
);

$test_bad = array(
	"something @ somewhere.edu",
	"the-name.for!you",
	"non:alpha@example.com",
	"",
	"\t\n",
	" ",
);

echo "Good:\n";
foreach ($test_good as $value) {
	Solar::dump($value);
	$result = Solar_Valid::email($value);
	Solar::dump($result);
	echo "\n";
}

echo "\nBad:\n";
foreach ($test_bad as $value) {
	Solar::dump($value);
	$result = Solar_Valid::email($value);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
Good:
string(20) "pmjones@solarphp.net"
bool(true)

string(20) "no.body@no.where.com"
bool(true)

string(19) "any-thing@gmail.com"
bool(true)

string(19) "any_one@hotmail.com"
bool(true)

string(28) "nobody1234567890@yahoo.co.uk"
bool(true)


Bad:
string(25) "something @ somewhere.edu"
bool(false)

string(16) "the-name.for!you"
bool(false)

string(21) "non:alpha@example.com"
bool(false)

string(0) ""
bool(false)

string(2) "	
"
bool(false)

string(1) " "
bool(false)