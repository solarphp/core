<?php
// when running from the command line, these elements are empty.
// add them so that web-like testing can occur.

$_SERVER['HTTP_HOST']    = 'example.com';
$_SERVER['SCRIPT_NAME']  = '/path/to/index.php';
$_SERVER['PATH_INFO']    = '/appname/action';
$_SERVER['QUERY_STRING'] = 'foo=bar&baz=dib';
$_SERVER['REQUEST_URI']  = $_SERVER['SCRIPT_NAME']
                         . $_SERVER['PATH_INFO']
                         . '?' . $_SERVER['QUERY_STRING']
                         . '#zim';

// emulate $_GET vars from the URI
parse_str($_SERVER['QUERY_STRING'], $_GET);

?>