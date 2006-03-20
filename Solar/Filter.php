<?php
/**
 * 
 * Methods for filtering data.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
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
 * Methods for filtering data.
 * 
 * An OOP container for applying filters to data, e.g., form elements.
 * Filters are typically callbacks that accept either a single value as
 * an argument (and return a transformed version of that value), or a
 * value to transform as the first argument and one or more additional
 * parameters.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @todo standardize names to indicate the action? use "only" or "keep"?
 * 
 * replace        => pregReplace
 * alpha          => stripAlpha, keepAlpha
 * alnum          => stripAlnum, keepAlnum
 * numeric        => stripNumeric, keepNumeric (should allow +/- and decimals)
 * blank          => stripBlanks, keepBlanks
 * cast           => cast
 * isoDate        => formatDate (ISO by default)
 * isoTime        => formatTime (ISO by default)
 * formatDateTime => formatTimestamp (ISO by default)
 * 
 * add new methods:
 * trim()
 * strReplace()
 * stripWordChars(), keepWordChars() (regex \w)
 * 
 */
class Solar_Filter extends Solar_Base {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'custom' => array(),
    );
    
    /**
     * 
     * Container for custom validator objects.
     * 
     * @var array
     * 
     */
    protected $_custom = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // "real" construction
        parent::__construct($config);
        
        // add custom dependencies in LIFO order
        $reverse = array_reverse((array) $this->_config['custom']);
        foreach ($reverse as $class => $spec) {
            $this->_custom[] = Solar::dependency($class, $spec);
        }
    }
    
    /**
     * 
     * Calls custom filtering methods.
     * 
     * @param string $method The filtering method to call.
     * 
     * @param array $params The parameters for the filtering method.
     * 
     * @return mixed The filtered value.
     * 
     */
    public function __call($method, $params)
    {
        // loop through the stack of custom objects, looking for the
        // right method name.
        foreach ($this->_custom as $obj) {
            if (method_exists($obj, $method)) {
                return call_user_func_array(array($obj, $method), $params);
            }
        }
        // couldn't find it
        throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED');
    }
    
    /**
     * 
     * Applies a regex replacement filter.
     * 
     * Applies a preg_replace() to a value. The first element in $info
     * should be the regex, the second the values that will replace those
     * in the regex. If arguments are missing, the original string is
     * returned without transformations.
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
    public function replace($value, $pattern, $replacement)
    {
        $final = @preg_replace($pattern, $replacement, $value);
        return $final;
    }
    
    /**
     * 
     * Removes non-alphabetic characters.
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
     * Removes non-alphabetic and non-numeric characters.
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
     * Removes all whitespace characters.
     * 
     * @param mixed $value The value to be filtered.
     * 
     * @return string The filtered value.
     * 
     */
    public function blank($value)
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
     * @param string $value The value to be filtered; must be a value
     * appropriate for strtotime().
     * 
     * @param string A format string appropriate for date().
     * 
     * @return string The filtered value.
     * 
     */
    public function formatDateTime($value, $format)
    {
        return @date($format, strtotime($value));
    }
    
    /**
     * 
     * Forces a value to an ISO-standard date string.
     * 
     * @param string $value The value to be filtered; must be a value
     * appropriate for strtotime().
     * 
     * @return string The filtered value.
     * 
     */
    public function isoDate($value)
    {
        return @date('Y-m-d', strtotime($value));
    }
    
    /**
     * 
     * Forces a value to an ISO-standard date-time string.
     * 
     * @param string $value The value to be filtered; must be a value
     * appropriate for strtotime().
     * 
     * @return string The filtered value.
     * 
     */
    public function isoDateTime($value)
    {
        return @date('Y-m-dTH:i:s', strtotime($value));
    }
    
    /**
     * 
     * Forces a value to an ISO-standard time string.
     * 
     * @param string $value The value to be filtered; must be a value
     * appropriate for strtotime().
     * 
     * @return string The filtered value.
     * 
     */
    public function isoTime($value)
    {
        return @date('H:i:s', strtotime($value));
    }
    
    /**
     * 
     * Removes non-numeric characters.
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
     * Filter: cast a value as a type
     * 
     * Casts a value as a specific type. $type should be a valid variable type:
     * 'array', 'string', 'int', 'float', 'double', 'real', or 'bool'.
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
     * If the filter type is {@link custom()}, then the second argument should
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