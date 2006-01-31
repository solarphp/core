<?php
/**
 * 
 * Class to collect and return localization strings.
 * 
 * @category Solar
 * 
 * @package Solar_Locale
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
 * Class to collect and return localization strings.
 * 
 * @category Solar
 * 
 * @package Solar_Locale
 * 
 */
class Solar_Locale extends Solar_Base {
    
    /**
     * 
     * User-provided configuration values.
     * 
     */
    protected $_config = array(
        'locale' => 'Solar/Locale/',
        'code'   => 'en_US',
    );
    
    /**
     * 
     * Array of translated strings organized by class and key.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_string = array();
    
    /**
     * 
     * Constructor.
     * 
     * @access public
     * 
     */
    public function __construct($config = null)
    {
        // basic construction
        parent::__construct();
        
        // reset the locale code and load the baseline strings
        $this->reset($this->_config['code']);
    }
    
    /**
     * 
     * Sets a new locale code and clears current strings.
     * 
     * @access public
     * 
     * @param $code string The new locale code.
     * 
     */
    public function reset($code)
    {
        $this->_config['code'] = $code;
        $this->_string = array();
        $this->load('Solar', $this->_config['locale']);
    }
    
    public function setCode($code)
    {
        trigger_error(
            "Solar_Locale::setCode() is deprecated, use Solar_Locale::reset() instead",
            E_USER_NOTICE
        );
        return $this->reset($code);
    }
    
    
    /**
     * 
     * Gets the locale code.
     * 
     * @access public
     * 
     * @return string The current local code.
     * 
     */
    public function code()
    {
        return $this->_config['code'];
    }
    
    /**
     * 
     * Loads class locale strings from a PHP array file.
     * 
     * @access public
     * 
     * @param string $class The class for the translation key, e.g.
     * 'Solar_Test_Example'.
     * 
     * @param string $dir The directory where the translation PHP
     * array files are located.  Will search this directory for a
     * file named after the locale code, ending in '.php'.  E.g., if
     * $this->_config['code'] is 'en_US' and $dir is
     * 'Solar/Test/Example/', load() will look for a file at the path
     * 'Solar/Test/Example/en_US.php'.
     * 
     * @return boolean True if the strings were loaded, false if not.
     * 
     */
    public function load($class, $dir)
    {
        // create the file name
        $dir = Solar::fixdir($dir);
        $file = $dir . $this->_config['code'] . '.php';
        
        // could we find the file?
        if (Solar::fileExists($file)) {
            $this->_string[$class] = (array) include $file;
            return true;
        } else {
            // could not find file.
            // fail silently, as it's often the case that the
            // translation file simply doesn't exist.
            $this->_string[$class] = array();
            return false;
        }
    }

    /**
     * 
     * Checks to see if strings have been loaded for a given class.
     * 
     * @access public
     * 
     * @param string $class The class for the translation key, e.g.
     * 'Solar_Model_Talk'.
     * 
     * @return bool True if strings are loaded, false if not.
     * 
     */
    public function loaded($class)
    {
        return array_key_exists($class, $this->_string);
    }
    
    /**
     * 
     * Returns the locale string for a class and key.
     * 
     * @access public
     * 
     * @param string $class The class for the translation key, e.g.
     * 'Solar_Model_Comments'.
     * 
     * @param string $key The translation key to find.
     * 
     * @param int|float $num If set to 1, returns the singluar form of
     * the translated key.  Otherwise, returns the plural form of
     * the translated key (if one exists, else singular).
     * 
     * @return string The translated key, or the key itself if no
     * translated string was found.
     * 
     */
    public function string($class, $key, $num = 1)
    {
        // if the key does not exist for the class,
        // return the key itself.
        if (! isset($this->_string[$class][$key])) {
            return $key;
        }
        
        // get the translation of the key and force
        // to an array.
        $string = (array) $this->_string[$class][$key];
        
        // return the number-appropriate version of the
        // translated key, if multiple values exist.
        if ($num != 1 && isset($string[1])) {
            return $string[1];
        } else {
            return $string[0];
        }
    }
}
?>
