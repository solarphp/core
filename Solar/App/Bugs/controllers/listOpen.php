<?php

/**
* 
* Controller action script for viewing a list of all "open" reports.
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
* Controller action script for viewing a list of all "open" reports.
* 
* @category Solar
* 
* @package Solar_App
* 
* @subpackage Solar_App_Bugs
* 
*/

// prepend for all controllers
include $this->helper('prepend');

// list only open bugs
$tpl->list = $bugs->fetchOpen();

// display
return $tpl->fetch('list.php');
?>