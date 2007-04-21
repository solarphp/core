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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
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
     * @param string $key The locale key to look up.
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
    
    /**
     * 
     * Sets the class used for translations.
     * 
     * You can use this method in a view like so:
     * 
     * {{code: php
     *     $this->getHelper('getText')->setClass('Some_Class');
     * }}
     * 
     * @param string $class The class used for translations.
     * 
     * @return void
     * 
     */
    public function setClass($class)
    {
        return $this->_view->getHelper('getTextRaw')->setClass($class);
    }
}
