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








    /**
     *
     * This method turns the form identified by the $selector into an Ajax.Updater,
     * meaning that it will update an element on the page on success.
     *
     * **NOTE** The $selector must be a form with an id. For example:
     *
     *    <div id="foo-wrapper">
     *
     *      <form id="foo" ...>
     *
     *    </div>
     *
     * would be selected with:
     *
     *      $this->JsPrototype()->ajaxifyForm('#foo', '#foo-wrapper');
     *
     * If `$form` is null, the form action defaults to `$_SERVER['REQUEST_URI']`.
     *
     * @param string $selector CSS Selector of the form to ajaxify
     *
     * @param string $updateSelector CSS Selector of element to update.
     *
     * @param array $options Array of options for Ajax.Updater
     *
     * @return object Solar_View_Helper_JsPrototype
     *
     */
    public function ajaxifyForm($selector, $updateSelector, $form = null, $options = array())
    {

        // Check for Solar_Form to get the action from a pre-defined Solar_Form obj
        if ($form instanceof Solar_Form) {
            $form_action = 'TBD';
        } else {
            $form_action = $_SERVER['REQUEST_URI'];
        }

        // Ajax default options
        $defaults = array(
            'parameters'    => 'Form.serialize(\''.substr($selector, 1).'\')',
            'asynchronous'  => true,
            '_deQuote'      => $this->getFunctionKeys()
        );

        $options = array_merge($defaults, $options);

        // If parameters is still an object, dequote it in Solar_Json
        if (substr($defaults['parameters'], 0, 14) == 'Form.serialize') {
            $defaults['_deQuote'][] = 'parameters';
        }

        // Generate the new Ajax.Updater string
        $ajax = $this->_view->JsPrototype()->ajax->updater($updateSelector, $form_action, $options);

        // Create the observer function, which is the equivalent of adding onsubmit="...; return false;"
        // to the <form> tag
        $ajaxfunc = 'function(evt) { '.$ajax.' Event.stop(evt); }';

        // Observe the form to trigger the Ajax.Updater when the submit button
        // is clicked
        $this->_view->JsPrototype()->event->observe($selector, 'submit', $ajaxfunc);

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
     * @return string JavaScript eval() function string
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
     * @param string $class Class name to observe
     *
     * @param string $name Argument to pass to new object
     *
     * @param array $options Assoc array of options for remote function
     *
     * @access private
     *
     * @return string JavaScript source
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
     * @param array $options Assoc array
     *
     * @access private
     *
     * @return array Array of callbacks.
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