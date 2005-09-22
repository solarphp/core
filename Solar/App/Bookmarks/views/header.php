<?php
/**
* 
* Savant3 template for the header portion of every page in the app.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bookmarks
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
* @subpackage Solar_App_Bookmarks
* 
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
		"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Solar_App_Bookmarks</title>
		<style>
		
			body, p, div, td, th, .Savant3 {font-family: "Lucida Sans", Verdana; font-size: 12px;}

			h1 {font-size: 200%};
			h2 {font-size: 160%};
			h3 {font-size: 180%};
			h4 {font-size: 140%};
			h5 {font-size: 120%};
			h6 {font-size: 100%};
			
			table.Savant3 { border-spacing: 1px; }
			th.Savant3 { background: #bcd; text-align: right; vertical-align: top; padding: 4px; }
			td.Savant3 { background: #eee; text-align: left; vertical-align: top;  padding: 4px; }
			
			select, option { font-family: "Lucida Sans", Verdana; font-size: 100%; }
			input[type="text"], input[type="password"], textarea { font-family: "Lucida Sans Typewriter", monospace; font-size: 100%;}
			
		</style>
		
		<?php if ($this->rss['avail']) {
			echo '<link rel="alternate" type="application/rss+xml" title="' . $this->escape($this->rss['title']) . '" ';
			echo 'href="' . $this->escape($this->rss['link']) . '" />' . "\n";
		} ?>
	</head>
	<body>
		
		<h1><?php $this->eprint(Solar::locale('Solar_App_Bookmarks', 'BOOKMARKS')) ?></h1>
		<?php $this->form('set', 'class', 'Savant3') ?>
		<?php include $this->template('auth.php') ?>
	
	