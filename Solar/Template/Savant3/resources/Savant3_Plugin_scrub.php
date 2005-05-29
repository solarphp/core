<?php

/**
* 
* Plugin to scrub (modify) a value with a series of callbacks.
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_scrub.php,v 1.5 2005/05/27 14:00:25 pmjones Exp $
*
*/

/**
* 
* Plugin to scrub (modify) a value with a series of callbacks.
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_scrub extends Savant3_Plugin {
	
	
	/**
	* 
	* The default callbacks to apply when making output safe.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $default = array('htmlspecialchars');
	
	
	/**
	* 
	* Custom callback sets.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $custom = array(
		'utf8' => array('utf8_decode', 'htmlspecialchars'),
	);
	
	
	/**
	* 
	* Scrubs a value for output.
	* 
	* <code>
	* echo $this->scrub($value);
	* </code>
	* 
	* To use a custom scrubber set (e.g., the 'utf8' set):
	* 
	* <code>
	* echo $this->scrub($value, 'utf8');
	* </code>
	* 
	* @access public
	* 
	* @param string $value The value to be scrubbed.
	* 
	* @param string|array $callbacks An array of parameters for
	* call_user_func().  If not specified, uses the defaults from the
	* $callbacks property.
	* 
	* @return mixed The scrubbed value.
	* 
	* @todo Add support for recursive scrubbing of arrays.
	* 
	*/
	
	function scrub($value, $custom = null)
	{
		if (! is_null($custom) && isset($this->custom[$custom])) {
			$callbacks = $this->custom[$custom];
		} else {
			$callbacks = $this->default;
		}
		
		// is there a space-delimited callback list?
		// if so, treat as a series of functions.
		if (is_string($callbacks)) {
			// yes.  split into an array of the
			// functions to be called.
			$callbacks = explode(' ', $callbacks);
		}
		
		// loop through the callback list and
		// apply to the output in sequence.
		foreach ($callbacks as $func) {
			if (trim($func) != '') {
				$value = call_user_func($func, $value);
			}
		}
		
		return $value;
	}

}
?>