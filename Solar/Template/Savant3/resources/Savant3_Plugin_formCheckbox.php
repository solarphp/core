<?php

/**
* 
* Plugin to generate a 'checkbox' element.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_formCheckbox.php,v 1.3 2005/08/12 19:29:39 pmjones Exp $
* 
*/

/**
* 
* Plugin to generate a 'checkbox' element.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_formCheckbox extends Savant3_Plugin {
	
	/**
	* 
	* Generates a 'checkbox' element.
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
	* @param mixed $options If a scalar (single value), the value of the
	* checkbox when checked; if an array, element 0 is the value when
	* checked, and element 1 is the value when not-checked.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	public function formCheckbox($name, $value = null, $attribs = null,
		$options = null)
	{
		// are we pulling the pieces from a Solar_Form array?
		$arg = func_get_arg(0);
		if (is_array($arg)) {
			// merge and extract variables.
			$default = array(
				'name'    => null,
				'value'   => null,
				'attribs' => null,
				'options' => null,
			);
			$arg = array_merge($default, $arg);
			extract($arg);
			settype($attribs, 'array');
		}
		
		// make sure attribs don't overwrite name and value
		unset($attribs['name']);
		unset($attribs['value']);
		
		// set up checked/unchecked options
		if (empty($options)) {
			$options = array(1, 0);
		} else {
			settype($options, 'array');
			if (! isset($options[1])) {
				$options[1] = null;
			}
		}
		
		// add the "unchecked" option first
		$xhtml = $this->Savant->formHidden($name, $options[1]);
		
		// add the "checked" option (the checkbox itself) next.
		// this way, if not-checked, the "unchecked" option is 
		// returned to the server instead.
		$xhtml .= '<input type="checkbox"';
		$xhtml .= ' name="' . htmlspecialchars($name) . '"';
		$xhtml .= ' value="' . htmlspecialchars($options[0]) . '"';
		
		// is it checked already?
		if ($value == $options[0]) {
			$xhtml .= ' checked="checked"';
		}
		
		// add attributes, and done.
		$xhtml .= $this->Savant->htmlAttribs($attribs);
		$xhtml .= ' />';
		return $xhtml;
	}
}
?>