<?php

/**
* 
* Plugin to generate 'select' list of options.
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_form_select.php,v 1.2 2005/08/09 21:54:24 pmjones Exp $
* 
*/

/**
* 
* Plugin to generate 'select' list of options.
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_form_select extends Savant3_Plugin {
	
	
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
	* @return string The select tag and options XHTML.
	* 
	*/
	
	public function form_select($name, $value = null, $attribs = null,
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
			);
			$arg = array_merge($default, $arg);
			extract($arg);
			settype($attribs, 'array');
			unset($attribs['name']);
			unset($attribs['value']);
		}
		
		// force $value to array so we can compare multiple values
		// to multiple options.
		settype($value, 'array');
		
		/** @todo Auto-determine if this is a 'multiple' select */
		
		// now start building the XHTML
		$xhtml = '';
		
		// the surrounding select element
		$xhtml .= '<select';
		
				
		if (! empty($name)) {
			$xhtml .= ' name="' . htmlspecialchars($name) . '"';
		}
		
		$xhtml .= $this->Savant->html_attribs($attribs);
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
		return $xhtml;
	}
}
?>