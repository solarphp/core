<?php
/**
 *
 * JsScriptaculous Effect helper class.
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
 * (copied from <http://wiki.script.aculo.us/scriptaculous/show/CoreEffects>):
 *
 * : duration  	: (float) Duration of the effect in seconds.
 *                Defaults to 1.0.
 *
 * : fps        : (int) Target this many frames per second. Default to 25.
 *                Can't be higher than 100.
 *
 * : transition : (string) Sets a function that modifies the current point of
 *                the animation, which is between 0 and 1. Following transitions
 *                are supplied: Effect.Transitions.sinoidal (default),
 *                Effect.Transitions.linear, Effect.Transitions.reverse,
 *                Effect.Transitions.wobble and Effect.Transitions.flicker.
 *
 * : from       : (float) Sets the starting point of the transition
 *                between 0.0 and 1.0. Defaults to 0.0.
 *
 * : to         : (float) Sets the end point of the transition
 *                between 0.0 and 1.0. Defaults to 1.0.
 *
 * : sync       : (bool) Sets whether the effect should render new frames
 *                automatically (which it does by default). If true,
 *                you can render frames manually by calling the
 *                render() instance method of an effect. This is
 *                used by Effect.Parallel().
 *
 * : queue      : Sets queuing options. When used with a string, can
 *                be 'front' or 'end' to queue the effect in the
 *                global effects queue at the beginning or end, or a
 *                queue parameter object that can have
 *                {position:'front/end', scope:'scope', limit:1}.
 *                For more info on this, see Effect Queues.
 *
 * : direction  : Sets the direction of the transition. Values can
 *                be either 'top-left', 'top-right', 'bottom-left',
 *               'bottom-right' or 'center' (Default). Applicable
 *               only on Grow and Shrink effects.
 *
 * @category Solar
 *
 * @package Solar_View
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
     * @return Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function effect()
    {
        return $this;
    }

    /**
     *
     * Fetch method called by Solar_View_Helper_Js.
     *
     *
     */
    public function fetch($selector, $action)
    {
        $out = "    \$\$('$selector').each(function(li){new Effect.{$action['name']}(li";

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

        $out .= ")});\n";

        return $out;
    }

    /** CORE EFFECTS **/

    /**
     *
     * Setup trigger for core script.aculo.us Highlight effect.
     *
     * @param string $selector CSS selector to highlight
     *
     * @param array $options Assoc array of Highlight effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function highlight($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Highlight',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for core script.aculo.us Opacity effect.
     *
     * @param string $selector CSS selector of element to adjust opacity of
     *
     * @param array $options Assoc array of Opacity effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function opacity($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Opacity',
            'options' => $options
        );
        return $this;
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
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function scale($selector, $percent, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Scale',
            'percent' => $percent,
            'options' => $options
        );
        return $this;
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
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function moveBy($selector, $y = 0, $x = 0, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'MoveBy',
            'y'       => $y,
            'x'       => $x,
            'options' => $options
        );
        return $this;
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
     * @return object Solar_View_Helper_JsScriptaculous
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
     * Setup trigger for combination Appear effect.
     *
     * @param string $selector CSS selector of element to appear
     *
     * @param array $options Assoc array of Appear effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function appear($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Appear',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination Fade effect.
     *
     * @param string $selector CSS selector of element to fade
     *
     * @param array $options Assoc array of Fade effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function fade($selector, $options = array())
    {
         $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Fade',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination Puff effect.
     *
     * @param string $selector CSS selector of element to puff
     *
     * @param array $options Assoc array of Puff effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function puff($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Puff',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination DropOut effect.
     *
     * @param string $selector CSS selector of element to drop out
     *
     * @param array $options Assoc array of DropOut effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function dropOut($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'DropOut',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination Shake effect.
     *
     * @param string $selector CSS selector of element to shake
     *
     * @param array $options Assoc array of Shake effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function shake($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Shake',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination SwitchOff effect.
     *
     * @param string $selector CSS selector of element to switch off
     *
     * @param array $options Assoc array of SwitchOff effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function switchOff($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'SwitchOff',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination BlindDown effect.
     *
     * @param string $selector CSS selector of element to run the BlindDown
     * effect on
     *
     * @param array $options Assoc array of BlindDown effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function blindDown($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'BlindDown',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination BlindUp effect.
     *
     * @param string $selector CSS selector of element to run the BlindUp effect
     * on
     *
     * @param array $options Assoc array of BlindUp effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function blindUp($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'BlindUp',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination SlideDown effect.
     *
     * @param string $selector CSS selector of element to run the SlideDown
     * effect on
     *
     * @param array $options Assoc array of SlideDown effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function slideDown($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'SlideDown',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination SlideUp effect.
     *
     * @param string $selector CSS selector of element to run the SlideUp effect
     * on
     *
     * @param array $options Assoc array of SlideUp effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function slideUp($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'SlideUp',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination Pulsate effect.
     *
     * @param string $selector CSS selector of element to pulsate
     *
     * @param array $options Assoc array of Pulsate effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function pulsate($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Pulsate',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination Squish effect.
     *
     * @param string $selector CSS selector of element to squish
     *
     * @param array $options Assoc array of Squish effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function squish($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Squish',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination Fold effect.
     *
     * @param string $selector CSS selector of element to fold
     *
     * @param array $options Assoc array of Fold effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function fold($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Fold',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination Grow effect.
     *
     * @param string $selector CSS selector of element to grow
     *
     * @param array $options Assoc array of Grow effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function grow($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Grow',
            'options' => $options
        );
        return $this;
    }

    /**
     *
     * Setup trigger for combination Shrink effect.
     *
     * @param string $selector CSS selector of element to shrink
     *
     * @param array $options Assoc array of Shrink effect options
     *
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function shrink($selector, $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Shrink',
            'options' => $options
        );
        return $this;
    }

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
     * @return object Solar_View_Helper_JsScriptaculous_Effect
     *
     */
    public function toggle($selector, $effect = 'appear', $options = array())
    {
        $this->_view->js()->selectors[$selector][] = array(
            'type'    => $this->_type,
            'name'    => 'Toggle',
            'effect'  => $effect,
            'options' => $options
        );
        return $this;
    }

}
?>