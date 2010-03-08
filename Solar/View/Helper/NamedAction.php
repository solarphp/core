<?php
/**
 * 
 * Helper to build an anchor for a named action from the rewrite rules, using
 * data interpolation, with built-in text translation.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Action.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_NamedAction extends Solar_View_Helper
{
    /**
     * 
     * Helper to build an anchor for a named action from the rewrite rules, 
     * using data interpolation, with built-in text translation.
     * 
     * @param string $name The named action from the rewrite rules.
     * 
     * @param array $data Data to interpolate into the token placeholders.
     * 
     * @param string $text A locale translation key.
     * 
     * @param string $attribs Additional attributes for the anchor.
     * 
     * @return string
     * 
     */
    public function namedAction($name, $data = null, $text = null, $attribs = null)
    {
        // get an escaped href rewrite value
        $href = $this->_view->namedActionHref($name, $data);
        
        // now build using action helper
        return $this->_view->action($href, $text, $attribs);
    }
}
