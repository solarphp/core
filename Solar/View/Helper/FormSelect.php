<?php
/**
 * 
 * Helper for 'select' list of options.
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
require_once 'Solar/View/Helper/FormElement.php';

/**
 * 
 * Helper for 'select' list of options.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
class Solar_View_Helper_FormSelect extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Generates 'select' list of options.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formSelect($info)
    {
        $this->_prepare($info);
        
        // force $this->_value to array so we can compare multiple values
        // to multiple options.
        settype($this->_value, 'array');
        
        // check for multiple attrib and change name if needed
        if (isset($this->_attribs['multiple']) &&
            $this->_attribs['multiple'] == 'multiple' &&
            substr($this->_name, -2) != '[]') {
            $this->_name .= '[]';
        }
        
        // check for multiple implied by the name, and set attrib if
        // needed
        if (substr($this->_name, -2) == '[]') {
            $this->_attribs['multiple'] = 'multiple';
        }
        
        // build the list of options
        $list = array();
        foreach ($this->_options as $opt_value => $opt_label) {
            $selected = '';
            if (in_array($opt_value, $this->_value)) {
                $selected = ' selected="selected"';
            }
            $list[] = '<option';
                    . ' value="' . $this->_view->escape($opt_value) . '"'
                    . ' label="' . $this->_view->escape($opt_label) . '"'
                    . $selected
                    . '>' . $this->_view->escape($opt_label) . "</option>";
        }
        
        // now build the XHTML
        return '<select name="' . $this->_view->escape($this->_name) . '"'
             . $this->_view->attribs($this->_attribs) . ">\n    "
             . implode("\n    ", $list);
             . "\n</select>";
    }
}
?>