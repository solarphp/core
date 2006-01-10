<?php

/**
* 
* Plugin to generate a set of radio button elements.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_formRadio.php,v 1.7 2005/09/13 21:54:39 pmjones Exp $
* 
*/

require_once 'Savant3_Plugin_form_element.php';


/**
* 
* Plugin to generate a set of radio button elements.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_formRadio extends Savant3_Plugin_form_element {
	
	
	/**
	* 
	* Generates a set of radio button elements.
	* 
	* @access public
	* 
	* @param string|array $name If a string, the element name.  If an
	* array, all other parameters are ignored, and the array elements
	* are extracted in place of added parameters.
	* 
	* @param mixed $value The radio value to mark as 'checked'.
	* 
	* @param array $options An array of key-value pairs where the array
	* key is the radio value, and the array value is the radio text.
	* 
	* @param array|string $attribs Attributes added to each radio.
	* 
	* @return string The radio buttons XHTML.
	* 
	*/
	
	public function formRadio($name, $value = null, $attribs = null, 
		$options = null, $listsep = "<br />\n")
	{
		$info = $this->getInfo($name, $value, $attribs, $options, $listsep);
		extract($info); // name, value, attribs, options, listsep, disable
		$xhtml = '';
		
		// retrieve attributes for labels (prefixed with 'label_')
		$label_attribs = array('style' => 'white-space: nowrap;');
		foreach ($attribs as $key => $val) {
			if (substr($key, 0, 6) == 'label_') {
				$tmp = substr($key, 6);
				$label_attribs[$tmp] = $val;
				unset($attribs[$key]);
			}
		}
		
		// the radio button values and labels
		settype($options, 'array');
		
		// default value if none are checked
		$xhtml .= $this->Savant->formHidden($name, null);
		
		// build the element
		if ($disable) {
		
			// disabled.
			// show the radios as a plain list.
			$list = array();
			
			// create the list of radios.
			foreach ($options as $opt_value => $opt_label) {
				if ($opt_value == $value) {
					// add a return value, and a checked text.
					$opt = $this->Savant->formHidden($name, $opt_value) . '[x]';
				} else {
					// not checked
					$opt = '[&nbsp;]';
				}
				$list[] = $opt . '&nbsp;' . $opt_label;
			}
			
			$xhtml .= implode($listsep, $list);
			
		} else {
		
			// enabled.
			// the array of all radios.
			$list = array();
			
			// add radio buttons to the list.
			foreach ($options as $opt_value => $opt_label) {
			
				// begin the label wrapper
				$radio = '<label';
				$radio .= $this->Savant->htmlAttribs($label_attribs);
				$radio .= '>';
				
				// begin the radio itself
				$radio .= '<input type="radio"';
				$radio .= ' name="' . htmlspecialchars($name) . '"';
				$radio .= ' value="' . htmlspecialchars($opt_value) . '"';
				
				// is it checked?
				if ($opt_value == $value) {
					$radio .= ' checked="checked"';
				}
				
				// add attribs and end the radio itself
				$radio .= $this->Savant->htmlAttribs($attribs);
				$radio .= ' />';
				
				// add the label and end the label wrapper
				$radio .= htmlspecialchars($opt_label) . '</label>';
				
				// add to the array of radio buttons
				$list[] = $radio;
			}
			
			// done!
			$xhtml .= implode($listsep, $list);
		}
		
		return $xhtml;
	}
}
?>