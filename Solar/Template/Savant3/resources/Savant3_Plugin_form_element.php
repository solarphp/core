<?php

/**
* 
* Base plugin for form elements.  Extend this, don't use it on its own.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_form_element.php,v 1.3 2005/09/13 17:35:23 pmjones Exp $
* 
*/

/**
* 
* Base plugin for form elements.  Extend this, don't use it on its own.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_form_element extends Savant3_Plugin {
	
	
	/**
	* 
	* Converts parameter arguments to an element info array.
	* 
	* E.g, formExample($name, $value, $attribs, $options, $listsep) is
	* the same thing as formExample(array('name' => ...)).
	* 
	* Note that you cannot pass a 'disable' param; you need to pass
	* it as an 'attribs' key.  A "'readonly' => 'readonly'" attribs
	* key-value pair has the same effect as "'disable' => true".
	* 
	* @access protected
	* 
	* @return array An element info array with keys for name, value,
	* attribs, options, listsep, and disable.
	* 
	*/
	
	protected function getInfo($name, $value = null, $attribs = null, 
		$options = null, $listsep = null)
	{
		// the baseline info.  note that $name serves a dual purpose;
		// if an array, it's an element info array that will override
		// these baseline values.  as such, ignore it for the 'name' 
		// if it's an array.
		$info = array(
			'name'    => is_array($name) ? '' : $name,
			'value'   => $value,
			'attribs' => $attribs,
			'options' => $options,
			'listsep' => $listsep,
			'disable' => false,
		);
		
		// override with named args
		if (is_array($name)) {
			// only set keys that are already in info
			foreach ($info as $key => $val) {
				if (isset($name[$key])) {
					$info[$key] = $name[$key];
				}
			}
		}
		
		// disable if readonly
		if (isset($info['attribs']['readonly']) &&
			$info['attribs']['readonly'] == 'readonly') {
			// disable the element
			$info['disable'] = true;
			unset($info['attribs']['readonly']);
		}
		
		// normal disable, overrides readonly
		if (isset($info['attribs']['disable']) &&
			$info['attribs']['disable']) {
			// disable the element
			$info['disable'] = true;
			unset($info['attribs']['disable']);
		}
		
		// remove attribs that might overwrite the other keys.
		// we do this LAST because we needed the other attribs
		// values earlier.
		foreach ($info as $key => $val) {
			if (isset($info['attribs'][$key])) {
				unset($info['attribs'][$key]);
			}
		}
		
		// done!
		return $info;
	}
}
?>