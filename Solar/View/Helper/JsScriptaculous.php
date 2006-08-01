<?php
/**
 *
 * Helper for {@link http://script.aculo.us script.aculo.us} JavaScript library
 *
 * @category Solar
 *
 * @package Solar_View
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @author Paul M. Jones <pmjones@solarphp.com>
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
 * Helper for {@link http://script.aculo.us script.aculo.us} JavaScript library
 *
 * @category Solar
 *
 * @package Solar_View
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 */
class Solar_View_Helper_JsScriptaculous extends Solar_View_Helper_JsLibrary {

    /**
     *
     * User-provided configuration values
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
     * Method interface
     *
     */
    public function jsScriptaculous()
    {
        return $this;
    }

    /**
     *
     * Creates a script.aculo.us effect instance.
     *
     * Note that very few script.aculo.us effects have required parameters.
     * In fact, only Effect.Scale and Effect.MoveBy have required parameters. In
     * JavaScript, those parameters are passed after the selector
     * and before the options array.
     *
     * To maintain compatibility with script.aculo.us documentation as much
     * as possible, func_get_args() is used as needed to adjust how parameters
     * are treated.
     *
     * @param string $name
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function effect($name, $selector, $options = array())
    {
        $this->_needsFile('effects.js');

        $details = array('type' => 'effect',
                            'name' => $name,
                            'options' => $options);

        switch ($name) {
            case 'Scale':
                $args = func_get_args();
                $details['percent'] = $args[2];
                $details['options'] = $args[3];
                break;

            case 'MoveBy':
                $args = func_get_args();
                $details['y'] = $args[2];
                $details['x'] = $args[3];
                $details['options'] = $args[4];
                break;

            case 'Toggle':
                $args = func_get_args();
                $details['effect'] = $args[2];
                $details['options'] = $args[3];
                break;

            default:
                break;
        }

        $this->_view->js()->selectors[$selector][] = $details;

        return $this;
    }


    /** CORE EFFECTS **/

    /**
     *
     * Convenience method for core script.aculo.us Highlight effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function highlight($selector, $options = array())
    {
        $this->effect('Highlight', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for core script.aculo.us Opacity effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function opacity($selector, $options = array())
    {
        $this->effect('Opacity', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for core script.aculo.us Scale effect.
     *
     * @param string $selector
     *
     * @param int $percent
     *
     * @param array $options
     *
     */
    public function scale($selector, $percent, $options = array())
    {
        $this->effect('Scale', $selector, $percent, $options);

        return $this;
    }

    /**
     *
     * Convenience method for core script.aculo.us MoveBy effect.
     *
     * @param string $selector
     *
     * @param int $y
     *
     * @param int $x
     *
     * @param array $options
     *
     */
    public function moveBy($selector, $y = 0, $x = 0, $options = array())
    {
        $this->effect('MoveBy', $selector, $y, $x, $options);

        return $this;
    }

    /**
     *
     * Convenience method for core script.aculo.us Parallel effect.
     *
     * @param array $subeffects
     *
     * @param array $options
     *
     * @todo Figure out the best way to handle this effect.
     *
     */
    public function parallel($subeffects = array(), $options = array())
    {
        //$this->effect('MoveBy', $selector, $y, $x, $options);

        return $this;
    }

    /** BUNDLED COMBINATION EFFECTS **/

