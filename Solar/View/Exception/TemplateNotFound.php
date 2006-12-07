<?php
/**
 * 
 * Solar_View exception for template-not-found.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Base class for Solar_View exceptions.
 */
Solar::loadClass('Solar_View_Exception');

/**
 * 
 * Solar_View exception for template-not-found.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Exception_TemplateNotFound extends Solar_View_Exception {}
