<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);

$include_path = '/path/to/pear';
ini_set('include_path', $include_path);

require_once 'Solar.php';

$config = null;
Solar::start($config);

$front = Solar::factory('Solar_Controller_Front');
$front->display();

Solar::stop();
