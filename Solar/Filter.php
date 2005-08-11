<?php
/**
* 
* Static methods for filtering data.
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
* 
* Static methods for filtering data.
* 
* An OOP container for applying filters to data, e.g., form elements.
* Filters are typically callbacks that accept either a single value as
* an argument (and return a transformed version of that value), or a
* value to transform as the first argument and one or more additional
* parameters.
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_Filter {
	
	
	/**
	* 
	* Applies a regex replacement filter.
	* 
	* Applies a preg_replace() to a value. The first element in $info
	* should be the regex, the second the values that will replace those
	* in the regex. If arguments are missing, the original string is
	* returned without transformations.
	* 
	* @access public
	* 
	* @param mixed $value The value to be filtered.
	* 
	* @param string The regex pattern to apply.
	* 
	* @param string Replace the found pattern with this string.
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function replace($value, $pattern, $replacement)
	{
		$final = @preg_replace($pattern, $replacement, $value);
		return $final;
	}
	
	
	/**
	* 
	* Removes non-alphabetic characters.
	* 
	* @access public
	* 
	* @param mixed $value The value to be filtered.
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function alpha($value)
	{
		return @preg_replace('/[^a-z]/i', '', $value);
	}
	
	
	/**
	* 
	* Removes non-alphabetic and non-numeric characters.
	* 
	* @access public
	* 
	* @param mixed $value The value to be filtered.
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function alnum($value)
	{
		return @preg_replace('/[^a-z0-9]/i', '', $value);
	}
	
	
	/**
	* 
	* Removes non-alphabetic and non-numeric characters.
	* 
	* @access public
	* 
	* @param mixed $value The value to be filtered.
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function alphanumeric($value)
	{
		return $this->alnum($value);
	}
	
	
	/**
	* 
	* Removes all whitespace characters.
	* 
	* @access public
	* 
	* @param mixed $value The value to be filtered.
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function blank($value)
	{
		return @preg_replace('/\s/', '', $value);
	}
	
	
	/**
	* 
	* Forces a value to a date() format.
	* 
	* Takes a string value time and formats it according to $format.
	* PHP's date() function is used to create the new value, and $format
	* should be a format that works with that function. The value should
	* be a format that strtotime() understands.
	* 
	* @access public
	* 
	* @param string $value The value to be filtered; must be a value
	* appropriate for strtotime().
	* 
	* @param string A format string appropriate for date().
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function formatDateTime($value, $format)
	{
		return @date($format, strtotime($value));
	}
	
	
	/**
	* 
	* Forces a value to an ISO-standard date string.
	* 
	* @access public
	* 
	* @param string $value The value to be filtered; must be a value
	* appropriate for strtotime().
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function isoDate($value)
	{
		return @date('Y-m-d', strtotime($value));
	}
	
	
	/**
	* 
	* Forces a value to an ISO-standard date-time string.
	* 
	* @access public
	* 
	* @param string $value The value to be filtered; must be a value
	* appropriate for strtotime().
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function isoDateTime($value)
	{
		return @date('Y-m-dTH:i:s', strtotime($value));
	}
	
	
	/**
	* 
	* Forces a value to an ISO-standard time string.
	* 
	* @access public
	* 
	* @param string $value The value to be filtered; must be a value
	* appropriate for strtotime().
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function isoTime($value)
	{
		return @date('H:i:s', strtotime($value));
	}
	
	
	/**
	* 
	* Removes non-numeric characters.
	* 
	* @access public
	* 
	* @param mixed $value The value to be filtered.
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function numeric($value)
	{
		return @preg_replace('/\D/', '', $value);
	}
	
	
	/**
	* 
	* Filter: cast a value as a type
	* 
	* Casts a value as a specific type. $type should be a valid variable type:
	* 'array', 'string', 'int', 'float', 'double', 'real', or 'bool'.
	* 
	* @access public
	* 
	* @param mixed $value The value to filter.
	* 
	* @param string $type A valid variable type: 'array', 'string',
	* 'int', 'float', 'double', 'real', or 'bool'
	* 
	* @return mixed The filtered value.
	* 
	*/
	
	public static function cast($value, $type)
	{
		switch (strtolower(strval($type))) {
		
		case 'array':
			settype($value, 'array');
			break;
			
		case 'bool':
		case 'boolean':
			$value = empty($value) ? false : true;
			break;
			
		case 'double':
		case 'float':
		case 'real':
			$value = floatval($value);
			break;
			
		case 'int':
		case 'integer':
			$value = intval($value);
			break;
			
		case 'string':
			$value = strval($value);
			break;
			
		default:
			break;
			
		}
		
		return $value;
	}
	
	
	/**
	* 
	* Uses a callback to filter a value.
	* 
	* Uses a callback to filter a value. If $callback is uncallable,
	* returns the $value untransformed.  Allows arbitrary parameters to
	* be passed to the callback function, as long as the value to be
	* filtered is the first parameter of the callback.
	* 
	* @access public
	* 
	* @param mixed $value The value to be filtered.
	* 
	* @param mixed $callback A callback value suitable for call_user_func().
	* 
	* @return string The filtered value.
	* 
	*/
	
	public static function custom($value, $callback)
	{
		// If callback isn't callable, then bail
		if (! is_callable($callback)) {
			return $value;
		}
		
		// Get the additional arguments, and stick the $value onto the
		// beginning of them
		$args = func_get_args();
		array_shift($args); // drops $value
		array_shift($args); // drops $callback
		array_unshift($args, $value);
		
		// apply the callback
		return call_user_func_array($callback, $args);
	}
	
	
	/**
	* 
	* Applies multiple filters to a value.
	* 
	* Applies multiple filters to a value. $filters should be an array of
	* arrays, with each subarray consisting of at least one element. The first
	* element should be the filter type; this is the name of a valid
	* Solar_Filter method <b>OR</b> a PHP function. 
	* 
	* If the filter type is {@link custom()}, then the second argument should
	* be a valid callback. 
	* 
	* Any additional elements in the subarray will be passed as additional
	* arguments to the filter (the first argument to a filter function/method
	* is always the value being filtered).
	* 
	* Returns the transformed value after applying all filters.
	* 
	* @access public
	* 
	* @param mixed $value The value to be filtered.
	* 
	* @param array $filters The filters to apply.
	* 
	* @return mixed The filtered value.
	* 
	*/
	
	public static function multiple($value, $filters)
	{
		// No filters found, invalid filters provided, etc -- return the
		// value
		if (! is_array($filters)) {
			return $value;
		}
		
		foreach ($filters as $params) {
			if (! is_array($params) && ! is_string($params)) {
				continue;
			}
			
			// Get the method name
			if (is_string($params)) {
				$method = $params;
				$params = array();
			} else {
				$method = array_shift($params);
			}
			
			// Get the callback
			$callback = false;
			if ('custom' == $method) {
				// Custom callback; check for availability
				$callback = array_shift($params);
				if (! is_callable($callback)) {
					$callback = false;
				}
			} elseif (is_callable(array('self', $method))) {
				// Solar_Filter method name, takes precedence over
				// native PHP functions.
				$callback = array('self', $method);
			} elseif (function_exists($method)) {
				// Function callback
				$callback = $method;
			}

			// Apply the filter callback
			if ($callback) {
				// Place the value at the beginning of the parameters
				array_unshift($params, $value);
				$value = call_user_func_array($callback, $params);
			}
		}
		
		return $value;
	}
}
?>