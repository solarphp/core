<?php
/**
 * 
 * Helper for a 'password' element.
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
 * The abstract FormElement class.
 */
require_once 'Solar/View/Helper/FormElement.php';

/**
 * 
 * Helper for a 'password' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 */
class Solar_View_Helper_FormPassword extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Generates a 'password' element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formPassword($info)
    {
        $this->_prepare($info);
        return '<input type="password"'
             . ' name="' . $this->_view->escape($this->_name) . '"'
             . ' value="' . $this->_view->escape($this->_value) . '"'
             . $this->_view->attribs($this->_attribs)
             . ' />';
    }
    
}
?>