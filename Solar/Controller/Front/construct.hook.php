<?php
/**
 * 
 * Solar_Controller_Front construct-time hook script.
 * 
 * This registers objects needed by all Solar_App classes and handes
 * user authentication.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

// register a Solar_Sql object if not already
if (! Solar::inRegistry('sql')) {
    Solar::register('sql', Solar::factory('Solar_Sql'));
}

// register a Solar_User object if not already.
// this will trigger the authentication process.
if (! Solar::inRegistry('user')) {
    Solar::register('user', Solar::factory('Solar_User'));
}

// register a Solar_Content object if not already.
if (! Solar::inRegistry('content')) {
    Solar::register('content', Solar::factory('Solar_Content'));
}

?>