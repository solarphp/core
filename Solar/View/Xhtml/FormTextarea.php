<?php
/**
 * 
 * Helper for a 'textarea' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * The abstract FormElement class.
 */
require_once 'Solar/View/Xhtml/FormElement.php';

/**
 * 
 * Helper for a 'textarea' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
class Solar_View_Helper_FormTextarea extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Generates a 'textarea' element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formTextarea($info)
    {
        $this->_prepare($info);
        return '<textarea'
             . ' name="' . $this->_view->escape($this->_name) . '"'
             . $this->_view->attribs($this->_attribs) . '>'
             . $this->_view->escape($this->_value)
             . '</textarea>';
    }
}
?>