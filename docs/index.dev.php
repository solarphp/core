<?php
/**
 * 
 * Sample index bootstrap file for developing with Solar.
 * 
 * Be sure to edit $include_path and $config properly.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.net>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
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
