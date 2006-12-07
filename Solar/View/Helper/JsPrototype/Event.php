<?php
/**
 *
 * JsPrototype Event helper class.
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
 * The parent JsPrototype class
 */
Solar::loadClass('Solar_View_Helper_JsPrototype');

/**
 *
 * @category Solar
 *
 * @package Solar_View
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 */
class Solar_View_Helper_JsPrototype_Event extends Solar_View_Helper_JsPrototype {

    /**
     *
     * Reference name for the type of JavaScript object this class produces
     *
     * @var string
     *
     */
    protected $_type = 'JsPrototype_Event';

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
    }

    /**
     *
     * Method interface
     *
     * @return object Solar_View_Helper_JsPrototype_Event
     *
     */
    public function event()
    {
        return $this;
    }

    /**
     *
     * Fetch method called by Solar_View_Helper_Js. Feeds generated JavaScript
     * back into a single block of JavaScript to be inserted into a page
     * header.
     *
     * @param string $selector Object or CSS Selector to generate scripts for
     *
     * @param array $action Action details array created by a JsPrototype_Event
     * method.
     *
     * @param bool $object Whether or not method is being called for an object.
     * Result affects how (and if) selector is used in generated script.
     *
     * @return string Generated JavaScript.
     *
     */
    public function fetch($selector, $action, $object = false)
    {

        $out = '';

        switch ($action['method']) {

            case 'observe':
                if ($object) {
                    $out .= "Event.{$action['method']}($selector,'{$action['name']}',";
                    $out .= $action['observer'];
                    if ($action['useCapture'] === true) {
                        $out .= ',true';
                    }
                    $out .= ");\n";
                } else {
                    $out .= "Event.{$action['method']}(el";
                    $out .= ",'{$action['name']}', {$action['observer']}";
                    if ($action['useCapture'] === true) {
                        $out .= ',true';
                    }
                    $out .= ");";
                }

                break;


            default:
                break;

        }

        return $out;
    }

    /**
     *
     * Adds an event handler function to the element defined by $selector.
     * Intended to be used with CSS Selectors. Use observeObject() if you
     * need to obverve objects defined by the JavaScript environment.
     *
     * @param string $selector CSS Selector of block to observe
     *
     * @param string $name Name of event to observe. Note that 'on' is prepended
     * by the Prototype library. So, valid $name values would be 'click', 'load',
     * 'mouseover', 'mouseout', etc.
     *
     * @param string $observer The JavaScript function to handle the event.
     *
     * @param bool $useCapture If true, handle the event in the capture phase
     * and if false, in the bubbling phase.
     * 
     * @return Solar_View_Helper_JsPrototype_Effect This object.
     * 
     */
    public function observe($selector, $name, $observer, $useCapture = false)
    {
        $details = array(
            'type'       => $this->_type,
            'method'     => 'observe',
            'name'       => $name,
            'observer'   => $observer,
            'useCapture' => $useCapture
        );
        $this->_view->js()->selectors[$selector][] = $details;

        return $this;
    }

    /**
     * 
     * Adds an event handler function to the JavaScript object defined by
     * $object. Use observe() if you need to obverve blocks in the DOM.
     *
     * @param string $object JavaScript object to observe
     *
     * @param string $name Name of event to observe. Note that 'on' is prepended
     * by the Prototype library. So, valid $name values would be 'click', 'load',
     * 'mouseover', 'mouseout', etc.
     *
     * @param string $observer The JavaScript function to handle the event.
     *
     * @param bool $useCapture If true, handle the event in the capture phase
     * and if false, in the bubbling phase.
     *
     * @return Solar_View_Helper_JsPrototype_Effect This object.
     * 
     */
    public function observeObject($object, $name, $observer, $useCapture = false)
    {
        $details = array(
            'type'       => $this->_type,
            'method'     => 'observe',
            'name'       => $name,
            'observer'   => $observer,
            'useCapture' => $useCapture
        );
        $this->_view->js()->objects[$object][] = $details;

        return $this;
    }

}
