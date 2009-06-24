<?php
/**
 * 
 * Helper to call jQuery for highlighting on a CSS-selected element.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_App_Base_Helper_JsHighlight extends Solar_View_Helper
{
    /**
     * 
     * Constructor.
     * 
     * @param array $config Configuration value overrides, if any.
     * 
     * @return void
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_setup();
    }
    
    /**
     * 
     * Post-construction setup; adds the correct JavaScript files to the
     * <head> section.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        $this->_view
             ->head()
             ->addScript('Solar/scripts/jquery/jquery.js')
             ->addScript('Solar/scripts/jquery/color.js')
             ->addScript('Solar/scripts/jquery/highlight.js');
    }
    
    /**
     * 
     * Calls "highlight()" on a CSS-selected element.
     * 
     * @param string $sel A CSS selector string.
     * 
     * @param mixed $speed The number of milliseconds for the highlighting
     * (1000ms = 1 sec), or one of these words: (slow|medium|fast).
     * 
     * @param string $color A color by word (e.g., "red") or RGB hex (e.g.,
     * "#ff0000").
     * 
     * @return void
     * 
     */
    public function jsHighlight($sel, $color = "yellow", $speed = "slow")
    {
        if (is_numeric($speed)) {
            $speed = (int) $speed;
        } else {
            $speed = "\"$speed\"";
        }
        
        $this->_view
             ->head()
             ->addScriptInline("\$(\"$sel\").highlight(\"$color\", $speed);");
    }
}