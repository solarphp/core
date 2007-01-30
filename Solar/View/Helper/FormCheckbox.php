<?php
/**
 * 
 * Helper for a 'checkbox' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormCheckbox.php 1186 2006-05-21 15:38:37Z pmjones $
 * 
 */

/**
 * The abstract FormElement class.
 */
Solar::loadClass('Solar_View_Helper_FormElement');

/**
 * 
 * Helper for a 'checkbox' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
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
        $this->_prepare($info);
        
        // make sure there is a checked value
        if (empty($this->_options[0])) {
            $this->_options[0] = 1;
        }
        
        // make sure there is an unchecked value
        if (empty($this->_options[1])) {
            $this->_options[1] = 0;
        }
        
        // is it checked already?
        if ($this->_value == $this->_options[0]) {
            $this->_attribs['checked'] = 'checked';
        } else {
            unset($this->_attribs['checked']);
        }
        
        // add the "checked" option first
        $xhtml = '<input type="checkbox"'
               . ' name="' . $this->_view->escape($this->_name) . '"'
               . ' value="' . $this->_view->escape($this->_options[0]) . '"'
               . $this->_view->attribs($this->_attribs)
               . ' />';
               
        // wrap in a label?
        if ($this->_label) {
            $xhtml = '<label>' . $xhtml . $this->_view->escape($this->_label) . '</label>';
        }
        
        // prefix with unchecked value
        $xhtml = $this->_view->formHidden(array('name' => $this->_name, 'value' => $this->_options[1])) . $xhtml;
        
        return $xhtml;
    }
}
?>