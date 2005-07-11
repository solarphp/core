<?php

/**
* 
* Class for hinting how to build forms.
* 
* @category Solar
* 
* @package Solar
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* Needed for performing validations.
*/
Solar::loadClass('Solar_Valid');

/**
* 
* Class for hinting how to build forms.
* 
* This is technically a pseudo-form class.   It stores information for how
* to build a form, but does not perform output.  Will validate data
* in the form and generate feedback messages.
* 
* Built for use primarly with multiple Solar_Sql_Entity extended classes.
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_Form extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration.
	* 
	* Each key corresponds directly with a valid <form> tag
	* attribute; you can add or remove as you wish.  Note that
	* although 'action' defaults to null, it will be replaced
	* in the constructor with $_SERVER['REQUEST_URI'].
	* 
	* Keys are:
	* 
	* action => (string) The form action attribute; defaults to null,
	* which is treated as $_SERVER['REQUEST_URI'].
	* 
	* method => (string) The form method attribute; defaults to 'post'.
	* 
	* enctype => (string) The form encoding type; defaults to
	* 'multipart/form-data'.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'action'  => null,
		'method'  => 'post',
		'enctype' => 'multipart/form-data',
	);
	
	
	/**
	* 
	* The array of elements in this form.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $elements = array();
	
	
	/**
	* 
	* Overall feedback about the state of the form.
	* 
	* E.g., "Saved successfully." or "Please correct the noted errors."
	* 
	* If you like, you can set this to an array and add multiple
	* feeback messages.
	* 
	* @access public
	* 
	* @var string|array
	* 
	*/
	
	public $feedback = null;
	
	
	/**
	* 
	* The array of validations for the form elements.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $validate = array();
	
	
	/**
	* 
	* Array of submitted values.
	* 
	* Populated on the first call to submitValue(), which itself uses
	* Solar::get() or Solar::post(), depending on the value of
	* $this->config['method'].
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $submit = null;
	
	
	/**
	* 
	* Default values for each element.
	* 
	* Keys are:
	* 
	* name => (string) The name attribute.
	* 
	* type => (string) The input or type attribute ('text', 'select', etc).
	* 
	* label => (string) A short label for the element.
	* 
	* value => (string) The default or selected value(s) for the element.
	* 
	* descr => (string) A longer description of the element, e.g. a tooltip
	* or help value.
	* 
	* require => (bool) Whether or not the element is required.
	* 
	* disable => (bool) If disabled, the element is read-only (but is still
	* submitted with other elements).
	* 
	* options => (array) The list of allowed values as options for this element
	* as an associative array in the form (value => label).
	* 
	* attribs => (array) Additional XHTML attributes for the element in the
	* form (attribute => value).
	* 
	* feedback => (array) An array of feedback messages for this element,
	* generally based on validation of previous user input.
	* 
	
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $default = array(
		'name'     => null,
		'type'     => null,
		'label'    => null,
		'value'    => null,
		'descr'    => null,
		'require'  => false,
		'disable'  => false,
		'options'  => array(),
		'attribs'  => array(),
		'feedback' => array(),
	);
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	*/
	
	public function __construct($config = null)
	{
		$this->config['action'] = Solar::server('REQUEST_URI');
		parent::__construct($config);
	}
	
	
	/**
	* 
	* Sets one element in the form.  Appends if element does not exist.
	* 
	* @access public
	* 
	* @param string $name The element name to set or add; overrides
	* $info['name'].
	* 
	* @param array $info Element information.
	* 
	* @param string $array Rename the element as a key in this array.
	* 
	* @return void
	* 
	*/
	
	public function setElement($name, $info, $array = null)
	{
		// prepare the name as an array key?
		$name = $this->prepName($name, $array);
		
		// prepare the element info
		$info = array_merge($this->default, $info);
		
		// forcibly cast each of the keys into the elements array
		$this->elements[$name] = array (
			'name'     =>          $name,
			'type'     => (string) $info['type'],
			'label'    => (string) $info['label'],
			'value'    =>          $info['value'], // mixed
			'descr'    => (string) $info['descr'],
			'require'  => (bool)   $info['require'],
			'disable'  => (bool)   $info['disable'],
			'options'  => (array)  $info['options'],
			'attribs'  => (array)  $info['attribs'],
			'feedback' => (array)  $info['feedback'],
		);
		
		// add validations
		if (array_key_exists('validate', $info)) {
			foreach ( (array) $info['validate'] as $args) {
				// shift the name onto the top of the args
				array_unshift($args, $name);
				// add the validation
				call_user_func_array(
					array($this, 'addValidate'),
					$args
				);
			}
		}
	}
	
	
	/**
	* 
	* Adds multiple feedback messages to elements.
	* 
	* @access public
	* 
	* @param array $list An associative array where the key is an element
	* name and the value is a string or sequential array of feedback messages.
	* 
	* @param string $array Rename each element as a key in this array.
	* 
	*/
	
	public function addFeedback($list, $array = null)
	{
		foreach ($list as $name => $feedback) {
			$name = $this->prepName($name, $array);
			settype($feedback, 'array');
			foreach ($feedback as $text) {
				$this->elements[$name]['feedback'][] = $text;
			}
		}
	}
	
	
	/**
	* 
	* Sets multiple elements in the form.  Appends if they do not exist.
	* 
	* @access public
	* 
	* @param array $list Element information as array(name => info).
	* 
	* @param string $array Rename the element as a key in this array.
	* 
	* @return void
	* 
	*/
	
	public function setElements($list, $array = null)
	{
		foreach ($list as $name => $info) {
			$this->setElement($name, $info, $array);
		}
	}
	
	
	/**
	* 
	* Populates form elements with submitted values.
	* 
	* @access public
	* 
	* @param array $list Element information as array(name => info).
	* 
	* @param string $array Rename the element as a key in this array.
	* 
	* @return void
	* 
	*/
	
	public function populate()
	{
		foreach ($this->elements as $name => $info) {
			// override the initial value with a submitted value, 
			// if one exists
			$value = $this->submitValue($name);
			if (! is_null($value)) {
				$this->elements[$name]['value'] = $value;
			}
		}
	}
	
	
	/**
	* 
	* Performs validation on each form element.
	* 
	* Updates the feedback keys for each element that fails validation.
	* 
	* @access public
	* 
	* @return bool True if all elements are valid, false if not.
	* 
	*/
	
	public function validate()
	{
		$validated = true;
		
		// loop through each element to be validated
		foreach ($this->validate as $name => $list) {
			
			// loop through each validation for the element
			foreach ($list as $args) {
				
				// the name of the Solar_Valid method
				$method = array_shift($args);
				
				// the text of the error message
				$feedback = array_shift($args);
				
				// config is now the remaining arguments,
				// put the value on top of it.
				array_unshift($args, $this->elements[$name]['value']);
				
				// call the appropriate Solar_Valid method
				$result = call_user_func_array(
					array('Solar_Valid', $method),
					$args
				);
				
				// was it valid?
				if (! $result) {
					// no, add the feedback message
					$validated = false;
					$this->elements[$name]['feedback'][] = $feedback;
				}
				
			} // inner loop of validations
			
		} // outer loop of elements
		
		return $validated;
	}
	
	
	/**
	* 
	* Returns the form element values.
	* 
	* @access public
	* 
	* @return array An associative array of element values.
	* 
	*/
	
	public function values()
	{
		// the values to be returned
		$values = array();
		
		// loop through each of the values
		foreach ($this->elements as $name => $elem) {
			
			if (strpos($name, '[') === false) {
				
				// no brackets in the name, so it's a plain variable
				$values[$name] = $elem['value'];
			
			} else {
			
				// there are brackets in the name. convert to an array
				// element. taken from PEAR/HTML/QuickForm/element.php.
				// 
				// this converts, e.g., "arrayname[key1][key2]" to
				// "arrayname']['key1']['key2".  the opening and closing
				// brackets and quotes will be added when we build the
				// PHP command.
				$path = str_replace(
					array(']', '['),
					array('', "']['"),
					$name
				);
				
				// evaluate a PHP command that sets the array path key
				// to the element value.  evil, slow, ugly hack.
				eval("\$values['" . $path . "'] = \$elem['value'];");
			}
		}
		
		return $values;
	}
	
	
	// -----------------------------------------------------------------
	//
	// Support methods.
	//
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Prepares a name as an array key, if needed.
	* 
	* @access protected
	* 
	* @param string $name The element name.
	* 
	* @param string $array The array name, if any, into which the place the element.
	* 
	* @return string The prepared element name.
	* 
	*/
	
	protected function prepName($name, $array = null)
	{
		if ($array) {
			$pos = strpos($name, '[');
			if ($pos === false) {
				// name is not itself an array.
				// e.g., 'field' becomes 'array[field]'
				$name = $array . "[$name]";
			} else {
				// the name already has array keys, e.g.
				// 'field[0]'. make the name just another key
				// in the array, e.g. 'array[field][0]'.
				$name = $array . '[' .
					substr($name, 0, $pos) . ']' .
					substr($name, $pos);
			}
		}
		return $name;
	}
	
	
	/**
	* 
	* Adds a Solar_Valid method callback as a validation for an element.
	* 
	* @access protected
	* 
	* @param string $name The element name.
	* 
	* @param string $method The Solar_Valid callback method.
	* 
	* @param string $message The message to use if validation fails.
	* 
	* @return void
	* 
	*/
	
	protected function addValidate($name, $method, $message)
	{
		// get the arguments, drop the element name
		$args = func_get_args();
		$name = array_shift($args);
		
		// add a default validation message (args[0] is the method)
		if (trim($args[1]) == '') {
			$args[1] = Solar::locale('Solar', 'ERR_INVALID');
		}
		
		// add to the validation array
		$this->validate[$name][] = $args;
	}
	
	
	/**
	* 
	* Returns the submitted value for a named element.
	* 
	* @access public
	* 
	* @param string $name The element name from the form.
	* 
	* @return mixed The submitted value for a named element.
	* 
	*/
	
	protected function submitValue($name)
	{
		// do we have to retrieve the submitted values?
		// (only need to do it once.)
		if (is_null($this->submit)) {
			// $this->config['method'] should be 'get' or 'post'
			$callback = array('Solar', $this->config['method']);
			// get all submitted values via Solar::get or Solar::post
			$this->submit = call_user_func($callback);
		}
		
		// get the related value from the $submit array
		if (strpos($name, '[') === false) {
		
			// no brackets in the name, so it's a plain variable
			$value = isset($this->submit[$name]) ? $this->submit[$name] : null;
		
		} else {
				
			// there are brackets in the name. convert to an array
			// element. taken from HTML_QuickForm, element.php.
			
			// this converts, e.g., "arrayname[key1][key2]" to
			// "arrayname']['key1']['key2".  the opening and closing
			// brackets and quotes will be added when we build the
			// PHP command.
			$path = str_replace(
				array(']', '['),
				array('', "']['"),
				$name
			);
			
			// evaluate a PHP command that sets value. slow ugly eval() hack.
			$tmp = "\$this->submit['" . $path . "']";
			eval("\$value = isset($tmp) ? $tmp : null;");
		}
		
		return $value;
	}
}
?>