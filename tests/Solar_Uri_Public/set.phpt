--TEST--
Solar_Uri_Public::set()
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

// the URI object itself
$uri = Solar::factory('Solar_Uri_Public');

// set up the expected values
$scheme = 'http';
$host = 'www.example.net';
$path = '/public/Solar/styles/default.css';
$spec = "$scheme://$host$path";

// import the URI spec and test that it imported properly
$uri->set($spec);
$assert->setLabel('Initial import');
$assert->same($uri->scheme, $scheme);
$assert->same($uri->host, $host);
$assert->same($uri->path, array('Solar', 'styles', 'default.css'));

// npw export in full, then re-import and check again.
// do this to make sure there are no translation errors.
$spec = $uri->fetch(true);
$uri->set($spec);
$assert->setLabel('Retranslation');
$assert->same($uri->scheme, $scheme);
$assert->same($uri->host, $host);
$assert->same($uri->path, array('Solar', 'styles', 'default.css'));

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
