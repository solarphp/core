<?php

/**
* 
* Static methods for validating data.
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
		$expr = '/^[a-zA-Z]+$/';
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
		return self::alnum($value, $blank);
	}
	
	public static function alnum($value, $blank = self::NOT_BLANK)
	{
		$expr = '/^[a-zA-Z0-9]+$/'; 
		return self::regex($value, $expr, $blank);
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
	* @return bool True if valid, false if not.
	* 
	* @see call_user_func_array()
	* 
	*/
	
	public static function custom($value, $callback)
	{
		// keep all arguments so we can pass extras to the callback
		$args = func_get_args();
		// drop the value and the callback from the arglist
		array_shift($args);
		array_shift($args);
		// put the value back at the top of the argument list
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
		$expr = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';
		return self::regex($value, $expr, $blank);
	}
	
	
	/**
	* 
	* Validate that a value is a key in the list of of allowed options.
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
	
	public static function inKeys($value, $array, $blank = self::NOT_BLANK)
	{
		if ($blank && self::blank($value)) {
			return true;
		}
		
		return array_key_exists($value, (array) $array);
	}
	
	
	/**
	* 
	* Validate that a value is in a list of allowed options.
	* 
	* Strict checking is enforced, so a string "1" is not the same as
	* an integer 1.  This helps to avoid matching 0 and empty, etc.
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
		
		return in_array($value, (array) $array, true);
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
		// allowed blank?
		if ($blank && self::blank($value)) {
			return true;
		}
		
		// scope has to be smaller than size.
		// both size and scope have to be positive numbers.
		if ($size < $scope || $size < 0 || $scope < 0 ||
			! is_numeric($size) || ! is_numeric($scope)) {
			return false;
		}
		
		// value must be only numeric
		if (! is_numeric($value)) {
			return false;
		}
		
		// drop trailing and leading zeroes
		$value = (float) $value;
		
		// test the size (whole + decimal) and scope (decimal only).
		// does not include signs (+/-) or the decimal point itself.
		// 
		// use the @ signs in strlen() checks to suppress errors
		// when the match-element doesn't exist.
		$expr = "/^(\-)?([0-9]+)?((\.)([0-9]+))?$/";
		if (preg_match($expr, $value, $match) &&
			@strlen($match[2] . $match[5]) <= $size &&
			@strlen($match[5]) <= $scope) {
			return true;
		} else {
			return false;
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
		$expr = '/^[\+\-]?[0-9]+$/';
		return self::regex($value, $expr, $blank);
	}
	
	
	/**
	* 
	* Validate that a value is a legal IPv4 address.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public function ipv4($value, $blank = self::NOT_BLANK)
	{
		// from http://www.regular-expressions.info/examples.html
		$expr = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
		return self::regex($value, $expr, $blank);
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
		$expr = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/';
		
		// validate
		if (preg_match($expr, $value, $match) &&
			checkdate($match[2], $match[3], $match[1])) {
			return true;
		} else {
			return false;
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
		
		// basic timestamp format (19 chars long)
		// yyyy-mm-ddThh:ii:ss
		// 0123456789012345678
		// get the individual portions
		$date = substr($value, 0, 10);
		$sep = substr($value, 10, 1);
		$time = substr($value, 11, 8);
		
		//echo "'$date' '$sep' '$time'\n";
		// now validate each portion
		if (strlen($value) == 19 &&
			self::isoDate($date) &&
			$sep == 'T' &&
			self::isoTime($time)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	* 
	* Validate that a value is an ISO 8601 time string (hh:ii::ss format).
	* 
	* Per note from Chris Drozdowski about ISO 8601, allows two midnight
	* times ... 00:00:00 for the beginning of the day, and 24:00:00 for
	* the end of the day.
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
		$expr = '/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/';
		return self::regex($value, $expr, $blank) || ($value == '24:00:00');
	}
	
	
	/**
	* 
	* Validate that a value is a locale code.
	* 
	* The format is two lower-case letters, an underscore, and two upper-case
	* letters.
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function locale($value, $blank = self::NOT_BLANK)
	{
		$expr = '/^[a-z]{2}_[A-Z]{2}$/';
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
		// reverse the blank-check so that empties are not
		// treated as zero.
		if (! $blank && self::blank($value)) {
			return false;
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
		// reverse the blank-check so that empties are not
		// treated as zero.
		if (! $blank && self::blank($value)) {
			return false;
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
	* <code>
	* Solar::loadClass('Solar_Valid');
	*
	* $validations = array(
	*     array('maxLength', 12),
	*     array('regex', '/^\w+$/', Solar_Valid::OR_BLANK),
	* );
	* 
	* // this will be valid
	* $valid = Solar_Valid::multiple('something', $validations);
	* 
	* // this will not be valid (too long)
	* $valid = Solar_Valid::multiple('somethingelse', $validations);
	* 
	* // this will not be valid (non-word character)
	* $valid = Solar_Valid::multiple('some~thing', $validations);
	* 
	* // this will be valid (not too long, and OR_BLANK)
	* $valid = Solar_Valid::multiple('', $validations);
	* </code>
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @param array $validations A sequential array of validations; each
	* element can be a string method name, or an array where element 0 is
	* the string method name and elements 1-N is are the arguments for
	* that method.  The method must be a Solar_Valid method.
	* 
	* @return bool True if the value passes all validations, false if not.
	* 
	*/
	
	public static function multiple($value, $validations)
	{
		// loop through all the requested validations
		settype($validations, 'array');
		foreach ($validations as $params) {
			
			// the first element is the method name
			settype($params, 'array');
			$method = array_shift($params);
			
			// put the value at the top of the remaining parameters.
			array_unshift($params, $value);
			
			// call the validation method
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
	* Validate that a value is not exactly zero.
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
		// allowed blank?
		if ($blank && self::blank($value)) {
			return true;
		}
		
		$expr = '/^0+$/';
		return ! self::regex($value, $expr);
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
		
		return (bool) preg_match($expr, $value);
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
	* https, ftp.  If null, any scheme at all is allowed.
	* 
	* @return bool True if the value is a URI and is one of the allowed
	* schemes, false if not.
	* 
	*/
	
	public static function uri($value, $schemes = null, $blank = self::NOT_BLANK)
	{
		// allow blankness?
		if ($blank && self::blank($value)) {
			return true;
		}
		
		// validate the general format. regex from PEAR Valid.
		$expr = '!^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?!';
		$result = preg_match($expr, $value, $matches);
		
		// was it formatted as a URI?
		if ($result && ! empty($schemes)) {
			// yes, now check against the allowed schemes.
			settype($schemes, 'array');
			$scheme = $matches[2];
			$result = in_array($scheme, (array) $schemes);
		}
		
		return (bool) $result;
	}
	
	
	/**
	* 
	* Validate that a value is composed only of "word" characters.
	* 
	* These include a-z, A-Z, 0-9, and underscore, indicated by a 
	* regular expression "\w".
	* 
	* @access public
	* 
	* @param mixed $value The value to validate.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	public static function word($value, $blank = self::NOT_BLANK)
	{
		$expr = '/^\w+$/';
		return self::regex($value, $expr);
	}
}
?>