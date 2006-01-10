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
* @version $Id: Savant3_Plugin_formImage.php,v 1.6 2005/09/13 17:35:23 pmjones Exp $
* 
*/

require_once 'Savant3_Plugin_form_element.php';


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

class Savant3_Plugin_formImage extends Savant3_Plugin_form_element {
	
	
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
		$info = $this->getInfo($name, $value, $attribs);
		extract($info); // name, value, attribs, options, listsep, disable
		$xhtml = '';
		
		// unset any 'src' attrib
		if (isset($attribs['src'])) {
			unset($attribs['src']);
		}
		
		// unset any 'alt' attrib
		if (isset($attribs['alt'])) {
			unset($attribs['alt']);
		}
		
		// build the element
		if ($disable) {
			// disabled, just an image tag
			$xhtml .= '<image';
			$xhtml .= ' alt="' . htmlspecialchars($name) . '"';
			$xhtml .= ' src="' . htmlspecialchars($value) . '"';
			$xhtml .= $this->Savant->htmlAttribs($attribs);
			$xhtml .= ' />';
		} else {
			// enabled
			$xhtml = '<input type="image"';
			$xhtml .= ' name="' . htmlspecialchars($name) . '"';
			$xhtml .= ' src="' . htmlspecialchars($value) . '"';
			$xhtml .= $this->Savant->htmlAttribs($attribs);
			$xhtml .= ' />';
		}
		
		return $xhtml;
	}
}
?>