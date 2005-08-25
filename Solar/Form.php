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
* @author Contributions from Matthew Weier O'Phinney <mweierophinney@gmail.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* Needed for performing pre-filtering.
*/
Solar::loadClass('Solar_Filter');

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
	* Each key corresponds directly with a valid form tag
	* attribute; you can add or remove as you wish.  Note that
	* although 'action' defaults to null, it will be replaced
	* in the constructor with Solar::server('REQUEST_URI').
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
	
	protected $config = array(
		'action'  => null,
		'method'  => 'post',
		'enctype' => 'multipart/form-data',
	);
	
	
	/**
	* 
	* Attributes for the form tag itself.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $attribs = array();
	
	
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
	* The array of pre-filters for the form elements
	* 
	* @var array 
	* 
	* @access protected
	* 
	*/
	
	protected $filter = array();
	
	
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
	* Populated on the first call to submittedValue(), which itself uses
	* Solar::get() or Solar::post(), depending on the value of
	* $this->attribs['method'].
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $submitted = null;
	
	
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
	* or help text.
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
		'descr'    => null,
		'value'    => null,
		'require'  => false,
		'disable'  => false,
		'options'  => array(),
		'listsep'  => null,
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
		$this->attribs = $this->config;
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
		$name = $this->prepareName($name, $array);
		
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
		
		// add filters
		if (array_key_exists('filter', $info)) {
			foreach ( (array) $info['filter'] as $args) {
				$this->filter[$name][] = $args;
			}
		}
		
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
			$name = $this->prepareName($name, $array);
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
	* Populates form elements with either submitted values or the
	* elements passed in $submit.
	* 
	* @access public
	* 
	* @param array $submit The source data array for populating form
	* values as array(name => value); if null, will populate from $_POST
	* or $_GET as determined from the form's 'method' attribute.
	* 
	* @return void
	* 
	*/
	
	public function populate($submit = null)
	{
		// import the submitted values
		if (is_array($submit)) {
			// from an array
			$this->submitted = $submit;
		} elseif (is_object($submit)) {
			// from an object
			$this->submitted = (array) $submit;
		} else {
			// from $_GET or $_POST, per the form method.
			$callback = array('Solar', $this->attribs['method']);
			$this->submitted = call_user_func($callback);
		}
		
		// populate the submitted values into the
		// elements themsevles.
		$this->_populate($this->submitted);
	}
	
	
	/**
	* 
	* Performs validation on each form element.
	* 
	* Updates the feedback keys for each element that fails validation.
	* Values are either pulled from the submitted form or from the array
	* passed in $submit.
	* 
	* @access public
	* 
	* @param array $submit The source data array for populating form
	* values as array(name => info); if null, will populate from $_POST
	* or $_GET as determined from the 'method' attribute.
	* 
	* @return bool True if all elements are valid, false if not.
	* 
	*/
	
	public function validate($submit = null)
	{
		// Populate the form values.
		if (empty($this->submitted)) {
			$this->populate($submit);
		}

		// Loop through each element to filter
		foreach ($this->filter as $name => $filters) {
			$value = $this->elements[$name]['value'];
			$this->elements[$name]['value'] = Solar_Filter::multiple($value, $filters);
		}

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
	* Returns the form element values as an array.
	* 
	* @access public
	* 
	* @return array An associative array of element values.
	* 
	*/
	
	public function values()
	{
		$values = array();
		foreach ($this->elements as $name => $elem) {
			$this->_values($name, $elem['value'], $values);
		}
		return $values;
	}
	
	
	/**
	* 
	* Resets the form object to its originally-configured state.
	* 
	* This clears out all elements, filters, validations, and feedback,
	* as well as all submitted values.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function reset()
	{
		$this->attribs   = $this->config;
		$this->elements  = array();
		$this->filters   = array();
		$this->validate  = array();
		$this->feedback  = null;
		$this->submitted = null;
	}
	
	
	/**
	* 
	* Loads form attributes and elements from an external source.
	* 
	* You can pass an arbitrary number of parameters to this method;
	* all params after the first will be passed as arguments to the
	* fetch() method of the loader class.
	* 
	* The loader class itself must have at least one method, fetch(),
	* that returns an associative array with keys 'attribs' and 
	* 'elements' which contain, respectively, values for $this->attribs
	* and $this->setElements().
	* 
	* Example use:
	* 
	* <code>
	* $form = Solar::object('Solar_Form');
	* $form->load('Solar_Form_Load_Xml', '/path/to/form.xml');
	* </code>
	* 
	* @access public
	* 
	* @param string|object $obj If a string, it is treated as a class
	* name to instantiate with Solar::object(); if an object, it is
	* used as-is.  Either way, the fetch() method of the object will
	* be called to populate this form (via $this->attribs property and
	* the $this->setElements() method).
	* 
	* @return mixed Void on success, or Solar_Error on failure.
	* 
	*/
	
	public function load($obj)
	{
		// if the first param is a string class name
		// try to instantiate it.
		if (is_string($obj)) {
			$obj = Solar::object($obj);
			if (Solar::isError($obj)) {
				return $obj;
			}
		}
		
		// if we *still* don't have an object, or if there's no
		// fetch() method, there's a problem.
		if (! is_object($obj) ||
			! is_callable(array($obj, 'fetch'))) {		
			return $this->error('ERR_LOAD_OBJECT');
		}
		
		// get any additional arguments to pass to the fetch
		// method (after dropping the first param) ...
		$args = func_get_args();
		array_shift($args);
		
		// ... and call the fetch method.
		$info = call_user_func_array(
			array($obj, 'fetch'),
			$args
		);
		
		// did it work?
		if (Solar::isError($info)) {
			return $info;
		}
		
		// yay, it worked!
		// 
		// we don't call reset() because there are
		// sure to be cases when you need to load()
		// more than once to get a full form.
		// 
		// merge the loaded attribs onto the current ones.
		$this->attribs = array_merge(
			$this->attribs,
			$info['attribs']
		);
		
		// add elements, overwriting existing ones (no way
		// around this, I'm afraid).
		$this->setElements($info['elements']);
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
	* @param string $array The array name, if any, into which we place
	* the element.
	* 
	* @return string The prepared element name.
	* 
	*/
	
	protected function prepareName($name, $array = null)
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
	* Adds a pre-filter for an element
	* 
	* Adds a pre-filter for an element. All pre-filters are applied via 
	* {@link Solar_Filter::multiple()} and should conform to the 
	* specifications for that method.
	* 
	* @access protected
	* 
	* @param string $name The element name.
	* 
	* @param string $method Solar_Filter method or PHP function to use
	* for filtering.
	* 
	* @return void
	* 
	*/
	
	protected function addFilter($name, $method) 
	{
		// Get the arguments, drop the element name
		$args = func_get_args();
		array_shift($args);

		$this->filter[$name][] = $args;
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
	
	protected function addValidate($name, $method, $message = null)
	{
		// get the arguments, drop the element name
		$args = func_get_args();
		$name = array_shift($args);
		
		// add a default validation message (args[0] is the method,
		// args[1] is the message)
		if (trim($args[1]) == '') {
			
			// see if we have an method-specific validation message
			$key = 'VALID_' . strtoupper($method);
			$args[1] = Solar::locale('Solar', $key);
			
			// if the message is the same as the key,
			// there was no method-specific validation
			// message.  revert to the generic default.
			if ($key == $args[1]) {
				$args[1] = Solar::locale('Solar', 'ERR_INVALID');
			}
		}
		
		// add to the validation array
		$this->validate[$name][] = $args;
	}
	
	
	/**
	* 
	* Recursive method to map the submitted values into elements.
	* 
	* @access public
	* 
	* @param array|string $src The source data populating form
	* values.  If an array, will recursively descend into the array;
	* if a scalar, will map the value into a form element.
	* 
	* @param string $elem The name of the current element mapped from
	* the array of submitted values.  E.g., $src['foo']['bar']['baz']
	* maps to "foo[bar][baz]".
	* 
	* @return void
	* 
	*/
	
	protected function _populate($src, $elem = null)
	{
		// are we working with an array?
		if (is_array($src)) {
			// yes, descend through each of the sub-elements.
			foreach ($src as $key => $val) {
				$sub = empty($elem) ? $key : $elem . "[$key]";
				$this->_populate($val, $sub);
			}
		} else {
			// populate an element value, but only if it exists.
			if (isset($this->elements[$elem])) {
				$this->elements[$elem]['value'] = $src;
			}
		}
	}
	
	
	/**
	* 
	* Recursively pulls values from elements into an associative array.
	* 
	* @access protected
	* 
	* @param string $key The current array key for the values array.  If
	* this has square brackets in it, that's a sign we need to keep creating
	* sub-elements for the values array.
	* 
	* @param mixed $val The element value to put into the values array, once
	* we stop creating sub-elements based on the element name.
	* 
	* @param array &$values The values array into which we will place the
	* element value.  Note that it becomes a reference to sub-elements as
	* the recursive function creates new sub-elements based on the form
	* element name.
	* 
	* @return void
	* 
	*/
	
	function _values($key, $val, &$values)
	{
		if (strpos($key, '[') === false) {
		
			// no '[' in the key, so we're at the end
			// of any recursive descent; capture the value.
			if (empty($key)) {
				// handles elements named as auto-append arrays '[]'
				$values[] = $val;
			} else {
				$values[$key] = $val;
			}
			return;
			
		} else {
		
			// recursively parse the element name ($key) to create an
			// array-key for its value.
			// 
			// $key is something like "foo[bar][baz]".
			// 
			// 0123456789012
			// foo[bar][baz]
			// 
			// find the first '['.
			$pos = strpos($key, '[');
			
			// the part before the '[' is the new value key
			$new = substr($key, 0, $pos);
			
			// the part after the '[' still needs to be processed
			$key = substr($key, $pos+1);
			
			// create $values['foo'] if it does not exist.
			if (! isset($values[$new])) {
				$values[$new] = null;
			}
			
			// now $key is something like "bar][baz]".
			// 
			// 012345678
			// bar][baz]
			// 
			// remove the first remaining ']'.  this should leave us
			// with 'bar[baz]'.
			$pos = strpos($key, ']');
			$key = substr_replace($key, '', $pos, 1);
			
			
			// continue to descend,
			// but relative to the new value array.
			$this->_values($key, $val, $values[$new]);
		}
	}
}
?>