--TEST--
valid: word()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$test = array(
	0,
	"0",
	"word_only",
	"WordOnLy",
	" Something 8 else",
	"someThing8else",
	"non:word-char's",
	"",
	" ",
);

foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::word($value);
	Solar::dump($result);
}
?>
--EXPECT--
int(0)
bool(true)
string(1) "0"
bool(true)
string(9) "word_only"
bool(true)
string(8) "WordOnLy"
bool(true)
string(17) " Something 8 else"
bool(false)
string(14) "someThing8else"
bool(true)
string(15) "non:word-char's"
bool(false)
string(0) ""
bool(false)
string(1) " "
bool(false)