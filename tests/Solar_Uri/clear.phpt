--TEST--
Solar_Uri::clear()
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
$uri->clear();

$assert->same($uri->scheme, null);
$assert->same($uri->host, null);
$assert->same($uri->port, null);
$assert->same($uri->user, null);
$assert->same($uri->pass, null);
$assert->same($uri->path, null);
$assert->same($uri->info, array());
$assert->same($uri->query, array());
$assert->same($uri->fragment, null);

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
