<?php
/**
 * 
 * Methods for validating and sanitizing user input.
 * 
 * @category Solar
 * 
 * @package Solar_DataFilter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Valid.php 2036 2006-12-15 20:21:28Z pmjones $
 * 
 */

/**
 * 
 * Methods for validating and sanitizing user input.
 * 
 * @category Solar
 * 
 * @package Solar_DataFilter
 * 
 */
class Solar_DataFilter extends Solar_Base {
    
    /**
     * 
     * String representations of "true" boolean values.
     * 
     * @var array
     * 
     */
    protected $_true = array('1', 'on', 'true', 't', 'yes', 'y');
    
    /**
     * 
     * String representations of "false" boolean values.
     * 
     * @var array
     * 
     */
    protected $_false = array('0', 'off', 'false', 'f', 'no', 'n', '');
    
    // -----------------------------------------------------------------
    // 
    // Sanitize
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Forces the value to a boolean.
     * 
     * Note that this recognizes $this->_true and $this->_false values.
     * 
     * @param mixed $value The value to sanitize.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return bool The sanitized value.
     * 
     */
    public function sanitizeBoolean($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        // PHP booleans
        if ($value === true || $value === false) {
            return $value;
        }
        
        // "string" booleans
        $value = strtolower(trim($value));
        if (in_array($value, $this->_true)) {
            return true;
        }
        if (in_array($value, $this->_false)) {
            return false;
        }
        
        // forcibly recast to a boolean
        return (bool) $value;
    }
    
    /**
     * 
     * Forces the value to a float.
     * 
     * Attempts to extract a valid float from the given value, using an
     * algorithm somewhat less naive that "remove all characters that are not
     * '0-9.,eE+-'".  The result may not be expected, but it will be a float.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return float The sanitized value.
     * 
     * @todo Extract scientific notation from weird strings?
     * 
     */
    public function sanitizeFloat($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        if (! is_string($value) || is_numeric($value)) {
            return (float) $value;
        }
        
        // it's a non-numeric string, attempt to extract a float from it.
        
        // remove all + signs; any - sign takes precedence because ...
        //     0 + -1 = -1
        //     0 - +1 = -1
        // ... at least it seems that way to me now.
        $value = str_replace('+', '', $value);
        
        // reduce multiple decimals and minuses
        $value = preg_replace('/[\.-]{2,}/', '.', $value);
        
        // remove all decimals without a digit or minus next to them
        $value = preg_replace('/([^0-9-]\.[^0-9])/', '', $value);
        
        // remove all chars except digit, decimal, and minus
        $value = preg_replace('/[^0-9\.-]/', '', $value);
        
        // remove all trailing decimals and minuses
        $value = rtrim($value, '.-');
        
        // pre-empt further checks if already empty
        if ($value == '') {
            return (float) $value;
        }
        
        // remove all minuses not at the front
        $is_negative = ($value[0] == '-');
        $value = str_replace('-', '', $value);
        if ($is_negative) {
            $value = '-' . $value;
        }
        
        // remove all decimals but the first
        $pos = strpos($value, '.');
        $value = str_replace('.', '', $value);
        if ($pos !== false) {
            $value = substr($value, 0, $pos)
                   . '.'
                   . substr($value, $pos);
        }
        
        // looks like we're done
        return (float) $value;
    }
    
    /**
     * 
     * Forces the value to an integer.
     * 
     * Attempts to extract a valid integer from the given value, using an
     * algorithm somewhat less naive that "remove all characters that are not
     * '0-9+-'".  The result may not be expected, but it will be a integer.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return int The sanitized value.
     * 
     */
    public function sanitizeInt($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        if (! is_string($value) || is_numeric($value)) {
            // we double-cast here to honor scientific notation.
            // (int) 1E5 == 1, but (int) (float) 1E5 == 100000
            return (int) (float) $value;
        }
        
        // it's a non-numeric string, attempt to extract an integer from it.
        
        // remove all chars except digit and minus.
        // this removes all + signs; any - sign takes precedence because ...
        //     0 + -1 = -1
        //     0 - +1 = -1
        // ... at least it seems that way to me now.
        $value = preg_replace('/[^0-9-]/', '', $value);
        
        // remove all trailing minuses
        $value = rtrim($value, '-');
        
        // pre-empt further checks if already empty
        if ($value == '') {
            return (int) $value;
        }
        
        // remove all minuses not at the front
        $is_negative = ($value[0] == '-');
        $value = str_replace('-', '', $value);
        if ($is_negative) {
            $value = '-' . $value;
        }
        
        // looks like we're done
        return (int) $value;
    }
    
