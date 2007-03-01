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
     * Keys are ...
     * 
     * `class`
     * : (string) The class for locale translations.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_GetTextRaw = array(
        'class' => 'Solar',
    );
    
    /**
     * 
     * The locale class-space key to use.
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
     * @param string $key The locale key to look up from the class.
     * 
     * @param int|float $num A number to help determine if the
     * translation should return singluar or plural.
     * 
     * @return string The translated locale string.
     * 
     */
    public function getTextRaw($key, $num = 1)
    {
        return Solar::$locale->fetch($this->_class, $key, $num);
    }
    
    /**
     * 
     * Sets the class used for translations.
     * 
     * You can use this method in a view like so:
     * 
     * {{code: php
     *     $this->getHelper('getTextRaw')->setClass('Some_Class');
     * }}
     * 
     * @param string $class The class used for translations.
     * 
     * @return void
     * 
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }
}
