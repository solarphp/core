<?php
/**
 * 
 * Helper for a set of radio button elements.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Solar_View_Helper_FormRadio.php 654 2006-01-11 17:10:06Z pmjones $
 * 
 */

/**
 * The abstract FormElement class.
 */
require_once 'Solar/View/Xhtml/FormElement.php';

/**
 * 
 * Helper for a set of radio button elements.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
class Solar_View_Helper_FormRadio extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Generates a set of radio button elements.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formRadio($info)
    {
        extract($this->_info($info));
        $radios = array();
        
        // build the element
        if ($disable) {
        
            // disabled.
            foreach ($options as $opt_value => $opt_label) {
                if ($opt_value == $value) {
                    // add a return value, and a checked text.
                    $opt = $this->_view->formHidden(array('name' => $name, 'value' => $opt_value))
                         . '[x]';
                } else {
                    // not checked
                    $opt = '[&nbsp;]';
                }
                $radios[] = $opt . '&nbsp;' . $this->_view->escape($opt_label);
            }
            
        } else {
        
            // enabled.
            // default value if none are checked.
            $radios[] = $this->_view->formHidden(array('name' => $name, 'value' => null)) . "\n";
            
            // add radio buttons.
            foreach ($options as $opt_value => $opt_label) {
            
                // is it checked?
                if ($opt_value == $value) {
                    $attribs['checked'] = 'checked';
                } else {
                    unset($attribs['checked']);
                }
                
                // build the radio button
                $radios[] = '<label><input type="radio"'
                          . ' name="' . $this->_view->escape($name) . '"'
                          . ' value="' . $this->_view->escape($opt_value) . '"'
                          . $this->_view->attribs($attribs) . ' />&nbsp;'
                          . $this->_view->escape($opt_label) . '</label>';
            }
        }
        
        return implode("\n", $radios);
    }
}
?>