    /**
     * 
     * Forces the value to an ISO-8601 formatted date ("yyyy-mm-dd").
     * 
     * @param string $value The value to be sanitized.  If an integer, it
     * is used as a Unix timestamp; otherwise, converted to a Unix
     * timestamp using [[php::strtotime() | ]].
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeIsoDate($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        $format = 'Y-m-d';
        if ($this->validateInt($value, true)) {
            return date($format, $value);
        } else {
            return date($format, strtotime($value));
        }
    }
    
    /**
     * 
     * Forces the value to an ISO-8601 formatted time ("hh:ii:ss").
     * 
     * @param string $value The value to be sanitized.  If an integer, it
     * is used as a Unix timestamp; otherwise, converted to a Unix
     * timestamp using [[php::strtotime() | ]].
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeIsoTime($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        $format = 'H:i:s';
        if (is_int($value)) {
            return date($format, $value);
        } else {
            return date($format, strtotime($value));
        }
    }
    
    /**
     * 
     * Forces the value to an ISO-8601 formatted timestamp
     * ("yyyy-mm-ddThh:ii:ss").
     * 
     * @param string $value The value to be sanitized.  If an integer, it
     * is used as a Unix timestamp; otherwise, converted to a Unix
     * timestamp using [[php::strtotime() | ]].
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeIsoTimestamp($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        $format = 'Y-m-d\\TH:i:s';
        if (is_int($value)) {
            return date($format, $value);
        } else {
            return date($format, strtotime($value));
        }
    }
    
    /**
     * 
     * Forces the value to a numeric string.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return int The sanitized value.
     * 
     */
    public function sanitizeNumeric($value, $require = false)
    {
        return (string) $this->sanitizeFloat($value, $require);
    }
    
    /**
     * 
     * Converts the value to a string; characters are not stripped or encoded.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeString($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return (string) $value;
    }
    
    /**
     * 
     * Strips non-alphabetic characters from the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeStringAlpha($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return preg_replace('/[^a-z]/i', '', $value);
    }
    
    /**
     * 
     * Strips non-alphanumeric characters from the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeStringAlnum($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return preg_replace('/[^a-z0-9]/i', '', $value);
    }
    
    /**
     * 
     * Strips non-email characters from the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeStringEmail($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * 
     * Applies [[php::preg_replace() | ]] to the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param string $pattern The regex pattern to apply.
     * 
     * @param string $replace Replace the found pattern with this string.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeStringRegex($value, $pattern, $replace, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return preg_replace($pattern, $replace, $value);
    }
    
    /**
     * 
     * Applies [[php::str_replace() | ]] to the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param string $find Find this string.
     * 
     * @param string $replace Replace with this string.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeStringReplace($value, $find, $replace, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return str_replace($find, $replace, $value);
    }
    
    /**
     * 
     * Trims characters from the beginning and end of the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param string $chars Trim these characters (default space).
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeStringTrim($value, $chars = ' ', $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return trim($value, $chars);
    }
    
    /**
     * 
     * Strips non-URI characters from the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeStringUri($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return filter_var($value, FILTER_SANITIZE_URL);
    }
    
    /**
     * 
     * Strips non-word characters within the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeStringWord($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return preg_replace('/\W/', '', $value);
    }
    
    // -----------------------------------------------------------------
    // 
    // Validate
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Validates that the value is only letters (upper or lower case) and digits.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateAlnum($value, $require = true)
    {
        return $this->validateCtype($value, 'alnum', $require);
    }
    
    /**
     * 
     * Validates that the value is letters only (upper or lower case).
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateAlpha($value, $require = true)
    {
        return $this->validateCtype($value, 'alpha', $require);
    }
    
    /**
     * 
     * Validates that the value composed only of whitespace.
     * 
     * Non-string types never validate as "blank".
     * 
     * Strings are trimmed; if '', then the value is blank.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateBlank($value)
    {
        if (! is_string($value)) {
            return false;
        }
        
        return (trim((string)$value) == '');
    }
    
    /**
     * 
     * Validates that the value is a boolean representation.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateBoolean($value, $require = true)
    {
        // need to allow for blanks if not required, because
        // empty strings are boolean false, and strings composed of blanks
        // are booleans true.
        if ($this->validateBlank($value) && ! $require) {
            return true;
        }
        
        // PHP booleans
        if ($value === true || $value === false) {
            return true;
        }
        
        // "string" booleans
        $value = strtolower(trim($value));
        if (in_array($value, $this->_true, true) ||
            in_array($value, $this->_false, true)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 
     * Validates the value against a [[php::ctype | ]] function.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $type The ctype to validate against: 'alnum',
     * 'alpha', 'digit', etc.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if the value matches the ctype, false if not.
     * 
     */
    public function validateCtype($value, $type, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        $func = 'ctype_' . $type;
        return (bool) $func((string)$value);
    }
    
