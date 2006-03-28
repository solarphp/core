<?php
/**
 * 
 * Methods for validating data.
 * 
 * @category Solar
 * 
 * @package Solar_Valid
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
 * Methods for validating data.
 * 
 * @category Solar
 * 
 * @package Solar_Valid
 * 
 */
class Solar_Valid extends Solar_Base {
    
    /**
     * Flag for allowing validation on a blank value.
     */
    const OR_BLANK  = true;
    
    /**
     * Flag for disallowing validation on a blank value.
     */
    const NOT_BLANK = false;
    
    /**
     * 
     * Validate that a value is only letters (upper or lower case) and digits.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function alnum($value, $blank = Solar_Valid::NOT_BLANK)
    {
        return $this->ctype($value, 'alnum', $blank);
    }
    
    /**
     * 
     * Validate that a value is letters only (upper or lower case).
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function alpha($value, $blank = Solar_Valid::NOT_BLANK)
    {
        return $this->ctype($value, 'alpha', $blank);
    }
    
    /**
     * 
     * Validate that a value is empty when trimmed of all whitespace.
     * 
     * The value is assessed as a string; thus, if you pass a numeric
     * zero, the value will not validate, becuse string '0' does not 
     * trim down to an empty string.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function blank($value)
    {
        return (trim((string)$value) == '');
    }
    
    /**
     * 
     * Validate against a callback function or method.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param callback $callback A string or array suitable for use
     * as the first argument to [[php call_user_func_array()]].
     * 
     * @return bool True if valid, false if not.
     * 
     * @see call_user_func_array()
     * 
     */
    public function callback($value, $callback)
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
     * Validate a value against a [[php ctype]] function.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $type The ctype to validate against: 'alnum',
     * 'alpha', 'digit', etc.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if the value matches the ctype, false if not.
     * 
     */
    public function ctype($value, $type, $blank = Solar_Valid::NOT_BLANK)
    {
        if ($blank && $this->blank($value)) {
            return true;
        }
        $func = 'ctype_' . $type;
        return (bool) $func((string)$value);
    }
    
