<?php

/**
 * 
 * Solar-specific plugin to help with locale strings.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_Template
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
 * Solar-specific plugin to help with locale strings.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_Template
 * 
 */

class Savant3_Plugin_locale extends Savant3_Plugin {
    
    /**
     * 
     * The default locale class.
     * 
     * @access public
     * 
     * @var string
     * 
     */
    
    public $default = 'Solar';
    
    
    /**
     * 
     * Returns a localized string.
     * 
     * @access public
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
    
    function locale($key, $num = 1)
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
                $this->default = $class;
                return;
            }
        } else {
            // no :: in the name, the class is the default
            // and the key remains as it is.
            $class = $this->default;
        }
        
        return Solar::locale($class, $key, $num);
    }

}
?>