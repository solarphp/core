<?php
/**
 * 
 * Manages locale strings for all Solar classes.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Base class for all Solar objects.
 */
Solar::loadClass('Solar_Base');

/**
 * 
 * Manages locale strings for all Solar classes.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
class Solar_Locale extends Solar_Base {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are ...
     * 
     * `code`
     * : (string) The default locale code to use.
     * 
     * @var array
     * 
     */
    protected $_Solar_Locale = array(
        'code' => 'en_US',
    );
    
    /**
     * 
     * Collected translation strings arranged by class and key.
     * 
     * @var array
     * 
     */
    public $trans = array();
    
    /**
     * 
     * The current locale code.
     * 
     * @var string
     * 
     */
    protected $_code = 'en_US';
    
    /**
     * 
     * Sets the locale code and clears out previous translations.
     * 
     * @param string $code A locale code, for example, 'en_US'.
     * 
     * @return void
     */
    public function setCode($code)
    {
        // set the code
        $this->_code = $code;
        
        // reset the strings
        $this->trans = array();
    }
    
    /**
     * 
     * Returns the current locale code.
     * 
     * @return string The current locale code, for example, 'en_US'.
     * 
     */
    public function getCode()
    {
        return $this->_code;
    }
    
    /**
     * 
     * Returns the translated locale string for a class and key.
     * 
     * Loads translations as needed.
     * 
     * @param string|object $spec The class name (or object) for the translation.
     * 
     * @param string $key The translation key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @return string A translated locale string.
     * 
     * @see Solar_Base::locale()
     * 
     * @see Manual::Solar/Using_locales
     * 
     */
    public function fetch($spec, $key, $num = 1)
    {
        // is the spec an object?
        if (is_object($spec)) {
            // yes, find its class
            $class = get_class($spec);
        } else {
            // no, assume the spec is a class name
            $class = (string) $spec;
        }
        
        // does the translation key exist for this class?
        // pre-empts the stack check.
        $string = $this->_trans($class, $key, $num);
        if ($string !== null) {
            return $string;
        }
        
        // find all parents of the class, including the class itself
        $parents = Solar::parents($class, true);
        
        // add the vendor namespace to the stack for vendor-wide strings,
        // and add Solar as the final fallback.
        $pos = strpos($class, '_');
        if ($pos !== false) {
            $vendor = substr($class, 0, $pos);
            $parents[] = $vendor;
            if ($vendor != 'Solar') {
                $parents[] = 'Solar';
            }
        } else {
            $parents[] = 'Solar';
        }
        
        // go through all parents and find the first matching key
        foreach ($parents as $parent) {
            
            // do we need to load locale strings for the class?
            if (! array_key_exists($parent, $this->trans)) {
                $this->_load($parent);
            }
        
            // does the key exist for the parent?
            $string = $this->_trans($parent, $key, $num);
            if ($string !== null) {
                // save it for the class so we don't need to go through the
                // stack again, and then we're done.
                $this->trans[$class][$key] = $this->trans[$parent][$key];
                return $string;
            }
        }
        
        // never found a translation, return the requested key.
        return $key;
    }
    
    /**
     * 
     * Returns an existing class/key/num string from the translation array.
     * 
     * @param string $class The translation class.
     * 
     * @param string $key The translation key.
     * 
     * @param mixed $num Helps determine if we need a singular or plural
     * translation.
     * 
     * @return string The translation string if it exists, or null if it
     * does not.
     * 
     */
    protected function _trans($class, $key, $num = 1)
    {
        if (! array_key_exists($class, $this->trans) ||
            ! array_key_exists($key, $this->trans[$class])) {
            // class or class-key does not exist
            return null;
        }
        
        // get the translation of the key and force to an array.
        $trans = (array) $this->trans[$class][$key];

        // return the number-appropriate version of the
        // translated key, if multiple values exist.
        if ($num != 1 && ! empty($trans[1])) {
            return $trans[1];
        } else {
            return $trans[0];
        }
    }
    
    /**
     * 
     * Loads the translation array for a given class.
     * 
     * @param string $class The class name to load translations for.
     * 
     * @return void
     * 
     */
    protected function _load($class)
    {
        // build the file name.  note that we use the fixdir()
        // method, which automatically replaces '/' with the
        // correct directory separator.
        $base = str_replace('_', '/', $class);
        $file = Solar::fixdir($base . '/Locale/')
              . $this->_code . '.php';

        // can we find the file?
        $target = Solar::fileExists($file);
        if ($target) {
            // put the locale values into the shared locale array
            $this->trans[$class] = (array) Solar::run($target);
        } else {
            // could not find file.
            // fail silently, as it's often the case that the
            // translation file simply doesn't exist.
            $this->trans[$class] = array();
        }
    }
}