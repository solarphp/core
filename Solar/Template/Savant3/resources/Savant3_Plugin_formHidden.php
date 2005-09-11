<?php

/**
* 
* Plugin to generate a 'hidden' element.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_formHidden.php,v 1.3 2005/08/12 19:29:39 pmjones Exp $
* 
*/

/**
* 
* Plugin to generate a 'hidden' element.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_formHidden extends Savant3_Plugin {
	
	
	/**
	* 
	* Generates a 'hidden' element.
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
	
	public function formHidden($name, $value = null, $attribs = null)
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
		}
		
		// make sure attribs don't overwrite name and value
		unset($attribs['name']);
		unset($attribs['value']);
		
		// build the element
		$xhtml = '<input type="hidden"';
		$xhtml .= ' name="' . htmlspecialchars($name) . '"';
		$xhtml .= ' value="' . htmlspecialchars($value) . '"';
		$xhtml .= $this->Savant->htmlAttribs($attribs);
		$xhtml .= ' />';
		return $xhtml;
	}
}
?>