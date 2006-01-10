<?php

/**
* 
* Plugin to generate a 'reset' button.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_formReset.php,v 1.7 2005/09/13 17:35:23 pmjones Exp $
* 
*/

require_once 'Savant3_Plugin_form_element.php';


/**
* 
* Plugin to generate a 'reset' button.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_formReset extends Savant3_Plugin_form_element {
	
	
	/**
	* 
	* Generates a 'reset' button.
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
	
	public function formReset($name, $value = null, $attribs = null)
	{
		$info = $this->getInfo($name, $value, $attribs);
		extract($info); // name, value, attribs, options, listsep, disable
		$xhtml = '';
		
		// always enabled
		$xhtml .= '<input type="reset"';
		$xhtml .= ' name="' . htmlspecialchars($name) . '"';
		
		if (! empty($value)) {
			$xhtml .= ' value="' . htmlspecialchars($value) . '"';
		}
		
		$xhtml .= $this->Savant->htmlAttribs($attribs);
		$xhtml .= ' />';
		
		return $xhtml;
	}
}
?>