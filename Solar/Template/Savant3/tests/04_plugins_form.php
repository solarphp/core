<?php
require_once '00_prepend.php';

// fake superglobals as needed
if (empty($_SERVER['REQUEST_URI'])) {
	// fake the request URI
	$_SERVER['REQUEST_URI'] = 'http://example.com/form.php?foo=bar&baz=dib';
}

if (empty($_POST)) {
	$_POST = array();
}

// configure and instantiate Savant
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);
$tpl->setTemplate('04_plugins_form.tpl.php');

// build some test values
$defaults = array(
	'hideme' => null,
	'mytext' => null,
	'xbox' => null,
	'picker' => null,
	'picker2' => null,
	'chooser' => null,
	'myarea' => null
);

$values = array_merge($defaults, $_POST);

$tmp = array();

if ($values['mytext'] == '') {
	// required
	$tmp[] = 'required';
}

if (strlen($values['mytext']) > 5) {
	// max 5 chars
	$tmp[] = 'max length is 5 chars';
}

if (preg_match('/[0-9]+/', $values['mytext'])) {
	// no digits
	$tmp[] = 'no digits allowed';
}

// validation messages for each element
$valid = array('mytext' => null);
if (count($tmp) > 0) {
	$valid['mytext'] = $tmp;
}

$tpl->opts = array('one', 'two', 'three', 'four', 'five');
$tpl->valid = $valid;
$tpl->assign($values);


echo $tpl;

?>
