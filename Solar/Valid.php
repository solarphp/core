<?php

/**
* 
* Static methods for validating data, mostly for the Sql_Entity class.
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
* @todo Try to pull more from regexlib.com site?
* 
*/

/**
* 
* Static methods for validating data.
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_Valid {
	
	/**
	* Flags for allowing validation on a blank value.
	*/
	const OR_BLANK  = true;
	const NOT_BLANK = false;
	
	/**
	* 
	* Validate that a value is letters only (upper or lower case).
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function alpha($value, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		$expr = '/^[a-zA-Z]*$/';
		return self::regex($value, $expr, $blank);
	}
	
	
	/**
	* 
	* Validate that a value is only letters and digits.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function alphanumeric($value, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		$expr = '/^[a-zA-Z0-9]*$/'; 
		return self::regex($value, $expr);
	}
	
	
	/**
	* 
	* Validate that a string is empty when trimmed.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function blank($value)
	{
		return (trim($value) == '');
	}
	
	
	/**
	* 
	* Validate against a custom callback function or method.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param string|array $callback A string or array suitable for use
	* as the first argument to call_user_func_array().
	* 
	* @param string|array $callback Additional arguments to pass to the
	* callback; note that the first argument will always be the value to
	* be validated.
	* 
	* @return bool True if valid, false if not.
	* 
	* @see call_user_func_array()
	* 
	*/
	
	public static function custom($value, $callback, $args)
	{
		// put the value at the top of the argument list
		array_unshift($args, $value);
		// make the callback
		return call_user_func_array($callback, $args);
	}
	
	
	/**
	* 
	* Validate that a value is an email address.
	* 
	* The regular expression in this method was taken from HTML_QuickForm.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function email($value, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		$expr = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';
		return self::regex($value, $expr);
	}
	
	
	/**
	* 
	* Validate that a value is in a list of allowed options.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param array $array An array of allowed options.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function inList($value, $array, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		return in_array($value, (array) $array);
	}
	
	
	/**
	* 
	* See a value has only a certain number of digits and decimals.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param int $size The total number of digits allowed in the value,
	* excluding the negative sign and decimal point.
	* 
	* @param int $scope The maximum number of decimal places.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function inScope($value, $size, $scope, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		// has to be at least a numeric value
		if (! is_numeric($value)) {
			return false;
		}
		
		// maximum number of digits allowed to the left
		// and right of the decimal point.
		$right_max = (int) $scope;
		$left_max = (int) $size - $scope;
		
		// get rid of trailing decimal zeroes
		settype($value, 'float');
		
		// ignore negative signs
		$value = str_replace('-', '', $value);
		
		// find the decimal point, then get the left
		// and right portions.
		$pos = strpos($value, '.');
		if ($pos === false) {
			$left = $value;
			$right = '';
		} else {
			$left = substr($value, 0, $pos);
			$right = substr($value, $pos+1);
		}
		
		// how long are the left and right portions?
		$left_len = strlen($left);
		$right_len = strlen($right);
		
		// do the portions exceed their maxes?
		if ($left_len > $left_max || $right_len > $right_max) {
			return false;
		} else {
			return true;
		}
	}
	
	
	/**
	* 
	* Validate that a value represents an integer (+/-).
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function integer($value, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		$expr = '/^[\+\-]{0,1}[0-9]+$/';
		return self::regex($value, $expr);
	}
	
	
	/**
	* 
	* Validate that a value is an ISO 8601 date string (yyyy-mm-dd format).
	* 
	* Also checks to see that the date itself is valid (e.g., no Feb 30).
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function isoDate($value, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		// basic date format
		// yyyy-mm-dd
		$expr = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
		
		// year, month, and day portions
		$y = (int) substr($value, 0, 4);
		$m = (int) substr($value, 6, 2);
		$d = (int) substr($value, 8, 2);
		
		// validate
		if (strlen($value) != 10 ||
			! preg_match($expr, $value) ||
			! checkdate($m, $d, $y)) {
			return false;
		} else {
			return true;
		}
	}
	
	
	/**
	* 
	* Validate that a value is an ISO 8601 date-time string.
	* 
	* The format is "yyyy-mm-ddThh:ii:ss" (note the literal "T" in the
	* middle, which acts as a separator).
	* 
	* Also checks that the date itself is valid (e.g., no Feb 30).
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function isoDateTime($value, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		// basic timestamp format
		// yyyy-mm-dd hh:ii:ss
		$expr = '/' . 
			'[0-9]{4}-[0-9]{2}-[0-9]{2}' . // date
			'T' . // a capital letter T
			'(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]' . // time
			'/';
		
		// year, month, and day portions
		$y = (int) substr($value, 0, 4);
		$m = (int) substr($value, 6, 2);
		$d = (int) substr($value, 8, 2);
		
		if (strlen($value) != 19 ||
			! preg_match($expr, $value) ||
			! checkdate($m, $d, $y)) {
			return false;
		} else {
			return true;
		}
	}
	
	
	/**
	* 
	* Validate that a value is an ISO 8601 time string (hh:ii::ss format).
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function isoTime($value, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		$expr = '/(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]/';
		return self::regex($value, $expr);
	}
	
	
	/**
	* 
	* Validate that a value is less than than or equal to a maximum.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param mixed $min The maximum valid value.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function max($value, $max, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		return $value <= $max;
	}
	
	
	/**
	* 
	* Validate that a string is no longer than a certain length.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param mixed $min The value must have no more than this many
	* characters.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function maxLength($value, $max, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		return (strlen($value) <= $max);
	}
	
	
	/**
	* 
	* Validate that a value is greater than or equal to a minimum.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param mixed $min The minimum valid value.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function min($value, $min, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		return $value >= $min;
	}
	
	
	/**
	* 
	* Validate that a string is at least a certain length.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param mixed $min The value must have at least this many
	* characters.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function minLength($value, $min, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		return (strlen($value) >= $min);
	}
	
	/**
	* 
	* Check the value against multiple validations.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param array $validations A sequential array of validations; each
	* element can be a string method name, or an array where element 0 is
	* the string method name and element 1 is an array of arguments for
	* that method.  The method must be a Solar_Valid method.
	* 
	* @return bool True if the value passes all validations, false if not.
	* 
	*/
	
	public static function multiple($value, $validations)
	{
		// loop through all the requested validations
		settype($validations, 'array');
		foreach ($validations as $info) {
			
			// element 0 is the method,
			// element 1 is the array of parameters (if any)
			settype($info, 'array');
			$method = $info[0];
			if (! isset($info[1])) {
				$params = array();
			} else {
				$params = (array) $info[1];
			}
			
			// put the value at the top of the params.
			array_unshift($params, $value);
			
			// validate
			$result = call_user_func_array(
				array('self', $method),
				$params
			);
			
			// if it failed, cancel further validation
			if (! $result) {
				return false;
			}
		}
		
		// passed all validations
		return true;
	}
	
	
	/**
	* 
	* Validate that a value is not equal to zero.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function nonZero($value, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		return $value != 0;
	}
	
	
	/**
	* 
	* Validate that a string is not empty when trimmed.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function notBlank($value)
	{
		return (trim($value) != '');
	}
	
	
	/**
	* 
	* Validate a value against a regular expression.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param string $expr The regular expression to validate against.
	* 
	* @return bool True if the value matches the expression, false if not.
	* 
	*/
	
	public static function regex($value, $expr, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		if (preg_match($expr, $value)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	* 
	* Validate a value as a URI per RFC2396.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param array $schemes Allowed schemes for the URI; e.g., http,
	* https, ftp.
	* 
	* @return bool True if the value is a URI, false if not.
	* 
	*/
	
	public static function uri($value, $schemes = null, $blank = self::NOT_BLANK)
	{
		// allow blankness?
		if ($blank && self::blank($value)) {
			return true;
		}
		
		// build a default set of acceptable schemes
		if (is_null($schemes)) {
			$schemes = array(
				'http',
				'https',
				'news',
				'ftp',
				'gopher',
				'mailto'
			);
		}
		
		// validate the general format. regex from PEAR Valid.
		$expr = '!^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?!';
		$result = preg_match($expr, $value, $matches);
		
		// was it formatted as a URI?
		if ($result) {
			// yes, now check against the allowed schemes.
			$scheme = $matches[2];
			$result = in_array($scheme, (array) $schemes);
		}
		
		return (bool) $result;
	}
}
?>