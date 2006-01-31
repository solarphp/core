<?php
/**
 * 
 * Abstract base class for all Solar objects.
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
 * Abstract base class for all Solar objects.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
abstract class Solar_Base {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are:
     * 
     * locale => The directory where locale string files for this class
     * are located.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'locale' => null,
    );
    
    /**
     * 
     * Constructor.
     * 
     * @access public
     * 
     * @param mixed $config If array, is merged with the default $_config property array
     * and any values from the Solar.config.php file.  If string, is loaded from that
     * file and merged with values from Solar.config.php file.  If boolean false,
     * no config overrides are performed (class defaults only).
     * 
     */
    public function __construct($config = null)
    {
        $class = get_class($this);
        
        if ($config === false) {
            // don't attempt to override class defaults at all,
            // usually for testing.
        } else {
            
            // normal behavior: merge from Solar.config.php,
            // then from construction-time config.
            
            // Solar.config.php values override class defaults
            $solar = Solar::config($class, null, array());
            $this->_config = array_merge($this->_config, $solar);
            
            // load construction-time config from a file?
            if (is_string($config)) {
                $config = Solar::run($config);
            }
            
            // construction-time values override Solar.config.php
            $this->_config = array_merge($this->_config, (array) $config);
        }
        
        // auto-define the locale directory if needed
        if (empty($this->_config['locale'])) {
            // converts "Solar_Test_Example" to
            // "Solar/Test/Example/Locale/"
            $this->_config['locale'] = str_replace('_', '/', $class);
            $this->_config['locale'] .= '/Locale/';
        }
        
        // Load the locale strings.  Solar_Locale is a special case,
        // as it gets started up with Solar, and extends Solar_Base,
        // which leads to all sorts of weird recursion problems.
        if ($class != 'Solar_Locale') {
            $this->locale('');
        }
    }
    
    /**
     * 
     * Reports the API version for this class.
     * 
     * If you don't override this method, your classes will use the same
     * API version string as the Solar package itself.
     * 
     * @access public
     * 
     * @return string A PHP-standard version number.
     * 
     */
    public function apiVersion()
    {
        return '@package_version@';
    }
    
    /**
     * 
     * Provides hooks for Solar::start() and Solar::stop() on shared objects.
     * 
     * @access public
     * 
     * @param string $hook The hook to activate, typically 'start' or 'stop'.
     * 
     * @return void
     * 
     */
    public function solar($hook)
    {
        switch ($hook) {
        case 'start':
            // do nothing
            break;
        case 'stop':
            // do nothing
            break;
        }
    }
    
    /**
     * 
     * Looks up locale strings based on a key.
     * 
     * Uses the locale strings in the directory noted by $config['locale'];
     * if no such key exists, falls back to the Solar/Locale strings.
     * 
     * @access public
     * 
     * @param string $key The key to get a locale string for.
     * 
     * @param string $num If 1, returns a singular string; otherwise, returns
     * a plural string (if one exists).
     * 
     * @return string The locale string, or the original $key if no
     * string found.
     * 
     */
    public function locale($key, $num = 1)
    {
        // the shared Solar_Locale object
        $locale = Solar::shared('locale');
        
        // is a locale directory specified?
        if (empty($this->_config['locale'])) {
            // use the generic Solar locale strings
            return $locale->string('Solar', $key, $num);
        }
        
        // the name of this class
        $class = get_class($this);
        
        // do we need to load locale strings? we check for loading here
        // because the locale may have changed during runtime.
        if (! $locale->loaded($class)) {
            $locale->load($class, $this->_config['locale']);
        }
        
        // get a translation for the current class
        $string = $locale->string($class, $key, $num);
        
        // is the translation the same as the key?
        if ($string != $key) {
            // found a translation (i.e., different from the key)
            return $string;
        } else {
            // no translation found.
            // fall back to the generic Solar locale strings.
            return $locale->string('Solar', $key, $num);
        }
    }
    
    /**
     * 
     * Convenience method for returning Solar::error() with localized text.
     * 
     * @access protected
     * 
     * @param string $code The error code.
     * 
     * @param array $info An array of error-specific data.
     * 
     * @param int $level The error level constant, e.g. E_USER_NOTICE.
     * 
     * @param bool $trace Whether or not to generate a debug_backtrace().
     * 
     * @return object A Solar_Error object.
     * 
     */
    protected function _error($code, $info = array(), $level = null,
        $trace = null)
    {
        // automatic value for the class name
        $class = get_class($this);
        
        // automatic locale string for the error text
        $text = $this->locale($code);
        
        // make sure info is an array
        settype($info, 'array');
        
        // generate the object and return
        $err = Solar::error($class, $code, $text, $info, $level, $trace);
        return $err;
    }
    
    /**
     * 
     * Convenience method for pushing onto an existing Solar_Error stack.
     * 
     * @access protected
     * 
     * @param object $err An existing Solar_Error object.
     * 
     * @param string $code The error code.
     * 
     * @param array $info An array of error-specific data.
     * 
     * @param int $level The error level constant, e.g. E_USER_NOTICE.
     * 
     * @param bool $trace Whether or not to generate a debug_backtrace().
     * 
     */
    protected function _errorPush($err, $code, $info = array(), $level = null,
        $trace = null)
    {
        // automatic value for the class name
        $class = get_class($this);
        
        // automatic locale string for the error text
        $text = $this->locale($code);
        
        // make sure info is an array
        settype($info, 'array');
        
        // push the error onto the stack
        $err->push($class, $code, $text, $info, $level, $trace);
    }
    
    /**
     * 
     * Converts an exception to a Solar_Error of E_USER_ERROR severity.
     * 
     * No localization is possible.
     * 
     * @access protected
     * 
     * @param object $e An exception object.
     * 
     * @param int $level The error level constant, e.g. E_USER_NOTICE.
     * 
     * @return object A Solar_Error object.
     */
    protected function _errorException($e, $level)
    {
        $info = array(
            'type'  => get_class($e),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString()
        );
        
        $err = Solar::error(
            get_class($this),
            $e->getCode(),
            $e->getMessage(),
            $info,
            $level,
            false
        );
        
        return $err;
    }
    
    /**
     * 
     * Convenience method for returning exceptions with localized text.
     * 
     * This method attempts to automatically load and throw exceptions
     * based on the error code, falling back to generic Solar exceptions
     * when no specific exception classes exist.  For example, if a
     * class named 'Solar_Example' throws an error code 'ERR_FILE_NOT_FOUND',
     * attempts will be made to find these exception classes in this order:
     * 
     * <ol>
     * <li>Example_Exception_FileNotFound (class specific)</li>
     * <li>Solar_Exception_FileNotFound (Solar specific)</li>
     * <li>Example_Exception (class generic)</li>
     * <li>Solar_Exception (Solar generic)</li>
     * </ol>
     * 
     * The final fallback is always the Solar_Exception class.
     * 
     * @access protected
     * 
     * @param string $code The error code; does additional duty as the
     * locale string key and the exception class name suffix.
     * 
     * @param array $info An array of error-specific data.
     * 
     * @return object A Solar_Exception object.
     * 
     */
    protected function _exception($code, $info = array())
    {
        // exception configuration
        $config = array(
            'class' => get_class($this),
            'code'  => $code,
            'text'  => $this->locale($code),
            'info'  => $info,
        );
        
        // the base exception class for this class
        $base = get_class($this) . '_Exception';
        
        // drop 'ERR_' and 'EXCEPTION_' prefixes from the code
        // to get a suffix for the exception class
        $suffix = $code;
        if (substr($suffix, 0, 4) == 'ERR_') {
            $suffix = substr($suffix, 4);
        } elseif (substr($suffix, 0, 10) == 'EXCEPTION_') {
            $suffix = substr($suffix, 10);
        }
        
        // convert suffix to StudlyCaps
        $suffix = str_replace('_', ' ', $suffix);
        $suffix = ucwords(strtolower($suffix));
        $suffix = str_replace(' ', '', $suffix);
        
        // look for Class_Exception_StudlyCapsSuffix
        try {
            $obj = Solar::factory("{$base}_$suffix", $config);
            return $obj;
        } catch (Exception $e) {
            // do nothing
        }
        
        // fall back to Solar_Exception_StudlyCapsSuffix
        try {
            $obj = Solar::factory("Solar_Exception_$suffix", $config);
            return $obj;
        } catch (Exception $e) {
            // do nothing
        }
        
        // look for generic Class_Exception
        try {
            $obj = Solar::factory($base, $config);
            return $obj;
        } catch (Exception $e) {
            // do nothing
        }
        
        // final fallback to generic Solar_Exception
        return Solar::factory('Solar_Exception', $config);
    }
}
?>