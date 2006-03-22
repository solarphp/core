<?php
/**
 * 
 * Methods for filtering user input or other data.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Matthew Weier O'Phinney <mweierophinney@gmail.com>
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
 * Methods for filtering user input or other data.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
class Solar_Filter extends Solar_Base {
    
    // -----------------------------------------------------------------
    // 
    // Character classes.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns only alphabetic characters within the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function alpha($value)
    {
        return @preg_replace('/[^a-z]/i', '', $value);
    }
    
    /**
     * 
     * Strips alphabetic characters from the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function stripAlpha($value)
    {
        return @preg_replace('/[a-z]/i', '', $value);
    }
    
    /**
     * 
     * Returns only alphanumeric characters within the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function alnum($value)
    {
        return @preg_replace('/[^a-z0-9]/i', '', $value);
    }
    
    /**
     * 
     * Strips alphanumeric characters from the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function stripAlnum($value)
    {
        return @preg_replace('/[a-z0-9]/i', '', $value);
    }
    
    /**
     * 
     * Returns only whitespace characters within the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function blanks($value)
    {
        return @preg_replace('/\S/', '', $value);
    }
    
    /**
     * 
     * Strips all whitespace from the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function stripBlanks($value)
    {
        return @preg_replace('/\s/', '', $value);
    }
    
    /**
     * 
     * Returns only numbers within the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function numeric($value)
    {
        return @preg_replace('/\D/', '', $value);
    }
    
    /**
     * 
     * Strips all numbers from the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function stripNumeric($value)
    {
        return @preg_replace('/\d/', '', $value);
    }
    
    /**
     * 
     * Returns only word characters within the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function words($value)
    {
        return @preg_replace('/\W/', '', $value);
    }
    
    /**
     * 
     * Strips word characters from the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function stripWords($value)
    {
        return @preg_replace('/\w/', '', $value);
    }
    
    // -----------------------------------------------------------------
    // 
    // Date and time formats.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Forces a value to a date format using [[php date()]].
     * 
     * Takes a string value time and formats it according to $format.
     * The [[php date()]] function is used to create the new value, and $format
     * should be a format that works with that function.
     * 
     * The value should be a format that [[php strtotime()]] understands.
     * 
     * @param string $value The value to be filtered; must be a value
     * appropriate for strtotime().
     * 
     * @param string $format A timestamp format string appropriate for date();
     * default is ISO 8601 format (e.g., '2005-02-25').
     * 
     * @return string The filtered value.
     * 
     */
    public function formatDate($value, $format = 'Y-m-d')
    {
        return @date($format, strtotime($value));
    }
    
    /**
     * 
     * Forces a value to a time format using [[php date()]].
     * 
     * Takes a string value time and formats it according to $format.
     * The [[php date()]] function is used to create the new value, and $format
     * should be a format that works with that function.
     * 
     * The value should be a format that [[php strtotime()]] understands.
     * 
     * @param string $value The value to be filtered; must be a value
     * appropriate for strtotime().
     * 
     * @param string $format A timestamp format string appropriate for date();
     * default is ISO 8601 format (e.g., '12:34:56').
     * 
     * @return string The filtered value.
     * 
     */
    public function formatTime($value, $format = 'H:i:s')
    {
        return @date($format, strtotime($value));
    }
    
    /**
     * 
     * Forces a value to a timestamp format using [[php date()]].
     * 
     * Takes a string value time and formats it according to $format.
     * The [[php date()]] function is used to create the new value, and $format
     * should be a format that works with that function.
     * 
     * The value should be a format that [[php strtotime()]] understands.
     * 
     * @param string $value The value to be filtered; must be a value
     * appropriate for strtotime().
     * 
     * @param string $format A timestamp format string appropriate for date();
     * default is ISO 8601 format (e.g., '2005-02-25T12:34:56').
     * 
     * @return string The filtered value.
     * 
     */
    public function formatTimestamp($value, $format = 'Y-m-d\TH:i:s')
    {
        return @date($format, strtotime($value));
    }
    
    // -----------------------------------------------------------------
    // 
    // PHP function analogs.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Applies a [[php preg_replace()]] filter.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @param string $pattern The regex pattern to apply.
     * 
     * @param string $replace Replace the found pattern with this string.
     * 
     * @return string The filtered value.
     * 
     */
    public function pregReplace($value, $pattern, $replace)
    {
        return @preg_replace($pattern, $replace, $value);
    }
    
    /**
     * 
     * Applies a [[php str_replace()]] filter.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @param string $find Find this string.
     * 
     * @param string $replace Replace with this string.
     * 
     * @return string The filtered value.
     * 
     */
    public function strReplace($value, $find, $replace)
    {
        return @str_replace($find, $replace, $value);
    }
     
    /**
     * 
     * Trims characters from the beginning and end of the value.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @param string $chars Trim these characters.
     * 
     * @return string The filtered value.
     * 
     */
    public function trim($value, $chars = ' ')
    {
        return trim($value, $chars);
    }
    
    /**
     * 
     * Casts the value as a PHP variable type.
     * 
     * @param mixed $value The value to filter.
     * 
     * @param string $type A valid variable type: 'array', 'string',
     * 'int', 'float', 'double', 'real', or 'bool'
     * 
     * @return mixed The filtered value.
     * 
     */
    public function cast($value, $type)
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
    
    // -----------------------------------------------------------------
    // 
    // Meta methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Uses a callback to filter a value.
     * 
     * Uses a callback to filter a value. If $callback is uncallable,
     * returns the $value untransformed.  Allows arbitrary parameters to
     * be passed to the callback function, as long as the value to be
     * filtered is the first parameter of the callback.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @param mixed $callback A callback value suitable for call_user_func().
     * 
     * @return mixed The filtered value.
     * 
     */
    public function callback($value, $callback)
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
     * If the filter method is Solar_Filter::callback(), then the second argument should
     * be a valid callback. 
     * 
     * Any additional elements in the subarray will be passed as additional
     * arguments to the filter (the first argument to a filter function/method
     * is always the value being filtered).
     * 
     * Returns the transformed value after applying all filters.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @param array $filters The filters to apply.
     * 
     * @return mixed The filtered value.
     * 
     */
    public function multiple($value, $filters)
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
            if ('callback' == $method) {
                // Custom callback; check for availability
                $callback = array_shift($params);
                if (! is_callable($callback)) {
                    $callback = false;
                }
            } elseif (is_callable(array($this, $method))) {
                // Solar_Filter method name, takes precedence over
                // native PHP functions.
                $callback = array($this, $method);
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