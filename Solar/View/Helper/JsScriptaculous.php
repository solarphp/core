<?php
/**
 * 
 * Helper for [script.aculo.us][] JavaScript library
 * 
 * [script.aculo.us]: http://script.aculo.us
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Js
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Helper for [script.aculo.us][] JavaScript library
 * 
 * [script.aculo.us]: http://script.aculo.us
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Js
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 */
class Solar_View_Helper_JsScriptaculous extends Solar_View_Helper_JsLibrary {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_JsScriptaculous = array(
        'path'   => 'Solar/scripts/scriptaculous/'
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
        
        // We need Prototype to be loaded
        $this->_view->getHelper('JsPrototype');
    }
    
    /**
     * 
     * Method interface.
     * 
     * @return Solar_View_Helper_JsScriptaculous
     * 
     */
    public function jsScriptaculous()
    {
        return $this;
    }
    
    /**
     * 
     * Returns a JsScriptaculous helper object; creates it as needed.
     * 
     * @param string $helper Name of JsScriptaculous class
     * 
     * @return object A new standalone helper object.
     * 
     */
    protected function __get($helper)
    {
        // Because Solar_View_Helpers typically are *not* in sub-dirs
        $helper = 'JsScriptaculous_' . ucfirst(strtolower($helper));
        return $this->_view->getHelper($helper);
    }

}
