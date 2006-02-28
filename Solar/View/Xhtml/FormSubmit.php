<?php
/**
 * 
 * Helper for a 'submit' button.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Solar_View_Helper_FormSubmit.php 654 2006-01-11 17:10:06Z pmjones $
 * 
 */

/**
 * The abstract FormElement class.
 */
require_once 'Solar/View/Xhtml/FormElement.php';

/**
 * 
 * Helper for a 'submit' button.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
class Solar_View_Helper_FormSubmit extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Generates a 'submit' button.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formSubmit($info)
    {
        extract($this->_info($info));
        $xhtml = '';
        
        // ignore disable/enable, always show the button.
        $xhtml .= '<input type="submit"';
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