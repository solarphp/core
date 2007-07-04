<?php
/**
 * 
 * Base library for console-based applications
 * 
 * @category Solar
 * 
 * @package Solar_Console_Getopt
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Base library for console-based applications. 
 * 
 * Many thanks to the following sources of inspiration:
 * 
 * * Paul M. Jones, Solar_Form
 * * Bertrand Mansion, PEAR::Console_Getargs
 * * Stefan Walk, PEAR::Console_Color
 * 
 * @category Solar
 * 
 * @package Solar_Console_Getopt
 * 
 */
class Solar_Console_Getopt extends Solar_Base {

    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `single_dash_ok`
     * : Boolean switch to allow/disallow single dashs in 
     * front of long options. Default: false
     * 
     * `auto_assign_shorts`
     * : Boolean switch to automatically assign values that follow
     * single-dash arguments to the switch that preceeded it. 
     * Default: true
     * 
     * `sub_command_factory`
     * : Boolean switch to allow treating the first parameter as a sub
     * commmand. Enables automatic running of applications, for example:
     * 
     *    sungrazr generator [args...]
     * 
     * ... would automatically run Solar_Console_GetoptApp_Generator with 
     * any args that came *after* 'generator'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Console_Getopt = array(
        'single_dash_ok'        => true,
        'auto_assign_shorts'    => true,
        'sub_command_factory'   => false,
    );

    /**
     * 
     * The array of acceptable arguments.
     * 
     * The `$arguments` array contains all arguments accepted by the
     * application, including their types, default values, descriptions,
     * requirements, and validation callbacks.
     * 
     * In general, you should not try to set $arguments yourself; 
     * instead, use Solar_Console_Getopt_Args::setArg() and/or
     * Solar_Console_Getopt_Args::setArgs()
     *
     */
    public $arguments = array();
    
    /**
     * 
     * Default settings for each argument.
     * 
     * Keys are ...
     * 
     * `name`
     * : (string) The name of the argument. If the string contains a pipe
     * character (|), the string preceeding the pipe will be the long version
     * of the name, and the string following the pipe will be the short
     * version of the name.
     * 
     * `short`
     * : (string) The short name of the argument. Can be used instead of the
     * combo pipe format in the `name` setting.
     * 
     * `type`
     * : (string) The type of argument ('option' or 'switch')
     * 
     * `require`
     * : (bool) Whether or not the argument is required
     * 
     * `value`
     * : (mixed) The default value for the option
     * 
     * `descr`
     * : (string) A description of the option/switch, which will 
     * be displayed if help text is requested.
     * 
     * `valid`
     * : (array) An array of validation parameters for Solar_Valid
     * 
     * `filter`
     * : (array) An array of Solar_Filter callbacks to apply to the value
     * 
     */
    protected $_default = array(
        'name'      => null,
        'short'     => null,
        'type'      => 'option',
        'require'   => false,
        'value'     => null,
        'descr'     => null,
        'valid'     => array(),
        'filter'    => array(),
    );
        
    /**
     * 
     * Arguments and properties parsed from the command line
     * 
     * @var array
     * 
     */
    protected $_args;

    /**
     * 
     * The array of pre-filters for the arguments.
     * 
     * @var array 
     * 
     */
    protected $_filter = array();
    
    /**
     * 
     * The array of validations for the arguments.
     * 
     * @var array
     * 
     */
    protected $_valid = array();

    /**
     * 
     * A Solar_Filter object for internal filtering needs.
     * 
     * @var Solar_Filter
     * 
     */
    protected $_obj_filter;
    
    /**
     * 
     * A Solar_Valid object for internal validation needs.
     * 
     * @var Solar_Valid
     * 
     */
    protected $_obj_valid;

    /**
     *
     * Request environment details
     *
     * @var Solar_Request
     *
     */
    protected $_request;
    
    /**
     * 
     * Shorthand type reference
     * 
     * @var array
     * 
     */
    protected $_types;
    
    /**
     * 
     * Current type of argument being examined
     * 
     * @var string
     * 
     */
    protected $_type;
    
