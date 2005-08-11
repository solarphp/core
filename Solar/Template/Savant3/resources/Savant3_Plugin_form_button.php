<?php

/**
* 
* Plugin to generate a 'button' element.
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_form_button.php,v 1.2 2005/08/09 21:54:24 pmjones Exp $
* 
*/

/**
* 
* Plugin to generate a 'button' element.
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_form_button extends Savant3_Plugin {

	/**
	* 
	* Generates a 'button' element.
	* 
	* @access public
	* 
	* @param string|array $name If a string, the element name.  If an
	* array, all other parameters are ignored, and the array elements
	* are extracted in place of added parameters.
	* 
	* @param mixed $value The element value.
	* 
	* @param array $attribs Attributes for the element tag.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	public function form_button($name, $value = null, $attribs = null)
	{
		// are we pulling the pieces from a Solar_Form array?
		$arg = func_get_arg(0);
		if (is_array($arg)) {
			// merge and extract variables.
			$default = array(
				'name'    => null,
				'value'   => null,
				'attribs' => null,
			);
			$arg = array_merge($default, $arg);
			extract($arg);
			settype($attribs, 'array');
			unset($attribs['name']);
			unset($attribs['value']);
		}
		
		// build the element
		$xhtml = '<input type="button"';
		
		if (! empty($name)) {
			$xhtml .= ' name="' . htmlspecialchars($name) . '"';
		}
		
		if (! empty($value)) {
			$xhtml .= ' value="' . htmlspecialchars($value) . '"';
		}
		
		$xhtml .= $this->Savant->html_attribs($attribs);
		$xhtml .= ' />';
		return $xhtml;
	}
}

?>