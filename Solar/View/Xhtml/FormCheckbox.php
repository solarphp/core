<?php
/**
 * 
 * Helper for a 'checkbox' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Solar_View_Helper_FormCheckbox.php 654 2006-01-11 17:10:06Z pmjones $
 * 
 */

/**
 * The abstract FormElement class.
 */
require_once 'Solar/View/Xhtml/FormElement.php';

/**
 * 
 * Helper for a 'checkbox' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
class Solar_View_Helper_FormCheckbox extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Generates a 'checkbox' element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formCheckbox($info)
    {
        extract($this->_info($info));
        
        // make sure there is a checked value
        if (empty($options[0])) {
            $options[0] = 1;
        }
        
        // make sure there is an unchecked value
        if (empty($options[1])) {
            $options[1] = 0;
        }
        
        // build the element
        if ($disable) {
        
            // disabled.
            if ($value == $options[0]) {
                // checked
                $xhtml = $this->_view->formHidden($name, $options[0])
                       . '[x]';
            } else {
                // not checked
                $xhtml = $this->_view->formHidden($name, $options[1])
                       . '[&nbsp;]';
            }
            
            if ($info['label']) {
                $xhtml .= ' ' . $this->_view->escape($info['label']);
            }
            
        } else {
            
            // enabled.
            // is it checked already?
            if ($value == $options[0]) {
                $attribs['checked'] = 'checked';
            } else {
                unset($attribs['checked']);
            }
            
            // add the "checked" option first
            $xhtml = '<input type="checkbox"'
                   . ' name="' . $this->_view->escape($name) . '"'
                   . ' value="' . $this->_view->escape($options[0]) . '"'
                   . $this->_view->attribs($attribs)
                   . ' />';
                   
            // wrap in a label?
            if ($info['label']) {
                $xhtml = '<label>' . $xhtml . '&nbsp;' . $this->_view->escape($info['label']) . '</label>';
            }
            
            // prefix with unchecked value
            $xhtml = $this->_view->formHidden(array('name' => $name, 'value' => $options[1])) . $xhtml;
        }
        
        return $xhtml;
    }
}
?>