<?php
/**
 * 
 * Retrieves and validates command-line options and parameter values.
 * 
 * @category Solar
 * 
 * @package Solar_Getopt
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 * @todo Add load() method similar to Solar_Form::load(), for loading from 
 * external XML, PHP array, etc. files.
 * 
 */
class Solar_Getopt extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string filter_class The data-filter class to use when validating and sanitizing
     *   parameter values.  Default is 'Solar_Filter'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Getopt = array(
        'filter_class' => 'Solar_Filter',
    );
    
    /**
     * 
     * The array of acceptable options.
     * 
     * The `$options` array contains all options accepted by the
     * application, including their types, default values, descriptions,
     * requirements, and validation callbacks.
     * 
     * In general, you should not try to set $options yourself;
     * instead, use [[Solar_Getopt::setOption()]] and/or
     * [[Solar_Getopt::setOptions()]].
     * 
     * @var array
     * 
     */
    public $options = array();
    
    /**
     * 
     * Default option settings.
     * 
     * `long`
     * : (string) The long-form of the option name (e.g., "--foo-bar" would
     *   be "foo-bar").
     * 
     * `short`
     * : (string) The short-form of the option, if any (e.g., "-f" would be
     *   "f").
     * 
     * `descr`
     * : (string) A description of the option (used in "help" output).
     * 
     * `param`
     * : (string) When the option is present, does it take a parameter?  If so,
     *   the param can be "required" every time, or be "optional". If empty, no
     *   parameter for the option will be recognized (the option's value will be
     *   boolean true when the option is present).  Default is 'optional'.
     * 
     * `value`
     * : (mixed) The default value for the option parameter, if any.  This way,
     *   options not specified in the arguments can have a default value.
     * 
     * `require`
     * : (bool) At validation time, the option must have a non-blank value
     *   of some sort.
     * 
     * `filters`
     * : (array) An array of filters to apply to the parameter value.  This can
     *   be a single filter (`array('validateInt')`), or a series of filters
     *   (`array('validateInt', array('validateRange', -10, +10)`).
     * 
     * @var array
     * 
     */
    protected $_default = array(
        'long'    => null,
        'short'   => null,
        'param'   => 'optional',
        'value'   => null,
        'descr'   => null,
        'require' => false,
        'filters' => array(),
    );
    
    /**
     * 
     * The arguments passed in from the command line.
     * 
     * @var array
     * 
     * @see populate()
     * 
     */
    protected $_argv;
    
    /**
     * 
     * List of names for invalid option values, and error messages.
     * 
     * @var array
     * 
     */
    protected $_invalid = array();
    
    /**
     * 
     * List of filters to apply to option values.
     * 
     * @var array
     * 
     */
    protected $_filters = array();
    
    /**
     * 
     * Option values parsed from the arguments, as well as remaining (numeric)
     * arguments.
     * 
     * @var array
     * 
     */
    protected $_values;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config Configuration value overrides, if any.
     * 
     */
    public function __construct($config = null)
    {
        // "real" contruction
        parent::__construct($config);
        
        // get the current request environment
        $this->_request = Solar_Registry::get('request');
        
        // set up the data-filter class
        $this->_filter = Solar::factory($this->_config['filter_class']);
    }
    
    // -----------------------------------------------------------------
    //
    // Option-management methods
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Sets one option for recognition.
     * 
     * @param string $name The option name to set or add; overrides
     * $info['short'] if 1 character long, otherwise overrides $info['long'].
     * 
     * @param array $info Option information using the same keys
     * as [[Solar_Getopt::$_default]].
     * 
     * @return void
     * 
     */
    public function setOption($name, $info)
    {
        // prepare the option info
        $info = array_merge($this->_default, $info);
        
        // override the short- or long-form of the option
        if (strlen($name) == 1) {
            $info['short'] = $name;
        } else {
            // convert underscores to dashes for the *cli*
            $info['long'] = str_replace('_', '-', $name);
        }
        
        // normalize the "param" setting
        $param = strtolower($info['param']);
        if ($param == 'r' || substr($param, 0, 3) == 'req') {
            $info['param'] = 'required';
        } elseif ($param == 'o' || substr($param, 0, 3) == 'opt') {
            $info['param'] = 'optional';
        } else {
            $info['param'] = null;
        }
        
        // convert dashes to underscores for the *key*
        $name = str_replace('-', '_', $name);
        
        // forcibly cast each of the keys in the options array
        $this->options[$name] = array(
            'long'    => $info['long'],
            'short'   => substr($info['short'], 0, 1),
            'param'   => $info['param'],
            'value'   => $info['value'],
            'descr'   => $info['descr'],
            'require' => (bool) $info['require']
        );
        
        // retain and fix any filters for the option value
        if ($info['filters']) {
            
            // make sure filters are an array
            settype($info['filters'], 'array');
            
            // make sure that strings are converted to arrays so that
            // validate() works properly.
            foreach ($info['filters'] as $key => $list) {
                if (is_string($list)) {
                    $info['filters'][$key] = array($list);
                }
            }
            
            // save the filters
            $this->_filters[$name] = $info['filters'];
        }
    }
    
    /**
     * 
     * Sets multiple acceptable options. Appends if they do not exist.
     * 
     * @param array $list Argument information as array(name => info), where
     * each info value is an array like Solar_Getopt::$_default.
     * 
     * @return void
     * 
     */
    public function setOptions($list)
    {
        if (! empty($list)) {
            foreach ($list as $name => $info) {
                $this->setOption($name, $info);
            }
        }
    }
    
    /**
     * 
     * Populates the options with values from $argv.
     * 
     * For a given option on the command line, these values will result:
     * 
     * `--foo-bar`
     * : `'foo-bar' => true`
     * 
     * `--foo-bar=baz`
     * : `'foo-bar' => 'baz'`
     * 
     * `--foo-bar="baz dib zim"`
     * : `'foo-bar' => 'baz dib zim'`
     * 
     * `-s`
     * : `'s' => true`
     * 
     * `-s dib`
     * : `'s' => 'dib'`
     * 
     * `-s "dib zim gir"`
     * : `'s' => 'dib zim gir'`
     * 
     * Short-option clusters are parsed as well, so that `-fbz` will result
     * in `array('f' => true, 'b' => true, 'z' => true)`.  Note that you cannot
     * pass parameters to an option in a cluster.
     * 
     * If an option is not defined, it will not be populated.
     * 
     * Options values are stored under the option key name, not
     * the short- or long-format version of the option.  For example, an option
     * named 'foo-bar' with a short-form of 'f' will be stored under 'foo-bar'.
     * This helps deconflict between long- and short-form aliases.
     * 
     * @param array $argv The argument values passed on the command line.  If
     * empty, will use $_SERVER['argv'] after shifting off its first element.
     * 
     * @return void
     * 
     */
    public function populate($argv = null)
    {
        // get the command-line arguments
        if ($argv === null) {
            $argv = $this->_request->argv();
            array_shift($argv);
        } else {
            $argv = (array) $argv;
        }
        
        // hold onto the argv source
        $this->_argv = $argv;
        
        // reset values to defaults
        $this->_values = array();
        foreach ($this->options as $name => $info) {
            $this->_values[$name] = $info['value'];
        }
        
        // flag to say when we've reached the end of options
        $done = false;
        
        // shift each element from the top of the $argv source
        while (true) {
            
            // get the next argument
            $arg = array_shift($this->_argv);
            if ($arg === null) {
                // no more args, we're done
                break;
            }
            
            // after a plain double-dash, all values are numeric (not options)
            if ($arg == '--') {
                $done = true;
                continue;
            }
            
            // if we're reached the end of options, just add to the numeric
            // values.
            if ($done) {
                $this->_values[] = $arg;
                continue;
            }
            
            // long, short, or numeric?
            if (substr($arg, 0, 2) == '--') {
                // long
                $this->_values = array_merge(
                    $this->_values,
                    (array) $this->_parseLong($arg)
                );
            } elseif (substr($arg, 0, 1) == '-') {
                // short
                $this->_values = array_merge(
                    $this->_values,
                    (array) $this->_parseShort($arg)
                );
            } else {
                // numeric
                $this->_values[] = $arg;
            }
        }
    }
    
    /**
     * 
     * Applies validation and sanitizing filters to the values.
     * 
     * @return bool True if all values are valid, false if not.
     * 
     */
    public function validate()
    {
        // reset previous invalidations
        $this->_invalid = array();
        
        // note that we use &$val here, which allows sanitizing methods to
        // work directly with the value.
        foreach ($this->_values as $key => &$val) {
            
            // does the option name exist?  (might not for numeric options)
            if (empty($this->options[$key])) {
                continue;
            }
            
            // setup for 'require' on parameter values
            $require = $this->options[$key]['require'];
            $this->_filter->setRequire($require);
            
            // is a value required for the option?
            if ($require && ! $this->_filter->validateNotBlank($val)) {
                // value was blank, that means it is invalid.
                // other validations will also be processed, meaning that their
                // messages will override this one.
                $this->_invalid[$key] = $this->locale('VALIDATE_NOT_BLANK');
            }
            
            // are there other filters for this option?
            if (empty($this->_filters[$key])) {
                // no filters, skip it
                continue;
            }
            
            // apply other filters
            foreach ($this->_filters[$key] as $params) {
                
                // take the method name off the top of the params ...
                $method = array_shift($params);
                
                // ... and put the value in its place.
                array_unshift($params, $val);
                
                // call the filtering method
                $result = call_user_func_array(
                    array($this->_filter, $method),
                    $params
                );
                
                // did the filter sanitize, or did it validate?
                $type = strtolower(substr($method, 0, 8));
                
                // what to do with the result?
                if ($type == 'sanitize') {
                    // retain the sanitized value
                    $val = $result;
                } elseif ($type == 'validate' && ! $result) {
                    // a validation method failed; use the method name as
                    // the locale translation key, converting from camelCase
                    // to camel_Case, then to CAMEL_CASE.
                    $tmp = preg_replace('/([a-z])([A-Z])/', '$1_$2', $method);
                    $tmp = strtoupper($tmp);
                    $this->_invalid[$key] = $this->_filter->locale($tmp);
                    // no more validations on this key
                    break;
                }
            }
        }
        
        // if there were any invalids, keep them and return false
        if ($this->_invalid) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Returns a list of invalid options and their error messages (if any).
     * 
     * @return array
     * 
     */
    public function getInvalid()
    {
        return $this->_invalid;
    }
    
    /**
     * 
     * Returns the populated option values.
     * 
     * @return array
     * 
     */
    public function values()
    {
        return $this->_values;
    }
    
    /**
     * 
     * Parse a long-form option.
     * 
     * @param string $arg The $argv element, e.g. "--foo" or "--bar=baz".
     * 
     * @return array An associative array where the key is the option name and
     * the value is the option value.
     * 
     */
    protected function _parseLong($arg)
    {
        // strip the leading "--"
        $arg = substr($arg, 2);
        
        // find the first = sign
        $eqpos = strpos($arg, '=');
        
        // get the key for name lookup
        if ($eqpos === false) {
            $key = $arg;
            $value = null;
        } else {
            $key = substr($arg, 0, $eqpos);
            $value = substr($arg, $eqpos+1);
        }
        
        // is this a recognized option?
        $name = $this->_getOptionName('long', $key);
        if (! $name) {
            return;
        }
        
        // was a value specified with equals?
        if ($eqpos !== false) {
            // parse the value for the option param
            return $this->_parseParam($name, $value);
        }
        
        // value was not specified with equals;
        // is a param needed at all?
        $info = $this->options[$name];
        if (! $info['param']) {
            // defined as not-needing a param, treat as a flag.
            return array($name => true);
        }
        
        // the option was defined as needing a param (required or optional),
        // but there was no equals-sign.  this means we need to look at the
        // next element for a possible param value.
        // 
        // get the next element from $argv to see if it's a param.
        $value = array_shift($this->_argv);
        
        // make sure the element not an option itself.
        if (substr($value, 0, 1) == '-') {
            
            // the next element is an option, not a param.
            // this means no param is present.
            // put the element back into $argv.
            array_unshift($this->_argv, $value);
            
            // was the missing param required?
            if ($info['param'] == 'required') {
                // required but not present
                return array($name => null);
            } else {
                // optional but not present, treat as a flag
                return array($name => true);
            }
        }
        
        // parse the parameter for a required or optional value
        return $this->_parseParam($name, $value);
    }
    
    /**
     * 
     * Parse the parameter value for a named option.
     * 
     * @param string $name The option name.
     * 
     * @param string $value The parameter.
     * 
     * @return array An associative array where the option name is the key,
     * and the parsed parameter is the value.
     * 
     */
    protected function _parseParam($name, $value)
    {
        // get info about the option
        $info = $this->options[$name];
        
        // is the value blank?
        if (trim($value) == '') {
            // value is blank. was it required for the option?
            if ($info['param'] == 'required') {
                // required but blank.
                return array($name => null);
            } else {
                // optional but blank, treat as a flag.
                return array($name => true);
            }
        }
        
        // param was present and not blank.
        return array($name => $value);
    }
    
    /**
     * 
     * Parse a short-form option (or cluster of options).
     * 
     * @param string $arg The $argv element, e.g. "-f" or "-fbz".
     * 
     * @param bool $cluster This option is part of a cluster.
     * 
     * @return array An associative array where the key is the option name and
     * the value is the option value.
     * 
     */
    protected function _parseShort($arg, $cluster = false)
    {
        // strip the leading "-"
        $arg = substr($arg, 1);
        
        // re-process as a cluster?
        if (strlen($arg) > 1) {
            $data = array();
            foreach (str_split($arg) as $key) {
                $data = array_merge(
                    $data,
                    (array) $this->_parseShort("-$key", true)
                );
            }
            return $data;
        }
        
        // is the option defined?
        $name = $this->_getOptionName('short', $arg);
        if (! $name) {
            // not defined
            return;
        } else {
            // keep the option info
            $info = $this->options[$name];
        }
        
        // are we processing as part of a cluster?
        if ($cluster) {
            // is a param required for the option?
            if ($info['param'] == 'required') {
                // can't get params when in a cluster.
                return array($name => null);
            } else {
                // param was optional or not needed, treat as a flag.
                return array($name => true);
            }
        }
        
        // not processing as part of a cluster.
        // does the option need a param?
        if (! $info['param']) {
            // defined as not-needing a param, treat as a flag.
            return array($name => true);
        }
        
        // the option was defined as needing a param (required or optional).
        // get the next element from $argv to see if it's a param.
        $value = array_shift($this->_argv);
        
        // make sure the element not an option itself.
        if (substr($value, 0, 1) == '-') {
            
            // the next element is an option, not a param.
            // this means no param is present.
            // put the element back into $argv.
            array_unshift($this->_argv, $value);
            
            // was the missing param required?
            if ($info['param'] == 'required') {
                // required but not present
                return array($name => null);
            } else {
                // optional but not present, treat as a flag
                return array($name => true);
            }
        }
        
        // parse the parameter for a required or optional value
        return $this->_parseParam($name, $value);
    }
    
    /**
     * 
     * Gets an option name from its short or long format.
     * 
     * @param string $type Look in the 'long' or 'short' key for option names.
     * 
     * @param string $value The long or short format of the option name.
     * 
     * @return string
     * 
     */
    protected function _getOptionName($type, $value)
    {
        foreach ($this->options as $name => $info) {
            if ($info[$type] == $value) {
                return $name;
            }
        }
    }
}