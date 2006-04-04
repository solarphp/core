--TEST--
Solar_Uri::__construct()
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
$assert->isInstance($uri, 'Solar_Uri');

// test default values from _setup.inc
$assert->same($uri->scheme, 'http');
$assert->same($uri->host, 'example.com');
$assert->same($uri->port, null);
$assert->same($uri->user, null);
$assert->same($uri->pass, null);
$assert->same($uri->path, array('path', 'to', 'index.php', 'appname', 'action'));
$assert->same($uri->query, array('foo'=>'bar', 'baz'=>'dib'));
$assert->same($uri->fragment, 'zim');

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
