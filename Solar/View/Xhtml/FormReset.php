<?php
/**
 * 
 * Helper for a 'reset' button.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Solar_View_Helper_FormReset.php 654 2006-01-11 17:10:06Z pmjones $
 * 
 */

/**
 * The abstract FormElement class.
 */
require_once 'Solar/View/Xhtml/FormElement.php';

/**
 * 
 * Helper for a 'reset' button.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
class Solar_View_Helper_FormReset extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Generates a 'reset' button.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formReset($info)
    {
        extract($this->_info($info));
        $xhtml = '';
        
        // always enabled
        $xhtml .= '<input type="reset"';
        $xhtml .= ' name="' . $this->_view->escape($name) . '"';
        
        if (! empty($value)) {
            $xhtml .= ' value="' . $this->_view->escape($value) . '"';
        }
        
        $xhtml .= $this->_view->attribs($attribs);
        $xhtml .= ' />';
        
        return $xhtml;
    }
}
?>