<?php
/**
 * 
 * Plugin to convert an associative array to a string of tag attributes.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
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
 * Plugin to convert an associative array to a string of tag attributes.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 */
class Solar_View_Helper_Attribs extends Solar_View_Helper {
    
    /**
     * 
     * Converts an associative array to an attribute string.
     * 
     * @param array $attribs From this array, each key-value pair is 
     * converted to an attribute name and value.
     * 
     * @return string The XHTML for the attributes.
     * 
     */
    public function attribs($attribs)
    {
        $xhtml = array();
        foreach ((array) $attribs as $key => $val) {
            if (is_array($val)) {
                $val = implode(' ', $val);
            }
            $key     = $this->_view->escape($key);
            $val     = $this->_view->escape($val);
            $xhtml[] = "$key=\"$val\"";
        }
        return ' ' . implode(' ', $xhtml);
    }
}
?>