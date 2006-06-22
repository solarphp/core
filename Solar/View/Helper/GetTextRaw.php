<?php
/**
 * 
 * Helper for locale strings (no escaping is applied).
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Solar_View_Helper
 */
Solar::loadClass('Solar_View_Helper');
 
/**
 * 
 * Helper for locale strings (no escaping is applied).
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_GetTextRaw extends Solar_View_Helper {
    
    /**
     * 
     * User-defined configuration.
     * 
     * Keys are:
     * 
     * : \\class\\ : (string) The class for locale translations.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'class' => 'Solar',
    );
    
    /**
     * 
     * The default locale class.
     * 
     * @var string
     * 
     */
    public $_class;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_class = $this->_config['class'];
    }
    
    /**
     * 
     * Returns a localized string WITH NO ESCAPING.
     * 
     * Alternatively, use this method to reset the default locale class,
     * or get a localized string from another class without resetting
     * the default.
     * 
     * To set a new default locale class, use the class name with two
     * colons and no key (e.g. 'New_Default_Class::').
     * 
     * To use a key from another locale class, prefix the key with that
     * class name and two colons (e.g. 'Non_Default_Class::KEY_NAME').
     * 
     * @param string $key The locale key to look up from the default
     * class, or a class name and key, or a class name to set as the new
     * default.
     * 
     * @param int|float $num A number to help determine if the
     * translation should return singluar or plural.
     * 
     * @return string The translated locale string.
     * 
     */
    public function getTextRaw($key, $num = 1)
    {
        // is there a :: in the key?
        $pos = strpos($key, '::');
        if ($pos) {
        
            // yes, so we're using a non-default class.
            // get the class and key.
            $class = substr($key, 0, $pos);
            $key = substr($key, $pos+2);
            
            // is there a key after the :: marker?
            if ($key == '') {
                // no, so set a new default class
                $this->_class = $class;
                return;
            }
        } else {
            // no :: in the name, the class is the default
            // and the key remains as it is.
            $class = $this->_class;
        }
        
        // get the translation
        return Solar::locale($class, $key, $num);
    }
}
?>