    /**
     *
     * Convenience method for combination Appear effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function appear($selector, $options = array())
    {
        $this->effect('Appear', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination Fade effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function fade($selector, $options = array())
    {
        $this->effect('Fade', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination Puff effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function puff($selector, $options = array())
    {
        $this->effect('Puff', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination DropOut effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function dropOut($selector, $options = array())
    {
        $this->effect('DropOut', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination Shake effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function shake($selector, $options = array())
    {
        $this->effect('Shake', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination SwitchOff effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function switchOff($selector, $options = array())
    {
        $this->effect('SwitchOff', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination BlindDown effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function blindDown($selector, $options = array())
    {
        $this->effect('BlindDown', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination BlindUp effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function blindUp($selector, $options = array())
    {
        $this->effect('BlindUp', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination SlideDown effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function slideDown($selector, $options = array())
    {
        $this->effect('SlideDown', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination SlideUp effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function slideUp($selector, $options = array())
    {
        $this->effect('SlideUp', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination Pulsate effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function pulsate($selector, $options = array())
    {
        $this->effect('Pulsate', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination Squish effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function squish($selector, $options = array())
    {
        $this->effect('Squish', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination Fold effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function fold($selector, $options = array())
    {
        $this->effect('Fold', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination Grow effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function grow($selector, $options = array())
    {
        $this->effect('Grow', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination Shrink effect.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function shrink($selector, $options = array())
    {
        $this->effect('Shrink', $selector, $options);

        return $this;
    }

    /**
     *
     * Convenience method for combination Toggle utility method.
     *
     * $effect can be one of 'appear', 'slide', or 'blind'
     *
     * @param string $selector
     *
     * @param string $effect
     *
     * @param array $options
     *
     */
    public function toggle($selector, $effect = 'appear', $options = array())
    {
        $this->effect('Toggle', $selector, $effect, $options);

        return $this;
    }

    /** CONTROLS **/

    /**
     *
     * Makes the element with the CSS selector specified by $selector draggable.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function draggable($selector, $options = array())
    {
        $this->_needsFile('effects.js');
        $this->_needsFile('dragdrop.js');

        $this->selectors[$selector][] = array('type' => 'draggable',
                                               'options' => $options);

        return $this;
    }

    /**
     * Makes the element with the CSS selector specified by $selector receive
     * dropped draggable elements (created by {@link draggable()}, and make
     * an Ajax call by default. The action called gets the DOM ID of the
     * dropped element as a parameter.
     *
     * @param string $selector
     *
     * @param array $options
     *
     */
    public function droppable($selector, $options = array())
    {
        $this->_needsFile('effects.js');
        $this->_needsFile('dragdrop.js');

        if (!isset($options['with'])) {
            $options['with'] = '\'id=\' + encodeURIComponent(el.id)';
        }

        if (!isset($options['ondrop'])) {
            $options['ondrop'] = 'function(el) {'
                . $this->remoteFunction($options) . '}';
        }

        // Clean out options
        $ajax_options = $this->ajax_options;
        foreach ($ajax_options as $key) {
            unset($options[$key]);
        }

        if (isset($options['accept'])) {
            $options['accept'] = $this->_arrayOrStringForJs($options['accept']);
        }

        if (isset($options['hoverclass'])) {
            $options['hoverclass'] = "'{$options['hoverclass']}'";
        }

        $this->selectors[$selector][] = array('type' => 'droppable',
                                               'options' => $options);

        return $this;
    }

    /**
     *
     * Makes the item with the CSS selector specified sortable by drag-and-drop,
     * and makes an Ajax call whenever the sort order has changed. By default,
     * the action called gets the serialized sortable element as parameters.
     *
     * @param string $selector
     *
     * @param string $url
     *
     * @param array $options
     *
     */
    public function sortable($selector, $url, $options = array())
    {

        return $this;
    }


    /** AUTO-COMPLETION CONTROLS **/

    /**
     *
     * Autocompleting text input field (server powered)
     *
     * @param string $selector
     *
     * @param string $divToPopulate
     *
     * @param string $url
     *
     * @param array $options
     *
     */
    public function autocompleter($selector, $divToPopulate, $url,
                                    $options = array())
    {
        $this->_needsFile('effects.js');
        $this->_needsFile('controls.js');

        return $this;
    }

    /**
     *
     * Autocompleting text input field (local)
     *
     * @param string $selector
     *
     * @param string $divToPopulate
     *
     * @param array $choices
     *
     * @param array $options
     *
     */
    public function autocompleterLocal($selector, $divToPopulate,
                                    $choices = array(), $options = array())
    {
        $this->_needsFile('effects.js');
        $this->_needsFile('controls.js');

        return $this;
    }


    /** IN-PLACE EDITING CONTROLS **/


}
?>