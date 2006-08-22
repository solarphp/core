<?php
/**
 *
 * Helper for [Prototype][] JavaScript library
 *
 * [Prototype]: http://prototype.conio.net
 *
 * @category Solar
 *
 * @package Solar_View
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 * The abstract JsLibrary class
 */
Solar::loadClass('Solar_View_Helper_JsLibrary');

/**
 *
 * Helper for [Prototype][] JavaScript library
 *
 * [Prototype]: http://prototype.conio.net
 *
 * @category Solar
 *
 * @package Solar_View
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 */
class Solar_View_Helper_JsPrototype extends Solar_View_Helper_JsLibrary {

    /**
     *
     * User-provided configuration values
     *
     * @var array
     *
     */
    protected $_Solar_View_Helper_JsPrototype = array(
        'path'   => 'Solar/scripts/prototype/'
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
        parent::__construct($config);

        // JsPrototype always needs the prototype.js file
        $this->_needsFile('prototype.js');

    }

    /**
     *
     * Method interface
     *
     * @return object Solar_View_Helper_JsPrototype
     *
     */
    public function jsPrototype()
    {
        return $this;
    }

    /**
     *
     * Returns a JsPrototype helper object; creates it as needed.
     *
     * @param string $helper Name of JsPrototype class
     *
     * @return object A new standalone helper object.
     *
     */
    protected function __get($helper)
    {
        // Because Solar_View_Helpers typically are *not* in sub-dirs
        $helper = 'JsPrototype_' . ucfirst(strtolower($helper));
        return $this->_view->getHelper($helper);
    }

}
?>