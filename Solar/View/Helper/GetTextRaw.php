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
 * @license LGPL
 * 
 * @version $Id$
 *
 */

/**
 * Solar_View_Helper
 */
require_once 'Solar/View/Helper.php';
 
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
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_class = $this->_config['class'];
    }
    
    /**
     * 
     * Returns a localized string WITH NO ESCAPING.
     * 
     * @param string $key The locale key to look up.  Uses the default
     * class; if you need to use a key from another class, prefix with
     * that class name and two colons (e.g.,
     * 'Non_Default_Class::KEY_NAME').  If you need to set a new default
     * class, use the class name with two colons and no key (e.g.
     * 'New_Default_Class::').
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
        $string = Solar::locale($class, $key, $num);
        
        if ($string == $key) {
            // no translation found.
            // fall back to the generic Solar locale strings.
            $string = Solar::locale('Solar', $key, $num);
        }
        
        return $string;
    }
}
?>