    /**
     * 
     * Validate that a value is an email address.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function email($value, $blank = Solar_Valid::NOT_BLANK)
    {
        // taken from HTML_QuickForm.
        $expr = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';
        return $this->regex($value, $expr, $blank);
    }
    
    /**
     * 
     * Validate that the value is a key in the list of allowed options.
     * 
     * Given the keys of the array (second parameter), the value
     * (first parameter) must match at least one of those keys.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $array An array of allowed options.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function inKeys($value, $array, $blank = Solar_Valid::NOT_BLANK)
    {
        if ($blank && $this->blank($value)) {
            return true;
        }
        
        return array_key_exists($value, (array) $array);
    }
    
    /**
     * 
     * Validate that a value is in a list of allowed values.
     * 
     * Strict checking is enforced, so a string "1" is not the same as
     * an integer 1.  This helps to avoid matching 0 and empty, etc.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $array An array of allowed values.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function inList($value, $array, $blank = Solar_Valid::NOT_BLANK)
    {
        if ($blank && $this->blank($value)) {
            return true;
        }
        
        return in_array($value, (array) $array, true);
    }
    
    /**
     * 
     * See a value has only a certain number of digits and decimals.
     * 
     * The value must be numeric, can be no longer than the \\$size\\,
     * and can have no more decimal places than the \\$scope\\.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param int $size The total number of digits allowed in the value,
     * excluding the negative sign and decimal point.
     * 
     * @param int $scope The maximum number of decimal places.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function inScope($value, $size, $scope, $blank = Solar_Valid::NOT_BLANK)
    {
        // allowed blank?
        if ($blank && $this->blank($value)) {
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
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function integer($value, $blank = Solar_Valid::NOT_BLANK)
    {
        $expr = '/^[\+\-]?[0-9]+$/';
        return $this->regex($value, $expr, $blank);
    }
    
    /**
     * 
     * Validate that a value is a legal IPv4 address.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function ipv4($value, $blank = Solar_Valid::NOT_BLANK)
    {
        if ($blank && $this->blank($value)) {
            return true;
        }
        
        $expr = '/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/';
        $result = preg_match($expr, $value, $matches);
        
        // no match
        if (! $result) {
            return false;
        }
        
        // check that all four quads are 0-255
        for ($i = 1; $i <= 4; $i++) {
            if ($matches[$i] < 0 || $matches[$i] > 255) {
                return false;
            }
        }
        
        // done!
        return true;
    }
    
    /**
     * 
     * Validate that a value is an ISO 8601 date string.
     * 
     * The format is "yyyy-mm-dd".  Also checks to see that the date
     * itself is valid (e.g., no Feb 30).
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function isoDate($value, $blank = Solar_Valid::NOT_BLANK)
    {
        if ($blank && $this->blank($value)) {
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
     * Validate that a value is an ISO 8601 time string (hh:ii::ss format).
     * 
     * Per note from Chris Drozdowski about ISO 8601, allows two
     * midnight times ... 00:00:00 for the beginning of the day, and
     * 24:00:00 for the end of the day.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function isoTime($value, $blank = Solar_Valid::NOT_BLANK)
    {
        $expr = '/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/';
        return $this->regex($value, $expr, $blank) || ($value == '24:00:00');
    }
    
    /**
     * 
     * Validate that a value is an ISO 8601 timestamp string.
     * 
     * The format is "yyyy-mm-ddThh:ii:ss" (note the literal "T" in the
     * middle, which acts as a separator).
     * 
     * Also checks that the date itself is valid (e.g., no Feb 30).
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function isoTimestamp($value, $blank = Solar_Valid::NOT_BLANK)
    {
        if ($blank && $this->blank($value)) {
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
            $this->isoDate($date) &&
            $sep == 'T' &&
            $this->isoTime($time)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Validate that a value is a locale code.
     * 
     * Note that this overrides Solar_Base::locale().
     * 
     * The format is two lower-case letters, an underscore, and two upper-case
     * letters.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function localeCode($value, $blank = Solar_Valid::NOT_BLANK)
    {
        $expr = '/^[a-z]{2}_[A-Z]{2}$/';
        return $this->regex($value, $expr, $blank);
    }
    
    /**
     * 
     * Validate that a value is less than than or equal to a maximum.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The maximum valid value.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function max($value, $max, $blank = Solar_Valid::NOT_BLANK)
    {
        // reverse the blank-check so that empties are not
        // treated as zero.
        if (! $blank && $this->blank($value)) {
            return false;
        }
        
        return $value <= $max;
    }
    
    /**
     * 
     * Validate that a string is no longer than a certain length.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The value must have no more than this many
     * characters.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function maxLength($value, $max, $blank = Solar_Valid::NOT_BLANK)
    {
        // reverse the blank-check so that empties are not
        // checked for length.
        if (! $blank && $this->blank($value)) {
            return false;
        }
        
        return (strlen($value) <= $max);
    }
    
    /**
     * 
     * Validate that a value is formatted as a MIME type.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function mimeType($value, $allowed = null,
        $blank = Solar_Valid::NOT_BLANK)
    {
        // basically, anything like 'text/plain' or
        // 'application/vnd.ms-powerpoint' or
        // 'text/xml+xhtml'
        $word = '[a-zA-Z][\-\.a-zA-Z0-9+]*';
        $expr = '|^' . $word . '/' . $word . '$|';
        $ok = $this->regex($value, $expr, $blank);
        $allowed = (array) $allowed;
        if ($ok && count($allowed) > 0) {
            $ok = in_array($value, $allowed);
        }
        return $ok;
    }
    
    /**
     * 
     * Validate that a value is greater than or equal to a minimum.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The minimum valid value.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function min($value, $min, $blank = Solar_Valid::NOT_BLANK)
    {
        if ($blank && $this->blank($value)) {
            return true;
        }
        
        return $value >= $min;
    }
    
    /**
     * 
     * Validate that a string is at least a certain length.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The value must have at least this many
     * characters.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function minLength($value, $min, $blank = Solar_Valid::NOT_BLANK)
    {
        if ($blank && $this->blank($value)) {
            return true;
        }
        
        return (strlen($value) >= $min);
    }
    
    /**
     * 
     * Check the value against multiple callback validations.
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
    public function multiple($value, $validations)
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
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function notZero($value, $blank = Solar_Valid::NOT_BLANK)
    {
        // reverse the blank-check so that empties are not
        // treated as zero.
        if (! $blank && $this->blank($value)) {
            return false;
        }
        
        // +-000.000
        $expr = '/^(\+|\-)?0+(.0+)?$/';
        return ! $this->regex($value, $expr);
    }
    
    /**
     * 
     * Validate that a string is not empty when trimmed.
     * 
     * Spaces, newlines, etc. will be trimmed, so a value consisting
     * only of whitespace is considered blank.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function notBlank($value)
    {
        return (trim((string)$value) != '');
    }
    
    /**
     * 
     * Validate a value against a regular expression.
     * 
     * Uses [[php preg_match()]] to compare the value against the given
     * regular epxression.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $expr The regular expression to validate against.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if the value matches the expression, false if not.
     * 
     */
    public function regex($value, $expr, $blank = Solar_Valid::NOT_BLANK)
    {
        if ($blank && $this->blank($value)) {
            return true;
        }
        return (bool) preg_match($expr, $value);
    }
    