    /**
     * 
     * Flag indicating whether the current argument appears
     * to be a compound "multishort" argument
     * 
     * @var boolean
     * 
     */
    protected $_multishort;
    
    /**
     * 
     * Flag to indicate that at least some of the $_args array has been
     * filled.
     * 
     * @var boolean
     * 
     */
    protected $_populated;
    
    /**
     * 
     * Command (or script name) used to call the script whose arguments 
     * are being parsed.
     * 
     * @var string
     * 
     */
    public $command;
    
    /**
     * 
     * Processed version of command string to be used in help output.
     * 
     * @var string
     * 
     */
    public $command_help;
    
    /**
     * 
     * Sub-command called under a sub_command_factory scenario.
     * 
     * @var string
     * 
     */
    public $sub_command;
        
    /**
     * 
     * Array of format conversions for use of a variety of pre-set console
     * style combinations.
     * 
     * Based on ANSI VT100 Color/Style Codes, according to the VT100 
     * User Guide[1] and the ANSI/VT100 Terminal Control[2] reference.
     * 
     * [1]: http://vt100.net/docs/vt100-ug
     * [2]: http://www.termsys.demon.co.uk/vtansi.htm
     * 
     * @var array
     * 
     */
    public $style_map = array(
        // color, normal weight
        '%k'    => "\033[30m",      // black
        '%r'    => "\033[31m",      // red
        '%g'    => "\033[32m",      // green
        '%y'    => "\033[33m",      // yellow
        '%b'    => "\033[34m",      // blue
        '%m'    => "\033[35m",      // magenta/purple
        '%p'    => "\033[35m",      // magenta/purple
        '%c'    => "\033[36m",      // cyan/light blue
        '%w'    => "\033[37m",      // white
        '%n'    => "\033[0m",       // reset to terminal default
        // color, bold
        '%K'    => "\033[30;1m",    // black, bold
        '%R'    => "\033[31;1m",    // red, bold
        '%G'    => "\033[32;1m",    // green, bold
        '%Y'    => "\033[33;1m",    // yellow, bold
        '%B'    => "\033[34;1m",    // blue, bold
        '%M'    => "\033[35;1m",    // magenta/purple, bold
        '%P'    => "\033[35;1m",    // magenta/purple, bold
        '%C'    => "\033[36;1m",    // cyan/light blue, bold
        '%W'    => "\033[37;1m",    // white, bold
        '%N'    => "\033[0;1m",     // terminal default, bold
        // background color
        '%0'    => "\033[40m",      // black background
        '%1'    => "\033[41m",      // red background
        '%2'    => "\033[42m",      // green background
        '%3'    => "\033[43m",      // yellow background
        '%4'    => "\033[44m",      // blue background
        '%5'    => "\033[45m",      // magenta/purple background
        '%6'    => "\033[46m",      // cyan/light blue background
        '%7'    => "\033[47m",      // white background
        // assorted style shortcuts
        '%F'    => "\033[5m",       // blink/flash
        '%_'    => "\033[5m",       // blink/flash
        '%U'    => "\033[4m",       // underline
        '%I'    => "\033[7m",       // reverse/inverse
        '%*'    => "\033[1m",       // bold
        '%d'    => "\033[2m",       // dim        
    );
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // Command line arguments must be available.
        if (!isset($_SERVER['argv']) || !is_array($_SERVER['argv'])) {
            throw $this->_exception(
                'ERR_ARGV_UNAVAILABLE'
            );
        }
        
        // "real" contruction
        parent::__construct($config);

        // Set up type reference list
        $typeref = array(
            '-'     => 'short',
            '--'    => 'long',
        );
        if ($this->_config['single_dash_ok']) {
            // treat short multi-char args like longs
            $typeref['-'] = 'long';
        }
        $this->_types = $typeref;
        
