--TEST--
valid: inKeys() and inList()
--FILE---
<?php
require_once '../setup.php';
Solar::start();

Solar::loadClass('Solar_Valid');

$opts = array(
	0      => 'val0',
	1      => 'val1',
	'key0' => 'val3',
	'key1' => 'val4',
	'key2' => 'val5'
);

$test = array_merge(
	array_keys($opts),
	array_values($opts),
	array(3, 4, 5)
);

echo "inKeys:\n";
foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::inKeys($value, $opts);
	Solar::dump($result);
	echo "\n";
}

echo "inList:\n";
foreach ($test as $value) {
	Solar::dump($value);
	$result = Solar_Valid::inList($value, $opts);
	Solar::dump($result);
	echo "\n";
}
?>
--EXPECT--
inKeys:
int(0)
bool(true)

int(1)
bool(true)

string(4) "key0"
bool(true)

string(4) "key1"
bool(true)

string(4) "key2"
bool(true)

string(4) "val0"
bool(false)

string(4) "val1"
bool(false)

string(4) "val3"
bool(false)

string(4) "val4"
bool(false)

string(4) "val5"
bool(false)

int(3)
bool(false)

int(4)
bool(false)

int(5)
bool(false)

inList:
int(0)
bool(false)

int(1)
bool(false)

string(4) "key0"
bool(false)

string(4) "key1"
bool(false)

string(4) "key2"
bool(false)

string(4) "val0"
bool(true)

string(4) "val1"
bool(true)

string(4) "val3"
bool(true)

string(4) "val4"
bool(true)

string(4) "val5"
bool(true)

int(3)
bool(false)

int(4)
bool(false)

int(5)
bool(false)