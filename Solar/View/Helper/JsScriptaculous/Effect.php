<?php
/**
 *
 * JsScriptaculous Effect helper class.
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
 * The parent JsScriptaculous class
 */
Solar::loadClass('Solar_View_Helper_JsScriptaculous');


/**
 *
 * script.aculo.us effect proxy class.
 *
 * Note that very few script.aculo.us effects have required parameters.
 * In fact, only Effect.Scale and Effect.MoveBy have required parameters. In
 * JavaScript, those parameters are passed after the selector
 * and before the options array.
 *
 * For $options, the core effects all support the following settings
 * (copied from [core effects][] documentation) ...
 *
 * `duration`
 * : _(float)_ Duration of the effect in seconds. Defaults to `1.0`.
 *
 * `fps`
 * : _(int)_ Target this many frames per second. Default to `25`. Can't be higher
 *   than `100`.
 *
 * `transition`
 * : _(string)_ Sets a function that modifies the current point of the animation,
 *   which is between `0` and `1`. Following transitions are supplied:
 *   `Effect.Transitions.sinoidal` (default), `Effect.Transitions.linear`,
 *   `Effect.Transitions.reverse`, `Effect.Transitions.wobble` and
 *   `Effect.Transitions.flicker`.
 *
 * `from`
 * : _(float)_ Sets the starting point of the transition between `0.0` and `1.0`.
 *   Defaults to `0.0`.
 *
 * `to`
 * : _(float)_ Sets the end point of the transition between `0.0` and `1.0`.
 *   Defaults to `1.0`.
 *
 * `sync`
 * : _(bool)_ Sets whether the effect should render new frames automatically
 *   (which it does by default). If true, you can render frames manually by
 *   calling the render() instance method of an effect. This is used by
 *   `Effect.Parallel()`.
 *
 * `queue`
 * : _(mixed)_ Sets queuing options. When used with a string, can be 'front' or 'end' to
 *   queue the effect in the global effects queue at the beginning or end, or a
 *   queue parameter object that can have `{position:'front/end', scope:'scope', limit:1}`.
 *   For more info on this, see [Effect Queues][].
 *
 * `direction`
 * : _(string)_ Sets the direction of the transition. Values can be either
 *   'top-left', 'top-right', 'bottom-left', 'bottom-right' or 'center' (Default).
 *   Applicable only on Grow and Shrink effects.
 *
 * [core effects]: http://wiki.script.aculo.us/scriptaculous/show/CoreEffects
 * [Effect Queues]: http://wiki.script.aculo.us/scriptaculous/show/EffectQueues
 *
 * @category Solar
 *
 * @package Solar_View_Helper_Js
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 */
class Solar_View_Helper_JsScriptaculous_Effect extends Solar_View_Helper_JsScriptaculous {

    /**
     *
     * Reference name for the type of JavaScript object this class produces
     *
     * @var string
     *
     */
    protected $_type = 'JsScriptaculous_Effect';

    /**
     *
     * Camel case correction map for script.aculou.us effects
     *
     * @var array
     *
     */
    protected $_caseCorrection = array(
        'dropout'   => 'DropOut',
        'blindup'   => 'BlindUp',
        'blinddown' => 'BlindDown',
        'slideup'   => 'SlideUp',
        'slidedown' => 'SlideDown',
        'switchoff' => 'SwitchOff'
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
        $this->_needsFile('effects.js');
    }

    /**
     *
     * Method interface
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function effect()
    {
        return $this;
    }

    /**
     *
     * Fetch method called by Solar_View_Helper_Js. Feeds generated JavaScript
     * back into a single block of JavaScript to be inserted into a page
     * header.
     *
     * @param string $selector CSS selector to generate scripts for
     *
     * @param array $action Action details array created by a
     * JsScriptaculous_Effect method.
     *
     * @param bool $useNamedSelector Boolean flag to determine whether or not
     * to use the passed selector or a generic element reference.
     *
     * @return string The JavaScript output.
     * 
     */
    public function fetch($selector, $action, $useNamedSelector = false)
    {
        if ($useNamedSelector) {
            $out = "\$\$('{$selector}').invoke('visualEffect', '{$action['name']}'";
        } else {
            $out = "new Effect.{$action['name']}(el";
        }

        switch ($action['name']) {

            case 'Scale':
                $out .= ", {$action['percent']}";
                break;

            case 'MoveBy':
                $out .= ", {$action['y']}, {$action['x']}";
                break;

            case 'Toggle':
                $out .= ", {$action['effect']}";
                break;

            default:
                break;

        }

        if (!empty($action['options'])) {
            $json = Solar::factory('Solar_Json');
            $out .= ', ' . $json->encode($action['options']);
        }

        $out .= ");";

        return $out;
    }

