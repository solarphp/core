<?php
/**
 *
 * Helper for {@link http://prototype.conio.net Prototype} JavaScript library
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
 * Helper for {@link http://prototype.conio.net Prototype} JavaScript library
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
     * Valid callback opportunties
     *
     * @var array
     *
     */
    protected $_valid_callbacks = array(
        'uninitialized',
        'loading',
        'loaded',
        'interactive',
        'complete',
        'failure',
        'success'
    );

    /**
     *
     * Valid Ajax Callback Options
     *
     * @var array
     *
     */
    protected $_ajax_options = array(
        'before',
        'after',
        'condition',
        'url',
        'asynchronous',
        'method',
        'insertion',
        'position',
        'form',
        'with',
        'update',
        'script'
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

        // Callbacks are also valid on any HTTP response code
        $codes = range(100, 599);
        $this->_valid_callbacks = array_merge($this->_valid_callbacks, $codes);
        $this->_ajax_options = array_merge($this->_ajax_options,
                                          $this->valid_callbacks);
    }

    /**
     *
     * Method interface
     *
     */
    public function jsPrototype()
    {
        return $this;
    }

    /**
     *
     * Returns 'eval(request.responseText)' which is the JavaScript function
     * that {@link formRemoteTag()} can call on completion to evaluate a multiple
     * update return document.
     *
     * @static
     *
     */
    public static function evaluate()
    {
        return 'eval(request.responseText)';
    }

    /**
     *
     * Create a valid Observer function in JavaScript
     *
     * @param string $class
     *
     * @param string $name
     *
     * @param array $options
     *
     * @access private
     *
     * @return string
     *
     */
    private function _buildObserver($class, $name, $options = array())
    {
        if (!isset($options['with']) && $options['update']) {
            $options['with'] = 'value';
        }
        $callback = $this->remoteFunction($options);
        $js = 'new '.$class.'("'.$name.'", ';
        if (isset($options['frequency'])) {
            $js .= $options['frequency'].', ';
        }
        $js .= 'function(element, value) {';
        $js .= $callback . '});';
        return $js;
    }

    /**
     *
     * Build a list of callbacks from valid possible callbacks.
     *
     * @param array $options
     *
     * @access private
     *
     * @return array
     *
     */
    private function _buildCallbacks($options)
    {
        $valid_cbs = $this->_valid_callbacks;
        $callbacks = array();
        foreach ($valid_cbs as $callback) {
            if (isset($options[$callback])) {
                $name = 'on'.ucfirst($callback);
                $callbacks[$name] = 'function(request){'.$options[$callback].'}';
            }
        }
        return $callbacks;
    }
}
?>