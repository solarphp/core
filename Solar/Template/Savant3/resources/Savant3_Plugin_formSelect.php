<?php

/**
* 
* Plugin to generate 'select' list of options.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_formSelect.php,v 1.6 2005/09/13 21:54:39 pmjones Exp $
* 
*/

require_once 'Savant3_Plugin_form_element.php';


/**
* 
* Plugin to generate 'select' list of options.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_formSelect extends Savant3_Plugin_form_element {
	
	
	/**
	* 
	* Generates 'select' list of options.
	* 
	* @access public
	* 
	* @param string|array $name If a string, the element name.  If an
	* array, all other parameters are ignored, and the array elements
	* are extracted in place of added parameters.
	* 
	* @param mixed $value The option value to mark as 'selected'; if an 
	* array, will mark all values in the array as 'selected' (used for
	* multiple-select elements).
	* 
	* @param array|string $attribs Attributes added to the 'select' tag.
	* 
	* @param array $options An array of key-value pairs where the array
	* key is the radio value, and the array value is the radio text.
	* 
	* @param string $listsep When disabled, use this list separator string
	* between list values.
	* 
	* @return string The select tag and options XHTML.
	* 
	*/
	
	public function formSelect($name, $value = null, $attribs = null,
		$options = null, $listsep = "<br />\n")
	{
		$info = $this->getInfo($name, $value, $attribs, $options, $listsep);
		extract($info); // name, value, attribs, options, listsep, disable
		$xhtml = '';
		
		// force $value to array so we can compare multiple values
		// to multiple options.
		settype($value, 'array');
		
		// check for multiple attrib and change name if needed
		if (isset($attribs['multiple']) &&
			$attribs['multiple'] == 'multiple' &&
			substr($name, -2) != '[]') {
			$name .= '[]';
		}
		
		// check for multiple implied by the name and set attrib if
		// needed
		if (substr($name, -2) == '[]') {
			$attribs['multiple'] = 'multiple';
		}
		
		// now start building the XHTML.
		if ($disable) {
		
			// disabled.
			// generate a plain list of selected options.
			// show the label, not the value, of the option.
			$list = array();
			foreach ($options as $opt_value => $opt_label) {
				if (in_array($opt_value, $value)) {
					// add the hidden value
					$opt = $this->Savant->formHidden($name, $opt_value);
					// add the display label
					$opt .= htmlspecialchars($opt_label);
					// add to the list
					$list[] = $opt;
				}
			}
			$xhtml .= implode($listsep, $list);
			
		} else {
		
			// enabled.
			// the surrounding select element first.
			$xhtml .= '<select';
			$xhtml .= ' name="' . htmlspecialchars($name) . '"';
			$xhtml .= $this->Savant->htmlAttribs($attribs);
			$xhtml .= ">\n\t";
			
			// build the list of options
			$list = array();
			foreach ($options as $opt_value => $opt_label) {
				$opt = '<option';
				$opt .= ' value="' . htmlspecialchars($opt_value) . '"';
				$opt .= ' label="' . htmlspecialchars($opt_label) . '"';
				if (in_array($opt_value, $value)) {
					$opt .= ' selected="selected"';
				}
				$opt .= '>' . htmlspecialchars($opt_label) . "</option>";
				$list[] = $opt;
			}
			
			// add the options to the xhtml
			$xhtml .= implode("\n\t", $list);
			
			// finish up
			$xhtml .= "\n</select>";
			
		}
		
		return $xhtml;
	}
}
?>