    /**
     *
     * Overload method for core script.aculo.us effects that follow the same
     * convention, which include ...
     *
     * - Core Effects
     *   - [Highlight][]
     *   - [Opacity][]
     * - Combination Effects
     *   - [Appear][]
     *   - [Fade][]
     *   - [Puff][]
     *   - [Shake][]
     *   - [Pulsate][]
     *   - [Squish][]
     *   - [Fold][]
     *   - [Grow][]
     *   - [Shrink][]
     *   - [DropOut][]
     *   - [SwitchOff][]
     *   - [BlindUp][]
     *   - [BlindDown][]
     *   - [SlideUp][]
     *   - [SlideDown][]
     *
     * The $args array is expected to have 1-3 values, which are ...
     *
     * : $selector
     * `(string)` CSS selector of element to apply effect to. There is no
     * default.
     *
     * : $options
     * `(array)` Associative array of options for the effect. Defaults to
     * `array()`
     *
     * : $returnJs
     * `(boolean)` Whether or not to return JavaScript string immediately, or
     * add it to the page-load observation stack. Defaults to `false`
     *
     * Since Core Effects [Scale][], [MoveBy][] and [Parallel][]accept a
     * different number of parameters, those effects are implemented in their
     * own explicit methods.
     *
     * Combination Effects [Toggle][] is also maintained in an explicit method
     * due to its use of different number of parameters.
     *
     * Use of an overload method for these effects allows JsScriptaculous_Effect
     * to remain more compatible with the script.aculo.us library. If the
     * script.aculo.us library of effects grows in-between Solar releases, you
     * will still be able to use this class to call those effects from your views,
     * so long as the effects additions only need "Selector" and "Options".
     *
     * This flexibility also allows you to leverage third-party effects such as
     * those you create yourself, or those found in the [Effects Treasure Chest][].
     *
     * [Highlight]: http://wiki.script.aculo.us/scriptaculous/show/Effect.Highlight
     * [Opacity]:   http://wiki.script.aculo.us/scriptaculous/show/Effect.Opacity
     * [Scale]:     http://wiki.script.aculo.us/scriptaculous/show/Effect.Scale
     * [MoveBy]:    http://wiki.script.aculo.us/scriptaculous/show/Effect.MoveBy
     * [Parallel]:  http://wiki.script.aculo.us/scriptaculous/show/Effect.Parallel
     * [Appear]:    http://wiki.script.aculo.us/scriptaculous/show/Effect.Appear
     * [Fade]:      http://wiki.script.aculo.us/scriptaculous/show/Effect.Fade
     * [Puff]:      http://wiki.script.aculo.us/scriptaculous/show/Effect.Puff
     * [DropOut]:   http://wiki.script.aculo.us/scriptaculous/show/Effect.DropOut
     * [Shake]:     http://wiki.script.aculo.us/scriptaculous/show/Effect.Shake
     * [SwitchOff]: http://wiki.script.aculo.us/scriptaculous/show/Effect.SwitchOff
     * [BlindDown]: http://wiki.script.aculo.us/scriptaculous/show/Effect.BlindDown
     * [BlindUp]:   http://wiki.script.aculo.us/scriptaculous/show/Effect.BlindUp
     * [SlideDown]: http://wiki.script.aculo.us/scriptaculous/show/Effect.SlideDown
     * [SlideUp]:   http://wiki.script.aculo.us/scriptaculous/show/Effect.SlideUp
     * [Pulsate]:   http://wiki.script.aculo.us/scriptaculous/show/Effect.Pulsate
     * [Squish]:    http://wiki.script.aculo.us/scriptaculous/show/Effect.Squish
     * [Fold]:      http://wiki.script.aculo.us/scriptaculous/show/Effect.Fold
     * [Grow]:      http://wiki.script.aculo.us/scriptaculous/show/Effect.Grow
     * [Shrink]:    http://wiki.script.aculo.us/scriptaculous/show/Effect.Shrink
     * [Toggle]:    http://wiki.script.aculo.us/scriptaculous/show/Effect.Toggle
     *
     * [Effects Treasure Chest]: http://wiki.script.aculo.us/scriptaculous/show/EffectsTreasureChest
     *
     *
     * @param string $effect Name of effect
     *
     * @param array $args Array of arguments
     *
     * @return mixed object Solar_View_Helper_JsScriptaculous_Effect | string $js JavaScript string
     */

