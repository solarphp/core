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
     * @param string $file
     *
     */
    protected function _needsFile($file = null)
    {
        // Add configured path
        $file = $this->_config['path'] . $file;

        $this->_view->js()->addFile($file);
        return $this;
    }

    /**
     *
     * Utility method: Structure a well-formed method call
     *
     * @param string $method
     *
     * @access protected
     *
     * @return string
     *
     */
    protected function _methodOptionToStr($method)
    {
        $ret = (is_string($method) && substr($method, 0, 1) != '\'')
                ? $method
                : "'$method'";
        return $ret;
    }

    /**
     *
     * Utility method: Generate options for suitable for JavaScript
     *
     * @param array $options
     *
     * @access protected
     *
     * @return string
     *
     */
    protected function _optionsForJs($options = array())
    {
        $opts = array();
        foreach ($options as $key => $val) {
            $opts[] = "$key:$val";
        }
        sort($opts);
        $jsopts = '{'.join(', ', $opts).'}';
        return $jsopts;
    }

    /**
     *
     * Manipulate the input option for output in a JavaScript
     *
     * @param mixed $option
     *
     * @access protected
     *
     * @return string
     *
     */
    protected function _arrayOrStringForJs($option)
    {
        $ret = '';
        if (is_array($option)) {
            $ret = '[\''. join('\',\'', $option . '\']');
        } else {
            $ret = "'$option'";
        }
        return $ret;
    }


}
?>