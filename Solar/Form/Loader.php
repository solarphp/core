<?php

/**
* 
* Class for loading form definitions from a file.
* 
* @category Solar
* 
* @package Solar
* 
* @author Matthew Weier O'Phinney <mweierophinney@gmail.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* Need Solar_Form for instanceof comparisons.
*/
Solar::loadClass('Solar_Form');

/**
* 
* Class for loading Solar_Form definitions.
* 
* This class loads Solar_Form element definitions. Initially, it only
* loads them from a SimpleXML file, but it could easily be extended to
* grab definitions from a database or other configuration storage.
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_Form_Loader extends Solar_Form {

	/**
	* 
	* Loads a Solar_Form definition from an XML file.
	* 
	* Loads a Solar_Form definition from an XML file using PHP5's
	* SimpleXML functions.
	* 
	* The location of a form definition file is required. You may
	* optionally pass it a Solar_Form object to which elements will be
	* added.  A Solar_Form object will be returned.
	* 
	* @static
	* 
	* @access public
	* 
	* @param string $filename Path to form definition file.
	* 
	* @param object $form Optional Solar_Form object.
	* 
	* @return object Solar_Form object.
	*
	*/
	
	public static function fromXml($filename, $form = null) 
	{
		$args     = func_get_args();
		$filename = array_shift($args);
		$param    = array_shift($args);

		if (! file_exists($filename)) {
			// Need to return an error here
			return;
		}

		// Initialize form object based on the second argument.
		if (is_array($param)) {
			// the second arg was an array, treat as a config
			// array for a new Solar_Form object
			$form = Solar::object('Solar_Form', $param);
		} elseif ($param instanceof Solar_Form) {
			// the second arg was itself a Solar_Form object
			$form = $param;
		} else {
			// no useful second arg, create a new Solar_Form
			$form = Solar::object('Solar_Form');
		}
		
		// load the XML file data
		$xml = simplexml_load_file($filename);
		if (false === $xml) {
			// return an error here
			return;
		}
		
		// loop through the XML file data elements
		foreach ($xml->element as $element) {
			
			// skip empty element names ...
			if (empty($element->name)) {
				continue;
			}
			
			// ... otherwise get element information.
			$name  = (string) $element->name;
			$info = array(
				'type'     => (string) $element->type,
				'label'    => (string) $element->label,
				'value'    => (string) $element->value,
				'descr'    => (string) $element->descr,
				'require'  => (bool)   $element->require,
				'disable'  => (bool)   $element->disable,
				'options'  => (array)  $element->options,
				'attribs'  => (array)  $element->attribs,
				'feedback' => (array)  $element->feedback,
			);
			$form->setElement($name, $info);

			// Get element filters
			if (! empty($element->filters)) {
				
				foreach ($element->filters->filter as $filter) {
					
					if (empty($filter->method)) {
						$method = (string) $filter;
					} else {
						$method	= (string) $filter->method;
					}

					$argType   = 'args';
					$args      = array();
					$tmpFilter = array($name, $method);

					// Were any arguments passed?
					if (! empty($filter->args)) {
						// Get the argument type
						// 'args' means arguments will be passed individually
						// 'array' means arguments are passed as an array
						// Anything other than 'array' will be ignored.
						if (! empty($filter->argType)) {
							$argType = (string) $filter->argType;
						}
						
						// Get arguments
						foreach ($filter->args->arg as $arg) {
							array_push($args, (string) $arg);
						}
						
						// If argType == array, then we're passing an array to
						// the filter
						if ('array' == $argType) {
							$args = array($args);
						}
						
						// Add arguments to filter array
						foreach ($args as $arg) {
							array_push($tmpFilter, $arg);
						}
					}
					
					// Add filter to element
					call_user_func_array(array($form, 'addFilter'), $tmpFilter);
				}
			}
			
			// Get element validations
			if (! empty($element->validate)) {
				foreach ($element->validate->rule as $rule) {
					
					if (empty($rule->method)) {
						continue;
					}
					
					$method  = (string) $rule->method;
					$message = '';
					if (! empty($rule->message)) {
						$message = (string) $rule->message;
					}
					
					$argType = 'args';
					$args    = array();
					$tmpRule = array($name, $method, $message);

					// Were any arguments passed?
					if (! empty($rule->args)) {
						// Get the argument type
						// 'args' means arguments will be passed individually
						// 'array' means arguments are passed as an array
						// Anything other than 'array' will be ignored.
						if (! empty($rule->argType)) {
							$argType = (string) $rule->argType;
						}
						
						// Get arguments
						foreach ($rule->args->arg as $arg) {
							array_push($args, (string) $arg);
						}
						
						// If argType == array, then we're passing an array to
						// the rule
						if ('array' == $argType) {
							$args = array($args);
						}
						
						// Add arguments to rule array
						foreach ($args as $arg) {
							array_push($tmpRule, $arg);
						}
					}

					// Add validation to element
					call_user_func_array(array($form, 'addValidate'), $tmpRule);
				}
			}
		}

		return $form;
	}
}
?>