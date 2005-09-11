<?php

/**
* 
* Plugin to generate an 'image' element.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_formImage.php,v 1.3 2005/08/12 19:29:39 pmjones Exp $
* 
*/

/**
* 
* Plugin to generate an 'image' element.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_formImage extends Savant3_Plugin {
	
	
	/**
	* 
	* Generates an 'image' element.
	* 
	* @access public
	* 
	* @param string|array $name If a string, the element name.  If an
	* array, all other parameters are ignored, and the array elements
	* are extracted in place of added parameters.
	* 
	* @param mixed $value The source ('src="..."') for the image.
	* 
	* @param array $attribs Attributes for the element tag.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	public function formImage($name, $value = null, $attribs = null)
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
		
		// make sure attribs don't overwrite name and value and src
		unset($attribs['name']);
		unset($attribs['value']);
		unset($attribs['src']);
		
		// build the element
		$xhtml = '<input type="image"';
		$xhtml .= ' name="' . htmlspecialchars($name) . '"';
		$xhtml .= ' src="' . htmlspecialchars($value) . '"';
		$xhtml .= $this->Savant->htmlAttribs($attribs);
		$xhtml .= ' />';
		return $xhtml;
	}
}
?>