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
 * @version $Id: Solar_View_Helper_FormTextarea.php 654 2006-01-11 17:10:06Z pmjones $
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
    
    protected $_config = array(
        'rows' => 12,
        'cols' => 40,
    );
    
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
        extract($this->_info($info));
        $xhtml = '';
        
        // disabled?
        if ($disable) {
            $xhtml = $this->_view->formHidden(array('name' => $name, 'value' => $value));
            $attribs['readonly'] = 'readonly';
        }
        
        
        // make sure that there are 'rows' and 'cols' values.
        if (empty($attribs['rows'])) {
            $attribs['rows'] = (int) $this->_config['rows'];
        }
        
        if (empty($attribs['cols'])) {
            $attribs['cols'] = (int) $this->_config['cols'];
        }
        
        // now build the element.
        $xhtml .= '<textarea'
                . ' name="' . $this->_view->escape($name) . '"'
                . $this->_view->attribs($attribs) . '>'
                . $this->_view->escape($value)
                . '</textarea>';
            
        return $xhtml;
    }
}
?>