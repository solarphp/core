<?php
/**
 * 
 * Handler for validating and sanitizing user input.
 * 
 * Includes one-off filtering as well as filter chains for flat data arrays.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Filter extends Solar_Base {
    
    /**
     * 
     * The chain of filters to be applied to the data array.
     * 
     * Format is 'data_key' => array(), where the sub-array is a sequential
     * array of callbacks.
     * 
     * For example, this will filter the $data['rank'] value to validate as
     * an integer in the range 0-9.
     * 
     *     $this->_chain_filters = array(
     *         'rank' => array(
     *             'validateInt',
     *             array('validateRange', 0, 9),
     *         )
     *     );
     * 
     * @var array
     * 
     * @see addFilter()
     * 
     * @see addFilters()
     * 
     * @see process()
     * 
     */
    protected $_chain_filters;
    
    /**
     * 
     * After processing, this contains the list of validation failure messages
     * for each data key.
     * 
     * @var array
     * 
     */
    protected $_chain_invalid;
    
    /**
     * 
     * The object used for generating "invalid" messages.
     * 
     * Defaults to $this.
     * 
     * @var Solar_Base
     * 
     */
    protected $_chain_locale_object;
    
    /**
     * 
     * Tells the filter chain if a particular data key is required.
     * 
     * The key is the data key name, the value is a boolean (true if required,
     * false if not).
     * 
     * @var array
     * 
     * @see setRequire()
     * 
     */
    protected $_chain_require;
    
    /**
     * 
     * The data array to be filtered by the chain.
     * 
     * @var array
     * 
     * @see process()
     * 
     */
    protected $_data;
    
    /**
     * 
     * The name of the data key currently being processed.
     * 
     * @var string
     * 
     * @see process()
     * 
     */
    protected $_data_key;
    
    /**
     * 
     * Filter objects, keyed on method name.
     * 
     * For example, 'sanitizeTrim' => Solar_Filter_Sanitize_Trim object.
     * 
     * @var array
     * 
     */
    protected $_filter = array();
    
    /**
     * 
     * Are values required to be not-blank?
     * 
     * For validate methods, when $_require is true, the value must be
     * non-blank for it to validate; when false, blank values are considered
     * valid.
     * 
     * For sanitize methods, when $_require is true, the method will attempt
     * to sanitize blank values; when false, the method will return blank
     * values as nulls.
     * 
     * @var bool
     * 
     * @see setRequire()
     * 
     * @see getRequire()
     * 
     */
    protected $_require = true;
    
    /**
     * 
     * Class stack for finding filters.
     * 
     * @var Solar_Class_Stack
     * 
     */
    protected $_stack;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // build the filter class stack
        $this->_stack = Solar::factory('Solar_Class_Stack');
        $this->setFilterClass();
        
        // extended setup
        $this->_setup();
    }
    
    /**
     * 
     * Call this method before you unset() this instance to fully recover
     * memory from circular-referenced objects.
     * 
     * @return void
     * 
     */
    public function free()
    {
        // each filter object instance
        foreach ($this->_filter as $key => $val) {
            unset($this->_filter[$key]);
        }
        
        // certain to be an object
        unset($this->_stack);
        
        // might be objects
        unset($this->_chain_locale_object);
        unset($this->_data);
    }
    
    /**
     * 
     * Magic call to filter methods represented as classes.
     * 
     * @param string $method The filter method to call; e.g., 'sanitizeTrim'
     * maps to `Solar_Filter_SanitizeTrim::sanitizeTrim()`.
     * 
     * @param array $params Params passed to the method, if any.
     * 
     * @return mixed
     * 
     */
    public function __call($method, $params)
    {
        $filter = $this->getFilter($method);
        return call_user_func_array(
            array($filter, $method),
            $params
        );
    }
    
    /**
     * 
     * Reset the filter class stack.
     * 
     * @param string|array $list The classes to set for the stack.
     * 
     * @return void
     * 
     * @see Solar_Class_Stack::set()
     * 
     * @see Solar_Class_Stack::add()
     * 
     */
    public function setFilterClass($list = null)
    {
        $parents = array_reverse(Solar::parents($this, true));
        array_shift($parents); // drops Solar_Base
        $this->_stack->set($parents);
        $this->_stack->add($list);
    }
    
    /**
     * 
     * Add to the filter class stack.
     * 
     * @param string|array $list The classes to add to the stack.
     * 
     * @return void
     * 
     * @see Solar_Class_Stack::add()
     * 
     */
    public function addFilterClass($list)
    {
        $this->_stack->add($list);
    }
    
    /**
     * 
     * Returns the filter class stack.
     * 
     * @return array The stack of filter classes.
     * 
     * @see Solar_Class_Stack::get()
     * 
     */
    public function getFilterClass()
    {
        return $this->_stack->get();
    }
    
    /**
     * 
     * Gets the stored filter object by method name.
     * 
     * Creates the filter object if it does not already exist.
     * 
     * @param string $method The method name, e.g. 'sanitizeTrim'.
     * 
     * @return Solar_Filter_Abstract The stored filter object.
     * 
     */
    public function getFilter($method)
    {
        if (empty($this->_filter[$method])) {
            $this->_filter[$method] = $this->newFilter($method);
        }
        
        return $this->_filter[$method];
    }
    
    /**
     * 
     * Creates a new filter object by method name.
     * 
     * @param string $method The method name, e.g. 'sanitizeTrim'.
     * 
     * @return Solar_Filter_Abstract The new filter object.
     * 
     */
    public function newFilter($method)
    {
        $method[0] = strtolower($method[0]);
        $class = $this->_stack->load($method);
        $obj = Solar::factory($class, array('filter' => $this));
        return $obj;
    }
    
    /**
     * 
     * Sets the value of the 'require' flag.
     * 
     * @param bool $flag Turn 'require' on (true) or off (false).
     * 
     * @return void
     * 
     * @see $_require
     * 
     */
    public function setRequire($flag)
    {
        $this->_require = (bool) $flag;
    }
    
    /**
     * 
     * Returns the value of the 'require' flag.
     * 
     * @return bool
     * 
     * @see $_require
     * 
     */
    public function getRequire()
    {
        return $this->_require;
    }
    
    /**
     * 
     * Sets the object used for getting locale() translations during
     * [[applyChain()]].
     * 
     * @param Solar_Base $obj Any Solar_Base object with a locale() method.
     * When empty, uses $this for locale().
     * 
     * @return void
     * 
     * @see applyChain()
     * 
     */
    public function setChainLocaleObject($obj)
    {
        $this->_chain_locale_object = $obj;
    }
    
    /**
     * 
     * Sets whether or not a particular data key is required to be present and
     * non-blank in the data being processed by [[applyChain()]].
     * 
     * @param string $key The data key.
     * 
     * @param bool $flag True if required, false if not.  Default true.
     * 
     * @return void
     * 
     * @see applyChain()
     * 
     */
    public function setChainRequire($key, $flag = true)
    {
        $this->_chain_require[$key] = (bool) $flag;
    }
    
    /**
     * 
     * Adds one filter-chain method for a data key.
     * 
     * @param string $key The data key.
     * 
     * @param string|array $spec The filter specification.  If a string, it's
     * just a method name. If an array, the first element is a method name,
     * and all remaining elements are parameters to that method.
     * 
     * @return void
     * 
     * @see applyChain()
     * 
     */
    public function addChainFilter($key, $spec)
    {
        $this->_chain_filters[$key][] = (array) $spec;
    }
    
    /**
     * 
     * Adds many filter-chain methods for a data key.
     * 
     * @param string $key The data key.
     * 
     * @param array $list An array of data keys and filter specifications.
     * 
     * @return void
     * 
     * @see applyChain()
     * 
     */
    public function addChainFilters($key, $list)
    {
        foreach ((array) $list as $spec) {
            $this->addChainFilter($key, $spec);
        }
    }
    
    /**
     * 
     * Resets the filter chain and required keys.
     * 
     * @param string $key Reset only this key. If empty, resets all keys.
     * 
     * @return void
     * 
     * @see applyChain()
     * 
     */
    public function resetChain($key = null)
    {
        if ($key === null) {
            $this->_chain_filters = array();
            $this->_chain_require = array();
            $this->_chain_invalid = array();
        } else {
            unset($this->_chain_filters[$key]);
            unset($this->_chain_require[$key]);
            unset($this->_chain_invalid[$key]);
        }
    }
    
    /**
     * 
     * Gets the list of invalid keys and feedback messages from the filter chain.
     * 
     * @param string $key Get messages for only this key. If empty, returns
     * messages for all keys.
     * 
     * @return array
     * 
     * @see applyChain()
     * 
     */
    public function getChainInvalid($key = null)
    {
        if ($key === null) {
            return $this->_chain_invalid;
        } elseif (! empty($this->_chain_invalid[$key])) {
            return $this->_chain_invalid[$key];
        }
    }
    
    /**
     * 
     * Gets a copy of the data array, or a specific element of data, being
     * processed by [[applyChain()]].
     * 
     * @param string $key If empty, returns the whole data array; otherwise,
     * returns just that key element of data.
     * 
     * @return mixed A copy of the data array or element.
     * 
     * @see applyChain()
     * 
     */
    public function getData($key = null)
    {
        if ($key === null) {
            return $this->_data;
        }
        
        if ($this->_data instanceof Solar_Struct && isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }
        
        return null;
    }
    
    /**
     * 
     * Sets one data element being processed by [[applyChain()]].
     * 
     * @param string $key Set this element key.
     * 
     * @param string $val Set the element to this value.
     * 
     * @return void
     * 
     * @see applyChain()
     * 
     */
    public function setData($key, $val)
    {
        $this->_data[$key] = $val;
    }
    
    /**
     * 
     * Gets the current data key being processed by the filter chain.
     * 
     * @return string
     * 
     * @see applyChain()
     * 
     */
    public function getDataKey()
    {
        return $this->_data_key;
    }
    
    /**
     * 
     * Applies the filter chain to an array of data in-place.
     * 
     * @param array &$data A reference to the data to be filtered; sanitizing
     * methods will be applied to the data directly, so the data is modified
     * in-place.
     * 
     * @return bool True if all elements were validated, and all required keys
     * were present and non-blank; false if not validated or a key was missing
     * or blank.
     * 
     */
    public function applyChain(&$data)
    {
        // keep the data as a property, because some extended Filter classes
        // may need to refer to various pieces of data for validation.
        $this->_data =& $data;
        
        // reset the list of invalid keys
        $this->_chain_invalid = array();
        
        // if we don't have a locale object, use self
        if (! $this->_chain_locale_object) {
            $this->_chain_locale_object = $this;
        }
        
        // see if we actually have all the required data keys
        foreach ($this->_chain_require as $key => $flag) {
            
            if (! $flag) {
                // not required
                continue;
            }
            
            // "blank" means the key does not exist in the data, or that it
            // validates as a blank value
            $blank = ! isset($this->_data[$key]) ||
                     $this->validateBlank($this->_data[$key]);
            
            // is it blank?
            if ($blank) {
                $msg = $this->_chainLocale('INVALID_NOT_BLANK');
                $this->_chain_invalid[$key][] = $msg;
            }
        }
        
        // loop through each data element
        foreach ($this->_data as $key => $val) {
            
            // keep the key name
            $this->_data_key = $key;
            
            // if it's already invalid (from "require" above)
            // then skip it.  this avoids multiple validation
            // messages on missing elements.
            if (! empty($this->_chain_invalid[$key])) {
                continue;
            }
            
            // are there filters on this key?
            if (empty($this->_chain_filters[$key])) {
                continue;
            }
            
            // is this key required?
            if (! empty($this->_chain_require[$key])) {
                $this->setRequire(true);
            } else {
                $this->setRequire(false);
            }
            
            // run the filters for the data element
            foreach ($this->_chain_filters[$key] as $params) {
                
                // take the method name off the top of the params ...
                $method = array_shift($params);
                
                // ... and put the value in its place. we use the
                // $data[$key] instead of $val so that the data
                // array itself is updated, not the local-scope $val.
                array_unshift($params, $this->_data[$key]);
                
                // call the filtering method
                $result = $this->__call($method, $params);
                
                // what to do with the result?
                $type = strtolower(substr($method, 0, 8));
                if ($type == 'sanitize') {
                    // retain the sanitized value
                    $this->_data[$key] = $result;
                } elseif ($type == 'validate' && ! $result) {
                    // a validation method failed; use the method name as
                    // the locale translation key, converting from camelCase
                    // to camel_Case, then to CAMEL_CASE.
                    $this->_chain_invalid[$key][] = $this->_chainLocale($method);
                }
            }
        }
        
        // return the validation status; if not empty, at least one of the
        // data elements was not valid.
        $result = empty($this->_chain_invalid);
        return $result;
    }
    
    /**
     * 
     * Uses the chain locale object to get translations before falling back
     * to this object for locale.
     * 
     * @param string $key The translation key, typically a validation method
     * name.
     * 
     * @return string
     * 
     */
    protected function _chainLocale($key)
    {
        // 'validatePregReplace' => 'invalidPregReplace'
        $key = 'invalid' . substr($key, 8);
        
        // 'validatePregReplace' => 'INVALID_PREG_REPLACE'
        $key = strtoupper(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
        
        // the translated message
        $msg = null;
        
        // if we have an alternative locale object, get a message from it
        if ($this->_chain_locale_object) {
            $msg = $this->_chain_locale_object->locale($key);
        }
        
        // if the message is still exactly null (meaning there was no locale
        // object), or if the key had no translation, fall back to the
        // translations from $this.
        if ($msg === null || $msg == $key) {
            $msg = $this->locale($key);
        }
        
        // done
        return $msg;
    }
    
    /**
     * 
     * Allows specialized setup for extended classes.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
}