    /**
     * 
     * Validate that a value is composed of separated words.
     * 
     * These include a-z, A-Z, 0-9, and underscore, indicated by a 
     * regular expression "\w".  By default, the separator is a space.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $sep The word separator character.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function sepWords($value, $sep = ' ', $blank = Solar_Valid::NOT_BLANK)
    {
        $expr = '/^[\w' . preg_quote($sep) . ']+$/';
        return $this->regex($value, $expr, $blank);
    }
    
    /**
     * 
     * Validate a value as a URI per RFC2396.
     * 
     * The value must match a generic URI format; e.g.,
     * ``http://example.com``, ``mms://example.org``, and so on.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string|array $schemes Allowed schemes for the URI;
     * e.g., http, https, ftp.  If null, all schemes are allowed.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if the value is a URI and is one of the allowed
     * schemes, false if not.
     * 
     */
    public function uri($value, $schemes = null, $blank = Solar_Valid::NOT_BLANK)
    {
        // allow blankness?
        if ($blank && $this->blank($value)) {
            return true;
        }
        
        // TAKEN (almost) DIRECTLY FROM PEAR_VALIDATE::URI()
        $result = preg_match(
            '£^(?:([a-z][-+.a-z0-9]*):)?                                                # 1. scheme
            (?://                                                                       #    authority start
            (?:((?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'();:&=+$,])*)@)?                         # 2. authority-userinfo
            (?:((?:[a-z0-9](?:[-a-z0-9]*[a-z0-9])?\.)*[a-z](?:[-a-z0-9]*[a-z0-9])?\.?)  # 3. authority-hostname OR
            |([0-9]{1,3}(?:\.[0-9]{1,3}){3}))                                           # 4. authority-ipv4
            (?::([0-9]*))?)?                                                            # 5. authority-port
            ((?:/(?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'():@&=+$,;])*)+)?                       # 6. path
            (?:\?([^#]*))?                                                              # 7. query
            (?:\#((?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'();/?:@&=+$,])*))?                     # 8. fragment
            $£xi', $value, $matches);
        
        if ($result) {
            
            $scheme = isset($matches[1]) ? $matches[1] : '';
            $authority = isset($matches[3]) ? $matches[3] : '' ;
            
            // we need some sort of scheme
            if (! $scheme) {
                return false;
            }
            
            // is the scheme allowed?
            settype($schemes, 'array');
            if ($schemes && ! in_array($scheme, $schemes)) {
                return false;
            }
            
            // check IPv4 addresses as domains
            if (isset($matches[4])) {
                $parts = explode('.', $matches[4]);
                foreach ($parts as $part) {
                    if ($part > 255) {
                        return false;
                    }
                }
            }
            
            // are we doing strict checks?
            $list = ';/?:@$,';
            $strict = '#[' . preg_quote($list, '#') . ']#';
            $test1 = (isset($matches[7]) && preg_match($strict, $matches[7]));
            $test2 = (isset($matches[8]) && preg_match($strict, $matches[8]));
            if ($test1 || $test2) {
                return false;
            }
            
            return true;
        }
        
        // default is to not-validate
        return false;
    }
    
    /**
     * 
     * Validate that a value is composed only of "word" characters.
     * 
     * These include a-z, A-Z, 0-9, and underscore, indicated by a 
     * regular expression "\w".
     * 
     * @param mixed $value The value to validate.
     * 
     * @param bool $blank Allow blank values to be valid.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function word($value, $blank = Solar_Valid::NOT_BLANK)
    {
        $expr = '/^\w+$/';
        return $this->regex($value, $expr, $blank);
    }
}
?>