<?php
/**
 * 
 * Helper for a 'button' element.
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
 * Helper for a 'button' element.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
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
        $this->_prepare($info);
        $xhtml = '<input type="button"'
               . ' name="' . $this->_view->escape($this->_name) . '"';
        
        if (! empty($this->_value)) {
            $xhtml .= ' value="' . $this->_view->escape($this->_value) . '"';
        }
        
        $xhtml .= $this->_view->attribs($this->_attribs) . ' />';
        return $xhtml;
    }
}
?>