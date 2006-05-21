<?php
/**
 * 
 * Helper for a 'reset' button.
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
 * The abstract FormElement class.
 */
Solar::loadClass('Solar_View_Helper_FormElement');

/**
 * 
 * Helper for a 'reset' button.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
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
        $this->_prepare($info);
        
        // we process values this way so that blank reset buttons
        // get the browser-default value
        if (empty($this->_value)) {
            $escval = '';
        } else {
            $escval = ' value="' . $this->_view->escape($this->_value) . '"';
        }
        
        // output
        return '<input type="reset"'
             . ' name="' . $this->_view->escape($this->_name) . '"'
             . $escval
             . $this->_view->attribs($this->_attribs)
             . ' />';
    }
}
?>