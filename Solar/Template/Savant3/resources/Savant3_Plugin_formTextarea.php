<?php

/**
* 
* Plugin to generate a 'textarea' element.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_formTextarea.php,v 1.6 2005/09/18 16:49:24 pmjones Exp $
* 
*/

require_once 'Savant3_Plugin_form_element.php';


/**
* 
* Plugin to generate a 'textarea' element.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_formTextarea extends Savant3_Plugin_form_element {
	
	
	/**
	* 
	* The default number of rows for a textarea.
	* 
	* @access public
	* 
	* @var int
	* 
	*/
	
	public $rows = 24;
	
	
	/**
	* 
	* The default number of columns for a textarea.
	* 
	* @access public
	* 
	* @var int
	* 
	*/
	
	public $cols = 80;
	
	
	/**
	* 
	* Generates a 'textarea' element.
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
	
	public function formTextarea($name, $value = null, $attribs = null)
	{
		$info = $this->getInfo($name, $value, $attribs);
		extract($info); // name, value, attribs, options, listsep, disable
		$xhtml = '';
		
		// build the element
		if ($disable) {
		
			// disabled.
			$xhtml .= $this->Savant->formHidden($name, $value);
			$xhtml .= nl2br(htmlspecialchars($value));
			
		} else {
		
			// enabled.
			
			// first, make sure that there are 'rows' and 'cols' values
			// as required by the spec.  noted by Orjan Persson.
			if (empty($attribs['rows'])) {
				$attribs['rows'] = (int) $this->rows;
			}
			
			if (empty($attribs['cols'])) {
				$attribs['cols'] = (int) $this->cols;
			}
			
			// now build the element.
			$xhtml = '<textarea';
			$xhtml .= ' name="' . htmlspecialchars($name) . '"';
			$xhtml .= $this->Savant->htmlAttribs($attribs) . '>';
			$xhtml .= htmlspecialchars($value);
			$xhtml .= '</textarea>';
			
		}
		
		return $xhtml;
	}
}
?>