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
* @version $Id: Savant3_Plugin_formRadio.php,v 1.3 2005/08/12 19:29:39 pmjones Exp $
* 
*/

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

class Savant3_Plugin_formRadio extends Savant3_Plugin {
	
	
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
		// are we pulling the pieces from a Solar_Form array?
		$arg = func_get_arg(0);
		if (is_array($arg)) {
			// merge and extract variables.
			$default = array(
				'name'    => null,
				'value'   => null,
				'attribs' => null,
				'options' => null,
				'listsep' => "<br />\n",
			);
			$arg = array_merge($default, $arg);
			extract($arg);
			settype($attribs, 'array');
		}
		
		// make sure attribs don't overwrite name and value
		unset($attribs['name']);
		unset($attribs['value']);
		
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
		
		// the array of xhtml output
		$xhtml = array();
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
			$xhtml[] = $radio;
		}
		
		// done!
		return implode($listsep, $xhtml);
	}
}
?>