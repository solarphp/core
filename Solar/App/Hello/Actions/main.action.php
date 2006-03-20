<?php
/**
 * 
 * Controller action script for viewing in HTML.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_HelloWorld
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

// get the locale code, default is en_US
$this->code = $this->_query('code', 'en_US');

// reset the locale strings to the new code
Solar::setLocale($this->code);

// set the translated text
$this->text = $this->locale('TEXT_HELLO_WORLD');

// tell the site layout what title to use
$this->layout_title = 'Solar: Hello World!';
?>