    /**
     * 
     * Validates that the value is an email address.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateEmail($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        // FILTER_VALIDATE_EMAIL modifies the given value to strip
        // invalid characters, then validates.  that bothers me.
        // so to compensate, we check the php-vlaidated value against
        // the original value to see if they match.  if they do, then
        // the original value was valid.
        $might_be_ok = filter_var($value, FILTER_VALIDATE_EMAIL);
        return $value == $might_be_ok;
    }
    
    /**
     * 
     * Validates that the value represents a float.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateFloat($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        if (is_float($value)) {
            return true;
        }
        
        // otherwise, must be numeric, and must be same as when cast to float
        return is_numeric($value) && $value == (float) $value;
    }
    
    /**
     * 
     * Validates that the value is a key in the list of allowed options.
     * 
     * Given the keys of the array (second parameter), the value
     * (first parameter) must match at least one of those keys.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $array An array of allowed options.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateInKeys($value, $array, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        return array_key_exists($value, (array) $array);
    }
    
    /**
     * 
     * Validates that the value is in a list of allowed values.
     * 
     * Strict checking is enforced, so a string "1" is not the same as
     * an integer 1.  This helps to avoid matching 0 and empty, etc.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $array An array of allowed values.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateInList($value, $array, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        return in_array($value, (array) $array, true);
    }
    
    /**
     * 
     * Validates that the value represents an integer.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateInt($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        if (is_int($value)) {
            return true;
        }
        
        // otherwise, must be numeric, and must be same as when cast to int
        return is_numeric($value) && $value == (int) $value;
    }
    
    /**
     * 
     * Validates that the value is a legal IP address (v4 or v6).
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIp($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        // FILTER_VALIDATE_IP modifies the given value to strip
        // invalid characters, then validates.  that bothers me.
        // so to compensate, we check the php-vlaidated value against
        // the original value to see if they match.  if they do, then
        // the original value was valid.
        $might_be_ok = filter_var($value, FILTER_VALIDATE_IP);
        return $value == $might_be_ok;
    }
    
    /**
     * 
     * Validates that the value is a legal IPv4 address.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIpv4($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        // FILTER_VALIDATE_IP modifies the given value to strip
        // invalid characters, then validates.  that bothers me.
        // so to compensate, we check the php-vlaidated value against
        // the original value to see if they match.  if they do, then
        // the original value was valid.
        $might_be_ok = filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        return $value == $might_be_ok;
    }
    
    /**
     * 
     * Validates that the value is a legal IPv6 address.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIpv6($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        // FILTER_VALIDATE_IP modifies the given value to strip
        // invalid characters, then validates.  that bothers me.
        // so to compensate, we check the php-vlaidated value against
        // the original value to see if they match.  if they do, then
        // the original value was valid.
        $might_be_ok = filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        return $value == $might_be_ok;
    }
    
    /**
     * 
     * Validates that the value is an ISO 8601 date string.
     * 
     * The format is "yyyy-mm-dd".  Also checks to see that the date
     * itself is valid (for example, no Feb 30).
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIsoDate($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
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
     * Validates that the value is an ISO 8601 time string (hh:ii::ss format).
     * 
     * Per note from Chris Drozdowski about ISO 8601, allows two
     * midnight times ... 00:00:00 for the beginning of the day, and
     * 24:00:00 for the end of the day.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIsoTime($value, $require = true)
    {
        $expr = '/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/';
        
        return $this->validateRegex($value, $expr, $require) ||
               ($value == '24:00:00');
    }
    
    /**
     * 
     * Validates that the value is an ISO 8601 timestamp string.
     * 
     * The format is "yyyy-mm-ddThh:ii:ss" (note the literal "T" in the
     * middle, which acts as a separator -- may also be a space).
     * 
     * Also checks that the date itself is valid (for example, no Feb 30).
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIsoTimestamp($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        // correct length?
        if (strlen($value) != 19) {
            return false;
        }
        
        // valid date?
        $date = substr($value, 0, 10);
        if (! $this->validateIsoDate($date)) {
            return false;
        }
        
        // valid separator?
        $sep = substr($value, 10, 1);
        if ($sep != 'T' && $sep != ' ') {
            return false;
        }
        
        // valid time?
        $time = substr($value, 11, 8);
        if (! $this->validateIsoTime($time)) {
            return false;
        }
        
        // must be ok
        return true;
    }
    
    /**
     * 
     * Validates that the value is a locale code.
     * 
     * The format is two lower-case letters, an underscore, and two upper-case
     * letters.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateLocaleCode($value, $require = true)
    {
        $expr = '/^[a-z]{2}_[A-Z]{2}$/';
        return $this->validateRegex($value, $expr, $require);
    }
    
    /**
     * 
     * Validates that the value is less than than or equal to a maximum.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $max The maximum valid value.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMax($value, $max, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        return is_numeric($value) && $value <= $max;
    }
    
    /**
     * 
     * Validates that a string is no longer than a certain length.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $max The value must have no more than this many
     * characters.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMaxLength($value, $max, $require = true)
    {
        // reverse the normal check for blankness so that blank strings
        // are not checked for length.
        if ($require && $this->validateBlank($value)) {
            return false;
        }
        
        return strlen($value) <= $max;
    }
    
    /**
     * 
     * Validates that the value is formatted as a MIME type.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $allowed The MIME type must be one of these
     * allowed values; if null, then all values are allowed.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMimeType($value, $allowed = null, $require = true)
    {
        // basically, anything like 'text/plain' or
        // 'application/vnd.ms-powerpoint' or
        // 'text/xml+xhtml'
        $word = '[a-zA-Z][\-\.a-zA-Z0-9+]*';
        $expr = '|^' . $word . '/' . $word . '$|';
        $ok = $this->validateRegex($value, $expr, $require);
        $allowed = (array) $allowed;
        if ($ok && count($allowed) > 0) {
            $ok = in_array($value, $allowed);
        }
        return $ok;
    }
    
    /**
     * 
     * Validates that the value is greater than or equal to a minimum.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The minimum valid value.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMin($value, $min, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        return is_numeric($value) && $value >= $min;
    }
    
    /**
     * 
     * Validates that a string is at least a certain length.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The value must have at least this many
     * characters.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMinLength($value, $min, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        return strlen($value) >= $min;
    }
    
    /**
     * 
     * Validates that the value is numeric (any number or number string).
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateNumeric($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        return is_numeric($value);
    }
    
    /**
     * 
     * Validates that the value is not exactly zero.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateNotZero($value, $require = true)
    {
        // reverse the blank-check so that empties are not
        // treated as zero.
        if ($require && $this->validateBlank($value)) {
            return false;
        }
        
        $zero = is_numeric($value) && $value == 0;
        return ! $zero;
    }
    
    /**
     * 
     * Validates that the value is not blank whitespace.
     * 
     * Boolean, integer, and float types are never "blank".
     * 
     * All other types are converted to string and trimmed; if '', then the
     * value is blank.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateNotBlank($value)
    {
        if (is_bool($value) || is_int($value) || is_float($value)) {
            return true;
        }
        
        return (trim((string)$value) != '');
    }
    
    /**
     * 
     * Validates that the value is within a given range.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The minimum valid value.
     * 
     * @param mixed $max The maximum valid value.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateRange($value, $min, $max, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        return ($value >= $min && $value <= $max);
    }
    
    /**
     * 
     * Validates that the length of the value is within a given range.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The minimum valid length.
     * 
     * @param mixed $max The maximum valid length.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateRangeLength($value, $min, $max, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        $len = strlen($value);
        return ($len >= $min && $len <= $max);
    }
    
    /**
     * 
     * Validates the value against a regular expression.
     * 
     * Uses [[php::preg_match() | ]] to compare the value against the given
     * regular epxression.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $expr The regular expression to validate against.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if the value matches the expression, false if not.
     * 
     */
    public function validateRegex($value, $expr, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        return (bool) preg_match($expr, $value);
    }
    
