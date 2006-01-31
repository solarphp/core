--TEST--
Solar_Valid::uri()
--FILE---
<?php
require '../_prepend.php';
// ---------------------------------------------------------------------

$valid = Solar::factory('Solar_Valid');

// good
$test = array(
    "http://example.com",
    "http://example.com/path/to/file.php",
    "http://example.com/path/to/file.php/info",
	"http://example.com/path/to/file.php/info?foo=bar&baz=dib#zim",
	"http://example.com/?foo=bar&baz=dib#zim",
	"mms://user:pass@site.info/path/to/file.php/info?foo=bar&baz=dib#zim",
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->uri($val));
}

// bad, or are blank
$test = array(
	"", '',
	'a,', '^b', '%',
	'ab-db cd-ef',
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isFalse($valid->uri($val));
}

// blanks allowed
$test = array(
    "", ' ',
    "http://example.com/path/to/file.php/info?foo=bar&baz=dib#zim",
    "mms://user:pass@site.info/path/to/file.php/info?foo=bar&baz=dib#zim",
);
foreach ($test as $val) {
    $assert->setLabel("'$val'");
    $assert->isTrue($valid->uri($val, null, Solar_Valid::OR_BLANK));
}

// only certain schemes allowed
$test = "http://example.com/path/to/file.php/info?foo=bar&baz=dib#zim";
$assert->isTrue($valid->uri($test, 'http'));
$assert->isTrue($valid->uri($test, array('ftp', 'http', 'news')));
$assert->isFalse($valid->uri($test, array('ftp', 'mms', 'gopher')));


// ---------------------------------------------------------------------
require '../_append.php';
?>
--EXPECT--
test complete