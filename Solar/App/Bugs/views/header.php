<?php
/**
* 
* Savant3 template for the header portion of every page in the app.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* 
* Savant3 template for the header portion of every page in the app.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
		"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Solar_App_Bugs</title>
		<style>
			body, p, div, td, th, .Savant3 {font-family: "Lucida Sans", Verdana; font-size: 12px;}
			table.Savant3 { border-spacing: 1px; }
			th.Savant3 { background: #bcd; text-align: right; vertical-align: top; padding: 4px; }
			td.Savant3 { background: #eee; text-align: left; vertical-align: top;  padding: 4px; }
			select, option { font-family: "Lucida Sans", Verdana; font-size: 12px; }
			input[type="text"], input[type="password"], textarea { font-family: "Lucida Sans Typewriter", monospace; font-size: 12px;}
		</style>
	</head>
	<body>
	
	<?php $this->form('set', 'class', 'Savant3') ?>
	<?php include $this->template('auth.php') ?>
	
	