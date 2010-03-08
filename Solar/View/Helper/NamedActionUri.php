<?php
/**
 * 
 * Helper to build an Solar_Action_Uri for a named action from the rewrite
 * rules using data interpolation.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ActionHref.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_NamedActionUri extends Solar_View_Helper
{
    /**
     * 
     * Returns an escaped href or src attribute value for a named action
     * from the rewrite rules, using data interpolation.
     * 
     * @param string $name The named action from the rewrite rules.
     * 
     * @param array $data Data to interpolate into the token placeholders.
     * 
     * @return string
     * 
     */
    public function namedActionUri($name, $data = null)
    {
        $href = $this->_view->namedActionHref($name, $data);
        return $this->_view->actionUri($href);
    }
}
