--TEST--
Solar_Uri::fetch()
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

$uri = Solar::factory('Solar_Uri');

// preliminaries
$scheme = 'http';
$host = 'www.example.net';
$port = 8080;
$path = '/some/path/index.php';

$info = array(
    'more', 'path', 'info'
);

$istr = implode('/', $info);

$query = array(
    'a"key' => 'a&value',
    'b?key' => 'this that other',
    'c\'key' => 'tag+tag+tag',
);

$tmp = array();
foreach ($query as $k => $v) {
    $tmp[] .= urlencode($k) . '=' . urlencode($v);
}

$qstr = implode('&', $tmp);

// set up expectations
$expect_full = "$scheme://$host:$port$path/$istr?$qstr";
$expect_part = "$path/$istr?$qstr";

// set the URI
$uri->set($expect_full);

// full fetch
$assert->setLabel('full');
$assert->same($uri->fetch(true), $expect_full);

// partial fetch
$assert->setLabel('part');
$assert->same($uri->fetch(), $expect_part);



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
