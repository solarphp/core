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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

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
        $xhtml = '';
        foreach ((array) $attribs as $key => $val) {
            
            // skip empty values
            if (empty($val)) {
                continue;
            }
            
            // space-separate multiple values
            if (is_array($val)) {
                $val = implode(' ', $val);
            }
            
            // add the attribute
            $xhtml .= ' ' . $this->_view->escape($key)
                   .  '="' . $this->_view->escape($val) . '"';
        }
        
        // done
        return $xhtml;
    }
}
