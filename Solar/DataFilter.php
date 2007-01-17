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
    protected $_true = array('1', 'true', 'on', 'yes');
    
    /**
     * 
     * String representations of "false" boolean values.
     * 
     * @var array
     * 
     */
    protected $_false = array('0', 'false', 'off', 'no', '');
    
    // -----------------------------------------------------------------
    // 
    // Sanitize
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Strip non-alphabetic characters from value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeAlpha($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return preg_replace('/[^a-z]/i', '', $value);
    }
    
    /**
     * 
     * Strip non-alphanumeric characters from a value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeAlnum($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return preg_replace('/[^a-z0-9]/i', '', $value);
    }
    
    /**
     * 
     * Converts the value to a boolean.
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
    public function sanitizeEmail($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * 
     * Converts the value to a float.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeFloat($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT,
            FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_SCIENTIFIC);
    }
    
    /**
     * 
     * Converts the value to an integer.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeInt($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
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
    public function sanitizePregReplace($value, $pattern, $replace, $require = false)
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
    public function sanitizeStrReplace($value, $find, $replace, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return str_replace($find, $replace, $value);
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
     * Trims characters from the beginning and end of the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param string $chars Trim these characters.
     * 
     * @param bool $require Sanitize blank values (default false, which
     * returns blank values as nulls).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeTrim($value, $chars = ' ', $require = false)
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
    public function sanitizeUri($value, $require = false)
    {
        if (! $require && $this->validateBlank($value)) {
            return null;
        }
        
        return filter_var($value, FILTER_SANITIZE_URI);
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
    public function sanitizeWord($value, $require = false)
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
     * Validates that a value is only letters (upper or lower case) and digits.
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
     * Validates that a value is letters only (upper or lower case).
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
     * Validates that a value composed only of whitespace.
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
    public function validateBlank($value)
    {
        if (is_bool($value) || is_int($value) || is_float($value)) {
            return false;
        }
        
        return (trim((string)$value) == '');
    }
    
    /**
     * 
     * Validates that a value is a boolean representation.
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
        if (! $require && $this->validateBlank($value)) {
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
     * Validates a value against a [[php::ctype | ]] function.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        $func = 'ctype_' . $type;
        return (bool) $func((string)$value);
    }
    
    /**
     * 
     * Validates that a value is an email address.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        // FILTER_VALIDATE_EMAIL, when valid, returns the email address,
        // not a boolean, so we need to cast to boolean here.
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return array_key_exists($value, (array) $array);
    }
    
    /**
     * 
     * Validates that a value is in a list of allowed values.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return in_array($value, (array) $array, true);
    }
    
    /**
     * 
     * Validates that a value represents an integer (+/-).
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        if (is_int($value)) {
            return true;
        }
        
        // otherwise, must be numeric, and must be same as when cast to int
        return is_numeric($value) && $value == (int) $value;
    }
    
    /**
     * 
     * Validates that a value is a legal IP address (v4 or v6).
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return (bool) filter_var($value, FILTER_VALIDATE_IP);
    }
    
    /**
     * 
     * Validates that a value is a legal IPv4 address.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return (bool) filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
    
    /**
     * 
     * Validates that a value is a legal IPv6 address.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return (bool) filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }
    
    /**
     * 
     * Validates that a value is an ISO 8601 date string.
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
        if (! $require && $this->validateBlank($value)) {
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
     * Validates that a value is an ISO 8601 time string (hh:ii::ss format).
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
     * Validates that a value is an ISO 8601 timestamp string.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
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
     * Validates that a value is a locale code.
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
     * Validates that a value is less than than or equal to a maximum.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
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
     * Validates that a value is formatted as a MIME type.
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
     * Validates that a value is greater than or equal to a minimum.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return strlen($value) >= $min;
    }
    
    /**
     * 
     * Validates that a value is numeric (any number or number string).
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return is_numeric($value);
    }
    
    /**
     * 
     * Validates that a value is not exactly zero.
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
     * Validates that a value is not blank whitespace.
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
     * Validates that a value is within a given range.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return ($value >= $min && $value <= $max);
    }
    
    /**
     * 
     * Validates that the length of a value is within a given range.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        $len = strlen($value);
        return ($len >= $min && $len <= $max);
    }
    
    /**
     * 
     * Validates a value against a regular expression.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        return (bool) preg_match($expr, $value);
    }
    
    /**
     * 
     * See a value has only a certain number of digits and decimals.
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
    public function validateScope($value, $size, $scope, $require = true)
    {
        // allowed blank?
        if (! $require && $this->validateBlank($value)) {
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
     * Validates that a value is composed of one or more words separated by
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
     * Validates that a value can be represented as a string.
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
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return is_scalar($value);
    }
    
    /**
     * 
     * Validates a value as a URI per RFC2396.
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
        // allowed blank?
        if (! $require && $this->validateBlank($value)) {
            return true;
        }
        
        return (bool) filter_var($value, FILTER_VALIDATE_URL,
            FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED);
    }
    
    /**
     * 
     * Validates that a value is composed only of "word" characters.
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
