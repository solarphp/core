<?php
/**
 * 
 * Helper for locale strings, with escaping.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id: Solar_View_Helper_locale.php 752 2006-02-04 18:59:34Z pmjones $
 *
 */

/**
 * 
 * Helper for locale strings, with escaping.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_GetText extends Solar_View_Helper {
    
    /**
     * 
     * Returns a localized string, with escaping applied.
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
    public function getText($key, $num = 1)
    {
        return $this->_view->escape($this->_view->getTextRaw($key, $num));
    }
}
?>