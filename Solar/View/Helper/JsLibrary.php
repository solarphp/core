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
     *
     * User-provided configuration values
     *
     * Keys are ...
     *
     * `events`:
     * (array) An array of JavaScript events that the JavaScript
     * environment is aware of. Used to manage quoting of strings generated
     * by Solar_Json.
     *
     * @var array
     *
     */
    protected $_Solar_View_Helper_JsLibrary = array(
        'events' => array(
            'uninitialized',
            'loading',
            'loaded',
            'interactive',
            'complete',
            'failure',
            'success'
            )
    );

    /**
     *
     * Valid events for JavaScript environment
     *
     * @var array
     *
     */
    protected $_valid_events;

    /**
     * Constructor.
     *
     * @param array $config User-defined configuration
     *
     */
    public function __construct($config = null)
    {
        parent::__construct($config);

        // Event Callbacks are also valid on any HTTP response code
        $codes = range(100, 599);
        $this->_valid_events = array_merge($this->_config['events'], $codes);
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

    /**
     *
     * Returns options keys whose values should be dequoted, as the values are
     * expected to be `function() {...}` or names of pre-defined functions
     * elsewhere in the JavaScript environment.
     *
     * @param bool $expand Expand events into full 'onxxx' strings. 'success'
     * would become 'onSuccess'. Defaults to true
     *
     * @return array List of keys to dequote from a JSON string
     *
     */
    public function getFunctionKeys($expand = true)
    {
        $keys = $this->_valid_events;
        if ($expand) {
            foreach ($keys as $key => $val) {
                $keys[$key] = 'on' . ucfirst($val);
            }
        }

        // These callback hooks aren't true JavaScript events, but their values
        // will be a function
        $keys[] = 'callback';
        $keys[] = 'beforeStart';
        $keys[] = 'beforeUpdate';
        $keys[] = 'afterUpdate';
        $keys[] = 'afterFinish';

        return $keys;
    }

}
?>