    /**
     * 
     * See the value has only a certain number of digits and decimals.
     * 
     * The value must be numeric, can be no longer than the `$size`,
     * and can have no more decimal places than the `$scope`.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param int $size The total number of digits allowed in the value,
     * excluding the negative sign and decimal point.
     * 
     * @param int $scope The maximum number of decimal places.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateSizeScope($value, $size, $scope, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
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
     * Validates that the value is composed of one or more words separated by
     * a single separator-character.
     * 
     * Word characters include a-z, A-Z, 0-9, and underscore, indicated by the 
     * regular expression "\w".
     * 
     * By default, the separator is a space, but you can include as many other
     * separators as you like.  Two separators in a row will fail validation.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $sep The word separator character(s), such as " -'" (to
     * allow spaces, dashes, and apostrophes in the word).  Default is ' '.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateSepWords($value, $sep = ' ', $require = true)
    {
        $expr = '/^[\w' . preg_quote($sep) . ']+$/';
        return $this->validateRegex($value, $expr, $require);
    }
    
    /**
     * 
     * Validates that the value can be represented as a string.
     * 
     * Essentially, this means any scalar value is valid (no arrays, objects,
     * resources, etc).
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateString($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        return is_scalar($value);
    }
    
    /**
     * 
     * Validates the value as a URI per RFC2396.
     * 
     * The value must match a generic URI format; for example,
     * ``http://example.com``, ``mms://example.org``, and so on.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateUri($value, $require = true)
    {
        if ($this->validateBlank($value)) {
            return ! $require;
        }
        
        // FILTER_VALIDATE_URL modifies the given value to strip
        // invalid characters, then validates.  that bothers me.
        // so to compensate, we check the php-vlaidated value against
        // the original value to see if they match.  if they do, then
        // the original value was valid.
        $might_be_ok = filter_var($value, FILTER_VALIDATE_URL,
            FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED);
        
        return $value == $might_be_ok;
    }
    
    /**
     * 
     * Validates that the value is composed only of "word" characters.
     * 
     * These include a-z, A-Z, 0-9, and underscore, indicated by a 
     * regular expression "\w".
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $require When true, value must be non-blank; when false,
     * blank values are valid.  Default true.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateWord($value, $require = true)
    {
        $expr = '/^\w+$/';
        return $this->validateRegex($value, $expr, $require);
    }
}
