<?php
/**
 * 
 * Plugin to convert an associative array to a string of tag attributes.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Solar_View_Helper_htmlAttribs.php 654 2006-01-11 17:10:06Z pmjones $
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
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
class Solar_View_Helper_Attribs extends Solar_View_Helper {
    
    /**
     * 
     * Converts an associative array to an attribute string.
     * 
     * @access public
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