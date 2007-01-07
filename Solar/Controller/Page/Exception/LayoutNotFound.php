<?php
/**
 * 
 * Page-controller could not find the file for a named layout.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ActionNotFound.php 2047 2006-12-18 17:29:29Z pmjones $
 * 
 */

/**
 * Parent class.
 */
Solar::loadClass('Solar_Controller_Page_Exception');

/**
 * 
 * Page-controller could not find the file for a named layout.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 */
class Solar_Controller_Page_Exception_LayoutNotFound extends Solar_Controller_Page_Exception {}
