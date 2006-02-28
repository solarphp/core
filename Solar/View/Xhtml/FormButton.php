<?php
/**
 * 
 * Helper for a 'button' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Solar_View_Helper_FormButton.php 654 2006-01-11 17:10:06Z pmjones $
 * 
 */

/**
 * The abstract FormElement class.
 */
require_once 'Solar/View/Xhtml/FormElement.php';

/**
 * 
 * Helper for a 'button' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
class Solar_View_Helper_FormButton extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Generates a 'button' element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formButton($info)
    {
        extract($this->_info($info));
        
        // build the element
        if ($disable) {
        
            // disabled. no hidden value because it can't be clicked.
            $xhtml = '[' . $this->_view->escape($value) . ']';
            
        } else {
        
            // enabled
            $xhtml .= '<input type="button"';
            $xhtml .= ' name="' . $this->_view->escape($name) . '"';
            
            if (! empty($value)) {
                $xhtml .= ' value="' . $this->_view->escape($value) . '"';
            }
            
            $xhtml .= $this->_view->attribs($attribs);
            $xhtml .= ' />';
            
        }
        
        return $xhtml;
    }
}
?>