    public function __call($effect, $args)
    {
        $effect   = $this->_correctCase($effect);
        $selector = $args[0];
        $options  = isset($args[1]) ? $args[1] : array();
        $returnJs = isset($args[2]) ? $args[2] : false;

        $action = array(
            'type'    => $this->_type,
            'name'    => $effect,
            'options' => $options
        );

        if ($returnJs) {
            return $this->fetch($selector, $action, true);
        } else {
            $this->_view->js()->selectors[$selector][] = $action;
            return $this;
        }
    }

    /**
     *
     * Correct the case of effects as appropriate, or return ucfirst()
     * version of the $effect if no correction is known or needed.
     *
     * @param string $effect script.aculo.us effect name
     *
     * @return string Effect name adjusted for aesthetic accuracy.
     *
     */
    protected function _correctCase($effect)
    {
        $effect = strtolower($effect);
        if (isset($this->_caseCorrection[$effect])) {
            return $this->_caseCorrection[$effect];
        } else {
            return ucfirst($effect);
        }
    }

    /**
     *
     * Setup trigger for core script.aculo.us Scale effect.
     *
     * @param string $selector CSS selector of element to scale
     *
     * @param int $percent Percentage value to scale element
     *
     * @param array $options Assoc array of Scale effect options
     *
     * @param bool $returnJs Optionally just return the JavaScript for the
     * effect, without adding it to the CSS selector observers linked up on page
     * load.
     *
     * @return mixed object Solar_View_Helper_JsScriptaculous_Effect | string $js JavaScript string
     *
     */
    public function scale($selector, $percent, $options = array(), $returnJs = false)
    {
        $action = array(
            'type'    => $this->_type,
            'name'    => 'Scale',
            'percent' => $percent,
            'options' => $options
        );

        if ($returnJs) {
            return $this->fetch($selector, $action, true);
        } else {
            $this->_view->js()->selectors[$selector][] = $action;
            return $this;
        }
    }

    /**
     *
     * Setup trigger for core script.aculo.us MoveBy effect.
     *
     * @param string $selector CSS selector of element to move
     *
     * @param int $y Pixels along y axis to move element from its current position
     *
     * @param int $x Pixels along x axis to move element from its current position
     *
     * @param array $options Assoc array of MoveBy effect options
     *
     * @param bool $returnJs Optionally just return the JavaScript for the
     * effect, without adding it to the CSS selector observers linked up on page
     * load.
     *
     * @return mixed object Solar_View_Helper_JsScriptaculous_Effect | string $js JavaScript string
     *
     */
    public function moveBy($selector, $y = 0, $x = 0, $options = array(), $returnJs = false)
    {
        $action = array(
            'type'    => $this->_type,
            'name'    => 'MoveBy',
            'y'       => $y,
            'x'       => $x,
            'options' => $options
        );

        if ($returnJs) {
            return $this->fetch($selector, $action, true);
        } else {
            $this->_view->js()->selectors[$selector][] = $action;
            return $this;
        }
    }

    /**
     *
     * Setup trigger for core script.aculo.us Parallel effect.
     *
     * @param array $subeffects Array of sub-effects to set up to be run in
     * parallel
     *
     * @param array $options Assoc array of options to be passed the the parallel
     * execution handler
     *
     * @todo Figure out the best way to handle this effect.
     *
     * @return mixed object Solar_View_Helper_JsScriptaculous_Effect | string $js JavaScript string
     *
     */
    /**
    public function parallel($subeffects = array(), $options = array())
    {
        //$this->effect('MoveBy', $selector, $y, $x, $options);
        return $this;
    }
    **/


    /**
     *
     * Setup trigger for combination Toggle utility method.
     *
     * $effect can be one of 'appear', 'slide', or 'blind'
     *
     * @param string $selector CSS selector of element to toggle
     *
     * @param string $effect Type of effect transition to use when toggling
     *
     * @param array $options Assoc array of Toggle effect options
     *
     * @param bool $returnJs Optionally just return the JavaScript for the
     * effect, without adding it to the CSS selector observers linked up on page
     * load.
     *
     * @return mixed object Solar_View_Helper_JsScriptaculous_Effect | string $js JavaScript string
     *
     */
    public function toggle($selector, $effect = 'appear', $options = array(), $returnJs = false)
    {
        $action = array(
            'type'    => $this->_type,
            'name'    => 'Toggle',
            'effect'  => $effect,
            'options' => $options
        );

        if ($returnJs) {
            return $this->fetch($selector, $action, true);
        } else {
            $this->_view->js()->selectors[$selector][] = $action;
            return $this;
        }
    }

}
