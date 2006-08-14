<?php
/**
 *
 * Abstract helper for JavaScript support.
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
 * Solar_View_Helper
 */
Solar::loadClass('Solar_View_Helper');

/**
 *
 * Abstract helper for JavaScript support.
 *
 * @category Solar
 *
 * @package Solar_View
 *
 */
abstract class Solar_View_Helper_JsLibrary extends Solar_View_Helper {

    /**
     * Constructor.
     *
     * @param array $config User-defined configuration
     *
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
    }

    /**
     *
     * Method interface
     *
     * @return Child JsLibrary object
     */
    public function jsLibrary()
    {
        return $this;
    }

    /**
     *
     * Add the specified JavaScript file to the Helper_Js file list
     * if it's not already present.
     *
     * @param string $file Name of .js file needed by Helper class
     *
     * @return Child JsLibrary object
     *
     */
    protected function _needsFile($file = null)
    {
        // Add configured path
        $file = $this->_config['path'] . $file;

        $this->_view->js()->addFile($file);
        return $this;
    }

}
?>