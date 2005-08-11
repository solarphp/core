<?php

/**
* 
* Plugin to generate a set of radio button elements.
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_form_radio.php,v 1.2 2005/08/09 21:54:24 pmjones Exp $
* 
*/

/**
* 
* Plugin to generate a set of radio button elements.
* 
* @package Savant3
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_form_radio extends Savant3_Plugin {
	
	
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
	
	public function form_radio($name, $value = null, $attribs = null, 
		$options = null, $listsep = '<br />')
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
				'listsep' => '<br />',
			);
			$arg = array_merge($default, $arg);
			extract($arg);
			settype($attribs, 'array');
			unset($attribs['name']);
			unset($attribs['value']);
		}
		
		// the radio button values and labels
		settype($options, 'array');
		
		// the array of xhtml output
		$xhtml = array();
		foreach ($options as $opt_value => $opt_label) {
			$radio = '<label style="white-space: nowrap;">';
			$radio .= '<input type="radio"';
			
			if (! empty($name)) {
				$radio .= ' name="' . htmlspecialchars($name) . '"';
			}
			
			$radio .= ' value="' . htmlspecialchars($opt_value) . '"';
			
			if ($opt_value == $value) {
				$radio .= ' checked="checked"';
			}
			
			$radio .= ' />' . htmlspecialchars($opt_label) . '</label>';
			$xhtml[] = $radio;
		}
		
		// done!
		return implode($listsep, $xhtml);
	}
}
?>