        // Set up request, validator and filter objects
        $this->_request = Solar::factory('Solar_Request');
        $this->_obj_filter = Solar::factory('Solar_Filter');
        $this->_obj_valid = Solar::factory('Solar_Valid');
        
    }

    // -----------------------------------------------------------------
    // 
    // Argument-management methods
    // 
    // -----------------------------------------------------------------

    /**
     * 
     * Sets one acceptable argument. 
     * 
     * @param string $name The argument name to set or add; overrides 
     * $info['name'].
     * 
     * @param array $info Argument information using the same keys 
     * as Solar_Console_Getopt::$_default.
     * 
     * @return void
     * 
     */
    public function setArg($name, $info)
    {
        // Prepare the name's long and short versions
        $name = $this->_prepareName($name);
        $short = null;
        if (is_array($name)) {
            $short = $name['short'];
            $name = $name['long'];
        }
        
        // prepare the argument info
        $info = array_merge($this->_default, $info);
        
        // double-check that we're catching short option
        if ($info['short'] !== null) {
            $short = $info['short'];
        }
        
        // convert string format of require
        if (is_string($info['require'])) {            
            if ($info['require'] == 'true') {
                $info['require'] = true;
            } else {
                $info['require'] = false;
            }
        }
        
        // forcibly cast each of the keys in the arguments array
        $this->arguments[$name] = array(
            'name'      =>          $name,
            'short'     =>          $short,
            'type'      => (string) $info['type'],
            'require'   => (bool)   $info['require'],
            'value'     =>          $info['value'],
            'descr'     => (string) $info['descr'],            
        );
        
        // add the filters
        if (! empty($info['filter'])) {
            foreach ( (array) $info['filter'] as $tmp) {
                $this->_filter[$name][] = $tmp;
            }
        }
        
        // add validations
        if (! empty($info['valid'])) {
            
            foreach ( (array) $info['valid'] as $tmp) {
                
                // make sure $tmp is an array
                settype($tmp, 'array');
                
                // shift the name onto the top of $tmp
                array_unshift($tmp, $name);
                
                // add the validation to the argument
                call_user_func_array(
                    array($this, 'addValid'),
                    $tmp
                );
                
            }
            
        }
        
        // Set reference for short version
        //if ($short !== null) {
        //    $this->arguments[$short] = &$this->arguments[$name];
        //}
        
    }

    /**
     * 
     * Sets multiple acceptable arguments. Appends if they do not exist.
     * 
     * @param array $list Argument information as array(name => info), where
     * each info value is an array like Solar_Console_Getopt::$_default.
     * 
     * @return void
     * 
     */
    public function setArgs($list)
    {
        if (! empty($list)) {
            foreach ($list as $name => $info) {
                $this->setArg($name, $info);
            }
        }
    }

    /**
     * 
     * Adds a Solar_Filter method callback for an argument.
     * 
     * All pre-filters are applied via 
     * Solar_Filter::multiple() and should conform to the 
     * specifications for that method.
     * 
     * All parameters after $method are treated as added parameters
     * for the Solar_Filter method call.
     * 
     * @param string $name The argument name. Use the long version if the
     * argument supports long and short.
     * 
     * @param string $method Solar_Filter method or PHP function to use
     * for filtering.
     * 
     * @return void
     * 
     */
    public function addFilter($name, $method) 
    {
        // Get the arguments, drop the element name
        $args = func_get_args();
        array_shift($args);

        $this->_filter[$name][] = $args;
    }

    /**
     * 
     * Adds a Solar_Valid method callback as a validation for an argument.
     * 
     * @param string $name The argument name. Use the long version if the
     * argument supports long and short.
     * 
     * @param string $method The Solar_Valid callback method.
     * 
     * @param string $message The feedback message to use if validation fails.
     * 
     * @return void
     * 
     */
    public function addValid($name, $method, $message = null)
    {
        // get the arguments, drop the element name
        $args = func_get_args();

        $name = array_shift($args);
        
        // add a default validation message (args[0] is the method,
        // args[1] is the message)
        if (empty($args[1]) || trim($args[1]) == '') {
            
            // see if we have an method-specific validation message
            $key = 'VALID_' . strtoupper($method);
            $args[1] = $this->locale($key);
            
            // if the message is the same as the key,
            // there was no method-specific validation
            // message.  revert to the generic default.
            if ($key == $args[1]) {
                $args[1] = $this->locale('ERR_INVALID');
            }
        }
        
        // add to the validation array
        $this->_valid[$name][] = $args;
    }

    /**
     * 
     * Loads the arguments/options that have been set into logical
     * groupings.
     * 
     * Argument parsing follows this convention:
     * 
     * --some-long-option
     *   Args array will look like:
     *   $args['long']['some-long-option'] = true;
     * 
     * --long-option=hasValue
     *   Args array will look like:
     *   $args['long']['some-long-option'] = 'hasValue';
     * 
     * -s
     *   Args array will look like:
     *   $args['short']['s'] = true;
     * 
     * -s value
     *   Args array will look like:
     *   $args['short']['s'] = 'value';
     *   ... unless the 'auto_assign_shorts' config option is false.
     * 
     * -svkd
     *   Args array will look like:
     *   $args['short'] = array(
     *     's' => true,
     *     'v' => true,
     *     'k' => true,
     *     'd' => true,
     *   );
     *  ... unless this is a valid option with single_dash_ok, in which case:
     *  $args['long']['svkd'] = true;
     *   
     *  -svkd=foo
     *   $args['short'] = array(
     *     's' => true,
     *     'v' => true,
     *     'k' => true,
     *     'd' => 'foo',
     *   );
     * 
     * 
     * @return void
     * 
     */
    public function populate() 
    {
        // reset args to ensure we avoid double-population
        $this->_args = array(
            'long' => array(),
            'short' => array(),
            'parameters' => array()
        );
        
        $argv = $this->_request->server['argv'];
        
        // Drop the first argument if it's not an option, because it's 
        // the script name
        if (substr($argv[0], 0, 1) != '-') {
            $cmd = array_shift($argv);
            $this->_setCommandName($cmd);
        }
        
        // If the next argument isn't an option, and we're acting as a 
        // subcommand factory, validate before continuing
        if (substr($argv[0], 0, 1) != '-' && 
            $this->_config['sub_command_factory'] === true) {

            $subcmd = array_shift($argv);
            $this->sub_command = trim($subcmd);        
        }

        $previous_arg = null;
        $no_more_options = false;
        
        foreach ($argv as $arg) {
            
            // Follow standard shell conventions
            if ($arg == '--') {
                $no_more_options = true;
                continue;
            }
            if ($no_more_options) {
                $this->_args['parameters'][] = $arg;
                continue;
            }
                        
            // reset baseline
            $len                = strlen($arg);
            $this->_type        = null;
            $this->_multishort  = false;
            $parsed             = array();
            
            // long opt?
            if ($len > 2) { 
                if (substr($arg, 0, 2) == '--') {
                    $this->_type = '--';
                }
            }
            
            // short opt?
            if (substr($arg, 0, 1) == '-' && substr($arg, 1, 1) != '-') {
                $this->_type = '-';
                
                if ($len > 2 && !$this->_config['single_dash_ok']) {
                    // more than one short option being passed at once
                    $this->_multishort = true;
                }
            }

            // Neither a short or long opt?
            // Check if previous was a short option, and if it's 
            // expecting a value
            if ($this->_type === null) {
                
                if ($previous_arg != null &&
                    $previous_arg['dash'] == '-' && 
                    $this->_config['auto_assign_shorts']) {
                        
                    $bucket = $this->_types[$previous_arg['dash']];
                        
                    $this->_args[$bucket][$previous_arg['value']] = $arg;
                    continue;
                    
                } else {
                        
                    // Otherwise, throw in parameters
                    $this->_args['parameters'][] = $arg;
                    $previous_arg = array('type' => 'parameter', 
                                          'value' => $arg);
                    continue;
                    
                }
            }
                        
            // Check for value assignment
            $parsed = $this->_assignValues($arg);

            // File this pair
            $type = $this->_types[$this->_type];
            
            
            $this->_args[$type] = array_merge($this->_args[$type], $parsed);
            
            // set previous arg for next pass
            $previous_arg = array(
                'type'  => $type, 
                'value' => key($parsed),
                'dash'  => $this->_type,
            );

        }
        
        // Set the values we've collected in the arguments array
        //$this->_setArgumentValues();
        
        // Set the populated flag
        $this->_populated = true;
    }
    
    /**
     * 
     * Performs filtering and validation on each argument.
     * 
     * Updates the feedback keys for each argument that fails validation.
     * Values are either pulled from the command line, or from the configured
     * argument's value default.
     * 
     * @return bool True if all arguments are valid, false if not
     * 
     */
    public function validate()
    {
        if (empty($this->_populated)) {
            $this->populate();
        }

        // Set the values we've collected in the arguments array
        $this->_setArgumentValues();
                
        // loop through each argument to filter
        foreach ($this->_filter as $name => $filters) {
            $value = $this->arguments[$name]['value'];
            $this->arguments[$name]['value'] = $this->_obj_filter->multiple(
                $value, $filters
            );
        }
        
        // Valid unless proven otherwise
        $validated = true;
        
        // loop through each argument to be validated
        foreach ($this->_valid as $name => $list) {
            
            // loop through each validation for the argument
            foreach ($list as $vargs) {
                
                // the name of the Solar_Valid method
                $method = array_shift($vargs);
                
                // the text of the error message
                $feedback = array_shift($vargs);
                
                // config is now the remaining bits, put the value
                // on top of it.
                array_unshift($vargs, $this->arguments[$name]['value']);
                
                // Required value or not?
                $blank_ok = true;
                if ($this->arguments[$name]['require'] === true) {
                    $blank_ok = false;
                }
                array_push($vargs, $blank_ok);
                
                // Call the appropriate Solar_Valid method
                $result = call_user_func_array(
                    array($this->_obj_valid, $method),
                    $vargs
                );
                
                // was it valid?
                if (! $result) {
                    // no, add the feedback message
                    $validated = false;
                    $this->arguments[$name]['feedback'][] = $feedback;
                    $this->arguments[$name]['status'] = false;
                } else {
                    $this->arguments[$name]['status'] = true;
                }
                
            } // inner loop
            
        } // outer loop
        
        if ($validated && !empty($this->_config['success'])) {
            $this->feedback = array($this->_config['success']);            
        } else {
            if (!empty($this->_config['failure'])) {
                $this->feedback = array($this->_config['failure']);
            }
        }
        
        $this->_status = $validated;
        return $validated;        
    }
    
    /**
     * 
     * Returns the validated arguments as a Solar_Console_Getopt_Args object.
     * 
     * @param bool $raw Return the object with what was passed, prior to
     * validation.
     * 
     * @return object
     * 
     */
    public function args($raw = false)
    {
        // Creating the struct and using Solar_Struct::load() avoids 
        // creating a temporary array representing the key => value pairs
        // of the validated arguments.
        
        $args = new stdClass();
        
        if (! $raw) {
            foreach ($this->arguments as $arg) {
                $prop = str_replace('-', '_', $arg['name']);
                $args->$prop = $arg['value'];
            }
            $args->parameters = $this->_args['parameters'];
            return $args;
        }
        
        // raw version
        $args->long = $this->_args['long'];
        $args->short = $this->_args['short'];
        $args->parameters = $this->_args['parameters'];
        return $args;
    }
    
    /**
     * Returns leftover parameters after all arguments were processed
     * 
     * @return array Array of values post-argument handling
     * 
     */
    public function parameters()
    {
        return $this->_args['parameters'];
    }

    /**
     * 
     * Return true/false depending on whether or not the sub_command_factory
     * option is set.
     * 
     * @return boolean
     * 
     */
    public function isSubCommandFactory()
    {
        return $this->_config['sub_command_factory'];
    }

    /** 
     * 
     * Return default argument array for merging with arrays generated in 
     * other areas.
     * 
     * @return array
     * 
     */
    public function getDefaultArgStructure()
    {
        return $this->_default;
    }

    // -----------------------------------------------------------------
    // 
    // Console output management methods
    // 
    // -----------------------------------------------------------------

    /**
     * 
     * Looks up locale strings based on a key. Also applies console 
     * style/colorization conversions based on format strings found in
     * returned locale string.
     * 
     * @param string $key The key to get a locale string for.
     * 
     * @param string $num If 1, returns a singular string; otherwise, returns
     * a plural string (if one exists).
     * 
     * @param array $replace An array of replacement values for the string, to
     * be applied using [[php::vsprintf() | ]].
     * 
     * @return string The locale string, or the original $key if no
     * string found.
     * 
     */
    public function locale($key, $num = 1, $replace = null)
    {
        $string = Solar::$locale->fetch(get_class($this), $key, $num, $replace);
        $string = str_replace(
            array_keys($this->style_map),
            $this->style_map,
            $string
        );
        return $string;        
    }    

    // -----------------------------------------------------------------
    //
    // Support methods
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Prep command name for help output
     * 
     * @param string $cmd Command name pulled from $argv[0]
     * 
     * @return void
     * 
     */
    protected function _setCommandName($cmd)
    {
        $cmd = trim($cmd);

        // strip off path
        $cmd = basename($cmd);
        
        $this->command = $cmd;
        
        if (substr($this->command, -4) == '.php') {
            $this->command_help = 'php ' . $this->command;
        } else {
            $this->command_help = $this->command;
        }
        
    }
    
    /**
     * 
     * Determine how the passed argument should treated in regard to
     * assignment of values.
     * 
     * If an equal sign is present in the arg, the value of what follows the
     * equal sign will be assigned to what precedes it. If no equal sign is
     * present, the argument will be assigned a boolean true value.
     * 
     * @param string $arg Argument to examine
     * 
     * @return array Array of key => value determinations made on the passed
     * argument.
     * 
     * @todo Once Solar has a PHP 5.2.0 requirement, use array_fill_keys where 
     * noted below.
     * 
     */
    protected function _assignValues($arg)
    {

        $typelen = strlen($this->_type);
        $equalpos = strpos($arg, '=');
        
        if ($equalpos === false) {
            

            // No value assignment
            $key = substr($arg, $typelen);

            if ($this->_multishort) {
                $key = str_split($key);
                
                // @todo: replace with array_fill_keys someday
                $parsed = array_combine(
                    $key,
                    array_fill(0, sizeof($key), true)
                );
                
            } else {
                $parsed[$key] = true;
            }
            
        } else {
            
            // value assignment
            $key = substr($arg, $typelen, $equalpos - $typelen);
            $val = substr($arg, $equalpos + 1);
                        
            if ($this->_multishort) {
                
                $lastkey = substr($key, -1);
                
                $key = str_split($key);
                
                // @todo: replace with array_fill_keys someday
                $parsed = array_combine(
                    $key,
                    array_fill(0, sizeof($key), true)
                );
                
                $parsed[$lastkey] = $val;
                
            } else {
                $parsed[$key] = $val;
            }
            
        }
        
        // return the parsed array
        return $parsed;
    }

    /**
     * 
     * Sets values from raw _args array in public $arguments for validation
     * and filtering. Also performs short-to-long option mapping.
     * 
     * @return void
     * 
     */
    protected function _setArgumentValues()
    {
        foreach ($this->arguments as $name => $arg) {
            if (!empty($arg['short'])) {
                
                // If single dashes for long options are ok, 
                // short options are put in the same bucket as long options
                $bucket = 'short';
                if ($this->_config['single_dash_ok']) {
                    $bucket = 'long';
                }
                
                if (isset($this->_args[$bucket][$arg['short']])) {
                    $arg['value'] = $this->_args[$bucket][$arg['short']];
                }
            }
            if (!empty($arg['name'])) {
                if (isset($this->_args['long'][$arg['name']])) {
                    $arg['value'] = $this->_args['long'][$arg['name']];
                }
            }
            $this->arguments[$name] = $arg;
        }
        
    }

    /**
     * 
     * Split a name into long and short versions if a pipe is present.
     * 
     * @param string $name Name of the argument
     * 
     * @return mixed Array if pipe was present, trimmed $name if not
     * 
     */
    protected function _prepareName($name)
    {
        $name = trim($name);
        
        $pos = strpos($name, '|');
        
        if ($pos !== false) {
            $combo = array();
            
            $combo['long'] = substr($name, 0, $pos);
            $combo['short'] = substr($name, $pos + 1);

            return $combo;
        }
        
        return $name;
    }

}